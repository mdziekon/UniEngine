<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(!CheckAuth('supportadmin'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

includeLang('admin/alerts');
$PageTitle = $_Lang['PageTitle'];

$Now = time();
$MSGColor = '';

$BlockSelectQuery = false;
if(isset($_GET['deleteall']) && $_GET['deleteall'] == 'yes')
{
    doquery("TRUNCATE TABLE {{table}};", 'system_alerts');
    doquery("OPTIMIZE TABLE {{table}};", 'system_alerts');
    header('Location: alerts_list.php?msg=1');
    safeDie();
}

if(isset($_GET['msg']) && $_GET['msg'] == 1)
{
    $MSGColor = 'lime';
    $MSG = $_Lang['Alert_Truncated'];
    $BlockSelectQuery = true;
}

$PageTPL = gettemplate('admin/alertslist_body');
$RowsTPL = gettemplate('admin/alertslist_rows');

$CurrentPage = 0;
if(!empty($_GET['page']))
{
    $CurrentPage = intval($_GET['page']);
}
if($CurrentPage <= 0)
{
    $CurrentPage = 1;
}
$_Lang['CurrentPage'] = $CurrentPage;

$PerPage = 0;
if(!empty($_COOKIE['alertslist_pp']))
{
    $PerPage = intval($_COOKIE['alertslist_pp']);
}
if($PerPage <= 0)
{
    $PerPage = 20;
}
if(isset($_GET['pp']) && $_GET['pp'] > 0 && $_GET['pp'] != $PerPage)
{
    $TempPerPage = intval($_GET['pp']);
    if($TempPerPage > 0 AND $TempPerPage != $PerPage)
    {
        $PerPage = $TempPerPage;
        setcookie('alertslist_pp', $PerPage, $Now + TIME_YEAR);
    }
}
$_Lang['perpage_select_'.$PerPage] = 'selected';

$GetStart = (string) ((($CurrentPage - 1) * $PerPage) + 0);

if(!empty($_GET['action']))
{
    $MSGColor = 'red';
    $ID = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if($ID > 0)
    {
        $ThisAction = $_GET['action'];
        if($ThisAction == 'delete')
        {
            doquery("DELETE FROM {{table}} WHERE `ID` = {$ID};", 'system_alerts');
            if(getDBLink()->affected_rows > 0)
            {
                $MSG = $_Lang['Alert_deleted'];
                $MSGColor = 'lime';
            }
            else
            {
                $MSG = $_Lang['Alert_noexist'];
            }
        }
        else if($ThisAction == 'change_status')
        {
            $Query_GetAlert = '';
            $Query_GetAlert .= "SELECT `ID`, `Status` FROM {{table}} ";
            $Query_GetAlert .= "WHERE `ID` = {$ID} LIMIT 1;";
            $GetAlert = doquery($Query_GetAlert, 'system_alerts', true);
            if($GetAlert['ID'] == $ID)
            {
                $Status = intval($_GET['set_status']);
                if($Status < 0)
                {
                    $MSG = $_Lang['Alert_no_status_given'];
                }
                else
                {
                    if($GetAlert['Status'] == $Status)
                    {
                        $MSG = $_Lang['Alert_same_status'];
                        $MSGColor = 'orange';
                    }
                    else
                    {
                        if(!empty($_Lang['StatusList'][$Status]))
                        {
                            doquery("UPDATE {{table}} SET `Status` = {$Status} WHERE `ID` = {$ID};", 'system_alerts');
                            $MSG = sprintf($_Lang['Alert_status_changed'], $_Lang['StatusList'][$GetAlert['Status']]);
                            $MSGColor = 'lime';
                        }
                        else
                        {
                            $MSG = $_Lang['Alert_status_noexist'];
                        }
                    }
                }
            }
            else
            {
                $MSG = $_Lang['Alert_noexist'];
            }
        }
    }
    else
    {
        $MSG = $_Lang['No_id_given'];
    }
}

if($BlockSelectQuery !== true)
{
    $Query_GetTotalCount = "SELECT COUNT(*) AS `Count` FROM {{table}};";
    $Result_GetTotalCount = doquery($Query_GetTotalCount, 'system_alerts', true);
    $_TotalCount = $Result_GetTotalCount['Count'];
    if($_TotalCount > 0)
    {
        if($GetStart >= $_TotalCount)
        {
            $GetStart = 0;
        }

        $Query_GetAlerts = '';
        $Query_GetAlerts .= "SELECT `Alerts`.*, `Users`.`username` AS `Username` ";
        $Query_GetAlerts .= "FROM {{table}} AS `Alerts` ";
        $Query_GetAlerts .= "LEFT JOIN {{prefix}}users AS `Users` ON `Alerts`.`User_ID` = `Users`.`id` ";
        $Query_GetAlerts .= "ORDER BY `Alerts`.`ID` DESC ";
        $Query_GetAlerts .= "LIMIT {$GetStart}, {$PerPage};";
        $Result_GetAlerts = doquery($Query_GetAlerts, 'system_alerts');
    }
}

$Parse = $_Lang;
$Parse['Rows'] = '';
if(!empty($MSG))
{
    $Parse['System_MSG'] = '<tr><th class="pad5 '.$MSGColor.'" colspan="7">'.$MSG.'</th></tr><tr style="visibility: hidden;"><td style="height: 8px;"></td></tr>';
}

if(isset($_TotalCount) && $_TotalCount > 0)
{
    include_once($_EnginePath.'includes/functions/Pagination.php');
    $Pagin = CreatePaginationArray($_TotalCount, $PerPage, $CurrentPage, 7);
    $PaginationTPL = '<input type="button" class="pagin {$Classes}" name="goto_{$Value}" value="{$ShowValue}"/>';
    $PaginationViewOpt = array('CurrentPage_Classes' => 'fatB orange', 'Breaker_View' => '...');
    $CreatePagination = implode(' ', ParsePaginationArray($Pagin, $CurrentPage, $PaginationTPL, $PaginationViewOpt));
    $Parse['Pagination'] = $CreatePagination;
}
else
{
    $Parse['HidePaginRow'] = ' class="hide"';
}

if(!empty($Result_GetAlerts) AND $Result_GetAlerts->num_rows > 0)
{
    $GetData = array('users' => array(), 'ips' => array());
    while($FetchData = $Result_GetAlerts->fetch_assoc())
    {
        $FetchData['mainusers'][$FetchData['User_ID']] = $FetchData['User_ID'];
        $FetchData['alertusers'][$FetchData['User_ID']] = $FetchData['User_ID'];
        $GetData['users'][$FetchData['User_ID']] = $FetchData['User_ID'];

        if($FetchData['Status'] == 0)
        {
            $MarkAlerts[] = $FetchData['ID'];
        }

        if(!empty($FetchData['Other_Data']))
        {
            $FetchData['Other_Data'] = json_decode($FetchData['Other_Data'], true);
            if($FetchData['Code'] == 1)
            {
                $FetchData['mainusers'][$FetchData['Other_Data']['TargetUserID']] = $FetchData['Other_Data']['TargetUserID'];
                $FetchData['alertusers'][$FetchData['Other_Data']['TargetUserID']] = $FetchData['Other_Data']['TargetUserID'];
                $GetData['users'][$FetchData['Other_Data']['TargetUserID']] = $FetchData['Other_Data']['TargetUserID'];
                if(!empty($FetchData['Other_Data']['OtherUsers']))
                {
                    foreach($FetchData['Other_Data']['OtherUsers'] as $ThisUserID)
                    {
                        $FetchData['alertusers'][$ThisUserID] = $ThisUserID;
                        $GetData['users'][$ThisUserID] = $ThisUserID;
                    }
                }
                foreach($FetchData['Other_Data']['Intersect'] as $IPData)
                {
                    $GetData['ips'][$IPData['IPID']] = $IPData['IPID'];
                }
            }
            else if($FetchData['Code'] == 2)
            {
                $FetchData['mainusers'][$FetchData['Other_Data']['ReferrerID']] = $FetchData['Other_Data']['ReferrerID'];
                $FetchData['alertusers'][$FetchData['Other_Data']['ReferrerID']] = $FetchData['Other_Data']['ReferrerID'];
                $GetData['users'][$FetchData['Other_Data']['ReferrerID']] = $FetchData['Other_Data']['ReferrerID'];
                if(!empty($FetchData['Other_Data']['OtherUsers']))
                {
                    foreach($FetchData['Other_Data']['OtherUsers'] as $ThisUserID)
                    {
                        $FetchData['alertusers'][$ThisUserID] = $ThisUserID;
                        $GetData['users'][$ThisUserID] = $ThisUserID;
                    }
                }
                foreach($FetchData['Other_Data']['Intersect'] as $IPData)
                {
                    $GetData['ips'][$IPData['IPID']] = $IPData['IPID'];
                }
            }
            else if($FetchData['Code'] == 3)
            {
                $GetData['ips'][$FetchData['Other_Data']['IPID']] = $FetchData['Other_Data']['IPID'];
            }
            else if($FetchData['Code'] == 4)
            {
                $FetchData['mainusers'][$FetchData['Other_Data']['TargetUserID']] = $FetchData['Other_Data']['TargetUserID'];
                $FetchData['alertusers'][$FetchData['Other_Data']['TargetUserID']] = $FetchData['Other_Data']['TargetUserID'];
                $GetData['users'][$FetchData['Other_Data']['TargetUserID']] = $FetchData['Other_Data']['TargetUserID'];
                if($FetchData['Other_Data']['SameAlly'] > 0)
                {
                    $GetData['allys'][$FetchData['Other_Data']['SameAlly']] = $FetchData['Other_Data']['SameAlly'];
                }
                else if(!empty($FetchData['Other_Data']['AllyPact']))
                {
                    $GetData['allys'][$FetchData['Other_Data']['AllyPact']['SenderAlly']] = $FetchData['Other_Data']['AllyPact']['SenderAlly'];
                    $GetData['allys'][$FetchData['Other_Data']['AllyPact']['TargetAlly']] = $FetchData['Other_Data']['AllyPact']['TargetAlly'];
                }
                $GetData['fleets'][$FetchData['Other_Data']['FleetID']] = $FetchData['Other_Data']['FleetID'];
            }
        }

        $FetchData['Sender'] = $_Lang['Senders'][$FetchData['Sender']];
        $FetchData['ThisDate'] = prettyDate('d m Y', $FetchData['Date'], 1);
        $FetchData['ThisTime'] = date('H:i:s', $FetchData['Date']);
        $FetchData['Type'] = $_Lang['Types'][$FetchData['Type']];
        $FetchData['Importance'] = $FetchData['Importance'];
        $FetchData['Status'] = $_Lang['StatusList'][$FetchData['Status']];
        $FetchData['AllUsers'] = implode('|', $FetchData['alertusers']);
        $FetchData['MainUsers'] = implode('|', $FetchData['mainusers']);

        $Rows[] = $FetchData;
    }
}

if(!empty($Rows))
{
    if(!empty($MarkAlerts))
    {
        $Query_UpdateAlerts = "UPDATE {{table}} SET `Status` = 1 WHERE `ID` IN (".implode(', ', $MarkAlerts).");";
        doquery($Query_UpdateAlerts, 'system_alerts');
    }
    if(!empty($GetData['users']))
    {
        $Query_GetUsers = '';
        $Query_GetUsers .= "SELECT `id`, `username` FROM {{table}} WHERE ";
        $Query_GetUsers .= "`id` IN (".implode(', ', $GetData['users']).") ";
        $Query_GetUsers .= "LIMIT ".count($GetData['users']).";";
        $Result_GetUsers = doquery($Query_GetUsers, 'users');
        if($Result_GetUsers->num_rows > 0)
        {
            while($FetchData = $Result_GetUsers->fetch_assoc())
            {
                $DataArray['users'][$FetchData['id']] = array('username' => $FetchData['username']);
            }
        }
        foreach($GetData['users'] as $ThisUserID)
        {
            if(empty($DataArray['users'][$ThisUserID]))
            {
                $DataArray['users'][$ThisUserID] = array('username' => $_Lang['AlertCodes_Texts']['DeletedUser'], 'deleted' => true);
            }
        }
    }
    if(!empty($GetData['allys']))
    {
        $Query_GetAllys = '';
        $Query_GetAllys .= "SELECT `id`, `ally_name`, `ally_tag` FROM {{table}} WHERE ";
        $Query_GetAllys .= "`id` IN (".implode(', ', $GetData['allys']).") ";
        $Query_GetAllys .= "LIMIT ".count($GetData['allys']).";";
        $Result_GetAllys = doquery($Query_GetAllys, 'alliance');
        if($Result_GetAllys->num_rows > 0)
        {
            while($FetchData = $Result_GetAllys->fetch_assoc())
            {
                $DataArray['allys'][$FetchData['id']] = array('ally_name' => $FetchData['ally_name'], 'ally_tag' => $FetchData['ally_tag']);
            }
        }
        foreach($GetData['allys'] as $ThisAllyID)
        {
            if(empty($DataArray['allys'][$ThisAllyID]))
            {
                $DataArray['allys'][$ThisAllyID] = array('ally_name' => $_Lang['AlertCodes_Texts']['DeletedAlly'], 'deleted' => true);
            }
        }
    }
    if(!empty($GetData['ips']))
    {
        $Query_GetIPs = '';
        $Query_GetIPs .= "SELECT `ID`, `Value` FROM {{table}} WHERE ";
        $Query_GetIPs .= "`ID` IN (".implode(', ', $GetData['ips']).") ";
        $Query_GetIPs .= "LIMIT ".count($GetData['ips']).";";
        $Result_GetIPs = doquery($Query_GetIPs, 'used_ip_and_ua');
        if($Result_GetIPs->num_rows > 0)
        {
            while($FetchData = $Result_GetIPs->fetch_assoc())
            {
                $DataArray['ips'][$FetchData['ID']] = array('Value' => $FetchData['Value']);
            }
        }
    }
    if(!empty($GetData['fleets']))
    {
        $Query_GetFleets = '';
        $Query_GetFleets .= "SELECT `Fleet_ID`, `Fleet_Time_Start`, `Fleet_Calculated_Mission`, `Fleet_TurnedBack` ";
        $Query_GetFleets .= "FROM {{table}} WHERE ";
        $Query_GetFleets .= "`Fleet_ID` IN (".implode(', ', $GetData['fleets']).") ";
        $Query_GetFleets .= "LIMIT ".count($GetData['fleets']).";";
        $Result_GetFleets = doquery($Query_GetFleets, 'fleet_archive');
        if($Result_GetFleets->num_rows > 0)
        {
            while($FetchData = $Result_GetFleets->fetch_assoc())
            {
                $DataArray['fleets'][$FetchData['Fleet_ID']] = $FetchData;
            }
        }
    }

    foreach($Rows as $RowData)
    {
        if(!empty($RowData['Other_Data']))
        {
            if($RowData['Code'] == 1)
            {
                // --- Fleet MultiAcc Detected ---
                // Parse User - Alert Sender
                if(!isset($DataArray['users'][$RowData['User_ID']]['deleted']))
                {
                    $RowData['CodeVars'][0] = vsprintf($_Lang['AlertCodes_Texts']['UserLink'], array
                    (
                        $RowData['User_ID'], $DataArray['users'][$RowData['User_ID']]['username'], $RowData['User_ID']
                    ));
                }
                else
                {
                    $RowData['CodeVars'][0] = vsprintf($_Lang['AlertCodes_Texts']['DeletedUserID'], array
                    (
                        $RowData['User_ID']
                    ));
                }
                // Parse Mission Type
                $RowData['CodeVars'][1] = vsprintf($_Lang['AlertCodes_Texts']['FleetInfo'], array
                (
                    $_Lang['type_mission'][$RowData['Other_Data']['MissionID']]
                ));
                // Parse User - Target Owner
                if(!isset($DataArray['users'][$RowData['Other_Data']['TargetUserID']]['deleted']))
                {
                    $RowData['CodeVars'][2] = vsprintf($_Lang['AlertCodes_Texts']['UserLink'], array
                    (
                        $RowData['Other_Data']['TargetUserID'], $DataArray['users'][$RowData['Other_Data']['TargetUserID']]['username'], $RowData['Other_Data']['TargetUserID']
                    ));
                }
                else
                {
                    $RowData['CodeVars'][2] = vsprintf($_Lang['AlertCodes_Texts']['DeletedUserID'], array
                    (
                        $RowData['Other_Data']['TargetUserID']
                    ));
                }
                // Parse FleetID
                if($RowData['Other_Data']['FleetID'] > 0)
                {
                    $RowData['CodeVars'][3] = vsprintf($_Lang['AlertCodes_Texts']['FleetBlock_NonBlocked_Text'], array
                    (
                        $RowData['Other_Data']['FleetID']
                    ));
                }
                else
                {
                    $RowData['CodeVars'][3] = $_Lang['AlertCodes_Texts']['FleetBlock_Blocked_Text'];
                }
                // Parse DeclarationID
                if(isset($RowData['Other_Data']['DeclarationID']) && $RowData['Other_Data']['DeclarationID'] > 0)
                {
                    $RowData['CodeVars'][4] = vsprintf($_Lang['AlertCodes_Texts']['MultiACCDeclaration_Exists_Text'], array
                    (
                        $RowData['Other_Data']['DeclarationID']
                    ));
                }
                else
                {
                    $RowData['CodeVars'][4] = $_Lang['AlertCodes_Texts']['MultiACCDeclaration_None_Text'];
                }

                // Parse IP Intersection
                foreach($RowData['Other_Data']['Intersect'] as $ThisIPData)
                {
                    $ThisIPData['TimeMax'] = ($ThisIPData['SenderData']['LastTime'] > $ThisIPData['TargetData']['LastTime'] ? $ThisIPData['SenderData']['LastTime'] : $ThisIPData['TargetData']['LastTime']);
                    $ThisIPData['TimeMaxDiff'] = $Now - (SERVER_MAINOPEN_TSTAMP + $ThisIPData['TimeMax']);
                    if($ThisIPData['TimeMaxDiff'] >= (TIME_DAY * 30))
                    {
                        $ThisIPData['TimeMaxDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Big'];
                    }
                    else if($ThisIPData['TimeMaxDiff'] >= (TIME_DAY * 1.5))
                    {
                        $ThisIPData['TimeMaxDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Medium'];
                    }
                    else
                    {
                        $ThisIPData['TimeMaxDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Small'];
                    }

                    $ThisIPData['TimeDiff'] = $ThisIPData['SenderData']['LastTime'] - $ThisIPData['TargetData']['LastTime'];
                    if($ThisIPData['TimeDiff'] < 0)
                    {
                        $ThisIPData['TimeDiff'] *= -1;
                    }
                    if($ThisIPData['TimeDiff'] >= (TIME_DAY * 30))
                    {
                        $ThisIPData['TimeDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Big'];
                    }
                    else if($ThisIPData['TimeDiff'] >= (TIME_DAY * 1.5))
                    {
                        $ThisIPData['TimeDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Medium'];
                    }
                    else
                    {
                        $ThisIPData['TimeDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Small'];
                    }

                    // Parse IP Intersection User - Alert Sender
                    $ThisIPData['SenderData']['LastTimeStamp'] = SERVER_MAINOPEN_TSTAMP + $ThisIPData['SenderData']['LastTime'];
                    if(!isset($DataArray['users'][$RowData['User_ID']]['deleted']))
                    {
                        $ThisIPData['UsersData'][] = vsprintf($_Lang['AlertCodes_Texts']['IPIntersect_UserExist'], array
                        (
                            $RowData['User_ID'], $DataArray['users'][$RowData['User_ID']]['username'], $RowData['User_ID'],
                            prettyNumber($ThisIPData['SenderData']['Count']), prettyDate('d m Y, H:i:s', $ThisIPData['SenderData']['LastTimeStamp'], 1)
                        ));
                    }
                    else
                    {
                        $ThisIPData['UsersData'][] = vsprintf($_Lang['AlertCodes_Texts']['IPIntersect_UserDeleted'], array
                        (
                            $RowData['User_ID'],
                            prettyNumber($ThisIPData['SenderData']['Count']), prettyDate('d m Y, H:i:s', $ThisIPData['SenderData']['LastTimeStamp'], 1)
                        ));
                    }
                    // Parse IP Intersection User - Target Owner
                    $ThisIPData['TargetData']['LastTimeStamp'] = SERVER_MAINOPEN_TSTAMP + $ThisIPData['TargetData']['LastTime'];
                    if(!isset($DataArray['users'][$RowData['Other_Data']['TargetUserID']]['deleted']))
                    {
                        $ThisIPData['UsersData'][] = vsprintf($_Lang['AlertCodes_Texts']['IPIntersect_UserExist'], array
                        (
                            $RowData['Other_Data']['TargetUserID'], $DataArray['users'][$RowData['Other_Data']['TargetUserID']]['username'], $RowData['Other_Data']['TargetUserID'],
                            prettyNumber($ThisIPData['TargetData']['Count']), prettyDate('d m Y, H:i:s', $ThisIPData['TargetData']['LastTimeStamp'], 1)
                        ));
                    }
                    else
                    {
                        $ThisIPData['UsersData'][] = vsprintf($_Lang['AlertCodes_Texts']['IPIntersect_UserDeleted'], array
                        (
                            $RowData['Other_Data']['TargetUserID'],
                            prettyNumber($ThisIPData['TargetData']['Count']), prettyDate('d m Y, H:i:s', $ThisIPData['TargetData']['LastTimeStamp'], 1)
                        ));
                    }
                    // Parse IP Intersection Row
                    $RowData['IntersectData'][] = vsprintf($_Lang['AlertCodes_Texts']['IPIntersect_Main'], array
                    (
                        $ThisIPData['IPID'], $DataArray['ips'][$ThisIPData['IPID']]['Value'], $ThisIPData['IPID'],
                        $ThisIPData['TimeDiff_Color'], pretty_time($ThisIPData['TimeDiff']),
                        $ThisIPData['TimeMaxDiff_Color'], pretty_time($ThisIPData['TimeMaxDiff']),
                        implode('<br/>', $ThisIPData['UsersData'])
                    ));
                }
                $RowData['CodeVars'][5] = implode('<br/>', $RowData['IntersectData']);
                // Parse Other Users
                if(!empty($RowData['Other_Data']['OtherUsers']))
                {
                    foreach($RowData['Other_Data']['OtherUsers'] as $ThisUserID)
                    {
                        if(!isset($DataArray['users'][$ThisUserID]['deleted']))
                        {
                            $RowData['CodeVars'][7][] = vsprintf($_Lang['AlertCodes_Texts']['UserLink'], array
                            (
                                $ThisUserID, $DataArray['users'][$ThisUserID]['username'], $ThisUserID
                            ));
                        }
                        else
                        {
                            $RowData['CodeVars'][7][] = vsprintf($_Lang['AlertCodes_Texts']['DeletedUserID'], array
                            (
                                $ThisUserID,
                            ));
                        }
                    }
                    $RowData['CodeVars'][6] = vsprintf($_Lang['AlertCodes_Texts']['OtherUsers_Count'], array
                    (
                        prettyNumber(count($RowData['CodeVars'][7]))
                    ));
                    $RowData['CodeVars'][7] = implode(', ', $RowData['CodeVars'][7]);
                }
                else
                {
                    $RowData['CodeVars'][6] = '';
                    $RowData['CodeVars'][7] = $_Lang['AlertCodes_Texts']['OtherUsers_None'];
                }
            }
            else if($RowData['Code'] == 2)
            {
                // --- Register MultiAcc Detected ---
                // Include Tasks Lang
                if(!isset($_Included_TasksLang))
                {
                    includeLang('tasks');
                    $_Included_TasksLang = true;
                }
                // Parse User - New User
                if(!isset($DataArray['users'][$RowData['User_ID']]['deleted']))
                {
                    $RowData['CodeVars'][0] = vsprintf($_Lang['AlertCodes_Texts']['UserLink'], array
                    (
                        $RowData['User_ID'], $DataArray['users'][$RowData['User_ID']]['username'], $RowData['User_ID']
                    ));
                }
                else
                {
                    $RowData['CodeVars'][0] = vsprintf($_Lang['AlertCodes_Texts']['DeletedUserID'], array
                    (
                        $RowData['User_ID']
                    ));
                }
                // Parse User - Referrer User
                if(!isset($DataArray['users'][$RowData['Other_Data']['ReferrerID']]['deleted']))
                {
                    $RowData['CodeVars'][1] = vsprintf($_Lang['AlertCodes_Texts']['UserLink'], array
                    (
                        $RowData['Other_Data']['ReferrerID'], $DataArray['users'][$RowData['Other_Data']['ReferrerID']]['username'], $RowData['Other_Data']['ReferrerID']
                    ));
                }
                else
                {
                    $RowData['CodeVars'][1] = vsprintf($_Lang['AlertCodes_Texts']['DeletedUserID'], array
                    (
                        $RowData['Other_Data']['ReferrerID']
                    ));
                }

                // Parse Tasks Data
                if(!empty($RowData['Other_Data']['Tasks']))
                {
                    foreach($RowData['Other_Data']['Tasks'] as $ThisData)
                    {
                        $RowData['CodeVars'][2][] = vsprintf($_Lang['AlertCodes_Texts']['TaskData'], array
                        (
                            $_Lang['Tasks'][$ThisData['TaskID']]['name'], $ThisData['TaskID'],
                            ($ThisData['TaskStatus'] >= $ThisData['TaskLimit'] ? $_Lang['AlertCodes_Texts']['TaskData_StatusDone'] : $_Lang['AlertCodes_Texts']['TaskData_StatusNotDone']),
                            $ThisData['TaskStatus'], $ThisData['TaskLimit']
                        ));
                    }
                    $RowData['CodeVars'][2] = implode('<br/>', $RowData['CodeVars'][2]);
                }
                else
                {
                    $RowData['CodeVars'][2] = $_Lang['AlertCodes_Texts']['NewUser_NoTasks'];
                }

                // Parse IP Intersection
                foreach($RowData['Other_Data']['Intersect'] as $ThisIPData)
                {
                    $ThisIPData['TimeMax'] = ($ThisIPData['NewUser']['LastTime'] > $ThisIPData['OldUser']['LastTime'] ? $ThisIPData['NewUser']['LastTime'] : $ThisIPData['OldUser']['LastTime']);
                    $ThisIPData['TimeMaxDiff'] = $Now - (SERVER_MAINOPEN_TSTAMP + $ThisIPData['TimeMax']);
                    if($ThisIPData['TimeMaxDiff'] >= (TIME_DAY * 30))
                    {
                        $ThisIPData['TimeMaxDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Big'];
                    }
                    else if($ThisIPData['TimeMaxDiff'] >= (TIME_DAY * 1.5))
                    {
                        $ThisIPData['TimeMaxDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Medium'];
                    }
                    else
                    {
                        $ThisIPData['TimeMaxDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Small'];
                    }

                    $ThisIPData['TimeDiff'] = $ThisIPData['NewUser']['LastTime'] - $ThisIPData['OldUser']['LastTime'];
                    if($ThisIPData['TimeDiff'] < 0)
                    {
                        $ThisIPData['TimeDiff'] *= -1;
                    }
                    if($ThisIPData['TimeDiff'] >= (TIME_DAY * 30))
                    {
                        $ThisIPData['TimeDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Big'];
                    }
                    else if($ThisIPData['TimeDiff'] >= (TIME_DAY * 1.5))
                    {
                        $ThisIPData['TimeDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Medium'];
                    }
                    else
                    {
                        $ThisIPData['TimeDiff_Color'] = $_Lang['AlertCodes_Texts']['IPIntersect_TimeDiff_Small'];
                    }

                    // Parse IP Intersection User - New User
                    $ThisIPData['NewUser']['LastTimeStamp'] = SERVER_MAINOPEN_TSTAMP + $ThisIPData['NewUser']['LastTime'];
                    if(!isset($DataArray['users'][$RowData['User_ID']]['deleted']))
                    {
                        $ThisIPData['UsersData'][] = vsprintf($_Lang['AlertCodes_Texts']['IPIntersect_UserExist'], array
                        (
                            $RowData['User_ID'], $DataArray['users'][$RowData['User_ID']]['username'], $RowData['User_ID'],
                            prettyNumber($ThisIPData['NewUser']['Count']), prettyDate('d m Y, H:i:s', $ThisIPData['NewUser']['LastTimeStamp'], 1)
                        ));
                    }
                    else
                    {
                        $ThisIPData['UsersData'][] = vsprintf($_Lang['AlertCodes_Texts']['IPIntersect_UserDeleted'], array
                        (
                            $RowData['User_ID'],
                            prettyNumber($ThisIPData['NewUser']['Count']), prettyDate('d m Y, H:i:s', $ThisIPData['NewUser']['LastTimeStamp'], 1)
                        ));
                    }
                    // Parse IP Intersection User - Referrer User
                    $ThisIPData['OldUser']['LastTimeStamp'] = SERVER_MAINOPEN_TSTAMP + $ThisIPData['OldUser']['LastTime'];
                    if(!isset($DataArray['users'][$RowData['Other_Data']['ReferrerID']]['deleted']))
                    {
                        $ThisIPData['UsersData'][] = vsprintf($_Lang['AlertCodes_Texts']['IPIntersect_UserExist'], array
                        (
                            $RowData['Other_Data']['ReferrerID'], $DataArray['users'][$RowData['Other_Data']['ReferrerID']]['username'], $RowData['Other_Data']['ReferrerID'],
                            prettyNumber($ThisIPData['OldUser']['Count']), prettyDate('d m Y, H:i:s', $ThisIPData['OldUser']['LastTimeStamp'], 1)
                        ));
                    }
                    else
                    {
                        $ThisIPData['UsersData'][] = vsprintf($_Lang['AlertCodes_Texts']['IPIntersect_UserDeleted'], array
                        (
                            $RowData['Other_Data']['ReferrerID'],
                            prettyNumber($ThisIPData['OldUser']['Count']), prettyDate('d m Y, H:i:s', $ThisIPData['OldUser']['LastTimeStamp'], 1)
                        ));
                    }
                    // Parse IP Intersection Row
                    $RowData['IntersectData'][] = vsprintf($_Lang['AlertCodes_Texts']['IPIntersect_Main'], array
                    (
                        $ThisIPData['IPID'], $DataArray['ips'][$ThisIPData['IPID']]['Value'], $ThisIPData['IPID'],
                        $ThisIPData['TimeDiff_Color'], pretty_time($ThisIPData['TimeDiff']),
                        $ThisIPData['TimeMaxDiff_Color'], pretty_time($ThisIPData['TimeMaxDiff']),
                        implode('<br/>', $ThisIPData['UsersData'])
                    ));
                }
                $RowData['CodeVars'][3] = implode('<br/>', $RowData['IntersectData']);

                // Parse Other Users
                if(!empty($RowData['Other_Data']['OtherUsers']))
                {
                    foreach($RowData['Other_Data']['OtherUsers'] as $ThisUserID)
                    {
                        if(!isset($DataArray['users'][$ThisUserID]['deleted']))
                        {
                            $RowData['CodeVars'][5][] = vsprintf($_Lang['AlertCodes_Texts']['UserLink'], array
                            (
                                $ThisUserID, $DataArray['users'][$ThisUserID]['username'], $ThisUserID
                            ));
                        }
                        else
                        {
                            $RowData['CodeVars'][5][] = vsprintf($_Lang['AlertCodes_Texts']['DeletedUserID'], array
                            (
                                $ThisUserID,
                            ));
                        }
                    }
                    $RowData['CodeVars'][4] = vsprintf($_Lang['AlertCodes_Texts']['OtherUsers_Count'], array
                    (
                        prettyNumber(count($RowData['CodeVars'][5]))
                    ));
                    $RowData['CodeVars'][5] = implode(', ', $RowData['CodeVars'][5]);
                }
                else
                {
                    $RowData['CodeVars'][4] = '';
                    $RowData['CodeVars'][5] = $_Lang['AlertCodes_Texts']['OtherUsers_None'];
                }
            }
            else if($RowData['Code'] == 3)
            {
                // --- Register with Proxy Detected ---
                // Parse User - New User
                if(!isset($DataArray['users'][$RowData['User_ID']]['deleted']))
                {
                    $RowData['CodeVars'][0] = vsprintf($_Lang['AlertCodes_Texts']['UserLink'], array
                    (
                        $RowData['User_ID'], $DataArray['users'][$RowData['User_ID']]['username'], $RowData['User_ID']
                    ));
                }
                else
                {
                    $RowData['CodeVars'][0] = vsprintf($_Lang['AlertCodes_Texts']['DeletedUserID'], array
                    (
                        $RowData['User_ID']
                    ));
                }
                // Parse IP
                $RowData['CodeVars'][1] = vsprintf($_Lang['AlertCodes_Texts']['NewUser_IPProxy'], array
                (
                    $RowData['Other_Data']['IPID'], $DataArray['ips'][$RowData['Other_Data']['IPID']]['Value'], $RowData['Other_Data']['IPID'],
                    ($RowData['Other_Data']['RegIP'] ? $_Lang['AlertCodes_Texts']['NewUser_IPProxy_RegIP'] : $_Lang['AlertCodes_Texts']['NewUser_IPProxy_NoRegIP'])
                ));
            }
            else if($RowData['Code'] == 4)
            {
                // --- ResourcePush Detected ---
                // Parse User - Alert Sender
                if(!isset($DataArray['users'][$RowData['User_ID']]['deleted']))
                {
                    $RowData['CodeVars'][0] = vsprintf($_Lang['AlertCodes_Texts']['UserLink'], array
                    (
                        $RowData['User_ID'], $DataArray['users'][$RowData['User_ID']]['username'], $RowData['User_ID']
                    ));
                }
                else
                {
                    $RowData['CodeVars'][0] = vsprintf($_Lang['AlertCodes_Texts']['DeletedUserID'], array
                    (
                        $RowData['User_ID']
                    ));
                }
                // Parse User - Target Owner
                if(!isset($DataArray['users'][$RowData['Other_Data']['TargetUserID']]['deleted']))
                {
                    $RowData['CodeVars'][1] = vsprintf($_Lang['AlertCodes_Texts']['UserLink'], array
                    (
                        $RowData['Other_Data']['TargetUserID'], $DataArray['users'][$RowData['Other_Data']['TargetUserID']]['username'], $RowData['Other_Data']['TargetUserID']
                    ));
                }
                else
                {
                    $RowData['CodeVars'][1] = vsprintf($_Lang['AlertCodes_Texts']['DeletedUserID'], array
                    (
                        $RowData['Other_Data']['TargetUserID']
                    ));
                }
                // Parse Users Acquaintance
                if($RowData['Other_Data']['SameAlly'] > 0)
                {
                    if(!isset($DataArray['allys'][$RowData['Other_Data']['SameAlly']]['deleted']))
                    {
                        $RowData['CodeVars'][2][0] = vsprintf($_Lang['AlertCodes_Texts']['AllyLink'], array
                        (
                            $DataArray['allys'][$RowData['Other_Data']['SameAlly']]['ally_name'],
                            $DataArray['allys'][$RowData['Other_Data']['SameAlly']]['ally_tag'],
                            $RowData['Other_Data']['SameAlly']
                        ));
                    }
                    else
                    {
                        $RowData['CodeVars'][2][0] = vsprintf($_Lang['AlertCodes_Texts']['DeletedAllyID'], array
                        (
                            $RowData['Other_Data']['SameAlly']
                        ));
                    }
                    $RowData['CodeVars'][2][0] = vsprintf($_Lang['AlertCodes_Texts']['PushAlert_UsersInAlly'], $RowData['CodeVars'][2][0]);
                }
                else if(!empty($RowData['Other_Data']['AllyPact']))
                {
                    if(!isset($DataArray['allys'][$RowData['Other_Data']['AllyPact']['SenderAlly']]['deleted']))
                    {
                        $RowData['CodeVars'][2][1][] = vsprintf($_Lang['AlertCodes_Texts']['AllyLink'], array
                        (
                            $DataArray['allys'][$RowData['Other_Data']['AllyPact']['SenderAlly']]['ally_name'],
                            $DataArray['allys'][$RowData['Other_Data']['AllyPact']['SenderAlly']]['ally_tag'],
                            $RowData['Other_Data']['AllyPact']['SenderAlly']
                        ));
                    }
                    else
                    {
                        $RowData['CodeVars'][2][1][] = vsprintf($_Lang['AlertCodes_Texts']['DeletedAllyID'], array
                        (
                            $RowData['Other_Data']['AllyPact']['SenderAlly']
                        ));
                    }
                    if(!isset($DataArray['allys'][$RowData['Other_Data']['AllyPact']['TargetAlly']]['deleted']))
                    {
                        $RowData['CodeVars'][2][1][] = vsprintf($_Lang['AlertCodes_Texts']['AllyLink'], array
                        (
                            $DataArray['allys'][$RowData['Other_Data']['AllyPact']['TargetAlly']]['ally_name'],
                            $DataArray['allys'][$RowData['Other_Data']['AllyPact']['TargetAlly']]['ally_tag'],
                            $RowData['Other_Data']['AllyPact']['TargetAlly']
                        ));
                    }
                    else
                    {
                        $RowData['CodeVars'][2][1][] = vsprintf($_Lang['AlertCodes_Texts']['DeletedAllyID'], array
                        (
                            $RowData['Other_Data']['AllyPact']['TargetAlly']
                        ));
                    }
                    $RowData['CodeVars'][2][1] = vsprintf($_Lang['AlertCodes_Texts']['PushAlert_UsersHasPact'], $RowData['CodeVars'][2][1]);
                }
                if($RowData['Other_Data']['BuddyFriends'] === true)
                {
                    $RowData['CodeVars'][2][2] = $_Lang['AlertCodes_Texts']['PushAlert_UsersAreBuddy'];
                }
                if(!empty($RowData['CodeVars'][2]))
                {
                    $RowData['CodeVars'][2] = implode(', ', $RowData['CodeVars'][2]);
                }
                else
                {
                    $RowData['CodeVars'][2] = $_Lang['AlertCodes_Texts']['PushAlert_UsersDontKnowEachother'];
                }
                // Parse User Stats Difference
                $RowData['CodeVars'][3] = prettyNumber($RowData['Other_Data']['Stats']['Target']['Points'] - $RowData['Other_Data']['Stats']['Sender']['Points']);
                if($RowData['Other_Data']['Stats']['Sender']['Points'] > 0)
                {
                    $RowData['CodeVars'][4] = prettyNumber(($RowData['Other_Data']['Stats']['Target']['Points'] / $RowData['Other_Data']['Stats']['Sender']['Points']) * 100);
                }
                else
                {
                    $RowData['CodeVars'][4] = '&infin;';
                }
                $RowData['CodeVars'][5] = prettyNumber($RowData['Other_Data']['Stats']['Sender']['Position'] - $RowData['Other_Data']['Stats']['Target']['Position']);
                // Parse Users StatsInfo
                if(!isset($DataArray['users'][$RowData['User_ID']]['deleted']))
                {
                    $RowData['CodeVars'][6] = vsprintf($_Lang['AlertCodes_Texts']['PushAlert_UserExist'], array
                    (
                        $RowData['User_ID'], $DataArray['users'][$RowData['User_ID']]['username'], $RowData['User_ID'],
                        prettyNumber($RowData['Other_Data']['Stats']['Sender']['Points']),
                        prettyNumber($RowData['Other_Data']['Stats']['Sender']['Position'])
                    ));
                }
                else
                {
                    $RowData['CodeVars'][6] = vsprintf($_Lang['AlertCodes_Texts']['PushAlert_UserDeleted'], array
                    (
                        $RowData['User_ID'],
                        prettyNumber($RowData['Other_Data']['Stats']['Sender']['Points']),
                        prettyNumber($RowData['Other_Data']['Stats']['Sender']['Position'])
                    ));
                }
                if(!isset($DataArray['users'][$RowData['Other_Data']['TargetUserID']]['deleted']))
                {
                    $RowData['CodeVars'][7] = vsprintf($_Lang['AlertCodes_Texts']['PushAlert_UserExist'], array
                    (
                        $RowData['Other_Data']['TargetUserID'], $DataArray['users'][$RowData['Other_Data']['TargetUserID']]['username'], $RowData['Other_Data']['TargetUserID'],
                        prettyNumber($RowData['Other_Data']['Stats']['Target']['Points']),
                        prettyNumber($RowData['Other_Data']['Stats']['Target']['Position'])
                    ));
                }
                else
                {
                    $RowData['CodeVars'][7] = vsprintf($_Lang['AlertCodes_Texts']['PushAlert_UserDeleted'], array
                    (
                        $RowData['Other_Data']['TargetUserID'],
                        prettyNumber($RowData['Other_Data']['Stats']['Target']['Points']),
                        prettyNumber($RowData['Other_Data']['Stats']['Target']['Position'])
                    ));
                }
                // Parse FleetID
                if($DataArray['fleets'][$RowData['Other_Data']['FleetID']]['Fleet_Calculated_Mission'] == 1)
                {
                    $RowData['CodeVars'][8] = $_Lang['AlertCodes_Texts']['PushAlert_FleetState_Delivered'];
                }
                else if($DataArray['fleets'][$RowData['Other_Data']['FleetID']]['Fleet_Time_Start'] <= $Now)
                {
                    $RowData['CodeVars'][8] = $_Lang['AlertCodes_Texts']['PushAlert_FleetState_GoalAchieved'];
                }
                else if($DataArray['fleets'][$RowData['Other_Data']['FleetID']]['Fleet_TurnedBack'])
                {
                    $RowData['CodeVars'][8] = $_Lang['AlertCodes_Texts']['PushAlert_FleetState_TurnedBack'];
                }
                else
                {
                    $RowData['CodeVars'][8] = sprintf($_Lang['AlertCodes_Texts']['PushAlert_FleetState_Flying'], pretty_time($DataArray['fleets'][$RowData['Other_Data']['FleetID']]['Fleet_Time_Start'] - $Now));
                }
                $RowData['CodeVars'][9] = $RowData['Other_Data']['FleetID'];
                // Parse Resources
                foreach($RowData['Other_Data']['Resources'] as $ThisKey => $ThisValue)
                {
                    if($ThisValue > 0)
                    {
                        $RowData['CodeVars'][10][] = vsprintf($_Lang['AlertCodes_Texts']['PushAlert_ResourceLine'], array
                        (
                            $_Lang['AlertCodes_Texts']['PushAlert_Res_'.$ThisKey], prettyNumber($ThisValue)
                        ));
                    }
                }
                $RowData['CodeVars'][10] = implode('<br/>', $RowData['CodeVars'][10]);
            }
            ksort($RowData['CodeVars']);
            $RowData['Data'] = vsprintf($_Lang['AlertCodes'][$RowData['Code']], $RowData['CodeVars']);
        }
        else
        {
            $RowData['Data'] = '&nbsp;';
        }

        $Parse['Rows'] .= parsetemplate($RowsTPL, $RowData);
    }
}
else
{
    if($CurrentPage > 1 AND $_TotalCount > 0)
    {
        $ThisWarning = $_Lang['No_Alerts_ThisPage'];
    }
    else
    {
        $ThisWarning = $_Lang['No_Alerts'];
    }
    $Parse['Rows'] = '<tr><th class="pad5 red" colspan="7">'.$ThisWarning.'</td></tr>';
}

$Page = parsetemplate($PageTPL, $Parse);
display($Page, $PageTitle, false, true);

?>
