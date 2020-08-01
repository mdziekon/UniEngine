<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

include($_EnginePath.'includes/functions/InsertGalaxyScripts.php');
include($_EnginePath.'includes/functions/ShowGalaxyRows.php');
include($_EnginePath.'includes/functions/ShowGalaxySelector.php');
include($_EnginePath.'includes/functions/ShowGalaxyMISelector.php');
include($_EnginePath.'includes/functions/ShowGalaxyTitles.php');
include($_EnginePath.'includes/functions/ShowGalaxyFooter.php');
include($_EnginePath.'includes/functions/GalaxyRowExpedition.php');
include($_EnginePath.'includes/functions/GalaxyRowPos.php');
include($_EnginePath.'includes/functions/GalaxyRowPlanet.php');
include($_EnginePath.'includes/functions/GalaxyRowPlanetName.php');
include($_EnginePath.'includes/functions/GalaxyRowMoon.php');
include($_EnginePath.'includes/functions/GalaxyRowDebris.php');
include($_EnginePath.'includes/functions/GalaxyRowUser.php');
include($_EnginePath.'includes/functions/GalaxyRowAlly.php');
include($_EnginePath.'includes/functions/GalaxyRowActions.php');
include($_EnginePath.'includes/functions/GalaxyLegendPopup.php');
include($_EnginePath.'includes/functions/GetMissileRange.php');
include($_EnginePath.'includes/functions/GetPhalanxRange.php');

includeLang('galaxy');

$Time = time();
$CurrentPlanet = &$_Planet;

$fleetmax = $_User['tech_computer'] + 1 + (($_User['admiral_time'] > 0) ? 2 : 0);
$CurrentMIP = $CurrentPlanet['interplanetary_missile'];
$CurrentRC = $CurrentPlanet['recycler'];
$CurrentSP = $CurrentPlanet['espionage_probe'];
$CurrentCS = $CurrentPlanet['colony_ship'];
$SensonPhalanxLevel = $CurrentPlanet['sensor_phalanx'];
$CurrentSystem = $CurrentPlanet['system'];
$CurrentGalaxy = $CurrentPlanet['galaxy'];
$CanDestroy = ($CurrentPlanet[$_Vars_GameElements[214]] > 0 ? true : false);

$GetFlyingFleetsCount = doquery("SELECT COUNT(*) AS `Count` FROM {{table}} WHERE `fleet_owner` = {$_User['id']};", 'fleets', true);
$maxfleet_count = $GetFlyingFleetsCount['Count'];

// Get GalaxyShow Mode
$mode = intval($_GET['mode']);
if(!in_array($mode, array(0, 1, 2, 3)))
{
    $mode = 0;
}

if($mode === 0)
{
    // Show CurrentPlanet Solar System
    $galaxy = $CurrentPlanet['galaxy'];
    $system = $CurrentPlanet['system'];
    $planet = $CurrentPlanet['planet'];
}
else if($mode === 1)
{
    // User sent $_POST Data
    if(isset($_POST['galaxyLeft']) && $_POST['galaxyLeft'] === 'dr')
    {
        if($_POST['galaxy'] <= 1 OR $_POST['galaxy'] > MAX_GALAXY_IN_WORLD)
        {
            $_POST['galaxy']= MAX_GALAXY_IN_WORLD;
            $galaxy = MAX_GALAXY_IN_WORLD;
        }
        else
        {
            $galaxy = $_POST['galaxy'] - 1;
        }
    }
    else if(isset($_POST['galaxyRight']) && $_POST['galaxyRight'] === 'dr')
    {
        if($_POST['galaxy'] >= MAX_GALAXY_IN_WORLD OR $_POST['galaxy'] < 1)
        {
            $_POST['galaxy']= 1;
            $galaxy = 1;
        }
        else
        {
            $galaxy = $_POST['galaxy'] + 1;
        }
    }
    else
    {
        $_POST['galaxy'] = intval($_POST['galaxy']);
        if($_POST['galaxy'] > 0 AND $_POST['galaxy'] <= MAX_GALAXY_IN_WORLD)
        {
            $galaxy = $_POST['galaxy'];
        }
        else
        {
            $galaxy = 1;
        }
    }

    if(isset($_POST['systemLeft']) && $_POST['systemLeft'] === 'dr')
    {
        if($_POST['system'] <= 1 OR $_POST['system'] > MAX_SYSTEM_IN_GALAXY)
        {
            $_POST['system']= MAX_SYSTEM_IN_GALAXY;
            $system = MAX_SYSTEM_IN_GALAXY;
        }
        else
        {
            $system = $_POST['system'] - 1;
        }
    }
    else if(isset($_POST['systemRight']) && $_POST['systemRight'] === 'dr')
    {
        if($_POST['system'] >= MAX_SYSTEM_IN_GALAXY OR $_POST['system'] < 1)
        {
            $_POST['system']= 1;
            $system = 1;
        }
        else
        {
            $system = $_POST['system'] + 1;
        }
    }
    else
    {
        $_POST['system'] = intval($_POST['system']);
        if($_POST['system'] > 0 AND $_POST['system'] <= MAX_SYSTEM_IN_GALAXY)
        {
            $system = $_POST['system'];
        }
        else
        {
            $system = 1;
        }
    }
    $planet = 0;
}
else if($mode === 2 OR $mode === 3)
{
    // User sent $_GET Data
    $_GET['galaxy'] = isset($_GET['galaxy']) ? intval($_GET['galaxy']) : 0;
    $_GET['system'] = isset($_GET['system']) ? intval($_GET['system']) : 0;
    $_GET['planet'] = isset($_GET['planet']) ? intval($_GET['planet']) : 0;

    if($_GET['galaxy'] > 0 AND $_GET['galaxy'] <= MAX_GALAXY_IN_WORLD)
    {
        $galaxy = $_GET['galaxy'];
    }
    else
    {
        $galaxy = 1;
    }
    if($_GET['system'] > 0 AND $_GET['system'] <= MAX_SYSTEM_IN_GALAXY)
    {
        $system = $_GET['system'];
    }
    else
    {
        $system = 1;
    }
    if($_GET['planet'] > 0 AND $_GET['planet'] <= MAX_PLANET_IN_SYSTEM)
    {
        $planet = $_GET['planet'];
    }
    else
    {
        $planet = 0;
    }
}

$planetcount = 0;
$MoonCount = 0;

$GalaxyTPL = gettemplate('galaxy_body');

// Include Script
$galaxyScriptsData = [
    'SkinPath' => $_SkinPath,
    'maxGal' => MAX_GALAXY_IN_WORLD,
    'maxSys' => MAX_SYSTEM_IN_GALAXY,
    'UseAjax' => ($_User['settings_UseAJAXGalaxy'] == 1 ? 'true' : 'false'),
    'P_AllowPrettyInputBox' => ($_User['settings_useprettyinputbox'] == 1 ? 'true' : 'false')
];

$Parse['Input_GalaxyScripts'] = InsertGalaxyScripts($galaxyScriptsData);

$Parse['Input_GalaxySelector'] = ShowGalaxySelector($galaxy, $system);
$Parse['Input_GalaxyHeaders'] = ShowGalaxyTitles();
$Parse['Input_GalaxyRows'] = ShowGalaxyRows($galaxy, $system, $planet);
$Parse['Input_GalaxyFooter'] = ShowGalaxyFooter($galaxy, $system, $CurrentMIP, $CurrentRC, $CurrentSP, $CurrentCS);

if ($mode === 2 OR $_User['settings_UseAJAXGalaxy'] == 1) {
    $HideMISelector = ($_User['settings_UseAJAXGalaxy'] == 1);

    $Parse['Input_GalaxyMissileSelector'] = ShowGalaxyMISelector($galaxy, $system, $planet, $CurrentMIP, $HideMISelector);
}

$Page = parsetemplate($GalaxyTPL, $Parse);

display($Page, $_Lang['PageTitle'], false);

?>
