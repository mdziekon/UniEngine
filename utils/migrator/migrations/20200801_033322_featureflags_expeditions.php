<?php

use \UniEngine\Utils\Migrations as Migrations;

use UniEngine\Utils\Migrations\FSHandler;

/**
 * Changelog:
 *  - Adds new constant "FEATURES__EXPEDITIONS__ISENABLED"
 *    to the "includes/constants.php" file
 *
 */
class Migration_20200801_033322 implements Migrations\Interfaces\Migration {
    protected $fsHandler;

    public function __construct() {
        $this->fsHandler = new FSHandler([
            "rootPath" => "./"
        ]);
    }

    public function up() {
        $fileLines = $this->fsHandler->loadFileLines("includes/constants.php");

        // Find position to inject new constant
        $insertAfter = -1;

        foreach ($fileLines as $lineIdx => $lineValue) {
            if (strpos($lineValue, "MAILER_MSGFIELDS_FROM_NAME") === false) {
                continue;
            }

            $insertAfter = $lineIdx;
            break;
        }

        if ($insertAfter === -1) {
            throw new \Exception(
                "Could not find the position to inject \"FEATURES__EXPEDITIONS__ISENABLED\" constant! " .
                "\"MAILER_MSGFIELDS_FROM_NAME\" constant not found!"
            );
        }

        array_splice(
            $fileLines,
            $insertAfter + 1,
            0,
            [
                "\n" .
                "// --- Feature flags ---\n" .
                "define('FEATURES__EXPEDITIONS__ISENABLED', true);"
            ]
        );

        $this->fsHandler->saveFileLines(
            "includes/constants.php",
            $fileLines
        );
    }

    public function down() {
        $fileLines = $this->fsHandler->loadFileLines("includes/constants.php");

        // Find position of the new constant
        $insertAfter = -1;

        foreach ($fileLines as $lineIdx => $lineValue) {
            if (strpos($lineValue, "FEATURES__EXPEDITIONS__ISENABLED") === false) {
                continue;
            }

            $insertAfter = $lineIdx;
            break;
        }

        if ($insertAfter === -1) {
            throw new \Exception(
                "Could not find the position of \"FEATURES__EXPEDITIONS__ISENABLED\" constant!" .
                "\"includes/constants.php\" is potentially broken."
            );
        }

        array_splice(
            $fileLines,
            $insertAfter - 2,
            3,
            []
        );

        $this->fsHandler->saveFileLines(
            "includes/constants.php",
            $fileLines
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
}

?>
