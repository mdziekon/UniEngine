<?php

use \UniEngine\Utils\Migrations as Migrations;

use UniEngine\Utils\Migrations\FSHandler;

/**
 * Changelog:
 *  - Removes now obsolete constant "GAMEURL_REMOTE_TESTSERVERHOST"
 *    from "includes/constants.php".
 *
 */
class Migration_20190706_204558 implements Migrations\Interfaces\Migration {
    protected $fsHandler;
    protected $previousValues = [
        "constants" => [
            "GAMEURL_REMOTE_TESTSERVERHOST" => null
        ]
    ];

    public function __construct() {
        $this->fsHandler = new FSHandler([
            "rootPath" => "./"
        ]);
    }

    public function up() {
        $fileLines = $this->fsHandler->loadFileLines("includes/constants.php");

        // Find position of the existing constant
        $removeLineIdx = -1;

        foreach ($fileLines as $lineIdx => $lineValue) {
            if (strpos($lineValue, "GAMEURL_REMOTE_TESTSERVERHOST") === false) {
                continue;
            }

            $removeLineIdx = $lineIdx;
            break;
        }

        if ($removeLineIdx === -1) {
            throw new \Exception(
                "Could not find the position of \"GAMEURL_REMOTE_TESTSERVERHOST\" constant!" .
                "\"includes/constants.php\" is potentially broken."
            );
        }

        $currentValue = $this->extractCurrentValue($fileLines[$removeLineIdx]);

        $this->previousValues['constants']['GAMEURL_REMOTE_TESTSERVERHOST'] = $currentValue;

        array_splice(
            $fileLines,
            $removeLineIdx,
            1,
            []
        );

        $this->fsHandler->saveFileLines(
            "includes/constants.php",
            $fileLines
        );
    }

    public function down() {
        $fileLines = $this->fsHandler->loadFileLines("includes/constants.php");

        // Find position to reinject constant
        $insertAfter = -1;

        foreach ($fileLines as $lineIdx => $lineValue) {
            if (strpos($lineValue, "GAMEURL_UNISTRICT") === false) {
                continue;
            }

            $insertAfter = $lineIdx;
            break;
        }

        if ($insertAfter === -1) {
            throw new \Exception(
                "Could not find the position to reinject \"GAMEURL_REMOTE_TESTSERVERHOST\" constant! " .
                "\"GAMEURL_UNISTRICT\" constant not found! " .
                "\"includes/constants.php\" is potentially broken."
            );
        }

        array_splice(
            $fileLines,
            $insertAfter + 1,
            0,
            [
                "define('GAMEURL_REMOTE_TESTSERVERHOST', '{$this->previousValues['constants']['GAMEURL_REMOTE_TESTSERVERHOST']}');"
            ]
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

    private function extractCurrentValue($line) {
        $matches = [];

        $hasMatch = preg_match("/define\\('GAMEURL_REMOTE_TESTSERVERHOST'\\s*,\\s*'(.*?)'\\)/i", $line, $matches);

        if (!$hasMatch) {
            throw new \Exception(
                "Could not read the current value of \"GAMEURL_REMOTE_TESTSERVERHOST\" constant! " .
                "\"includes/constants.php\" is potentially broken."
            );
        }

        return $matches[1];
    }
}

?>
