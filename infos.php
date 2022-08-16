<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath . 'common.php');
include_once($_EnginePath . 'modules/info/_includes.php');
include_once($_EnginePath . 'includes/functions/GetMissileRange.php');
include_once($_EnginePath . 'includes/functions/GetPhalanxRange.php');

use UniEngine\Engine\Modules\Info;

loggedCheck();

$BuildID = $_GET['gid'];

includeLang('infos');
includeLang('worldElements.detailed');

Info\Screens\ElementInfo\render([
    'elementId' => $BuildID,
    'user' => &$_User,
    'planet' => &$_Planet,
    'currentTimestamp' => time(),
]);

die();

?>
