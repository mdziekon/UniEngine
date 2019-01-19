<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

includeLang('admin/iplog');

if(!CheckAuth('sgo'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

$PageTPL = gettemplate('admin/iplog_body');
$PageTitle = $_Lang['PageTitle'];

if(!empty($_POST['proxyEdit']))
{
    $Proxy_IPID = intval($_POST['proxyEdit']);
    if($Proxy_IPID > 0)
    {
        $Query_GetIP = '';
        $Query_GetIP .= "SELECT `ID`, `isProxy` FROM {{table}} ";
        $Query_GetIP .= "WHERE `ID` = {$Proxy_IPID} AND `Type` = 'ip' LIMIT 1;";
        $Result_GetIP = doquery($Query_GetIP, 'used_ip_and_ua', true);
        if($Result_GetIP['ID'] == $Proxy_IPID)
        {
            $Query_UpdateIP = '';
            $Query_UpdateIP .= "UPDATE {{table}} SET ";
            $Query_UpdateIP .= "`isProxy` = ".($Result_GetIP['isProxy'] == 1 ? 'false' : 'true')." ";
            $Query_UpdateIP .= "WHERE `ID` = {$Proxy_IPID} LIMIT 1;";
            doquery($Query_UpdateIP, 'used_ip_and_ua');

            $_Lang['InfoBox_Msg'] = "<b class=\"lime\">{$_Lang['Info_ProxySet_OK_'.($Result_GetIP['isProxy'] == 0 ? 'Set' : 'Unset')]}</b>";
        }
        else
        {
            $_Lang['InfoBox_Msg'] = "<b class=\"orange\">{$_Lang['Info_ProxySet_NoIP']}</b>";
        }
    }
    else
    {
        $_Lang['InfoBox_Msg'] = "<b class=\"orange\">{$_Lang['Info_ProxySet_BadID']}</b>";
    }
}

if(!empty($_POST['search']))
{
    if(!empty($_POST['uid']))
    {
        $_GET['uid'] = $_POST['uid'];
    }
    if(!empty($_POST['ipid']))
    {
        $_GET['ipid'] = $_POST['ipid'];
    }

    if(empty($_GET['uid']) && empty($_GET['ipid']))
    {
        // Do Search and then Redirect to result page (if found)
        $Search = explode('|', $_POST['search']);
        $Search = array_map(function($val){return trim($val);}, $Search);

        if(isset($_POST['strict']) && $_POST['strict'] == '1')
        {
            $UseStrict = true;
        }
        else
        {
            $UseStrict = false;
        }

        if(in_array($_POST['type'], array('uname', 'uid', 'ipstr', 'ipid')))
        {
            // If searchType is supported:
            // Create search data for each searchType
            if($_POST['type'] == 'uname')
            {
                $Query[0]['table'] = 'users';
                $Query[0]['select'] = 'id';
                $Query[0]['select_field'] = 'id';
                $Query[0]['result'] = 'uid';
                $Query[0]['fields'][0] = 'username';
                $Query[0]['search'][0] = 'REGEXP';
                if(!$UseStrict)
                {
                    $CheckFunction = function($Value)
                    {
                        if(preg_match(REGEXP_USERNAME, $Value))
                        {
                            return true;
                        }
                        return false;
                    };
                }
                else
                {
                    $CheckFunction = function($Value)
                    {
                        if(preg_match(REGEXP_USERNAME_ABSOLUTE, $Value))
                        {
                            return true;
                        }
                        return false;
                    };
                }
            }
            else if($_POST['type'] == 'uid')
            {
                $Query[0]['table'] = 'users';
                $Query[0]['select'] = 'id';
                $Query[0]['select_field'] = 'id';
                $Query[0]['result'] = 'uid';
                $Query[0]['fields'][0] = 'id';
                $Query[0]['search'][0] = 'IN';
                $CheckFunction = function(&$Value)
                {
                    $Value = intval($Value);
                    if($Value > 0)
                    {
                        return true;
                    }
                    return false;
                };
            }
            else if($_POST['type'] == 'ipstr')
            {
                $Query[0]['table'] = 'used_ip_and_ua';
                $Query[0]['select'] = 'ID';
                $Query[0]['select_field'] = 'ID';
                $Query[0]['result'] = 'ipid';
                if($UseStrict)
                {
                    $Query[0]['fields'][0] = 'ValueHash';
                    $Query[0]['search'][0] = 'IN';
                    $Query[0]['valadd'][0] = '\'{$VAL}\'';
                }
                else
                {
                    $Query[0]['fields'][0] = 'Value';
                    $Query[0]['search'][0] = 'REGEXP';
                }
                $CheckFunction = function($Value)
                {
                    if(preg_match(REGEXP_IP, $Value))
                    {
                        return true;
                    }
                    return false;
                };
            }
            else if($_POST['type'] == 'ipid')
            {
                $Query[0]['table'] = 'user_enterlog';
                $Query[0]['select'] = 'DISTINCT IP_ID';
                $Query[0]['select_field'] = 'IP_ID';
                $Query[0]['result'] = 'ipid';
                $Query[0]['fields'][0] = 'IP_ID';
                $Query[0]['search'][0] = 'IN';
                $CheckFunction = function(&$Value)
                {
                    $Value = round($Value);
                    if($Value > 0)
                    {
                        return true;
                    }
                    return false;
                };
            }

            // Pass all values through CheckFunction
            foreach($Search as $Value)
            {
                if($CheckFunction($Value))
                {
                    $QryData[] = $Value;
                }
            }

            if(!empty($QryData))
            {
                // Do Search
                foreach($Query as $QueryID => $QueryData)
                {
                    $LastQueryID = $QueryID;

                    $ComposeQuery = "SELECT {$QueryData['select']} FROM {{table}} WHERE {Fields};";
                    $Fields = array();
                    foreach($QueryData['fields'] as $FieldID => $Field)
                    {
                        if(empty($QueryData['values'][$FieldID]))
                        {
                            $Temp = $QryData;
                            if(!empty($QueryData['valadd'][$FieldID]))
                            {
                                $Temp = array_map(function($val){global $QueryData; return str_replace('{$VAL}', $val, $QueryData['valadd'][$FieldID]);}, $Temp);
                            }
                            if($QueryData['search'][$FieldID] == 'REGEXP')
                            {
                                $Values = '\''.implode('|', $Temp).'\'';
                            }
                            else if($QueryData['search'][$FieldID] == 'IN')
                            {
                                $Values = '('.implode(', ', $Temp).')';
                            }
                        }
                        else
                        {
                            $Values = $QueryData['values'][$FieldID];
                        }
                        $Fields[] = "`{$Field}` {$QueryData['search'][$FieldID]} {$Values}";
                    }
                    if(!empty($QueryData['joins']))
                    {
                        foreach($QueryData['joins'] as $JoinID => $JoinType)
                        {
                            $Fields[$JoinID] .= " {$JoinType} ";
                        }
                    }
                    $ComposeQuery = str_replace('{Fields}', implode('', $Fields), $ComposeQuery);

                    $SQLResult_ComposedQuery = doquery($ComposeQuery, $QueryData['table']);
                    if($SQLResult_ComposedQuery->num_rows > 0)
                    {
                        while($Data = $SQLResult_ComposedQuery->fetch_assoc())
                        {
                            $Query[$QueryID]['results'][] = $Data;
                        }
                    }
                    else
                    {
                        break;
                    }
                }

                if(!empty($Query[$LastQueryID]['results']))
                {
                    $Data = array_map(function($val){global $Query, $LastQueryID; return $val[$Query[$LastQueryID]['select_field']];}, $Query[$LastQueryID]['results']);
                    if(!empty($FoundData[$Query[$LastQueryID]['result']]))
                    {
                        $FoundData[$Query[$LastQueryID]['result']] = array_merge($FoundData[$Query[$LastQueryID]['result']], $Data);
                    }
                    else
                    {
                        $FoundData[$Query[$LastQueryID]['result']] = $Data;
                    }
                }
                else
                {
                    $Error['txt'] = $_Lang['Error_Search_NoRowsFound'];
                }
            }
        }
        else
        {
            // Bad Type - throw Error
            $Error['txt'] = $_Lang['Error_Search_BadType'];
        }

        if(!empty($FoundData['uid']))
        {
            $_GET['uid'] = implode('|', $FoundData['uid']);
        }
        else if(!empty($FoundData['ipid']))
        {
            $_GET['ipid'] = implode('|', $FoundData['ipid']);
        }
    }

    $ThisUrl['search'] = $_POST['search'];
    $ThisUrl['type'] = $_POST['type'];
}

if(!empty($_GET['search']))
{
    $ThisUrl['search'] = $_GET['search'];
}
if(!empty($_GET['type']))
{
    $ThisUrl['type'] = $_GET['type'];
}
if(!empty($_POST['sort']))
{
    if(in_array($_POST['sort'], array('uid', 'ipid', 'logcount', 'logdate')))
    {
        $SortType = $_POST['sort'];
        if($_POST['mode'] == 'asc' OR $_POST['mode'] == 'desc')
        {
            $SortMode = $_POST['mode'];
        }
        else
        {
            $SortMode = 'asc';
        }
    }
    if(!empty($SortType))
    {
        if($SortType == 'uid')
        {
            $Sortings['user']['uid'] = " ORDER BY `id` {$SortMode}";
            $Sortings['ip']['uid'] = " ORDER BY `User_ID` {$SortMode}";
            $_Lang['SortUIDMode'] = '&amp;mode='.($SortMode == 'asc' ? 'desc' : 'asc');
            $_Lang['SortUIDModeClass'] = $SortMode;
        }
        else if($SortType == 'ipid')
        {
            $Sortings['user']['ipid'] = true;
            $Sortings['ip']['ipid'] = " ORDER BY `IP_ID` {$SortMode}";
            $_Lang['SortIPIDMode'] = '&amp;mode='.($SortMode == 'asc' ? 'desc' : 'asc');
            $_Lang['SortIPIDModeClass'] = $SortMode;
        }
        else if($SortType == 'logcount')
        {
            $Sortings['user']['logcount'] = true;
            $Sortings['ip']['logcount'] = true;
            $_Lang['SortLogCountMode'] = '&amp;mode='.($SortMode == 'asc' ? 'desc' : 'asc');
            $_Lang['SortLogCountModeClass'] = $SortMode;
        }
        else if($SortType == 'logdate')
        {
            $Sortings['user']['logdate'] = true;
            $Sortings['ip']['logdate'] = true;
            $_Lang['SortLogDateMode'] = '&amp;mode='.($SortMode == 'asc' ? 'desc' : 'asc');
            $_Lang['SortLogDateModeClass'] = $SortMode;
        }
        $_Lang['Insert_SortType'] = $SortType;
        $_Lang['Insert_SortMode'] = $SortMode;
    }
}

if(!empty($Error))
{
    $_Lang['InfoBox_Msg'] = "<b class=\"red\">{$Error['txt']}</b>";
}

// If data given, create Where statement
if(!empty($_GET['uid']))
{
    $_Lang['Insert_Found_UID'] = $_GET['uid'];
    $_GET['uid'] = explode('|', $_GET['uid']);
    foreach($_GET['uid'] as $Value)
    {
        $Value = round($Value);
        if($Value > 0)
        {
            $GetData['value'][] = $Value;
        }
    }
    if(!empty($GetData['value']))
    {
        $GetData['type'] = 'user';
        $GetData['query'] = "SELECT `id`, `username`, `ip_at_reg`, `user_lastip` FROM {{table}} WHERE `id` IN (".implode(', ', $GetData['value']).")".(isset($Sortings['user']['uid']) ? $Sortings['user']['uid'] : null).";";
        $GetData['table'] = 'users';
        $GetData['tpl'] = 'user';
    }
}
if(!empty($_GET['ipid']))
{
    $_Lang['Insert_Found_IPID'] = $_GET['ipid'];
    $_GET['ipid'] = explode('|', $_GET['ipid']);
    foreach($_GET['ipid'] as $Value)
    {
        $Value = round($Value);
        if($Value > 0)
        {
            $GetData['value'][] = $Value;
        }
    }
    if(!empty($GetData['value']))
    {
        $GetData['type'] = 'ip';
        $GetData['query'] = "SELECT DISTINCT `logs`.`User_ID`, `logs`.`IP_ID`, `logs`.`Count`, `logs`.`LastTime`, `values`.`Value`, `values`.`isProxy`, `users`.`username` FROM {{table}} AS `logs` LEFT JOIN `{{prefix}}used_ip_and_ua` AS `values` ON `values`.`ID` = `logs`.`IP_ID` LEFT JOIN `{{prefix}}users` AS `users` ON `users`.`id` = `logs`.`User_ID` WHERE `IP_ID` IN (".implode(', ', $GetData['value']).")".(isset($Sortings['ip']['uid']) ? $Sortings['ip']['uid'] : null).(isset($Sortings['ip']['ipid']) ? $Sortings['ip']['ipid'] : null).";";
        $GetData['table'] = 'user_enterlog';
        $GetData['tpl'] = 'ip';
    }
}

$_Lang['Insert_Search_Value'] = (isset($ThisUrl['search']) ? $ThisUrl['search'] : '');
if(isset($ThisUrl['type']))
{
    $_Lang['Insert_Search_OptSel_'.$ThisUrl['type']] = 'selected';
}

if(!empty($GetData))
{
    if(empty($_Lang['Insert_Search_Value']))
    {
        $_Lang['Insert_Search_Value'] = implode('|', $GetData['value']);
        $_Lang['Insert_Search_OptSel_'.($GetData['type'] == 'user' ? 'uid' : 'ipid')] = 'selected';
    }

    $SQLResult_LoadData = doquery($GetData['query'], $GetData['table']);

    if($SQLResult_LoadData->num_rows > 0)
    {
        // Parse main data and then grab secondary data
        $HeaderTPL = gettemplate('admin/iplog_result_header_'.$GetData['tpl']);
        $BreakTPL = gettemplate('admin/iplog_result_header_breakline');
        $RowTPL = gettemplate('admin/iplog_result_mainrow_'.$GetData['tpl']);
        $SubRowTPL = gettemplate('admin/iplog_result_subrow_'.$GetData['tpl']);

        while($Data = $SQLResult_LoadData->fetch_assoc())
        {
            if($GetData['type'] == 'user')
            {
                $Rows[] = $Data;
            }
            else if($GetData['type'] == 'ip')
            {
                if(empty($Data['username']))
                {
                    $Data['username'] = '<b class="red">'.$_Lang['SubRow_IP_NoData'].'</b>';
                }
                if(empty($Rows[$Data['User_ID']]))
                {
                    $Rows[$Data['User_ID']] = $Data;
                }
                if(empty($Rows[$Data['User_ID']][$Data['IP_ID']]))
                {
                    $Rows[$Data['User_ID']]['IP'][$Data['IP_ID']]['IP_ID'] = $Data['IP_ID'];
                    $Rows[$Data['User_ID']]['IP'][$Data['IP_ID']]['Value'] = $Data['Value'];
                    $Rows[$Data['User_ID']]['IP'][$Data['IP_ID']]['isProxy'] = ($Data['isProxy'] ? 'isProxy' : 'noProxy');
                    $Rows[$Data['User_ID']]['IP'][$Data['IP_ID']]['isProxyColor'] = ($Data['isProxy'] ? 'orange' : '');
                }
                if(!isset($Rows[$Data['User_ID']]['IP'][$Data['IP_ID']]['Count']))
                {
                    $Rows[$Data['User_ID']]['IP'][$Data['IP_ID']]['Count'] = 0;
                }
                $Rows[$Data['User_ID']]['IP'][$Data['IP_ID']]['Count'] += $Data['Count'];
                if(!isset($Rows[$Data['User_ID']]['IP'][$Data['IP_ID']]['LastDateRaw']) || $Data['LastTime'] > $Rows[$Data['User_ID']]['IP'][$Data['IP_ID']]['LastDateRaw'])
                {
                    $Rows[$Data['User_ID']]['IP'][$Data['IP_ID']]['LastDateRaw'] = $Data['LastTime'];
                    $Rows[$Data['User_ID']]['IP'][$Data['IP_ID']]['LastDate'] = prettyDate('d m Y, H:i:s', $Data['LastTime'] + SERVER_MAINOPEN_TSTAMP, 1);
                }
            }
        }

        if($GetData['type'] == 'user')
        {
            foreach($Rows as $RowData)
            {
                $LoadWhere[] = $RowData['id'];
            }
            $LoadWhere = implode(', ', $LoadWhere);

            $SQLResult_GetUserEnterlog = doquery(
                "SELECT `logs`.`IP_ID`, `logs`.`User_ID`, `logs`.`Count`, `logs`.`LastTime`, `values`.`Value`, `values`.`isProxy` FROM {{table}} AS `logs` LEFT JOIN `{{prefix}}used_ip_and_ua` AS `values` ON `logs`.`IP_ID` = `values`.`ID` WHERE `User_ID` IN ({$LoadWhere});",
                'user_enterlog'
            );

            $MainKey = 'id';

            if($SQLResult_GetUserEnterlog->num_rows > 0)
            {
                while($Data = $SQLResult_GetUserEnterlog->fetch_assoc())
                {
                    if(empty($SubRows[$Data['User_ID']][$Data['IP_ID']]))
                    {
                        $SubRows[$Data['User_ID']][$Data['IP_ID']] = $Data;
                        $SubRows[$Data['User_ID']][$Data['IP_ID']]['isProxy'] = ($Data['isProxy'] ? 'isProxy' : 'noProxy');
                        $SubRows[$Data['User_ID']][$Data['IP_ID']]['isProxyColor'] = ($Data['isProxy'] ? 'orange' : '');
                        if(!isset($IPTable[$Data['IP_ID']]))
                        {
                            $IPTable[$Data['IP_ID']] = 0;
                        }
                        $IPTable[$Data['IP_ID']] += 1;
                        $IPReverse[$Data['IP_ID']] = $Data['Value'];
                    }
                    else
                    {
                        $SubRows[$Data['User_ID']][$Data['IP_ID']]['Count'] += $Data['Count'];
                    }
                    if(!isset($SubRows[$Data['User_ID']][$Data['IP_ID']]['LastDateRaw']) || $Data['LastTime'] > $SubRows[$Data['User_ID']][$Data['IP_ID']]['LastDateRaw'])
                    {
                        $SubRows[$Data['User_ID']][$Data['IP_ID']]['LastDateRaw'] = $Data['LastTime'];
                        $SubRows[$Data['User_ID']][$Data['IP_ID']]['LastDate'] = prettyDate('d m Y, H:i:s', $Data['LastTime'] + SERVER_MAINOPEN_TSTAMP, 1);
                    }
                }
                if(isset($Sortings['user']['logcount']) && $Sortings['user']['logcount'] === true)
                {
                    if($SortMode == 'desc')
                    {
                        $SortMode = SORT_DESC;
                    }
                    else
                    {
                        $SortMode = SORT_ASC;
                    }
                    foreach($SubRows as $Key => $Array)
                    {
                        $SortArray = array();
                        foreach($Array as $ID => $Array2)
                        {
                            $SortArray[$ID] = $Array2['Count'];
                        }
                        array_multisort($SortArray, $SortMode, $SubRows[$Key]);
                    }
                }
                else if(isset($Sortings['user']['logdate']) && $Sortings['user']['logdate'] === true)
                {
                    if($SortMode == 'desc')
                    {
                        $SortMode = SORT_DESC;
                    }
                    else
                    {
                        $SortMode = SORT_ASC;
                    }
                    foreach($SubRows as $Key => $Array)
                    {
                        $SortArray = array();
                        foreach($Array as $ID => $Array2)
                        {
                            $SortArray[$ID] = $Array2['LastDateRaw'];
                        }
                        array_multisort($SortArray, $SortMode, $SubRows[$Key]);
                    }
                }
                else if(isset($Sortings['user']['ipid']) && $Sortings['user']['ipid'] === true)
                {
                    foreach($SubRows as $Key => $Data)
                    {
                        if($SortMode == 'desc')
                        {
                            krsort($SubRows[$Key]);
                        }
                        else
                        {
                            ksort($SubRows[$Key]);
                        }
                    }
                }
            }
        }
        else if($GetData['type'] == 'ip')
        {
            foreach($Rows as $UserID => $UserData)
            {
                $SubRows[$UserID] = $UserData['IP'];
                foreach($UserData['IP'] as $ThisData)
                {
                    if(!isset($IPTable[$ThisData['IP_ID']]))
                    {
                        $IPTable[$ThisData['IP_ID']] = 0;
                    }
                    $IPTable[$ThisData['IP_ID']] += 1;
                    $IPReverse[$ThisData['IP_ID']] = $ThisData['Value'];
                }
            }
            $MainKey = 'User_ID';
        }

        $RowNoTable = array();
        foreach($Rows as $RowData)
        {
            if(!empty($SubRows[$RowData[$MainKey]]))
            {
                foreach($SubRows[$RowData[$MainKey]] as $SubRowData)
                {
                    if($IPTable[$SubRowData['IP_ID']] > 1)
                    {
                        $ThisKey = $SubRowData['IP_ID'];
                    }
                    else
                    {
                        $ThisKey = 0;
                    }
                    if(empty($RowData['sub'][$ThisKey]['SubRowFirst']))
                    {
                        $RowData['sub'][$ThisKey]['SubRowFirst'] = parsetemplate($SubRowTPL, $SubRowData);
                        $RowData['sub'][$ThisKey]['SubRowsCount'] = 1;
                    }
                    else
                    {
                        if (empty($RowData['sub'][$ThisKey]['SubRows'])) {
                            $RowData['sub'][$ThisKey]['SubRows'] = '';
                        }

                        $RowData['sub'][$ThisKey]['SubRows'] .= '<tr>'.parsetemplate($SubRowTPL, $SubRowData).'</tr>';
                        $RowData['sub'][$ThisKey]['SubRowsCount'] += 1;
                    }
                }
            }
            else
            {
                if($GetData['type'] == 'user')
                {
                    $NoData_Text = $_Lang['SubRow_User_NoData'];
                }
                else
                {
                    $NoData_Text = $_Lang['SubRow_IP_NoData'];
                }
                $RowData['sub'][0]['SubRowFirst'] = '<th class="pad2 red" colspan="'.substr_count($SubRowTPL, '</th>').'">'.$NoData_Text.'</th>';
                $RowData['sub'][0]['SubRowsCount'] = 1;
            }

            $RowDataReduced = $RowData;
            unset($RowDataReduced['sub']);
            foreach($RowData['sub'] as $TableKey => $ThisData)
            {
                $ThisData = array_merge($RowDataReduced, $ThisData);

                if(!isset($RowNoTable[$TableKey]))
                {
                    $RowNoTable[$TableKey] = 0;
                }
                $ThisData['RowNo'] = $RowNoTable[$TableKey] + 1;
                $RowNoTable[$TableKey] += 1;
                if($ThisData['RowNo'] % 2 == 0)
                {
                    $ThisData['EvenOrOdd'] = 'even';
                }
                else
                {
                    $ThisData['EvenOrOdd'] = 'odd';
                }

                $ParsedRows[$TableKey][] = parsetemplate($RowTPL, $ThisData);
            }
        }

        foreach($ParsedRows as $TableKey => $ParsedRow) {
            $ParsedRows[$TableKey] = implode("", $ParsedRows[$TableKey]);
        }

        krsort($ParsedRows);
        $ParsedRowsCount = count($ParsedRows);
        foreach($ParsedRows as $ThisKey => $CombinedRows)
        {
            if($ThisKey > 0)
            {
                $BreakArray = array('BreakText' => sprintf($_Lang['BreakLine_Intersection'], $ThisKey, $IPReverse[$ThisKey], $ThisKey, $ThisKey));
                $_Lang['Results'][] = parsetemplate($BreakTPL, $BreakArray);
            }
            else
            {
                if($ParsedRowsCount > 1)
                {
                    $BreakArray = array('BreakText' => $_Lang['BreakLine_NormalLogs']);
                    $_Lang['Results'][] = parsetemplate($BreakTPL, $BreakArray);
                }
            }
            $_Lang['Results'][] = $CombinedRows;
        }

        $_Lang['Results'] = implode('', $_Lang['Results']);
        $_Lang['Headers'] = parsetemplate($HeaderTPL, $_Lang);
    }
    else
    {
        $_Lang['InfoBox_Msg'] = "<b class=\"red\">{$_Lang['Error_Where_NoData']}</b>";
    }
}
else
{
    if(empty($Error))
    {
        $_Lang['InfoBox_Msg'] = "<b class=\"orange\">{$_Lang['Info_InsertData']}</b>";
    }
}

if(empty($_Lang['InfoBox_Msg']))
{
    $_Lang['Insert_InfoBox_Hide'] = 'hide';
}

display(parsetemplate($PageTPL, $_Lang), $PageTitle, false, true);

?>
