<?php

define('INSIDE', true );

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

function CheckJobsDone($JobType, $UserID)
{
    global $_User, $GlobalParsedTasks;

    if($_User['id'] == $UserID)
    {
        $CurrentUser = $_User;
    }
    else
    {
        if(empty($GlobalParsedTasks[$UserID]['tasks_done_parsed']))
        {
            $GetUserTasksDone = doquery("SELECT `tasks_done` FROM {{table}} WHERE `id` = {$UserID} LIMIT 1;", 'users', true);
            Tasks_CheckUservar($GetUserTasksDone);
            $GlobalParsedTasks[$UserID] = $GetUserTasksDone;
        }
        $CurrentUser = $GlobalParsedTasks[$UserID];
        $CurrentUser['id'] = $UserID;
    }
    Tasks_TriggerTask($CurrentUser, $JobType);
}

includeLang('buddy');
$Parse = &$_Lang;
$Now = time();

$_PerPage = 15;
$_MaxLength = 1000;

$Command = (isset($_GET['cmd']) ? $_GET['cmd'] : null);
$UID = (isset($_GET['uid']) ? intval($_GET['uid']) : 0);

if($Command == 'accept')
{
    $Command = '';
    if($UID > 0)
    {
        doquery("UPDATE {{table}} SET `active` = 1 WHERE `sender` = {$UID} AND `owner` = {$_User['id']} AND `active` = 0;", 'buddy');
        if(getDBLink()->affected_rows == 1)
        {
            $Message['msg_id'] = '090';
            $Message['args'] = array($_User['id'], $_User['username']);
            $Message = json_encode($Message);
            Cache_Message($UID, 0, NULL, 70, '007', '021', $Message);

            CheckJobsDone('BUDDY_OR_ALLY_TASK', $_User['id']);
            CheckJobsDone('BUDDY_OR_ALLY_TASK', $UID);

            $Parse['Insert_MsgBoxText'] = $_Lang['Success_Accepted'];
            $Parse['Insert_MsgBoxColor'] = 'lime';
        }
        else
        {
            $Parse['Insert_MsgBoxText'] = $_Lang['Error_BadElement'];
            $Parse['Insert_MsgBoxColor'] = 'red';
        }
    }
    else
    {
        $Parse['Insert_MsgBoxText'] = $_Lang['Error_ElementIDBad'];
        $Parse['Insert_MsgBoxColor'] = 'red';
    }
}
else if($Command == 'refuse')
{
    $Command = '';
    if($UID > 0)
    {
        doquery("DELETE FROM {{table}} WHERE `sender` = {$UID} AND `owner` = {$_User['id']} AND `active` = 0;", 'buddy');
        if(getDBLink()->affected_rows == 1)
        {
            $Message['msg_id'] = '091';
            $Message['args'] = array($_User['id'], $_User['username']);
            $Message = json_encode($Message);
            Cache_Message($UID, 0, NULL, 70, '007', '021', $Message);

            $Parse['Insert_MsgBoxText'] = $_Lang['Success_Refused'];
            $Parse['Insert_MsgBoxColor'] = 'lime';
        }
        else
        {
            $Parse['Insert_MsgBoxText'] = $_Lang['Error_BadElement'];
            $Parse['Insert_MsgBoxColor'] = 'red';
        }
    }
    else
    {
        $Parse['Insert_MsgBoxText'] = $_Lang['Error_ElementIDBad'];
        $Parse['Insert_MsgBoxColor'] = 'red';
    }
}
else if($Command == 'rmv')
{
    $Command = '';
    if($UID > 0)
    {
        doquery(
            "DELETE FROM {{table}} WHERE ((`sender` = {$UID} AND `owner` = {$_User['id']}) OR (`owner` = {$UID} AND `sender` = {$_User['id']})) AND `active` = 1;",
            'buddy'
        );

        if(getDBLink()->affected_rows == 1)
        {
            $Message['msg_id'] = '092';
            $Message['args'] = array($_User['id'], $_User['username']);
            $Message = json_encode($Message);
            Cache_Message($UID, 0, NULL, 70, '007', '021', $Message);

            $Parse['Insert_MsgBoxText'] = $_Lang['Success_Removed'];
            $Parse['Insert_MsgBoxColor'] = 'lime';
        }
        else
        {
            $Parse['Insert_MsgBoxText'] = $_Lang['Error_BadElement'];
            $Parse['Insert_MsgBoxColor'] = 'red';
        }
    }
    else
    {
        $Parse['Insert_MsgBoxText'] = $_Lang['Error_ElementIDBad'];
        $Parse['Insert_MsgBoxColor'] = 'red';
    }
}
else if($Command == 'del')
{
    $Command = '';
    if($UID > 0)
    {
        doquery(
            "DELETE FROM {{table}} WHERE `owner` = {$UID} AND `sender` = {$_User['id']} AND `active` = 0;",
            'buddy'
        );

        if(getDBLink()->affected_rows == 1)
        {
            $Parse['Insert_MsgBoxText'] = $_Lang['Success_Deleted'];
            $Parse['Insert_MsgBoxColor'] = 'lime';
        }
        else
        {
            $Parse['Insert_MsgBoxText'] = $_Lang['Error_BadElement'];
            $Parse['Insert_MsgBoxColor'] = 'red';
        }
    }
    else
    {
        $Parse['Insert_MsgBoxText'] = $_Lang['Error_ElementIDBad'];
        $Parse['Insert_MsgBoxColor'] = 'red';
    }
}
else if($Command != 'edit' AND $Command != 'add')
{
    $Command = '';
}

if(empty($Command))
{
    $TPL_Default = gettemplate('buddy_list_body');

    $Parse['Insert_HideSeparator'] = 'hide';
    $Parse['Insert_HideWithBuddyList'] = '';
    $Parse['Insert_HidePagination'] = 'hide';

    $Get_BuddyCount = doquery("SELECT COUNT(*) AS `Count` FROM {{table}} WHERE `owner` = {$_User['id']} OR `sender` = {$_User['id']};", 'buddy', true);
    $BuddyCount = $Get_BuddyCount['Count'];
    if($BuddyCount > 0)
    {
        $TPL_BuddyRow = gettemplate('buddy_list_row');

        $ThisPage = (isset($_GET['page']) ? intval($_GET['page']) : 0);
        if($ThisPage < 1)
        {
            $ThisPage = 1;
        }
        $LimitStart = ($ThisPage - 1) * $_PerPage;
        if($LimitStart >= $Get_BuddyCount)
        {
            $ThisPage = 1;
            $LimitStart = 0;
        }
        $AddPage2Link = '';
        if($ThisPage > 1)
        {
            $AddPage2Link = '&amp;page='.$ThisPage;
        }
        $LastPage = ceil($BuddyCount / $_PerPage);

        $NeededFields = "`buddy`.*, `users`.`username`, `users`.`onlinetime`, `users`.`galaxy`, `users`.`system`, `users`.`planet`, `users`.`ally_id`, `ally`.`ally_name`, `stats`.`total_rank`, `stats`.`total_points` ";
        $Query = '';
        $Query .= "SELECT {$NeededFields} FROM {{table}} AS `buddy` ";
        $Query .= "LEFT JOIN `{{prefix}}users` AS `users` ON `users`.`id` = IF(`buddy`.`owner` = {$_User['id']}, `buddy`.`sender`, `buddy`.`owner`) ";
        $Query .= "LEFT JOIN `{{prefix}}alliance` AS `ally` ON `ally`.`id` = `users`.`ally_id` ";
        $Query .= "LEFT JOIN `{{prefix}}statpoints` AS `stats` ON `users`.`id` = `stats`.`id_owner` AND `stats`.`stat_type` = '1' ";
        $Query .= "WHERE `buddy`.`owner` = {$_User['id']} OR `buddy`.`sender` = {$_User['id']} ";
        $Query .= "ORDER BY `buddy`.`active` ASC, `buddy`.`date` DESC LIMIT {$LimitStart}, {$_PerPage};";
        $Get_Buddy = doquery($Query, 'buddy');

        while($BuddyData = $Get_Buddy->fetch_assoc())
        {
            $BuddyData['UID'] = ($BuddyData['owner'] == $_User['id'] ? $BuddyData['sender'] : $BuddyData['owner']);
            $BuddyParse = array
            (
                'Insert_UID' => $BuddyData['UID'],
                'Insert_Nick' => $BuddyData['username'],
                'Insert_StatPositionLink' => $BuddyData['total_rank'],
                'Insert_StatPoints' => prettyNumber($BuddyData['total_points']),
                'Insert_StatPosition' => prettyNumber($BuddyData['total_rank']),
                'Insert_AllyID' => $BuddyData['ally_id'],
                'Insert_Ally' => $BuddyData['ally_name'],
                'Insert_PosGalaxy' => $BuddyData['galaxy'],
                'Insert_PosSystem' => $BuddyData['system'],
                'Insert_PosPlanet' => $BuddyData['planet'],
                'Insert_Date' => date('d.m.Y, H:i:s', $BuddyData['date'])
            );

            if($BuddyData['active'] == 1)
            {
                $OnlineDiff = $Now - $BuddyData['onlinetime'];
                if($OnlineDiff <= TIME_ONLINE)
                {
                    $BuddyParse['Insert_State'] = $_Lang['Row_State_Online'];
                    $BuddyParse['Insert_StateColor'] = 'lime';
                }
                else if($OnlineDiff <= TIME_HOUR)
                {
                    $BuddyParse['Insert_State'] = floor($OnlineDiff / 60).' '.$_Lang['Row_State_Minutes'];
                    $BuddyParse['Insert_StateColor'] = 'orange';
                }
                else
                {
                    $BuddyParse['Insert_State'] = $_Lang['Row_State_Offline'].': '.floor($OnlineDiff / TIME_DAY).' '.$_Lang['Row_State_Days'];
                    $BuddyParse['Insert_StateColor'] = 'red';
                }
                $BuddyParse['Insert_Actions'][] = "<a class=\"rmv\" href=\"?cmd=rmv&amp;uid={$BuddyData['UID']}{$AddPage2Link}\"></a>";
            }
            else
            {
                $BuddyParse['Insert_State'] = $_Lang['Row_State_Awaiting'];
                if($BuddyData['sender'] == $_User['id'])
                {
                    $BuddyParse['Insert_StateColor'] = 'orange';
                    $BuddyParse['Insert_Actions'][] = "<a class=\"edit\" href=\"?cmd=edit&amp;uid={$BuddyData['UID']}\"></a>";
                    $BuddyParse['Insert_Actions'][] = "<a class=\"del\" href=\"?cmd=del&amp;uid={$BuddyData['UID']}{$AddPage2Link}\"></a>";
                }
                else
                {
                    $BuddyParse['Insert_StateColor'] = 'lime';
                    $BuddyParse['Insert_Actions'][] = "<a class=\"accept\" href=\"?cmd=accept&amp;uid={$BuddyData['UID']}{$AddPage2Link}\"></a>";
                    $BuddyParse['Insert_Actions'][] = "<a class=\"refuse\" href=\"?cmd=refuse&amp;uid={$BuddyData['UID']}{$AddPage2Link}\"></a>";
                }
            }
            if(!empty($BuddyParse['Insert_Actions']))
            {
                $BuddyParse['Insert_Actions'] = implode(' ', $BuddyParse['Insert_Actions']);
            }
            else
            {
                $BuddyParse['Insert_Actions'] = '&nbsp;';
            }

            $BuddyParse = parsetemplate($TPL_BuddyRow, $BuddyParse);
            if($BuddyData['active'] == 1)
            {
                $BuddyRows['active'][] = $BuddyParse;
            }
            else
            {
                $BuddyRows['awaiting'][] = $BuddyParse;
            }
        }

        if(!empty($BuddyRows['awaiting']))
        {
            $Parse['Insert_AwaitingList'] = implode('', $BuddyRows['awaiting']);
            $Parse['Insert_HideSeparator'] = '';
        }
        if(!empty($BuddyRows['active']))
        {
            $Parse['Insert_BuddyList'] = implode('', $BuddyRows['active']);
            $Parse['Insert_HideWithBuddyList'] = 'hide';
        }
        else
        {
            $Parse['Insert_HideSeparator'] = '';
            if($LastPage != $ThisPage)
            {
                $Parse['Insert_HideSeparator'] = 'hide';
                $Parse['Insert_HideWithBuddyList'] = 'hide';
            }
        }

        if($BuddyCount > $_PerPage)
        {
            include_once($_EnginePath.'includes/functions/Pagination.php');
            $Pagination = CreatePaginationArray($BuddyCount, $_PerPage, $ThisPage, 7);
            $PaginationTPL = "<a class=\"pagin {\$Classes}\" href=\"?page={\$Value}\">{\$ShowValue}</a>";
            $PaginationViewOpt = array('CurrentPage_Classes' => 'orange', 'Breaker_View' => '...');
            $Pagination = ParsePaginationArray($Pagination, $ThisPage, $PaginationTPL, $PaginationViewOpt);
            $Parse['Insert_Pagination'] = implode(' ', $Pagination);
            $Parse['Insert_HidePagination'] = '';
        }
    }
}
else if($Command == 'edit' OR $Command == 'add')
{
    $TPL_Default = gettemplate('buddy_form');

    if($Command == 'add')
    {
        if($UID < 1)
        {
            message($_Lang['Error_BadUID'], $_Lang['Title'], 'buddy.php', 3);
        }
        $GetUserdata = doquery("SELECT `username` FROM {{table}} WHERE `id` = {$UID} LIMIT 1;", 'users', true);
        if(empty($GetUserdata['username']))
        {
            message($_Lang['Error_BadUser'], $_Lang['Title'], 'buddy.php', 3);
        }
        $CheckBuddy = doquery("SELECT COUNT(*) AS `Count` FROM {{table}} WHERE (`sender` = {$_User['id']} AND `owner` = {$UID}) OR (`sender` = {$UID} AND `owner` = {$_User['id']}) LIMIT 1;", 'buddy', true);
        if($CheckBuddy['Count'] > 0)
        {
            message($_Lang['Error_LinkExists'], $_Lang['Title'], 'buddy.php', 3);
        }

        $Parse['Insert_Username'] = $GetUserdata['username'];
    }
    else
    {
        if($UID < 1)
        {
            message($_Lang['Error_ElementIDBad'], $_Lang['Title'], 'buddy.php', 3);
        }

        $Query = '';
        $Query .= "SELECT `buddy`.*, `users`.`username` FROM {{table}} AS `buddy` ";
        $Query .= "LEFT JOIN {{prefix}}users AS `users` ON `users`.`id` = `buddy`.`owner` ";
        $Query .= "WHERE (`buddy`.`sender` = {$UID} AND `buddy`.`owner` = {$_User['id']}) OR (`buddy`.`sender` = {$_User['id']} AND `buddy`.`owner` = {$UID}) ";
        $Query .= "LIMIT 1;";
        $CheckBuddy = doquery($Query, 'buddy', true);
        if(empty($CheckBuddy))
        {
            message($_Lang['Error_RowNoExists'], $_Lang['Title'], 'buddy.php', 3);
        }
        if($CheckBuddy['sender'] != $_User['id'])
        {
            message($_Lang['Error_CantEdit'], $_Lang['Title'], 'buddy.php', 3);
        }
        if($CheckBuddy['active'] == 1)
        {
            message($_Lang['Error_AlreadyActive'], $_Lang['Title'], 'buddy.php', 3);
        }

        $Parse['Insert_Username'] = $CheckBuddy['username'];
        $Parse['Insert_Text'] = $CheckBuddy['text'];
    }

    if(isset($_POST['send']) && $_POST['send'] == '1')
    {
        $InsertClean['Text'] = substr(trim(stripslashes($_POST['text'])), 0, $_MaxLength);
        $Insert['Text'] = getDBLink()->escape_string($InsertClean['Text']);

        if(!empty($Insert['Text']))
        {
            if($Command == 'add')
            {
                doquery("INSERT INTO {{table}} SET `sender` = {$_User['id']}, `owner` = {$UID}, `text` = '{$Insert['Text']}', `date` = UNIX_TIMESTAMP();", 'buddy');

                header('Location: ?msg=add');
                safeDie();
            }
            else if($Command == 'edit')
            {
                doquery("UPDATE {{table}} SET `text` = '{$Insert['Text']}', `date` = UNIX_TIMESTAMP() WHERE `sender` = {$CheckBuddy['sender']} AND `owner` = {$CheckBuddy['owner']} LIMIT 1;", 'buddy');

                $Parse['Insert_MsgBoxText'] = $_Lang['Success_Edited'];
                $Parse['Insert_MsgBoxColor'] = 'lime';
            }
        }
        else
        {
            $Parse['Insert_MsgBoxText'] = $_Lang['Error_EmptyText'];
            $Parse['Insert_MsgBoxColor'] = 'red';
        }

        $Parse['Insert_Text'] = $InsertClean['Text'];
    }

    $Parse['Insert_MaxLength'] = $_MaxLength;
}

if(isset($_GET['msg']) && $_GET['msg'] == 'add')
{
    $Parse['Insert_MsgBoxText'] = $_Lang['Success_Sent'];
    $Parse['Insert_MsgBoxColor'] = 'lime';
}

if(empty($Parse['Insert_MsgBoxText']))
{
    $Parse['Insert_MsgBoxHide'] = 'hide';
}

display(parsetemplate($TPL_Default, $Parse), $_Lang['Title'], false);

?>
