<?php

use \UniEngine\Utils\Migrations as Migrations;

/**
 * Changelog:
 *  - Mines, power planets & satellites are now managed by "_workpercent" values
 *    (instead of incorrect "_porcent")
 *
 */
class Migration_20130722_235900 implements Migrations\Interfaces\Migration {
    protected $configProvider;

    public function __construct() {
        $self = $this;

        $this->configProvider = new Migrations\PHPConfigProvider();
    }

    public function up() {
        $db = $this->getDBDriver();

        if ($this->isLegacyMigrationAlreadyApplied($db)) {
            return;
        }

        $migrationQueries = [
            "ALTER TABLE `{{table(planets)}}` CHANGE `metal_mine_porcent` `metal_mine_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';",
            "ALTER TABLE `{{table(planets)}}` CHANGE `crystal_mine_porcent` `crystal_mine_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';",
            "ALTER TABLE `{{table(planets)}}` CHANGE `deuterium_synthesizer_porcent` `deuterium_synthesizer_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';",
            "ALTER TABLE `{{table(planets)}}` CHANGE `solar_plant_porcent` `solar_plant_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';",
            "ALTER TABLE `{{table(planets)}}` CHANGE `fusion_reactor_porcent` `fusion_reactor_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';",
            "ALTER TABLE `{{table(planets)}}` CHANGE `solar_satellite_porcent` `solar_satellite_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';"
        ];

        foreach ($migrationQueries as $query) {
            $db->executeQuery($query);
        }
    }

    public function down() {
        $db = $this->getDBDriver();

        $migrationQueries = [
            "ALTER TABLE `{{table(planets)}}` CHANGE `metal_mine_workpercent` `metal_mine_porcent` tinyint(3) unsigned NOT NULL DEFAULT '10';",
            "ALTER TABLE `{{table(planets)}}` CHANGE `crystal_mine_workpercent` `crystal_mine_porcent` tinyint(3) unsigned NOT NULL DEFAULT '10';",
            "ALTER TABLE `{{table(planets)}}` CHANGE `deuterium_synthesizer_workpercent` `deuterium_synthesizer_porcent` tinyint(3) unsigned NOT NULL DEFAULT '10';",
            "ALTER TABLE `{{table(planets)}}` CHANGE `solar_plant_workpercent` `solar_plant_porcent` tinyint(3) unsigned NOT NULL DEFAULT '10';",
            "ALTER TABLE `{{table(planets)}}` CHANGE `fusion_reactor_workpercent` `fusion_reactor_porcent` tinyint(3) unsigned NOT NULL DEFAULT '10';",
            "ALTER TABLE `{{table(planets)}}` CHANGE `solar_satellite_workpercent` `solar_satellite_porcent` tinyint(3) unsigned NOT NULL DEFAULT '10';"
        ];

        foreach ($migrationQueries as $query) {
            $db->executeQuery($query);
        }
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

    private function isLegacyMigrationAlreadyApplied($db) {
        $tableFields = $db->fetchAllRows(
            "DESCRIBE `{{table(planets)}}`;"
        );

        $hasNewNames = null;

        $oldNames = [
            "metal_mine_porcent",
            "crystal_mine_porcent",
            "deuterium_synthesizer_porcent",
            "solar_plant_porcent",
            "fusion_reactor_porcent",
            "solar_satellite_porcent"
        ];
        $newNames = [
            "metal_mine_workpercent",
            "crystal_mine_workpercent",
            "deuterium_synthesizer_workpercent",
            "solar_plant_workpercent",
            "fusion_reactor_workpercent",
            "solar_satellite_workpercent"
        ];

        foreach ($tableFields as $tableFieldDetails) {
            $fieldName = $tableFieldDetails["Field"];

            if (in_array($fieldName, $oldNames)) {
                // Found at least one old name, assuming all names are old
                $hasNewNames = false;
                break;
            }
            if (in_array($fieldName, $newNames)) {
                // Found at least one new name, assuming all names are new
                $hasNewNames = true;
                break;
            }
        }

        if ($hasNewNames === null) {
            throw new \Exception(
                "Could not determine if legacy migration \"update1.php\" was applied. " .
                "Database is potentially broken..."
            );
        }

        return ($hasNewNames === true);
    }
}

?>
