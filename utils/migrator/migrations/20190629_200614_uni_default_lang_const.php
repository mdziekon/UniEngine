<?php

use \UniEngine\Utils\Migrations as Migrations;

use UniEngine\Utils\Migrations\FSHandler;

/**
 * Changelog:
 *  - Adds new constant "UNI_DEFAULT_LANG"
 *    to the "includes/constants.php" file
 *    The value is set to "pl", because that was the only language available before.
 *
 */
class Migration_20190629_200614 implements Migrations\Interfaces\Migration {
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
            if (strpos($lineValue, "GAMEURL_REMOTE_TESTSERVERHOST") === false) {
                continue;
            }

            $insertAfter = $lineIdx;
            break;
        }

        if ($insertAfter === -1) {
            throw new \Exception(
                "Could not find the position to inject \"UNI_DEFAULT_LANG\" constant! " .
                "\"GAMEURL_REMOTE_TESTSERVERHOST\" constant not found!"
            );
        }

        array_splice(
            $fileLines,
            $insertAfter + 1,
            0,
            [
                "define('UNI_DEFAULT_LANG', 'pl');"
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
            if (strpos($lineValue, "UNI_DEFAULT_LANG") === false) {
                continue;
            }

            $insertAfter = $lineIdx;
            break;
        }

        if ($insertAfter === -1) {
            throw new \Exception(
                "Could not find the position of \"UNI_DEFAULT_LANG\" constant!" .
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
}

?>
