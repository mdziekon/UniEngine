<?php

use \UniEngine\Utils\Migrations as Migrations;

use UniEngine\Utils\Migrations\FSHandler;

/**
 * Changelog:
 *  - Adds new constant "REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME"
 *    to the "includes/constants.php" file
 *
 */
class Migration_20190119_225700 implements Migrations\Interfaces\Migration {
    protected $fsHandler;

    public function __construct() {
        $this->fsHandler = new FSHandler([
            "rootPath" => "./"
        ]);
    }

    public function up() {
        $fileLines = $this->fsHandler->loadFileLines("includes/constants.php");

        if ($this->isLegacyMigrationAlreadyApplied($fileLines)) {
            echo "> Legacy migration already applied, skipping...\n";

            return;
        }

        // Find position to inject new constant
        $insertAfter = -1;

        foreach ($fileLines as $lineIdx => $lineValue) {
            if (strpos($lineValue, "REGISTER_RECAPTCHA_ENABLE") === false) {
                continue;
            }

            $insertAfter = $lineIdx;
            break;
        }

        if ($insertAfter === -1) {
            throw new \Exception(
                "Could not find the position to inject \"REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME\" constant! " .
                "\"REGISTER_RECAPTCHA_ENABLE\" constant not found!"
            );
        }

        array_splice(
            $fileLines,
            $insertAfter + 1,
            0,
            [
                "define('REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME', true);"
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
            if (strpos($lineValue, "REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME") === false) {
                continue;
            }

            $insertAfter = $lineIdx;
            break;
        }

        if ($insertAfter === -1) {
            throw new \Exception(
                "Could not find the position of \"REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME\" constant!" .
                "\"includes/constants.php\" is potentially broken."
            );
        }

        array_splice(
            $fileLines,
            $insertAfter,
            1,
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

    private function isLegacyMigrationAlreadyApplied($fileLines) {
        foreach ($fileLines as $lineIdx => $lineValue) {
            if (strpos($lineValue, "REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME") !== false) {
                return true;
            }
        }

        return false;
    }
}

?>
