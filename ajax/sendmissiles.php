<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_DontForceRulesAcceptance = true;

$_SetAccessLogPreFilename = 'ajax/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath.'common.php');

function CreateReturn($ReturnCode, $Update = '0')
{
    global $_Planet, $FlyingFleets;

    $Missiles = prettyNumber($_Planet['interplanetary_missile']);
    safeDie("{$ReturnCode}|{$Update}|{$Missiles}|{$FlyingFleets}");
}

if(!isLogged())
{
    CreateReturn('601');
}
if(!empty($_User['activation_code']))
{
    CreateReturn('661');
}

$Now = time();

$FlyingFleets = doquery("SELECT COUNT(`fleet_id`) as `Number` FROM {{table}} WHERE `fleet_owner` = '{$_User['id']}';", 'fleets', true);
$FlyingFleets = $FlyingFleets['Number'];
$MaxFleets = 1 + $_User[$_Vars_GameElements[108]] + (($_User['admiral_time'] > $Now) ? 2 : 0);
if($FlyingFleets >= $MaxFleets)
{
    CreateReturn('609');
}

$protection = $_GameConfig['noobprotection'];
$protectiontime = $_GameConfig['noobprotectiontime'];
$protectionmulti = $_GameConfig['noobprotectionmulti'];
$adminprotection = $_GameConfig['adminprotection'];
$allyprotection = $_GameConfig['allyprotection'];
$noNoobProtect = $_GameConfig['no_noob_protect'];
$noIdleProtect = $_GameConfig['no_idle_protect'];
$Protections['idleTime'] = $_GameConfig['no_idle_protect'] * TIME_DAY;

$Galaxy = (isset($_POST['galaxy']) ? intval($_POST['galaxy']) : 0);
$System = (isset($_POST['system']) ? intval($_POST['system']) : 0);
$Planet = (isset($_POST['planet']) ? intval($_POST['planet']) : 0);
$Type = 1;

$Mission = 10;
$Missiles = (isset($_POST['count']) ? round(str_replace('.', '', $_POST['count'])) : 0);
$PrimTarget = (isset($_POST['target']) ? intval($_POST['target']) : 0);
if($PrimTarget == 0)
{
    $PrimTarget = '0';
}

$Dist = abs($System - $_Planet['system']);
include($_EnginePath.'includes/functions/GetMissileRange.php');
$MissilesRange = GetMissileRange();
$FlightTime = round(((30 + (60 * $Dist)) * 2500) / $_GameConfig['game_speed']);

if($Missiles <= 0)
{
    CreateReturn('651');
}
if($Galaxy <= 0 OR $Planet <= 0 OR $System <= 0 OR $PrimTarget < 0 OR $PrimTarget > 99)
{
    CreateReturn('652');
}
if($_Planet['missile_silo'] < 4)
{
    CreateReturn('649');
}
if($MissilesRange <= 0)
{
    CreateReturn('648');
}
if($Dist > $MissilesRange OR $Galaxy != $_Planet['galaxy'])
{
    CreateReturn('647');
}
if($Missiles > $_Planet['interplanetary_missile'])
{
    CreateReturn('646', '1');
}

$Query_GetPlanet = '';
$Query_GetPlanet .= "SELECT `pl`.`id`, `pl`.`id_owner`, `galaxy`.`galaxy_id` FROM {{table}} AS `pl` ";
$Query_GetPlanet .= "LEFT JOIN `{{prefix}}galaxy` AS `galaxy` ON `galaxy`.`id_planet` = `pl`.`id` ";
$Query_GetPlanet .= "WHERE `pl`.`galaxy` = {$Galaxy} AND `pl`.`system` = {$System} AND `pl`.`planet` = {$Planet} AND `pl`.`planet_type` = {$Type} ";
$Query_GetPlanet .= "LIMIT 1; -- SendMissiles|GetPlanet";
$PlanetData = doquery($Query_GetPlanet, 'planets', true);

if($PlanetData['id'] <= 0)
{
    CreateReturn('650');
}
if($PlanetData['id_owner'] == $_User['id'])
{
    CreateReturn('645');
}

if($PlanetData['id_owner'] > 0)
{
    $Query_GetUser = '';
    $Query_GetUser .= "SELECT `usr`.`is_onvacation`, `usr`.`is_banned`, `usr`.`ally_id`, `usr`.`first_login`, `usr`.`NoobProtection_EndTime`, `usr`.`onlinetime`, `usr`.`authlevel`, ";
    $Query_GetUser .= "`stat`.`total_points`, `stat`.`total_rank` ";
    $Query_GetUser .= "FROM {{table}} AS `usr` ";
    $Query_GetUser .= "LEFT JOIN `{{prefix}}statpoints` AS `stat` ON `stat`.`stat_type` = 1 AND `stat`.`id_owner` = `usr`.`id` ";
    $Query_GetUser .= "WHERE `usr`.`id` = {$PlanetData['id_owner']} LIMIT 1; -- SendMissiles|GetUser";
    $HeDBRec = doquery($Query_GetUser, 'users', true);

    if(isOnVacation($HeDBRec))
    {
        if($HeDBRec['is_banned'] == 1)
        {
            CreateReturn('642');
        }
        else
        {
            CreateReturn('643');
        }
    }
    if($allyprotection == 1)
    {
        if($_User['ally_id'] > 0 AND $_User['ally_id'] == $HeDBRec['ally_id'])
        {
            CreateReturn('644');
        }
    }

    if(!CheckAuth('programmer'))
    {
        $MyGameLevel = $_User['total_points'];
    }
    else
    {
        $MyGameLevel = $HeDBRec['total_points'];
        if($_User['total_rank'] <= 0)
        {
            $_User['total_rank'] = $HeDBRec['total_rank'];
        }
    }
    $HeGameLevel = $HeDBRec['total_points'];

    if($protection == 1)
    {
        if($_User['total_rank'] < 1)
        {
            CreateReturn('663');
        }
        if($HeDBRec['total_rank'] < 1)
        {
            CreateReturn('662');
        }

        if($_User['NoobProtection_EndTime'] > $Now)
        {
            CreateReturn('653');
        }
        else if($HeDBRec['first_login'] == 0)
        {
            CreateReturn('655');
        }
        else if($HeDBRec['NoobProtection_EndTime'] > $Now)
        {
            CreateReturn('654');
        }

        if($HeDBRec['onlinetime'] >= ($Now - (TIME_DAY * $noIdleProtect)))
        {
            if($HeGameLevel < ($protectiontime * 1000))
            {
                CreateReturn('656');
            }
            else if($MyGameLevel < ($protectiontime * 1000))
            {
                CreateReturn('657');
            }
            else
            {
                if($MyGameLevel < ($noNoobProtect * 1000) OR $HeGameLevel < ($noNoobProtect * 1000))
                {
                    if(($MyGameLevel > ($HeGameLevel * $protectionmulti)))
                    {
                        CreateReturn('656');
                    }
                    else if(($MyGameLevel * $protectionmulti) < $HeGameLevel)
                    {
                        CreateReturn('658');
                    }
                }
            }
        }
    }
    if((CheckAuth('supportadmin') OR CheckAuth('supportadmin', AUTHCHECK_NORMAL, $HeDBRec)) AND $adminprotection == 1)
    {
        if(CheckAuth('supportadmin'))
        {
            CreateReturn('659');
        }
        else
        {
            CreateReturn('660');
        }
    }
}

// Fleet Blockade System
$SFBSelectWhere[] = "(`Type` = 1 AND (`EndTime` > UNIX_TIMESTAMP() OR `PostEndTime` > UNIX_TIMESTAMP()))";
if($PlanetData['id_owner'] > 0)
{
    $SFBSelectWhere[] = "(`Type` = 2 AND `ElementID` = {$PlanetData['id_owner']} AND `EndTime` > UNIX_TIMESTAMP())";
}
$SFBSelectWhere[] = "(`Type` = 3 AND `ElementID` = {$PlanetData['id']} AND `EndTime` > UNIX_TIMESTAMP())";
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

        if($BlockedMissions === true OR in_array($Mission, $BlockedMissions))
        {
            if($GetSFBData['Type'] == 1)
            {
                // Global Blockade
                if($GetSFBData['EndTime'] > $Now)
                {
                    // Normal Blockade
                    if(!($GetSFBData['DontBlockIfIdle'] == 1 AND in_array($Mission, $_Vars_FleetMissions['military']) AND $PlanetData['id_owner'] > 0 AND $HeDBRec['onlinetime'] <= ($Now - $Protections['idleTime'])))
                    {
                        $BlockFleet = true;
                        $BlockReason = $_Lang['SFB_Stop_GlobalBlockade'];
                    }
                }
                else if($GetSFBData['PostEndTime'] > $Now)
                {
                    // Post Blockade
                    if(in_array($Mission, $_Vars_FleetMissions['military']) AND $PlanetData['id_owner'] > 0 AND
                    (
                        ($AllMissionsBlocked !== true AND $HeDBRec['onlinetime'] > ($Now - $Protections['idleTime']) AND $HeDBRec['onlinetime'] < $GetSFBData['StartTime'])
                        OR
                        ($AllMissionsBlocked === true AND $HeDBRec['onlinetime'] > ($Now - $Protections['idleTime']) AND $HeDBRec['onlinetime'] < $GetSFBData['EndTime'])
                    ))
                    {
                        $BlockFleet = true;
                        $BlockReason = sprintf($_Lang['SFB_Stop_GlobalPostBlockade'], prettyDate('d m Y, H:i:s', $GetSFBData['PostEndTime'], 1));
                    }
                }
            }
            else if($GetSFBData['Type'] == 2)
            {
                // Per User Blockade
                $BlockFleet = true;
                $BlockGivenReason = (empty($GetSFBData['Reason']) ? $_Lang['SFB_Stop_ReasonNotGiven'] : "\"{$GetSFBData['Reason']}\"");
                $BlockReason = sprintf(($GetSFBData['ElementID'] == $_User['id'] ? $_Lang['SFB_Stop_UserBlockadeOwn'] : $_Lang['SFB_Stop_UserBlockade']), prettyDate('d m Y', $GetSFBData['EndTime'], 1), date('H:i:s', $GetSFBData['EndTime']), $BlockGivenReason);
            }
            else if($GetSFBData['Type'] == 3)
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
                    $UseLangVar = ($Type == 1 ? $_Lang['SFB_Stop_PlanetBlockade_Planet'] : $_Lang['SFB_Stop_PlanetBlockade_Moon']);
                }
                $BlockReason = sprintf($UseLangVar, prettyDate('d m Y', $GetSFBData['EndTime'], 1), date('H:i:s', $GetSFBData['EndTime']), $BlockGivenReason);
            }
        }

        if($BlockFleet === true)
        {
            CreateReturn('626');
        }
    }
}

$CreateMIPAttack = '';
$CreateMIPAttack .= "INSERT INTO {{table}} SET ";
$CreateMIPAttack .= "`fleet_owner` = {$_User['id']}, ";
$CreateMIPAttack .= "`fleet_mission` = {$Mission}, ";
$CreateMIPAttack .= "`fleet_amount` = {$Missiles}, ";
$CreateMIPAttack .= "`fleet_array` = '503,{$Missiles};primary_target,{$PrimTarget}', ";
$CreateMIPAttack .= "`fleet_start_time` = (UNIX_TIMESTAMP() + {$FlightTime}), ";
$CreateMIPAttack .= "`fleet_start_id` = {$_Planet['id']}, ";
$CreateMIPAttack .= "`fleet_start_galaxy` = {$_Planet['galaxy']}, ";
$CreateMIPAttack .= "`fleet_start_system` = {$_Planet['system']}, ";
$CreateMIPAttack .= "`fleet_start_planet` = {$_Planet['planet']}, ";
$CreateMIPAttack .= "`fleet_start_type` = 1, ";
$CreateMIPAttack .= "`fleet_end_time` = (UNIX_TIMESTAMP() + {$FlightTime}), ";
$CreateMIPAttack .= "`fleet_end_id` = {$PlanetData['id']}, ";
$CreateMIPAttack .= "`fleet_end_id_galaxy` = {$PlanetData['galaxy_id']}, ";
$CreateMIPAttack .= "`fleet_end_galaxy` = {$Galaxy}, ";
$CreateMIPAttack .= "`fleet_end_system` = {$System}, ";
$CreateMIPAttack .= "`fleet_end_planet` = {$Planet}, ";
$CreateMIPAttack .= "`fleet_end_type` = 1, ";
$CreateMIPAttack .= "`fleet_target_owner` = '{$PlanetData['id_owner']}', ";
$CreateMIPAttack .= "`fleet_send_time` = UNIX_TIMESTAMP();";
doquery($CreateMIPAttack, 'fleets');

$LastFleetID = doquery("SELECT LAST_INSERT_ID() as `id`;", '', true);
$LastFleetID = $LastFleetID['id'];

doquery("UPDATE {{table}} SET `interplanetary_missile` = `interplanetary_missile` - {$Missiles} WHERE `id` = {$_Planet['id']};", 'planets');

$QryArchive = '';
$QryArchive .= "INSERT INTO {{table}} SET ";
$QryArchive .= "`Fleet_ID` = {$LastFleetID}, ";
$QryArchive .= "`Fleet_Owner` = {$_User['id']}, ";
$QryArchive .= "`Fleet_Mission` = 10, ";
$QryArchive .= "`Fleet_Array` = '503,{$Missiles};primary_target,{$PrimTarget}', ";
$QryArchive .= "`Fleet_Time_Send` = UNIX_TIMESTAMP(), ";
$QryArchive .= "`Fleet_Time_Start` = (UNIX_TIMESTAMP() + {$FlightTime}), ";
$QryArchive .= "`Fleet_Start_ID` = {$_Planet['id']}, ";
$QryArchive .= "`Fleet_Start_Galaxy` = {$_Planet['galaxy']}, ";
$QryArchive .= "`Fleet_Start_System` = {$_Planet['system']}, ";
$QryArchive .= "`Fleet_Start_Planet` = {$_Planet['planet']}, ";
$QryArchive .= "`Fleet_Start_Type` = {$_Planet['planet_type']}, ";
$QryArchive .= "`Fleet_End_ID` = '{$PlanetData['id']}', ";
$QryArchive .= "`Fleet_End_ID_Galaxy` = '{$PlanetData['galaxy_id']}', ";
$QryArchive .= "`Fleet_End_Galaxy` = {$Galaxy}, ";
$QryArchive .= "`Fleet_End_System` = {$System}, ";
$QryArchive .= "`Fleet_End_Planet` = {$Planet}, ";
$QryArchive .= "`Fleet_End_Type` = 1, ";
$QryArchive .= "`Fleet_End_Owner` = '{$PlanetData['id_owner']}' ";
doquery($QryArchive, 'fleet_archive');

// User Development Log
$UserDev_Log[] = array('PlanetID' => $_Planet['id'], 'Date' => $Now, 'Place' => 11, 'Code' => '0', 'ElementID' => $LastFleetID, 'AdditionalData' => 'R,'.$Missiles);
// ---

$FlyingFleets += 1;
$_Planet['interplanetary_missile'] -= $Missiles;
CreateReturn('600_4', 1);

?>
