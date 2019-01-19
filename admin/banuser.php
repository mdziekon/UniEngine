<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(!CheckAuth('sgo'))
{
    AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}
includeLang('admin/banuser');
$Now = time();

$TPL = gettemplate("admin/banuser");

$_Lang['InsertInfoBoxText'] = '&nbsp;';
$_Lang['HideInfoBox'] = ' class="hide"';
$_Lang['InsertInfoBoxColor'] = 'red';

if(isset($_POST['save']) && $_POST['save'] == 'yes')
{
    $_Lang['HideInfoBox'] = '';
    $_POST['users']        = trim($_POST['users']);
    $_POST['reason']    = trim($_POST['reason']);
    $_Lang['Insert_SearchBox'] = $_POST['users'];
    // Opts
    $Opt_ExtendBan           = (isset($_POST['extend']) && $_POST['extend'] == 'on'                                ? true : false);
    $Opt_OnVacation          = (isset($_POST['vacation']) && $_POST['vacation'] == 'on'                            ? true : false);
    $Opt_BanCookies          = (isset($_POST['cookies']) && $_POST['cookies'] == 'on'                              ? true : false);
    $Opt_FleetRetreat_Own    = (isset($_POST['fleet_retreat_own']) && $_POST['fleet_retreat_own'] == 'on'          ? true : false);
    $Opt_FleetRetreat_All    = (isset($_POST['fleet_retreat_others']) && $_POST['fleet_retreat_others'] == 'on'    ? true : false);

    if(!empty($_POST['users']))
    {
        $UserErrors['badID'] = 0;
        $UserErrors['badNick'] = 0;

        $Users = explode(',', $_POST['users']);
        foreach($Users as $UserData)
        {
            $UserData = trim($UserData);
            if(strstr($UserData, '[') !== FALSE)
            {
                if(preg_match('/^\[[0-9]{1,20}\]$/D', $UserData))
                {
                    $BanUsers['id'][] = trim($UserData, '[]');
                }
                else
                {
                    $UserErrors['badID'] += 1;
                }
            }
            else
            {
                if(preg_match(REGEXP_USERNAME_ABSOLUTE, $UserData))
                {
                    $BanUsers['name'][] = "'{$UserData}'";
                }
                else
                {
                    $UserErrors['badNick'] += 1;
                }
            }
        }
        if(!empty($BanUsers))
        {
            if(!empty($BanUsers['id']))
            {
                $Where[] = "`id` IN (".implode(', ', $BanUsers['id']).")";
            }
            if(!empty($BanUsers['name']))
            {
                $Where[] = "`username` IN (".implode(', ', $BanUsers['name']).")";
            }

            $SQLResult_CheckUsers = doquery(
                "SELECT `id`, `username`, `ban_endtime` FROM {{table}} WHERE ".implode(' OR ', $Where).";",
                'users'
            );

            $BanUsers = array();
            $GetBanRows = array();

            if($SQLResult_CheckUsers->num_rows > 0)
            {
                while($Data = $SQLResult_CheckUsers->fetch_assoc())
                {
                    $BanUsers[$Data['id']] = $Data;
                    if($Data['ban_endtime'] > $Now)
                    {
                        $GetBanRows[] = $Data['id'];
                    }
                }
            }
            if(!empty($BanUsers))
            {
                $CalcPeriod  = intval($_POST['period_days']) * TIME_DAY;
                $CalcPeriod += intval($_POST['period_hours']) * TIME_HOUR;
                $CalcPeriod += intval($_POST['period_mins']) * 60;
                $CalcPeriod += intval($_POST['period_secs']);

                if($CalcPeriod > 0)
                {
                    // Do Ban
                    $BanEnd = $Now + $CalcPeriod;
                    $UserIDs = implode(', ', array_keys($BanUsers));

                    if($Opt_OnVacation)
                    {
                        $VacationMaxTime = MAXVACATIONS_REG * TIME_DAY;
                        $UpdateUsers[] = '`is_onvacation` = 1';
                        $UpdateUsers[] = "`vacation_starttime` = IF(`vacation_starttime` = 0, {$Now}, `vacation_starttime`)";
                        if($Opt_ExtendBan)
                        {
                            $UpdateUsers[] = "`vacation_endtime` = IF((`is_onvacation` = 1 AND `vacation_type` = 1 AND `ban_endtime` > {$Now}), `ban_endtime` + {$CalcPeriod} + {$VacationMaxTime}, {$BanEnd} + {$VacationMaxTime})";
                        }
                        else
                        {
                            $UpdateUsers[] = "`vacation_endtime` = {$BanEnd} + {$VacationMaxTime}";
                        }
                        $UpdateUsers[] = '`vacation_type` = 1';
                        $WithVacation = '1';
                    }
                    else
                    {
                        if($Opt_ExtendBan)
                        {
                            $UpdateUsers[] = "`vacation_endtime` = IF((`is_onvacation` = 1 AND `vacation_type` = 1 AND `ban_endtime` > {$Now}), `vacation_endtime` + {$CalcPeriod}, `vacation_endtime`)";
                        }
                        $WithVacation = '0';
                    }
                    if($Opt_BanCookies)
                    {
                        $UpdateUsers[] = '`block_cookies` = 1';
                        $WithBlockade_CookieStyle = '1';
                    }
                    else
                    {
                        $WithBlockade_CookieStyle = '0';
                    }

                    $UpdateUsers[] = '`is_banned` = 1';
                    if($Opt_ExtendBan)
                    {
                        $UpdateUsers[] = "`ban_endtime` = IF(`ban_endtime` > {$Now}, `ban_endtime` + {$CalcPeriod}, {$BanEnd})";
                    }
                    else
                    {
                        $UpdateUsers[] = "`ban_endtime` = {$BanEnd}";
                    }

                    if($Opt_FleetRetreat_Own)
                    {
                        $RetreatOwn = '1';
                        $RetreatSearch[] = "`fleet_owner` IN ({$UserIDs})";
                    }
                    else
                    {
                        $RetreatOwn = '0';
                    }
                    if($Opt_FleetRetreat_All)
                    {
                        $RetreatOthers = '1';
                        $RetreatSearch[] = "`fleet_target_owner` IN ({$UserIDs})";
                    }
                    else
                    {
                        $RetreatOthers = '0';
                    }

                    $_POST['reason'] = trim(strip_tags(stripslashes($_POST['reason'])), '<br><br/>');
                    if(!empty($_POST['reason']))
                    {
                        $Reason = getDBLink()->escape_string($_POST['reason']);
                    }
                    else
                    {
                        $Reason = '';
                    }

                    $UserLinks = [];
                    $UserLinkTPL = gettemplate('admin/banuser_userlink');
                    foreach($BanUsers as $UserID => $UserData)
                    {
                        $UserLinks[] = parsetemplate($UserLinkTPL, array('ID' => $UserID, 'Username' => $UserData['username']));
                        if($Opt_ExtendBan AND in_array($UserID, $GetBanRows))
                        {
                            continue;
                        }
                        $InsertBans[] = "(NULL, {$UserID}, {$Now}, {$BanEnd}, '{$Reason}', {$_User['id']}, {$WithVacation}, 1, 0, 0, 0, {$RetreatOwn}, {$RetreatOthers}, {$WithBlockade_CookieStyle})";
                    }
                    $BannedUsers = count($UserLinks);
                    $UserLinks = implode(', ', $UserLinks);

                    doquery("UPDATE {{table}} SET ".implode(', ', $UpdateUsers)." WHERE `id` IN ({$UserIDs});", 'users');
                    if($Opt_ExtendBan)
                    {
                        if(!empty($GetBanRows))
                        {
                            $GetBanRows = implode(', ', $GetBanRows);
                            $SelectBanRows  = "SELECT `ID` FROM {{table}} WHERE ";
                            $SelectBanRows .= "`Active` = 1 AND `EndTime` > {$Now} AND `UserID` IN ({$GetBanRows}) ";
                            $SelectBanRows .= "ORDER BY `EndTime` DESC;";

                            $SQLResult_GetBans = doquery($SelectBanRows, 'bans');

                            if($SQLResult_GetBans->num_rows > 0)
                            {
                                while($BanRow = $SQLResult_GetBans->fetch_assoc())
                                {
                                    $InsertBans[] = "({$BanRow['ID']}, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0)";
                                }
                            }
                        }
                        $UpdateQuery = "INSERT INTO {{table}} VALUES ".implode(', ', $InsertBans)." ON DUPLICATE KEY UPDATE ";
                        $UpdateQueryArr[] = "`EndTime` = `EndTime` + {$CalcPeriod}";
                        if($Opt_OnVacation)
                        {
                            $UpdateQueryArr[] = "`With_Vacation` = 1";
                        }
                        if($Opt_FleetRetreat_Own)
                        {
                            $UpdateQueryArr[] = "`Fleets_Retreated_Own` = 1";
                        }
                        if($Opt_FleetRetreat_All)
                        {
                            $UpdateQueryArr[] = "`Fleets_Retreated_Others` = 1";
                        }
                        if($Opt_BanCookies)
                        {
                            $UpdateQueryArr[] = "`BlockadeOn_CookieStyle` = 1";
                        }
                        if(!empty($Reason))
                        {
                            $UpdateQueryArr[] = "`Reason` = IF(`Reason` != '', CONCAT(`Reason`, '<br/>', '{$Reason}'), '{$Reason}')";
                        }
                        $UpdateQuery .= implode(', ', $UpdateQueryArr);
                        doquery($UpdateQuery, 'bans');
                    }
                    else
                    {
                        doquery("UPDATE {{table}} SET `Active` = 0, `Removed` = 1, `RemoveDate` = {$Now} WHERE `Active` = 1 AND `EndTime` > {$Now} AND `UserID` IN ({$UserIDs});", 'bans');
                        doquery("INSERT INTO {{table}} VALUES ".implode(', ', $InsertBans).";", 'bans');
                    }
                    if(!empty($RetreatSearch))
                    {
                        include($_EnginePath.'includes/functions/FleetControl_Retreat.php');
                        $Result = FleetControl_Retreat(implode(' OR ', $RetreatSearch));
                        $_Lang['InsertInfoBoxText'] = sprintf(($BannedUsers > 1 ? $_Lang['Msg_BanMOK_wFleets'] : $_Lang['Msg_Ban1OK_wFleets']), $UserLinks, (isset($Result['Updates']['Fleets']) ? prettyNumber($Result['Updates']['Fleets']) : 0));
                    }
                    else
                    {
                        $_Lang['InsertInfoBoxText'] = sprintf(($BannedUsers > 1 ? $_Lang['Msg_BanMOK'] : $_Lang['Msg_Ban1OK']), $UserLinks);
                    }
                    $_Lang['InsertInfoBoxColor'] = 'lime';
                    $_Lang['Insert_SearchBox'] = '';
                }
                else
                {
                    $_Lang['InsertInfoBoxText'] = $_Lang['Msg_PeriodBad'];
                }
            }
            else
            {
                $_Lang['InsertInfoBoxText'] = $_Lang['Msg_UsersNotFound'];
            }
        }
        else
        {
            $_Lang['InsertInfoBoxText'] = $_Lang['Msg_BadUserDataGiven'];
        }
    }
    else
    {
        $_Lang['InsertInfoBoxText'] = $_Lang['Msg_EmptyUserInput'];
    }
}

if(!empty($_GET['ids']))
{
    $InsertIDs = explode(',', $_GET['ids']);
    foreach($InsertIDs as $ThisID)
    {
        $_Lang['InsertUsernames'][] = "[{$ThisID}]";
    }
    $_Lang['InsertUsernames'] = implode(',', $_Lang['InsertUsernames']);
}
else if(!empty($_GET['user']))
{
    $_Lang['InsertUsernames'] = $_GET['user'];
}

$Page = parsetemplate($TPL, $_Lang);
display($Page, $_Lang['PageTitle'], false, true);

?>
