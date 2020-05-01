<?php

namespace UniEngine\Utils\OneOffs;

class ScriptRunner {
    const SCRIPTS_DIRECTORY = "./utils/one_offs/scripts";

    private $fsHandler;

    /**
     * @param array $options Array containing general options.
     *      $options = [
     *          'rootPath' => (string)
     *              The base location of the project. Necessary to locate
     *              "/scripts" directory.
     *      ]
     */
    function __construct($options) {
        $this->fsHandler = new FSHandler([
            "rootPath" => $options["rootPath"]
        ]);
    }

    /**
     * @param array $options Array containing script options.
     *      $options = [
     *          'id' => (string)
     *      ]
     */
    public function runScript($options) {
        $scripts = $this->loadScriptEntries();

        $scriptID = $options["id"];

        $scriptEntries = array_filter(
            $scripts,
            function ($script) use ($scriptID) {
                return $script["id"] === $scriptID;
            }
        );

        if (empty($scriptEntries)) {
            $this->printLog("> Script \"{$scriptID}\" not found!");

            return;
        }

        $scriptEntry = array_values($scriptEntries)[0];

        $scriptResult = $this->executeScript($scriptEntry, $options);

        if ($scriptResult["isSuccess"]) {
            $this->printLog("> Script \"{$scriptID}\" has been successfuly executed");
        } else {
            $this->printLog("> Script \"{$scriptID}\" has been failed to execute properly");
        }
    }

    private function getScriptFilepath($filename) {
        return (
            self::SCRIPTS_DIRECTORY .
            "/" .
            $filename
        );
    }

    private function loadScriptEntries() {
        $list = $this->fsHandler->loadDirectoryFilenames(self::SCRIPTS_DIRECTORY);

        $scriptFiles = array_filter($list, function ($file) {
            // Scripts' filenames follow this pattern:
            // <4 digit year><2 digit month><2 digit day>_<2 digit hour><2 digit minute><2 digit second>_<short description>.php
            $isMatching = preg_match("/^\d{8}_\d{6}_.*?\.php$/", $file);

            return ($isMatching === 1);
        });

        return array_map(function ($file) {
            preg_match(
                "/^(\d{8}_\d{6})_(.*?)\.php$/",
                $file,
                $matches
            );

            $datetime = \DateTime::createFromFormat(
                "Ymd_His",
                $matches[1]
            );

            return [
                "filename" => $file,
                "id" => $datetime->format("Ymd_His"),
                "datetime" => $datetime,
                "desc" => $matches[2]
            ];
        }, $scriptFiles);
    }

    /**
     * @param array $scriptEntry
     * @param array $options Array containing application options.
     *      $options = []
     *
     * @return array {
     *      @var bool $isSuccess
     *          Did the script execution succeed?
     * }
     */
    private function executeScript($scriptEntry, $options) {
        $script = $this->instantiateScript($scriptEntry);

        try {
            $this->printLog("> Running script \"{$script["className"]}\"");

            $script["instance"]->execute();
        } catch (\Exception $exception) {
            $this->printLog("> An error occured while running script \"{$script["className"]}\"");
            $this->printLog("");
            $this->printLog($exception->__toString());
            $this->printLog("");

            return [
                "isSuccess" => false,
            ];
        }

        return [
            "isSuccess" => true,
        ];
    }

    private function instantiateScript($scriptEntry) {
        $scriptID = $scriptEntry["id"];
        $filename = $scriptEntry["filename"];

        $scriptClassFilePath = $this->fsHandler->getRealPath(
            $this->getScriptFilepath($filename)
        );

        require_once($scriptClassFilePath);

        $scriptClass = "Script_" . $scriptID;

        $reflectionClass = new \ReflectionClass($scriptClass);
        $scriptInterfaceName = "\UniEngine\Utils\OneOffs\Interfaces\Script";

        if (!($reflectionClass->implementsInterface($scriptInterfaceName))) {
            throw new \Exception(
                "Script \"{$scriptClass}\" (\"{$filename}\") " .
                "does not implement Script interface " .
                "({$scriptInterfaceName})"
            );
        }

        return [
            "id" => $scriptID,
            "className" => $reflectionClass->getName(),
            "instance" => $reflectionClass->newInstance()
        ];
    }

    private function printLog($line) {
        echo "{$line}\n";
    }
}

?>
