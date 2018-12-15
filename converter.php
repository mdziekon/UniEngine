<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_DontShowMenus = true;
$_DontShowRulesBox = true;
$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

$Title = $_Lang['BattleConverter_title'];
$ReportID = (isset($_GET['id']) ? floor(floatval(trim($_GET['id']))) : 0);

if($ReportID > 0)
{
    $Report = doquery("SELECT * FROM {{table}} WHERE `ID` = {$ReportID};", 'battle_reports', true);
    if($Report)
    {
        $Owners = array_merge(explode(',', $Report['id_owner1']), explode(',', $Report['id_owner2']));
        if(in_array($_User['id'], $Owners))
        {
            includeLang('converter');
            include_once($_EnginePath.'includes/functions/ConvertBattleReport.php');
            $_Lang['Title'] = $_Lang['BattleConverter_title'];

            $Settings = array
            (
                'colorTheme' => (isset($_POST['colorTheme']) && $_POST['colorTheme'] == '2' ? 2 : 1),
                'colorArray' => null
            );
            $Settings['colorArray'] = $_Lang['Conv_Colors'][$Settings['colorTheme']];
            $_Lang['Set_ColorTheme_'.$Settings['colorTheme'].'_Check'] = 'selected';

            $_Lang['ReportCode'] = ConvertBattleReport($Report, $Settings);
            $Page = parsetemplate(gettemplate('converter'), $_Lang);
        }
        else
        {
            message("<b class=\"red\">{$_Lang['BattleReportConverter_CannotConvert']}</b>", $Title);
        }
    }
    else
    {
        message("<b class=\"red\">{$_Lang['BattleReport_DoesntExist']}</b>", $Title);
    }
}
else
{
    message("<b class=\"red\">{$_Lang['BattleReport_NoIDGiven']}</b>", $Title);
}

display($Page, $_Lang['BattleConverter_title'], false);

?>
