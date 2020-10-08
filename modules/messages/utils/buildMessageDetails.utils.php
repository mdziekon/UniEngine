<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Modules\Messages;

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
        'text' => null,
        'Thread_ID' => null,
        'isCarbonCopy' => null,
        'carbonCopyOriginalId' => null,
    ];

    $senderUserId = $dbMessageData['id_sender'];
    $senderUsername = $dbMessageData['username'];
    $senderAuthLabelKey = GetAuthLabel($dbMessageData);
    $senderAuthLabel = $_Lang['msg_const']['senders']['rangs'][$senderAuthLabelKey];

    $senderDetailsPieces = Collections\compact([
        $senderAuthLabel,
        "<a href=\"profile.php?uid={$senderUserId}\">{$senderUsername}</a>",
        (
            !empty($dbMessageData['from']) ?
                $dbMessageData['from'] :
                null
        ),
    ]);

    $messageParsedContent = null;
    $checkIsMessageCopy = Messages\Utils\getMessageCopyId([
        'messageData' => &$dbMessageData,
    ]);
    $isMessageCopy = $checkIsMessageCopy['isSuccess'];

    if ($isMessageCopy) {
        $originalMessageId = $checkIsMessageCopy['payload']['originalMessageId'];

        $messageDetails['isCarbonCopy'] = true;
        $messageDetails['carbonCopyOriginalId'] = $originalMessageId;

        $messageParsedContent = sprintf(
            $_Lang['msg_const']['msgs']['err4'],
            $dbMessageData['id']
        );
    } else {
        $messageParsedContent = $dbMessageData['text'];

        if ($_GameConfig['enable_bbcode'] == 1) {
            $messageParsedContent = bbcode(image($messageParsedContent));
        }

        $messageParsedContent = nl2br($messageParsedContent);
    }

    $messageDetails['from'] = implode(' ', $senderDetailsPieces);
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

function _buildSystemMessageButtons($dbMessageData, $params) {
    global $_Lang;

    $buttons = [];

    if ($dbMessageData['type'] == 3) {
        $msgPayload = json_decode($dbMessageData['text'], true);

        $buttons[] = "<span class=\"hov\"><a class=\"convert\" id=\"cv_{$msgPayload['args'][0]}\">{$_Lang['mess_convert']}</a></span>";
    }

    $buttons[] = "<span class=\"hov\"><a class=\"report\" href=\"report.php?type=5&amp;eid={$dbMessageData['id']}\">{$_Lang['mess_report']}</a></span>";
    $buttons[] = "<span class=\"hov\"><a class=\"delete\">{$_Lang['mess_delete_single']}</a></span>";

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

        $buttons[] = "<span class=\"hov\"><a class=\"reply\" href=\"messages.php?mode=write&amp;replyto={$replyId}\">{$_Lang['mess_reply']}</a></span>";
    }
    if (
        $dbMessageData['type'] == 2 &&
        $readerUserData['ally_id'] > 0
    ) {
        $buttons[] = "<span class=\"hov\"><a class=\"reply2\" href=\"alliance.php?mode=sendmsg\">{$_Lang['mess_reply_toally']}</a></span>";
    }

    if (
        $dbMessageData['type'] != 80 &&
        $dbMessageData['type'] != 2 &&
        !CheckAuth('user', AUTHCHECK_HIGHER, $dbMessageData)
    ) {
        $buttons[] = "<span class=\"hov\"><a class=\"ignore\" href=\"settings.php?ignoreadd={$senderId}\">{$_Lang['mess_ignore']}</a></span>";
    }

    $buttons[] = "<span class=\"hov\"><a class=\"report2\" href=\"report.php?type=1&amp;uid={$senderId}&amp;eid={$dbMessageData['id']}\">{$_Lang['mess_report']}</a></span>";
    $buttons[] = "<span class=\"hov\"><a class=\"delete\">{$_Lang['mess_delete_single']}</a></span>";

    return $buttons;
}

?>