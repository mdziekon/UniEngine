<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_DontShowMenus = true;
$_DontShowRulesBox = true;
$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

$TPL_Body = gettemplate('battlereport_body');
$TPL_Error = gettemplate('battlereport_error');

$ReportID = (isset($_GET['id']) ? floor(floatval(trim($_GET['id']))) : 0);
$ReportHash = (isset($_GET['hash']) ? trim($_GET['hash']) : null);
$IsSimulation = isset($_GET['sim']);
if($IsSimulation || empty($ReportHash) || !preg_match('/^[a-zA-Z0-9]{32}$/D', $ReportHash))
{
    $ReportHash = false;
}

if($ReportID > 0 || $ReportHash !== false)
{
    if($ReportHash !== false)
    {
        $WHERE = "`Hash` = '{$ReportHash}'";
    }
    else
    {
        $WHERE = "`ID` = {$ReportID}";
    }
    $Report = doquery("SELECT * FROM {{table}} WHERE {$WHERE};", ($IsSimulation ? 'sim_' : '').'battle_reports', true);

    if($Report)
    {
        $DisallowView = false;
        if($IsSimulation == 1)
        {
            if($Report['time'] < time())
            {
                doquery("DELETE FROM {{table}} WHERE `time` < UNIX_TIMESTAMP();", 'sim_battle_reports');
                $DisallowView = true;
                $Reason = 'deleted';
            }
        }
        else
        {
            if($ReportHash === false)
            {
                $ReportOwners1 = explode(',', $Report['id_owner1']);
                $ReportOwners2 = explode(',', $Report['id_owner2']);
                foreach($ReportOwners1 as $UserID)
                {
                    $AllowedOwners[] = trim($UserID);
                }
                foreach($ReportOwners2 as $UserID)
                {
                    $AllowedOwners[] = trim($UserID);
                }
                if(!in_array($_User['id'], $AllowedOwners) AND !CheckAuth('supportadmin'))
                {
                    $DisallowView = true;
                    $Reason = 'notyour';
                }
            }
        }
        if($DisallowView !== true)
        {
            include($_EnginePath.'includes/functions/ReadBattleReport.php');
            $ParsePage['Content'] = ReadBattleReport($Report);
        }
        else
        {
            $ParsePage['Content'] = parsetemplate($TPL_Error, array('Error' => $_Lang['BattleReportReader_CannotRead'].'<br/>'.$_Lang['BattleReportReader_'.$Reason]));
        }
    }
    else
    {
        $ParsePage['Content'] = parsetemplate($TPL_Error, array('Error' => $_Lang['BattleReport_DoesntExist']));
    }
}
else
{
    $ParsePage['Content'] = parsetemplate($TPL_Error, array('Error' => $_Lang['BattleReport_NoIDGiven']));
}

display(parsetemplate($TPL_Body, $ParsePage), $_Lang['BattleReport_title'], false);

?>
