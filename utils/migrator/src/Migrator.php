<?php

namespace UniEngine\Utils\Migrations;

use UniEngine\Utils\Migrations\Exceptions\FileIOException;
use UniEngine\Utils\Migrations\Exceptions\FileMissingException;

class Migrator {
    private $fsHandler;

    /**
     * @param array $options Array containing general options.
     *      $options = [
     *          'rootPath' => (string)
     *              The base location of the project. Necessary to locate
     *              "/migrations" directory.
     *      ]
     */
    function __construct($options) {
        $this->fsHandler = new FSHandler([
            "rootPath" => $options["rootPath"]
        ]);
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
            $latestAppliedID = $this->loadLastAppliedMigrationID();
        } catch (FileMissingException $exception) {
            $latestAppliedID = null;
        }

        if ($latestAppliedID !== null) {
            $this->printLog("> Last applied migration ID: \"{$latestAppliedID}\"");
        } else {
            $this->printLog("> No \"config/latest-applied-migration\" file found, assuming no migrations have been applied yet");
        }

        $migrations = $this->getMigrationsNewerThan($migrations, $latestAppliedID);

        $migrationResult = $this->applyMigrations($migrations, $options);

        if ($migrationResult["migrationsApplied"] > 0) {
            $lastMigrationID = $migrationResult["lastAppliedMigrationID"];

            $this->printLog("> Saving \"{$lastMigrationID}\" as the last applied migration ID");

            $this->saveLastAppliedMigrationID($lastMigrationID);
        } else {
            $this->printLog("> No migrations applied");
        }
    }

    public function getMostRecentMigrationID() {
        $migrationEntries = $this->loadMigrationEntries();

        if (empty($migrationEntries)) {
            return null;
        }

        $this->sortMigrations($migrationEntries);

        $lastMigrationEntry = end($migrationEntries);

        return $lastMigrationEntry["id"];
    }

    public function loadLastAppliedMigrationID() {
        $lastMigrationID = $this->fsHandler->loadFile("./config/latest-applied-migration");

        $isValid = preg_match("/^\d{8}_\d{6}$/", $lastMigrationID);

        if (!($isValid === 1)) {
            throw new \Exception("Invalid migration ID in \"config/latest-applied-migration\"");
        }

        return $lastMigrationID;
    }

    public function saveLastAppliedMigrationID($migrationID) {
        $this->fsHandler->saveFile("./config/latest-applied-migration", $migrationID);
    }

    private function loadMigrationEntries() {
        $list = $this->fsHandler->loadDirectoryFilenames("./migrations");

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
        $lastRunMigrationIdx = -1;
        $isManualActionRequired = false;

        foreach ($migrationEntries as $migrationEntry) {
            $migrations[] = $this->instantiateMigration($migrationEntry);
        }

        try {
            // Try to apply all migrations
            foreach ($migrations as $idx => $migration) {
                $isManualActionRequired = $migration["instance"]->isPriorManualActionRequired();

                if ($isManualActionRequired) {
                    if (
                        $idx !== 0 ||
                        !($options["wasManualActionConfirmed"])
                    ) {
                        // Manual action confirmation applies only to the first action
                        // to prevent unexpected constrained migrations down the line
                        // from running.
                        // It also has to be confirmed by the user.
                        $previousVersion = $migration["instance"]->getPreviousProjectVersion();

                        $this->printLog(
                            "> Migration \"{$migration["className"]}\" requires manual action. " .
                            "Read release notes (post release \"{$previousVersion}\"), " .
                            "apply any required manual actions " .
                            "and then run migrations again with \"--confirm-manual-action\" flag."
                        );

                        break;
                    }

                    $isManualActionRequired = false;

                    $this->printLog("> Migration's \"{$migration["className"]}\" manual action confirmed, proceeding...");
                }

                $this->printLog("> Running migration \"{$migration["className"]}\"");

                $lastRunMigrationIdx = $idx;

                $migration["instance"]->up();
            }
        } catch (\Exception $exception) {
            // Try to revert all already applied migrations

            $lastMigration = $migrations[$lastRunMigrationIdx];

            $this->printLog("> An error occured while running migration \"{$lastMigration["className"]}\"");
            $this->printLog($exception->getMessage());
            $this->printLog($exception->getTraceAsString());

            for ($idx = ($lastRunMigrationIdx - 1); $idx >= 0; $idx--) {
                $migration = $migrations[$idx];

                $this->printLog("> Reverting migration \"{$migration["className"]}\"");

                $migration["instance"]->down();
            }

            return [
                "migrationsApplied" => 0,
                "migrationRolledback" => true,
                "lastAppliedMigrationID" => $lastMigration["id"],
                "isManualActionRequired" => false
            ];
        }

        if ($lastRunMigrationIdx !== -1) {
            $lastMigration = $migrations[$lastRunMigrationIdx];
        } else {
            $lastMigration = [
                "id" => null
            ];
        }

        return [
            "migrationsApplied" => ($lastRunMigrationIdx + 1),
            "migrationRolledback" => false,
            "lastAppliedMigrationID" => $lastMigration["id"],
            "isManualActionRequired" => $isManualActionRequired
        ];
    }

    private function instantiateMigration($migrationEntry) {
        $migrationID = $migrationEntry["id"];
        $filename = $migrationEntry["filename"];

        $migrationClassFilePath = $this->fsHandler->getRealPath("./migrations/" . $filename);

        require_once($migrationClassFilePath);

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
