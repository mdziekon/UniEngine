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

$StartTime = microtime(true);
includeLang('admin/cron_GarbageCollector');

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
    !empty(AUTOTOOL_GARBAGECOLLECTOR_PASSWORDHASH) &&
    !empty($_GET['pass']) &&
    md5($_GET['pass']) == AUTOTOOL_GARBAGECOLLECTOR_PASSWORDHASH
) {
    $isAuthorised = true;
}

if (!$isAuthorised) {
    AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);

    die();
}

$ShowOutput = (isset($_User['id']) && $_User['id'] > 0 ? true : false);

// Settings
$Now = time();

$DelLimit                        = 500;
$DelTime['spymsg']                = TIME_DAY;
$DelTime['sysmsg_deleted']        = TIME_DAY * 7;
$DelTime['sysmsg_nondeleted']    = TIME_DAY * 14;
$DelTime['usrmsg_deleted']        = TIME_DAY * 14;
$DelTime['usrmsg_nondeleted']    = TIME_DAY * 30;
$DelTime['simreport']            = 3600;
$DelTime['allyinvite_all']        = TIME_DAY * 14;
$DelTime['allyinvite_changed']    = TIME_DAY * 7;
$DelTime['loginprotection']        = LOGINPROTECTION_LOCKTIME;
$DelTime['planet_abandontime']    = PLANET_ABANDONTIME;

$Opt_Interval                    = TIME_HOUR;
// End of Settings

$Delete_Planets = [];
$Delete_Messages = [];
$Delete_PlanetsFromGalaxy = [];

foreach($DelTime as &$Value)
{
    $Value = $Now - $Value;
}

$Query_SelectMessages  = "SELECT `id`, `Thread_ID` FROM {{table}} WHERE ";
$Query_SelectMessages .= "(`type` = 0 AND `time` < {$DelTime['spymsg']}) OR ";
$Query_SelectMessages .= "(`type` IN (4, 5, 15, 50, 70) AND ((`deleted` = 1 AND `time` < {$DelTime['sysmsg_deleted']}) OR `time` < {$DelTime['sysmsg_nondeleted']})) OR ";
$Query_SelectMessages .= "(`type` IN (1, 2, 3, 80) AND ((`deleted` = 1 AND `time` < {$DelTime['usrmsg_deleted']}) OR `time` < {$DelTime['usrmsg_nondeleted']})) ";
$Query_SelectMessages .= "LIMIT {$DelLimit};";

$Query_SelectThreaded = "SELECT MAX(`id`) AS `id` FROM {{table}} WHERE `Thread_ID` IN ({_InsertIDs_}) AND `deleted` = false GROUP BY `Thread_ID`, `id_owner`;";

$Query_SelectPlanets = "SELECT `id` FROM {{table}} WHERE `abandon_time` > 0 AND `abandon_time` < {$DelTime['planet_abandontime']} AND `planet_type` = 1;";
$Query_SelectMoons = "SELECT `id_moon` FROM {{table}} WHERE `id_planet` IN ({_InsertIDs_}) LIMIT {_InsertCount_};";

$Query_DeleteMessages = "DELETE FROM {{table}} WHERE `id` IN ({_InsertIDs_}) LIMIT {_InsertCount_};";
$Query_DeleteSimReports = "DELETE FROM {{table}} WHERE `time` < {$DelTime['simreport']} LIMIT {$DelLimit};";
$Query_DeleteAllyInvites = "DELETE FROM {{table}} WHERE `Date` < {$DelTime['allyinvite_all']} OR (`State` != 0 AND `Date` < {$DelTime['allyinvite_changed']});";
$Query_DeleteLoginProtectionRows = "DELETE FROM {{table}} WHERE `Date` < {$DelTime['loginprotection']};";
$Query_DeletePlanets = "DELETE FROM {{table}} WHERE `id` IN ({_InsertIDs_}) LIMIT {_InsertCount_};";
$Query_DeletePlanetsOnGalaxy = "DELETE FROM {{table}} WHERE `id_planet` IN ({_InsertIDs_}) LIMIT {_InsertCount_};";

$Query_UpdateThreads = "UPDATE {{table}} SET `Thread_IsLast` = 1 WHERE `id` IN ({_InsertIDs_});";

// Preparations begin
$CounterNames[] = 'Preparing';
$Bench->simpleCountStart();

$CounterNames[] = '> Messages';
$Bench->simpleCountStart();

$SQLResult_GetMessages = doquery($Query_SelectMessages, 'messages');

$Update_Threads = array();
if($SQLResult_GetMessages->num_rows > 0)
{
    while($FetchData = $SQLResult_GetMessages->fetch_assoc())
    {
        $Delete_Messages[] = $FetchData['id'];
        if($FetchData['Thread_ID'] > 0)
        {
            if(!in_array($FetchData['Thread_ID'], $Update_Threads))
            {
                $Update_Threads[] = $FetchData['Thread_ID'];
            }
        }
    }
}
$Bench->simpleCountStop();

$CounterNames[] = '> Planets';
$Bench->simpleCountStart();

$SQLResult_GetPlanets = doquery($Query_SelectPlanets, 'planets');

if($SQLResult_GetPlanets->num_rows > 0)
{
    while($FetchData = $SQLResult_GetPlanets->fetch_assoc())
    {
        if($FetchData['id'] > 0)
        {
            $Delete_Planets[] = $FetchData['id'];
            $Delete_PlanetsFromGalaxy[] = $FetchData['id'];
        }
    }
    $Query_SelectMoons = str_replace(
        array('{_InsertIDs_}', '{_InsertCount_}'),
        array(implode(', ', $Delete_Planets), count($Delete_Planets)),
        $Query_SelectMoons
    );

    $SQLResult_GetDeletedMoons = doquery($Query_SelectMoons, 'galaxy');

    if($SQLResult_GetDeletedMoons->num_rows > 0)
    {
        while($FetchData = $SQLResult_GetDeletedMoons->fetch_assoc())
        {
            if($FetchData['id_moon'] > 0)
            {
                $Delete_Planets[] = $FetchData['id_moon'];
            }
        }
    }
}
$Bench->simpleCountStop();

$Bench->simpleCountStop();
// Preparations end

// Deletion begins
$CounterNames[] = 'Deleting';
$Bench->simpleCountStart();

$CounterNames[] = '> Messages';
$Bench->simpleCountStart();
if(!empty($Delete_Messages))
{
    $Query_DeleteMessages = str_replace(array('{_InsertIDs_}', '{_InsertCount_}'), array(implode(', ', $Delete_Messages), count($Delete_Messages)), $Query_DeleteMessages);

    doquery($Query_DeleteMessages, 'messages');

    $Stats['del']['msg'] = getDBLink()->affected_rows;
}
else
{
    $Stats['del']['msg'] = 0;
}
$Bench->simpleCountStop();

$CounterNames[] = '> SimReports';
$Bench->simpleCountStart();

doquery($Query_DeleteSimReports, 'sim_battle_reports');

$Stats['del']['simrep'] = getDBLink()->affected_rows;

$Bench->simpleCountStop();

$CounterNames[] = '> Ally Invites';
$Bench->simpleCountStart();

doquery($Query_DeleteAllyInvites, 'ally_invites');

$Stats['del']['allyinvite'] = getDBLink()->affected_rows;

$Bench->simpleCountStop();

$CounterNames[] = '> LoginProtection Rows';
$Bench->simpleCountStart();

doquery($Query_DeleteLoginProtectionRows, 'login_protection');

$Stats['del']['loginprotection'] = getDBLink()->affected_rows;

$Bench->simpleCountStop();

$CounterNames[] = '> Planets';
$Bench->simpleCountStart();

if(!empty($Delete_Planets))
{
    $Query_DeletePlanets = str_replace(
        array('{_InsertIDs_}', '{_InsertCount_}'),
        array(implode(', ', $Delete_Planets), count($Delete_Planets)),
        $Query_DeletePlanets
    );
    $Query_DeletePlanetsOnGalaxy = str_replace(
        array('{_InsertIDs_}', '{_InsertCount_}'),
        array(implode(', ', $Delete_PlanetsFromGalaxy), count($Delete_PlanetsFromGalaxy)),
        $Query_DeletePlanetsOnGalaxy
    );

    doquery($Query_DeletePlanets, 'planets');

    $Stats['del']['planets'] = getDBLink()->affected_rows;

    doquery($Query_DeletePlanetsOnGalaxy, 'galaxy');
}
else
{
    $Stats['del']['planets'] = 0;
}

$Bench->simpleCountStop();

$Bench->simpleCountStop();
// Deletion ends

// Updating begins
$CounterNames[] = 'Updating';
$Bench->simpleCountStart();

if(!empty($Update_Threads))
{
    $CounterNames[] = '> Messages';
    $Bench->simpleCountStart();

    $Query_SelectThreaded = str_replace('{_InsertIDs_}', implode(',', $Update_Threads), $Query_SelectThreaded);

    $SQLResult_GetThreadedMessages = doquery($Query_SelectThreaded, 'messages');

    if($SQLResult_GetThreadedMessages->num_rows > 0)
    {
        $Update_Threads = array();
        while($FetchData = $SQLResult_GetThreadedMessages->fetch_assoc())
        {
            $Update_Threads[] = $FetchData['id'];
        }
        $Query_UpdateThreads = str_replace('{_InsertIDs_}', implode(',', $Update_Threads), $Query_UpdateThreads);
        doquery($Query_UpdateThreads, 'messages');
    }
    $Bench->simpleCountStop();
}

if(!empty($Delete_Planets))
{
    $CounterNames[] = '> Planets (Fleets Retreat)';
    $Bench->simpleCountStart();
    $Delete_Planets_Implode = implode(',', $Delete_Planets);
    include($_EnginePath.'includes/functions/FleetControl_Retreat.php');
    FleetControl_Retreat("`fleet_start_id` IN ({$Delete_Planets_Implode}) OR `fleet_end_id` IN ({$Delete_Planets_Implode})", false);
    $Bench->simpleCountStop();
}

$Bench->simpleCountStop();
// Updating ends

// Optimization begins
if($_GameConfig['cron_GC_LastOptimize'] < ($Now - $Opt_Interval))
{
    $Tables2Optimize = array('messages', 'sim_battle_reports', 'ally_invites', 'login_protection');

    $Query_OptimizeTables  = "OPTIMIZE TABLE ";
    foreach($Tables2Optimize as &$Table)
    {
        $Table = "`{{prefix}}{$Table}`";
    }
    $Query_OptimizeTables .= implode(', ', $Tables2Optimize);

    $CounterNames[] = 'Optimizing';
    $Bench->simpleCountStart();

    $CounterNames[] = '> Doin "OPTIMIZE TABLE"';
    $Bench->simpleCountStart();
    doquery($Query_OptimizeTables, '');
    $Bench->simpleCountStop();

    $CounterNames[] = '> Updating GameConfig';
    $Bench->simpleCountStart();
    doquery("INSERT INTO {{table}} VALUES ('cron_GC_LastOptimize', {$Now}) ON DUPLICATE KEY UPDATE `config_value` = VALUES(`config_value`);", 'config');
    $_GameConfig['cron_GC_LastOptimize'] = $Now;
    $_MemCache->GameConfig = $_GameConfig;
    $Bench->simpleCountStop();

    $Bench->simpleCountStop();
}

$EndTime = microtime(true);
$TotalTime = sprintf('%0.6f', $EndTime - $StartTime);
$CountTime = $Bench->ReturnSimpleCountArray();

if($ShowOutput)
{
    $Counted = '<center><table style="width: 100%;"><tbody>';
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
        $Counted .= '<tr><td class="c" style="width: 30%; text-align: right; '.(strstr($CounterNames[$Key], '>') == true ? 'color: orange;' : '').'">'.$CounterNames[$Key].'</td><td class="c"> '.sprintf('%0.20f', $Data['result']).'<br/>'.$Data['endram'].'</td></tr>';
    }
    $Counted .= '</tbody></table></center>';

    foreach($Stats['del'] as &$Value)
    {
        $Value = prettyNumber($Value);
    }

    $ResultsArray = array($TotalTime, $Stats['del']['msg'], $Stats['del']['simrep'], $Stats['del']['allyinvite'], $Stats['del']['loginprotection'], $Stats['del']['planets'], $Counted);
    AdminMessage(vsprintf($_Lang['Result_Parse'], $ResultsArray), $_Lang['Title']);
}
else
{
    AdminMessage('OK', $_Lang['Title']);
}

?>
