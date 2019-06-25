<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(!CheckAuth('sgo'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

includeLang('admin/banslist');
$_Lang['Insert_Scripts'] = '';
$Now = time();
$_PerPage = 20;
$_Colspan = 9;
$_JSChronoAppletIncluded = false;

if(!empty($_GET['ids']))
{
    $InsertIDs = explode(',', $_GET['ids']);
    $MappedIDs = [];
    foreach($InsertIDs as $ThisID)
    {
        $MappedIDs[] = "[{$ThisID}]";
    }
    $_POST['users'] = implode(',', $MappedIDs);
    $_POST['send'] = 'yes';
}

if(isset($_POST['send']) && $_POST['send'] == 'yes')
{
    if(!empty($_POST['users']))
    {
        $Users = explode(',', $_POST['users']);
        foreach($Users as $UserData)
        {
            $UserData = trim($UserData);
            if(strstr($UserData, '[') !== FALSE)
            {
                if(preg_match('/^\[[0-9]{1,20}\]$/D', $UserData))
                {
                    $Filters['UserID'][] = trim($UserData, '[]');
                    $_Lang['Insert_Input_Users'][] = $UserData;
                }
            }
            else
            {
                if(preg_match(REGEXP_USERNAME_ABSOLUTE, $UserData))
                {
                    $GetUsers['name'][] = "'{$UserData}'";
                    $_Lang['Insert_Input_Users'][] = $UserData;
                }
            }
        }
        if(!empty($GetUsers))
        {
            if(!empty($GetUsers['name']))
            {
                $Where[] = "`username` IN (".implode(', ', $GetUsers['name']).")";
            }

            $SQLResult_CheckUsers = doquery(
                "SELECT `id` FROM {{table}} WHERE ".implode(' OR ', $Where).";",
                'users'
            );

            if($SQLResult_CheckUsers->num_rows > 0)
            {
                while($Data = $SQLResult_CheckUsers->fetch_assoc())
                {
                    $Filters['UserID'][] = $Data['id'];
                }
            }
        }
        if(!empty($Filters['UserID']))
        {
            $Filters['UserID'] = implode(',', $Filters['UserID']);
            $Filters['UserID'] = "`UserID` IN ({$Filters['UserID']})";
        }
        if(!empty($_Lang['Insert_Input_Users']))
        {
            $_Lang['Insert_Input_Users'] = implode(',', $_Lang['Insert_Input_Users']);
        }
    }

    if(!empty($_POST['date_from']))
    {
        $TempDate = strtotime($_POST['date_from']);
        if($TempDate >= 0)
        {
            $FilterDateFrom = $TempDate;
            $Filters[] = "`StartTime` >= {$TempDate}";
            $_Lang['Insert_Input_DateFrom'] = $_POST['date_from'];
        }
    }

    if(!empty($_POST['date_to']))
    {
        $TempDate = strtotime($_POST['date_to']);
        if($TempDate > 0 AND $TempDate > $FilterDateFrom)
        {
            $Filters[] = "((`Removed` = 1 AND `RemoveDate` <= {$TempDate}) OR (`Removed` = 0 AND `EndTime` <= {$TempDate}))";
            $_Lang['Insert_Input_DateTo'] = $_POST['date_to'];
        }
    }
}

$_Page = isset($_POST['page']) ? intval($_POST['page']) : 0;
if($_Page < 1)
{
    $_Page = 1;
}
$Select_Start = ($_Page - 1) * $_PerPage;

if(!empty($Filters))
{
    $SelectIDs = "SELECT `ID` FROM {{table}} ";
    foreach($Filters as $FilterData)
    {
        $QueryArr[] = $FilterData;
    }
    $SelectIDs .= "WHERE ".implode(' AND ', $QueryArr);

    $SQLResult_SelectedIDs = doquery($SelectIDs, 'bans');

    if($SQLResult_SelectedIDs->num_rows > 0)
    {
        $FilterIDs = [];
        while($ThisRow = $SQLResult_SelectedIDs->fetch_assoc())
        {
            $FilterIDs[] = $ThisRow['ID'];
        }
        $TotalCount = count($FilterIDs);
        $SelectWhere = " WHERE `ID` IN (".implode(', ', $FilterIDs).") ";
        $RunQuery = true;
    }
    else
    {
        $TotalCount = 0;
    }
}
else
{
    $SelectTotalCount = doquery("SELECT COUNT(*) AS `Count` FROM {{table}};", 'bans', true);
    $TotalCount = $SelectTotalCount['Count'];
    if($TotalCount > 0)
    {
        $RunQuery = true;
    }
}
if(isset($RunQuery))
{
    if($Select_Start >= $TotalCount)
    {
        $_Page = 1;
        $Select_Start = 0;
    }

    $SelectQuery  = "SELECT * FROM {{table}} ";
    $SelectQuery .= isset($SelectWhere) ? $SelectWhere : '';
    $SelectQuery .= "ORDER BY `Active` DESC, `EndTime` DESC LIMIT {$Select_Start}, {$_PerPage};";
    $SQLResult_GetBans = doquery($SelectQuery, 'bans');
}
else
{
    $SQLResult_GetBans = false;
}

if($SQLResult_GetBans !== false)
{
    $GetUsernames = array();
    $TPL_Row = gettemplate('admin/banslist_row');

    while($Row = $SQLResult_GetBans->fetch_assoc())
    {
        if($Row['Active'] == 1)
        {
            if($Row['EndTime'] > $Now)
            {
                if($_JSChronoAppletIncluded === false)
                {
                    $_JSChronoAppletIncluded = true;
                    include($_EnginePath.'includes/functions/InsertJavaScriptChronoApplet.php');
                }
                $_Lang['Insert_Scripts'] .= InsertJavaScriptChronoApplet($Row['ID'], '', $Row['EndTime'], true);
                $Row['Status'] = "{$_Lang['Row_Active']}<br/><span id=\"bxx{$Row['ID']}\">".pretty_time($Row['EndTime'] - $Now, true, 'D')."</span>";
            }
            else
            {
                $Row['Status'] = $_Lang['Row_Expired'];
            }
        }
        else
        {
            if($Row['Expired'] == 1)
            {
                $Row['Status'] = $_Lang['Row_Expired'];
            }
            else
            {
                $Row['Status'] = "{$_Lang['Row_Removed']}<br/>".prettyDate('d m Y H:i:s', $Row['RemoveDate'], 1);
            }
        }

        if($Row['With_Vacation'] == 1)
        {
            $Row['Indicators'][] = $_Lang['Row_WithVacation'];
        }
        else
        {
            $Row['Indicators'][] = $_Lang['Row_WithoutVacation'];
        }
        if($Row['Fleets_Retreated_Own'] == 1 AND $Row['Fleets_Retreated_Others'])
        {
            $Row['Indicators'][] = $_Lang['Row_AllFleetsRetreaded'];
        }
        elseif($Row['Fleets_Retreated_Own'] == 1)
        {
            $Row['Indicators'][] = $_Lang['Row_OwnFleetsRetreaded'];
        }
        elseif($Row['Fleets_Retreated_Others'] == 1)
        {
            $Row['Indicators'][] = $_Lang['Row_OthersFleetsRetreaded'];
        }
        else
        {
            $Row['Indicators'][] = $_Lang['Row_NoFleetsRetreaded'];
        }
        if($Row['BlockadeOn_CookieStyle'] == 1)
        {
            $Row['Indicators'][] = $_Lang['Row_BlockadeOnCookieStyle'];
        }
        if(!empty($Row['Indicators']))
        {
            $Row['Indicators'] = implode('<br/>', $Row['Indicators']);
        }
        else
        {
            $Row['Indicators'] = '';
        }

        $ParsedRow = array
        (
            'ID' => $Row['ID'],
            'UserID' => $Row['UserID'],
            'StartTimeParsed' => prettyDate('d m Y H:i:s', $Row['StartTime'], 1),
            'Duration' => str_replace('00:00:00', '', pretty_time($Row['EndTime'] - $Row['StartTime'], true, 'D')),
            'EndTimeParsed' => prettyDate('d m Y H:i:s', $Row['EndTime'], 1),
            'Status' => $Row['Status'],
            'Reason' => (!empty($Row['Reason']) ? $Row['Reason'] : $_Lang['Row_ReasonEmpty']),
            'GiverID' => $Row['GiverID'],
            'Indicators' => $Row['Indicators']
        );

        if(!in_array($Row['UserID'], $GetUsernames))
        {
            $GetUsernames[] = $Row['UserID'];
        }
        if(!in_array($Row['GiverID'], $GetUsernames))
        {
            $GetUsernames[] = $Row['GiverID'];
        }

        $InsertRows[] = $ParsedRow;
    }

    if(!empty($GetUsernames))
    {
        $SQLResult_Usernames = doquery(
            "SELECT `id`, `username` FROM {{table}} WHERE `id` IN (".implode(', ', $GetUsernames).");",
            'users'
        );

        if($SQLResult_Usernames != false)
        {
            while($UserData = $SQLResult_Usernames->fetch_assoc())
            {
                $UsernamesArray[$UserData['id']] = $UserData['username'];
            }
        }
    }

    foreach($InsertRows as $RowData)
    {
        $RowBannedUserID = $RowData['UserID'];
        $RowBannedUsername = (
            isset($UsernamesArray[$RowBannedUserID]) ?
            $UsernamesArray[$RowBannedUserID] :
            "<b class=\"red\">{$_Lang['Row_UserDeleted']}</b>"
        );
        $RowGiverUserID = $RowData['GiverID'];
        $RowGiverUsername = (
            isset($UsernamesArray[$RowGiverUserID]) ?
            $UsernamesArray[$RowGiverUserID] :
            "<b class=\"red\">{$_Lang['Row_UserDeleted']}</b>"
        );

        $RowData['UserParsed'] = (
            "<a href=\"?ids={$RowBannedUserID}\">{$RowBannedUsername}</a>" .
            " " .
            "(<a href=\"user_info.php?uid={$RowBannedUserID}\" target=\"_blank\">#{$RowBannedUserID}</a>)"
        );

        $RowData['GiverParsed'] = (
            "<a href=\"?ids={$RowGiverUserID}\">{$RowGiverUsername}</a>" .
            " " .
            "(<a href=\"user_info.php?uid={$RowGiverUserID}\" target=\"_blank\">#{$RowGiverUserID}</a>)"
        );

        $_Lang['Insert_Rows'][] = parsetemplate($TPL_Row, $RowData);
    }
    $_Lang['Insert_Rows'] = implode('', $_Lang['Insert_Rows']);

    if($TotalCount > $_PerPage)
    {
        include($_EnginePath.'includes/functions/Pagination.php');
        $Pagin = CreatePaginationArray($TotalCount, $_PerPage, $_Page, 7);
        $PaginationTPL = "<a class=\"pagin {\$Classes}\" id=\"page_{\$Value}\">{\$ShowValue}</a>";
        $PaginationViewOpt = array('CurrentPage_Classes' => 'orange', 'Breaker_View' => '...');
        $CreatePagination = implode(' ', ParsePaginationArray($Pagin, $_Page, $PaginationTPL, $PaginationViewOpt));
        $_Lang['Insert_Pagination'] = '<tr><th colspan="'.$_Colspan.'" style="padding: 7px;">'.$CreatePagination.'</th></tr>';
    }
}
else
{
    $_Lang['Insert_Rows'] = parsetemplate(gettemplate('_singleRow'), array('Colspan' => $_Colspan, 'Classes' => 'pad5 orange', 'Text' => $_Lang['Error_NoRows']));
}

$_Lang['JS_DatePicker_TranslationLang'] = getJSDatePickerTranslationLang();

$_Lang['Colspan'] = $_Colspan;
display(parsetemplate(gettemplate('admin/banslist_body'), $_Lang), $_Lang['Page_Title'], false, true);

?>
