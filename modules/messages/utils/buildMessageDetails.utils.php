<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

use UniEngine\Engine\Modules\Messages;

function _buildBasicMessageDetails($dbMessageData, $params) {
    global $_Lang;

    $isReadingThread = $params['isReadingThread'];
    $displayedCategoryId = $params['displayedCategoryId'];
    $readerUserData = &$params['readerUserData'];

    $isViewingAllMessageCategories = ($displayedCategoryId == 100);
    $isOwnMessageInThread = (
        $isReadingThread &&
        $dbMessageData['id_sender'] == $readerUserData['id']
    );

    return [
        'CurrMSG_ID' => $dbMessageData['id'],
        'CurrMSG_send' => sprintf(
            (
                (
                    $dbMessageData['id_owner'] == $readerUserData['id'] ||
                    !$isOwnMessageInThread
                ) ?
                $_Lang['mess_send_date'] :
                $_Lang['mess_sendbyyou_date']
            ),
            date('d.m.Y', $dbMessageData['time']),
            date('H:i:s', $dbMessageData['time'])
        ),
        'CurrMSG_color' => (
            $isViewingAllMessageCategories ?
                Messages\Utils\formatMessageTypeColorClass($dbMessageData) :
                ''
        ),
        'CurrMSG_IsUnread' => (
            (
                !$dbMessageData['read'] &&
                !$isOwnMessageInThread
            ) ?
                ' class="isNew"' :
                ''
        ),
        'CurrMSG_HideCheckbox' => (
            (
                $dbMessageData['type'] == 80 ||
                $isOwnMessageInThread
            ) ?
                'class="inv"' :
                ''
        ),
        'CurrMSG_buttons' => (
            !$isOwnMessageInThread ?
                Messages\Utils\_buildMessageButtons(
                    $dbMessageData,
                    [
                        'readerUserData' => &$readerUserData,
                    ]
                ) :
                null
        ),
        'isAdditional' => (
            isset($dbMessageData['isAdditional']) &&
            $dbMessageData['isAdditional'] === true
        ),

        // Placeholders required by the template
        'inThreadMessages' => [],
        'AddMSG_parsed' => '',
        'CurrMSG_subject' => null,
        'CurrMSG_from' => null,
        'CurrMSG_text' => null,
        'Thread_ID' => null,
    ];
}

function _buildTypedSystemMessageDetails($dbMessageData, $params) {
    global $_Lang;

    $messageDetails = [
        'from' => null,
        'subject' => null,
        'text' => null,
        'addons' => null,
    ];

    $senderSystemId = $dbMessageData['from'];

    $messageContent = _buildTypedSystemMessageContent($dbMessageData, $params);

    $messageDetails['from'] = (
        !empty($senderSystemId) ?
            $_Lang['msg_const']['senders']['system'][$senderSystemId] :
            '&nbsp;'
    );
    $messageDetails['subject'] = (
        !empty($dbMessageData['subject']) ?
            $_Lang['msg_const']['subjects'][$dbMessageData['subject']] :
            '&nbsp;'
    );

    $messageDetails['text'] = $messageContent['content'];
    $messageDetails['addons'] = $messageContent['addons'];

    return $messageDetails;
}

function _buildTypedSystemMessageContent($dbMessageData, $params = []) {
    global $_Lang;

    $msgPayload = json_decode($dbMessageData['text'], true);
    $shouldIncludeSimulationForm = (
        isset($params['shouldIncludeSimulationForm']) ?
            $params['shouldIncludeSimulationForm'] :
            false
    );

    if (!empty($msgPayload['msg_text'])) {
        // NonConstant Message (eg. SpyReport)
        // TODO: Determine whether this format should be replaced as well by JSON's with raw data.

        if (!is_array($msgPayload['msg_text'])) {
            return [
                'content' => sprintf(
                    $_Lang['msg_const']['msgs']['err'],
                    $dbMessageData['id']
                ),
            ];
        }

        $battleSimulationDetails = (
            (
                !empty($msgPayload['sim']) &&
                $shouldIncludeSimulationForm
            ) ?
                _buildBattleSimulationDetails($msgPayload['sim']) :
                null
        );

        if ($battleSimulationDetails) {
            $_Lang['GoToSimButton'] = $battleSimulationDetails['simulationCTAButton'];
        }

        $content = implode(
            '',
            innerReplace(multidim2onedim($msgPayload['msg_text']), $_Lang)
        );

        return [
            'content' => $content,
            'addons' => [
                'battleSimulation' => $battleSimulationDetails,
            ],
        ];
    }

    // Interpolated message format
    // TODO: Deprecate this format and use JSON's with raw data instead of pre-formatted strings,
    // to prevent problems with messages sent from different-than-destination language selection.

    if (empty($msgPayload['msg_id'])) {
        return [
            'content' => sprintf(
                $_Lang['msg_const']['msgs']['err2'],
                $dbMessageData['id']
            ),
        ];
    }

    $content = vsprintf(
        $_Lang['msg_const']['msgs'][$msgPayload['msg_id']],
        $msgPayload['args']
    );

    return [
        'content' => $content,
    ];
}

function _buildBattleSimulationDetails($simulationDataString) {
    global $_Lang;

    $SimTechs = [
        109,
        110,
        111,
        120,
        121,
        122,
        125,
        126,
        199,
    ];
    $SimTechsRep = [
        109 => 1,
        110 => 2,
        111 => 3,
        120 => 4,
        121 => 5,
        122 => 6,
        125 => 7,
        126 => 8,
        199 => 9,
    ];

    $battleSimulationData = [
        'tech' => [],
        'ships' => [],
    ];

    // TODO: For now, this uid generator is good enough (result doesn't have to be "truly unique"),
    // but consider using something more standard in the future.
    $simulationFormUid = uniqid();
    $simulationElements = explode(';', $simulationDataString);

    foreach ($simulationElements as $simulationElementPacked) {
        $simulationElement = explode(',', $simulationElementPacked);

        if (empty($simulationElement[0])) {
            continue;
        }

        $elementId = $simulationElement[0];
        $elementData = $simulationElement[1];

        if (in_array($elementId, $SimTechs)) {
            $battleSimulationData['tech'][$SimTechsRep[$elementId]] = $elementData;
        } else {
            $battleSimulationData['ships'][$elementId] = $elementData;
        }
    }
    $simulationForm = sprintf(
        $_Lang['msg_const']['sim']['form'],
        $simulationFormUid,
        json_encode($battleSimulationData)
    );
    $simulationCTAButton = sprintf(
        $_Lang['msg_const']['sim']['button'],
        'sim_' . $simulationFormUid
    );

    return [
        'simulationForm' => $simulationForm,
        'simulationCTAButton' => $simulationCTAButton,
    ];
}

function _buildTypedUserMessageDetails($dbMessageData, $params) {
    global $_Lang, $_GameConfig;

    $messageDetails = [
        'from' => null,
        'subject' => null,
        'text' => null,
        'Thread_ID' => null,
        'isCarbonCopy' => null,
        'carbonCopyOriginalId' => null,
    ];

    $copyOriginalMessagesStorage = &$params['copyOriginalMessagesStorage'];

    $messageParsedFrom = Messages\Utils\formatUserMessageSenderLabel($dbMessageData);
    $messageParsedSubject = $dbMessageData['subject'];
    $messageParsedContent = null;

    $checkIsMessageCopy = Messages\Utils\getMessageCopyId([
        'messageData' => &$dbMessageData,
    ]);
    $isMessageCopy = $checkIsMessageCopy['isSuccess'];

    if ($isMessageCopy) {
        $originalMessageId = $checkIsMessageCopy['payload']['originalMessageId'];

        $messageDetails['isCarbonCopy'] = true;
        $messageDetails['carbonCopyOriginalId'] = $originalMessageId;

        if (!isset($copyOriginalMessagesStorage[$originalMessageId])) {
            $messageParsedSubject = $_Lang['msg_const']['subjects']['019'];
            $messageParsedContent = sprintf(
                $_Lang['msg_const']['msgs']['err3'],
                $originalMessageId
            );
        } else {
            $originalMessage = $copyOriginalMessagesStorage[$originalMessageId];

            $messageParsedSubject = $originalMessage['subject'];
            $messageParsedFrom = $originalMessage['from'];
            $messageParsedContent = $originalMessage['text'];
        }
    } else {
        $messageParsedContent = Messages\Utils\formatUserMessageContent($dbMessageData);
    }

    $messageDetails['from'] = $messageParsedFrom;
    $messageDetails['subject'] = $messageParsedSubject;
    $messageDetails['text'] = $messageParsedContent;
    $messageDetails['Thread_ID'] = (
        ($dbMessageData['Thread_ID'] > 0) ?
            $dbMessageData['Thread_ID'] :
            null
    );

    return $messageDetails;
}

function _buildMessageButtons($dbMessageData, $params) {
    $buttons = [];

    $buttons = (
        ($dbMessageData['id_sender'] == 0) ?
            _buildSystemMessageButtons($dbMessageData, $params) :
            _buildUserMessageButtons($dbMessageData, $params)
    );

    return implode('<span class="lnBr"></span>', $buttons);
}

function _buildMessageButtonElementHTML($params) {
    $buttonElementHTML = (
        isset($params['href']) ?
            buildLinkHTML([
                'text' => $params['label'],
                'href' => $params['href'],
                'query' => $params['query'],
                'attrs' => $params['attributes'],
            ]) :
            buildDOMElementHTML([
                'tagName' => 'a',
                'contentHTML' => $params['label'],
                'attrs' => $params['attributes'],
            ])
    );

    return buildDOMElementHTML([
        'tagName' => 'span',
        'contentHTML' => $buttonElementHTML,
        'attrs' => [
            'class' => 'hov',
        ]
    ]);
}

function _buildSystemMessageButtons($dbMessageData, $params) {
    global $_Lang;

    $buttons = [];

    if ($dbMessageData['type'] == 3) {
        $msgPayload = json_decode($dbMessageData['text'], true);
        $reportId = $msgPayload['args'][0];

        $buttons[] = _buildMessageButtonElementHTML([
            'label' => $_Lang['mess_convert'],
            'attributes' => [
                'id' => "cv_{$reportId}",
                'class' => 'convert',
            ],
        ]);
    }

    $buttons[] = _buildMessageButtonElementHTML([
        'label' => $_Lang['mess_report'],
        'href' => 'report.php',
        'query' => [
            'type' => '5',
            'eid' => $dbMessageData['id'],
        ],
        'attributes' => [
            'class' => 'report',
        ],
    ]);
    $buttons[] = _buildMessageButtonElementHTML([
        'label' => $_Lang['mess_delete_single'],
        'attributes' => [
            'class' => 'delete',
        ],
    ]);

    return $buttons;
}

function _buildUserMessageButtons($dbMessageData, $params) {
    global $_Lang;

    $readerUserData = &$params['readerUserData'];

    $buttons = [];

    $senderId = $dbMessageData['id_sender'];

    if ($senderId != $readerUserData['id']) {
        $replyId = (
            ($dbMessageData['Thread_ID'] > 0) ?
                $dbMessageData['Thread_ID'] :
                $dbMessageData['id']
        );

        $buttons[] = _buildMessageButtonElementHTML([
            'label' => $_Lang['mess_reply'],
            'href' => 'messages.php',
            'query' => [
                'mode' => 'write',
                'replyto' => $replyId,
            ],
            'attributes' => [
                'class' => 'reply',
            ],
        ]);
    }
    if (
        $dbMessageData['type'] == 2 &&
        $readerUserData['ally_id'] > 0
    ) {
        $buttons[] = _buildMessageButtonElementHTML([
            'label' => $_Lang['mess_reply_toally'],
            'href' => 'alliance.php',
            'query' => [
                'mode' => 'sendmsg',
            ],
            'attributes' => [
                'class' => 'reply2',
            ],
        ]);
    }

    if (
        $dbMessageData['type'] != 80 &&
        $dbMessageData['type'] != 2 &&
        !CheckAuth('user', AUTHCHECK_HIGHER, $dbMessageData)
    ) {
        $buttons[] = _buildMessageButtonElementHTML([
            'label' => $_Lang['mess_ignore'],
            'href' => 'settings.php',
            'query' => [
                'ignoreadd' => $senderId,
            ],
            'attributes' => [
                'class' => 'ignore',
            ],
        ]);
    }

    $buttons[] = _buildMessageButtonElementHTML([
        'label' => $_Lang['mess_report'],
        'href' => 'report.php',
        'query' => [
            'type' => '1',
            'uid' => $senderId,
            'eid' => $dbMessageData['id'],
        ],
        'attributes' => [
            'class' => 'report2',
        ],
    ]);
    $buttons[] = _buildMessageButtonElementHTML([
        'label' => $_Lang['mess_delete_single'],
        'attributes' => [
            'class' => 'delete',
        ],
    ]);

    return $buttons;
}

?>
