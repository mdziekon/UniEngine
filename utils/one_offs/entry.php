<?php

require_once(__DIR__ . "/autoload.php");

use \UniEngine\Utils\OneOffs as OneOffs;

if (PHP_SAPI !== "cli") {
    throw new \Exception("OneOffs utility can only be run from 'cli' environment");
}

$rootPath = "./";

$cmd = $argv[1];

switch ($cmd) {
    case "oneoffs:run":
        $runner = new OneOffs\ScriptRunner([
            "rootPath" => "./"
        ]);

        $runner->runScript([
            "id" => (
                !empty($argv[2]) ?
                $argv[2] :
                null
            )
        ]);

        break;
    case "oneoffs:make":
        $generator = new OneOffs\ScriptsGenerator([
            "rootPath" => $rootPath,
        ]);

        $generator->generateNewScript([
            "name" => (
                !empty($argv[2]) ?
                $argv[2] :
                null
            )
        ]);

        break;
    default:
        throw new \Exception("Invalid command \"{$cmd}\"");
}

?>
