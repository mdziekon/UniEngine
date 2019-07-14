<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');

loggedCheck();

if($_POST['sending_fleet'] != '1')
{
    header('Location: fleet.php'.($_GET['quickres'] == 1 ? '?quickres=1' : ''));
    safeDie();
}

function messageRed($Text, $Title)
{
    global $_POST, $_Lang;
    $_POST = base64_encode(json_encode($_POST));
    $GoBackForm = '';
    $GoBackForm .= '<form action="fleet2.php" method="post"><input type="hidden" name="fromEnd" value="1"/>';
    $GoBackForm .= '<input type="hidden" name="gobackVars" value="'.$_POST.'"/>';
    $GoBackForm .= '<input class="orange pad5" style="font-weight: bold;" type="submit" value="&laquo; '.$_Lang['fl_goback'].'"/>';
    $GoBackForm .= '</form>';
    message("<br/><b class=\"red\">{$Text}</b><br/>{$GoBackForm}", $Title);
}

includeLang('fleet');

$QuantumGateInterval = QUANTUMGATE_INTERVAL_HOURS;
$Now = time();
$ErrorTitle = &$_Lang['fl_error'];

if(MORALE_ENABLED)
{
    Morale_ReCalculate($_User, $Now);
}

// --- Initialize Vars
$Target['galaxy'] = intval($_POST['galaxy']);
$Target['system'] = intval($_POST['system']);
$Target['planet'] = intval($_POST['planet']);
$Target['type'] = intval($_POST['planettype']);
$Fleet['Speed'] = floatval($_POST['speed']);
$Fleet['array'] = explode(';', $_POST['FleetArray']);
$Fleet['UseQuantum'] = (isset($_POST['usequantumgate']) && $_POST['usequantumgate'] == 'on' ? true : false);
$Fleet['resources'] = array('metal' => $_POST['resource1'], 'crystal' => $_POST['resource2'], 'deuterium' => $_POST['resource3']);
$Fleet['ExpeTime'] = intval($_POST['expeditiontime']);
$Fleet['HoldTime'] = intval($_POST['holdingtime']);
$Fleet['ACS_ID'] = isset($_POST['acs_id']) ? floor(floatval($_POST['acs_id'])) : 0;
$Fleet['Mission'] = isset($_POST['mission']) ? intval($_POST['mission']) : 0;

$Protections['enable'] = (bool) $_GameConfig['noobprotection'];
$Protections['basicLimit'] = $_GameConfig['noobprotectiontime'] * 1000;
$Protections['weakMulti'] = $_GameConfig['noobprotectionmulti'];
$Protections['adminEnable'] = (bool) $_GameConfig['adminprotection'];
$Protections['ally'] = $_GameConfig['allyprotection'];
$Protections['weakLimit'] = $_GameConfig['no_noob_protect'] * 1000;
$Protections['idleTime'] = $_GameConfig['no_idle_protect'] * TIME_DAY;
$Protections['mtypes'] = array(1, 2, 6, 9);
$Protections['newTime'] = $_GameConfig['Protection_NewPlayerTime'];
$Protections['antifarm_enabled'] = (bool) $_GameConfig['Protection_AntiFarmEnabled'];
$Protections['antifarm_rate'] = $_GameConfig['Protection_AntiFarmRate'];
$Protections['antifarm_counttotal'] = $_GameConfig['Protection_AntiFarmCountTotal'];
$Protections['antifarm_countplanet'] = $_GameConfig['Protection_AntiFarmCountPlanet'];
$Protections['bashLimit_enabled'] = (bool) $_GameConfig['Protection_BashLimitEnabled'];
$Protections['bashLimit_interval'] = $_GameConfig['Protection_BashLimitInterval'];
$Protections['bashLimit_counttotal'] = $_GameConfig['Protection_BashLimitCountTotal'];
$Protections['bashLimit_countplanet'] = $_GameConfig['Protection_BashLimitCountPlanet'];

// --- Check if User's account is activated
if(!empty($_User['activation_code']))
{
    messageRed($_Lang['fl3_BlockAccNotActivated'], $ErrorTitle);
}

// --- Check if Mission is selected
if($Fleet['Mission'] <= 0)
{
    messageRed($_Lang['fl3_NoMissionSelected'], $ErrorTitle);
}

// --- Get FlyingFleets Count
$FlyingFleetsCount = 0;
$FlyingExpeditions = 0;

$Query_GetFleets = '';
$Query_GetFleets .= "SELECT `fleet_mission`, `fleet_target_owner`, `fleet_end_id`, `fleet_mess` FROM {{table}} ";
$Query_GetFleets .= "WHERE `fleet_owner` = {$_User['id']};";
$Result_GetFleets = doquery($Query_GetFleets, 'fleets');
while($FleetData = $Result_GetFleets->fetch_assoc())
{
    $FlyingFleetsCount += 1;
    if($FleetData['fleet_mission'] == 15)
    {
        $FlyingExpeditions += 1;
    }
    if(in_array($FleetData['fleet_mission'], array(1, 2, 9)) AND $FleetData['fleet_mess'] == 0)
    {
        if(!isset($FlyingFleetsData[$FleetData['fleet_target_owner']]['count']))
        {
            $FlyingFleetsData[$FleetData['fleet_target_owner']]['count'] = 0;
        }
        if(!isset($FlyingFleetsData[$FleetData['fleet_target_owner']][$FleetData['fleet_end_id']]))
        {
            $FlyingFleetsData[$FleetData['fleet_target_owner']][$FleetData['fleet_end_id']] = 0;
        }
        $FlyingFleetsData[$FleetData['fleet_target_owner']]['count'] += 1;
        $FlyingFleetsData[$FleetData['fleet_target_owner']][$FleetData['fleet_end_id']] += 1;
    }
}

// Get Available Slots for Fleets (1 + ComputerTech + 2 on Admiral)
// Get Available Slots for Expeditions (1 + floor(ExpeditionTech / 3))
$Slots['MaxFleetSlots'] = 1 + $_User[$_Vars_GameElements[108]] + (($_User['admiral_time'] > $Now) ? 2 : 0);
$Slots['MaxExpedSlots'] = 1 + floor($_User[$_Vars_GameElements[124]] / 3);
$Slots['FlyingFleetsCount'] = $FlyingFleetsCount;
$Slots['FlyingExpeditions'] = $FlyingExpeditions;
if($Slots['FlyingFleetsCount'] >= $Slots['MaxFleetSlots'])
{
    messageRed($_Lang['fl3_NoMoreFreeSlots'], $ErrorTitle);
}
if($Slots['FlyingExpeditions'] >= $Slots['MaxExpedSlots'] AND $Fleet['Mission'] == 15)
{
    messageRed($_Lang['fl3_NoMoreFreeExpedSlots'], $ErrorTitle);
}

// --- Switch Off Expeditions
if($Fleet['Mission'] == 15)
{
    messageRed($_Lang['fl3_ExpeditionsAreOff'], $ErrorTitle);
}

// --- Check if all resources are correct (no negative numbers and enough on planet)
foreach($Fleet['resources'] as $Key => $Data)
{
    $Fleet['resources'][$Key] = floor(floatval(str_replace('.', '', $Data)));
    if($Fleet['resources'][$Key] < 0)
    {
        messageRed($_Lang['fl3_BadResourcesGiven'], $ErrorTitle);
    }
    elseif($Fleet['resources'][$Key] > $_Planet[$Key])
    {
        messageRed($_Lang['fl3_PlanetNoEnough'.$Key], $ErrorTitle);
    }

    if($Fleet['resources'][$Key] == 0)
    {
        $Fleet['resources'][$Key] = '0';
    }
}

// --- Check, if Target Data are correct
if($Target['galaxy'] == $_Planet['galaxy'] AND $Target['system'] == $_Planet['system'] AND $Target['planet'] == $_Planet['planet'] AND $Target['type'] == $_Planet['planet_type'])
{
    messageRed($_Lang['fl2_cantsendsamecoords'], $ErrorTitle);
}
foreach($Target as $Type => $Value)
{
    if($Value < 1)
    {
        $TargetError = true;
        break;
    }
    switch($Type)
    {
        case 'galaxy':
            $CheckValue = MAX_GALAXY_IN_WORLD;
            break;
        case 'system':
            $CheckValue = MAX_SYSTEM_IN_GALAXY;
            break;
        case 'planet':
            $CheckValue = MAX_PLANET_IN_SYSTEM + 1;
            break;
        case 'type':
            $CheckValue = 3;
            break;
    }
    if($Value > $CheckValue)
    {
        $TargetError = true;
        break;
    }
}
if(isset($TargetError))
{
    messageRed($_Lang['fl2_targeterror'], $ErrorTitle);
}

// Create SpeedsArray
$SpeedsAvailable = array(10, 9, 8, 7, 6, 5, 4, 3, 2, 1);

if($_User['admiral_time'] > $Now)
{
    $SpeedsAvailable[] = 12;
    $SpeedsAvailable[] = 11;
    $SpeedsAvailable[] = 0.5;
    $SpeedsAvailable[] = 0.25;
}
if(MORALE_ENABLED)
{
    $MaxAvailableSpeed = max($SpeedsAvailable);
    if($_User['morale_level'] >= MORALE_BONUS_FLEETSPEEDUP1)
    {
        $SpeedsAvailable[] = $MaxAvailableSpeed + (MORALE_BONUS_FLEETSPEEDUP1_VALUE / 10);
    }
    if($_User['morale_level'] >= MORALE_BONUS_FLEETSPEEDUP2)
    {
        $SpeedsAvailable[] = $MaxAvailableSpeed + (MORALE_BONUS_FLEETSPEEDUP2_VALUE / 10);
    }
}
if(!in_array($Fleet['Speed'], $SpeedsAvailable))
{
    messageRed($_Lang['fl_bad_fleet_speed'], $ErrorTitle);
}

// --- Check PlanetOwner
$YourPlanet                    = false;
$UsedPlanet                    = false;
$OwnerFriend                = false;
$OwnerIsBuddyFriend            = false;
$OwnerIsAlliedUser            = false;
$OwnerHasMarcantilePact        = false;
$PlanetAbandoned            = false;

if($Fleet['Mission'] != 8)
{
    // This is not a Recycling Mission, so check Planet Data
    $Query_CheckPlanetOwner = '';
    $Query_CheckPlanetOwner .= "SELECT `pl`.`id` AS `id`, `pl`.`id_owner` AS `owner`, `pl`.`name` AS `name`, `pl`.`quantumgate`, ";
    $Query_CheckPlanetOwner .= "`users`.`ally_id`, `users`.`onlinetime`, `users`.`username` as `username`, `users`.`user_lastip` as `lastip`, `users`.`is_onvacation`, `users`.`is_banned`, `users`.`authlevel`, `users`.`first_login`, `users`.`NoobProtection_EndTime`, `users`.`multiIP_DeclarationID`, ";
    $Query_CheckPlanetOwner .= "`stats`.`total_rank`, `stats`.`total_points`, `buddy1`.`active` AS `active1`, `buddy2`.`active` AS `active2` ";
    if($_User['ally_id'] > 0)
    {
        $Query_CheckPlanetOwner .= ", `apact1`.`Type` AS `AllyPact1`, `apact2`.`Type` AS `AllyPact2` ";
    }
    $Query_CheckPlanetOwner .= "FROM {{table}} as `pl` ";
    $Query_CheckPlanetOwner .= "LEFT JOIN {{prefix}}buddy as `buddy1` ON (`pl`.`id_owner` = `buddy1`.`sender` AND `buddy1`.`owner` = {$_User['id']}) ";
    $Query_CheckPlanetOwner .= "LEFT JOIN {{prefix}}buddy as `buddy2` ON (`pl`.`id_owner` = `buddy2`.`owner` AND `buddy2`.`sender` = {$_User['id']}) ";
    $Query_CheckPlanetOwner .= "LEFT JOIN {{prefix}}users as `users` ON `pl`.`id_owner` = `users`.`id` ";
    $Query_CheckPlanetOwner .= "LEFT JOIN {{prefix}}statpoints AS `stats` ON `pl`.`id_owner` = `stats`.`id_owner` AND `stat_type` = '1' ";
    if($_User['ally_id'] > 0)
    {
        $Query_CheckPlanetOwner .= "LEFT JOIN `{{prefix}}ally_pacts` AS `apact1` ON (`apact1`.`AllyID_Sender` = {$_User['ally_id']} AND `apact1`.`AllyID_Owner` = `users`.`ally_id` AND `apact1`.`Active` = 1) ";
        $Query_CheckPlanetOwner .= "LEFT JOIN `{{prefix}}ally_pacts` AS `apact2` ON (`apact2`.`AllyID_Sender` = `users`.`ally_id` AND `apact2`.`AllyID_Owner` = {$_User['ally_id']} AND `apact2`.`Active` = 1) ";
    }
    $Query_CheckPlanetOwner .= "WHERE `pl`.`galaxy` = {$Target['galaxy']} AND `pl`.`system` = {$Target['system']} AND `pl`.`planet` = {$Target['planet']} AND `pl`.`planet_type` = {$Target['type']} ";
    $Query_CheckPlanetOwner .= "LIMIT 1;";

    $SQLResult_GetPlanetData = doquery($Query_CheckPlanetOwner, 'planets');

    if($SQLResult_GetPlanetData->num_rows == 1)
    {
        $CheckGalaxyRow = doquery(
            "SELECT `galaxy_id` FROM {{table}} WHERE `galaxy` = {$Target['galaxy']} AND `system` = {$Target['system']} AND `planet` = {$Target['planet']} LIMIT 1;", 'galaxy',
            true
        );

        $CheckPlanetOwner = $SQLResult_GetPlanetData->fetch_assoc();

        $CheckPlanetOwner['galaxy_id'] = $CheckGalaxyRow['galaxy_id'];
        $UsedPlanet = true;
        if($CheckPlanetOwner['owner'] > 0)
        {
            if($CheckPlanetOwner['owner'] == $_User['id'])
            {
                $YourPlanet = true;
            }
            else
            {
                if((isset($CheckPlanetOwner['AllyPact1']) && $CheckPlanetOwner['AllyPact1'] >= ALLYPACT_NONAGGRESSION) || (isset($CheckPlanetOwner['AllyPact2']) && $CheckPlanetOwner['AllyPact2'] >= ALLYPACT_NONAGGRESSION))
                {
                    $OwnerIsAlliedUser = true;
                }
                if((isset($CheckPlanetOwner['AllyPact1']) && $CheckPlanetOwner['AllyPact1'] >= ALLYPACT_MERCANTILE) || (isset($CheckPlanetOwner['AllyPact2']) && $CheckPlanetOwner['AllyPact2'] >= ALLYPACT_MERCANTILE))
                {
                    $OwnerHasMarcantilePact = true;
                }
                if(($CheckPlanetOwner['active1'] == 1 OR $CheckPlanetOwner['active2'] == 1) OR ($CheckPlanetOwner['ally_id'] == $_User['ally_id'] AND $_User['ally_id'] > 0) OR ((isset($CheckPlanetOwner['AllyPact1']) && $CheckPlanetOwner['AllyPact1'] >= ALLYPACT_DEFENSIVE) || (isset($CheckPlanetOwner['AllyPact2']) && $CheckPlanetOwner['AllyPact2'] >= ALLYPACT_DEFENSIVE)))
                {
                    $OwnerFriend = true;
                    if($CheckPlanetOwner['active1'] == 1 OR $CheckPlanetOwner['active2'] == 1)
                    {
                        $OwnerIsBuddyFriend = true;
                    }
                }
            }
        }
        else
        {
            $PlanetAbandoned = true;
        }
    }
    else
    {
        $CheckPlanetOwner = array();
    }
}
else
{
    // This is a Recycling Mission, so check Galaxy Data
    $CheckDebrisField = doquery("SELECT `galaxy_id`, `metal`, `crystal` FROM {{table}} WHERE galaxy = '{$Target['galaxy']}' AND system = '{$Target['system']}' AND planet = '{$Target['planet']}'", 'galaxy', true);
}

// Fleet Blockade System
$SFBSelectWhere[] = "(`Type` = 1 AND (`EndTime` > UNIX_TIMESTAMP() OR `PostEndTime` > UNIX_TIMESTAMP()))";
if($CheckPlanetOwner['owner'] > 0)
{
    $SFBSelectWhere[] = "(`Type` = 2 AND `ElementID` = {$CheckPlanetOwner['owner']} AND `EndTime` > UNIX_TIMESTAMP())";
    $SFBSelectWhere[] = "(`Type` = 3 AND `ElementID` = {$CheckPlanetOwner['id']} AND `EndTime` > UNIX_TIMESTAMP())";
}
$SFBSelectWhere[] = "(`Type` = 2 AND `ElementID` = {$_User['id']} AND `EndTime` > UNIX_TIMESTAMP())";
$SFBSelectWhere[] = "(`Type` = 3 AND `ElementID` = {$_Planet['id']} AND `EndTime` > UNIX_TIMESTAMP())";

$SFBSelect = '';
$SFBSelect .= "SELECT `Type`, `BlockMissions`, `Reason`, `StartTime`, `EndTime`, `PostEndTime`, `ElementID`, `DontBlockIfIdle` FROM {{table}} WHERE `StartTime` <= UNIX_TIMESTAMP() AND ";
$SFBSelect .= implode(' OR ', $SFBSelectWhere);
$SFBSelect .= " ORDER BY `Type` ASC, `EndTime` DESC;";

$SQLResult_GetSmartFleetBlockadeData = doquery($SFBSelect, 'smart_fleet_blockade');

if($SQLResult_GetSmartFleetBlockadeData->num_rows > 0)
{
    while($GetSFBData = $SQLResult_GetSmartFleetBlockadeData->fetch_assoc())
    {
        $BlockedMissions = false;
        if($GetSFBData['BlockMissions'] == '0')
        {
            $BlockedMissions = true;
            $AllMissionsBlocked = true;
        }
        else
        {
            $BlockedMissions = explode(',', $GetSFBData['BlockMissions']);
        }

        if($BlockedMissions === true OR in_array($Fleet['Mission'], $BlockedMissions))
        {
            if($GetSFBData['Type'] == 1)
            {
                // Global Blockade
                if($GetSFBData['EndTime'] > $Now)
                {
                    // Normal Blockade
                    if(!($GetSFBData['DontBlockIfIdle'] == 1 AND in_array($Fleet['Mission'], $_Vars_FleetMissions['military']) AND $CheckPlanetOwner['owner'] > 0 AND $CheckPlanetOwner['onlinetime'] <= ($Now - $Protections['idleTime'])))
                    {
                        $BlockFleet = true;
                        $BlockReason = $_Lang['SFB_Stop_GlobalBlockade'];
                    }
                }
                elseif($GetSFBData['PostEndTime'] > $Now)
                {
                    // Post Blockade
                    if(in_array($Fleet['Mission'], $_Vars_FleetMissions['military']) AND $CheckPlanetOwner['owner'] > 0 AND
                    (
                        ($AllMissionsBlocked !== true AND $CheckPlanetOwner['onlinetime'] > ($Now - $Protections['idleTime']) AND $CheckPlanetOwner['onlinetime'] < $GetSFBData['StartTime'])
                        OR
                        ($AllMissionsBlocked === true AND $CheckPlanetOwner['onlinetime'] > ($Now - $Protections['idleTime']) AND $CheckPlanetOwner['onlinetime'] < $GetSFBData['EndTime'])
                    ))
                    {
                        $BlockFleet = true;
                        $BlockReason = sprintf($_Lang['SFB_Stop_GlobalPostBlockade'], prettyDate('d m Y, H:i:s', $GetSFBData['PostEndTime'], 1));
                    }
                }
            }
            elseif($GetSFBData['Type'] == 2)
            {
                // Per User Blockade
                $BlockFleet = true;
                $BlockGivenReason = (empty($GetSFBData['Reason']) ? $_Lang['SFB_Stop_ReasonNotGiven'] : "\"{$GetSFBData['Reason']}\"");
                $BlockReason = sprintf(($GetSFBData['ElementID'] == $_User['id'] ? $_Lang['SFB_Stop_UserBlockadeOwn'] : $_Lang['SFB_Stop_UserBlockade']), prettyDate('d m Y', $GetSFBData['EndTime'], 1), date('H:i:s', $GetSFBData['EndTime']), $BlockGivenReason);
            }
            elseif($GetSFBData['Type'] == 3)
            {
                // Per Planet Blockade
                $BlockFleet = true;
                $BlockGivenReason = (empty($GetSFBData['Reason']) ? $_Lang['SFB_Stop_ReasonNotGiven'] : "\"{$GetSFBData['Reason']}\"");
                if($GetSFBData['ElementID'] == $_Planet['id'])
                {
                    $UseLangVar = ($_Planet['planet_type'] == 1 ? $_Lang['SFB_Stop_PlanetBlockadeOwn_Planet'] : $_Lang['SFB_Stop_PlanetBlockadeOwn_Moon']);
                }
                else
                {
                    $UseLangVar = ($Target['type'] == 1 ? $_Lang['SFB_Stop_PlanetBlockade_Planet'] : $_Lang['SFB_Stop_PlanetBlockade_Moon']);
                }
                $BlockReason = sprintf($UseLangVar, prettyDate('d m Y', $GetSFBData['EndTime'], 1), date('H:i:s', $GetSFBData['EndTime']), $BlockGivenReason);
            }
        }

        if($BlockFleet === true)
        {
            messageRed($BlockReason.$_Lang['SFB_Stop_LearnMore'], $_Lang['SFB_BoxTitle']);
        }
    }
}

// --- Parse Fleet Array
$Fleet['count'] = 0;
$Fleet['storage'] = 0;
$Fleet['FuelStorage'] = 0;
$Fleet['TotalResStorage'] = 0;

$FleetArray = array();
if(!empty($Fleet['array']) AND (array)$Fleet['array'] === $Fleet['array'])
{
    foreach($Fleet['array'] as $ShipData)
    {
        $ShipData = explode(',', $ShipData);
        $ShipID = intval($ShipData[0]);
        if(in_array($ShipID, $_Vars_ElementCategories['fleet']))
        {
            if(!empty($_Vars_Prices[$ShipID]['engine']))
            {
                $ShipCount = floor($ShipData[1]);
                if($ShipCount > 0)
                {
                    if($_Planet[$_Vars_GameElements[$ShipID]] >= $ShipCount)
                    {
                        $FleetArray[$ShipID] = $ShipCount;
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
                        $FleetRemover[] = "`{$_Vars_GameElements[$ShipID]}` = `{$_Vars_GameElements[$ShipID]}` - {$ShipCount}";
                    }
                    else
                    {
                        messageRed($_Lang['fl1_NoEnoughShips'], $ErrorTitle);
                    }
                }
                else
                {
                    messageRed($_Lang['fl2_ShipCountCantBe0'], $ErrorTitle);
                }
            }
            else
            {
                messageRed($_Lang['fl1_CantSendUnflyable'], $ErrorTitle);
            }
        }
        else
        {
            messageRed($_Lang['fl1_BadShipGiven'], $ErrorTitle);
        }
    }
}
else
{
    messageRed($_Lang['fl2_FleetArrayPostEmpty'], $ErrorTitle);
}
if($Fleet['count'] <= 0)
{
    messageRed($_Lang['fl2_ZeroShips'], $ErrorTitle);
}
$Fleet['array'] = $FleetArray;
unset($FleetArray);

// --- Create Array of Available Missions
$AvailableMissions = array();
if($Target['type'] == 2)
{
    if($Fleet['array'][209] > 0)
    {
        $AvailableMissions[] = 8;
    }
}
else
{
    if($UsedPlanet)
    {
        if(!isset($Fleet['array'][210]) || $Fleet['count'] > $Fleet['array'][210])
        {
            $AvailableMissions[] = 3;
        }
        if(!$YourPlanet)
        {
            $AvailableMissions[] = 1;
            $AvailableMissions[] = 2;
            if($OwnerFriend)
            {
                $AvailableMissions[] = 5;
            }
            if(isset($Fleet['array'][210]) && $Fleet['count'] == $Fleet['array'][210])
            {
                $AvailableMissions[] = 6;
            }
            if($Target['type'] == 3 && isset($Fleet['array'][214]) && $Fleet['array'][214] > 0)
            {
                $AvailableMissions[] = 9;
            }
        }
        else
        {
            $AvailableMissions[] = 4;
        }
    }
    else
    {
        if($Target['planet'] == (MAX_PLANET_IN_SYSTEM + 1))
        {
            $AvailableMissions[] = 15;
        }
        else
        {
            if($Fleet['array'][208] > 0 AND $Target['type'] == 1)
            {
                $AvailableMissions[] = 7;
            }
        }
    }
}

// --- Check if everything is OK with ACS
$Throw = false;
if($Fleet['Mission'] == 2 AND in_array(2, $AvailableMissions))
{
    if($Fleet['ACS_ID'] > 0)
    {
        $CheckACS = doquery("SELECT {{table}}.*, `fleets`.`fleet_send_time` AS `mf_start_time` FROM {{table}} LEFT JOIN {{prefix}}fleets AS `fleets` ON `fleets`.`fleet_id` = {{table}}.`main_fleet_id` WHERE {{table}}.`id` = {$Fleet['ACS_ID']} LIMIT 1;", 'acs', true);
        if($CheckACS)
        {
            if($CheckACS['owner_id'] == $_User['id'] OR strstr($CheckACS['users'], '|'.$_User['id'].'|') !== FALSE)
            {
                if($CheckACS['end_target_id'] == $CheckPlanetOwner['id'])
                {
                    if($CheckACS['fleets_count'] < ACS_MAX_JOINED_FLEETS)
                    {
                        if($CheckACS['start_time'] > $Now)
                        {
                            $UpdateACS = true;
                        }
                        else
                        {
                            $Throw = $_Lang['fl_acs_cannot_join_time_extended'];
                        }
                    }
                    else
                    {
                        $Throw = $_Lang['fl_acs_fleetcount_extended'];
                    }
                }
                else
                {
                    $Throw = $_Lang['fl_acs_badcoordinates'];
                }
            }
            else
            {
                $Throw = $_Lang['fl_acs_cannot_join_this_group'];
            }
        }
        else
        {
            $Throw = $_Lang['fl_acs_bad_group_id'];
        }
    }
    else
    {
        $Throw = $_Lang['fl_acs_bad_group_id'];
    }
    if($Throw)
    {
        messageRed($Throw, $ErrorTitle);
    }
}

// --- If Mission is not correct, show Error
if(!in_array($Fleet['Mission'], $AvailableMissions))
{
    switch($Fleet['Mission'])
    {
        case 1:
            if($Target['type'] == 2)
            {
                $Throw = $_Lang['fl3_CantAttackDebris'];
            }
            else
            {
                if(!$UsedPlanet)
                {
                    $Throw = $_Lang['fl3_CantAttackNonUsed'];
                }
                else
                {
                    if($YourPlanet)
                    {
                        $Throw = $_Lang['fl3_CantAttackYourself'];
                    }
                }
            }
            break;
        case 2:
            if($Target['type'] == 2)
            {
                $Throw = $_Lang['fl3_CantACSDebris'];
            }
            else
            {
                if(!$UsedPlanet)
                {
                    $Throw = $_Lang['fl3_CantACSNonUsed'];
                }
                else
                {
                    if($YourPlanet)
                    {
                        $Throw = $_Lang['fl3_CantACSYourself'];
                    }
                }
            }
            break;
        case 3:
            if($Target['type'] == 2)
            {
                $Throw = $_Lang['fl3_CantTransportDebris'];
            }
            else
            {
                if(!$UsedPlanet)
                {
                    $Throw = $_Lang['fl3_CantTransportNonUsed'];
                }
                else
                {
                    $Throw = $_Lang['fl3_CantTransportSpyProbes'];
                }
            }
            break;
        case 4:
            if($Target['type'] == 2)
            {
                $Throw = $_Lang['fl3_CantStayDebris'];
            }
            else
            {
                if(!$UsedPlanet)
                {
                    $Throw = $_Lang['fl3_CantStayNonUsed'];
                }
                else
                {
                    if(!$YourPlanet)
                    {
                        $Throw = $_Lang['fl3_CantStayNonYourself'];
                    }
                }
            }
            break;
        case 5:
            if($Target['type'] == 2)
            {
                $Throw = $_Lang['fl3_CantProtectDebris'];
            }
            else
            {
                if(!$UsedPlanet)
                {
                    $Throw = $_Lang['fl3_CantProtectNonUsed'];
                }
                else
                {
                    if($YourPlanet)
                    {
                        $Throw = $_Lang['fl3_CantProtectYourself'];
                    }
                    else
                    {
                        $Throw = $_Lang['fl3_CantProtectNonFriend'];
                    }
                }
            }
            break;
        case 6:
            if($Target['type'] == 2)
            {
                $Throw = $_Lang['fl3_CantSpyDebris'];
            }
            else
            {
                if(!$UsedPlanet)
                {
                    $Throw = $_Lang['fl3_CantSpyNonUsed'];
                }
                else
                {
                    if($YourPlanet)
                    {
                        $Throw = $_Lang['fl3_CantSpyYourself'];
                    }
                    else
                    {
                        $Throw = $_Lang['fl3_CantSpyProbesCount'];
                    }
                }
            }
            break;
        case 7:
            if($UsedPlanet)
            {
                $Throw = $_Lang['fl3_CantSettleOnUsed'];
            }
            else
            {
                if($Target['type'] != 1)
                {
                    $Throw = $_Lang['fl3_CantSettleNonPlanet'];
                }
                else
                {
                    $Throw = $_Lang['fl3_CantSettleNoShips'];
                }
            }
            break;
        case 8:
            if($Target['type'] != 2)
            {
                $Throw = $_Lang['fl3_CantRecycleNonDebris'];
            }
            else
            {
                $Throw = $_Lang['fl3_CantRecycleNoShip'];
            }
            break;
        case 9:
            if($Target['type'] == 2)
            {
                $Throw = $_Lang['fl3_CantDestroyDebris'];
            }
            else
            {
                if(!$UsedPlanet)
                {
                    $Throw = $_Lang['fl3_CantDestroyNonUsed'];
                }
                else
                {
                    if($YourPlanet)
                    {
                        $Throw = $_Lang['fl3_CantDestroyYourself'];
                    }
                    else
                    {
                        if($Target['type'] != 3)
                        {
                            $Throw = $_Lang['fl3_CantDestroyNonMoon'];
                        }
                        else
                        {
                            $Throw = $_Lang['fl3_CantDestroyNoShip'];
                        }
                    }
                }
            }
            break;
    }
    if($Throw)
    {
        messageRed($Throw, $ErrorTitle);
    }
    messageRed($_Lang['fl3_BadMissionSelected'], $ErrorTitle);
}

// --- If Mission is Recycling and there is no Debris Field, show Error
if($Fleet['Mission'] == 8)
{
    if($CheckDebrisField['metal'] <= 0 AND $CheckDebrisField['crystal'] <= 0)
    {
        messageRed($_Lang['fl3_NoDebrisFieldHere'], $ErrorTitle);
    }
}

// --- Check if Expeditions and HoldingTimes are Correct
$Throw = false;
$Fleet['StayTime'] = 0;
if($Fleet['Mission'] == 15)
{
    if($Fleet['ExpeTime'] < 1)
    {
        $Throw = $_Lang['fl3_Expedition_Min1H'];
    }
    elseif($Fleet['ExpeTime'] > 12)
    {
        $Throw = $_Lang['fl3_Expedition_Max12H'];
    }
    $Fleet['StayTime'] = $Fleet['ExpeTime'] * 3600;
}
elseif($Fleet['Mission'] == 5)
{
    if(!in_array($Fleet['HoldTime'], array(1, 2, 4, 8, 16, 32)))
    {
        $Throw = $_Lang['fl3_Holding_BadTime'];
    }
    $Fleet['StayTime'] = $Fleet['HoldTime'] * 3600;
}
if($Throw)
{
    messageRed($Throw, $ErrorTitle);
}

// --- Set Variables to better usage
if(!empty($CheckDebrisField))
{
    $TargetData = &$CheckDebrisField;
}
else
{
    $TargetData = &$CheckPlanetOwner;
}

// --- Check if User data are OK
if($UsedPlanet AND !$YourPlanet AND !$PlanetAbandoned)
{
    $SaveMyTotalRank = false;

    $StatsData['his'] = ($TargetData['total_points'] > 0 ? $TargetData['total_points'] : 0);
    if(!CheckAuth('programmer'))
    {
        $StatsData['mine'] = ($_User['total_points'] > 0 ? $_User['total_points'] : 0);
    }
    else
    {
        $StatsData['mine'] = $StatsData['his'];
        if($_User['total_rank'] <= 0)
        {
            $SaveMyTotalRank = $_User['total_rank'];
            $_User['total_rank'] = $TargetData['total_rank'];
        }
    }

    if(isOnVacation($TargetData))
    {
        if($SaveMyTotalRank !== false)
        {
            $_User['total_rank'] = $SaveMyTotalRank;
        }
        if($TargetData['is_banned'] == 1)
        {
            messageRed($_Lang['fl3_CantSendBanned'], $ErrorTitle);
        }
        else
        {
            messageRed($_Lang['fl3_CantSendVacation'], $ErrorTitle);
        }
    }

    if($Protections['ally'] == 1)
    {
        if($_User['ally_id'] > 0 AND $_User['ally_id'] == $TargetData['ally_id'])
        {
            if(in_array($Fleet['Mission'], $Protections['mtypes']))
            {
                if($SaveMyTotalRank !== false)
                {
                    $_User['total_rank'] = $SaveMyTotalRank;
                }
                messageRed($_Lang['fl3_CantSendAlly'], $ErrorTitle);
            }
        }
    }

    if($Protections['enable'])
    {
        $Throw = false;
        $DoFarmCheck = false;
        if(in_array($Fleet['Mission'], $Protections['mtypes']))
        {
            if($_User['total_rank'] >= 1)
            {
                if($TargetData['total_rank'] >= 1)
                {
                    if($_User['NoobProtection_EndTime'] > $Now)
                    {
                        $Throw = sprintf($_Lang['fl3_ProtectNewTimeYou'], pretty_time($_User['NoobProtection_EndTime'] - $Now));
                    }
                    else if($TargetData['first_login'] == 0)
                    {
                        $Throw = $_Lang['fl3_ProtectNewTimeHe2'];
                    }
                    else if($TargetData['NoobProtection_EndTime'] > $Now)
                    {
                        $Throw = sprintf($_Lang['fl3_ProtectNewTimeHe'], pretty_time($TargetData['NoobProtection_EndTime'] - $Now));
                    }

                    if($Throw === false)
                    {
                        if($StatsData['mine'] >= $Protections['basicLimit'])
                        {
                            if($TargetData['onlinetime'] >= ($Now - $Protections['idleTime']))
                            {
                                if($StatsData['his'] < $Protections['basicLimit'])
                                {
                                    $Throw = sprintf($_Lang['fl3_ProtectHIWeak'], prettyNumber($Protections['basicLimit']));
                                }
                                else
                                {
                                    if($StatsData['his'] < $Protections['weakLimit'] OR $StatsData['mine'] < $Protections['weakLimit'])
                                    {
                                        if($StatsData['mine'] > ($StatsData['his'] * $Protections['weakMulti']))
                                        {
                                            $Throw = sprintf($_Lang['fl3_ProtectUR2Strong'], prettyNumber($Protections['weakMulti']));
                                        }
                                        elseif(($StatsData['mine'] * $Protections['weakMulti']) < $StatsData['his'])
                                        {
                                            $Throw = sprintf($_Lang['fl3_ProtectHI2Strong'], prettyNumber($Protections['weakMulti']));
                                        }
                                    }
                                    else
                                    {
                                        if($Protections['antifarm_enabled'] == true AND ($StatsData['mine'] / $StatsData['his']) >= $Protections['antifarm_rate'])
                                        {
                                            $DoFarmCheck = true;
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            $Throw = sprintf($_Lang['fl3_ProtectURWeak'], prettyNumber($Protections['basicLimit']));
                        }
                    }
                }
                else
                {
                    $Throw = $_Lang['fl3_ProtectHIStatNotCalc'];
                }
            }
            else
            {
                $Throw = $_Lang['fl3_ProtectURStatNotCalc'];
            }
            if($Protections['adminEnable'])
            {
                if(CheckAuth('supportadmin') OR CheckAuth('supportadmin', AUTHCHECK_NORMAL, $TargetData))
                {
                    if(CheckAuth('supportadmin'))
                    {
                        $Throw = $_Lang['fl3_ProtectAdminCant'];
                    }
                    else
                    {
                        $Throw = $_Lang['fl3_ProtectCantAdmin'];
                    }
                }
            }

            if(empty($Throw) AND ($DoFarmCheck === true OR $Protections['bashLimit_enabled'] === true))
            {
                if($DoFarmCheck === true)
                {
                    $TodayIs = explode('.', date('d.m.Y'));
                    $TodayTimestamp = mktime(0, 0, 0, $TodayIs[1], $TodayIs[0], $TodayIs[2]);
                    if($TodayTimestamp <= 0)
                    {
                        $TodayTimestamp = 0;
                    }
                    $BashTimestamps[] = array('type' => 'farm', 'key' => 'antifarm', 'stamp' => $TodayTimestamp);
                }
                if($Protections['bashLimit_enabled'] === true)
                {
                    $BashTimestamps[] = array('type' => 'bash', 'key' => 'bashLimit', 'stamp' => $Now - $Protections['bashLimit_interval']);
                }
                sort($BashTimestamps, SORT_ASC);
                $BashTimestampMinVal = $BashTimestamps[0]['stamp'];

                $SQLResult_GetFleetArchiveRecords = doquery(
                    "SELECT * FROM {{table}} WHERE (`Fleet_Time_Start` + `Fleet_Time_ACSAdd`) >= {$BashTimestampMinVal} AND `Fleet_Owner` = {$_User['id']} AND `Fleet_End_Owner` = {$TargetData['owner']} AND `Fleet_Mission` IN (1, 2, 9) AND `Fleet_ReportID` > 0 AND `Fleet_Destroyed_Reason` NOT IN (1, 4, 11);",
                    'fleet_archive'
                );

                if($SQLResult_GetFleetArchiveRecords->num_rows > 0)
                {
                    while($ArchiveRecord = $SQLResult_GetFleetArchiveRecords->fetch_assoc())
                    {
                        foreach($BashTimestamps as $Values)
                        {
                            if(($ArchiveRecord['Fleet_Time_Start'] + $ArchiveRecord['Fleet_Time_ACSAdd']) >= $Values['stamp'])
                            {
                                $GetEndID = $ArchiveRecord['Fleet_End_ID'];
                                if($ArchiveRecord['Fleet_End_ID_Changed'] > 0)
                                {
                                    $GetEndID = $ArchiveRecord['Fleet_End_ID_Changed'];
                                }
                                $SaveArchiveData[$Values['type']][$GetEndID] += 1;
                            }
                        }
                    }
                }

                foreach($BashTimestamps as $Values)
                {
                    if(!empty($SaveArchiveData))
                    {
                        $FleetArchiveRecordsCount = array_sum($SaveArchiveData[$Values['type']]);
                    }
                    if($FleetArchiveRecordsCount >= $Protections[$Values['key'].'_counttotal'])
                    {
                        $Throw = sprintf($_Lang['fl3_Protect_AttackLimitTotal'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
                        break;
                    }
                    elseif(($FleetArchiveRecordsCount + $FlyingFleetsData[$TargetData['owner']]['count']) >= $Protections[$Values['key'].'_counttotal'])
                    {
                        $Throw = sprintf($_Lang['fl3_Protect_AttackLimitTotalFly'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
                        break;
                    }
                    elseif($SaveArchiveData[$Values['type']][$TargetData['id']] >= $Protections[$Values['key'].'_countplanet'])
                    {
                        $Throw = sprintf($_Lang['fl3_Protect_AttackLimitSingle'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
                        break;
                    }
                    elseif(($SaveArchiveData[$Values['type']][$TargetData['id']] + $FlyingFleetsData[$TargetData['owner']][$TargetData['id']]) >= $Protections[$Values['key'].'_countplanet'])
                    {
                        $Throw = sprintf($_Lang['fl3_Protect_AttackLimitSingleFly'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
                        break;
                    }
                }
            }

            if($Throw)
            {
                if($SaveMyTotalRank !== false)
                {
                    $_User['total_rank'] = $SaveMyTotalRank;
                }
                messageRed($Throw, $ErrorTitle);
            }
        }
    }

    if($SaveMyTotalRank !== false)
    {
        $_User['total_rank'] = $SaveMyTotalRank;
    }
}

// --- Calculate Speed and Distance
$AllFleetSpeed = getFleetShipsSpeeds($Fleet['array'], $_User);
$GenFleetSpeed = $Fleet['Speed'];
$SpeedFactor = getUniFleetsSpeedFactor();
$MaxFleetSpeed = min($AllFleetSpeed);
if(MORALE_ENABLED)
{
    if($_User['morale_level'] <= MORALE_PENALTY_FLEETSLOWDOWN)
    {
        $MaxFleetSpeed *= MORALE_PENALTY_FLEETSLOWDOWN_VALUE;
    }
}

$Distance = getFlightDistanceBetween($_Planet, $Target);

$Allow_UseQuantumGate = false;
if($Fleet['UseQuantum'])
{
    $QuantumGate_Disallow = array(1, 2, 6, 9);
    if($_Planet['quantumgate'] == 1)
    {
        if(!in_array($Fleet['Mission'], $QuantumGate_Disallow))
        {
            if($UsedPlanet)
            {
                if($TargetData['quantumgate'] == 1)
                {
                    if($YourPlanet)
                    {
                        $Allow_UseQuantumGate = true;
                        $QuantumGate_UseType = 1;
                    }
                    else
                    {
                        if($OwnerFriend OR $OwnerHasMarcantilePact)
                        {
                            $Allow_UseQuantumGate = true;
                            $QuantumGate_UseType = 1;
                        }
                        else
                        {
                            $Check_SpaceTimeJump = true;
                        }
                    }
                }
                else
                {
                    $Check_SpaceTimeJump = true;
                }
            }
            else
            {
                $Check_SpaceTimeJump = true;
            }

            if($Check_SpaceTimeJump === true)
            {
                if($_Planet['galaxy'] == $Target['galaxy'])
                {
                    if(($_Planet['quantumgate_lastuse'] + ($QuantumGateInterval * 60 * 60)) <= $Now)
                    {
                        $Allow_UseQuantumGate = true;
                        $QuantumGate_UseType = 2;
                    }
                    else
                    {
                        $CannotUseTill = $_Planet['quantumgate_lastuse'] + ($QuantumGateInterval * 60 * 60);
                        messageRed(sprintf($_Lang['CannotUseQuantumGateTill'], prettyDate('d m Y \o H:i:s', $CannotUseTill, 1)), $ErrorTitle);
                    }
                }
                else
                {
                    messageRed($_Lang['fl3_SpaceTimeJumpGalaxy'], $ErrorTitle);
                }
            }
        }
        else
        {
            messageRed($_Lang['fl3_QuantumDisallowAttack'], $ErrorTitle);
        }
    }
    else
    {
        messageRed($_Lang['fl3_NoQuantumGate'], $ErrorTitle);
    }
}

if($Allow_UseQuantumGate)
{
    if($QuantumGate_UseType == 1)
    {
        $DurationTarget = $DurationBack = 1;
        $Consumption = 0;
    }
    elseif($QuantumGate_UseType == 2)
    {
        $DurationTarget = 1;
        $DurationBack = getFlightDuration([
            'speedFactor' => $GenFleetSpeed,
            'distance' => $Distance,
            'maxShipsSpeed' => $MaxFleetSpeed
        ]);

        $Consumption = getFlightTotalConsumption(
            [
                'ships' => $Fleet['array'],
                'distance' => $Distance,
                'duration' => $DurationBack,
            ],
            $_User
        );
        $Consumption = $Consumption / 2;
    }
}
else
{
    $DurationTarget = $DurationBack = getFlightDuration([
        'speedFactor' => $GenFleetSpeed,
        'distance' => $Distance,
        'maxShipsSpeed' => $MaxFleetSpeed
    ]);

    $Consumption = getFlightTotalConsumption(
        [
            'ships' => $Fleet['array'],
            'distance' => $Distance,
            'duration' => $DurationTarget,
        ],
        $_User
    );
}

if($_Planet['deuterium'] < $Consumption)
{
    messageRed($_Lang['fl3_NoEnoughFuel'], $ErrorTitle);
}
if($Consumption > ($Fleet['storage'] + $Fleet['FuelStorage']))
{
    messageRed($_Lang['fl3_NoEnoughtStorage4Fuel'], $ErrorTitle);
}
if($_Planet['deuterium'] < ($Consumption + $Fleet['resources']['deuterium']))
{
    messageRed($_Lang['fl3_PlanetNoEnoughdeuterium'], $ErrorTitle);
}
if($Fleet['FuelStorage'] >= $Consumption)
{
    $Fleet['storage'] += $Consumption;
}
else
{
    $Fleet['storage'] += $Fleet['FuelStorage'];
}
$Fleet['storage'] -= $Consumption;
foreach($Fleet['resources'] as $Value)
{
    $Fleet['TotalResStorage'] += $Value;
}
if($Fleet['TotalResStorage'] > $Fleet['storage'])
{
    messageRed($_Lang['fl3_NoEnoughStorage4Res'], $ErrorTitle);
}

$Fleet['SetCalcTime'] = $Now + $DurationTarget;
$Fleet['SetStayTime'] = ($Fleet['StayTime'] > 0 ? $Fleet['SetCalcTime'] + $Fleet['StayTime'] : '0');
$Fleet['SetBackTime'] = $Fleet['SetCalcTime'] + $Fleet['StayTime'] + $DurationBack;

if(isset($UpdateACS))
{
    $NewEndTime = $Fleet['SetCalcTime'];
    $OldFlightTime = $CheckACS['start_time_org'] - $CheckACS['mf_start_time'];
    $FlightDifference = ($NewEndTime - $CheckACS['mf_start_time']) - $OldFlightTime;

    if($OldFlightTime == 0)
    {
        $OldFlightTime = 1;
    }
    if($FlightDifference == 0)
    {
        $FlightDifference = 1;
    }
    if(($FlightDifference/$OldFlightTime) <= 0.3)
    {
        if($NewEndTime < $CheckACS['start_time'])
        {
            $Difference = $CheckACS['start_time'] - $NewEndTime;
            $Fleet['SetCalcTime'] += $Difference;
            $Fleet['SetBackTime'] += $Difference;
        }
        elseif($NewEndTime > $CheckACS['start_time'])
        {
            $Difference = $NewEndTime - $CheckACS['start_time'];
            $UpdateACSRow[] = "`start_time` = `start_time` + {$Difference}";
            $UpdateACSFleets[] = "`fleet_start_time` = `fleet_start_time` + {$Difference}";
            $UpdateACSFleets[] = "`fleet_end_time` = `fleet_end_time` + {$Difference}";
        }
    }
    else
    {
        messageRed($_Lang['fl3_ACSFleet2Slow'], $ErrorTitle);
    }
}

if($Allow_UseQuantumGate AND $QuantumGate_UseType == 2)
{
    $Add2UpdatePlanet[] = "`quantumgate_lastuse` = {$Now}";
    $Add2UpdatePlanetPHP['quantumgate_lastuse'] = $Now;
}

// MultiAlert System
$SendAlert = false;
$IPIntersectionFound = 'false';
$IPIntersectionFiltred = 'false';
$IPIntersectionNow = 'false';
if($UsedPlanet AND !$YourPlanet AND !$PlanetAbandoned)
{
    include($_EnginePath.'includes/functions/AlertSystemUtilities.php');
    $CheckIntersection = AlertUtils_IPIntersect($_User['id'], $TargetData['owner'], array
    (
        'LastTimeDiff' => (TIME_DAY * 30),
        'ThisTimeDiff' => (TIME_DAY * 30),
        'ThisTimeStamp' => ($Now - SERVER_MAINOPEN_TSTAMP)
    ));
    if($CheckIntersection !== false)
    {
        $IPIntersectionFound = 'true';
        if($_User['user_lastip'] == $TargetData['lastip'])
        {
            $IPIntersectionNow = 'true';
        }

        $FiltersData['place'] = 1;
        $FiltersData['alertsender'] = 1;
        $FiltersData['users'] = array($_User['id'], $TargetData['owner']);
        $FiltersData['ips'] = $CheckIntersection['Intersect'];
        $FiltersData['sender'] = $_User['id'];
        $FiltersData['target'] = $TargetData['owner'];
        foreach($CheckIntersection['Intersect'] as $IP)
        {
            $FiltersData['logcount'][$IP][$_User['id']] = $CheckIntersection['IPLogData'][$_User['id']][$IP]['Count'];
            $FiltersData['logcount'][$IP][$TargetData['owner']] = $CheckIntersection['IPLogData'][$TargetData['owner']][$IP]['Count'];
        }

        if($_User['multiIP_DeclarationID'] > 0 AND $_User['multiIP_DeclarationID'] == $TargetData['multiIP_DeclarationID'])
        {
            $Query_CheckDeclaration = '';
            $Query_CheckDeclaration .= "SELECT `id` FROM {{table}} WHERE ";
            $Query_CheckDeclaration .= "`status` = 1 AND `id` = {$_User['multiIP_DeclarationID']} ";
            $Query_CheckDeclaration .= "LIMIT 1;";
            $CheckDeclaration = doquery($Query_CheckDeclaration, 'declarations', true);
            $DeclarationID = $CheckDeclaration['id'];
        }
        else
        {
            $DeclarationID = 0;
        }

        $FilterResult = AlertUtils_CheckFilters($FiltersData);
        if($FilterResult['FilterUsed'])
        {
            $IPIntersectionFiltred = 'true';
        }
        if(!$FilterResult['SendAlert'])
        {
            $DontSendAlert = true;
        }
        if(!$FilterResult['ShowAlert'])
        {
            $DontShowAlert = true;
        }

        if(!isset($DontSendAlert))
        {
            $SendAlert = true;
        }
        if(1 == 0 AND $DontShowAlert !== true)
        {
            // Currently there is no indicator that user wants to get MultiAlert Messages (do disable this code)
            $LockFleetSending = true;
            $ShowMultiAlert = true;
            $_Alert['MultiAlert']['Data']['Blocked'] = true;
        }
    }
}

if(!isset($LockFleetSending))
{
    $FleetArray = array();
    foreach($Fleet['array'] as $ShipID => $ShipCount)
    {
        $FleetArray[] = "{$ShipID},{$ShipCount}";
    }
    $FleetArrayCopy = $Fleet['array'];
    $Fleet['array'] = implode(';', $FleetArray);

    if(empty($TargetData['id']))
    {
        $TargetData['id'] = '0';
    }
    if(empty($TargetData['owner']))
    {
        $TargetData['owner'] = '0';
    }
    if(empty($TargetData['galaxy_id']))
    {
        $TargetData['galaxy_id'] = '0';
    }

    $Query_Insert = '';
    $Query_Insert .= "INSERT INTO {{table}} SET ";
    $Query_Insert .= "`fleet_owner` = {$_User['id']}, ";
    $Query_Insert .= "`fleet_mission` = {$Fleet['Mission']}, ";
    $Query_Insert .= "`fleet_amount` = {$Fleet['count']}, ";
    $Query_Insert .= "`fleet_array` = '{$Fleet['array']}', ";
    $Query_Insert .= "`fleet_start_time` = {$Fleet['SetCalcTime']}, ";
    $Query_Insert .= "`fleet_start_id` = {$_Planet['id']}, ";
    $Query_Insert .= "`fleet_start_galaxy` = {$_Planet['galaxy']}, ";
    $Query_Insert .= "`fleet_start_system` = {$_Planet['system']}, ";
    $Query_Insert .= "`fleet_start_planet` = {$_Planet['planet']}, ";
    $Query_Insert .= "`fleet_start_type` = {$_Planet['planet_type']}, ";
    $Query_Insert .= "`fleet_end_time` = {$Fleet['SetBackTime']}, ";
    $Query_Insert .= "`fleet_end_id` = {$TargetData['id']}, ";
    $Query_Insert .= "`fleet_end_id_galaxy` = {$TargetData['galaxy_id']}, ";
    $Query_Insert .= "`fleet_end_stay` = {$Fleet['SetStayTime']}, ";
    $Query_Insert .= "`fleet_end_galaxy` = {$Target['galaxy']}, ";
    $Query_Insert .= "`fleet_end_system` = {$Target['system']}, ";
    $Query_Insert .= "`fleet_end_planet` = {$Target['planet']}, ";
    $Query_Insert .= "`fleet_end_type` = {$Target['type']}, ";
    $Query_Insert .= "`fleet_resource_metal` = {$Fleet['resources']['metal']}, ";
    $Query_Insert .= "`fleet_resource_crystal` = {$Fleet['resources']['crystal']}, ";
    $Query_Insert .= "`fleet_resource_deuterium` = {$Fleet['resources']['deuterium']}, ";
    $Query_Insert .= "`fleet_target_owner` = '{$TargetData['owner']}', ";
    $Query_Insert .= "`fleet_send_time` = {$Now};";
    doquery($Query_Insert, 'fleets');

    $LastFleetID = doquery("SELECT LAST_INSERT_ID() as `id`;", '', true);
    $LastFleetID = $LastFleetID['id'];

    // PushAlert
    if($UsedPlanet AND !$YourPlanet AND !$PlanetAbandoned)
    {
        if($Fleet['Mission'] == 3)
        {
            if($StatsData['mine'] < $StatsData['his'])
            {
                if(!empty($Fleet['resources']))
                {
                    foreach($Fleet['resources'] as $ThisValue)
                    {
                        if($ThisValue > 0)
                        {
                            $_Alert['PushAlert']['HasResources'] = true;
                            break;
                        }
                    }
                }
                if($_Alert['PushAlert']['HasResources'] === true)
                {
                    $FiltersData = array();
                    $FiltersData['place'] = 1;
                    $FiltersData['alertsender'] = 1;
                    $FiltersData['users'] = array($_User['id'], $TargetData['owner']);
                    $FiltersData['sender'] = $_User['id'];
                    $FiltersData['target'] = $TargetData['owner'];
                    $FilterResult = AlertUtils_CheckFilters($FiltersData, array('DontLoad' => true, 'DontLoad_OnlyIfCacheEmpty' => true));

                    if($FilterResult['SendAlert'])
                    {
                        $_Alert['PushAlert']['Data']['TargetUserID'] = $TargetData['owner'];
                        if($_User['ally_id'] > 0 AND $_User['ally_id'] == $TargetData['ally_id'])
                        {
                            $_Alert['PushAlert']['Data']['SameAlly'] = $TargetData['ally_id'];
                        }
                        else if($OwnerIsAlliedUser === true)
                        {
                            $_Alert['PushAlert']['Data']['AllyPact'] = array
                            (
                                'SenderAlly' => $_User['ally_id'],
                                'TargetAlly' => $TargetData['ally_id'],
                            );
                        }
                        if($OwnerIsBuddyFriend === true)
                        {
                            $_Alert['PushAlert']['Data']['BuddyFriends'] = true;
                        }
                        $_Alert['PushAlert']['Data']['FleetID'] = $LastFleetID;
                        $_Alert['PushAlert']['Data']['Stats']['Sender'] = array('Points' => $StatsData['mine'], 'Position' => $_User['total_rank']);
                        $_Alert['PushAlert']['Data']['Stats']['Target'] = array('Points' => $StatsData['his'], 'Position' => $TargetData['total_rank']);
                        $_Alert['PushAlert']['Data']['Resources'] = array
                        (
                            'Metal' => floatval($Fleet['resources']['metal']),
                            'Crystal' => floatval($Fleet['resources']['crystal']),
                            'Deuterium' => floatval($Fleet['resources']['deuterium'])
                        );

                        Alerts_Add(1, $Now, 5, 4, 5, $_User['id'], $_Alert['PushAlert']['Data']);
                    }
                }
            }
        }
    }
}

if($SendAlert)
{
    $_Alert['MultiAlert']['Importance'] = 10;
    $_Alert['MultiAlert']['Data']['MissionID'] = $Fleet['Mission'];
    if($LastFleetID > 0)
    {
        $_Alert['MultiAlert']['Data']['FleetID'] = $LastFleetID;
    }
    $_Alert['MultiAlert']['Data']['TargetUserID'] = $TargetData['owner'];
    foreach($CheckIntersection['Intersect'] as $ThisIPID)
    {
        $_Alert['MultiAlert']['Data']['Intersect'][] = array
        (
            'IPID' => $ThisIPID,
            'SenderData' => $CheckIntersection['IPLogData'][$_User['id']][$ThisIPID],
            'TargetData' => $CheckIntersection['IPLogData'][$TargetData['owner']][$ThisIPID]
        );
    }
    if($DeclarationID > 0)
    {
        $_Alert['MultiAlert']['Data']['DeclarationID'] = $DeclarationID;
        $_Alert['MultiAlert']['Type'] = 2;
    }
    else
    {
        $_Alert['MultiAlert']['Type'] = 1;
    }

    $Query_AlertOtherUsers = '';
    $Query_AlertOtherUsers .= "SELECT DISTINCT `User_ID` FROM {{table}} WHERE ";
    $Query_AlertOtherUsers .= "`User_ID` NOT IN ({$_User['id']}, {$TargetData['owner']}) AND ";
    $Query_AlertOtherUsers .= "`IP_ID` IN (".implode(', ', $CheckIntersection['Intersect']).") AND ";
    $Query_AlertOtherUsers .= "`Count` > `FailCount`;";
    $Result_AlertOtherUsers = doquery($Query_AlertOtherUsers, 'user_enterlog');
    if($Result_AlertOtherUsers->num_rows > 0)
    {
        while($FetchData = $Result_AlertOtherUsers->fetch_assoc())
        {
            $_Alert['MultiAlert']['Data']['OtherUsers'][] = $FetchData['User_ID'];
        }
    }

    Alerts_Add(1, $Now, $_Alert['MultiAlert']['Type'], 1, $_Alert['MultiAlert']['Importance'], $_User['id'], $_Alert['MultiAlert']['Data']);
}

if(isset($ShowMultiAlert))
{
    messageRed($_Lang['MultiAlert'], $_Lang['fl_error']);
}

if(isset($UpdateACS))
{
    if(!empty($CheckACS['fleets_id']))
    {
        $NewFleetsID[] = $CheckACS['fleets_id'];
    }
    $NewFleetsID[] = '|'.$LastFleetID.'|';
    $UpdateACSRow[] = "`fleets_id` = '".implode(',', $NewFleetsID)."'";

    if(!empty($CheckACS['user_joined']))
    {
        if(strstr($CheckACS['user_joined'], '|'.$_User['id'].'|') === FALSE)
        {
            $NewUsers[] = $CheckACS['user_joined'];
            $NewUsers[] = '|'.$_User['id'].'|';
            $UpdateACSRow[] = "`user_joined` = '".implode(',', $NewUsers)."'";
        }
    }
    else
    {
        $UpdateACSRow[] = "`user_joined` = '|{$_User['id']}|'";
    }

    $UpdateACSRow[] = "`fleets_count` = `fleets_count` + 1";

    if(!empty($UpdateACSRow))
    {
        doquery("UPDATE {{table}} SET ".implode(', ', $UpdateACSRow)." WHERE `id` = {$Fleet['ACS_ID']};", 'acs');
    }

    if(!empty($UpdateACSFleets))
    {
        $Fleets = $CheckACS['main_fleet_id'];
        if(!empty($CheckACS['fleets_id']))
        {
            $Fleets .= ','.str_replace('|', '', $CheckACS['fleets_id']);
        }
        doquery("UPDATE {{table}} SET ".implode(', ', $UpdateACSFleets)." WHERE `fleet_id` IN ({$Fleets});", 'fleets');
    }
}

if($Allow_UseQuantumGate)
{
    $QuantumGate_Used = '1';
}
else
{
    $QuantumGate_Used = '0';
}
$QryArchive = '';
$QryArchive .= "INSERT INTO {{table}} (`Fleet_ID`, `Fleet_Owner`, `Fleet_Mission`, `Fleet_Array`, `Fleet_Time_Send`, `Fleet_Time_Start`, `Fleet_Time_Stay`, `Fleet_Time_End`, `Fleet_Start_ID`, `Fleet_Start_Galaxy`, `Fleet_Start_System`, `Fleet_Start_Planet`, `Fleet_Start_Type`, `Fleet_Start_Res_Metal`, `Fleet_Start_Res_Crystal`, `Fleet_Start_Res_Deuterium`, `Fleet_End_ID`, `Fleet_End_ID_Galaxy`, `Fleet_End_Galaxy`, `Fleet_End_System`, `Fleet_End_Planet`, `Fleet_End_Type`, `Fleet_End_Owner`, `Fleet_ACSID`, `Fleet_Info_HadSameIP_Ever`, `Fleet_Info_HadSameIP_Ever_Filtred`, `Fleet_Info_HadSameIP_OnSend`, `Fleet_Info_UsedTeleport`) VALUES ";
$QryArchive .= " ({$LastFleetID}, {$_User['id']}, {$Fleet['Mission']}, '{$Fleet['array']}', {$Now}, {$Fleet['SetCalcTime']}, {$Fleet['SetStayTime']}, {$Fleet['SetBackTime']}, {$_Planet['id']}, {$_Planet['galaxy']}, {$_Planet['system']}, {$_Planet['planet']}, {$_Planet['planet_type']}, {$Fleet['resources']['metal']}, {$Fleet['resources']['crystal']}, {$Fleet['resources']['deuterium']}, '{$TargetData['id']}', '{$TargetData['galaxy_id']}', {$Target['galaxy']}, {$Target['system']}, {$Target['planet']}, {$Target['type']}, '{$TargetData['owner']}', '{$Fleet['ACS_ID']}', {$IPIntersectionFound}, {$IPIntersectionFiltred}, {$IPIntersectionNow}, {$QuantumGate_Used}) ";

if(!empty($UpdateACSFleets))
{
    $UpdateACSFleetsIDs = explode(',', str_replace('|', '', $CheckACS['fleets_id']));
    $UpdateACSFleetsIDs[] = $CheckACS['main_fleet_id'];
    if(!empty($UpdateACSFleetsIDs))
    {
        $QryArchive .= ', ';
        foreach($UpdateACSFleetsIDs as $FleetID)
        {
            if(!empty($FleetID))
            {
                $QryArchiveA[] = "({$FleetID}, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
            }
        }
        $QryArchive .= implode(', ',$QryArchiveA);
        $QryArchive .= " ON DUPLICATE KEY UPDATE ";
        $QryArchive .= "`Fleet_Time_ACSAdd` = `Fleet_Time_ACSAdd` + {$Difference}";
    }
}
doquery($QryArchive, 'fleet_archive');

$_Planet['metal'] -= $Fleet['resources']['metal'];
$_Planet['crystal'] -= $Fleet['resources']['crystal'];
$_Planet['deuterium'] -= ($Fleet['resources']['deuterium'] + $Consumption);

$_Lang['ShipsRows'] = '';
foreach($FleetArrayCopy as $ShipID => $ShipCount)
{
    $_Planet[$_Vars_GameElements[$ShipID]] -= $ShipCount;
    $_Lang['ShipsRows'] .= '<tr><th class="pad">'.$_Lang['tech'][$ShipID].'</th><th class="pad">'.prettyNumber($ShipCount).'</th></tr>';
}
if(!empty($Add2UpdatePlanetPHP))
{
    foreach($Add2UpdatePlanetPHP as $Key => $Value)
    {
        $_Planet[$Key] = $Value;
    }
}

$QryUpdatePlanet = '';
$QryUpdatePlanet .= "UPDATE {{table}} SET ";
$QryUpdatePlanet .= implode(', ', $FleetRemover).', ';
$QryUpdatePlanet .= "`metal` = '{$_Planet['metal']}', ";
$QryUpdatePlanet .= "`crystal` = '{$_Planet['crystal']}', ";
$QryUpdatePlanet .= "`deuterium` = '{$_Planet['deuterium']}' ";
if(!empty($Add2UpdatePlanet))
{
    $QryUpdatePlanet .= ", ".implode(', ', $Add2UpdatePlanet);
}
$QryUpdatePlanet .= " WHERE ";
$QryUpdatePlanet .= "`id` = {$_Planet['id']};";

doquery('LOCK TABLE {{table}} WRITE', 'planets');
doquery($QryUpdatePlanet, 'planets');
doquery('UNLOCK TABLES', '');

// User Development Log
if($Fleet['resources']['metal'] > 0)
{
    $Add2UserDev_Log[] = 'M,'.$Fleet['resources']['metal'];
}
if($Fleet['resources']['crystal'] > 0)
{
    $Add2UserDev_Log[] = 'C,'.$Fleet['resources']['crystal'];
}
if($Fleet['resources']['deuterium'] > 0)
{
    $Add2UserDev_Log[] = 'D,'.$Fleet['resources']['deuterium'];
}
if($Consumption > 0)
{
    $Add2UserDev_Log[] = 'F,'.$Consumption;
}
$RTrim = rtrim($Fleet['array'], ';');
if(!empty($Add2UserDev_Log))
{
    $Add2UserDev_Log = ';'.implode(';', $Add2UserDev_Log);
}
$UserDev_Log[] = array('PlanetID' => $_Planet['id'], 'Date' => $Now, 'Place' => 10, 'Code' => $Fleet['Mission'], 'ElementID' => $LastFleetID, 'AdditionalData' => $RTrim.$Add2UserDev_Log);
// ---

$_Lang['FleetMission'] = $_Lang['type_mission'][$Fleet['Mission']];
$_Lang['FleetDistance'] = prettyNumber($Distance);
$_Lang['FleetSpeed'] = prettyNumber($MaxFleetSpeed);
$_Lang['FleetFuel'] = prettyNumber($Consumption);
$_Lang['StartGalaxy'] = $_Planet['galaxy'];
$_Lang['StartSystem'] = $_Planet['system'];
$_Lang['StartPlanet'] = $_Planet['planet'];
$_Lang['StartType'] = ($_Planet['planet_type'] == 1 ? 'planet' : ($_Planet['planet_type'] == 3 ? 'moon' : 'debris'));
$_Lang['TargetGalaxy'] = $Target['galaxy'];
$_Lang['TargetSystem'] = $Target['system'];
$_Lang['TargetPlanet'] = $Target['planet'];
$_Lang['TargetType'] = ($Target['type'] == 1 ? 'planet' : ($Target['type'] == 3 ? 'moon' : 'debris'));
$_Lang['FleetStartTime'] = prettyDate('d m Y H:i:s', $Fleet['SetCalcTime'], 1);
$_Lang['FleetEndTime'] = prettyDate('d m Y H:i:s', $Fleet['SetBackTime'], 1);
$_Lang['useQuickRes'] = ($_POST['useQuickRes'] == '1' ? 'true' : 'false');

display(parsetemplate(gettemplate('fleet3_body'), $_Lang), $_Lang['fl_title']);

?>
