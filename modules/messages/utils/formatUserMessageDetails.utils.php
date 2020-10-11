<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

function formatMessageTypeColorClass($dbMessageData) {
    $messageType = $dbMessageData['type'];
    $messageTypeLabel = str_pad($messageType, 1, '0');

    return "c{$messageTypeLabel}";
}

function formatUserMessageSenderLabel($dbMessageData) {
    global $_Lang;

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

    $senderLabel = implode(' ', $senderDetailsPieces);

    return $senderLabel;
}

function formatUserMessageContent($dbMessageData) {
    global $_EnginePath, $_GameConfig;

    include_once($_EnginePath . 'includes/functions/BBcodeFunction.php');

    $messageParsedContent = $dbMessageData['text'];

    if ($_GameConfig['enable_bbcode'] == 1) {
        $messageParsedContent = bbcode(image($messageParsedContent));
    }

    $messageParsedContent = nl2br($messageParsedContent);

    return $messageParsedContent;
}

?>
