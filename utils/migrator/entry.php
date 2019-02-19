<?php

require_once(__DIR__ . "/autoload.php");

use \UniEngine\Utils\Migrations as Migrations;

if (PHP_SAPI !== "cli") {
    throw new \Exception("Migrator utility can only be run from 'cli' environment");
}

$migrator = new Migrations\Migrator([
    "rootPath" => "./"
]);

$cmd = $argv[1];

switch ($cmd) {
    case "migrate:run":
        $migrator->runMigration([
            "wasManualActionConfirmed" => in_array(
                "--confirm-manual-action",
                $argv
            )
        ]);

        break;
    case "migrate:make":
        $migrator->generateNewMigration([
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
