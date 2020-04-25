<?php

use \UniEngine\Utils\Migrations as Migrations;

/**
 * Changelog:
 *  - Removes the last trace of the old "updater.php" admin panel script
 *
 */
class Migration_20190219_024600 implements Migrations\Interfaces\Migration {
    protected $configProvider;

    public function __construct() {
        $self = $this;

        $this->configProvider = new Migrations\PHPConfigProvider();
    }

    public function up() {
        $db = $this->getDBDriver();

        $db->executeQuery(
            "DELETE FROM `{{table(config)}}` " .
            "WHERE `config_name` = 'UniEngine_Updater_LastUpdateApplied' " .
            "LIMIT 1;"
        );
    }

    public function down() {
        $db = $this->getDBDriver();

        $db->executeQuery(
            "INSERT INTO `{{table(config)}}` (`config_name`, `config_value`) " .
            "VALUES ('UniEngine_Updater_LastUpdateApplied', '2');"
        );
    }

    public function isPriorManualActionRequired() {
        return false;
    }

    public function getPreviousProjectVersion() {
        return "1.0.0";
    }

    public function getMinimumMigrationLevelRequired() {
        return "";
    }

    public function getPriorManualActionDescription() {
        return "";
    }

    private function getDBDriver() {
        $self = $this;
        $db = new Migrations\DBDriver([
            "configProvider" => function () use ($self) {
                return $self->configProvider->getDatabaseConfig();
            }
        ]);

        return $db;
    }
}

?>
