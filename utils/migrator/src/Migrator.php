<?php

namespace UniEngine\Utils\Migrations;

class Migrator {
    private $rootPath;

    /**
     * @param array $options Array containing general options.
     *      $options = [
     *          'rootPath' => (string)
     *              The base location of the project. Necessary to locate
     *              "/migrations" directory.
     *      ]
     */
    function __construct($options) {
        $this->rootPath = $options["rootPath"];
    }

    /**
     * @param array $options Array containing migration options.
     *      $options = [
     *          'wasManualActionConfirmed' => (bool)
     *              Whether we should allow the first migration on the unapplied
     *              list to be run regardless of its manual action requirement.
     *      ]
     */
    public function runMigration($options) {
        $migrations = $this->loadMigrationEntries();

        $latestAppliedID;

        try {
            $latestAppliedID = $this->loadLastMigrationID();
        } catch (FileMissingException $exception) {
            $latestAppliedID = null;
        }

        if ($latestAppliedID !== null) {
            $this->printLog("> Last applied migration ID: \"{$latestAppliedID}\"");
        } else {
            $this->printLog("> No \"config/latest-migration\" file found, assuming no migrations have been applied yet");
        }

        $migrations = $this->getMigrationsNewerThan($migrations, $latestAppliedID);

        $lastAppliedID = $this->applyMigrations($migrations, $options);

        if ($lastAppliedID !== null) {
            $this->saveMigrationID($lastAppliedID);
        } else {
            $this->printLog("> No migrations applied");
        }
    }

    private function getRealPath($path) {
        return ($this->rootPath . $path);
    }

    private function loadFile($filePath) {
        $path = $this->getRealPath($filePath);

        if (!file_exists($path)) {
            throw new FileMissingException("File does not exist");
        }

        if (!is_readable($path)) {
            throw new FileIOException("File is not readable");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new FileIOException("File could not be loaded");
        }

        return $content;
    }

    private function saveFile($filePath, $data) {
        $path = $this->getRealPath($filePath);

        $accessCheckPath = $path;

        if (!file_exists($path)) {
            $accessCheckPath = dirname($path);
        } else {
            $accessCheckPath = $path;
        }

        if (!is_writeable($accessCheckPath)) {
            throw new FileIOException("File / file's directory is not writeable");
        }

        $result = file_put_contents($path, $data,  LOCK_EX);

        if ($result === false) {
            throw new FileIOException("File could not be saved");
        }
    }

    private function loadLastMigrationID() {
        $lastMigrationID = $this->loadFile("./config/latest-migration");

        $isValid = preg_match("/^\d{8}_\d{6}$/", $lastMigrationID);

        if (!($isValid === 1)) {
            throw new \Exception("Invalid migration ID in \"config/latest-migration\"");
        }

        return $lastMigrationID;
    }

    private function saveMigrationID($migrationID) {
        $this->saveFile("./config/latest-migration", $migrationID);
    }

    private function loadMigrationEntries() {
        $migrationsPath = $this->getRealPath("./migrations");

        $list = scandir($migrationsPath);

        if ($list === false) {
            throw new FileIOException("Could not load migrations directory");
        }

        $migrationFiles = array_filter($list, function ($file) {
            // Migration scripts' filenames follow this pattern:
            // <4 digit year><2 digit month><2 digit day>_<2 digit hour><2 digit minute><2 digit second>_<short description>.php
            $isMatching = preg_match("/^\d{8}_\d{6}_.*?\.php$/", $file);

            return ($isMatching === 1);
        });

        return array_map(function ($file) {
            preg_match(
                "/^(\d{8}_\d{6})_(.*?)\.php$/",
                $file,
                $matches
            );

            $datetime = \DateTime::createFromFormat(
                "Ymd_His",
                $matches[1]
            );

            return [
                "filename" => $file,
                "id" => $datetime->format("Ymd_His"),
                "datetime" => $datetime,
                "desc" => $matches[2]
            ];
        }, $migrationFiles);
    }

    private function getMigrationsNewerThan($migrations, $latestAppliedID) {
        if ($latestAppliedID === null) {
            return $migrations;
        }

        $latestDatetime = \DateTime::createFromFormat(
            "Ymd_His",
            $latestAppliedID
        );

        return array_filter($migrations, function ($migrationEntry) use ($latestDatetime) {
            return (
                $migrationEntry["datetime"]->getTimestamp() >
                $latestDatetime->getTimestamp()
            );
        });
    }

    private function sortMigrations($migrations) {
        usort($migrations, function ($left, $right) {
            return (
                $left["datetime"]->getTimestamp() -
                $right["datetime"]->getTimestamp()
            );
        });

        return $migrations;
    }

    /**
     * @param array $migrationEntries
     * @param array $options Array containing application options.
     *      $options = [
     *          'wasManualActionConfirmed' => (bool)
     *              Whether we should allow the first migration on the unapplied
     *              list to be run regardless of its manual action requirement.
     *      ]
     *
     * @return array {
     *      @var int $migrationsApplied
     *          How many migrations were applied.
     *      @var bool $migrationRolledback
     *          Was there a problem with one of the migrations which caused
     *          a rollback?
     *      @var string | null $lastAppliedMigrationID
     *          Last migration's ID. Either the actually applied migration,
     *          or the one that caused a rollback.
     *      @var bool $isManualActionRequired
     *          Whether the migration process was stopped because of a manual
     *          action requirement.
     * }
     */
    private function applyMigrations($migrationEntries, $options) {
        if (empty($migrationEntries)) {
            return [
                "migrationsApplied" => 0,
                "migrationRolledback" => false,
                "lastAppliedMigrationID" => null,
                "isManualActionRequired" => false
            ];
        }

        $this->sortMigrations($migrationEntries);

        $migrations = [];
        $lastAppliedMigrationIdx = -1;
        $isManualActionRequired = false;

        foreach ($migrationEntries as $migrationEntry) {
            $migrations[] = $this->instantiateMigration($migrationEntry);
        }

        try {
            // Try to apply all migrations
            foreach ($migrations as $idx => $migration) {
                $isManualActionRequired = $migration["instance"]->isPriorManualActionRequired();

                if ($isManualActionRequired) {
                    if ($idx !== 0) {
                        // Manual action confirmation applies only to the first action
                        // to prevent unexpected constrained migrations down the line
                        // from running.

                        $this->printLog("> Migration \"{$migration["className"]}\" requires manual action. Read release notes, apply any required manual actions and then run migrations again with \"--confirmManualAction\" flag.");

                        break;
                    }
                    if (!($options["wasManualActionConfirmed"])) {
                        // This is the first migration, but manual action was not confirmed.

                        $this->printLog("> Migration \"{$migration["className"]}\" requires manual action. Read release notes, apply any required manual actions and then run migrations again with \"--confirmManualAction\" flag.");

                        break;
                    }

                    $isManualActionRequired = false;

                    $this->printLog("> Migration's \"{$migration["className"]}\" manual action confirmed, proceeding...");
                }

                $this->printLog("> Running migration \"{$migration["className"]}\"");

                $migration["instance"]->up();

                $lastAppliedMigrationIdx = $idx;
            }
        } catch (\Exception $exception) {
            // Try to revert all already applied migrations

            $lastMigrationID = $migrations[$lastAppliedMigrationIdx]["id"];

            for ($idx = $lastAppliedMigrationIdx; $idx >= 0; $idx--) {
                $migration = $migrations[$idx];

                $migration["instance"]->down();
            }

            return [
                "migrationsApplied" => 0,
                "migrationRolledback" => true,
                "lastAppliedMigrationID" => $lastMigrationID,
                "isManualActionRequired" => false
            ];
        }

        $lastMigration = $migrations[$lastAppliedMigrationIdx];

        return [
            "migrationsApplied" => ($lastAppliedMigrationIdx + 1),
            "migrationRolledback" => false,
            "lastAppliedMigrationID" => $lastMigration["id"],
            "isManualActionRequired" => $isManualActionRequired
        ];
    }

    private function instantiateMigration($migrationEntry) {
        $migrationID = $migrationEntry["id"];
        $filename = $migrationEntry["filename"];
        $migrationPath = $this->getRealPath("./migrations/" . $filename);

        require_once($migrationPath);

        $migrationClass = "Migration_" . $migrationID;

        $reflectionClass = new \ReflectionClass($migrationClass);

        if (!($reflectionClass->implementsInterface("\UniEngine\Utils\Migrations\Interfaces\Migration"))) {
            throw new \Exception("Migration \"{$migrationClass}\" (\"{$filename}\") does not implement Migration interface");
        }

        return [
            "id" => $migrationID,
            "className" => $reflectionClass->getName(),
            "instance" => $reflectionClass->newInstance()
        ];
    }

    private function printLog($line) {
        echo "{$line}\n";
    }
}

?>
