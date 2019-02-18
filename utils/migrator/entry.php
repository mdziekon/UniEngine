<?php

require_once(__DIR__ . "/autoload.php");

use \UniEngine\Utils\Migrations as Migrations;

if (PHP_SAPI !== "cli") {
    throw new Exception("Migrator utility can only be run from 'cli' environment");
}

$migrator = new Migrations\Migrator([
    "rootPath" => "./"
]);

$migrator->runMigration([
    "wasManualActionConfirmed" => in_array("--confirm-manual-action", $argv)
]);

?>
