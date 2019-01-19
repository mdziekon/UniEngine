<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath= './../';

include($_EnginePath.'common.php');

includeLang('admin');
$_Lang['ChronoApplets'] = '';

if(CheckAuth('go'))
{
    includeLang('admin/user_info');

    $Error = $_Lang['PageTitle'];

    $Query = "SELECT `id` FROM {{table}} WHERE `authlevel` >= {$_User['authlevel']} AND `id` != {$_User['id']};";

    $SQLResult_GetUsersWithHigherAuth = doquery($Query, 'users');

    if($SQLResult_GetUsersWithHigherAuth->num_rows > 0)
    {
        while($Data = $SQLResult_GetUsersWithHigherAuth->fetch_assoc())
        {
            $ExcludedUsers[] = $Data['id'];
        }
    }

    if(!empty($_GET['uid']))
    {
        $UID = intval($_GET['uid']);
        if($UID <= 0)
        {
            message($_Lang['Error_BadUID'], $Error);
        }
        else
        {
            $WhereClausure = "`user`.`id` = {$UID}";
            $OnNotFoundError = 'UID';
        }
    }
    else
    {
        if(!empty($_GET['name']))
        {
            $Name = trim($_GET['name']);
            if(preg_match(REGEXP_USERNAME_ABSOLUTE, $Name))
            {
                $WhereClausure = "`user`.`username` = '{$Name}'";
                $OnNotFoundError = 'Name';
            }
            else
            {
                message($_Lang['Error_BadName'], $Error);
            }
        }
        else
        {
            message($_Lang['Error_NoUID_NoName'], $Error);
        }
    }

    $Query_GetUser = '';
    $Query_GetUser .= "SELECT `user`.*, `planet`.`name` AS `mothername`, ";
    $Query_GetUser .= "`ally`.`ally_name`, `ally`.`ally_name` AS `ally_request_name`, `ally`.`ally_owner` AS `ally_owner`, ";
    $Query_GetUser .= "`inviter`.`username` AS `inviter_username` ";
    $Query_GetUser .= "FROM {{table}} AS `user` ";
    $Query_GetUser .= "LEFT JOIN `{{prefix}}planets` AS `planet` ON `planet`.`id` = `user`.`id_planet` ";
    $Query_GetUser .= "LEFT JOIN `{{prefix}}alliance` AS `ally` ON ";
    $Query_GetUser .= "(`user`.`ally_request` > 0 AND `ally`.`id` = `user`.`ally_request`) ";
    $Query_GetUser .= "OR ";
    $Query_GetUser .= "(`user`.`ally_id` > 0 AND `ally`.`id` = `user`.`ally_id`) ";
    $Query_GetUser .= "LEFT JOIN `{{prefix}}users` AS `inviter` ON `user`.`referred` > 0 AND `user`.`referred` = `inviter`.`id` ";
    $Query_GetUser .= "WHERE {$WhereClausure} ";
    $Query_GetUser .= "LIMIT 1;";

    $SQLResult_GetUserData = doquery($Query_GetUser, 'users');

    if($SQLResult_GetUserData->num_rows == 0)
    {
        message($_Lang['Error_NotFound_'.$OnNotFoundError], $Error);
    }

    $Now = time();
    $Data = $SQLResult_GetUserData->fetch_assoc();
    $UID = $Data['id'];

    if(!empty($ExcludedUsers))
    {
        if(in_array($UID, $ExcludedUsers))
        {
            message($_Lang['Error_NoEnoughAccess'], $Error);
        }
    }

    $SQLQuery_GetUserStats = "SELECT * FROM {{table}} WHERE `id_owner` = {$UID} AND `stat_type` = 1 LIMIT 1;";

    $SQLResult_GetUserStats = doquery($SQLQuery_GetUserStats, 'statpoints');

    if($SQLResult_GetUserStats->num_rows == 0)
    {
        $Data['stats'] = 'EMPTY';
    }
    else
    {
        $Data['stats'] = $SQLResult_GetUserStats->fetch_assoc();
    }

    $SQLQuery_GetUserFleets = "SELECT * FROM {{table}} WHERE `fleet_owner` = {$UID} OR `fleet_target_owner` = {$UID};";

    $SQLResult_GetUserFleets = doquery($SQLQuery_GetUserFleets, 'fleets');

    if($SQLResult_GetUserFleets->num_rows == 0)
    {
        $Data['fleets'] = 'EMPTY';
    }
    else
    {
        while($Fleets = $SQLResult_GetUserFleets->fetch_assoc())
        {
            $GetACSData[] = $Fleets['fleet_id'];
            $Data['fleets'][$Fleets['fleet_id']] = $Fleets;
            if(empty($GetUserNicks) OR !in_array($Fleets['fleet_owner'], $GetUserNicks))
            {
                $GetUserNicks[] = $Fleets['fleet_owner'];
            }
            if(empty($GetUserNicks) OR !in_array($Fleets['fleet_target_owner'], $GetUserNicks) AND $Fleets['fleet_target_owner'] > 0)
            {
                $GetUserNicks[] = $Fleets['fleet_target_owner'];
            }
        }

        if(!empty($GetUserNicks))
        {
            $SQLQuery_GetOwnersUsernames = "SELECT `id`, `username` FROM {{table}} WHERE `id` IN (".implode(', ', $GetUserNicks).");";
            $SQLResult_GetOwnersUsernames = doquery($SQLQuery_GetOwnersUsernames, 'users');
            if($SQLResult_GetOwnersUsernames->num_rows > 0)
            {
                while($UserData = $SQLResult_GetOwnersUsernames->fetch_assoc())
                {
                    $UsersNicks[$UserData['id']] = $UserData['username'];
                }
            }
        }

        if(!empty($GetACSData))
        {
            foreach($GetACSData as $ACSTempData)
            {
                $CheckJoinedFleets[] = "`fleets_id` LIKE '%|{$ACSTempData}|%'";
            }
            $CheckJoinedFleets = implode(' OR ', $CheckJoinedFleets);

            $SQLQuery_GetACSData = "SELECT `id`, `main_fleet_id`, `fleets_id` FROM {{table}} WHERE `main_fleet_id` IN (".implode(', ', $GetACSData).") OR {$CheckJoinedFleets};";

            $SQLResult_GetACSData = doquery($SQLQuery_GetACSData, 'acs');

            if($SQLResult_GetACSData->num_rows > 0)
            {
                while($TempACSData = $SQLResult_GetACSData->fetch_assoc())
                {
                    $TempFleetsFromACS = false;
                    $Temp1 = explode(',', str_replace('|', '', $TempACSData['fleets_id']));
                    foreach($Temp1 as $TempData)
                    {
                        $ACSData[$TempData] = $TempACSData['id'];
                    }
                    $ACSData[$TempACSData['main_fleet_id']] = $TempACSData['id'];
                }
            }
        }
    }

    $TPL = gettemplate('admin/user_info');
    $Parse= $_Lang;

    if(empty($_GET['mark']) OR $_GET['mark'] == '1')
    {
        $Parse['MarkSelect'] = '01';
    }
    else
    {
        $Parse['MarkSelect'] = "0{$_GET['mark']}";
    }

    // --- Create General Overview Marker! ---

    $Player['Name'] = $Data['username'];
    $Player['Email'] = $Data['email'];
    $Player['Email2'] = $Data['email_2'];
    $Player['LastIP'] = (!empty($Data['user_lastip']) ? "<a class=\"help\" title=\"{$_Lang['SearchByIP']}\" href=\"userlist.php?search_user={$Data['user_lastip']}&amp;search_by=ip\">{$Data['user_lastip']}</a>" : $_Lang['NoLogin']);
    $Player['RegIP'] = (!empty($Data['ip_at_reg']) ? "<a class=\"help\" title=\"{$_Lang['SearchByIP']}\" href=\"userlist.php?search_user={$Data['ip_at_reg']}&amp;search_by=ip\">{$Data['ip_at_reg']}</a>" : $_Lang['No_Data']);

    // - Last Activity
    $OnlineDiff = $Now - $Data['onlinetime'];
    if($OnlineDiff >= 28 * TIME_DAY)
    {
        $OLColor = 'red';
    }
    else if($OnlineDiff >= 7 * TIME_DAY)
    {
        $OLColor = '#FFA0A0';
    }
    else if($OnlineDiff >= TIME_DAY)
    {
        $OLColor = 'orange';
    }
    else if($OnlineDiff > TIME_ONLINE)
    {
        $OLColor = 'yellow';
    }
    else
    {
        $OLColor = 'lime';
    }
    $OnlineDiffText = '<span style=color:'.$OLColor.'>('.pretty_time($OnlineDiff).' '.$_Lang['_ago'].')</span>';
    if($OLColor == 'lime')
    {
        $OnlineDiffText = "<a class=\"help\" title=\"{$_Lang['ShowOnline']}\" href=\"userlist.php?online=on\">{$OnlineDiffText}</a>";
    }
    if(CheckAuth('supportadmin'))
    {
        $OnlineDiffText = "{$OnlineDiffText}<br/>{$Data['current_page']}<br/><a class=\"aLog\" href=\"browse_actionlogs.php?uid={$UID}\">{$_Lang['ShowLogs']}</a>";
    }
    $Player['LastActivity'] = prettyDate('d m Y, H:i:s', $Data['onlinetime'], 1)."<br/>{$OnlineDiffText}";
    // - END - Last Activy
    $Player['Browser'] = (!empty($Data['user_agent']) ? $Data['user_agent'] : $_Lang['No_Data']);
    $Player['Screen'] = (!empty($Data['screen_settings']) ? str_replace('_', 'x', $Data['screen_settings']) : $_Lang['No_Data']);

    // - Registration Time
    if($Data['register_time'] > 0)
    {
        $Bloc['adm_ul_data_regd'] = '';
        $RegisterDays = floor(($Now - $Data['register_time']) / (24*60*60));
        if($RegisterDays == 1)
        {
            $RegDays = $_Lang['_day'];
        }
        else
        {
            $RegDays = $_Lang['_days'];
        }
        $RegisterDays = prettyNumber($RegisterDays);
        if($RegisterDays == 0)
        {
            if(date('d') == date('d', $Data['register_time']))
            {
                $Player['RegisterInfo'] = $_Lang['_today'];
            }
            else
            {
                $Player['RegisterInfo'] = $_Lang['_yesterday'];
            }
        }
        else
        {
            $Player['RegisterInfo'] = "{$RegisterDays} {$RegDays} {$_Lang['_ago']}";
        }
        $Player['RegisterInfo'] .= '<br/>'.prettyDate('d m Y, H:i:s', $Data['register_time'], 1);
    }
    else
    {
        $Player['RegisterInfo'] = $_Lang['No_Data'];
    }
    // - END - Registration Time

    // MoreInfo - Vacation Info
    if(isOnVacation($Data))
    {
        $Player['Vacations']= "<b class=\"skyblue\">{$_Lang['Active']}</b><br/>({$_Lang['_Since']}: ".pretty_time($Now - $Data['vacation_starttime'])." / ".prettyDate('d m Y, H:i:s', $Data['vacation_starttime'], 1).")<br/>(".($Data['vacation_endtime'] == 0 ? "<b class=\"orange\">{$_Lang['InfiniteVacation']}</b>" : "{$_Lang['_Duration']}: ".pretty_time($Data['vacation_endtime'] - $Now)." / ".prettyDate('d m Y, H:i:s', $Data['vacation_endtime'], 1)).")";
    }
    else
    {
        $Player['Vacations']= $_Lang['Inactive'];
    }

    $Player['Ban'] = '';
    if($Data['is_banned'] == 1)
    {
        if($Data['ban_endtime'] < $Now)
        {
            $BanColor = 'orange';
            $BanTime= $_Lang['Ban_Expired'];
        }
        else
        {
            $BanColor = 'red';
            $BanTime= $_Lang['Ban_TimeLeft'].' '.pretty_time($Data['ban_endtime'] - $Now);
        }
        $Player['Ban'] = "<b class=\"red\">{$_Lang['Active']}</b><br/>({$_Lang['_Untill']}: ".prettyDate("d m Y, H:i:s", $Data['ban_endtime'], 1).")<br/>({$BanTime})";
    }
    else
    {
        $Player['Ban'] = $_Lang['Inactive'];
    }
    if($Data['block_cookies'] == 1)
    {
        $Player['Ban'] .= "<br/>(<b class=\"red\">{$_Lang['CookieBanActive']}</b>)";
    }

    $Player['Delete'] = ($Data['is_ondeletion'] == 1 ? "<b class=\"orange\">{$_Lang['Active']}</b><br/>".($Data['deletion_endtime'] < $Now ? $_Lang['Delete_Time_onNextRecalc'] : "{$_Lang['Delete_Time']}: ".pretty_time($Data['deletion_endtime'] - $Now))."<br/>".prettyDate('d m Y, H:i:s', $Data['deletion_endtime'], 1) : $_Lang['Inactive'] );
    $Player['OldNick'] = (!empty($Data['old_username']) ? "{$Data['old_username']}<br/>".($Data['old_username_expire'] > $Now ? $_Lang['WillExpire'].pretty_time($Data['old_username_expire'] - $Now) : "<b class=\"orange\">{$_Lang['Expired']}</b>")."<br/>".prettyDate('d m Y, H:i:s', $Data['old_username_expire'], 1) : $_Lang['No_Data']);
    $Player['InvitedBy'] = ($Data['referred'] > 0 ? (!empty($Data['inviter_username']) ? "<a href=\"user_info.php?uid={$Data['referred']}\">{$Data['inviter_username']} [#{$Data['referred']}]</a>" : "{$_Lang['InviterDeleted']} [#{$Data['referred']}]") : $_Lang['NotInvited']);

    $Player['DarkEnergy'] = ($Data['darkEnergy'] > 0 ? prettyNumber($Data['darkEnergy']) : '0');
    $Player['ProTime'] = ($Data['pro_time'] > 0 ? ($Data['pro_time'] > $Now ? "<b class=\"lime\">{$_Lang['WillExpire']}</b><br/>".pretty_time($Data['pro_time'] - $Now)."<br/>".prettyDate('d m Y, H:i:s', $Data['pro_time'], 1) : "<b class=\"red\">{$_Lang['Expired2']}</b><br/>".pretty_time(-($Data['pro_time'] - $Now))." {$_Lang['_ago']}<br/>".prettyDate('d m Y, H:i:s', $Data['pro_time'], 1)) : $_Lang['NeverBought']);
    $Player['GeoTime'] = ($Data['geologist_time'] > 0 ? ($Data['geologist_time'] > $Now ? "<b class=\"lime\">{$_Lang['WillExpire']}</b><br/>".pretty_time($Data['geologist_time'] - $Now)."<br/>".prettyDate('d m Y, H:i:s', $Data['geologist_time'], 1) : "<b class=\"red\">{$_Lang['Expired']}</b><br/>".pretty_time(-($Data['geologist_time'] - $Now))." {$_Lang['_ago']}<br/>".prettyDate('d m Y, H:i:s', $Data['geologist_time'], 1)) : $_Lang['NeverBought']);
    $Player['EngTime'] = ($Data['engineer_time'] > 0 ? ($Data['engineer_time'] > $Now ? "<b class=\"lime\">{$_Lang['WillExpire']}</b><br/>".pretty_time($Data['engineer_time'] - $Now)."<br/>".prettyDate('d m Y, H:i:s', $Data['engineer_time'], 1) : "<b class=\"red\">{$_Lang['Expired']}</b><br/>".pretty_time(-($Data['engineer_time'] - $Now))." {$_Lang['_ago']}<br/>".prettyDate('d m Y, H:i:s', $Data['engineer_time'], 1)) : $_Lang['NeverBought']);
    $Player['AdmTime'] = ($Data['admiral_time'] > 0 ? ($Data['admiral_time'] > $Now ? "<b class=\"lime\">{$_Lang['WillExpire']}</b><br/>".pretty_time($Data['admiral_time'] - $Now)."<br/>".prettyDate('d m Y, H:i:s', $Data['admiral_time'], 1) : "<b class=\"red\">{$_Lang['Expired']}</b><br/>".pretty_time(-($Data['admiral_time'] - $Now))." {$_Lang['_ago']}<br/>".prettyDate('d m Y, H:i:s', $Data['admiral_time'], 1)) : $_Lang['NeverBought']);
    $Player['TecTime'] = ($Data['technocrat_time'] > 0 ? ($Data['technocrat_time'] > $Now ? "<b class=\"lime\">{$_Lang['WillExpire']}</b><br/>".pretty_time($Data['technocrat_time'] - $Now)."<br/>".prettyDate('d m Y, H:i:s', $Data['technocrat_time'], 1) : "<b class=\"red\">{$_Lang['Expired']}</b><br/>".pretty_time(-($Data['technocrat_time'] - $Now))." {$_Lang['_ago']}<br/>".prettyDate('d m Y, H:i:s', $Data['technocrat_time'], 1)) : $_Lang['NeverBought']);
    $Player['JamTime'] = ($Data['spy_jam_time'] > 0 ? ($Data['spy_jam_time'] > $Now ? "<b class=\"lime\">{$_Lang['WillExpire']}</b><br/>".pretty_time($Data['spy_jam_time'] - $Now)."<br/>".prettyDate('d m Y, H:i:s', $Data['spy_jam_time'], 1) : "<b class=\"red\">{$_Lang['Expired']}</b><br/>".pretty_time(-($Data['spy_jam_time'] - $Now))." {$_Lang['_ago']}<br/>".prettyDate('d m Y, H:i:s', $Data['spy_jam_time'], 1)) : $_Lang['NeverBought']);
    $Player['AdditionalPlanets'] = ($Data['additional_planets'] > 0 ? prettyNumber($Data['additional_planets']) : '0');
    if($Data['ally_id'] <= 0 AND $Data['ally_request'] > 0)
    {
        $Data['ally_id'] = $Data['ally_request'];
        $Data['ally_name'] = $Data['ally_request_name'];
    }
    $Player['Ally'] = ($Data['ally_id'] > 0 ? '<a class="help" title="'.$_Lang['SearchByAlly'].'" href="userlist.php?search_user='.$Data['ally_id'].'&search_by=aid">'.$Data['ally_name'].' ('.$Data['ally_id'].')</a>'.($Data['ally_request'] != 0 ? ' ['.$_Lang['Request'].']' : ($Data['ally_owner'] == $UID ? ' ['.$_Lang['Ally_owner'].']' : '')) : '&nbsp;-&nbsp;');
    $Player['AccountActive'] = (!empty($Data['activation_code']) ? "<b class=\"orange\">{$_Lang['_no']}</b><br/>{$_Lang['ActivationCode']}: {$Data['activation_code']}" : "<b class=\"lime\">{$_Lang['_yes']}</b>");
    $Player['DisableIPCheck'] = (($Data['noipcheck'] == 1) ? $_Lang['_no'] : $_Lang['_yes']);
    $Player['MotherPlanet'] = "{$Data['mothername']} ({$_Lang['MotherPlanet_ID']}: {$Data['id_planet']}) [<a class=\"help\" title=\"{$_Lang['GoToGalaxy']}\" href=\"../galaxy.php?mode=3&amp;galaxy={$Data['galaxy']}&amp;system={$Data['system']}&amp;planet={$Data['planet']}\">{$Data['galaxy']}:{$Data['system']}:{$Data['planet']}</a>]";

    // --- END of Creating General Overview Marker! ---

    // --- Create Statistics Marker! ---

    if($Data['stats'] == 'EMPTY')
    {
        $Parse['HideStatsIfUnavailable']= 'style="display: none;"';
        $Parse['ShowInfoIfStatsUnavailable']= "<br/><span class=\"red\">{$_Lang['NoStatsAvailable']}</span><br/>&nbsp;";
    }
    else
    {
        // General Pos
        $Player['GeneralPos'] = $Data['stats']['total_rank'];
        $Player['GeneralChange1'] = $Data['stats']['total_rank'] - $Data['stats']['total_old_rank'];
        if($Data['stats']['total_yesterday_rank'] > 0)
        {
            $Player['GeneralChange2'] = $Data['stats']['total_rank'] - $Data['stats']['total_yesterday_rank'];
        }
        else
        {
            $Player['GeneralChange2'] = 0;
        }
        if($Player['GeneralChange1'] == 0)
        {
            $Player['GeneralChange1'] = "<span class=\"blue\">0</span>";
        }
        else if($Player['GeneralChange1'] < 0)
        {
            $Player['GeneralChange1'] = abs($Player['GeneralChange1']);
            $Player['GeneralChange1'] = "<span class=\"lime\">+{$Player['GeneralChange1']}</span>";
        }
        else
        {
            $Player['GeneralChange1'] = "<span class=\"red\">-{$Player['GeneralChange1']}</span>";
        }
        if($Player['GeneralChange2'] == 0)
        {
            $Player['GeneralChange2'] = "<span class=\"blue\">0</span>";
        }
        else if($Player['GeneralChange2'] < 0)
        {
            $Player['GeneralChange2'] = abs($Player['GeneralChange2']);
            $Player['GeneralChange2'] = "<span class=\"lime\">+{$Player['GeneralChange2']}</span>";
        }
        else
        {
            $Player['GeneralChange2'] = "<span class=\"red\">-{$Player['GeneralChange2']}</span>";
        }
        $Player['GeneralPoints']= prettyNumber($Data['stats']['total_points']);

        // Fleet pos
        $Player['FleetPos'] = $Data['stats']['fleet_rank'];
        $Player['FleetChange1'] = $Data['stats']['fleet_rank'] - $Data['stats']['fleet_old_rank'];
        if($Data['stats']['fleet_yesterday_rank'] > 0)
        {
            $Player['FleetChange2'] = $Data['stats']['fleet_rank'] - $Data['stats']['fleet_yesterday_rank'];
        }
        else
        {
            $Player['FleetChange2'] = 0;
        }
        if($Player['FleetChange1'] == 0)
        {
            $Player['FleetChange1'] = "<span class=\"blue\">0</span>";
        }
        else if($Player['FleetChange1'] < 0)
        {
            $Player['FleetChange1'] = abs($Player['FleetChange1']);
            $Player['FleetChange1'] = "<span class=\"lime\">+{$Player['FleetChange1']}</span>";
        }
        else
        {
            $Player['FleetChange1'] = "<span class=\"red\">-{$Player['FleetChange1']}</span>";
        }
        if($Player['FleetChange2'] == 0)
        {
            $Player['FleetChange2'] = "<span class=\"blue\">0</span>";
        }
        else if($Player['FleetChange2'] < 0)
        {
            $Player['FleetChange2'] = abs($Player['FleetChange2']);
            $Player['FleetChange2'] = "<span class=\"lime\">+{$Player['FleetChange2']}</span>";
        }
        else
        {
            $Player['FleetChange2'] = "<span class=\"red\">-{$Player['FleetChange2']}</span>";
        }
        $Player['FleetPoints']= prettyNumber($Data['stats']['fleet_points']);
        if($Data['stats']['total_points'] != 0)
        {
            $Player['FleetPercent'] = (round($Data['stats']['fleet_points']/$Data['stats']['total_points'], 4) * 100).'%';
        }
        else
        {
            $Player['FleetPercent'] = '0%';
        }

        // Buildings pos
        $Player['BuildingsPos'] = $Data['stats']['build_rank'];
        $Player['BuildingsChange1'] = $Data['stats']['build_rank'] - $Data['stats']['build_old_rank'];
        if($Data['stats']['build_yesterday_rank'] > 0)
        {
            $Player['BuildingsChange2'] = $Data['stats']['build_rank'] - $Data['stats']['build_yesterday_rank'];
        }
        else
        {
            $Player['BuildingsChange2'] = 0;
        }
        if($Player['BuildingsChange1'] == 0)
        {
            $Player['BuildingsChange1'] = "<span class=\"blue\">0</span>";
        }
        else if($Player['BuildingsChange1'] < 0)
        {
            $Player['BuildingsChange1'] = abs($Player['BuildingsChange1']);
            $Player['BuildingsChange1'] = "<span class=\"lime\">+{$Player['BuildingsChange1']}</span>";
        }
        else
        {
            $Player['BuildingsChange1'] = "<span class=\"red\">-{$Player['BuildingsChange1']}</span>";
        }
        if($Player['BuildingsChange2'] == 0)
        {
            $Player['BuildingsChange2'] = "<span class=\"blue\">0</span>";
        }
        else if($Player['BuildingsChange2'] < 0)
        {
            $Player['BuildingsChange2'] = abs($Player['BuildingsChange2']);
            $Player['BuildingsChange2'] = "<span class=\"lime\">+{$Player['BuildingsChange2']}</span>";
        }
        else
        {
            $Player['BuildingsChange2'] = "<span class=\"red\">-{$Player['BuildingsChange2']}</span>";
        }
        $Player['BuildingsPoints']= prettyNumber($Data['stats']['build_points']);
        if($Data['stats']['total_points'] != 0)
        {
            $Player['BuildingsPercent'] = (round($Data['stats']['build_points']/$Data['stats']['total_points'], 4) * 100).'%';
        }
        else
        {
            $Player['BuildingsPercent'] = '0%';
        }

        // Defence pos
        $Player['DefencePos'] = $Data['stats']['defs_rank'];
        $Player['DefenceChange1'] = $Data['stats']['defs_rank'] - $Data['stats']['defs_old_rank'];
        if($Data['stats']['defs_yesterday_rank'] > 0)
        {
            $Player['DefenceChange2'] = $Data['stats']['defs_rank'] - $Data['stats']['defs_yesterday_rank'];
        }
        else
        {
            $Player['DefenceChange2'] = 0;
        }
        if($Player['DefenceChange1'] == 0)
        {
            $Player['DefenceChange1'] = "<span class=\"blue\">0</span>";
        }
        else if($Player['DefenceChange1'] < 0)
        {
            $Player['DefenceChange1'] = abs($Player['DefenceChange1']);
            $Player['DefenceChange1'] = "<span class=\"lime\">+{$Player['DefenceChange1']}</span>";
        }
        else
        {
            $Player['DefenceChange1'] = "<span class=\"red\">-{$Player['DefenceChange1']}</span>";
        }
        if($Player['DefenceChange2'] == 0)
        {
            $Player['DefenceChange2'] = "<span class=\"blue\">0</span>";
        }
        else if($Player['DefenceChange2'] < 0)
        {
            $Player['DefenceChange2'] = abs($Player['DefenceChange2']);
            $Player['DefenceChange2'] = "<span class=\"lime\">+{$Player['DefenceChange2']}</span>";
        }
        else
        {
            $Player['DefenceChange2'] = "<span class=\"red\">-{$Player['DefenceChange2']}</span>";
        }
        $Player['DefencePoints']= prettyNumber($Data['stats']['defs_points']);
        if($Data['stats']['total_points'] != 0)
        {
            $Player['DefencePercent'] = (round($Data['stats']['defs_points']/$Data['stats']['total_points'], 4) * 100).'%';
        }
        else
        {
            $Player['DefencePercent'] = '0%';
        }

        // Research pos
        $Player['ResearchPos'] = $Data['stats']['tech_rank'];
        $Player['ResearchChange1'] = $Data['stats']['tech_rank'] - $Data['stats']['tech_old_rank'];
        if($Data['stats']['tech_yesterday_rank'] > 0)
        {
            $Player['ResearchChange2'] = $Data['stats']['tech_rank'] - $Data['stats']['tech_yesterday_rank'];
        }
        else
        {
            $Player['ResearchChange2'] = 0;
        }
        if($Player['ResearchChange1'] == 0)
        {
            $Player['ResearchChange1'] = "<span class=\"blue\">0</span>";
        }
        else if($Player['ResearchChange1'] < 0)
        {
            $Player['ResearchChange1'] = abs($Player['ResearchChange1']);
            $Player['ResearchChange1'] = "<span class=\"lime\">+{$Player['ResearchChange1']}</span>";
        }
        else
        {
            $Player['ResearchChange1'] = "<span class=\"red\">-{$Player['ResearchChange1']}</span>";
        }
        if($Player['ResearchChange2'] == 0)
        {
            $Player['ResearchChange2'] = "<span class=\"blue\">0</span>";
        }
        else if($Player['ResearchChange2'] < 0)
        {
            $Player['ResearchChange2'] = abs($Player['ResearchChange2']);
            $Player['ResearchChange2'] = "<span class=\"lime\">+{$Player['ResearchChange2']}</span>";
        }
        else
        {
            $Player['ResearchChange2'] = "<span class=\"red\">-{$Player['ResearchChange2']}</span>";
        }
        $Player['ResearchPoints']= prettyNumber($Data['stats']['tech_points']);
        if($Data['stats']['total_points'] != 0)
        {
            $Player['ResearchPercent'] = (round($Data['stats']['tech_points']/$Data['stats']['total_points'], 4) * 100).'%';
        }
        else
        {
            $Player['ResearchPercent'] = '0%';
        }
    }

    // --- END of Creating Statistics Marker! ---

    // --- Create Fleet Control Marker! ---

    if($Data['fleets'] == 'EMPTY')
    {
        $Parse['FleetControlContent']= "<br/><span class=\"red\">{$_Lang['NoFleetsInFlight']}</span><br/>&nbsp;";
    }
    else
    {
        $FleetTPL = gettemplate('admin/user_info_fleet_row');
        $FleetHeadTPL = gettemplate('admin/user_info_fleet_header');

        include("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");

        $AllFleetParse = '';
        foreach($Data['fleets'] as $FleetID => $FleetData)
        {
            $FleetParse = false;
            $FleetArray = false;
            $FleetShipsTemp = false;
            $FleetCount = 0;
            $FleetShipsParsed = false;

            $FleetParse['Fleet_ID'] = $FleetID;
            $FleetParse['Fleet_Owner'] = "{$UsersNicks[$FleetData['fleet_owner']]}<br/>[{$FleetData['fleet_owner']}]";
            if($FleetData['fleet_owner'] == $UID)
            {
                $FleetParse['Fleet_Owner_color'] = 'lime';
            }
            else
            {
                if(in_array($FleetData['fleet_mission'], array(1, 2, 6, 9, 10, 11)))
                {
                    $FleetParse['Fleet_Owner_color'] = 'red';
                }
                else
                {
                    $FleetParse['Fleet_Owner_color'] = 'blue';
                }
                $FleetParse['Fleet_Owner'] = "<a class=\"{$FleetParse['Fleet_Owner_color']}\" href=\"user_info.php?uid={$FleetData['fleet_owner']}\">{$FleetParse['Fleet_Owner']}</a>";
            }
            $FleetParse['Fleet_Mission'] = $_Lang['type_mission'][$FleetData['fleet_mission']];
            if($ACSData[$FleetID] > 0)
            {
                if($FleetData['fleet_mission'] == 1)
                {
                    $FleetParse['Fleet_Mission'] = $_Lang['type_mission'][2];
                }
                $FleetParse['Fleet_Mission'] = "{$FleetParse['Fleet_Mission']}<br/>[ACS: {$ACSData[$FleetID]}]";
                if($FleetData['fleet_mission'] == 1)
                {
                    $FleetParse['Fleet_Mission'] = "<span class=\"orange help\" title=\"{$_Lang['Main_ACS_Fleet']}\">{$FleetParse['Fleet_Mission']}</span>";
                }
            }
            if($FleetData['fleet_mess'] == 1 AND $FleetData['fleet_mission'] != 5)
            {
                $FleetParse['Fleet_Mission'] = "{$FleetParse['Fleet_Mission']}<br/>[{$_Lang['Coming_back']}]";
            }
            if(($FleetData['fleet_mess'] == 0 AND $FleetData['fleet_start_time'] <= $Now) OR ($FleetData['fleet_mess'] != 0 AND $FleetData['fleet_end_time'] <= $Now))
            {
                $FleetParse['Fleet_Mission'] = "{$FleetParse['Fleet_Mission']}<br/>[<b class=\"orange\">{$_Lang['Not_calculated']}</b>]";
            }

            $FleetArray = explode(';', $FleetData['fleet_array']);
            foreach($FleetArray as $FleetShipsTemp)
            {
                if(!empty($FleetShipsTemp))
                {
                    $FleetShipsTemp = explode(',', $FleetShipsTemp);
                    $FleetCount += $FleetShipsTemp[1];
                    $FleetShipsParsed[] = "<tr><th class='help_th'>{$_Lang['tech'][$FleetShipsTemp[0]]}</th><th class='help_th'>".prettyNumber($FleetShipsTemp[1])."</th></tr>";
                }
            }
            $FleetCount = prettyNumber($FleetCount);
            $FleetParse['Fleet_Ships'] = "<table>".implode('', $FleetShipsParsed)."</table>";
            $FleetParse['Fleet_Array'] = "{$FleetCount}<br/>(?)";
            if($FleetData['fleet_resource_metal'] == 0 AND $FleetData['fleet_resource_crystal'] == 0 AND $FleetData['fleet_resource_deuterium'] == 0)
            {
                $FleetParse['Fleet_Cargo'] = $_Lang['No_cargo'];
            }
            else
            {
                $FleetParse['Fleet_Cargo'] = $_Lang['See_cargo'];
                $FleetParse['Fleet_Resources'] = "<table><tr><th class='help_th cargo_res'>{$_Lang['Metal']}</th><th class='help_th'>".prettyNumber($FleetData['fleet_resource_metal'])."</th></tr><tr><th class='help_th cargo_res'>{$_Lang['Crystal']}</th><th class='help_th'>".prettyNumber($FleetData['fleet_resource_crystal'])."</th></tr><tr><th class='help_th cargo_res'>{$_Lang['Deuterium']}</th><th class='help_th'>".prettyNumber($FleetData['fleet_resource_deuterium'])."</th></tr></table>";
                $FleetParse['Fleet_Cargo_class'] = ' fCar';
            }
            $FleetParse['Fleet_Start_Title'] = ($FleetData['fleet_start_type'] == '1' ? $_Lang['Start_from_planet'] : $_Lang['Start_from_moon']);
            $FleetParse['Fleet_Start'] = "<a href=\"../galaxy.php?mode=3&amp;galaxy={$FleetData['fleet_start_galaxy']}&amp;system={$FleetData['fleet_start_system']}&amp;planet={$FleetData['fleet_start_planet']}\">[{$FleetData['fleet_start_galaxy']}:{$FleetData['fleet_start_system']}:{$FleetData['fleet_start_planet']}] ".($FleetData['fleet_start_type'] == '1' ? $_Lang['Planet_sign'] : $_Lang['Moon_sign'])."</a>";
            $FleetParse['Fleet_Start'].= "<br/>".date('d.m.Y', $FleetData['fleet_send_time'])."<br/>".date('H:i:s', $FleetData['fleet_send_time'])."<br/>(<span id=\"bxxa{$FleetID}\">".pretty_time($Now - $FleetData['fleet_send_time'], true, 'D')."</span> {$_Lang['_ago']})";
            $_Lang['ChronoApplets'] .= InsertJavaScriptChronoApplet('a', $FleetID, $FleetData['fleet_send_time'], true, true);

            $FleetParse['Fleet_End_Title'] = ($FleetData['fleet_end_type'] == '1' ? $_Lang['Target_is_planet'] : ($FleetData['fleet_end_type'] == '2' ? $_Lang['Target_is_debris'] : $_Lang['Target_is_moon']));
            $FleetParse['Fleet_End'] = "<a href=\"../galaxy.php?mode=3&amp;galaxy={$FleetData['fleet_end_galaxy']}&amp;system={$FleetData['fleet_end_system']}&amp;planet={$FleetData['fleet_end_planet']}\">[{$FleetData['fleet_end_galaxy']}:{$FleetData['fleet_end_system']}:{$FleetData['fleet_end_planet']}] ".($FleetData['fleet_end_type'] == '1' ? $_Lang['Planet_sign'] : ($FleetData['fleet_end_type'] == '2' ? $_Lang['Debris_sign'] : $_Lang['Moon_sign']))."</a>";
            if($FleetData['fleet_start_time'] <= $Now)
            {
                $FleetParse['Fleet_end_time_set'] = "<span class=\"lime\">{$_Lang['TargetAchieved']}</span>";
            }
            else
            {
                $FleetParse['Fleet_end_time_set'] = pretty_time($FleetData['fleet_start_time'] - $Now, true, 'D');
                $_Lang['ChronoApplets'] .= InsertJavaScriptChronoApplet('b', $FleetID, $FleetData['fleet_start_time'] - $Now);
            }
            $FleetParse['Fleet_End'].= "<br/>".date('d.m.Y', $FleetData['fleet_start_time'])."<br/>".date('H:i:s', $FleetData['fleet_start_time'])."<br/>(<span id=\"bxxb{$FleetID}\">{$FleetParse['Fleet_end_time_set']}</span>)";

            $FleetParse['Fleet_End_owner']= "{$UsersNicks[$FleetData['fleet_target_owner']]}<br/>[{$FleetData['fleet_target_owner']}]";
            if($FleetData['fleet_target_owner'] == $UID)
            {
                $FleetParse['Fleet_End_owner_color'] = 'lime';
            }
            else
            {
                if(in_array($FleetData['fleet_mission'], array(1, 2, 6, 9, 10, 11)))
                {
                    $FleetParse['Fleet_End_owner_color'] = 'red';
                }
                else
                {
                    $FleetParse['Fleet_End_owner_color'] = 'blue';
                }
                $FleetParse['Fleet_End_owner'] = "<a class=\"{$FleetParse['Fleet_End_owner_color']}\" href=\"user_info.php?uid={$FleetData['fleet_target_owner']}\">{$FleetParse['Fleet_End_owner']}</a>";
            }

            if($FleetData['fleet_end_time'] <= $Now)
            {
                $FleetParse['Fleet_back_time_set'] = "<span class=\"orange\">{$_Lang['FleetCameBack']}</span>";
            }
            else
            {
                $FleetParse['Fleet_back_time_set'] = pretty_time($FleetData['fleet_end_time'] - $Now, true, 'D');
                $_Lang['ChronoApplets'] .= InsertJavaScriptChronoApplet('c', $FleetID, $FleetData['fleet_end_time'] - $Now);
            }
            $FleetParse['Fleet_Back_time'].= date('d.m.Y - H:i:s', $FleetData['fleet_end_time'])."<br/>(<span id=\"bxxc{$FleetID}\">{$FleetParse['Fleet_back_time_set']}</span>)";

            $AllFleetParse .= parsetemplate($FleetTPL, $FleetParse);
        }

        $_Lang['FleetParsed'] = $AllFleetParse;

        $Parse['FleetControlContent'] = parsetemplate($FleetHeadTPL, $_Lang);
    }

    // --- END of Creating Fleet Control Marker! ---

    foreach($Player as $Key => $Val)
    {
        if(empty($Val))
        {
            if($Val === 0 OR $Val === '0')
            {
                $Val = '0';
            }
            else
            {
                $Val = '&nbsp;';
            }
        }
        $Parse['Player'.$Key] = $Val;
    }

    $Page = parsetemplate($TPL, $Parse);
    display($Page, $_Lang['PageTitle'], false, true);
}
else
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

?>
