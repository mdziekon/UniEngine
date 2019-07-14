<?php

define('INSIDE', true);

$_SetAccessLogPreFilename = 'ajax/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath.'common.php');

function CreateReturn($ReturnCode)
{
    global $Update, $ShipCount, $Galaxy, $System, $Planet, $Type, $ActualFleets, $Spy_Probes, $Recyclers, $Colonizers;
    if(empty($Update))
    {
        $Update = '0';
    }
    if(empty($Galaxy))
    {
        $Galaxy = '0';
    }
    if(empty($System))
    {
        $System = '0';
    }
    if(empty($Planet))
    {
        $Planet = '0';
    }
    if(empty($Type))
    {
        $Type = '0';
    }
    if(empty($ActualFleets))
    {
        $ActualFleets = '0';
    }
    safeDie($ReturnCode.';'.$Update.';'.prettyNumber($ShipCount).';'.$Galaxy.';'.$System.';'.$Planet.';'.$Type.'|'.$ActualFleets.','.prettyNumber($Spy_Probes).','.prettyNumber($Recyclers).','.prettyNumber($Colonizers));
}

if(!isLogged())
{
    CreateReturn('601');
}
if(!empty($_User['activation_code']))
{
    CreateReturn('661');
}
$Galaxy        = (isset($_POST['galaxy']) ? intval($_POST['galaxy']) : 0);
$System        = (isset($_POST['system']) ? intval($_POST['system']) : 0);
$Planet        = (isset($_POST['planet']) ? intval($_POST['planet']) : 0);
$Type        = (isset($_POST['type']) ? intval($_POST['type']) : 0);
$Mission    = (isset($_POST['mission']) ? intval($_POST['mission']) : 0);
$Time        = time();
if($Mission != 6 AND $Mission != 7 AND $Mission != 8)
{
    CreateReturn('602');
}
if(!($Galaxy > 0 AND $System > 0 AND $Planet > 0 AND $Galaxy <= MAX_GALAXY_IN_WORLD AND $System <= MAX_SYSTEM_IN_GALAXY AND $Planet <= MAX_PLANET_IN_SYSTEM))
{
    CreateReturn('603');
}
if(!($Type == 1 OR $Type == 2 OR $Type == 3))
{
    CreateReturn('604');
}

$CurrentPlanet    = &$_Planet;
$FlyingFleets    = doquery("SELECT COUNT(`fleet_id`) as `Number` FROM {{table}} WHERE `fleet_owner` = '{$_User['id']}';", 'fleets', true);

$Recyclers        = $CurrentPlanet['recycler'];
$Spy_Probes        = $CurrentPlanet['espionage_probe'];
$Colonizers        = $CurrentPlanet['colony_ship'];
$ActualFleets    = $FlyingFleets['Number'];

if(MORALE_ENABLED)
{
    Morale_ReCalculate($_User, $Time);
}

if(($_User[$_Vars_GameElements[108]] + 1 + (($_User['admiral_time'] > 0) ? 2 : 0)) <= $ActualFleets)
{
    $Update = '1';
    CreateReturn('609');
}

switch($Mission)
{
    case 6:
    {
        //Spy
        if(!($Type == 1 OR $Type == 3))
        {
            CreateReturn('613');
        }

        $ShipID = 210;
        $ShipCount = $_User['settings_spyprobescount'];

        if($Type == 1)
        {
            $Query_GetTarget_Galaxy = 'id_planet';
        }
        else
        {
            $Query_GetTarget_Galaxy = 'id_moon';
        }
        $Query_GetTarget = '';
        $Query_GetTarget .= "SELECT `pl`.`id`, `pl`.`id_owner`, `galaxy`.`galaxy_id` FROM {{table}} AS `pl` ";
        $Query_GetTarget .= "LEFT JOIN `{{prefix}}galaxy` AS `galaxy` ON `pl`.`id` = `galaxy`.`{$Query_GetTarget_Galaxy}` ";
        $Query_GetTarget .= "WHERE `pl`.`galaxy` = {$Galaxy} AND `pl`.`system` = {$System} AND `pl`.`planet` = {$Planet} AND `pl`.`planet_type` = {$Type} ";
        $Query_GetTarget .= "LIMIT 1; -- GalaxyFleet|GetTarget";
        $TargetPlanet = doquery($Query_GetTarget, 'planets', true);

        $GalaxyRow['galaxy_id'] = $TargetPlanet['galaxy_id'];
        $TargetUser = $TargetPlanet['id_owner'];
        $TargetID = $TargetPlanet['id'];
        $TargetPlanetType = $Type;

        if($TargetID <= 0)
        {
            CreateReturn('614');
        }
        if($TargetUser == $_User['id'])
        {
            CreateReturn('615');
        }

        $protection = $_GameConfig['noobprotection'];
        $protectiontime = $_GameConfig['noobprotectiontime'];
        $protectionmulti = $_GameConfig['noobprotectionmulti'];
        $adminprotection = $_GameConfig['adminprotection'];
        $allyprotection = $_GameConfig['allyprotection'];
        $noNoobProtect = $_GameConfig['no_noob_protect'];
        $noIdleProtect = $_GameConfig['no_idle_protect'];
        $Protections['idleTime'] = $_GameConfig['no_idle_protect'] * TIME_DAY;

        if($TargetUser > 0)
        {
            $Query_GetUser = '';
            $Query_GetUser .= "SELECT `usr`.`ally_id`, `usr`.`is_onvacation`, `usr`.`is_banned`, `usr`.`onlinetime`, `usr`.`authlevel`, `usr`.`first_login`, `usr`.`NoobProtection_EndTime`, ";
            $Query_GetUser .= "`stat`.`total_points`, `stat`.`total_rank` ";
            $Query_GetUser .= "FROM {{table}} as `usr` ";
            $Query_GetUser .= "LEFT JOIN `{{prefix}}statpoints` AS `stat` ON `stat`.`stat_type` = 1 AND `stat`.`id_owner` = `usr`.`id` ";
            $Query_GetUser .= "WHERE `id` = {$TargetUser} LIMIT 1;";
            $HeDBRec = doquery($Query_GetUser, 'users', true);

            $SaveMyTotalRank = false;
            if(!CheckAuth('programmer'))
            {
                $MyGameLevel = $_User['total_points'];
            }
            else
            {
                $MyGameLevel = $HeDBRec['total_points'];
                if($_User['total_rank'] <= 0)
                {
                    $SaveMyTotalRank = $_User['total_rank'];
                    $_User['total_rank'] = $HeDBRec['total_rank'];
                }
            }
            $HeGameLevel = $HeDBRec['total_points'];

            if($allyprotection == 1 AND $_User['ally_id'] > 0 AND $_User['ally_id'] == $HeDBRec['ally_id'])
            {
                CreateReturn('616');
            }
            if((CheckAuth('supportadmin') OR CheckAuth('supportadmin', AUTHCHECK_NORMAL, $HeDBRec)) AND $adminprotection == 1)
            {
                if(CheckAuth('supportadmin'))
                {
                    CreateReturn('623');
                }
                else
                {
                    CreateReturn('625');
                }
            }
            if(isOnVacation($HeDBRec))
            {
                if($HeDBRec['is_banned'] == 1)
                {
                    CreateReturn('617');
                }
                else
                {
                    CreateReturn('618');
                }
            }

            if($protection == 1)
            {
                if($_User['total_rank'] < 1)
                {
                    CreateReturn('631');
                }
                if($HeDBRec['total_rank'] < 1)
                {
                    CreateReturn('630');
                }

                if($_User['NoobProtection_EndTime'] > $Time)
                {
                    CreateReturn('632'); // You are under Newcommer protection
                }
                else if($HeDBRec['first_login'] == 0)
                {
                    CreateReturn('634'); // Newcommer protection (never logged in)
                }
                else if($HeDBRec['NoobProtection_EndTime'] > $Time)
                {
                    CreateReturn('633'); // Newcommer protection
                }

                if($HeDBRec['onlinetime'] >= ($Time - (TIME_DAY * $noIdleProtect)))
                {
                    if($HeGameLevel < ($protectiontime * 1000))
                    {
                        CreateReturn('619'); //Player under n00b protection time
                    }
                    else if($MyGameLevel < ($protectiontime * 1000))
                    {
                        CreateReturn('620'); //You are under n00b protection time
                    }
                    else
                    {
                        if($MyGameLevel < ($noNoobProtect * 1000) OR $HeGameLevel < ($noNoobProtect * 1000))
                        {
                            if(($MyGameLevel > ($HeGameLevel * $protectionmulti)))
                            {
                                CreateReturn('621'); //Player is too weak
                            }
                            else if(($MyGameLevel * $protectionmulti) < $HeGameLevel)
                            {
                                CreateReturn('622'); //Player is too strony
                            }
                        }
                    }
                }
            }
            if($SaveMyTotalRank !== false)
            {
                $_User['total_rank'] = $SaveMyTotalRank;
            }
        }
        break;
    }
    case 8:
    {
        //Recycling
        $ShipID = 209;
        if($Type != 2)
        {
            CreateReturn('612');
        }

        $GalaxyRow = doquery("SELECT `galaxy_id`, `metal`,`crystal` FROM {{table}} WHERE `galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} LIMIT 1;", 'galaxy', true);
        if(!($GalaxyRow['metal'] > 0 OR $GalaxyRow['crystal'] > 0))
        {
            CreateReturn('611');
        }
        $ShipCount = ceil(($GalaxyRow['metal'] + $GalaxyRow['crystal']) / $_Vars_Prices[$ShipID]['capacity']);
        break;
    }
    case 7:
    {
        //Colonization
        $ShipID = 208;
        if($Type != 1)
        {
            CreateReturn('612');
        }

        $PlanetCheck = doquery("SELECT `id` FROM {{table}} WHERE `galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} AND `planet_type` = 1 LIMIT 1;", 'planets', true);
        if($PlanetCheck['id'] > 0)
        {
            CreateReturn('624');
        }
        $ShipCount = 1;
        break;
    }
}

// Fleet Blockade System
$BlockFleet = false;
$SFBSelectWhere[] = "(`Type` = 1 AND (`EndTime` > UNIX_TIMESTAMP() OR `PostEndTime` > UNIX_TIMESTAMP()))";
if($Mission == 6)
{
    if($TargetUser > 0)
    {
        $SFBSelectWhere[] = "(`Type` = 2 AND `ElementID` = {$TargetUser} AND `EndTime` > UNIX_TIMESTAMP())";
    }
    $SFBSelectWhere[] = "(`Type` = 3 AND `ElementID` = {$TargetID} AND `EndTime` > UNIX_TIMESTAMP())";
}
$SFBSelectWhere[] = "(`Type` = 2 AND `ElementID` = {$_User['id']} AND `EndTime` > UNIX_TIMESTAMP())";
$SFBSelectWhere[] = "(`Type` = 3 AND `ElementID` = {$CurrentPlanet['id']} AND `EndTime` > UNIX_TIMESTAMP())";

$SFBSelect = '';
$SFBSelect .= "SELECT `Type`, `BlockMissions`, `Reason`, `StartTime`, `EndTime`, `PostEndTime`, `ElementID`, `DontBlockIfIdle` FROM {{table}} WHERE `StartTime` <= UNIX_TIMESTAMP() AND ";
$SFBSelect .= implode(' OR ', $SFBSelectWhere);
$SFBSelect .= " ORDER BY `Type` ASC, `EndTime` DESC;";

$SQLResult_SmartFleetBlockadeData = doquery($SFBSelect, 'smart_fleet_blockade');

if($SQLResult_SmartFleetBlockadeData->num_rows > 0)
{
    while($GetSFBData = $SQLResult_SmartFleetBlockadeData->fetch_assoc())
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
                if($GetSFBData['EndTime'] > $Time)
                {
                    // Normal Blockade
                    if(!($GetSFBData['DontBlockIfIdle'] == 1 AND in_array($Mission, $_Vars_FleetMissions['military']) AND $TargetUser > 0 AND $HeDBRec['onlinetime'] <= ($Time - $Protections['idleTime'])))
                    {
                        CreateReturn('628');
                    }
                }
                else if($GetSFBData['PostEndTime'] > $Time)
                {
                    // Post Blockade
                    if(in_array($Mission, $_Vars_FleetMissions['military']) AND $TargetUser > 0 AND
                    (
                        ($AllMissionsBlocked !== true AND $HeDBRec['onlinetime'] > ($Time - $Protections['idleTime']) AND $HeDBRec['onlinetime'] < $GetSFBData['StartTime'])
                        OR
                        ($AllMissionsBlocked === true AND $HeDBRec['onlinetime'] > ($Time - $Protections['idleTime']) AND $HeDBRec['onlinetime'] < $GetSFBData['EndTime'])
                    ))
                    {
                        CreateReturn('635');
                    }
                }
            }
            else if($GetSFBData['Type'] == 2)
            {
                // Per User Blockade
                if($GetSFBData['ElementID'] == $_User['id'])
                {
                    CreateReturn('636');
                }
                else
                {
                    CreateReturn('637');
                }
            }
            else if($GetSFBData['Type'] == 3)
            {
                // Per Planet Blockade
                if($GetSFBData['ElementID'] == $CurrentPlanet['id'])
                {
                    // SFB per Planet (your) is Active
                    if($CurrentPlanet['planet_type'] == 1)
                    {
                        CreateReturn('638');
                    }
                    else
                    {
                        CreateReturn('639');
                    }
                }
                else
                {
                    // SFB per Planet (target) is Active
                    if($TargetPlanetType == 1)
                    {
                        CreateReturn('640');
                    }
                    else
                    {
                        CreateReturn('641');
                    }
                }
            }
        }
    }
}

if($ShipCount < 0)
{
    CreateReturn('605');
}
if($ShipCount == 0)
{
    $Update = 1;
    CreateReturn('610');
}
if($CurrentPlanet[$_Vars_GameElements[$ShipID]] <= 0)
{
    //No ships
    switch($Mission)
    {
        case 6:
        {
            //Spy
            $Return = '606_1';
            break;
        }
        case 8:
        {
            //Recycling
            $Return = '606_2';
            break;
        }
        case 7:
        {
            //Colonization
            $Return = '606_3';
            break;
        }
    }
    $Update = '1';
    CreateReturn($Return);
}

if($CurrentPlanet[$_Vars_GameElements[$ShipID]] < $ShipCount)
{
    $ShipCount = $CurrentPlanet[$_Vars_GameElements[$ShipID]];
}

// Create SpeedsArray
$SpeedsAvailable = array(10, 9, 8, 7, 6, 5, 4, 3, 2, 1);

if($_User['admiral_time'] > $Time)
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
arsort($SpeedsAvailable);
reset($SpeedsAvailable);

$GenFleetSpeed = current($SpeedsAvailable);
$SpeedFactor = getUniFleetsSpeedFactor();
$MaxFleetSpeed = getShipsCurrentSpeed($ShipID, $_User);

if(MORALE_ENABLED)
{
    if($_User['morale_level'] <= MORALE_PENALTY_FLEETSLOWDOWN)
    {
        $MaxFleetSpeed *= MORALE_PENALTY_FLEETSLOWDOWN_VALUE;
    }
}

$distance = getFlightDistanceBetween(
    $CurrentPlanet,
    [
        'galaxy' => $Galaxy,
        'system' => $System,
        'planet' => $Planet
    ]
);
$duration = getFlightDuration([
    'speedFactor' => $GenFleetSpeed,
    'distance' => $distance,
    'maxShipsSpeed' => $MaxFleetSpeed
]);
$consumption = getFlightTotalConsumption(
    [
        'ships' => [
            $ShipID => $ShipCount
        ],
        'distance' => $distance,
        'duration' => $duration,
    ],
    $_User
);

$fleet['start_time'] = $duration + $Time;
$fleet['end_time'] = (2 * $duration) + $Time;

if($CurrentPlanet['deuterium'] >= $consumption)
{
    $FleetStorage = $_Vars_Prices[$ShipID]['capacity'] * $ShipCount;
    if($Mission == 6)
    {
        // Try to SlowDown fleet only if it's Espionage Mission
        while($FleetStorage < $consumption)
        {
            $GenFleetSpeed = next($SpeedsAvailable);
            if($GenFleetSpeed !== false)
            {
                $duration = getFlightDuration([
                    'speedFactor' => $GenFleetSpeed,
                    'distance' => $distance,
                    'maxShipsSpeed' => $MaxFleetSpeed
                ]);
                $consumption = getFlightTotalConsumption(
                    [
                        'ships' => [
                            $ShipID => $ShipCount
                        ],
                        'distance' => $distance,
                        'duration' => $duration,
                    ],
                    $_User
                );

                $fleet['start_time'] = $duration + $Time;
                $fleet['end_time'] = (2 * $duration) + $Time;
            }
            else
            {
                break;
            }
        }
    }

    if($FleetStorage >= $consumption)
    {
        switch($Mission)
        {
            case 6: //Spy
                $TargetOwner = $TargetUser;
                break;
            case 8: //Recycling
                $TargetOwner = 0;
                break;
            case 7: //Colonization
                $TargetOwner = 0;
                break;
        }
    }
    else
    {
        CreateReturn('608');
    }
}
else
{
    CreateReturn('607');
}

if(!isset($TargetID) || $TargetID <= 0)
{
    $TargetID = '0';
}
if(empty($GalaxyRow['galaxy_id']))
{
    $GalaxyRow['galaxy_id'] = '0';
}

$FleetArray[$ShipID] = $ShipCount;
$FleetArrayQuery = Array2String($FleetArray);

$QryInsertFleet = '';
$QryInsertFleet .= "INSERT INTO {{table}} SET ";
$QryInsertFleet .= "`fleet_owner` = '{$_User['id']}', ";
$QryInsertFleet .= "`fleet_mission` = '{$Mission}', ";
$QryInsertFleet .= "`fleet_amount` = '{$ShipCount}', ";
$QryInsertFleet .= "`fleet_array` = '{$FleetArrayQuery}', ";
$QryInsertFleet .= "`fleet_start_time` = '{$fleet['start_time']}', ";
$QryInsertFleet .= "`fleet_start_id` = {$CurrentPlanet['id']}, ";
$QryInsertFleet .= "`fleet_start_galaxy` = '{$CurrentPlanet['galaxy']}', ";
$QryInsertFleet .= "`fleet_start_system` = '{$CurrentPlanet['system']}', ";
$QryInsertFleet .= "`fleet_start_planet` = '{$CurrentPlanet['planet']}', ";
$QryInsertFleet .= "`fleet_start_type` = '{$CurrentPlanet['planet_type']}', ";
$QryInsertFleet .= "`fleet_end_time` = '{$fleet['end_time']}', ";
$QryInsertFleet .= "`fleet_end_id` = {$TargetID}, ";
$QryInsertFleet .= "`fleet_end_id_galaxy` = {$GalaxyRow['galaxy_id']}, ";
$QryInsertFleet .= "`fleet_end_stay` = '0', ";
$QryInsertFleet .= "`fleet_end_galaxy` = '{$Galaxy}', ";
$QryInsertFleet .= "`fleet_end_system` = '{$System}', ";
$QryInsertFleet .= "`fleet_end_planet` = '{$Planet}', ";
$QryInsertFleet .= "`fleet_end_type` = '{$Type}', ";
$QryInsertFleet .= "`fleet_resource_metal` = '0', ";
$QryInsertFleet .= "`fleet_resource_crystal` = '0', ";
$QryInsertFleet .= "`fleet_resource_deuterium` = '0', ";
$QryInsertFleet .= "`fleet_target_owner` = '{$TargetOwner}', ";
$QryInsertFleet .= "`fleet_send_time` = UNIX_TIMESTAMP();";
doquery($QryInsertFleet, 'fleets');

$LastFleetID = doquery("SELECT LAST_INSERT_ID() as `id`;", '', true);
$LastFleetID = $LastFleetID['id'];

$QryArchive = '';
$QryArchive .= "INSERT INTO {{table}} SET ";
$QryArchive .= "`Fleet_ID` = {$LastFleetID}, ";
$QryArchive .= "`Fleet_Owner` = {$_User['id']}, ";
$QryArchive .= "`Fleet_Mission` = {$Mission}, ";
$QryArchive .= "`Fleet_Array` = '{$FleetArrayQuery}', ";
$QryArchive .= "`Fleet_Time_Send` = UNIX_TIMESTAMP(), ";
$QryArchive .= "`Fleet_Time_Start` = {$fleet['start_time']}, ";
$QryArchive .= "`Fleet_Time_End` = {$fleet['end_time']}, ";
$QryArchive .= "`Fleet_Start_ID` = {$CurrentPlanet['id']}, ";
$QryArchive .= "`Fleet_Start_Galaxy` = {$CurrentPlanet['galaxy']}, ";
$QryArchive .= "`Fleet_Start_System` = {$CurrentPlanet['system']}, ";
$QryArchive .= "`Fleet_Start_Planet` = {$CurrentPlanet['planet']}, ";
$QryArchive .= "`Fleet_Start_Type` = {$CurrentPlanet['planet_type']}, ";
$QryArchive .= "`Fleet_End_ID` = '{$TargetID}', ";
$QryArchive .= "`Fleet_End_ID_Galaxy` = '{$GalaxyRow['galaxy_id']}', ";
$QryArchive .= "`Fleet_End_Galaxy` = {$Galaxy}, ";
$QryArchive .= "`Fleet_End_System` = {$System}, ";
$QryArchive .= "`Fleet_End_Planet` = {$Planet}, ";
$QryArchive .= "`Fleet_End_Type` = {$Type}, ";
$QryArchive .= "`Fleet_End_Owner` = '{$TargetOwner}' ";
doquery($QryArchive, 'fleet_archive');

$CurrentPlanet['deuterium'] = $CurrentPlanet['deuterium'] - $consumption;

$QryUpdatePlanet = '';
$QryUpdatePlanet .= "UPDATE {{table}} SET ";
$QryUpdatePlanet .= "`{$_Vars_GameElements[$ShipID]}` = `{$_Vars_GameElements[$ShipID]}` - {$ShipCount}, ";
$QryUpdatePlanet .= "`deuterium` = '{$CurrentPlanet["deuterium"]}' ";
$QryUpdatePlanet .= "WHERE ";
$QryUpdatePlanet .= "`id` = '{$CurrentPlanet['id']}'";

doquery("LOCK TABLE {{table}} WRITE", 'planets');
doquery($QryUpdatePlanet, "planets");
doquery("UNLOCK TABLES", '');

// User Development Log
if($consumption > 0)
{
    $FleetArray['F'] = $consumption;
}
$FleetArrayDevLog = Array2String($FleetArray);
$UserDev_Log[] = array('PlanetID' => $CurrentPlanet['id'], 'Date' => $Time, 'Place' => 9, 'Code' => $Mission, 'ElementID' => $LastFleetID, 'AdditionalData' => $FleetArrayDevLog);

$ActualFleets += 1;
switch($Mission)
{
    case 6:
    {
        //Spy
        $Spy_Probes -= $ShipCount;
        $Return = '600_1'; //OK
        break;
    }
    case 8:
    {
        //Recycling
        $Recyclers -= $ShipCount;
        $Return = '600_2'; //OK
        break;
    }
    case 7:
    {
        //Colonization
        $Colonizers -= $ShipCount;
        $Return = '600_3'; //OK
        break;
    }
}
$Update = '1';
if(empty($Return))
{
    $Return = '694';
}
CreateReturn($Return);

?>
