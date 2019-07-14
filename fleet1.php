<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');

loggedCheck();

if((!isset($_POST['sending_fleet']) || $_POST['sending_fleet'] != '1') && (!isset($_POST['gobackUsed']) || $_POST['gobackUsed'] != '1'))
{
    header('Location: fleet.php');
    safeDie();
}

includeLang('fleet');

$Now = time();
$_Lang['Now'] = $Now;
$ErrorTitle = &$_Lang['fl_error'];
$Hide = ' class="hide"';

$FleetHiddenBlock = '';

$Fleet['count'] = 0;
$Fleet['storage'] = 0;
$Fleet['FuelStorage'] = 0;

if(MORALE_ENABLED)
{
    Morale_ReCalculate($_User, $Now);
}

if(isset($_POST['gobackUsed']))
{
    $_POST['quickres'] = $_POST['useQuickRes'];
    $_POST['target_mission'] = (isset($_POST['mission']) ? $_POST['mission'] : 0);
    $_POST['getacsdata'] = (isset($_POST['acs_id']) ? $_POST['acs_id'] : 0);

    $_Set_DefaultSpeed = $_POST['speed'];
    if(!empty($_POST['FleetArray']))
    {
        $PostFleet = explode(';', $_POST['FleetArray']);
        foreach($PostFleet as $Data)
        {
            if(!empty($Data))
            {
                $Data = explode(',', $Data);
                if(in_array($Data[0], $_Vars_ElementCategories['fleet']))
                {
                    $_POST['ship'][$Data[0]] = $Data[1];
                }
            }
        }
    }
    $GoBackVars = array
    (
        'resource1' => $_POST['resource1'],
        'resource2' => $_POST['resource2'],
        'resource3' => $_POST['resource3'],
        'usequantumgate' => (isset($_POST['usequantumgate']) ? $_POST['usequantumgate'] : null),
        'expeditiontime' => (isset($_POST['expeditiontime']) ? $_POST['expeditiontime'] : null),
        'holdingtime' => (isset($_POST['holdingtime']) ? $_POST['holdingtime'] : null)
    );
}
if(!empty($_POST['gobackVars']))
{
    $_Lang['P_GoBackVars'] = json_decode(base64_decode($_POST['gobackVars']), true);
    if((array)$_Lang['P_GoBackVars'] === $_Lang['P_GoBackVars'])
    {
        if(!empty($GoBackVars))
        {
            $GoBackVars = array_merge($GoBackVars, $_Lang['P_GoBackVars']);
        }
        else
        {
            $GoBackVars = $_Lang['P_GoBackVars'];
        }
    }
}
if(!empty($GoBackVars))
{
    $_Lang['P_GoBackVars'] = base64_encode(json_encode($GoBackVars));
}

if(!empty($_POST['gobackVars']))
{
    $_POST['gobackVars'] = json_decode(base64_decode($_POST['gobackVars']), true);
    $_Set_DefaultSpeed = $_POST['gobackVars']['speed'];
}

// Management of ShipsList
if(!empty($_POST['ship']))
{
    foreach($_POST['ship'] as $ShipID => $ShipCount)
    {
        $ShipID = intval($ShipID);
        if(in_array($ShipID, $_Vars_ElementCategories['fleet']))
        {
            if(!empty($_Vars_Prices[$ShipID]['engine']))
            {
                $ShipCount = floor(str_replace('.', '', $ShipCount));
                if($ShipCount > 0)
                {
                    if($_Planet[$_Vars_GameElements[$ShipID]] >= $ShipCount)
                    {
                        $Fleet['array'][$ShipID] = $ShipCount;
                        $Fleet['count'] += $ShipCount;
                        $ThisStorage = $_Vars_Prices[$ShipID]['capacity'] * $ShipCount;
                        if($ShipID != 210)
                        {
                            $Fleet['storage'] += $ThisStorage;
                        }
                        else
                        {
                            $Fleet['FuelStorage'] += $ThisStorage;
                        }
                        $speedalls[$ShipID] = getShipsCurrentSpeed($ShipID, $_User);
                        $shipConsumption = getShipsCurrentConsumption($ShipID, $_User);
                        $allShipsConsumption = ($shipConsumption * $ShipCount);

                        // TODO: Check if that "+1" is correct
                        $FleetHiddenBlock .= "<input type=\"hidden\" id=\"consumption{$ShipID}\" value=\"".((string)($allShipsConsumption + 1))."\" />";
                        $FleetHiddenBlock .= "<input type=\"hidden\" id=\"speed{$ShipID}\" value=\"{$speedalls[$ShipID]}\" />";
                    }
                    else
                    {
                        message($_Lang['fl1_NoEnoughShips'], $ErrorTitle, 'fleet.php', 3);
                    }
                }
            }
            else
            {
                message($_Lang['fl1_CantSendUnflyable'], $ErrorTitle, 'fleet.php', 3);
            }
        }
        else
        {
            message($_Lang['fl1_BadShipGiven'], $ErrorTitle, 'fleet.php', 3);
        }
    }
}

if($Fleet['count'] <= 0)
{
    message($_Lang['fl1_NoShipsGiven'], $ErrorTitle, 'fleet.php', 3);
}
$speedallsmin = min($speedalls);

// Create SpeedsArray
$SpeedsAvailable = array
(
    10 => 100,
    9 => 90,
    8 => 80,
    7 => 70,
    6 => 60,
    5 => 50,
    4 => 40,
    3 => 30,
    2 => 20,
    1 => 10
);

if($_User['admiral_time'] > $Now)
{
    $SpeedsAvailable[12] = 120;
    $SpeedsAvailable[11] = 110;
    $SpeedsAvailable['0.5'] = 5;
    $SpeedsAvailable['0.25'] = 2.5;
}
if(MORALE_ENABLED)
{
    $MaxAvailableSpeed = max($SpeedsAvailable);
    if($_User['morale_level'] >= MORALE_BONUS_FLEETSPEEDUP1)
    {
        $SpeedsAvailable[(string)(($MaxAvailableSpeed + MORALE_BONUS_FLEETSPEEDUP1_VALUE) / 10)] = $MaxAvailableSpeed + MORALE_BONUS_FLEETSPEEDUP1_VALUE;
    }
    if($_User['morale_level'] >= MORALE_BONUS_FLEETSPEEDUP2)
    {
        $SpeedsAvailable[(string)(($MaxAvailableSpeed + MORALE_BONUS_FLEETSPEEDUP2_VALUE) / 10)] = $MaxAvailableSpeed + MORALE_BONUS_FLEETSPEEDUP2_VALUE;
    }

    if($_User['morale_level'] <= MORALE_PENALTY_FLEETSLOWDOWN)
    {
        $speedallsmin *= MORALE_PENALTY_FLEETSLOWDOWN_VALUE;
    }
}
arsort($SpeedsAvailable);

$_Lang['P_HideACSJoining'] = $Hide;
$GetACSData = intval($_POST['getacsdata']);
$SetPosNotEmpty = false;
if($GetACSData > 0)
{
    $ACSData = doquery("SELECT `id`, `name`, `end_galaxy`, `end_system`, `end_planet`, `end_type`, `start_time` FROM {{table}} WHERE `id` = {$GetACSData};", 'acs', true);
    if($ACSData['id'] == $GetACSData)
    {
        if($ACSData['start_time'] > $Now)
        {
            $SetPos['g'] = $ACSData['end_galaxy'];
            $SetPos['s'] = $ACSData['end_system'];
            $SetPos['p'] = $ACSData['end_planet'];
            $SetPos['t'] = $ACSData['end_type'];

            $SetPosNotEmpty = true;
            $_Lang['P_HideACSJoining'] = '';
            $_Lang['fl1_ACSJoiningFleet'] = sprintf($_Lang['fl1_ACSJoiningFleet'], $ACSData['name'], $ACSData['end_galaxy'], $ACSData['end_system'], $ACSData['end_planet']);
            $_Lang['P_DisableCoordSel'] = 'disabled';
            $_Lang['SelectedACSID'] = $GetACSData;
        }
        else
        {
            message($_Lang['fl1_ACSTimeUp'], $ErrorTitle, 'fleet.php', 3);
        }
    }
    else
    {
        message($_Lang['fl1_ACSNoExist'], $ErrorTitle, 'fleet.php', 3);
    }
}

if($SetPosNotEmpty !== true)
{
    $SetPos['g'] = intval($_POST['galaxy']);
    $SetPos['s'] = intval($_POST['system']);
    $SetPos['p'] = intval($_POST['planet']);
    $SetPos['t'] = (isset($_POST['planet_type']) ? intval($_POST['planet_type']) : 0);
    if(!in_array($SetPos['t'], array(1, 2, 3)) && isset($_POST['planettype']))
    {
        $SetPos['t'] = intval($_POST['planettype']);
    }

    if($SetPos['g'] < 1 OR $SetPos['g'] > MAX_GALAXY_IN_WORLD)
    {
        $SetPos['g'] = $_Planet['galaxy'];
    }
    if($SetPos['s'] < 1 OR $SetPos['s'] > MAX_SYSTEM_IN_GALAXY)
    {
        $SetPos['s'] = $_Planet['system'];
    }
    if($SetPos['p'] < 1 OR $SetPos['p'] > MAX_PLANET_IN_SYSTEM)
    {
        $SetPos['p'] = $_Planet['planet'];
    }
    if(!in_array($SetPos['t'], array(1, 2, 3)))
    {
        $SetPos['t'] = $_Planet['planet_type'];
    }

    $_Lang['SetTargetMission'] = $_POST['target_mission'];
}
else
{
    $_Lang['SetTargetMission'] = 2;
}

// Fleet Blockade Info (here, only for Global Block)
$GetSFBData = doquery("SELECT `ID`, `EndTime`, `BlockMissions`, `DontBlockIfIdle`, `Reason` FROM {{table}} WHERE `Type` = 1 AND `StartTime` <= UNIX_TIMESTAMP() AND (`EndTime` > UNIX_TIMESTAMP() OR `PostEndTime` > UNIX_TIMESTAMP()) ORDER BY `EndTime` DESC LIMIT 1;", 'smart_fleet_blockade', true);
if($GetSFBData['ID'] > 0)
{
    // Fleet Blockade is Active
    include($_EnginePath.'includes/functions/CreateSFBInfobox.php');
    $_Lang['P_SFBInfobox'] = CreateSFBInfobox($GetSFBData, array('standAlone' => true, 'Width' => 750, 'MarginBottom' => 10));
}

$_Lang['FleetHiddenBlock'] = $FleetHiddenBlock;
$_Lang['speedallsmin'] = $speedallsmin;
$_Lang['MaxSpeedPretty'] = prettyNumber($speedallsmin);
$_Lang['Storage'] = (string)($Fleet['storage'] + 0);
$_Lang['FuelStorage'] = (string)($Fleet['FuelStorage'] + 0);
$_Lang['ThisGalaxy'] = $_Planet['galaxy'];
$_Lang['ThisSystem'] = $_Planet['system'];
$_Lang['ThisPlanet'] = $_Planet['planet'];
$_Lang['GalaxyEnd'] = intval($_POST['galaxy']);
$_Lang['SystemEnd'] = intval($_POST['system']);
$_Lang['PlanetEnd'] = intval($_POST['planet']);
$_Lang['SpeedFactor'] = getUniFleetsSpeedFactor();
$_Lang['ThisPlanetType'] = $_Planet['planet_type'];
$_Lang['ThisResource3'] = (string)(floor($_Planet['deuterium']) + 0);
foreach($Fleet['array'] as $ID => $Count)
{
    $_Lang['FleetArray'][] = $ID.','.$Count;
}
$_Lang['FleetArray'] = implode(';', $_Lang['FleetArray']);
if($_POST['quickres'] == '1')
{
    $_Lang['P_SetQuickRes'] = '1';
}
else
{
    $_Lang['P_SetQuickRes'] = '0';
}

$_Lang['P_MaxGalaxy'] = MAX_GALAXY_IN_WORLD;
$_Lang['P_MaxSystem'] = MAX_SYSTEM_IN_GALAXY;
$_Lang['P_MaxPlanet'] = MAX_PLANET_IN_SYSTEM + 1;

foreach($SetPos as $Key => $Value)
{
    if($Key == 't')
    {
        $_Lang['SetPos_Type'.$Value.'Selected'] = 'selected';
        continue;
    }
    $_Lang['SetPos_'.$Key] = $Value;
}

if(empty($_Set_DefaultSpeed) OR !in_array($_Set_DefaultSpeed, array_keys($SpeedsAvailable)))
{
    $_Set_DefaultSpeed = max(array_keys($SpeedsAvailable));
}
$_Lang['Insert_SpeedInput'] = $_Set_DefaultSpeed;

foreach($SpeedsAvailable as $Selector => $Text)
{
    $_Lang['Insert_Speeds'][] = "<a href=\"#\" class=\"setSpeed ".(($_Set_DefaultSpeed == $Selector) ? 'setSpeed_Selected setSpeed_Current' : '')."\" data-speed=\"{$Selector}\">{$Text}</a>";
}
$_Lang['Insert_Speeds'] = implode('<span class="speedBreak">|</span>', $_Lang['Insert_Speeds']);

// Create Colony List and Shortcuts List (dropdown)
$OtherPlanets = SortUserPlanets($_User);
$Shortcuts = doquery("SELECT {{table}}.*, IF(`planets`.`id` > 0, `planets`.`name`, '') AS `name`, IF(`planets`.`id` > 0, `planets`.`galaxy`, {{table}}.galaxy) AS `galaxy`, IF(`planets`.`id` > 0, `planets`.`system`, {{table}}.system) AS `system`, IF(`planets`.`id` > 0, `planets`.`planet`, {{table}}.planet) AS `planet`, IF(`planets`.`id` > 0, `planets`.`planet_type`, {{table}}.type) AS `planet_type` FROM {{table}} LEFT JOIN {{prefix}}planets as `planets` ON `planets`.`id` = {{table}}.`id_planet` WHERE {{table}}.`id_owner` = {$_User['id']} ORDER BY {{table}}.id ASC;", 'fleet_shortcuts');

if($OtherPlanets->num_rows > 1)
{
    while($PlanetData = $OtherPlanets->fetch_assoc())
    {
        if($PlanetData['galaxy'] == $_Planet['galaxy'] AND $PlanetData['system'] == $_Planet['system'] AND $PlanetData['planet'] == $_Planet['planet'] AND $PlanetData['planet_type'] == $_Planet['planet_type'])
        {
            // Do nothing, we don't want current planet on this list
        }
        else
        {
            $OtherPlanetsList[] = array
            (
                'txt' => $PlanetData['name'].' '.(($PlanetData['planet_type'] == 3) ? '('.$_Lang['DropdownList_Moon_sign'].') ' : '')."[{$PlanetData['galaxy']}:{$PlanetData['system']}:{$PlanetData['planet']}]",
                'js' => "{$PlanetData['galaxy']},{$PlanetData['system']},{$PlanetData['planet']},{$PlanetData['planet_type']}"
            );
        }
    }
}

if($Shortcuts->num_rows > 0)
{
    while($Data = $Shortcuts->fetch_assoc())
    {
        $ShortcutList[] = array
        (
            'txt' => ((!empty($Data['own_name']) ? $Data['own_name'].' - ' : '')).$Data['name'].(($Data['planet_type'] == 3) ? ' ('.$_Lang['moon_sign'].')' : (($Data['planet_type'] == 2) ? ' ('.$_Lang['debris_sign'].')' : ''))." [{$Data['galaxy']}:{$Data['system']}:{$Data['planet']}]",
            'js' => "{$Data['galaxy']},{$Data['system']},{$Data['planet']},{$Data['planet_type']}"
        );
    }
}

$_Lang['P_HideFastLinks'] = $Hide;
$_Lang['P_HideNoFastLinks'] = $Hide;

if(!empty($OtherPlanetsList) OR !empty($ShortcutList))
{
    $_Lang['P_HideFastLinks'] = '';

    if(!empty($OtherPlanetsList))
    {
        $_Lang['FastLinks_Planets'] = '<select class="updateInfo fastLink" id="fl_sel1" '.(isset($_Lang['P_DisableCoordSel']) ? $_Lang['P_DisableCoordSel'] : 0).'>';
        $_Lang['FastLinks_Planets'] .= '<option value="-">- '.$_Lang['fl_dropdown_select'].' -</option>';
        foreach($OtherPlanetsList as $PlanetData)
        {
            $_Lang['FastLinks_Planets'] .= '<option value="'.$PlanetData['js'].'">'.$PlanetData['txt'].'</option>';
        }
        $_Lang['FastLinks_Planets'] .= '</select>';
    }
    else
    {
        $_Lang['FastLinks_Planets'] = $_Lang['fl_no_planets'];
    }

    if(!empty($ShortcutList))
    {
        $_Lang['FastLinks_ShortCuts'] = '<select class="updateInfo fastLink" id="fl_sel2" '.(isset($_Lang['P_DisableCoordSel']) ? $_Lang['P_DisableCoordSel'] : 0).'>';
        $_Lang['FastLinks_ShortCuts'] .= '<option value="-">- '.$_Lang['fl_dropdown_select'].' -</option>';
        foreach($ShortcutList as $PlanetData)
        {
            $_Lang['FastLinks_ShortCuts'] .= '<option value="'.$PlanetData['js'].'">'.$PlanetData['txt'].'</option>';
        }
        $_Lang['FastLinks_ShortCuts'] .= '</select>';
    }
    else
    {
        $_Lang['FastLinks_ShortCuts'] = $_Lang['fl_no_shortcuts'];
    }
}
else
{
    $_Lang['P_HideNoFastLinks'] = '';
}

$Page = parsetemplate(gettemplate('fleet1_body'), $_Lang);
display($Page, $_Lang['fl_title']);

?>
