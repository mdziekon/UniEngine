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

include($_EnginePath . 'modules/messages/_includes.php');

use UniEngine\Engine\Modules\Messages;

$_PerPage = 25;
includeLang('messageSystem');
includeLang('spyReport');
includeLang('FleetMission_MissileAttack');
includeLang('admin/messagelist');

$BodyTpl = gettemplate('admin/messagelist_body');
$RowsTpl = gettemplate('admin/messagelist_table_rows');

$Prev        = (!empty($_POST['prev'])) ? true : false;
$Next        = (!empty($_POST['next'])) ? true : false;
$DelSel        = (!empty($_POST['delsel_hard'])) ? true : false;
$DelSelSoft = (!empty($_POST['delsel_soft'])) ? true : false;
$SetRead    = (!empty($_POST['setsel_read'])) ? true : false;
$SetNotRead = (!empty($_POST['setsel_notread'])) ? true : false;
$CurrPage    = isset($_POST['curr']) ? intval($_POST['curr']) : 1;
$Selected    = isset($_POST['sele']) ? intval($_POST['sele']) : 100;
$SelType    = isset($_POST['type']) ? intval($_POST['type']) : 100;
$SelPage    = isset($_POST['page_input']) ? round($_POST['page_input']) : null;
if($SelPage < 1)
{
    $SelPage = (isset($_POST['page_select']) ? round($_POST['page_select']) : 0);
}
if($SelPage < 1)
{
    $SelPage = 1;
}
if(empty($_POST))
{
    if(isset($_GET['mid']) && $_GET['mid'] > 0)
    {
        $MID = intval($_GET['mid']);
        if($MID > 0)
        {
            $_POST['msg_id'] = $MID;
        }
    }
}

if(isset($_POST['msg_id']) && $_POST['msg_id'] > 0)
{
    $_POST['msg_id'] = intval($_POST['msg_id']);
    if($_POST['msg_id'] <= 0)
    {
        $_POST['msg_id'] = 0;
    }
}
if(!isset($_POST['msg_id']) || $_POST['msg_id'] == 0)
{
    if(isset($_POST['user_id']) && $_POST['user_id'] > 0)
    {
        $_POST['user_id'] = intval($_POST['user_id']);
        if($_POST['user_id'] <= 0)
        {
            $_POST['user_id'] = 0;
        }
    }
    else
    {
        $_POST['user_id'] = 0;
    }
    if($_POST['user_id'] == 0)
    {
        if(isset($_GET['uid']) && $_GET['uid'] > 0)
        {
            $UID = intval($_GET['uid']);
            if($UID > 0)
            {
                $_POST['user_id'] = $UID;
            }
        }
    }
}

// Do actions in here!
if($DelSelSoft === true || $DelSel === true || $SetRead === true || $SetNotRead === true)
{
    if(CheckAuth('supportadmin'))
    {
        if(!empty($_POST['sele']) && (array)$_POST['sele'] === $_POST['sele'])
        {
            foreach($_POST['sele'] as $MessId => $Value)
            {
                if($Value == 'on')
                {
                    $DeleteIDs[] = $MessId;
                }
            }
            if(!empty($DeleteIDs))
            {
                foreach($DeleteIDs as $CheckID)
                {
                    $CheckCopyTexts[] = "'{COPY_MSG_#{$CheckID}}'";
                }

                $SQLResult_FindAllMessageCopies = doquery(
                    "SELECT `id` FROM {{table}} WHERE `text` IN (".implode(', ', $CheckCopyTexts).");",
                    'messages'
                );

                if($SQLResult_FindAllMessageCopies->num_rows > 0)
                {
                    while($AdditionalIDs = $SQLResult_FindAllMessageCopies->fetch_assoc())
                    {
                        if(!in_array($AdditionalIDs['id'], $DeleteIDs))
                        {
                            $DeleteIDs[] = $AdditionalIDs['id'];
                        }
                    }
                }
                if(!empty($DeleteIDs))
                {
                    $DeleteIDs = implode(',', $DeleteIDs);

                    $IsThreadsUpdateNeeded = false;

                    if($DelSelSoft === true || $DelSel === true)
                    {
                        $IsThreadsUpdateNeeded = true;

                        $SQLResult_GetThreadedMessages = doquery(
                            "SELECT `Thread_ID` FROM {{table}} WHERE `Thread_ID` > 0 AND `id` IN ({$DeleteIDs});",
                            'messages'
                        );
                    }

                    if($DelSelSoft === true)
                    {
                        doquery("UPDATE {{table}} SET `deleted` = 1, `Thread_IsLast` = 0 WHERE `id` IN ({$DeleteIDs});", 'messages');
                    }
                    else if($DelSel === true)
                    {
                        doquery("DELETE FROM {{table}} WHERE `id` IN ({$DeleteIDs});", 'messages');
                    }
                    else if($SetRead === true)
                    {
                        doquery("UPDATE {{table}} SET `read` = 1 WHERE `id` IN ({$DeleteIDs});", 'messages');
                    }
                    else if($SetNotRead === true)
                    {
                        doquery("UPDATE {{table}} SET `read` = 0 WHERE `id` IN ({$DeleteIDs});", 'messages');
                    }

                    if($IsThreadsUpdateNeeded != false)
                    {
                        if($SQLResult_GetThreadedMessages->num_rows > 0)
                        {
                            $UpdateThreads = array();
                            while($FetchData = $SQLResult_GetThreadedMessages->fetch_assoc())
                            {
                                if(!in_array($FetchData['Thread_ID'], $UpdateThreads))
                                {
                                    $UpdateThreads[] = $FetchData['Thread_ID'];
                                }
                            }
                            $IDs = implode(',', $UpdateThreads);

                            $SQLResult_GetThreadsToUpdate = doquery(
                                "SELECT MAX(`id`) AS `id` FROM {{table}} WHERE `Thread_ID` IN ({$IDs}) AND `deleted` = false GROUP BY `Thread_ID`, `id_owner`;",
                                'messages'
                            );

                            if($SQLResult_GetThreadsToUpdate->num_rows > 0)
                            {
                                $UpdateThreads = array();
                                while($SelectData = $SQLResult_GetThreadsToUpdate->fetch_assoc())
                                {
                                    $UpdateThreads[] = $SelectData['id'];
                                }
                                $IDs = implode(',', $UpdateThreads);
                                doquery("UPDATE {{table}} SET `Thread_IsLast` = 1 WHERE `id` IN ({$IDs});", 'messages');
                            }
                        }
                    }
                }
            }
        }
    }
}
// - End of Actions

$ViewPage = 1;
if($Selected != $SelType)
{
    $Selected = $SelType;
    $ViewPage = 1;
}
else if($CurrPage != $SelPage)
{
    $ViewPage = !empty($SelPage) ? $SelPage : 1;
}

$ExcludedUsers = false;
if(CheckAuth('supportadmin'))
{
    $SQLQuery_GetUsersWithHigherAuth = "SELECT `id` FROM {{table}} WHERE `authlevel` >= {$_User['authlevel']} AND `id` != {$_User['id']};";
    $SQLResult_GetUsersWithHigherAuth = doquery(
        $SQLQuery_GetUsersWithHigherAuth,
        'users'
    );

    if($SQLResult_GetUsersWithHigherAuth->num_rows > 0)
    {
        while($Data = $SQLResult_GetUsersWithHigherAuth->fetch_assoc())
        {
            $ExcludedUsers[] = $Data['id'];
        }
        $DisallowTpl = gettemplate('admin/messagelist_table_rows_disallowed');
    }
}

$WhereClausures = false;
if($Selected != 100)
{
    $WhereClausures[] = "`type` = {$Selected}";
}
// $_POST['user_id'] is SAFE for SQL
if($_POST['user_id'] > 0)
{
    if($Selected != 2)
    {
        $WhereClausures[] = "(`id_owner` = {$_POST['user_id']} OR `id_sender` = {$_POST['user_id']})";
    }
    else
    {
        $SQLResult_GetAllianceUsers = doquery(
            "SELECT `id` FROM {{table}} WHERE `ally_id` = {$_POST['user_id']};",
            'users'
        );

        if($SQLResult_GetAllianceUsers->num_rows > 0)
        {
            while($AdditionalData = $SQLResult_GetAllianceUsers->fetch_assoc())
            {
                $AdditionalUsers[] = $AdditionalData['id'];
            }
        }
        if(!empty($AdditionalUsers))
        {
            $WhereClausures[] = "(`id_owner` IN (".implode(', ', $AdditionalUsers).") OR `id_sender` IN (".implode(', ', $AdditionalUsers)."))";
        }
        else
        {
            $WhereClausures[] = "(1 = 2)";
        }
    }
    $DontBlockCopy = true;
}
// $_POST['msg_id'] is SAFE for SQL
if(isset($_POST['msg_id']) && $_POST['msg_id'] > 0)
{
    $WhereClausures[] = "{{table}}.`id` = {$_POST['msg_id']}";
    $DontBlockCopy = true;
}

if(!isset($DontBlockCopy))
{
    $WhereClausures[] = '`text` NOT LIKE \'{COPY_MSG_#%}\'';
}

if(!empty($WhereClausures))
{
    $WhereClausures = ' WHERE '.implode(' AND ', $WhereClausures);
}

$Mess = doquery("SELECT COUNT(`id`) AS `max` FROM {{table}}{$WhereClausures};", 'messages', true);
$MaxPage = ceil(($Mess['max'] / $_PerPage));

if(isset($_POST['stay']) && $_POST['stay'] == 'true')
{
    $Selected = $SelType;
    $ViewPage = (!empty($SelPage)) ? $SelPage : 1;
    if($ViewPage > $MaxPage)
    {
        $ViewPage = $MaxPage;
    }
}

if($Prev == true)
{
    $CurrPage -= 1;
    if($CurrPage >= 1)
    {
        $ViewPage = $CurrPage;
    }
    else
    {
        $ViewPage = 1;
    }
}
else if($Next == true)
{
    $CurrPage += 1;
    if($CurrPage <= $MaxPage)
    {
        $ViewPage = $CurrPage;
    }
    else
    {
        $ViewPage = $MaxPage;
    }
}

$parse = $_Lang;
$parse['mlst_data_rows'] = [];
$parse['mlst_data_page'] = $ViewPage;
$parse['mlst_data_pagemax'] = $MaxPage;
$parse['mlst_data_sele'] = $Selected;

$parse['mlst_data_types']  = '<option value="0"'.(($Selected == '0') ? ' SELECTED' : '') .">{$_Lang['mlst_mess_typ__0']}</option>";
$parse['mlst_data_types'] .= '<option value="1"'.(($Selected == '1') ? ' SELECTED' : '') .">{$_Lang['mlst_mess_typ__1']}</option>";
$parse['mlst_data_types'] .= '<option value="2"'.(($Selected == '2') ? ' SELECTED' : '') .">{$_Lang['mlst_mess_typ__2']}</option>";
$parse['mlst_data_types'] .= '<option value="3"'.(($Selected == '3') ? ' SELECTED' : '') .">{$_Lang['mlst_mess_typ__3']}</option>";
$parse['mlst_data_types'] .= '<option value="4"'.(($Selected == '4') ? ' SELECTED' : '') .">{$_Lang['mlst_mess_typ__4']}</option>";
$parse['mlst_data_types'] .= '<option value="5"'.(($Selected == '5') ? ' SELECTED' : '') .">{$_Lang['mlst_mess_typ__5']}</option>";
$parse['mlst_data_types'] .= '<option value="15"'. (($Selected == '15') ? ' SELECTED' : '') .">{$_Lang['mlst_mess_typ_15']}</option>";
$parse['mlst_data_types'] .= '<option value="50"'. (($Selected == '50') ? ' SELECTED' : '') .">{$_Lang['mlst_mess_typ_50']}</option>";
$parse['mlst_data_types'] .= '<option value="70"'. (($Selected == '70') ? ' SELECTED' : '') .">{$_Lang['mlst_mess_typ_70']}</option>";
$parse['mlst_data_types'] .= '<option value="80"'. (($Selected == '80') ? ' SELECTED' : '') .">{$_Lang['mlst_mess_typ_80']}</option>";
$parse['mlst_data_types'] .= '<option value="100"'. (($Selected == '100') ? ' SELECTED' : '') .">{$_Lang['mlst_mess_typ_100']}</option>";

$parse['_PagesTotalCount'] = $MaxPage;
$parse['_PagesTotalCount_Pretty'] = prettyNumber($MaxPage);
$parse['_PagesCurrent_Pretty'] = prettyNumber($ViewPage);

$parse['tbl_rows'] = '';
$parse['mlst_title'] = $_Lang['mlst_title'];

if($ViewPage < 1)
{
    $ViewPage = 1;
}

$StartRec = (($ViewPage - 1) * $_PerPage);

$GetMessages  = "SELECT {{table}}.*, `users`.`username`, `users`.`authlevel`, `users`.`galaxy`, `users`.`system`, `users`.`planet`, `users2`.`username` as `username2` FROM {{table}} ";
$GetMessages .= "LEFT JOIN {{prefix}}users AS `users2` ON `id_owner` = `users2`.`id` ";
$GetMessages .= "LEFT JOIN {{prefix}}users as `users` ON `id_sender` = `users`.`id` ";
$GetMessages .= $WhereClausures;
$GetMessages .= " ORDER BY {{table}}.`time` DESC, {{table}}.`id` DESC ";
$GetMessages .= "LIMIT {$StartRec}, {$_PerPage};";

$SQLResult_GetMessages = doquery($GetMessages, 'messages');

while($row = $SQLResult_GetMessages->fetch_assoc())
{
    $bloc = array();

    if(!empty($ExcludedUsers))
    {
        if(in_array($row['id_sender'], $ExcludedUsers) OR in_array($row['id_owner'], $ExcludedUsers))
        {
            $parse['mlst_data_rows'][] = parsetemplate($DisallowTpl , $_Lang);
            continue;
        }
    }

    if ($row['id_sender'] == 0) {
        $messageDetails = Messages\Utils\_buildTypedSystemMessageDetails(
            $row,
            [ 'shouldIncludeSimulationForm' => false, ]
        );

        $row['from'] = $messageDetails['from'];
        $row['subject'] = $messageDetails['subject'];
        $row['text'] = $messageDetails['text'];
    } else {
        $messageDetails = Messages\Utils\_buildTypedUserMessageDetails($row, []);

        $row['from'] = (
            $messageDetails['from'] .
            '<br/>[<a href="?uid=' . $row['id_sender'] . '">ID: ' . $row['id_sender'] . '</a>]'
        );
        $row['text'] = (
            !$messageDetails['isCarbonCopy'] ?
                $messageDetails['text'] :
                $row['text']
        );
        $row['subject'] = stripslashes($row['subject']);

        if ($messageDetails['isCarbonCopy']) {
            $carbonCopyOriginalId = $messageDetails['carbonCopyOriginalId'];

            $GetMsgCopies[$carbonCopyOriginalId][] = $row['id'];

            $bloc['mlst_status'][] = '<img src="../images/reply.png" class="tipCopy"/>';
            $bloc['mlst_copyID'] = "<br/>[{$carbonCopyOriginalId}]";
        }
    }

    if($row['read'] != 0)
    {
        $bloc['mlst_status'][] = '<img src="../images/eye.png" class="tipEye"/>';
    }
    if($row['deleted'] != 0)
    {
        $bloc['mlst_status'][] = '<img src="../images/bin.png" class="tipBin"/>';
    }
    $bloc['mlst_id'] = $row['id'];
    if(isset($_POST['msg_id']) && $_POST['msg_id'] == $row['id'])
    {
        $bloc['mlst_rowcolor'] = 'lime';
    }
    $bloc['mlst_from'] = $row['from'];
    $bloc['mlst_to'] = "{$row['username2']}<br/>[<a href=\"?uid={$row['id_owner']}\">ID: {$row['id_owner']}</a>]";
    $bloc['mlst_text'] = $row['text'];
    if(!empty($bloc['mlst_status']))
    {
        $bloc['mlst_status'] = implode('<br/>', $bloc['mlst_status']);
    }
    else
    {
        $bloc['mlst_status'] = '&nbsp;';
    }
    $bloc['mlst_time'] = date('d.m.Y', $row['time']).'<br/>'.date('H:i:s', $row['time']);
    $parse['mlst_data_rows'][$row['id']] = $bloc;
}
if(empty($parse['mlst_data_rows']))
{
    $parse['mlst_data_rows'][] = '<tr><th colspan="6" class="pad5 red">'.$_Lang['Error_NoMsgFound'].'<br/><a href="messagelist.php" class="orange">('.$_Lang['Form_ResetFilters'].')</a></th></tr>';
    $parse['HideSelectedActionRow'] = ' style="display: none;"';
}
else
{
    if(!empty($GetMsgCopies))
    {
        $GetMsgCopiesIDs = implode(', ', array_keys($GetMsgCopies));
        $GetMsgCopiesQuery  = "SELECT `id`, `subject`, `text` FROM {{table}} WHERE `id` IN ({$GetMsgCopiesIDs});";

        $SQLResult_GetCopiedMessageSources = doquery($GetMsgCopiesQuery, 'messages');

        if($SQLResult_GetCopiedMessageSources->num_rows > 0)
        {
            while($GetMsgCopiesRow = $SQLResult_GetCopiedMessageSources->fetch_assoc())
            {
                $GetMsgCopiesData[$GetMsgCopiesRow['id']] = $GetMsgCopiesRow;
            }
        }

        foreach($GetMsgCopies as $CopyID => $ReplaceIDs)
        {
            if(!empty($GetMsgCopiesData[$CopyID]))
            {
                $GetMsgCopiesData[$CopyID]['text'] = Messages\Utils\formatUserMessageContent($GetMsgCopiesData[$CopyID]);

                foreach($ReplaceIDs as $ReplaceID)
                {
                    $parse['mlst_data_rows'][$ReplaceID]['mlst_text'] = $GetMsgCopiesData[$CopyID]['text'];
                    $parse['mlst_data_rows'][$ReplaceID]['mlst_subject'] = $GetMsgCopiesData[$CopyID]['subject'];
                }
            }
            else
            {
                foreach($ReplaceIDs as $ReplaceID)
                {
                    $parse['mlst_data_rows'][$ReplaceID]['mlst_text'] = $_Lang['Err_CopyNotFound'];
                    $parse['mlst_data_rows'][$ReplaceID]['mlst_subject'] = '-';
                }
            }
        }
    }

    $JoinMsgs = '';
    foreach($parse['mlst_data_rows'] as $RowData)
    {
        if (is_array($RowData)) {
            $JoinMsgs .= parsetemplate($RowsTpl, $RowData);
        } else {
            $JoinMsgs .= $RowData;
        }
    }

    $parse['mlst_data_rows'] = $JoinMsgs;
}

$parse['selected_user_id'] = $_POST['user_id'];
$display = parsetemplate($BodyTpl , $parse);

display ($display, $_Lang['mlst_title'], false, '', true);

?>
