<?php

use \UniEngine\Utils\Migrations as Migrations;

/**
 * Changelog:
 *  - Stop-gap migration forcing users to apply one-off script
 *    "20200424_031431_tasks_applylockedjobs"
 *
 */
class Migration_20200425_234106 implements Migrations\Interfaces\Migration {
    public function up() {
        // noop
    }

    public function down() {
        // noop
    }

    public function isPriorManualActionRequired() {
        return true;
    }

    public function getPreviousProjectVersion() {
        return "1.0.0";
    }

    public function getMinimumMigrationLevelRequired() {
        return "";
    }

    public function getPriorManualActionDescription() {
        return (
            "- Apply one-off script \"20200424_031431_tasks_applylockedjobs\" by running the following command:\n" .
            "  $ composer run-script utils:oneoffs:run 20200424_031431 \n" .
            "- Proceed with migration process by using the \"--confirm-manual-action\" flag."
        );
    }
}

?>
