<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_DontForceRulesAcceptance = true;
$_UseMinimalCommon = true;

$_SetAccessLogPreFilename = 'ajax/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath.'common.php');

function ajaxReturn($Array)
{
    safeDie(json_encode($Array));
}

if(!isLogged())
{
    ajaxReturn(array('Err' => '601'));
}

include($_EnginePath.'includes/functions/ShowGalaxyRows.php');
include($_EnginePath.'includes/functions/GalaxyRowExpedition.php');
include($_EnginePath.'includes/functions/GalaxyRowPos.php');
include($_EnginePath.'includes/functions/GalaxyRowPlanet.php');
include($_EnginePath.'includes/functions/GalaxyRowPlanetName.php');
include($_EnginePath.'includes/functions/GalaxyRowMoon.php');
include($_EnginePath.'includes/functions/GalaxyRowDebris.php');
include($_EnginePath.'includes/functions/GalaxyRowUser.php');
include($_EnginePath.'includes/functions/GalaxyRowAlly.php');
include($_EnginePath.'includes/functions/GalaxyRowActions.php');
include($_EnginePath.'includes/functions/GetMissileRange.php');
include($_EnginePath.'includes/functions/GetPhalanxRange.php');

includeLang('galaxy');
$Time = time();
$planetcount = 0;

$_Planet = doquery("SELECT * FROM {{table}} WHERE `id` = {$_User['current_planet']};", 'planets', true);
if($_Planet['id'] <= 0)
{
    SetSelectedPlanet($_User, $_User['id_planet']);
    $_Planet = doquery("SELECT * FROM {{table}} WHERE `id` = {$_User['id_planet']};", 'planets', true);
    if($_Planet['id'] <= 0)
    {
        ajaxReturn(array('Err' => '697'));
    }
}

$SelectMsgs = doquery("SELECT COUNT(`id`) as `Count` FROM {{table}} WHERE `id_owner` = {$_User['id']} AND `deleted` = false AND `read` = false;", 'messages', true);

$GetFlyingFleetsCount = doquery("SELECT COUNT(*) AS `Count` FROM {{table}} WHERE `fleet_owner` = {$_User['id']};", 'fleets', true);

$CurrentMIP = $_Planet['interplanetary_missile'];
$CurrentRC = $_Planet['recycler'];
$CurrentSP = $_Planet['espionage_probe'];
$CurrentCS = $_Planet['colony_ship'];
$SensonPhalanxLevel = $_Planet['sensor_phalanx'];
$CurrentSystem = $_Planet['system'];
$CurrentGalaxy = $_Planet['galaxy'];
$CanDestroy = ($_Planet[$_Vars_GameElements[214]] > 0 ? true : false);

$Galaxy = intval($_GET['galaxy']);
$System = intval($_GET['system']);
if(!($Galaxy > 0 AND $Galaxy <= MAX_GALAXY_IN_WORLD))
{
    $Galaxy = 1;
}
if(!($System > 0 AND $System <= MAX_SYSTEM_IN_GALAXY))
{
    $System = 1;
}

$Response = ShowGalaxyRows($Galaxy, $System);

$planetcount = intval($planetcount);
$Msg_Count = floatval($SelectMsgs['Count']);
$FlyingFleets_Count = intval($GetFlyingFleetsCount['Count']);

ajaxReturn(array('PC' => $planetcount, 'G' => $Galaxy, 'S' => $System, 'msg' => $Msg_Count, 'fly' => $FlyingFleets_Count, 'Data' => $Response));

?>
