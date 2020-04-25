<?php

namespace UniEngine\Utils\Migrations;

use UniEngine\Utils\Migrations\Exceptions\FileIOException;
use UniEngine\Utils\Migrations\Exceptions\FileMissingException;

class Migrator {
    const CONFIG_DIRECTORY = "./config";
    const CONFIG_LATESTMIGRATION_FILENAME = "latest-applied-migration";
    const MIGRATIONS_DIRECTORY = "./utils/migrator/migrations";
    const MIGRATIONS_TEMPLATE_FILEPATH = "./utils/migrator/src/migration.tpl";

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

        $latestAppliedID = null;

        try {
            $latestAppliedID = $this->loadLastAppliedMigrationID();
        } catch (FileMissingException $exception) {
            $latestAppliedID = null;
        }

        if ($latestAppliedID !== null) {
            $this->printLog("> Last applied migration ID: \"{$latestAppliedID}\"");
        } else {
            $this->printLog(
                "> No \"{$this->getConfigLatestMigrationFilepath()}\" file found, " .
                "assuming no migrations have been applied yet"
            );
        }

        $migrations = $this->getMigrationsNewerThan($migrations, $latestAppliedID);

        if (count($migrations) === 0) {
            $this->printLog("> The latest migration script is already applied");
            $this->printLog("> No migrations applied");

            return;
        }

        $checkMigrationConstraints = $this->checkMigrationsConstraints($migrations, [
            'lastAppliedMigrationID' => $latestAppliedID,
        ]);

        if (!$checkMigrationConstraints['canApplyMigrations']) {
            foreach ($checkMigrationConstraints['reasons'] as $reason) {
                $this->printLog($reason['message']);
            }

            $this->printLog("");
            $this->printLog("> No migrations applied");

            return;
        }

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
        $lastMigrationID = $this->fsHandler->loadFile(
            $this->getConfigLatestMigrationFilepath()
        );

        $isValid = preg_match("/^\d{8}_\d{6}$/", $lastMigrationID);

        if (!($isValid === 1)) {
            throw new \Exception(
                "Invalid migration ID in \"{$this->getConfigLatestMigrationFilepath()}\""
            );
        }

        return trim($lastMigrationID);
    }

    public function saveLastAppliedMigrationID($migrationID) {
        $this->fsHandler->saveFile(
            $this->getConfigLatestMigrationFilepath(),
            $migrationID
        );
    }

    /**
     * @param array $options Array containing generator options.
     *      $options = [
     *          'name' => (string) [required]
     *              Migration's name to be used in the file name.
     *      ]
     */
    public function generateNewMigration($options) {
        if (empty($options["name"])) {
            throw new \InvalidArgumentException("\$options[\"name\"] was not defined");
        }

        $content = $this->fsHandler->loadfile(
            self::MIGRATIONS_TEMPLATE_FILEPATH
        );

        $newMigrationID = date(
            "Ymd_His",
            time()
        );

        $content = str_replace(
            "{{MIGRATION_ID}}",
            $newMigrationID,
            $content
        );

        $this->fsHandler->saveFile(
            $this->getMigrationFilepath(
                $newMigrationID . "_" . $options["name"] . ".php"
            ),
            $content
        );
    }

    public function getConfigLatestMigrationFilepath() {
        return (
            self::CONFIG_DIRECTORY .
            "/" .
            self::CONFIG_LATESTMIGRATION_FILENAME
        );
    }

    private function getMigrationFilepath($filename) {
        return (
            self::MIGRATIONS_DIRECTORY .
            "/" .
            $filename
        );
    }

    private function loadMigrationEntries() {
        $list = $this->fsHandler->loadDirectoryFilenames(
            self::MIGRATIONS_DIRECTORY
        );

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
     *          'lastAppliedMigrationID' => (string)
     *      ]
     *
     * @return array {
     *      @var bool $canApplyMigrations
     *          Do all migrations in question meet their constraints?
     *      @var string[] $reasons
     *          Reason why the migrations do not meet constraints
     * }
     */
    private function checkMigrationsConstraints($migrationEntries, $options) {
        if (empty($migrationEntries)) {
            return [
                "canApplyMigrations" => true,
                "reasons" => [],
            ];
        }

        $result = [
            "canApplyMigrations" => true,
            "reasons" => [],
        ];

        $lastAppliedMigrationDate = \DateTime::createFromFormat(
            "Ymd_His",
            $options['lastAppliedMigrationID']
        );

        $this->sortMigrations($migrationEntries);

        $migrations = [];

        foreach ($migrationEntries as $migrationEntry) {
            $migrations[] = $this->instantiateMigration($migrationEntry);
        }

        foreach ($migrations as $migration) {
            $minimumMigrationLevelRequired = $migration["instance"]->getMinimumMigrationLevelRequired();

            if ($minimumMigrationLevelRequired === '') {
                continue;
            }

            $minimumMigrationLevelRequiredDate = \DateTime::createFromFormat(
                "Ymd_His",
                $minimumMigrationLevelRequired
            );

            if (
                $minimumMigrationLevelRequiredDate->getTimestamp() >
                $lastAppliedMigrationDate->getTimestamp()
            ) {
                $previousVersion = $migration["instance"]->getPreviousProjectVersion();

                $reasonMessage = (
                    "> Migration \"{$migration["className"]}\" cannot be applied, " .
                    "because it does not meet one of its constraints:\n" .
                    "This instance is required to have one of the previous migrations already applied " .
                    "(migrationId: \"{$minimumMigrationLevelRequired}\").\n" .
                    "You most likely have to revert your instance's code back to version \"{$previousVersion}\", " .
                    "apply its migrations and then try again with more recent code.\n" .

                    "It is recommended to read release notes for both version \"{$previousVersion}\" " .
                    "and the next one, succeeding it."
                );

                $result['canApplyMigrations'] = false;
                $result['reasons'][] = [
                    'message' => $reasonMessage,
                ];
            }
        }

        return $result;
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
                        $this->printLog(
                            "> Migration notes (more details in the release notes):\n" .
                            $migration["instance"]->getPriorManualActionDescription()
                        );

                        break;
                    }

                    $isManualActionRequired = false;

                    $this->printLog(
                        "> Migration's \"{$migration["className"]}\" manual action confirmed, " .
                        "proceeding..."
                    );
                }

                $this->printLog("> Running migration \"{$migration["className"]}\"");

                $lastRunMigrationIdx = $idx;

                $migration["instance"]->up();
            }
        } catch (\Exception $exception) {
            // Try to revert all already applied migrations

            $lastMigration = $migrations[$lastRunMigrationIdx];

            $this->printLog("> An error occured while running migration \"{$lastMigration["className"]}\"");
            $this->printLog("");
            $this->printLog($exception->__toString());
            $this->printLog("");

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

        $migrationClassFilePath = $this->fsHandler->getRealPath(
            $this->getMigrationFilepath($filename)
        );

        require_once($migrationClassFilePath);

        $migrationClass = "Migration_" . $migrationID;

        $reflectionClass = new \ReflectionClass($migrationClass);
        $migrationInterfaceName = "\UniEngine\Utils\Migrations\Interfaces\Migration";

        if (!($reflectionClass->implementsInterface($migrationInterfaceName))) {
            throw new \Exception(
                "Migration \"{$migrationClass}\" (\"{$filename}\") " .
                "does not implement Migration interface " .
                "({$migrationInterfaceName})"
            );
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
