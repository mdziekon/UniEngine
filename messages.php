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

$CreateSimForms = '';
$SetTitle = $_Lang['mess_pagetitle_read'];

$Now = time();

$_MaxLength_Subject = 100;
$_MaxLength_Text = 5000;

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
        $userInput = [
            'pageNo' => 0,
        ];

        if($_User['settings_msgperpage'] <= 0)
        {
            $_PerPage = 20;
        }
        else
        {
            $_PerPage = $_User['settings_msgperpage'];
        }

        if (!empty($_POST['page'])) {
            $userInput['pageNo'] = intval($_POST['page']);
        } else if (!empty($_GET['page'])) {
            $userInput['pageNo'] = intval($_GET['page']);
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

        if (!Messages\Utils\isValidMessageType($_ThisCategory)) {
            $_ThisCategory = 100;
        }

        $parse['SelectedCat'] = $_Lang['type'][$_ThisCategory];

        $isViewingAllMessageCategories = ($_ThisCategory == 100);

        if ($isViewingAllMessageCategories) {
            $parse['show_delete_all_cat'] = 'style="display: none;"';
            $parse['Hide_NoActions'] = ' style="display: none"';
        } else {
            if ($_ThisCategory == 80) {
                $parse['Hide_AdminMsg'] = ' style="display: none"';
            } else {
                $parse['Hide_NoActions'] = ' style="display: none"';
            }
        }

        $MsgCount = Messages\Utils\fetchUserMessagesCount([
            'user' => &$_User,
            'filterMessageType' => (
                !$isViewingAllMessageCategories ?
                    $_ThisCategory :
                    null
            ),
        ]);

        $_ThisPage = Messages\Utils\_normalizeCurrentPageNo([
            'page' => $userInput['pageNo'],
            'pageSize' => $_PerPage,
            'messagesCount' => $MsgCount,
        ]);

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
        $parse['MessCategoryColor'] = Messages\Utils\getMessageTypeColor($_ThisCategory);
        $parse['Pagination'] = $Pagination;
        $page = '';

        // Let's show Messages!
        if ($MsgCount > 0) {
            $SQLResult_GetMessages = Messages\Utils\fetchUserMessages([
                'user' => &$_User,
                'filterMessageType' => (
                    !$isViewingAllMessageCategories ?
                        $_ThisCategory :
                        null
                ),
                'page' => $_ThisPage,
                'pageSize' => $_PerPage,
                'messagesCount' => $MsgCount,
            ]);

            if ($SQLResult_GetMessages->num_rows > 0) {
                $Messages = [];

                $unpackedMessages = Messages\Utils\_unpackFetchedMessages([
                    'getMessagesDbResult' => &$SQLResult_GetMessages,
                    'shouldGatherThreadInfo' => Messages\Utils\_isMessagesThreadViewEnabled([
                        'user' => &$_User,
                    ]),
                ]);

                $MsgCache = $unpackedMessages['messages'];
                $CheckThreads = $unpackedMessages['threads']['ids'];
                $ThreadMap = $unpackedMessages['threads']['oldestMessageIdByThreadId'];

                if (!empty($CheckThreads)) {
                    $SQLResult_GetThreadedMessages = Messages\Utils\_fetchThreadMessages([
                        'threadIds' => $CheckThreads,
                        'alreadyFetchedMessageIds' => $unpackedMessages['threads']['alreadyFetchedMessageIds'],
                        'user' => &$_User,
                    ]);

                    while ($CurMess = $SQLResult_GetThreadedMessages->fetch_assoc()) {
                        $CurMess['isAdditional'] = true;
                        $MsgCache[] = $CurMess;
                    }

                    $SQLResult_GetThreadLengths = Messages\Utils\_fetchThreadLengths([
                        'oldestMessageIdByThreadId' => $unpackedMessages['threads']['oldestMessageIdByThreadId'],
                        'user' => &$_User,
                    ]);

                    while ($ThisThread = $SQLResult_GetThreadLengths->fetch_assoc()) {
                        $ThreadsLength[$ThisThread['Thread_ID']] = $ThisThread['Count'];
                    }
                }

                $messagesCopyIds = Messages\Utils\getMessagesCopyIds($MsgCache);
                $copyOriginalMessages = Messages\Utils\fetchOriginalMessagesForRefSystem([
                    'originalMessageIds' => $messagesCopyIds,
                ]);

                foreach ($MsgCache as $CurMess) {
                    $parseMSG = Messages\Utils\_buildBasicMessageDetails(
                        $CurMess,
                        [
                            'isReadingThread' => false,
                            'displayedCategoryId' => $_ThisCategory,
                            'readerUserData' => &$_User,
                        ]
                    );

                    if (Messages\Utils\isSystemSentMessage($CurMess)) {
                        $messageDetails = Messages\Utils\_buildTypedSystemMessageDetails(
                            $CurMess,
                            [ 'shouldIncludeSimulationForm' => true, ]
                        );

                        $parseMSG['CurrMSG_subject'] = $messageDetails['subject'];
                        $parseMSG['CurrMSG_from'] = $messageDetails['from'];
                        $parseMSG['CurrMSG_text'] = $messageDetails['text'];

                        if (isset($messageDetails['addons']['battleSimulation'])) {
                            $battleSimulationDetails = $messageDetails['addons']['battleSimulation'];

                            $CreateSimForms .= $battleSimulationDetails['simulationForm'];
                        }
                    } else {
                        $messageDetails = Messages\Utils\_buildTypedUserMessageDetails(
                            $CurMess,
                            [
                                'copyOriginalMessagesStorage' => &$copyOriginalMessages,
                            ]
                        );

                        $parseMSG['CurrMSG_subject'] = $messageDetails['subject'];
                        $parseMSG['CurrMSG_from'] = $messageDetails['from'];
                        $parseMSG['CurrMSG_text'] = $messageDetails['text'];
                        $parseMSG['Thread_ID'] = $messageDetails['Thread_ID'];
                    }

                    $Messages[$CurMess['id']] = $parseMSG;
                }

                Messages\Utils\updateMessagesReadStatus(
                    Messages\Utils\getUnreadMessageIds($MsgCache),
                    Messages\Utils\MessageReadStatus::Read
                );

                $MsgCache = null;

                $MsgTPL = gettemplate('message_mailbox_body');
                $ThreadMsgTPL = gettemplate('message_mailbox_threaded');

                foreach ($Messages as $ThisKey => $MessageData) {
                    if (
                        !isset($MessageData['isAdditional']) ||
                        $MessageData['isAdditional'] !== true
                    ) {
                        continue;
                    }

                    $threadId = $MessageData['Thread_ID'];
                    $threadMainMessage = &$Messages[$ThreadMap[$threadId]];

                    $threadMainMessage['inThreadMessages'][] = $MessageData;

                    unset($Messages[$ThisKey]);
                }

                $isThreadViewEnabled = Messages\Utils\_isMessagesThreadViewEnabled([
                    'user' => &$_User,
                ]);

                foreach ($Messages as $MessageData) {
                    if (
                        $isThreadViewEnabled &&
                        isset($MessageData['Thread_ID']) &&
                        $MessageData['Thread_ID'] > 0
                    ) {
                        $threadedMessagesParsingResult = Messages\Utils\parseThreadedMessages([
                            'displayedCategoryId' => $_ThisCategory,
                            'messageDetails' => &$MessageData,
                            'threadLengthsByThreadId' => $ThreadsLength,
                        ]);

                        $MessageData['AddMSG_parsed'] = $threadedMessagesParsingResult['contentHTML'];
                    }

                    $AllMessages[] = parsetemplate($MsgTPL, $MessageData);
                }
                $page .= implode('<tr><td class="invBR"></td></tr>', $AllMessages);
                $Messages = null;
                $AllMessages = null;
            } else {
                $page .= "<tr><th colspan=\"3\">{$_Lang['NoMessages']}</th></tr>";
            }
        } else {
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
        $renderResult = Messages\Screens\CategoriesListView\render([
            'readerUser' => &$_User,
        ]);
        $page = $renderResult['componentHTML'];

        break;
}

display($CreateSimForms.$page, $SetTitle);

?>
