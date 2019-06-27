<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(empty($_BenchTool))
{
    include('../includes/phpBench.php');
}
$Bench = new phpBench;
$Bench->showSimpleCountSwitch();
$Bench->echoTableSwitch();

// Load StatFunctions
include($_EnginePath.'admin/autostatbuilder_functions.php');

// Init Time
$StatDate = time();
includeLang('admin');
includeLang('admin/autostatbuilder');

$isAuthorised = false;

if (
    isset($_User['id']) &&
    $_User['id'] > 0 &&
    CheckAuth('programmer')
) {
    $isAuthorised = true;
}

if (
    (
        !isset($_User['id']) ||
        $_User['id'] <= 0
    ) &&
    !empty(AUTOTOOL_STATBUILDER_PASSWORDHASH) &&
    !empty($_GET['pass']) &&
    md5($_GET['pass']) == AUTOTOOL_STATBUILDER_PASSWORDHASH
) {
    $isAuthorised = true;
}

if (!$isAuthorised) {
    AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);

    die();
}

$CounterNames[] = 'Init & Delete old stats';
$Bench->simpleCountStart();

// Initialization
$StartTime = microtime(true);

$Users = array();
$Allys = array();

$UsersBuilds    = array();
$UsersDefs        = array();
$UsersFleets    = array();
$UsersTech        = array();
$UsersTotal        = array();

$AllysBuilds    = array();
$AllysDefs        = array();
$AllysFleets    = array();
$AllysTech        = array();
$AllysTotal        = array();
$DailyStatsDiff = $StatDate - $_GameConfig['last_stats_daily'];
$Loop            = 0;

$ForceDailyStats    = (isset($_GET['force_yesterday']) && $_GET['force_yesterday'] == 'true' ? true : false);
$ShowOutput            = (isset($_User['id']) && $_User['id'] > 0 ? true : false);
//END-OF-Initialization

/////////// USERS ///////////

// Techs needen fields
foreach($_Vars_ElementCategories['tech'] as $ID)
{
    if(!empty($_Vars_GameElements[$ID]))
    {
        $CreateTechFieldsList[] = "`user`.`{$_Vars_GameElements[$ID]}`";
    }
}
// Planet needen fields
$CreatePlanetFieldsList[] = 'id';
$CreatePlanetFieldsList[] = 'id_owner';
foreach($_Vars_ElementCategories as $ResType => $ResLists)
{
    if($ResType != 'build' AND $ResType != 'defense' AND $ResType != 'fleet')
    {
        continue;
    }
    foreach($ResLists as $ID)
    {
        $CreatePlanetFieldsList[] = "`{$_Vars_GameElements[$ID]}`";
    }
}

// Needen fields
$CreateFleetFieldsList    = "`fleet_owner`, `fleet_array`";
$CreateStatFieldsList    = "`id_owner`, `total_rank`, `tech_rank`, `build_rank`, `defs_rank`, `fleet_rank`, `tech_yesterday_rank`, `build_yesterday_rank`, `defs_yesterday_rank`, `fleet_yesterday_rank`, `total_yesterday_rank`";
$CreateTechFieldsList    = implode(', ', $CreateTechFieldsList);
$CreatePlanetFieldsList = implode(', ', $CreatePlanetFieldsList);
$UserNeedenFields        = "`user`.`id`, `user`.`username`, `user`.`ally_id`, `is_ondeletion`, `deletion_endtime`, `pro_time`, `is_onvacation`, `vacation_endtime`, `is_banned`, `ban_endtime`, {$CreateTechFieldsList}";

$Bench->simpleCountStop();
$CounterNames[] = 'User Stats Select';
$Bench->simpleCountStart();

$CounterNames[] = '> Getting UsersData';
$Bench->simpleCountStart();

$SQLResult_GameUsers = doquery(
    "SELECT {$UserNeedenFields} FROM {{table}} as `user` WHERE `user`.`authlevel` = 0 ORDER BY `user`.`id` ASC;",
    'users'
);

if($SQLResult_GameUsers->num_rows <= 0)
{
    AdminMessage('No Users Found', 'StatBuilder');
}

$Bench->simpleCountStop();
$CounterNames[] = '> Getting StatPoints';
$Bench->simpleCountStart();

$SQLResult_UsersStats = doquery(
    "SELECT {$CreateStatFieldsList} FROM {{table}} WHERE `stat_type` = 1;",
    'statpoints'
);

$Bench->simpleCountStop();
$CounterNames[] = '> Getting PlanetsData';
$Bench->simpleCountStart();

$SQLResult_AllPlanets = doquery(
    "SELECT {$CreatePlanetFieldsList} FROM {{table}};",
    'planets'
);

$Bench->simpleCountStop();
$CounterNames[] = '> Getting FleetsData';
$Bench->simpleCountStart();

$SQLResult_AllFleets= doquery(
    "SELECT {$CreateFleetFieldsList} FROM {{table}};",
    'fleets'
);

$Bench->simpleCountStop();
$CounterNames[] = '> Parsing PlanetsData';
$Bench->simpleCountStart();

while($PlanetsData = $SQLResult_AllPlanets->fetch_assoc())
{
    $Planets[$PlanetsData['id_owner']][] = $PlanetsData;
}

$Bench->simpleCountStop();
$CounterNames[] = '> Parsing FleetsData';
$Bench->simpleCountStart();

while($FleetsData = $SQLResult_AllFleets->fetch_assoc())
{
    $Fleets[$FleetsData['fleet_owner']][] = $FleetsData;
}

$Bench->simpleCountStop();
$CounterNames[] = '> Parsing StatsData';
$Bench->simpleCountStart();

while($StatsData = $SQLResult_UsersStats->fetch_assoc())
{
    $Stats[$StatsData['id_owner']] = $StatsData;
}
$Bench->simpleCountStop();

$Bench->simpleCountStop();
$CounterNames[] = 'User Stats Calculation';
$Bench->simpleCountStart();

$OnlyOnce = true;

while($CurUser = $SQLResult_GameUsers->fetch_assoc())
{
    // Delete User with DeletionRequest
    if($CurUser['is_ondeletion'] == 1 AND $CurUser['deletion_endtime'] < $StatDate)
    {
        $UsersToDelete[] = $CurUser['id'];
        continue;
    }
    if(!empty($CurUser['activation_code']))
    {
        continue;
    }

    // Remove "too long" Vacation Users
    if(isOnVacation($CurUser) AND $CurUser['vacation_endtime'] != 0 AND $CurUser['vacation_endtime'] < $StatDate)
    {
        $UsersToRemoveVacations[] = $CurUser['id'];
    }

    // Remove elapsed Bans
    if($CurUser['is_banned'] == 1 AND $CurUser['ban_endtime'] < $StatDate)
    {
        $UsersToRemoveBan[] = $CurUser['id'];
    }

    if($OnlyOnce)
    {
        $CounterNames[] = '> Set OldData';
        $Bench->simpleCountStart();
    }
    $CurrentUserDefense = array();
    $CurrentUserFleet    = array();

    if(!isset($Stats[$CurUser['id']]))
    {
        $OldTotalRank    = 0;
        $OldTechRank    = 0;
        $OldBuildRank    = 0;
        $OldDefsRank    = 0;
        $OldFleetRank    = 0;
    }
    else
    {
        $OldTotalRank    = $Stats[$CurUser['id']]['total_rank'];
        $OldTechRank    = $Stats[$CurUser['id']]['tech_rank'];
        $OldBuildRank    = $Stats[$CurUser['id']]['build_rank'];
        $OldDefsRank    = $Stats[$CurUser['id']]['defs_rank'];
        $OldFleetRank    = $Stats[$CurUser['id']]['fleet_rank'];
    }

    if($OnlyOnce)
    {
        $Bench->simpleCountStop();
        $CounterNames[] = '> Calc TechnoPoints';
        $Bench->simpleCountStart();
    }

    $Points            = GetTechnoPoints($CurUser);
    $TTechCount        = $Points['TechCount'];
    $TTechPoints    = ($Points['TechPoint'] / $_GameConfig['stat_settings']);

    // Get Tech Records
    if($Points['TechCount'] > 0)
    {
        foreach($Points['TechArr'] as $TechID => $Level)
        {
            if(!isset($TechRecords[$TechID]['lvl']) || $Level > $TechRecords[$TechID]['lvl'])
            {
                $TechRecords[$TechID] = array('lvl' => $Level, 'user' => $CurUser['id']);
            }
        }
    }

    $TBuildCount    = 0;
    $TBuildPoints    = 0;
    $TDefsCount        = 0;
    $TDefsPoints    = 0;
    $TFleetCount    = 0;
    $TFleetPoints    = 0;
    $GCount            = $TTechCount;
    $GPoints        = $TTechPoints;

    if($OnlyOnce)
    {
        $Bench->simpleCountStop();
        $CounterNames[] = '> Calc Planets Points';
        $Bench->simpleCountStart();
    }

    if(!empty($Planets[$CurUser['id']]))
    {
        foreach($Planets[$CurUser['id']] as $PlanetKey => $CurPlanet)
        {
            if($CurPlanet['id'] > 0)
            {
                // Calculate BuildingPoints
                $Points            = GetBuildPoints($CurPlanet);
                $TBuildCount    += $Points['BuildCount'];
                $GCount            += $Points['BuildCount'];
                $PlanetPoints    = ($Points['BuildPoint'] / $_GameConfig['stat_settings']);
                $TBuildPoints    += $PlanetPoints;
                if($Points['BuildCount'] > 0)
                {
                    foreach($Points['BuildArr'] as $ID => $Level)
                    {
                        if(!isset($BuildingRecords[$ID]['lvl']) || $Level > $BuildingRecords[$ID]['lvl'])
                        {
                            $BuildingRecords[$ID] = array('lvl' => $Level, 'user' => $CurUser['id']);
                        }
                    }
                }

                // Calculate DefensePoints
                $Points            = GetDefensePoints($CurPlanet);
                $TDefsCount        += $Points['DefenseCount'];
                $GCount            += $Points['DefenseCount'];
                $DefsPoints        = ($Points['DefensePoint'] / $_GameConfig['stat_settings']);
                $PlanetPoints    += $DefsPoints;
                $TDefsPoints    += $DefsPoints;
                if($Points['DefenseCount'] > 0)
                {
                    foreach($Points['DefenseArr'] as $ID => $Count)
                    {
                        if(!isset($CurrentUserDefense[$ID]))
                        {
                            $CurrentUserDefense[$ID] = 0;
                        }
                        $CurrentUserDefense[$ID] += $Count;
                    }
                }

                // Calculate FleetPoints (on Planet)
                $Points            = GetFleetPoints($CurPlanet);
                $TFleetCount    += $Points['FleetCount'];
                $GCount            += $Points['FleetCount'];
                $FleetsPoints    = ($Points['FleetPoint'] / $_GameConfig['stat_settings']);
                $PlanetPoints    += $FleetsPoints;
                $TFleetPoints    += $FleetsPoints;
                if($Points['FleetCount'] > 0)
                {
                    foreach($Points['FleetArr'] as $ID => $Count)
                    {
                        if(!isset($CurrentUserFleet[$ID]))
                        {
                            $CurrentUserFleet[$ID] = 0;
                        }
                        $CurrentUserFleet[$ID] += $Count;
                    }
                }
                $GPoints        += $PlanetPoints;

                if($PlanetPoints == 0)
                {
                    $PlanetPoints = '0';
                }
                $PlanetsUpdate[] = '('.$CurPlanet['id'].', '.$PlanetPoints.')';
            }
            unset($Planets[$CurUser['id']][$PlanetKey]);
        }

        if(!empty($CurrentUserDefense))
        {
            foreach($CurrentUserDefense as $ID => $Count)
            {
                if(!isset($DefenseRecords[$ID]['count']) || $Count > $DefenseRecords[$ID]['count'])
                {
                    $DefenseRecords[$ID] = array('count' => $Count, 'user' => $CurUser['id']);
                }
            }
        }
    }

    if($OnlyOnce)
    {
        $Bench->simpleCountStop();
        $CounterNames[] = '> Calc FleetInFlight Points';
        $Bench->simpleCountStart();
    }

    if(!empty($Fleets[$CurUser['id']]))
    {
        foreach($Fleets[$CurUser['id']] as $FleetKey => $CurFleet)
        {
            $Points            = GetFleetPointsOnTour($CurFleet['fleet_array']);
            $TFleetCount    += $Points['FleetCount'];
            $GCount            += $Points['FleetCount'];
            $FleetsPoints    = ($Points['FleetPoint'] / $_GameConfig['stat_settings']);
            $TFleetPoints    += $FleetsPoints;
            $GPoints        += $FleetsPoints;
            if($Points['FleetCount'] > 0)
            {
                foreach($Points['FleetArr'] as $ID => $Count)
                {
                    if(!isset($CurrentUserFleet[$ID]))
                    {
                        $CurrentUserFleet[$ID] = 0;
                    }
                    $CurrentUserFleet[$ID] += $Count;
                }
            }

            unset($Fleets[$CurUser['id']][$FleetKey]);
        }
    }

    if(!empty($CurrentUserFleet))
    {
        foreach($CurrentUserFleet as $ID => $Count)
        {
            if(!isset($FleetRecords[$ID]['count']) || $Count > $FleetRecords[$ID]['count'])
            {
                $FleetRecords[$ID] = array('count' => $Count, 'user' => $CurUser['id']);
            }
        }
    }

    if($OnlyOnce)
    {
        $Bench->simpleCountStop();
        $CounterNames[] = '> Set Data to Array';
        $Bench->simpleCountStart();
    }

    $Users[$Loop]['id']                        = $CurUser['id'];
    $Users[$Loop]['tech_points']            = $TTechPoints;
    $Users[$Loop]['tech_count']                = $TTechCount;
    $Users[$Loop]['tech_old_rank']            = $OldTechRank;
    $Users[$Loop]['build_points']            = $TBuildPoints;
    $Users[$Loop]['build_count']            = $TBuildCount;
    $Users[$Loop]['build_old_rank']            = $OldBuildRank;
    $Users[$Loop]['defs_points']            = $TDefsPoints;
    $Users[$Loop]['defs_count']                = $TDefsCount;
    $Users[$Loop]['defs_old_rank']            = $OldDefsRank;
    $Users[$Loop]['fleet_points']            = $TFleetPoints;
    $Users[$Loop]['fleet_count']            = $TFleetCount;
    $Users[$Loop]['fleet_old_rank']            = $OldFleetRank;
    $Users[$Loop]['total_points']            = $GPoints;
    $Users[$Loop]['total_count']            = $GCount;
    $Users[$Loop]['total_old_rank']            = $OldTotalRank;
    if(!isset($Stats[$CurUser['id']]))
    {
        $Users[$Loop]['tech_yesterday_rank']    = 0;
        $Users[$Loop]['build_yesterday_rank']    = 0;
        $Users[$Loop]['defs_yesterday_rank']    = 0;
        $Users[$Loop]['fleet_yesterday_rank']    = 0;
        $Users[$Loop]['total_yesterday_rank']    = 0;
    }
    else
    {
        $Users[$Loop]['tech_yesterday_rank']    = $Stats[$CurUser['id']]['tech_yesterday_rank'];
        $Users[$Loop]['build_yesterday_rank']    = $Stats[$CurUser['id']]['build_yesterday_rank'];
        $Users[$Loop]['defs_yesterday_rank']    = $Stats[$CurUser['id']]['defs_yesterday_rank'];
        $Users[$Loop]['fleet_yesterday_rank']    = $Stats[$CurUser['id']]['fleet_yesterday_rank'];
        $Users[$Loop]['total_yesterday_rank']    = $Stats[$CurUser['id']]['total_yesterday_rank'];
    }

    $UsersUpdate[] = "({$CurUser['id']}, ".(floor((($TTechPoints + $TBuildPoints) / 2) + $TFleetPoints + ($TDefsPoints * (3/4)))).")";

    if($CurUser['ally_id'] > 0)
    {
        if(!isset($AllyStats[$CurUser['ally_id']]))
        {
            $AllyStats[$CurUser['ally_id']]['TechPoint'] = 0;
            $AllyStats[$CurUser['ally_id']]['TechCount'] = 0;
            $AllyStats[$CurUser['ally_id']]['BuildPoint'] = 0;
            $AllyStats[$CurUser['ally_id']]['BuildCount'] = 0;
            $AllyStats[$CurUser['ally_id']]['DefsPoint'] = 0;
            $AllyStats[$CurUser['ally_id']]['DefsCount'] = 0;
            $AllyStats[$CurUser['ally_id']]['FleetPoint'] = 0;
            $AllyStats[$CurUser['ally_id']]['FleetCount'] = 0;
            $AllyStats[$CurUser['ally_id']]['TotalPoint'] = 0;
            $AllyStats[$CurUser['ally_id']]['TotalCount'] = 0;
        }
        $AllyStats[$CurUser['ally_id']]['TechPoint']    += $TTechPoints;
        $AllyStats[$CurUser['ally_id']]['TechCount']    += $TTechCount;
        $AllyStats[$CurUser['ally_id']]['BuildPoint']    += $TBuildPoints;
        $AllyStats[$CurUser['ally_id']]['BuildCount']    += $TBuildCount;
        $AllyStats[$CurUser['ally_id']]['DefsPoint']    += $TDefsPoints;
        $AllyStats[$CurUser['ally_id']]['DefsCount']    += $TDefsCount;
        $AllyStats[$CurUser['ally_id']]['FleetPoint']    += $TFleetPoints;
        $AllyStats[$CurUser['ally_id']]['FleetCount']    += $TFleetCount;
        $AllyStats[$CurUser['ally_id']]['TotalPoint']    += $GPoints;
        $AllyStats[$CurUser['ally_id']]['TotalCount']    += $GCount;
    }

    $UsersBuilds[$Loop] = $TBuildPoints;
    $UsersTech[$Loop]    = $TTechPoints;
    $UsersDefs[$Loop]    = $TDefsPoints;
    $UsersFleets[$Loop] = $TFleetPoints;
    $UsersTotal[$Loop]    = $GPoints;

    $Loop += 1;

    if($OnlyOnce)
    {
        $Bench->simpleCountStop();
        $OnlyOnce = false;
    }
}
$Bench->simpleCountStop();
$CounterNames[] = 'User Stats Insertion';
$Bench->simpleCountStart();

$CounterNames[] = '> Users Update';
$Bench->simpleCountStart();

$Query_UpdateUsers = '';
$Query_UpdateUsers .= "INSERT INTO {{table}} (`id`, `morale_points`) VALUES ";
$Query_UpdateUsers .= implode(',', $UsersUpdate);
$Query_UpdateUsers .= "ON DUPLICATE KEY UPDATE ";
$Query_UpdateUsers .= "`morale_points` = VALUES(`morale_points`);";
doquery($Query_UpdateUsers, 'users');
unset($UsersUpdate);
unset($Stats);

$Bench->simpleCountStop();

$CounterNames[] = '> Planets Update';
$Bench->simpleCountStart();

$QryPlanetsUpdate  = "INSERT INTO {{table}} (`id`, `points`) VALUES ";
$QryPlanetsUpdate .= implode(', ', $PlanetsUpdate);
$QryPlanetsUpdate .= "ON DUPLICATE KEY UPDATE ";
$QryPlanetsUpdate .= "`points` = VALUES(`points`);";
doquery($QryPlanetsUpdate, 'planets');
unset($QryPlanetsUpdate);
unset($PlanetsUpdate);
unset($Planets);

$Bench->simpleCountStop();
$CounterNames[] = '> Sorting tables';
$Bench->simpleCountStart();

arsort($UsersBuilds);
arsort($UsersTech);
arsort($UsersDefs);
arsort($UsersFleets);
arsort($UsersTotal);

$Bench->simpleCountStop();
$CounterNames[] = '> Creating rank numbers';
$Bench->simpleCountStart();
$Loop = 1;
foreach($UsersBuilds as $Key => $Val)
{
    $Users[$Key]['build_rank'] = $Loop;
    $Loop += 1;
}
unset($UsersBuilds);
$Loop = 1;
foreach($UsersTech as $Key => $Val)
{
    $Users[$Key]['tech_rank'] = $Loop;
    $Loop += 1;
}
unset($UsersTech);
$Loop = 1;
foreach($UsersDefs as $Key => $Val)
{
    $Users[$Key]['defs_rank'] = $Loop;
    $Loop += 1;
}
unset($UsersDefs);
$Loop = 1;
foreach($UsersFleets as $Key => $Val)
{
    $Users[$Key]['fleets_rank'] = $Loop;
    $Loop += 1;
}
unset($UsersFleets);
$Loop = 1;
foreach($UsersTotal as $Key => $Val)
{
    $Users[$Key]['total_rank'] = $Loop;
    $Loop += 1;
}
unset($UsersTotal);

$Bench->simpleCountStop();
$CounterNames[] = '> Deleting old Stats';
$Bench->simpleCountStart();
doquery("DELETE FROM {{table}} WHERE `stat_type` = '1';", 'statpoints');
$Bench->simpleCountStop();

if(count($Users) > 0)
{
    $CounterNames[] = '> Making Query';
    $Bench->simpleCountStart();

    $QryInsertUserStats = 'INSERT INTO {{table}} (`id_owner`, `stat_type`, `tech_rank`, `tech_old_rank`, `tech_yesterday_rank`, `tech_points`, `tech_count`, `build_rank`, `build_old_rank`, `build_yesterday_rank`, `build_points`, `build_count`, `defs_rank`, `defs_old_rank`, `defs_yesterday_rank`, `defs_points`, `defs_count`, `fleet_rank`, `fleet_old_rank`, `fleet_yesterday_rank`, `fleet_points`, `fleet_count`, `total_rank`, `total_old_rank`, `total_yesterday_rank`, `total_points`, `total_count`) VALUES ';
    foreach($Users as $Val)
    {
        if($Val['tech_old_rank'] <= 0)
        {
            $Val['tech_old_rank'] = $Val['tech_rank'];
        }
        if($Val['build_old_rank'] <= 0)
        {
            $Val['build_old_rank'] = $Val['build_rank'];
        }
        if($Val['defs_old_rank'] <= 0)
        {
            $Val['defs_old_rank'] = $Val['defs_rank'];
        }
        if($Val['fleet_old_rank'] <= 0)
        {
            $Val['fleet_old_rank'] = $Val['fleets_rank'];
        }
        if($Val['total_old_rank'] <= 0)
        {
            $Val['total_old_rank'] = $Val['total_rank'];
        }
        if($Val['tech_yesterday_rank'] <= 0)
        {
            $Val['tech_yesterday_rank'] = $Val['tech_old_rank'];
        }
        if($Val['build_yesterday_rank'] <= 0)
        {
            $Val['build_yesterday_rank'] = $Val['build_old_rank'];
        }
        if($Val['defs_yesterday_rank'] <= 0)
        {
            $Val['defs_yesterday_rank'] = $Val['defs_old_rank'];
        }
        if($Val['fleet_yesterday_rank'] <= 0)
        {
            $Val['fleet_yesterday_rank'] = $Val['fleet_old_rank'];
        }
        if($Val['total_yesterday_rank'] <= 0)
        {
            $Val['total_yesterday_rank'] = $Val['total_old_rank'];
        }

        foreach($Val as $key => $value)
        {
            if(empty($value))
            {
                $Val[$key] = '0';
            }
        }

        if($DailyStatsDiff > TIME_DAY OR $ForceDailyStats)
        {
            $Val['tech_yesterday_rank'] = $Val['tech_old_rank'];
            $Val['build_yesterday_rank']= $Val['build_old_rank'];
            $Val['defs_yesterday_rank'] = $Val['defs_old_rank'];
            $Val['fleet_yesterday_rank']= $Val['fleet_old_rank'];
            $Val['total_yesterday_rank']= $Val['total_old_rank'];
        }

        $CreateQryRow  = "({$Val['id']}, 1, ";
        $CreateQryRow .= "{$Val['tech_rank']}, {$Val['tech_old_rank']}, {$Val['tech_yesterday_rank']}, {$Val['tech_points']}, {$Val['tech_count']}, ";
        $CreateQryRow .= "{$Val['build_rank']}, {$Val['build_old_rank']}, {$Val['build_yesterday_rank']}, {$Val['build_points']}, {$Val['build_count']}, ";
        $CreateQryRow .= "{$Val['defs_rank']}, {$Val['defs_old_rank']}, {$Val['defs_yesterday_rank']}, {$Val['defs_points']}, {$Val['defs_count']}, ";
        $CreateQryRow .= "{$Val['fleets_rank']}, {$Val['fleet_old_rank']}, {$Val['fleet_yesterday_rank']}, {$Val['fleet_points']}, {$Val['fleet_count']}, ";
        $CreateQryRow .= "{$Val['total_rank']}, {$Val['total_old_rank']}, {$Val['total_yesterday_rank']}, {$Val['total_points']}, {$Val['total_count']}";
        $CreateQryRow .= ")";

        $QryInsertUserStatsArr[] = $CreateQryRow;
    }
    $QryInsertUserStats .= implode(', ', $QryInsertUserStatsArr).';';

    $Bench->simpleCountStop();
    $CounterNames[] = '> Sending Query';
    $Bench->simpleCountStart();

    doquery($QryInsertUserStats, 'statpoints');
    unset($QryInsertUserStatsArr);
    unset($QryInsertUserStats);
    unset($Users);

    $Bench->simpleCountStop();
}
$Bench->simpleCountStop();

$CounterNames[] = 'Allys data Calculation and Insertion';
$Bench->simpleCountStart();

///////////////////// ALLIANCES ////////////////////
$AllysNeedenFields = "{{prefix}}statpoints.total_rank, {{prefix}}statpoints.tech_rank, {{prefix}}statpoints.build_rank, {{prefix}}statpoints.defs_rank, {{prefix}}statpoints.fleet_rank, {{prefix}}statpoints.tech_yesterday_rank,{{prefix}}statpoints.build_yesterday_rank,{{prefix}}statpoints.defs_yesterday_rank,{{prefix}}statpoints.fleet_yesterday_rank,{{prefix}}statpoints.total_yesterday_rank";

$SQLResult_GameAllys = doquery(
    "SELECT {{table}}.*, {$AllysNeedenFields} FROM {{table}} LEFT JOIN {{prefix}}statpoints ON `stat_type` = 2 AND {{prefix}}statpoints.id_owner = {{table}}.id GROUP BY {{table}}.id",
    'alliance'
);

$Loop = 0;
while($CurAlly = $SQLResult_GameAllys->fetch_assoc())
{
    $OldTotalRank    = $CurAlly['total_rank'];
    $OldTechRank    = $CurAlly['tech_rank'];
    $OldBuildRank    = $CurAlly['build_rank'];
    $OldDefsRank    = $CurAlly['defs_rank'];
    $OldFleetRank    = $CurAlly['fleet_rank'];

    if(isset($AllyStats[$CurAlly['id']]))
    {
        $Points = $AllyStats[$CurAlly['id']];
    }
    else
    {
        $Points = array(
            'TechCount' => 0,
            'TechPoint' => 0,
            'BuildCount' => 0,
            'BuildPoint' => 0,
            'DefsCount' => 0,
            'DefsPoint' => 0,
            'FleetCount' => 0,
            'FleetPoint' => 0,
            'TotalCount' => 0,
            'TotalPoint' => 0
        );
    }

    $TTechCount        = $Points['TechCount'];
    $TTechPoints    = $Points['TechPoint'];
    $TBuildCount    = $Points['BuildCount'];
    $TBuildPoints    = $Points['BuildPoint'];
    $TDefsCount        = $Points['DefsCount'];
    $TDefsPoints    = $Points['DefsPoint'];
    $TFleetCount    = $Points['FleetCount'];
    $TFleetPoints    = $Points['FleetPoint'];
    $GCount            = $Points['TotalCount'];
    $GPoints        = $Points['TotalPoint'];

    $Allys[$Loop]['id']                        = $CurAlly['id'];
    $Allys[$Loop]['tech_points']            = $TTechPoints;
    $Allys[$Loop]['tech_count']                = $TTechCount;
    $Allys[$Loop]['tech_old_rank']            = $OldTechRank;
    $Allys[$Loop]['build_points']            = $TBuildPoints;
    $Allys[$Loop]['build_count']            = $TBuildCount;
    $Allys[$Loop]['build_old_rank']            = $OldBuildRank;
    $Allys[$Loop]['defs_points']            = $TDefsPoints;
    $Allys[$Loop]['defs_count']                = $TDefsCount;
    $Allys[$Loop]['defs_old_rank']            = $OldDefsRank;
    $Allys[$Loop]['fleet_points']            = $TFleetPoints;
    $Allys[$Loop]['fleet_count']            = $TFleetCount;
    $Allys[$Loop]['fleet_old_rank']            = $OldFleetRank;
    $Allys[$Loop]['total_points']            = $GPoints;
    $Allys[$Loop]['total_count']            = $GCount;
    $Allys[$Loop]['total_old_rank']            = $OldTotalRank;
    $Allys[$Loop]['tech_yesterday_rank']    = $CurAlly['tech_yesterday_rank'];
    $Allys[$Loop]['build_yesterday_rank']    = $CurAlly['build_yesterday_rank'];
    $Allys[$Loop]['defs_yesterday_rank']    = $CurAlly['defs_yesterday_rank'];
    $Allys[$Loop]['fleet_yesterday_rank']    = $CurAlly['fleet_yesterday_rank'];
    $Allys[$Loop]['total_yesterday_rank']    = $CurAlly['total_yesterday_rank'];

    $AllysBuilds[$Loop] = $TBuildPoints;
    $AllysTech[$Loop]    = $TTechPoints;
    $AllysDefs[$Loop]    = $TDefsPoints;
    $AllysFleets[$Loop] = $TFleetPoints;
    $AllysTotal[$Loop]    = $GPoints;

    $Loop += 1;
}

arsort($AllysBuilds);
arsort($AllysTech);
arsort($AllysDefs);
arsort($AllysFleets);
arsort($AllysTotal);
$Loop = 1;
foreach($AllysBuilds as $Key => $Val)
{
    $Allys[$Key]['build_rank'] = $Loop;
    $Loop += 1;
}
unset($AllysBuilds);
$Loop = 1;
foreach($AllysTech as $Key => $Val)
{
    $Allys[$Key]['tech_rank'] = $Loop;
    $Loop += 1;
}
unset($AllysTech);
$Loop = 1;
foreach($AllysDefs as $Key => $Val)
{
    $Allys[$Key]['defs_rank'] = $Loop;
    $Loop += 1;
}
unset($AllysDefs);
$Loop = 1;
foreach($AllysFleets as $Key => $Val)
{
    $Allys[$Key]['fleets_rank'] = $Loop;
    $Loop += 1;
}
unset($AllysFleets);
$Loop = 1;
foreach($AllysTotal as $Key => $Val)
{
    $Allys[$Key]['total_rank'] = $Loop;
    $Loop += 1;
}
unset($AllysTotal);

doquery("DELETE FROM {{table}} WHERE `stat_type` = '2';", 'statpoints');

if(count($Allys) > 0)
{
    $QryInsertAllyStats = 'INSERT INTO {{table}} (`id_owner`, `stat_type`, `tech_rank`, `tech_old_rank`, `tech_yesterday_rank`, `tech_points`, `tech_count`, `build_rank`, `build_old_rank`, `build_yesterday_rank`, `build_points`, `build_count`, `defs_rank`, `defs_old_rank`, `defs_yesterday_rank`, `defs_points`, `defs_count`, `fleet_rank`, `fleet_old_rank`, `fleet_yesterday_rank`, `fleet_points`, `fleet_count`, `total_rank`, `total_old_rank`, `total_yesterday_rank`, `total_points`, `total_count`) VALUES ';
    foreach($Allys as $Val)
    {
        if($Val['tech_old_rank'] <= 0)
        {
            $Val['tech_old_rank'] = $Val['tech_rank'];
        }
        if($Val['build_old_rank'] <= 0)
        {
            $Val['build_old_rank'] = $Val['build_rank'];
        }
        if($Val['defs_old_rank'] <= 0)
        {
            $Val['defs_old_rank'] = $Val['defs_rank'];
        }
        if($Val['fleet_old_rank'] <= 0)
        {
            $Val['fleet_old_rank'] = $Val['fleets_rank'];
        }
        if($Val['total_old_rank'] <= 0)
        {
            $Val['total_old_rank'] = $Val['total_rank'];
        }
        if($Val['tech_yesterday_rank'] <= 0)
        {
            $Val['tech_yesterday_rank'] = $Val['tech_old_rank'];
        }
        if($Val['build_yesterday_rank'] <= 0)
        {
            $Val['build_yesterday_rank'] = $Val['build_old_rank'];
        }
        if($Val['defs_yesterday_rank'] <= 0)
        {
            $Val['defs_yesterday_rank'] = $Val['defs_old_rank'];
        }
        if($Val['fleet_yesterday_rank'] <= 0)
        {
            $Val['fleet_yesterday_rank'] = $Val['fleet_old_rank'];
        }
        if($Val['total_yesterday_rank'] <= 0)
        {
            $Val['total_yesterday_rank'] = $Val['total_old_rank'];
        }

        foreach($Val as $key => $value)
        {
            if(empty($value))
            {
                $Val[$key] = '0';
            }
        }

        if($DailyStatsDiff > TIME_DAY OR $ForceDailyStats)
        {
            $Val['tech_yesterday_rank'] = $Val['tech_old_rank'];
            $Val['build_yesterday_rank'] = $Val['build_old_rank'];
            $Val['defs_yesterday_rank'] = $Val['defs_old_rank'];
            $Val['fleet_yesterday_rank'] = $Val['fleet_old_rank'];
            $Val['total_yesterday_rank'] = $Val['total_old_rank'];
        }

        $CreateQryRow  = "({$Val['id']}, 2, ";
        $CreateQryRow .= "{$Val['tech_rank']}, {$Val['tech_old_rank']}, {$Val['tech_yesterday_rank']}, {$Val['tech_points']}, {$Val['tech_count']}, ";
        $CreateQryRow .= "{$Val['build_rank']}, {$Val['build_old_rank']}, {$Val['build_yesterday_rank']}, {$Val['build_points']}, {$Val['build_count']}, ";
        $CreateQryRow .= "{$Val['defs_rank']}, {$Val['defs_old_rank']}, {$Val['defs_yesterday_rank']}, {$Val['defs_points']}, {$Val['defs_count']}, ";
        $CreateQryRow .= "{$Val['fleets_rank']}, {$Val['fleet_old_rank']}, {$Val['fleet_yesterday_rank']}, {$Val['fleet_points']}, {$Val['fleet_count']}, ";
        $CreateQryRow .= "{$Val['total_rank']}, {$Val['total_old_rank']}, {$Val['total_yesterday_rank']}, {$Val['total_points']}, {$Val['total_count']}";
        $CreateQryRow .= ")";

        $QryInsertAllyStatsArr[] = $CreateQryRow;
    }
    $QryInsertAllyStats .= implode(', ', $QryInsertAllyStatsArr).';';

    doquery($QryInsertAllyStats, 'statpoints');
    unset($QryInsertAllyStatsArr);
    unset($QryInsertAllyStats);
    unset($Allys);
}
$Bench->simpleCountStop();
$CounterNames[] = 'Records Calculation and Insertion';
$Bench->simpleCountStart();

//////////////// RECORDS /////////////////////
doquery("DELETE FROM {{table}};", 'records');
$records = array();
$Loop = 0;

foreach($_Vars_GameElements as $Element => $ElementName)
{
    if(!empty($ElementName))
    {
        $UserID = 0;
        $ElementCount = 0;
        $Allow = true;
        if(($Element >= 1 AND $Element <= 39) OR $Element == 44)
        {
            // Buildings
            $UserID = isset($BuildingRecords[$Element]['user']) ? $BuildingRecords[$Element]['user'] : null;
            $ElementCount = isset($BuildingRecords[$Element]['lvl']) ? $BuildingRecords[$Element]['lvl'] : null;
        }
        elseif($Element >= 41 AND $Element <= 99 AND $Element != 44 AND $Element != 50)
        {
            // Special buildings
            $UserID = isset($BuildingRecords[$Element]['user']) ? $BuildingRecords[$Element]['user'] : null;
            $ElementCount = isset($BuildingRecords[$Element]['lvl']) ? $BuildingRecords[$Element]['lvl'] : null;
        }
        elseif($Element >= 101 AND $Element <= 199)
        {
            // Technology
            $UserID = isset($TechRecords[$Element]['user']) ? $TechRecords[$Element]['user'] : null;
            $ElementCount = isset($TechRecords[$Element]['lvl']) ? $TechRecords[$Element]['lvl'] : null;
        }
        elseif($Element >= 201 AND $Element <= 399)
        {
            // Fleets
            $UserID = isset($FleetRecords[$Element]['user']) ? $FleetRecords[$Element]['user'] : null;
            $ElementCount = isset($FleetRecords[$Element]['lvl']) ? $FleetRecords[$Element]['lvl'] : null;
        }
        elseif($Element >= 401 AND $Element <= 599 AND $Element != 407 AND $Element != 408 AND $Element != 409)
        {
            // Defences (Excluded: Shields)
            $UserID = isset($DefenseRecords[$Element]['user']) ? $DefenseRecords[$Element]['user'] : null;
            $ElementCount = isset($DefenseRecords[$Element]['lvl']) ? $DefenseRecords[$Element]['lvl'] : null;
        }
        else
        {
            $Allow = false;
        }

        if($Allow)
        {
            if($UserID == 0)
            {
                $UserID = '0';
                $ElementCount = '0';
            }
            if($ElementCount == 0)
            {
                $ElementCount = '0';
                $UserID = '0';
            }
            $records[] = array('element' => $Element, 'user' => $UserID, 'count' => $ElementCount);
        }
    }
}

if(count($records) > 0)
{
    $QryInsertRecords = "INSERT INTO {{table}} (`id`, `id_owner`, `element`, `count`) VALUES ";
    foreach($records as $val)
    {
        $QryInsertRecordsArr[] = "(NULL, {$val['user']}, {$val['element']}, {$val['count']})";
    }
    $QryInsertRecords .= implode(', ', $QryInsertRecordsArr).';';

    doquery($QryInsertRecords, 'records');
    unset($QryInsertRecordsArr);
    unset($QryInsertRecords);
}
$Bench->simpleCountStop();
// -------------------------------------------------------------------------------------------
$CounterNames[] = 'Deleting Users';
$Bench->simpleCountStart();

// Delete all NonActivated Users which are in DataBase for at least 7 days
$SQLResult_SelectNonActivatedUsers = doquery(
    "SELECT `id` FROM {{table}} WHERE `activation_code` != '' AND `register_time` < ({$StatDate} - (".NONACTIVE_DELETETIME."));",
    'users'
);

while($NonActivated = $SQLResult_SelectNonActivatedUsers->fetch_assoc())
{
    $UsersToDelete[] = $NonActivated['id'];
}

if(!empty($UsersToDelete))
{
    include($_EnginePath.'includes/functions/DeleteSelectedUser.php');
    include($_EnginePath.'includes/functions/FleetControl_Retreat.php');
    DeleteSelectedUser($UsersToDelete);
}

$Bench->simpleCountStop();
$CounterNames[] = 'Removing VacationModes & Updating Banned PPL';
$Bench->simpleCountStart();

if(!empty($UsersToRemoveVacations))
{
    $UsersToRemoveVacations = implode(', ', $UsersToRemoveVacations);
    doquery("UPDATE {{table}} SET `is_onvacation` = '0', `vacation_starttime` = '0', `vacation_endtime` = '0', `vacation_leavetime` = IF(`vacation_type` = 2, 0, {$StatDate}) WHERE `id` IN ({$UsersToRemoveVacations});", 'users');
    doquery("UPDATE {{table}} SET `last_update` = {$StatDate} WHERE `id_owner` IN ({$UsersToRemoveVacations});", 'planets');
}
if(!empty($UsersToRemoveBan))
{
    $UsersToRemoveBan = implode(', ', $UsersToRemoveBan);
    doquery("UPDATE {{table}} SET `is_banned` = 0, `ban_endtime` = 0 WHERE `id` IN ({$UsersToRemoveBan});", 'users');
    doquery("UPDATE {{table}} SET `Active` = 0, `Expired` = 1 WHERE `Active` = 1 AND `UserID` IN ({$UsersToRemoveBan});", 'bans');
}

$Bench->simpleCountStop();

$CounterNames[] = 'DataBase Optimization';
$Bench->simpleCountStart();
if(($StatDate - $_GameConfig['last_db_optimization']) > (2 * TIME_HOUR))
{
    $TableList = array
    (
        'acs', 'alliance', 'buddy', 'declarations', 'errors', 'fleets',
        'galaxy', 'ignoresystem', 'notes', 'planets',
        'records', 'statpoints', 'users'
    );

    foreach($TableList as $key => $val)
    {
        $TableList[$key] = '{{prefix}}'.$val;
    }

    $TableList = implode(', ', $TableList);

    doquery("OPTIMIZE TABLE {$TableList}", '');
    doquery("UPDATE {{table}} SET `config_value` = {$StatDate} WHERE `config_name` = 'last_db_optimization';", 'config');
    $_GameConfig['last_db_optimization'] = $StatDate;
}
$Bench->simpleCountStop();
$CounterNames[] = 'Final Updates';
$Bench->simpleCountStart();
doquery("UPDATE {{table}} SET `config_value` = {$StatDate} WHERE `config_name` = 'last_update';", 'config');
$_GameConfig['last_update'] = $StatDate;

// Create LastUpdate CacheFile (for Signature Generator)
file_put_contents('../cache/data/last_stats_update.php', '<?php $LastStatsUpdate = '.time().'; ?>');

if($DailyStatsDiff > TIME_DAY)
{
    doquery("UPDATE {{table}} SET `config_value` = {$StatDate} WHERE `config_name` = 'last_stats_daily';", 'config');
    $_GameConfig['last_stats_daily'] = $StatDate;
}
$_MemCache->GameConfig = $_GameConfig;

$EndTime = microtime(true);
$Bench->simpleCountStop();
$CountTime = $Bench->ReturnSimpleCountArray();

$TotalTime = sprintf('%0.6f', $EndTime - $StartTime);

if($ShowOutput)
{
    $Counted = '<center><table><tbody>';
    foreach($CountTime as $Key => $Data)
    {
        if(strstr($CounterNames[$Key], '>') == true)
        {
            $LastWasAssoc = true;
        }
        else
        {
            if(isset($LastWasAssoc) && $LastWasAssoc === true)
            {
                $Counted .= '<tr style="visibility: hidden;"><th></th></tr>';
            }
            $LastWasAssoc = false;
        }
        $Counted .= '<tr><td class="c" style="text-align: right; '.(strstr($CounterNames[$Key], '>') == true ? 'color: orange;' : '').'">'.$CounterNames[$Key].'</td><td class="c"> '.sprintf('%0.20f', $Data['result']).'<br/>'.$Data['endram'].'</td></tr>';
    }
    $Counted .= '</tbody></table></center>';

    AdminMessage(sprintf($_Lang['AutoStatBuilder_BuildInfo'], $TotalTime, pretty_time($DailyStatsDiff, true), $DailyStatsDiff, $Counted), $_Lang['AutoStatBuilder_Title']);
}
else
{
    AdminMessage('OK', $_Lang['AutoStatBuilder_Title']);
}

?>
