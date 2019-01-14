<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

includeLang('admin');
includeLang('admin/userlist');

$Now = time();
$AllowedSortTypes = array('id', 'username', 'email', 'ip_at_reg', 'user_lastip', 'register_time', 'onlinetime');
$_Hide = 'hide';
$_Selected = 'selected';
$_Checked = 'checked';

if(!CheckAuth('go'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

$parse = $_Lang;

$MassActionsPHP = $_Lang['MassAction'];
GlobalTemplate_AppendToBottomMenuInjection(parsetemplate(gettemplate('admin/userlist_massaction_body'), $MassActionsPHP));

if(!empty($_POST['deleteID']))
{
    $DeleteID = round($_POST['deleteID']);
    if($DeleteID > 0)
    {
        if(CheckAuth('supportadmin'))
        {
            if($DeleteID != $_User['id'])
            {
                doquery("UPDATE {{table}} SET `is_ondeletion` = 1, `deletion_endtime` = UNIX_TIMESTAMP() WHERE `id` = {$DeleteID};", 'users');
            }
        }
        else
        {
            message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
        }
    }
}

if(isset($_POST['cmd']) && $_POST['cmd'] == 'sort' AND !empty($_POST['type']) AND (!isset($_POST['nosort']) || $_POST['nosort'] != 'on'))
{
    if(in_array($_POST['type'], $AllowedSortTypes))
    {
        $TypeSort = $_POST['type'];
        $parse['FormSortType'] = $TypeSort;
    }
    else
    {
        $TypeSort = 'id';
    }
}
else
{
    $TypeSort = 'id';
    $parse['DontShowNoSort'] = 'inv';
}

if(empty($_POST['mode']) || (isset($_POST['nosort']) && $_POST['nosort'] == 'on'))
{
    $parse['sort_mode'] = 'asc';
    $SortMode = 'ASC';
}
else
{
    if($_POST['mode'] == 'asc')
    {
        $SortMode = 'ASC';
        $parse['FormSortMode'] = 'asc';
        $parse['sort_mode'] = 'desc';
    }
    else
    {
        $SortMode = 'DESC';
        $parse['FormSortMode'] = 'desc';
        $parse['sort_mode'] = 'asc';
    }
}

if(!empty($_POST['pp']))
{
    $PerPage = intval($_POST['pp']);
    if($PerPage <= 0)
    {
        $PerPage = 20;
    }
    $parse['per_page'] = '&amp;pp='.$PerPage;
    $parse['InitPerPage'] = $PerPage;
    setcookie('ACP_UserList_PerPage', $PerPage, 0, 'admin/');
}
else
{
    if(!empty($_COOKIE['ACP_UserList_PerPage']))
    {
        $PerPage = intval($_COOKIE['ACP_UserList_PerPage']);
        if($PerPage <= 0)
        {
            $PerPage = 20;
        }
    }
    else
    {
        $PerPage = 20;
    }
}
$parse['DefaultPerPage'] = 20;
$parse['perPageSelect_'.$PerPage] = 'selected';

// ----------------------------
// Get Overrides --------------
if(!empty($_GET['uid']))
{
    $_GET['uid'] = intval($_GET['uid']);
    if($_GET['uid'] > 0)
    {
        $_POST['search_user'] = $_GET['uid'];
        $_POST['search_by'] = 'uid';
    }
}
if(!empty($_GET['ipid']))
{
    if(preg_match(REGEXP_IP, $_GET['ipid']))
    {
        $_POST['search_user'] = $_GET['ipid'];
        $_POST['search_by'] = 'ip';
        $_POST['anyip'] = 'on';
    }
}
if(!empty($_GET['search_user']) AND !empty($_GET['search_by']))
{
    $_POST['search_user'] = $_GET['search_user'];
    $_POST['search_by'] = $_GET['search_by'];
}

// ----------------------------

$PerPageArray = array(5, 10, 15, 20, 25, 50);
if(!in_array($PerPage, $PerPageArray))
{
    $parse['perPageSelect_0'] = 'selected';
}

$CurrentPage = (isset($_POST['page']) ? intval($_POST['page']) : 0);
if($CurrentPage < 1)
{
    $CurrentPage = 1;
}
$parse['FormPage'] = $CurrentPage;
$GetStart = (string) ((($CurrentPage - 1) * $PerPage) + 0);

$PageTPL = gettemplate('admin/userlist_body');
$RowsTPL = gettemplate('admin/userlist_rows');

$parse['AllySearchTypeDisplay'] = $_Hide;
$parse['AllyInRequestDisplay'] = $_Hide;
$parse['AnyIPDisplay'] = $_Hide;
$parse['AnyIPChecked'] = $_Checked;
$parse['AllySearch_name_Checked'] = $_Checked;
$parse['AllySearch_tag_Checked'] = $_Checked;

include($_EnginePath.'includes/functions/Filters.php');

if(isset($_POST['preserve']) && strstr($_POST['preserve'], 'over=yes') !== false)
{
    $_GET['over'] = 'yes';
}

if(isset($_GET['over']) && $_GET['over'] == 'yes')
{
    $_POST['online_yes'] = 'on';
    $parse['FormPreserve'][] = 'over=yes';
}

if(!empty($parse['FormPreserve']))
{
    $parse['FormPreserve'] = implode('|', $parse['FormPreserve']);
}

$Search['Flags']['strict']        = (isset($_POST['strict'])                && $_POST['strict'] == 'on'                ? true    : false);
$Search['Flags']['utableOnly']    = (isset($_POST['anyip'])                && $_POST['anyip'] == 'on'                ? false : true);
$Search['Flags']['isOnline']    = (isset($_POST['online_yes'])            && $_POST['online_yes'] == 'on'            ? true    : (isset($_POST['online_no'])            && $_POST['online_no'] == 'on'            ? false : null));
$Search['Flags']['onVacation']    = (isset($_POST['onvacation_yes'])        && $_POST['onvacation_yes'] == 'on'        ? true    : (isset($_POST['onvacation_no'])        && $_POST['onvacation_no'] == 'on'        ? false : null));
$Search['Flags']['isBanned']    = (isset($_POST['isbanned_yes'])        && $_POST['isbanned_yes'] == 'on'        ? true    : (isset($_POST['isbanned_no'])            && $_POST['isbanned_no'] == 'on'        ? false : null));
$Search['Flags']['isAI']        = (isset($_POST['isai_yes'])            && $_POST['isai_yes'] == 'on'            ? true    : (isset($_POST['isai_no'])                && $_POST['isai_no'] == 'on'            ? false : null));
$Search['Flags']['inDeletion']    = (isset($_POST['isdeleting_yes'])        && $_POST['isdeleting_yes'] == 'on'        ? true    : (isset($_POST['isdeleting_no'])        && $_POST['isdeleting_no'] == 'on'        ? false : null));
$Search['Flags']['inAlly']        = (isset($_POST['isinally_yes'])        && $_POST['isinally_yes'] == 'on'        ? true    : (isset($_POST['isinally_no'])            && $_POST['isinally_no'] == 'on'        ? false : null));
$Search['Flags']['isActive']    = (isset($_POST['isactive_yes'])        && $_POST['isactive_yes'] == 'on'        ? true    : (isset($_POST['isactive_no'])            && $_POST['isactive_no'] == 'on'        ? false : null));
$Search['Flags']['allyRequest'] = (isset($_POST['allyinrequest_yes'])    && $_POST['allyinrequest_yes'] == 'on'    ? true    : (isset($_POST['allyinrequest_no'])    && $_POST['allyinrequest_no'] == 'on'    ? false : null));
if(isset($_POST['search_by']) && $_POST['search_by'] == 'ally')
{
    $parse['searchBySelect_ally'] = $_Selected;
    if(isset($_POST['allysearch_name']) && isset($_POST['allysearch_tag']) && $_POST['allysearch_name'] == 'on' && $_POST['allysearch_tag'] == 'on')
    {
        $_POST['search_by'] = 'astring';
    }
    else if(isset($_POST['allysearch_name']) && $_POST['allysearch_name'] == 'on')
    {
        $_POST['search_by'] = 'aname';
        $parse['AllySearch_tag_Checked'] = '';
    }
    else if(isset($_POST['allysearch_tag']) && $_POST['allysearch_tag'] == 'on')
    {
        $_POST['search_by'] = 'atag';
        $parse['AllySearch_name_Checked'] = '';
    }
    else
    {
        $_POST['search_by'] = 'astring';
        $parse['AllySearch_name_Checked'] = '';
        $parse['AllySearch_tag_Checked'] = '';
    }
    $parse['AllySearchTypeDisplay'] = '';
    $parse['AllyInRequestDisplay'] = '';
}
if(isset($_POST['search_by']) && $_POST['search_by'] == 'aid')
{
    $parse['AllyInRequestDisplay'] = '';
}

// Check settings in Form
if(isset($_POST['strict']) && $_POST['strict'] == 'on')
{
    $parse['StrictChecked'] = $_Checked;
}
if(isset($_POST['online_yes']) && $_POST['online_yes'] == 'on')
{
    if(isset($_GET['over']) && $_GET['over'] == 'yes')
    {
        $parse['UsingOverviewShortcut'] = 'orange';
    }
    else
    {
        $parse['Online_Yes_Checked'] = $_Checked;
    }
}
if(isset($_POST['online_no']) && $_POST['online_no'] == 'on')
{
    $parse['Online_No_Checked'] = $_Checked;
}
if(isset($_POST['onvacation_yes']) && $_POST['onvacation_yes'] == 'on')
{
    $parse['OnVacation_Yes_Checked'] = $_Checked;
}
if(isset($_POST['onvacation_no']) && $_POST['onvacation_no'] == 'on')
{
    $parse['OnVacation_No_Checked'] = $_Checked;
}
if(isset($_POST['isbanned_yes']) && $_POST['isbanned_yes'] == 'on')
{
    $parse['IsBanned_Yes_Checked'] = $_Checked;
}
if(isset($_POST['isbanned_no']) && $_POST['isbanned_no'] == 'on')
{
    $parse['IsBanned_No_Checked'] = $_Checked;
}
if(isset($_POST['isai_yes']) && $_POST['isai_yes'] == 'on')
{
    $parse['IsAi_Yes_Checked'] = $_Checked;
}
if(isset($_POST['isai_no']) && $_POST['isai_no'] == 'on')
{
    $parse['IsAi_No_Checked'] = $_Checked;
}
if(isset($_POST['isdeleting_yes']) && $_POST['isdeleting_yes'] == 'on')
{
    $parse['IsDeleting_Yes_Checked'] = $_Checked;
}
if(isset($_POST['isdeleting_no']) && $_POST['isdeleting_no'] == 'on')
{
    $parse['IsDeleting_No_Checked'] = $_Checked;
}
if(isset($_POST['isinally_yes']) && $_POST['isinally_yes'] == 'on')
{
    $parse['IsInAlly_Yes_Checked'] = $_Checked;
}
if(isset($_POST['isinally_no']) && $_POST['isinally_no'] == 'on')
{
    $parse['IsInAlly_No_Checked'] = $_Checked;
}
if(isset($_POST['isactive_yes']) && $_POST['isactive_yes'] == 'on')
{
    $parse['IsActive_Yes_Checked'] = $_Checked;
}
if(isset($_POST['isactive_no']) && $_POST['isactive_no'] == 'on')
{
    $parse['IsActive_No_Checked'] = $_Checked;
}
if(isset($_POST['anyip']) && $_POST['anyip'] == 'on')
{
    $parse['AnyIPChecked'] = $_Checked;
}
if(isset($_POST['allysearch_name']) && $_POST['allysearch_name'] == 'on')
{
    $parse['AllySearch_name_Checked'] = $_Checked;
}
if(isset($_POST['allysearch_tag']) && $_POST['allysearch_tag'] == 'on')
{
    $parse['AllySearch_tag_Checked'] = $_Checked;
}
if(isset($_POST['allyinrequest_yes']) && $_POST['allyinrequest_yes'] == 'on')
{
    $parse['AllyInRequest_Yes_Checked'] = $_Checked;
}
if(isset($_POST['allyinrequest_no']) && $_POST['allyinrequest_no'] == 'on')
{
    $parse['AllyInRequest_No_Checked'] = $_Checked;
}

$parse['search_user_val'] = (isset($_POST['search_user']) ? $_POST['search_user'] : null);

$UserFields = array
(
    'id', 'username', 'ally_id', 'ally_request', 'authlevel', 'email', 'email_2', 'user_agent', 'screen_settings', 'ip_at_reg',
    'user_lastip', 'register_time', 'onlinetime', 'current_page', 'is_banned', 'ban_endtime', 'is_onvacation',
    'vacation_starttime', 'vacation_endtime', 'pro_time', 'multi_validated', 'activation_code', 'is_ondeletion', 'deletion_endtime',
);
array_walk($UserFields, function(&$Value){ $Value = "`{$Value}`"; });
$UserFields = implode(', ', $UserFields);

if(empty($_POST['search_user']))
{
    $Search['mode'] = 1;
    $Search['string'] = '';
    $Search['type'] = '';
}
else
{
    $Search['mode'] = 2;
    $Search['string'] = $_POST['search_user'];
    if($_POST['search_by'] == 'uid')
    {
        $Search['type'] = 'uid';
    }
    else if($_POST['search_by'] == 'astring' || $_POST['search_by'] == 'aname' || $_POST['search_by'] == 'atag')
    {
        $Search['type'] = $_POST['search_by'];
    }
    else if($_POST['search_by'] == 'aid')
    {
        $Search['type'] = 'aid';
    }
    else if($_POST['search_by'] == 'ip')
    {
        $Search['type'] = 'ipstring';
        $parse['AnyIPDisplay'] = '';
    }
    else
    {
        $Search['type'] = 'uname';
        $_POST['search_by'] = 'name';
    }
    $parse['searchBySelect_'.$_POST['search_by']] = 'selected';
}

$Search['StartTime'] = microtime(true);
$FilterResult = Filter_Users($Search['string'], $Search['type'], $Search['Flags']);
$Search['EndTime'] = microtime(true);

if(!empty($_POST['massAction']))
{
    if($_POST['massAction'] == 'ban' || $_POST['massAction'] == 'unban')
    {
        if(isset($_POST['useAllFiltered']) && $_POST['useAllFiltered'] == '1')
        {
            if($FilterResult !== true AND $FilterResult !== false)
            {
                $PassIDs = implode(',', $FilterResult);
            }
        }
        else
        {
            $GetIDs = explode(',', trim($_POST['massActionIDs']));
            foreach($GetIDs as $ThisID)
            {
                $ThisID = round($ThisID);
                if($ThisID > 0)
                {
                    $PassIDs[] = $ThisID;
                }
            }
            if(!empty($PassIDs))
            {
                $PassIDs = implode(',', $PassIDs);
            }
        }

        if(!empty($PassIDs))
        {
            if($_POST['massAction'] == 'ban')
            {
                header('Location: banuser.php?ids='.$PassIDs);
            }
            else if($_POST['massAction'] == 'unban')
            {
                header('Location: unbanuser.php?ids='.$PassIDs);
            }
            safeDie();
        }
    }
}

$Loading['StartTime'] = microtime(true);
if($FilterResult !== true)
{
    if(!empty($FilterResult))
    {
        $WhereClausure = implode(', ', $FilterResult);
        $SelectCount = count($FilterResult);
        if($GetStart >= $SelectCount)
        {
            $CurrentPage = ceil($SelectCount / $PerPage);
            $GetStart = (string) ((($CurrentPage - 1) * $PerPage) + 0);
        }
        $SQLResult_GetUsers = doquery("SELECT {$UserFields} FROM {{table}} WHERE `id` IN ({$WhereClausure}) ORDER BY `{$TypeSort}` {$SortMode} LIMIT {$GetStart}, {$PerPage};", 'users');
    }
    else
    {
        $SelectCount = 0;
        if($FilterResult === false)
        {
            $parse['AdditionalInfoBox'] = $_Lang['UserFilter_NoGoodStrings'];
            $parse['AdditionalInfoBox_Color'] = 'red';
        }
    }
}
else
{
    $SelectCount = doquery("SELECT COUNT(`id`) AS `Count` FROM {{table}};", 'users', true);
    $SelectCount = $SelectCount['Count'];
    if($GetStart >= $SelectCount)
    {
        $CurrentPage = ceil($SelectCount / $PerPage);
        $GetStart = (string) ((($CurrentPage - 1) * $PerPage) + 0);
    }
    $SQLResult_GetUsers = doquery("SELECT {$UserFields} FROM {{table}} ORDER BY `{$TypeSort}` {$SortMode} LIMIT {$GetStart}, {$PerPage};", 'users');
}
$Loading['EndTime'] = microtime(true);

if($SelectCount > $PerPage)
{
    include_once($_EnginePath.'includes/functions/Pagination.php');
    $Pagin = CreatePaginationArray($SelectCount, $PerPage, $CurrentPage, 7);
    $PaginationTPL = "<a class=\"pagin {\$Classes}\" id=\"page_{\$Value}\">{\$ShowValue}</a>";
    $PaginationViewOpt = array('CurrentPage_Classes' => 'orange', 'Breaker_View' => '...');
    $CreatePagination = implode(' ', ParsePaginationArray($Pagin, $CurrentPage, $PaginationTPL, $PaginationViewOpt));
    $parse['pagination'] = '<tr><th colspan="8" style="padding: 7px;">'.$CreatePagination.'</th></tr>';
}

if($SelectCount > 0)
{
    while($UserData = $SQLResult_GetUsers->fetch_assoc())
    {
        $Users[$UserData['id']] =
        array
        (
            'username'            => $UserData['username'],
            'ally_id'            => $UserData['ally_id'],
            'ally_request'        => $UserData['ally_request'],
            'authlevel'            => $UserData['authlevel'],
            'email'                => $UserData['email'],
            'email_2'            => $UserData['email_2'],
            'uagent'            => $UserData['user_agent'],
            'resolution'        => $UserData['screen_settings'],
            'ip_at_reg'            => $UserData['ip_at_reg'],
            'user_lastip'        => $UserData['user_lastip'],
            'register_time'        => $UserData['register_time'],
            'onlinetime'        => $UserData['onlinetime'],
            'current_page'        => $UserData['current_page'],
            'is_banned'            => $UserData['is_banned'],
            'ban_endtime'        => $UserData['ban_endtime'],
            'vacation'            => $UserData['is_onvacation'],
            'vacation_since'    => $UserData['vacation_starttime'],
            'vacation_endtime'    => $UserData['vacation_endtime'],
            'is_ondeletion'        => $UserData['is_ondeletion'],
            'deletion_endtime'    => $UserData['deletion_endtime'],
            'pro_till'            => $UserData['pro_time'],
            'multi_validated'    => $UserData['multi_validated'],
            'activation_code'    => $UserData['activation_code'],
        );

        if(!empty($UserData['ip_at_reg']))
        {
            $GetIPs['ip_at_reg'][] = "'{$UserData['ip_at_reg']}'";
        }
        if(!empty($UserData['user_lastip']))
        {
            $GetIPs['user_lastip'][] = "'{$UserData['user_lastip']}'";
        }

        if($UserData['ally_id'] > 0)
        {
            if(!empty($GetAllys))
            {
                if(!in_array($UserData['ally_id'], $GetAllys))
                {
                    $GetAllys[] = $UserData['ally_id'];
                }
            }
            else
            {
                $GetAllys[] = $UserData['ally_id'];
            }
        }
        else
        {
            if(!empty($UserData['ally_request']))
            {
                if(!empty($GetAllys))
                {
                    if(!in_array($UserData['ally_request'], $GetAllys))
                    {
                        $GetAllys[] = $UserData['ally_request'];
                    }
                }
                else
                {
                    $GetAllys[] = $UserData['ally_request'];
                }
            }
        }
    }

    $Profiling['repeatedIPs'] = microtime(true);
    foreach($GetIPs as $Field => $Values)
    {
        $GetIPsWhere[$Field] = implode(', ', $Values);
    }
    if(!empty($GetIPsWhere['user_lastip']))
    {
        $GetIPsQuery[] = "SELECT `user_lastip` AS `ip`, COUNT(`user_lastip`) AS `count`, '1' AS `type` FROM {{table}} WHERE `user_lastip` IN ({$GetIPsWhere['user_lastip']}) GROUP BY `user_lastip`";
    }
    if(!empty($GetIPsWhere['ip_at_reg']))
    {
        $GetIPsQuery[] = "SELECT `ip_at_reg` AS `ip`, COUNT(`ip_at_reg`) AS `count`, '2' AS `type` FROM {{table}} WHERE `ip_at_reg` IN ({$GetIPsWhere['ip_at_reg']}) GROUP BY `ip_at_reg`";
    }

    if(!empty($GetIPsQuery))
    {
        $SQLResult_GetIPs = doquery(implode(' UNION ', $GetIPsQuery), 'users');
        if($SQLResult_GetIPs->num_rows > 0)
        {
            while($IPsData = $SQLResult_GetIPs->fetch_assoc())
            {
                if($IPsData['type'] == 1)
                {
                    $Key = 'user_lastip';
                }
                else
                {
                    $Key = 'ip_at_reg';
                }
                $IPsList[$Key][$IPsData['ip']] = $IPsData['count'];
            }
        }
        $Profiling['repeatedIPs'] = microtime(true) - $Profiling['repeatedIPs'];
    }

    if(!empty($GetAllys))
    {
        $Query = "SELECT `id`, `ally_name`, `ally_owner` FROM {{table}} WHERE `id` IN (".implode(', ', $GetAllys).");";
        $SQLResult_GetAlliances = doquery($Query, 'alliance');
        if($SQLResult_GetAlliances->num_rows > 0)
        {
            while($Data = $SQLResult_GetAlliances->fetch_assoc())
            {
                $Allys[$Data['id']] =
                array
                (
                    'name'=> $Data['ally_name'],
                    'owner' => $Data['ally_owner']
                );
            }
        }
    }
}

$parse['adm_ul_table'] = '';
if(!empty($Users))
{
    $ThisDate['d'] = date('d');
    $ThisDate['dmy'] = date('d.m.Y');
    $ThisDate['dmyp'] = date('d.m.Y', $Now - TIME_DAY);
    $Times['28d'] = TIME_DAY * 28;
    $Times['7d'] = TIME_DAY * 7;

    foreach($Users as $ID => $Data)
    {
        $HasHigherAuthlevel = ($Data['authlevel'] > $_User['authlevel'] ? true : false);

        if(isset($IPsList['user_lastip'][$Data['user_lastip']]) && $IPsList['user_lastip'][$Data['user_lastip']] > 1)
        {
            if($Data['multi_validated'] == 1)
            {
                $ColorIP = 'orange';
            }
            else
            {
                $ColorIP = 'red';
            }
        }
        else
        {
            $ColorIP = 'lime';
        }
        if(isset($IPsList['ip_at_reg'][$Data['ip_at_reg']]) && $IPsList['ip_at_reg'][$Data['ip_at_reg']] > 1)
        {
            if($Data['multi_validated'] == 1)
            {
                $ColorReg = 'orange';
            }
            else
            {
                $ColorReg = 'red';
            }
        }
        else
        {
            $ColorReg = 'lime';
        }

        // ------------------------------
        // Here start creating user Bloc!
        // RowElement - ID
        $Bloc['adm_ul_data_id'] = $ID;

        // RowElement - Username
        if($Data['is_ondeletion'] == 1)
        {
            $AddClass4Name = ' orange';
        }
        else if($Data['ban_endtime'] > $Now)
        {
            $AddClass4Name = ' banned';
        }
        else if($Data['vacation'] == 1)
        {
            $AddClass4Name = ' vacations';
        }
        else
        {
            $AddClass4Name = '';
        }
        $Bloc['adm_ul_data_name'] = "<a href=\"#\" class=\"usrName{$AddClass4Name}\" id=\"usrNo{$ID}\">{$Data['username']}</a>";
        if($Data['is_ondeletion'] == 1)
        {
            $Bloc['adm_ul_data_name'] = "<strike class=\"red\">{$Bloc['adm_ul_data_name']}</strike>";
        }

        // RowElement - User Alliance
        if($Data['ally_id'] > 0 OR $Data['ally_request'] > 0)
        {
            if($Data['ally_id'] <= 0)
            {
                $Data['ally_id'] = $Data['ally_request'];
            }
            $Bloc['UserAlliance'] = "<a class=\"insertSearch string_{$Data['ally_id']} type_aid\" href=\"#\">".wordwrap($Allys[$Data['ally_id']]['name'], 15, '<br/>', true)."</a>";
            if($ID == $Allys[$Data['ally_id']]['owner'])
            {
                $Bloc['UserAlliance'] = "<span class=\"allyOwn\">{$Bloc['UserAlliance']}</span>";
            }
            if($Data['ally_id'] == $Data['ally_request'])
            {
                $Bloc['UserAlliance'] = "<span class=\"allyReq\">{$Bloc['UserAlliance']}</span>";
            }
        }
        else
        {
            $Bloc['UserAlliance'] = '&nbsp;';
        }

        // RowElement - Email (mail)
        if($HasHigherAuthlevel)
        {
            $Bloc['adm_ul_data_mail'] = $_Lang['ThisData_NoAccess'];
        }
        else
        {
            $Bloc['adm_ul_data_mail'] = $Data['email'];
        }

        // RowElement - User IPs
        if($HasHigherAuthlevel)
        {
            $Bloc['ip_adress_at_register'] = $_Lang['ThisData_NoAccess'];
        }
        else
        {
            if(!empty($Data['ip_at_reg']))
            {
                $Bloc['ip_adress_at_register'] = "<a class=\"insertSearch string_{$Data['ip_at_reg']} type_ip {$ColorReg}\" href=\"#\">{$Data['ip_at_reg']}</a>";
            }
            else
            {
                $Bloc['ip_adress_at_register'] = "<b class=\"red\">{$_Lang['_NoIP']}</b>";
            }
        }
        if($HasHigherAuthlevel)
        {
            $Bloc['adm_ul_data_adip'] = $_Lang['ThisData_NoAccess'];
        }
        else
        {
            if(!empty($Data['user_lastip']))
            {
                $Bloc['adm_ul_data_adip'] = "<a class=\"insertSearch string_{$Data['user_lastip']} type_ip {$ColorIP}\" href=\"#\">{$Data['user_lastip']}</a>";
            }
            else
            {
                $Bloc['adm_ul_data_adip'] = "<b class=\"red\">{$_Lang['_NoIP']}</b>";
            }
        }

        // RowElement - Registration Time
        if($Data['register_time'] > 0)
        {
            $Bloc['adm_ul_data_regd'] = '';
            $RegisterDays = floor(($Now - $Data['register_time']) / TIME_DAY);
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
                if($ThisDate['d'] == date('d', $Data['register_time']))
                {
                    $Bloc['adm_ul_data_regd'] = $_Lang['_today'];
                }
                else
                {
                    $Bloc['adm_ul_data_regd'] = $_Lang['_yesterday'];
                }
            }
            else
            {
                $Bloc['adm_ul_data_regd'] = "{$RegisterDays} {$RegDays} {$_Lang['_ago']}";
            }
            $Bloc['RegDate_TH_Title'] = '<center>'.prettyDate('d m Y, H:i:s', $Data['register_time'], 1).'</center>';
        }
        else
        {
            $Bloc['adm_ul_data_regd'] = '&nbsp;';
            $Bloc['RegDate_TH_Title'] = $_Lang['No_Data'];
        }

        // RowElement - Last Onlinetime
        if($ThisDate['dmy'] == date('d.m.Y', $Data['onlinetime']))
        {
            if($Data['onlinetime'] >= ($Now - TIME_ONLINE))
            {
                $OnlineDate = pretty_time($Now - $Data['onlinetime'], false, 'ms');
            }
            else
            {
                $OnlineDate = $_Lang['_today'];
            }
        }
        else if($ThisDate['dmyp'] == date('d.m.Y', $Data['onlinetime']))
        {
            $OnlineDate = $_Lang['_yesterday'];
        }
        else
        {
            $OnlineDate = date('d.m.Y', $Data['onlinetime']);
        }
        $Bloc['adm_ul_data_lconn'] = $OnlineDate;
        $OnlineDiff = $Now - $Data['onlinetime'];

        if($OnlineDiff >= $Times['28d'])
        {
            $OLColor = 'red';
        }
        elseif($OnlineDiff >= $Times['7d'])
        {
            $OLColor = '#FFA0A0';
        }
        elseif($OnlineDiff >= TIME_DAY)
        {
            $OLColor = 'orange';
        }
        elseif($OnlineDiff > TIME_ONLINE)
        {
            $OLColor = 'yellow';
        }
        else
        {
            $OLColor = 'lime';
        }
        $OnlineDiffText = '<span class='.$OLColor.'>('.pretty_time($OnlineDiff).' '.$_Lang['_ago'].')</span>';
        if(CheckAuth('supportadmin'))
        {
            $Bloc['OnlineDate_TH_Title'] = "<br/>{$Data['current_page']}";
        }
        $Bloc['OnlineDate_TH_Title'] = "<center>".prettyDate('d m Y, H:i:s', $Data['onlinetime'], 1)."<br/>{$OnlineDiffText}{$Bloc['OnlineDate_TH_Title']}</center>";
        $Bloc['adm_ul_data_lconn'] = '<span style="cursor: help;'.($OLColor == 'lime' ? ' color: lime;' : '').'">'.$Bloc['adm_ul_data_lconn'].'</span>';

        // RowElement - Expanded Info (MoreInfo)
        // -------------------------------------

        // MoreInfo - Browser and Screen
        if($HasHigherAuthlevel)
        {
            $Bloc['UserMoreInfo'] = $_Lang['ThisData_NoAccess'];
        }
        else
        {
            $Bloc['UserMoreInfo'] = "<b class=\"orange\">{$_Lang['Userlist_Browser']}:</b> ".(!empty($Data['uagent']) ? $Data['uagent'] : $_Lang['No_Data'])."<br/><b class=\"orange\">{$_Lang['Userlist_Screen']}:</b> ".(!empty($Data['resolution']) ? str_replace('_', 'x', $Data['resolution']) : $_Lang['No_Data']);
        }
        // MoreInfo - EMails
        if($HasHigherAuthlevel)
        {
            $Bloc['UserMoreInfo'] .= '<br/>'.$_Lang['ThisData_NoAccess'];
        }
        else
        {
            $Bloc['UserMoreInfo'] .= "<br/><span class=\"fl\"><b class=\"orange\">{$_Lang['EMails']}</b>: <b class=\"mEmail\">{$Data['email']}</b> (<b class=\"xEmail\">{$Data['email_2']}</b>)</span><span class=\"fr\">[".(($Data['email'] == $Data['email_2']) ? $_Lang['_same'] : $_Lang['_notsame'])."]</span>";
        }
        // MoreInfo - IPs
        if($HasHigherAuthlevel)
        {
            $Bloc['UserMoreInfo'] .= '<br/>'.$_Lang['ThisData_NoAccess'];
        }
        else
        {
            $Bloc['UserMoreInfo'] .= "<br/><span class=\"fl\"><b class=\"orange\">{$_Lang['IPs']}</b>: <b class=\"lastIP\">{$Bloc['adm_ul_data_adip']}</b> / <b class=\"regIP\">{$Bloc['ip_adress_at_register']}</b></span><span class=\"fr\">[<b class=\"lkupinfo\">{$_Lang['Lookup']}</b>: <a href=\"http://network-tools.com/default.asp?prog=lookup&host={$Data['user_lastip']}\" target=\"_blank\">{$_Lang['LastIPLook']}</a> | <a href=\"http://network-tools.com/default.asp?prog=lookup&host={$Data['ip_at_reg']}\" target=\"_blank\">{$_Lang['RegIPLook']}</a>]</span>";
        }
        // MoreInfo - Vacation Info
        if($Data['vacation'] == 1)
        {
            $Bloc['UserMoreInfo'] .= "<br/><b class=\"vacations fl\">{$_Lang['UserOnVacation']}</b>";
            $Bloc['UserMoreInfo'] .= "<span class=\"fr\">({$_Lang['_Since']}: ".pretty_time($Now - $Data['vacation_since'])." / ".date('d.m.Y, H:i:s', $Data['vacation_since'])." | ".($Data['vacation_endtime'] == 0 ? "<b class=\"orange\">{$_Lang['InfiniteVacation']}</b>" : "{$_Lang['_Duration']}: ".pretty_time($Data['vacation_endtime'] - $Now)." / ".date('d.m.Y, H:i:s', $Data['vacation_endtime'])).")</span>";
        }
        // MoreInfo - Ban Info
        if($Data['is_banned'] == 1)
        {
            if($Data['ban_endtime'] < $Now)
            {
                $BanColor = 'orange';
                $BanTime = $_Lang['Ban_Expired'];
            }
            else
            {
                $BanColor = 'red';
                $BanTime = $_Lang['Ban_TimeLeft'].' '.pretty_time($Data['ban_endtime'] - $Now);
            }
            $Bloc['UserMoreInfo'] .= "<br/><span class=\"fl\"><b class=\"red\">{$_Lang['UserIsBanned']}:</b> <b class=\"{$BanColor}\">".date('d.m.Y, H:i:s', $Data['ban_endtime'])."</b></span><span class=\"fr\">({$BanTime})</span>";
        }
        // MoreInfo - Activation Link
        if(!empty($Data['activation_code']))
        {
            $Bloc['UserMoreInfo'] .= "<br/><span class=\"fl\"><b class=\"orange\">{$_Lang['UserNotActivated']}</b></span><span class=\"fr\">[{$_Lang['UserActivationLink']}: <a href=\"".(GAMEURL)."activate.php?code={$Data['activation_code']}\" target=\"_blank\">".(GAMEURL)."activate.php?code=<b class=\"orange\">{$Data['activation_code']}</b></a>]</span>";
        }
        // MoreInfo - is On Deletion
        if($Data['is_ondeletion'] == 1)
        {
            $Bloc['UserMoreInfo'] .= "<br/><span class=\"fl\"><b class=\"red\">{$_Lang['UserIsOnDeletion']}</b></span><span class=\"fr\">[{$_Lang['UserDeleteIn']}: <span class=\"orange\">".($Data['deletion_endtime'] > $Now ? (pretty_time($Data['deletion_endtime'] - $Now)." / ".date('d.m.Y, H:i:s', $Data['deletion_endtime'])) : $_Lang['DeleteInNextCalc'])."</span>]</span>";
        }

        // MoreInfo - Action Links
        $UserActions = false;
        // > Action Links for GameOperator (GO) and higher ranks
        if(CheckAuth('go'))
        {
            $UserActions[] = "<a class=\"aInfo\" href=\"user_info.php?uid={$ID}\">{$_Lang['UserInfo']}</a>";
            $UserActions[] = "<a class=\"aPM\" href=\"../messages.php?mode=write&uid={$ID}\">{$_Lang['UserPM']}</a>";
            $UserActions[] = "<a class=\"aMsgFind\" href=\"messagelist.php?uid={$ID}\">{$_Lang['ShowUserMessages']}</a>";
        }
        // > Action Links for SuperGameOperator (SGO) and higher ranks
        if(CheckAuth('sgo')){
            if($Data['is_banned'] == 1)
            {
                $UserActions[] = "<a class=\"aUnban\" href=\"unbanuser.php?ids={$ID}\">{$_Lang['UserUnban']}</a>";
            }
            else
            {
                $UserActions[] = "<a class=\"aBan\" href=\"banuser.php?ids={$ID}\">{$_Lang['UserBan']}</a>";
            }
        }
        // > Action Links for Administrator (Admin) and higher ranks
        if(CheckAuth('supportadmin'))
        {
            $UserActions[] = "<a class=\"aLog\" href=\"browse_actionlogs.php?uid={$ID}\">{$_Lang['ShowActionLogs']}</a>";
            $UserActions[] = "<a class=\"aDel\" id=\"delID_{$ID}\">{$_Lang['UserDelete']}</a>";
        }
        $Bloc['UserActions'] = implode(' | ', $UserActions);
        // > End of Action Links HERE
        // End of MoreInfo HERE
        // End of RowElement HERE

        // - Create Bloc!
        $parse['adm_ul_table'] .= parsetemplate($RowsTPL, $Bloc);
        $Bloc = false;
    }
}
else
{
    $parse['adm_ul_table'] .= '<tr><th colspan="9" class="c red">'.$_Lang['Userlist_NoUsersFound'].'</th></tr>';
}

$parse['FoundUsers_Count'] = sprintf($_Lang['FoundUsers_Count'], prettyNumber($SelectCount));
$parse['Filtering_Time'] = sprintf($_Lang['Filtering_Time'], sprintf('%0.5f', ($Search['EndTime'] - $Search['StartTime'])));
$parse['Loading_Time'] = sprintf($_Lang['Loading_Time'], sprintf('%0.5f', ($Loading['EndTime'] - $Loading['StartTime'])));

if(!empty($parse['AdditionalInfoBox']))
{
    $parse['AdditionalInfoBox'] = '<tr><th class="c pad2 '.$parse['AdditionalInfoBox_Color'].'" colspan="9">'.$parse['AdditionalInfoBox'].'</th></tr>';
}

$page = parsetemplate($PageTPL, $parse);
display($page, $_Lang['Userlist_Title'], false, true);

?>
