<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

include($_EnginePath . 'modules/messages/_includes.php');

use UniEngine\Engine\Modules\Messages;

loggedCheck();

includeLang('messages');
includeLang('messageSystem');
includeLang('spyReport');
includeLang('FleetMission_MissileAttack');

$parse = &$_Lang;
$parse['Insert_Styles'] = '';
$parse['Insert_CategoryList'] = '';
$CreateSimForms = '';
$SetTitle = $_Lang['mess_pagetitle_read'];

$Now = time();

$MessageType = array(100, 0, 1, 2, 3, 4, 5, 15, 50, 70, 80);
foreach($MessageType as $TypeID)
{
    $MsgCounter['total'][$TypeID] = 0;
    $MsgCounter['threaded'][$TypeID] = 0;
    $MsgCounter['unread'][$TypeID] = 0;
}
$_CanBeThreaded = array(1, 80, 100);
$TitleColor = array
(
    0 => '#FFFF00', 1 => '#FF6699', 2 => '#FF3300', 3 => '#FF9900', 4 => '#9540BF', 5 => '#009933',
    15 => '#6661FF', 80 => 'white', 50 => 'skyblue', 70 => '#75F121', 100 => '#ABABAB'
);
$MsgColors = array(0 => 'c0', 1 => 'c1', 2 => 'c2', 3 => 'c3', 4 => 'c4', 5 => 'c5', 15 => 'c15', 80 => 'c80', 50 => 'c50', 70 => 'c70', 100 => 'c100');
$SimTechs = array(109, 110, 111, 120, 121, 122, 125, 126, 199);
$SimTechsRep = array(109 => 1, 110 => 2, 111 => 3, 120 => 4, 121 => 5, 122 => 6, 125 => 7, 126 => 8, 199 => 9);
$_MaxLength_Subject = 100;
$_MaxLength_Text = 5000;

$_UseThreads = ($_User['settings_UseMsgThreads'] == 1 ? true : false);

$_ThisCategory = (isset($_GET['messcat']) ? intval($_GET['messcat']) : 0);
$DeleteWhat = (isset($_POST['deletemessages']) ? $_POST['deletemessages'] : '');
if(!empty($DeleteWhat) || (isset($_POST['delid']) && $_POST['delid'] > 0))
{
    $_GET['mode'] = 'delete';
    if(isset($_POST['delid']) && $_POST['delid'] > 0)
    {
        $_POST['deletemessages'] = '';
    }
}
$CreatedForms = 0;

if(!isset($_GET['mode']))
{
    $_GET['mode'] = '';
}
switch($_GET['mode'])
{
    case 'write':
        // Message Sending System
        $SetTitle = $_Lang['mess_pagetitle_send'];
        $AllowSend = false;

        $FormData['username'] = null;
        $FormData['uid'] = null;
        $FormData['replyto'] = 0;
        $FormData['subject'] = null;
        $FormData['text'] = null;
        $FormData['lock_username'] = false;
        $FormData['lock_subject'] = false;

        if(empty($_POST['subject']) AND !empty($_GET['subject']))
        {
            $_POST['subject'] = $_GET['subject'];
        }
        if(empty($_POST['text']) AND !empty($_GET['insert']))
        {
            $_POST['text'] = $_GET['insert'];
        }

        if(!empty($_GET['replyto']) OR !empty($_POST['replyto']))
        {
            if(!empty($_POST['replyto']))
            {
                $ReplyID = round($_POST['replyto']);
            }
            else
            {
                $ReplyID = round($_GET['replyto']);
            }
            if($ReplyID > 0)
            {
                $GetReplyMsg = '';
                $GetReplyMsg .= "SELECT `m`.`id`, `m`.`Thread_ID`, `m`.`subject`, `m`.`text`, `u`.`id` AS `user_id`, `u`.`username`, `u`.`authlevel` FROM {{table}} AS `m` ";
                $GetReplyMsg .= "LEFT JOIN `{{prefix}}users` AS `u` ON `u`.`id` = IF(`m`.`id_owner` != {$_User['id']}, `m`.`id_owner`, `m`.`id_sender`) ";
                $GetReplyMsg .= "WHERE (`m`.`id` = {$ReplyID} OR `m`.`Thread_ID` = {$ReplyID}) AND (`m`.`id_owner` = {$_User['id']} OR `m`.`id_sender` = {$_User['id']}) AND `deleted` = false LIMIT 1;";

                $ReplyMsg = doquery($GetReplyMsg, 'messages', true);
                if($ReplyMsg['id'] > 0)
                {
                    if(preg_match('/^\{COPY\_MSG\_\#([0-9]{1,}){1}\}$/D', $ReplyMsg['text'], $ThisMatch))
                    {
                        $GetCopyMsg = doquery("SELECT `subject` FROM {{table}} WHERE `id` = {$ThisMatch[1]} LIMIT 1;", 'messages', true);
                        $ReplyMsg['subject'] = $GetCopyMsg['subject'];
                    }

                    $FormData['username'] = $ReplyMsg['username'];
                    $FormData['uid'] = $ReplyMsg['user_id'];
                    $FormData['authlevel'] = $ReplyMsg['authlevel'];
                    $FormData['subject'] = $ReplyMsg['subject'];
                    $FormData['replyto'] = $ReplyID;
                    $FormData['Thread_Started'] = ($ReplyMsg['Thread_ID'] > 0 ? true : false);
                    if($FormData['Thread_Started'] === false)
                    {
                        $CreateReCounter = 1;
                    }
                    else
                    {
                        $GetThreadCount = doquery("SELECT COUNT(*) AS `Count` FROM {{table}} WHERE `Thread_ID` = {$ReplyID};", 'messages', true);
                        $CreateReCounter = $GetThreadCount['Count'];
                    }
                    $FormData['lock_username'] = true;
                    $FormData['lock_subject'] = true;
                    $AllowSend = true;

                    $FormData['subject'] = preg_replace('#'.$_Lang['mess_answer_prefix'].'\[[0-9]{1,}\]\: #si', '', $FormData['subject']);
                    $FormData['subject'] = $_Lang['mess_answer_prefix'].'['.$CreateReCounter.']: '.$FormData['subject'];
                }
                else
                {
                    $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_CantReply']);
                }
            }
            else
            {
                $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_CantReply']);
            }
        }
        else
        {
            if(!empty($_GET['uid']) OR !empty($_POST['uid']))
            {
                if(!empty($_POST['uid']))
                {
                    $UserID = intval($_POST['uid']);
                    $UIDFromPost = true;
                }
                else
                {
                    $UserID = intval($_GET['uid']);
                    $UIDFromPost = false;
                }
                if($UserID > 0)
                {
                    $CheckUser = doquery("SELECT `id`, `username`, `authlevel` FROM {{table}} WHERE `id` = {$UserID} LIMIT 1;", 'users', true);
                    if($CheckUser['id'] == $UserID)
                    {
                        $FormData['username'] = $CheckUser['username'];
                        $FormData['uid'] = $UserID;
                        $FormData['authlevel'] = $CheckUser['authlevel'];
                        if($UIDFromPost === false)
                        {
                            $FormData['lock_username'] = true;
                        }
                        $AllowSend = true;
                    }
                    else
                    {
                        $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_UserNoExist']);
                    }
                }
                else
                {
                    $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_BadUserID']);
                }
            }

            if(!empty($_POST['uname']) && $_POST['uname'] != $FormData['username'])
            {
                $FormData['username'] = '';
                $FormData['uid'] = '';
                $FormData['authlevel'] = '';
                $AllowSend = false;

                $UserName = trim($_POST['uname']);
                if(preg_match(REGEXP_USERNAME_ABSOLUTE, $UserName))
                {
                    $CheckUser = doquery("SELECT `id`, `authlevel` FROM {{table}} WHERE `username` = '{$UserName}' LIMIT 1;", 'users', true);
                    if($CheckUser['id'] > 0)
                    {
                        $FormData['username'] = $UserName;
                        $FormData['uid'] = $CheckUser['id'];
                        $FormData['authlevel'] = $CheckUser['authlevel'];
                        $AllowSend = true;
                    }
                    else
                    {
                        $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_UserNoExist']);
                    }
                }
                else
                {
                    $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_BadUserName']);
                }
            }
        }

        if($FormData['uid'] > 0 && $FormData['uid'] == $_User['id'])
        {
            $FormData['username'] = '';
            $FormData['uid'] = '';
            $FormData['lock_username'] = false;
            $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_CantWriteToYourself']);
            $AllowSend = false;
        }
        $FormData['type'] = 1;
        if(isset($_POST['send_as_admin_msg']) && $_POST['send_as_admin_msg'] == 'on')
        {
            if(!CheckAuth('supportadmin'))
            {
                $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_CantUseAdminMsg']);
                $AllowSend = false;
            }
            else
            {
                $FormData['type'] = 80;
                $parse['FormInsert_checkSendAsAdmin'] = 'checked';
            }
        }

        $_POST['text'] = (isset($_POST['text']) ? stripslashes(trim($_POST['text'])) : '');
        $_POST['subject'] = (isset($_POST['subject']) ? stripslashes(trim($_POST['subject'])) : '');
        if(get_magic_quotes_gpc())
        {
            $_POST['text'] = stripslashes($_POST['text']);
            $_POST['subject'] = stripslashes($_POST['subject']);
        }

        if(empty($FormData['subject']))
        {
            $FormData['subject'] = substr(strip_tags($_POST['subject']), 0, $_MaxLength_Subject);
        }
        $FormData['text'] = substr(strip_tags($_POST['text']), 0, $_MaxLength_Text);

        if(isset($_POST['send_msg']))
        {
            if($FormData['uid'] == 0 && empty($MsgBox))
            {
                $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_NoUserID']);
                $AllowSend = false;
            }

            if(empty($FormData['subject']))
            {
                $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_SubjectEmpty']);
                $AllowSend = false;
            }
            if(empty($FormData['text']))
            {
                $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_TextEmpty']);
                $AllowSend = false;
            }
            else
            {
                include($_EnginePath.'includes/functions/FilterMessages.php');
                if(FilterMessages($_POST['text'], 1))
                {
                    $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_TextSPAM']);
                    $AllowSend = false;
                }
            }

            if($FormData['uid'] > 0 && !CheckAuth('user', AUTHCHECK_HIGHER) AND !CheckAuth('user', AUTHCHECK_HIGHER, $FormData))
            {
                $Query_IgnoreSystem = '';
                $Query_IgnoreSystem .= "SELECT `OwnerID` FROM {{table}} WHERE ";
                $Query_IgnoreSystem .= "(`OwnerID` = {$_User['id']} AND `IgnoredID` = {$FormData['uid']}) OR ";
                $Query_IgnoreSystem .= "(`OwnerID` = {$FormData['uid']} AND `IgnoredID` = {$_User['id']}) ";
                $Query_IgnoreSystem .= "LIMIT 2; -- messages.php|IgnoreSystem";

                $Result_IgnoreSystem = doquery($Query_IgnoreSystem, 'ignoresystem');

                if($Result_IgnoreSystem->num_rows > 0)
                {
                    $AllowSend = false;
                    while($IgnoreData = $Result_IgnoreSystem->fetch_assoc())
                    {
                        if($IgnoreData['OwnerID'] == $_User['id'])
                        {
                            $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_IgnoreYour']);
                        }
                        else
                        {
                            $MsgBox[] = array('color' => 'red', 'text' => $_Lang['Errors_IgnoreHis']);
                        }
                    }
                }
            }

            if($AllowSend === true)
            {
                $FormSend['type'] = $FormData['type'];
                $FormSend['subject'] = $FormData['subject'];
                $FormSend['text'] = $FormData['text'];
                $FormSend['uid'] = $FormData['uid'];
                $FormSend['Thread_ID'] = $FormData['replyto'];
                $FormSend['Thread_IsLast'] = ($FormSend['Thread_ID'] > 0 ? 1 : 0);

                if($FormSend['Thread_ID'] > 0)
                {
                    if($FormData['Thread_Started'] === false)
                    {
                        doquery("UPDATE {{table}} SET `Thread_ID` = `id`, `Thread_IsLast` = 1 WHERE `id` = {$FormSend['Thread_ID']} LIMIT 1;", 'messages');
                    }
                    else
                    {
                        doquery("UPDATE {{table}} SET `Thread_IsLast` = 0 WHERE `Thread_ID` = {$FormSend['Thread_ID']} AND `id_owner` = {$FormSend['uid']};", 'messages');
                    }
                }

                Cache_Message($FormSend['uid'], $_User['id'], $Now, $FormSend['type'], '', $FormSend['subject'], $FormSend['text'], $FormSend['Thread_ID'], $FormSend['Thread_IsLast']);

                if(preg_match('#'.$_Lang['mess_answer_prefix'].'\[([0-9]{1,})\]\: #si', $FormData['subject'], $ThisMatch))
                {
                    $FormData['subject'] = preg_replace('#'.$_Lang['mess_answer_prefix'].'\[[0-9]{1,}\]\: #si', $_Lang['mess_answer_prefix'].'['.($ThisMatch[1] + 1).']: ', $FormData['subject']);
                }
                $MsgBox[] = array('color' => 'lime', 'text' => $_Lang['Info_MsgSend']);
            }
        }

        $parse['FormInsert_username'] = $FormData['username'];
        $parse['FormInsert_uid'] = $FormData['uid'];
        $parse['FormInsert_replyto'] = $FormData['replyto'];
        $parse['FormInsert_subject'] = $FormData['subject'];
        $parse['FormInsert_text'] = $FormData['text'];
        if($FormData['lock_username'] === true)
        {
            $parse['FormInsert_LockUsername'] = 'disabled';
        }
        if($FormData['lock_subject'] === true)
        {
            $parse['FormInsert_LockSubject'] = 'disabled';
        }

        // Let's handle System Messages
        if(!empty($MsgBox))
        {
            foreach($MsgBox as $MsgData)
            {
                $MsgBoxData[] = "<span class=\"{$MsgData['color']}\">{$MsgData['text']}</span>";
            }
            $parse['Insert_MsgBoxText'] = implode('<br/>', $MsgBoxData);
        }
        else
        {
            $parse['Insert_HideMsgBox'] = 'inv';
            $parse['Insert_MsgBoxText'] = '&nbsp;';
        }

        // Now let's show Form to User
        if(!CheckAuth('user', AUTHCHECK_HIGHER))
        {
            $parse['FormInsert_displaySendAsAdmin'] = 'display: none;';
        }
        $_Lang['FormInsert_MaxSigns'] = $_MaxLength_Text;
        if($_GameConfig['enable_bbcode'] == 1)
        {
            $page = parsetemplate(gettemplate('messages_pm_form_bb'), $parse);
        }
        else
        {
            $page = parsetemplate(gettemplate('messages_pm_form'), $parse);
        }
        // It's done!
    break;

    case 'delete':
        // Delete or do other things with Selected/Nonselected/Add Messages

        $_ThisCategory = intval($_POST['category']);
        $DeleteWhat = $_POST['deletemessages'];
        $ActionBreak = false;
        if(in_array($DeleteWhat, array('deleteall', 'deleteallcat', 'setallread', 'setcatread')))
        {
            $TimeStamp = round($_POST['time']);
            if($TimeStamp <= 0 OR $TimeStamp > $Now)
            {
                $DelNotifs[] = $_Lang['Delete_BadTimestamp'];
                $ActionBreak = true;
            }
        }

        if($ActionBreak !== true)
        {
            if ($DeleteWhat == 'deleteall') {
                $cmdResult = Messages\Commands\batchDeleteMessagesOlderThan([
                    'userID' => $_User['id'],
                    'untilTimestamp' => $TimeStamp,
                ]);

                if ($cmdResult['deletedMessagesCount'] > 0) {
                    $DelMsgs[] = $_Lang['Delete_AllMsgsDeleted'];
                } else {
                    $DelNotifs[] = $_Lang['Delete_NoMsgsToDelete'];
                }
            } elseif ($DeleteWhat == 'deleteallcat') {
                if (
                    in_array($_ThisCategory, $MessageType) &&
                    $_ThisCategory != 100 &&
                    $_ThisCategory != 80
                ) {
                    $cmdResult = Messages\Commands\batchDeleteMessagesOlderThan([
                        'userID' => $_User['id'],
                        'messageTypeID' => $_ThisCategory,
                        'untilTimestamp' => $TimeStamp,
                    ]);

                    if ($cmdResult['deletedMessagesCount'] > 0) {
                        $DelMsgs[] = $_Lang['Delete_AllCatMsgsDeleted'];
                    } else {
                        $DelNotifs[] = $_Lang['Delete_NoMsgsToDelete'];
                    }
                } else {
                    $DelNotifs[] = (
                        $_ThisCategory == 80 ?
                        $_Lang['Delete_CannotDeleteAdminMsgsAtOnce'] :
                        $_Lang['Delete_BadCatSelected']
                    );
                }
            } else if($DeleteWhat == 'deletemarked') {
                // User is Deleting all Marked messages
                $DeleteIDs = false;
                if (!empty($_POST['del_all'])) {
                    preg_match_all('#([0-9]{1,})#si', $_POST['del_all'], $DeleteIDs);
                    $DeleteIDs = $DeleteIDs[0];
                } else {
                    foreach($_POST as $Message => $Answer)
                    {
                        if(preg_match("/^del([0-9]{1,})$/D", $Message, $MsgMatch) AND $Answer == 'on')
                        {
                            $DeleteIDs[] = $MsgMatch[1];
                        }
                    }
                }

                if ($DeleteIDs !== FALSE) {
                    $cmdResult = Messages\Commands\batchDeleteMessagesByID([
                        'messageIDs' => $DeleteIDs,
                        'userID' => $_User['id'],
                    ]);

                    if ($cmdResult['deletedMessagesCount'] > 0) {
                        $DelMsgs[] = $_Lang['Delete_SelectedDeleted'];
                    } else {
                        $DelNotifs[] = $_Lang['Delete_BadSelections'];
                    }
                } else {
                    $DelNotifs[] = $_Lang['Delete_NoMsgsSelected'];
                }
            }
            else if($DeleteWhat == 'deleteunmarked')
            {
                // User is deleting all Unmarked messages
                $DeleteIDs = false;
                if (!empty($_POST['sm_all'])) {
                    preg_match_all('#([0-9]{1,})#si', $_POST['sm_all'], $DeleteIDs);
                    $DeleteIDs = $DeleteIDs[0];
                } else {
                    foreach($_POST as $Message => $Answer)
                    {
                        if(preg_match("/^sm([0-9]{1,})$/D", $Message, $MsgMatch))
                        {
                            if($_POST[('del'.$MsgMatch[1])] != 'on')
                            {
                                $DeleteIDs[] = $MsgMatch[1];
                            }
                        }
                    }
                }

                if ($DeleteIDs !== FALSE) {
                    $cmdResult = Messages\Commands\batchDeleteMessagesByID([
                        'messageIDs' => $DeleteIDs,
                        'userID' => $_User['id'],
                    ]);

                    if ($cmdResult['deletedMessagesCount'] > 0) {
                        $DelMsgs[] = $_Lang['Delete_UnselectedDeleted'];
                    } else {
                        $DelNotifs[] = $_Lang['Delete_BadSelections'];
                    }
                } else {
                    $DelNotifs[] = $_Lang['Delete_NoMsgsUnselected'];
                }
            } else if($DeleteWhat == 'setallread') {
                $cmdResult = Messages\Commands\batchMarkMessagesAsRead([
                    'userID' => $_User['id'],
                    'untilTimestamp' => $TimeStamp,
                ]);

                if ($cmdResult['markedMessagesCount'] > 0) {
                    $DelMsgs[] = $_Lang['Delete_AllMsgsRead'];
                } else {
                    $DelNotifs[] = $_Lang['Delete_NoMsgsToRead'];
                }
            } else if($DeleteWhat == 'setcatread') {
                if (
                    in_array($_ThisCategory, $MessageType) &&
                    $_ThisCategory != 100 &&
                    $_ThisCategory != 80
                ) {
                    $cmdResult = Messages\Commands\batchMarkMessagesAsRead([
                        'userID' => $_User['id'],
                        'messageTypeID' => $_ThisCategory,
                        'untilTimestamp' => $TimeStamp,
                    ]);

                    if ($cmdResult['markedMessagesCount'] > 0) {
                        $DelMsgs[] = $_Lang['Delete_CatMsgsRead'];
                    } else {
                        $DelNotifs[] = $_Lang['Delete_NoMsgsToRead'];
                    }
                } else {
                    $DelNotifs[] = (
                        $_ThisCategory == 80 ?
                        $_Lang['Delete_CannotReadAdminMsgsAtOnce'] :
                        $_Lang['Delete_BadCatSelected']
                    );
                }
            } else {
                if (!empty($_POST['delid'])) {
                    $DeleteID = round(floatval($_POST['delid']));

                    $cmdResult = Messages\Commands\batchDeleteMessagesByID([
                        'messageIDs' => [ $DeleteID ],
                        'userID' => $_User['id'],
                        'ignoreExcludedMessageTypesRestriction' => true,
                    ]);

                    if ($cmdResult['deletedMessagesCount'] > 0) {
                        $DelMsgs[] = $_Lang['Delete_MsgDeleted'];
                    } else {
                        $DelNotifs[] = $_Lang['Delete_MsgNoExist'];
                    }
                }
            }
        }

        if(!empty($DelMsgs))
        {
            foreach($DelMsgs as $Data)
            {
                $MsgBoxData[] = "<span class=\"lime\">{$Data}</span>";
            }
        }
        if(!empty($DelNotifs))
        {
            foreach($DelNotifs as $Data)
            {
                $MsgBoxData[] = "<span class=\"red\">{$Data}</span>";
            }
        }
        // Don't break here to allow deleting while showing...

    case 'show':
        // Show Messages

        if($_User['settings_msgperpage'] <= 0)
        {
            $_PerPage = 20;
        }
        else
        {
            $_PerPage = $_User['settings_msgperpage'];
        }
        if(isset($_POST['page']) && !empty($_POST['page']))
        {
            $_ThisPage = intval($_POST['page']);
        }
        else
        {
            $_ThisPage = isset($_GET['page']) ? intval($_GET['page']) : 0;
        }

        $PageTPL = gettemplate('message_list');
        $parse['InsertTimestamp'] = $Now;

        if($_User['settings_spyexpand'] == 0)
        {
            $parse['SpyExpanded'] = 'true';
            $parse['SpyDisplay'] = '';
        }
        else
        {
            $parse['SpyExpanded'] = 'false';
            $parse['SpyDisplay'] = 'display: none;';
        }

        if($_ThisPage > 1)
        {
            $Start = ($_ThisPage - 1) * $_PerPage;
        }
        else
        {
            $_ThisPage = 1;
            $Start = 0;
        }

        if(!in_array($_ThisCategory, $MessageType))
        {
            $_ThisCategory = 100;
        }

        $parse['SelectedCat'] = $_Lang['type'][$_ThisCategory];
        if($_ThisCategory == 100)
        {
            $parse['show_delete_all_cat'] = 'style="display: none;"';
            $GetMsgCountType = "`type` != 80";
            $parse['Hide_NoActions'] = ' style="display: none"';
        }
        else
        {
            $GetMsgCountType = "`type` = {$_ThisCategory}";
            if($_ThisCategory == 80)
            {
                $parse['Hide_AdminMsg'] = ' style="display: none"';
            }
            else
            {
                $parse['Hide_NoActions'] = ' style="display: none"';
            }
        }
        if($_UseThreads)
        {
            $GetMsgCount = doquery("SELECT COUNT(`id`) as `count` FROM {{table}} WHERE `deleted` = false AND `id_owner` = {$_User['id']} AND (`type` NOT IN (".implode(', ', $_CanBeThreaded).") OR `Thread_ID` = 0 OR `Thread_IsLast` = 1) AND {$GetMsgCountType};", 'messages', true);
        }
        else
        {
            $GetMsgCount = doquery("SELECT COUNT(`id`) as `count` FROM {{table}} WHERE `deleted` = false AND `id_owner` = {$_User['id']} AND {$GetMsgCountType};", 'messages', true);
        }
        $MsgCount = $GetMsgCount['count'];

        if($Start >= $MsgCount)
        {
            $_ThisPage = ceil($MsgCount/$_PerPage);
            $Start = ($_ThisPage - 1) * $_PerPage;
        }

        $Pagination = '';
        if($MsgCount > $_PerPage)
        {
            include_once($_EnginePath.'includes/functions/Pagination.php');
            $Pagin = CreatePaginationArray($MsgCount, $_PerPage, $_ThisPage, 7);
            $PaginationTPL = "<a class=\"pagebut {\$Classes}\" href=\"messages.php?mode=show&amp;messcat={$_ThisCategory}&amp;page={\$Value}\">{\$ShowValue}</a>";
            $PaginationViewOpt = array('CurrentPage_Classes' => 'orange', 'Breaker_View' => '...');
            $CreatePagination = implode(' ', ParsePaginationArray($Pagin, $_ThisPage, $PaginationTPL, $PaginationViewOpt));

            $Pagination = "<tr><th style=\"padding: 7px;\">{$_Lang['Pages']}</th><th>{$CreatePagination}</th></tr>";
        }

        $parse['ThisPage'] = $_ThisPage;
        $parse['MessCategory'] = $_ThisCategory;
        $parse['MessCategoryColor'] = $TitleColor[$_ThisCategory];
        $parse['Pagination'] = $Pagination;
        $page = '';

        // Let's show Messages!
        if($MsgCount > 0)
        {
            if($_ThisCategory == 100)
            {
                $GetMsgsType = '!= 80';
            }
            else
            {
                $GetMsgsType = "= {$_ThisCategory}";
            }
            $Query_GetMessages = '';
            $Query_GetMessages .= "SELECT `m`.*, `u`.`username`, `u`.`authlevel` FROM {{table}} AS `m` ";
            $Query_GetMessages .= "LEFT JOIN `{{prefix}}users` AS `u` ON `u`.`id` = `m`.`id_sender` ";
            if($_UseThreads)
            {
                $Query_GetMessages .= "WHERE `m`.`deleted` = false AND `m`.`id_owner` = {$_User['id']} AND (`type` NOT IN (".implode(', ', $_CanBeThreaded).") OR `m`.`Thread_ID` = 0 OR `m`.`Thread_IsLast` = 1) AND `m`.`type` {$GetMsgsType} ";
            }
            else
            {
                $Query_GetMessages .= "WHERE `m`.`deleted` = false AND `m`.`id_owner` = {$_User['id']} AND `m`.`type` {$GetMsgsType} ";
            }
            $Query_GetMessages .= "ORDER BY `m`.`time` DESC, `m`.`id` DESC LIMIT {$Start}, {$_PerPage};";

            $SQLResult_GetMessages = doquery($Query_GetMessages, 'messages');

            if($SQLResult_GetMessages->num_rows > 0)
            {
                if($_GameConfig['enable_bbcode'] == 1)
                {
                    include($_EnginePath.'includes/functions/BBcodeFunction.php');
                }

                $ReadIDs = false;
                $Messages = array();
                $CheckThreads = array();

                while($CurMess = $SQLResult_GetMessages->fetch_assoc())
                {
                    $MsgCache[] = $CurMess;
                    if($_UseThreads AND $CurMess['Thread_ID'] > 0 AND in_array($CurMess['type'], $_CanBeThreaded))
                    {
                        $CheckThreads[] = $CurMess['Thread_ID'];
                        $CheckThreadsExclude[] = $CurMess['id'];
                        $ThreadMap[$CurMess['Thread_ID']] = $CurMess['id'];
                    }
                }

                if(!empty($CheckThreads))
                {
                    $ThreadsIDs = implode(', ', $CheckThreads);
                    $ExcludeIDs = implode(', ', $CheckThreadsExclude);
                    $Query_GetThreaded = '';
                    $Query_GetThreaded .= "SELECT `m`.*, `u`.`username`, `u`.`authlevel` FROM {{table}} AS `m` ";
                    $Query_GetThreaded .= "LEFT JOIN `{{prefix}}users` AS `u` ON `u`.`id` = `m`.`id_sender` ";
                    $Query_GetThreaded .= "WHERE `m`.`deleted` = false AND `m`.`id_owner` = {$_User['id']} AND `read` = false AND `m`.`Thread_ID` IN ({$ThreadsIDs}) AND `m`.`id` NOT IN ({$ExcludeIDs}) ";
                    $Query_GetThreaded .= "ORDER BY `m`.`id` DESC;";

                    $SQLResult_GetThreadedMessages = doquery($Query_GetThreaded, 'messages');

                    if($SQLResult_GetThreadedMessages->num_rows > 0)
                    {
                        while($CurMess = $SQLResult_GetThreadedMessages->fetch_assoc())
                        {
                            $CurMess['isAdditional'] = true;
                            $MsgCache[] = $CurMess;
                        }
                    }

                    foreach($CheckThreads as $ThreadID)
                    {
                        $Query_ThreadsLengthWhere[] = "(`Thread_ID` = {$ThreadID} AND `id` <= {$ThreadMap[$ThreadID]})";
                    }

                    $Query_ThreadsLengthWhere = implode(' OR ', $Query_ThreadsLengthWhere);
                    $Query_ThreadsLength = "SELECT `Thread_ID`, COUNT(*) AS `Count` FROM {{table}} WHERE {$Query_ThreadsLengthWhere} AND (`id_sender` = {$_User['id']} OR `deleted` = false) GROUP BY `Thread_ID`;";

                    $GetThreadsLength = doquery($Query_ThreadsLength, 'messages');

                    if($GetThreadsLength->num_rows > 0)
                    {
                        while($ThisThread = $GetThreadsLength->fetch_assoc())
                        {
                            $ThreadsLength[$ThisThread['Thread_ID']] = $ThisThread['Count'];
                        }
                    }
                }

                foreach($MsgCache as $MsgIndex => $CurMess)
                {
                    $parseMSG = array();
                    if($CurMess['read'] == false)
                    {
                        $ReadIDs[] = $CurMess['id'];
                    }

                    if($CurMess['id_sender'] == 0)
                    {
                        // Message sent by System
                        $MsgArray = json_decode($CurMess['text'], true);
                        $CurMess['from'] = $_Lang['msg_const']['senders']['system'][$CurMess['from']];
                        $CurMess['subject'] = $_Lang['msg_const']['subjects'][$CurMess['subject']];
                        if(empty($MsgArray['msg_text']))
                        {
                            // Constant-formated Message
                            if(!empty($MsgArray['msg_id']))
                            {
                                $CurMess['text'] = vsprintf($_Lang['msg_const']['msgs'][$MsgArray['msg_id']], $MsgArray['args']);
                            }
                            else
                            {
                                $CurMess['text'] = sprintf($_Lang['msg_const']['msgs']['err2'], $CurMess['id']);
                            }
                        }
                        else
                        {
                            // NonConstant Message (eg. SpyReport)
                            if((array)$MsgArray['msg_text'] === $MsgArray['msg_text'])
                            {
                                if(!empty($MsgArray['sim']))
                                {
                                    $CreatedForms += 1;
                                    $Temp = explode(';', $MsgArray['sim']);
                                    foreach($Temp as $Data)
                                    {
                                        $Data = explode(',', $Data);
                                        if(!empty($Data[0]))
                                        {
                                            if(in_array($Data[0], $SimTechs))
                                            {
                                                $MsgArray['simData']['tech'][$SimTechsRep[$Data[0]]] = $Data[1];
                                            }
                                            else
                                            {
                                                $MsgArray['simData']['ships'][$Data[0]] = $Data[1];
                                            }
                                        }
                                    }
                                    $CreateSimForms .= sprintf($_Lang['msg_const']['sim']['form'], $CreatedForms, json_encode($MsgArray['simData']));

                                    $_Lang['GoToSimButton'] = sprintf($_Lang['msg_const']['sim']['button'], 'sim_'.$CreatedForms);
                                }
                                $CurMess['text'] = implode('', innerReplace(multidim2onedim($MsgArray['msg_text']), $_Lang));
                            }
                            else
                            {
                                $CurMess['text'] = sprintf($_Lang['msg_const']['msgs']['err'], $CurMess['id']);
                            }
                        }
                    }
                    else
                    {
                        // Message sent by User
                        $AddFrom = '';
                        if(!empty($CurMess['from']))
                        {
                            $AddFrom = ' '.$CurMess['from'];
                        }
                        $CurMess['from'] = "{$_Lang['msg_const']['senders']['rangs'][GetAuthLabel($CurMess)]} <a href=\"profile.php?uid={$CurMess['id_sender']}\">{$CurMess['username']}</a>{$AddFrom}";

                        if(in_array($CurMess['type'], array(2, 80)) AND preg_match('/^\{COPY\_MSG\_\#([0-9]{1,}){1}\}$/D', $CurMess['text'], $ThisMatch))
                        {
                            $GetMassMsgs[] = $ThisMatch[1];
                            $CopyMsgMap[$ThisMatch[1]][] = $CurMess['id'];
                            $CurMess['text'] = sprintf($_Lang['msg_const']['msgs']['err4'], $CurMess['id']);
                        }
                        else
                        {
                            if($_GameConfig['enable_bbcode'] == 1)
                            {
                                $CurMess['text'] = bbcode(image($CurMess['text']));
                            }
                            $CurMess['text'] = nl2br($CurMess['text']);
                        }

                        if($CurMess['Thread_ID'] > 0)
                        {
                            $parseMSG['isThreaded'] = true;
                            $parseMSG['Thread_ID'] = $CurMess['Thread_ID'];
                        }
                    }

                    $parseMSG['CurrMSG_ID'] = $CurMess['id'];
                    if($CurMess['read'] == false)
                    {
                        $parseMSG['CurrMSG_IsUnread'] = ' class="isNew"';
                    }
                    $parseMSG['CurrMSG_date'] = date('d.m.Y', $CurMess['time']);
                    $parseMSG['CurrMSG_time'] = date('H:i:s', $CurMess['time']);
                    $parseMSG['CurrMSG_from'] = $CurMess['from'];
                    $parseMSG['CurrMSG_subject'] = $CurMess['subject'];
                    $parseMSG['CurrMSG_text'] = $CurMess['text'];
                    if($_ThisCategory == 100)
                    {
                        $parseMSG['CurrMSG_color'] = $MsgColors[$CurMess['type']];
                    }
                    else
                    {
                        $parseMSG['CurrMSG_color'] = '';
                    }
                    if($CurMess['type'] == 80)
                    {
                        $parseMSG['CurrMSG_HideCheckbox'] = 'class="inv"';
                    }
                    $parseMSG['CurrMSG_send'] = sprintf($_Lang['mess_send_date'], $parseMSG['CurrMSG_date'], $parseMSG['CurrMSG_time']);
                    if($CurMess['id_sender'] == 0)
                    {
                        if($CurMess['type'] == 3)
                        {
                            $parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"convert\" id=\"cv_{$MsgArray['args'][0]}\">{$_Lang['mess_convert']}</a></span>";
                        }
                        $parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"report\" href=\"report.php?type=5&amp;eid={$CurMess['id']}\">{$_Lang['mess_report']}</a></span>";
                        $parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"delete\">{$_Lang['mess_delete_single']}</a></span>";
                    }
                    else
                    {
                        if($CurMess['id_sender'] != $_User['id'])
                        {
                            $parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"reply\" href=\"messages.php?mode=write&amp;replyto=".($CurMess['Thread_ID'] > 0 ? $CurMess['Thread_ID'] : $CurMess['id'])."\">{$_Lang['mess_reply']}</a></span>";
                        }
                        if($CurMess['type'] == 2 AND $_User['ally_id'] > 0)
                        {
                            $parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"reply2\" href=\"alliance.php?mode=sendmsg\">{$_Lang['mess_reply_toally']}</a></span>";
                        }

                        if($CurMess['type'] != 80 AND $CurMess['type'] != 2 AND !CheckAuth('user', AUTHCHECK_HIGHER, $CurMess))
                        {
                            $parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"ignore\" href=\"settings.php?ignoreadd={$CurMess['id_sender']}\">{$_Lang['mess_ignore']}</a></span>";
                        }
                        $parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"report2\" href=\"report.php?type=1&amp;uid={$CurMess['id_sender']}&amp;eid={$CurMess['id']}\">{$_Lang['mess_report']}</a></span>";
                        $parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"delete\">{$_Lang['mess_delete_single']}</a></span>";
                    }
                    if(!empty($parseMSG['CurrMSG_buttons']))
                    {
                        $parseMSG['CurrMSG_buttons'] = implode('<span class="lnBr"></span>', $parseMSG['CurrMSG_buttons']);
                    }

                    if(isset($CurMess['isAdditional']) && $CurMess['isAdditional'] === true)
                    {
                        $parseMSG['isAdditional'] = true;
                    }

                    $Messages[$CurMess['id']] = $parseMSG;
                }
                $MsgCache = null;

                if($ReadIDs !== FALSE)
                {
                    $ReadIDs = implode(', ', $ReadIDs);
                    doquery("UPDATE {{table}} SET `read` = true WHERE `id` IN ({$ReadIDs}) AND `deleted` = false;", 'messages');
                }

                if(!empty($GetMassMsgs))
                {
                    if($_ThisCategory == 100)
                    {
                        $SQLResult_GetMassMessages = doquery(
                            "SELECT `id`, `type`, `subject`, `text` FROM {{table}} WHERE `id` IN (".implode(', ', $GetMassMsgs).");",
                            'messages'
                        );
                    }
                    else
                    {
                        $SQLResult_GetMassMessages = doquery(
                            "SELECT `id`, `type`, `subject`, `text`, `from` FROM {{table}} WHERE `id` IN (".implode(', ', $GetMassMsgs).");",
                            'messages'
                        );
                    }

                    while($CopyData = $SQLResult_GetMassMessages->fetch_assoc())
                    {
                        if($CopyData['type'] == 80 OR $CopyData['type'] == 2)
                        {
                            if($_GameConfig['enable_bbcode'] == 1)
                            {
                                $CopyData['text'] = bbcode(image($CopyData['text']));
                            }
                            $CopyData['text'] = nl2br($CopyData['text']);
                            foreach($CopyMsgMap[$CopyData['id']] as $MsgKey)
                            {
                                $Messages[$MsgKey]['CurrMSG_subject'] = $CopyData['subject'];
                                $Messages[$MsgKey]['CurrMSG_text'] = $CopyData['text'];
                                if($CopyData['type'] == 2)
                                {
                                    $Messages[$MsgKey]['CurrMSG_from'] .= ' '.$CopyData['from'];
                                }
                            }
                        }
                        else
                        {
                            foreach($CopyMsgMap[$CopyData['id']] as $MsgKey)
                            {
                                $Messages[$MsgKey]['CurrMSG_subject'] = $_Lang['msg_const']['subjects']['019'];
                                $Messages[$MsgKey]['CurrMSG_text'] = sprintf($_Lang['msg_const']['msgs']['err3'], $CopyData['id']);
                            }
                        }
                    }
                }

                $MsgTPL = gettemplate('message_mailbox_body');
                $ThreadMsgTPL = gettemplate('message_mailbox_threaded');
                foreach($Messages as $ThisKey => $MessageData)
                {
                    if(isset($MessageData['isAdditional']) && $MessageData['isAdditional'] === true)
                    {
                        $ExcludeThreadIDs[$MessageData['Thread_ID']][] = $MessageData['CurrMSG_ID'];
                        $Messages[$ThreadMap[$MessageData['Thread_ID']]]['AddMSG_parsed'][] = parsetemplate($MsgTPL, $MessageData);
                        unset($Messages[$ThisKey]);
                    }
                }
                foreach($Messages as $MessageData)
                {
                    if($_UseThreads && isset($MessageData['Thread_ID']) && $MessageData['Thread_ID'] > 0)
                    {
                        if(!empty($MessageData['AddMSG_parsed']))
                        {
                            $NeededLength = 1 + count($MessageData['AddMSG_parsed']);
                        }
                        else
                        {
                            $NeededLength = 1;
                        }
                        $ThreadParse = array
                        (
                            'Hidden' => 0,
                            'Lang_Expand' => $_Lang['Action_Expand'],
                            'Lang_Collapse' => $_Lang['Action_Collapse'],
                            'Lang_Loading' => $_Lang['Action_ThreadLoading'],
                            'Insert_ThreadID' => $MessageData['Thread_ID'],
                            'Insert_MaxMsgID' => $MessageData['CurrMSG_ID'],
                            'Insert_CatID' => $_ThisCategory,
                            'Insert_ExcludeIDs' => (!empty($ExcludeThreadIDs[$MessageData['Thread_ID']]) ? implode('_', $ExcludeThreadIDs[$MessageData['Thread_ID']]) : ''),
                        );
                        if(!empty($MessageData['AddMSG_parsed']))
                        {
                            $ThreadParse['Insert_Msgs'] = implode('', $MessageData['AddMSG_parsed']);
                        }
                        else
                        {
                            $ThreadParse['Insert_HideParsed'] = 'hide';
                            $ThreadParse['Hidden'] += 1;
                        }
                        if($ThreadsLength[$MessageData['Thread_ID']] <= $NeededLength)
                        {
                            $ThreadParse['Insert_HideExpand'] = 'hide';
                            $ThreadParse['Hidden'] += 1;
                        }
                        else
                        {
                            $ThreadParse['Insert_Count'] = prettyNumber($ThreadsLength[$MessageData['Thread_ID']]);
                        }
                        if($ThreadParse['Hidden'] < 2)
                        {
                            $MessageData['AddMSG_parsed'] = parsetemplate($ThreadMsgTPL, $ThreadParse);
                        }
                    }
                    $AllMessages[] = parsetemplate($MsgTPL, $MessageData);
                }
                $page .= implode('<tr><td class="invBR"></td></tr>', $AllMessages);
                $Messages = null;
                $AllMessages = null;
            }
            else
            {
                $page .= "<tr><th colspan=\"3\">{$_Lang['NoMessages']}</th></tr>";
            }
        }
        else
        {
            $parse['Hide_headers'] = ' class="hide"';
            $page .= "<tr><th colspan=\"3\" class=\"eFrom\">{$_Lang['NoMessages']}</th></tr>";
            $parse['Hide_NoActions'] = ' style="display: none"';
        }

        if(!empty($MsgInfos))
        {
            foreach($MsgInfos as $Data)
            {
                $MsgBoxData[] = "<span class=\"red\">{$Data}</span>";
            }
        }

        $InsertMsgBox = '';
        if(!empty($MsgBoxData))
        {
            $InsertMsgBox = '<tr><th colspan="3" class="pad5">'.implode('<br/>', $MsgBoxData).'</th></tr><tr><td class="inv" style="height: 5px;"></td></tr>';
        }
        $parse['content'] = $page;
        $parse['MsgBox'] = $InsertMsgBox;

        $page = parsetemplate($PageTPL, $parse);

    break;

    default:
        // Show Message Categories
        $SQLResult_GetMessageCategoriesCounters = doquery(
            "SELECT `type`, `read`, `Thread_ID`, `Thread_IsLast`, COUNT(*) AS `Count` FROM {{table}} WHERE `id_owner` = {$_User['id']} AND `deleted` = false GROUP BY `type`, `read`, `Thread_ID`, `Thread_IsLast` ORDER BY `Thread_IsLast` DESC;",
            'messages'
        );

        if($SQLResult_GetMessageCategoriesCounters->num_rows > 0)
        {
            $SeenThreads = array();
            $ThreadMainTypes = array();
            while($Counter = $SQLResult_GetMessageCategoriesCounters->fetch_assoc())
            {
                // Handler TypeCount
                if($_UseThreads AND $Counter['Thread_ID'] > 0)
                {
                    if($Counter['Thread_IsLast'] == 1)
                    {
                        $ThreadMainTypes[$Counter['Thread_ID']] = $Counter['type'];
                    }
                    else
                    {
                        $Counter['type'] = $ThreadMainTypes[$Counter['Thread_ID']];
                    }
                    if(!in_array($Counter['Thread_ID'], $SeenThreads))
                    {
                        $MsgCounter['threaded'][$Counter['type']] += 1;
                    }
                }
                else
                {

                    $MsgCounter['threaded'][$Counter['type']] += $Counter['Count'];
                }
                $MsgCounter['total'][$Counter['type']] += $Counter['Count'];
                if($Counter['read'] == 0)
                {
                    $MsgCounter['unread'][$Counter['type']] += $Counter['Count'];
                }
                // Handle TotalCount
                if($Counter['type'] != 80)
                {
                    $MsgCounter['total'][100] += $Counter['Count'];
                    if($Counter['read'] == 0)
                    {
                        $MsgCounter['unread'][100] += $Counter['Count'];
                    }
                    if($_UseThreads AND $Counter['Thread_ID'] > 0)
                    {
                        if(!in_array($Counter['Thread_ID'], $SeenThreads))
                        {
                            $MsgCounter['threaded'][100] += 1;
                        }
                    }
                    else
                    {
                        $MsgCounter['threaded'][100] += $Counter['Count'];
                    }
                }

                if($_UseThreads AND $Counter['Thread_ID'] > 0)
                {
                    if(!in_array($Counter['Thread_ID'], $SeenThreads))
                    {
                        $SeenThreads[] = $Counter['Thread_ID'];
                    }
                }
            }
        }

        $TPL_CatList_Body = gettemplate('messages_catlist_body');
        $TPL_CatList_Row = gettemplate('messages_catlist_row');

        foreach($MessageType as $TypeID)
        {
            $ThisClass = 'c'.(string)($TypeID + 0);
            $parse['Insert_Styles'] .= ".{$ThisClass} { color: {$TitleColor[$TypeID]}; } ";
            $ThisArray = array
            (
                'Insert_CatID' => $TypeID,
                'Insert_CatClass' => $ThisClass,
                'Insert_CatName' => $_Lang['type'][$TypeID],
                'Insert_CatUnread' => prettyNumber($MsgCounter['unread'][$TypeID]),
                'Insert_CatTotal' => (($_UseThreads AND in_array($TypeID, $_CanBeThreaded) AND $MsgCounter['threaded'][$TypeID] < $MsgCounter['total'][$TypeID]) ? prettyNumber($MsgCounter['threaded'][$TypeID]).'/' : '').prettyNumber($MsgCounter['total'][$TypeID]),
            );
            $parse['Insert_CategoryList'] .= parsetemplate($TPL_CatList_Row, $ThisArray);
        }

        if($_UseThreads)
        {
            $parse['Insert_Hide_ThreadEnabled'] = 'display: none;';
        }
        else
        {
            $parse['Insert_Hide_ThreadDisabled'] = 'display: none;';
        }

        $page = parsetemplate($TPL_CatList_Body, $parse);

        break;
}

display($CreateSimForms.$page, $SetTitle);

?>
