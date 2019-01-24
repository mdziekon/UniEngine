<?php

namespace UniEngine\Utils\Migrations;

class Migrator {
    private $rootPath;

    //  $options:
    //      - rootPath (string)
    //
    function __construct($options) {
        $this->rootPath = $options["rootPath"];
    }

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
}

?>
