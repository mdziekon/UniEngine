<?php

if(!defined('IN_INSTALL'))
{
    die();
}

require_once('../utils/migrator/autoload.php');

function generateRandomHash($Length)
{
    $Signs = '0123456789abcdefghijklmnoprstuwxyzABCDEFGHIJKLMNOPRSTUWXYZ_';
    $SignsLength = strlen($Signs) - 1;

    $Return = '';
    for($i = 0; $i < $Length; ++$i)
    {
        $Return .= $Signs[mt_rand(0, $SignsLength)];
    }
    return $Return;
}

function parseFile($filepath, $parseArray)
{
    return preg_replace_callback(
        '#\{([a-z0-9\-_]*?)\}#Ssi',
        function ($matches) use ($parseArray) {
            return (
                isset($parseArray[$matches[1]]) ?
                $parseArray[$matches[1]] :
                ""
            );
        },
        file_get_contents($filepath)
    );
}

function display()
{
    global $_Lang;

    echo parseFile('install_body.tpl', $_Lang);
}

function includeLang()
{
    global $_Lang, $_UseLang;

    include("./language/{$_UseLang}/install.lang");
}

function generateMigrationEntryFile()
{
    $migrator = new UniEngine\Utils\Migrations\Migrator([
        "rootPath" => "../"
    ]);

    $migrator->saveLastAppliedMigrationID(
        $migrator->getMostRecentMigrationID()
    );
}

?>
