<?php

use \UniEngine\Utils\Migrations as Migrations;

/**
 * Changelog:
 *  - Removes obsolete comment on fleet_archive::Fleet_Destroyed_Reason
 *    (codes documentation moved to in-source enums)
 *
 */
class Migration_20200731_011754 implements Migrations\Interfaces\Migration {
    protected $configProvider;

    public function __construct() {
        $this->configProvider = new Migrations\PHPConfigProvider();
    }

    public function up() {
        $db = $this->getDBDriver();

        $db->executeQuery(
            "ALTER TABLE `{{table(fleet_archive)}}` " .
            "CHANGE `Fleet_Destroyed_Reason` " .
            "`Fleet_Destroyed_Reason` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' " .
            "COMMENT ''" .
            ";"
        );
    }

    public function down() {
        $db = $this->getDBDriver();

        $db->executeQuery(
            "ALTER TABLE `{{table(fleet_archive)}}` " .
            "CHANGE `Fleet_Destroyed_Reason` " .
            "`Fleet_Destroyed_Reason` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' " .
            "COMMENT '1 - inBattle_FirstRound_NoDamage; 2 - inBattle_OtherRound_Damage; 3 - friendDefense; 4 - drawNoBash; 5 - inBattle_ACSLeader; 6 - inBattle_ACSJoined; 7 - byMoon; 8 - doColony; 9 - missile; 10 - antiSpy; 11 - inBattle_OtherRound_NoDamage; 12 - inBattle_FirstRound_Damage;' " .
            ";"
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
