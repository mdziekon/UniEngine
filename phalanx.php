<?php

define('INSIDE', true);

$_DontShowMenus = true;
$_DontShowRulesBox = true;
$_DontCheckPolls = true;
$_BlockFleetHandler = true;

$_EnginePath = './';
include($_EnginePath.'common.php');
include($_EnginePath.'modules/flights/_includes.php');
include($_EnginePath.'modules/phalanx/_includes.php');
include($_EnginePath.'includes/functions/GetPhalanxRange.php');

use UniEngine\Engine\Modules\Phalanx;

loggedCheck();

Phalanx\Screens\PlanetScan\render([
    'input' => &$_GET,
    'currentUser' => &$_User,
    'currentPlanet' => &$_Planet,
    'currentTimestamp' => time(),
]);

?>
