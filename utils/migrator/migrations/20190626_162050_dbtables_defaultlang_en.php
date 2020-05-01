<?php

use \UniEngine\Utils\Migrations as Migrations;

/**
 * Changelog:
 *  - Changes the default value of language to "en" (previously was "pl")
 *
 */
class Migration_20190626_162050 implements Migrations\Interfaces\Migration {
    protected $configProvider;

    public function __construct() {
        $this->configProvider = new Migrations\PHPConfigProvider();
    }

    public function up() {
        $db = $this->getDBDriver();

        $db->executeQuery(
            "ALTER TABLE `{{table(users)}}` " .
            "ALTER COLUMN `lang` " .
            "SET DEFAULT 'en';"
        );
    }

    public function down() {
        $db = $this->getDBDriver();

        $db->executeQuery(
            "ALTER TABLE `{{table(users)}}` " .
            "ALTER COLUMN `lang` " .
            "SET DEFAULT 'pl';"
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
