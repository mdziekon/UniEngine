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

if (!isset($_GET['mode'])) {
    $_GET['mode'] = '';
}

switch($_GET['mode']) {
    case 'write':
        // Message Sending System
        $SetTitle = $_Lang['mess_pagetitle_send'];
        $MsgBox = [];

        $addErrorToMsgBox = function ($errorMessage) use (&$MsgBox) {
            $MsgBox[] = [
                'color' => 'red',
                'text' => $errorMessage,
            ];
        };

        if (isset($_POST['send_msg'])) {
            $result = Messages\Input\UserCommands\handleSendMessage(
                $_POST,
                [
                    'senderUser' => &$_User,
                    'subjectMaxLength' => $_MaxLength_Subject,
                    'contentMaxLength' => $_MaxLength_Text,
                    'timestamp' => $Now,
                ]
            );

            $formData = $result['payload']['formData'];

            // Modify template data (regardless of the result)
            $parse['FormInsert_username'] = $formData['recipient']['username'];
            $parse['FormInsert_replyto'] = $formData['replyToId'];
            $parse['FormInsert_text'] = $formData['message']['content'];
            $parse['FormInsert_checkSendAsAdmin'] = (
                $formData['meta']['isSendingAsAdmin'] ?
                    'checked' :
                    ''
            );
            $parse['FormInsert_subject'] = (
                (
                    $formData['meta']['nextSubject'] !== null &&
                    $result['isSuccess']
                ) ?
                    $formData['meta']['nextSubject'] :
                    $formData['message']['subject']
            );

            if (
                $formData['replyToId'] !== null &&
                (
                    $result['isSuccess'] ||
                    !$errors['isReplyToInvalid']
                )
            ) {
                $parse['FormInsert_LockUsername'] = 'disabled';
                $parse['FormInsert_LockSubject'] = 'disabled';
            }

            if (!$result['isSuccess']) {
                $errors = $result['errors'];

                if ($errors['isReplyToInvalid']) {
                    $addErrorToMsgBox($_Lang['Errors_CantReply']);
                }
                if ($errors['isRecipientUsernameInvalid']) {
                    $addErrorToMsgBox($_Lang['Errors_BadUserName']);
                }
                if ($errors['recipientNotFound']) {
                    $addErrorToMsgBox($_Lang['Errors_UserNoExist']);
                }
                if ($errors['hasNoPermissionToChangeType']) {
                    $addErrorToMsgBox($_Lang['Errors_CantUseAdminMsg']);
                }
                if ($errors['isWritingToYourself']) {
                    $addErrorToMsgBox($_Lang['Errors_CantWriteToYourself']);
                }
                if ($errors['isMessageSubjectEmpty']) {
                    $addErrorToMsgBox($_Lang['Errors_SubjectEmpty']);
                }
                if ($errors['isMessageContentEmpty']) {
                    $addErrorToMsgBox($_Lang['Errors_TextEmpty']);
                }
                if ($errors['isMessageContentSpam']) {
                    $addErrorToMsgBox($_Lang['Errors_TextSPAM']);
                }
                if ($errors['isRecipientIgnored']) {
                    $addErrorToMsgBox($_Lang['Errors_IgnoreYour']);
                }
                if ($errors['isSenderIgnored']) {
                    $addErrorToMsgBox($_Lang['Errors_IgnoreHis']);
                }
            }

            if ($result['isSuccess']) {
                $MsgBox[] = [
                    'color' => 'lime',
                    'text' => $_Lang['Info_MsgSend'],
                ];
            }
        } else {
            $isReplying = !empty($_GET['replyto']);

            if ($isReplying) {
                $handleReplyTo = function () use ($_Lang, $_User, &$parse, $addErrorToMsgBox) {
                    $replyId = round($_GET['replyto']);

                    if (!($replyId > 0)) {
                        $addErrorToMsgBox($_Lang['Errors_CantReply']);

                        return;
                    }

                    $replyFormData = Messages\Utils\fetchFormDataForReply([
                        'replyToMessageId' => $replyId,
                        'senderUser' => &$_User,
                    ]);

                    if (!$replyFormData['isSuccess']) {
                        $addErrorToMsgBox($_Lang['Errors_CantReply']);

                        return;
                    }

                    // Modify template data
                    $parse['FormInsert_replyto'] = $replyId;
                    $parse['FormInsert_username'] = $replyFormData['payload']['username'];
                    $parse['FormInsert_subject'] = $replyFormData['payload']['subject'];
                    $parse['FormInsert_LockUsername'] = 'disabled';
                    $parse['FormInsert_LockSubject'] = 'disabled';
                };

                $handleReplyTo();
            }

            if (!$isReplying) {
                $handleQueryParamUserId = function () use ($_Lang, &$parse, $addErrorToMsgBox) {
                    if (empty($_GET['uid'])) {
                        return;
                    }

                    $fetchResult = Messages\Utils\fetchRecipientDataByUserId([
                        'userId' => $_GET['uid'],
                    ]);

                    if (!($fetchResult['isSuccess'])) {
                        if ($fetchResult['errors']['isUserIdInvalid']) {
                            $addErrorToMsgBox($_Lang['Errors_BadUserID']);
                        }
                        if ($fetchResult['errors']['notFound']) {
                            $addErrorToMsgBox($_Lang['Errors_UserNoExist']);
                        }

                        return;
                    }

                    // Modify template data
                    $parse['FormInsert_username'] = $fetchResult['payload']['username'];
                    $parse['FormInsert_uid'] = $fetchResult['payload']['id'];
                    $parse['FormInsert_LockUsername'] = 'disabled';
                };

                $handleQueryParamUserId();
            }

            if (!empty($_GET['subject']) && empty($parse['FormInsert_subject'])) {
                $parse['FormInsert_subject'] = $_GET['subject'];
            }
            if (!empty($_GET['insert'])) {
                $parse['FormInsert_text'] = $_GET['insert'];
            }
        }

        // Handle feedback messages
        if (!empty($MsgBox)) {
            $messagesListElements = [];

            foreach ($MsgBox as $MsgData) {
                $messagesListElements[] = buildDOMElementHTML([
                    'tagName' => 'span',
                    'contentHTML' => $MsgData['text'],
                    'attrs' => [
                        'class' => $MsgData['color'],
                    ]
                ]);
            }
            $parse['Insert_MsgBoxText'] = implode('<br/>', $messagesListElements);
        } else {
            $parse['Insert_HideMsgBox'] = 'inv';
            $parse['Insert_MsgBoxText'] = '&nbsp;';
        }

        // Handle form rendering
        if (!CheckAuth('user', AUTHCHECK_HIGHER)) {
            $parse['FormInsert_displaySendAsAdmin'] = 'display: none;';
        }
        $_Lang['FormInsert_MaxSigns'] = $_MaxLength_Text;

        $tplName = (
            $_GameConfig['enable_bbcode'] == 1 ?
                'messages_pm_form_bb' :
                'messages_pm_form'
        );
        $tplBody = gettemplate($tplName);

        $page = parsetemplate($tplBody, $parse);
    break;

    case 'delete':
        // Delete or do other things with Selected/Nonselected/Add Messages

        $_ThisCategory = intval($_POST['category']);

        $result = Messages\Input\UserCommands\handleBatchAction(
            $_POST,
            [
                'user' => &$_User,
                'timestamp' => $Now,
                'knownMessageTypes' => $MessageType,
            ]
        );

        if ($result['isSuccess'] && !empty($result['messages'])) {
            foreach ($result['messages'] as $messageContent) {
                $MsgBoxData[] = "<span class=\"lime\">{$messageContent}</span>";
            }
        }
        if (!$result['isSuccess'] && !empty($result['errors'])) {
            foreach ($result['errors'] as $messageContent) {
                $MsgBoxData[] = "<span class=\"red\">{$messageContent}</span>";
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

            if ($SQLResult_GetMessages->num_rows > 0) {
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

                    if ($CurMess['id_sender'] == 0) {
                        $messageDetails = Messages\Utils\_buildTypedSystemMessageDetails(
                            $CurMess,
                            [ 'shouldIncludeSimulationForm' => true, ]
                        );

                        $CurMess['from'] = $messageDetails['from'];
                        $CurMess['subject'] = $messageDetails['subject'];
                        $parseMSG['CurrMSG_text'] = $messageDetails['text'];

                        if (isset($messageDetails['addons']['battleSimulation'])) {
                            $battleSimulationDetails = $messageDetails['addons']['battleSimulation'];

                            $CreateSimForms .= $battleSimulationDetails['simulationForm'];
                        }
                    } else {
                        $messageDetails = Messages\Utils\_buildTypedUserMessageDetails($CurMess, []);

                        $CurMess['from'] = $messageDetails['from'];
                        $parseMSG['CurrMSG_text'] = $messageDetails['text'];
                        $parseMSG['Thread_ID'] = $messageDetails['Thread_ID'];

                        if ($messageDetails['isCarbonCopy']) {
                            $carbonCopyOriginalId = $messageDetails['carbonCopyOriginalId'];

                            $GetMassMsgs[] = $carbonCopyOriginalId;
                            $CopyMsgMap[$carbonCopyOriginalId][] = $CurMess['id'];
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
                    $parseMSG['CurrMSG_buttons'] = Messages\Utils\_buildMessageButtons(
                        $CurMess,
                        [
                            'readerUserData' => &$_User,
                        ]
                    );

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
                            $CopyData['text'] = Messages\Utils\formatUserMessageContent($CopyData);

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
