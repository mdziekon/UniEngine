<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Modules\Messages;

/**
 * Extracts message's carbon copy reference ID, as long as the message is indeed
 * a copy of another message. Otherwise, returns an error.
 *
 * @param array $params
 * @param &array $params['messageData']
 * @param string $params['messageData']['text']
 */
function getMessageCopyId($params) {
    $messageContent = $params['messageData']['text'];

    $extractedMatches = null;

    $isCopy = preg_match('/^\{COPY\_MSG\_\#([0-9]{1,}){1}\}$/D', $messageContent, $extractedMatches);

    if ($isCopy !== 1) {
        return [
            'isSuccess' => false,
            'error' => [
                'isNotCopy' => true,
            ],
        ];
    }

    return [
        'isSuccess' => true,
        'payload' => [
            'originalMessageId' => $extractedMatches[1],
        ],
    ];
}

function getMessagesCopyIds($messages) {
    $messageCopyIds = array_map(
        function ($messageDetails) {
            if (Messages\Utils\isSystemSentMessage($messageDetails)) {
                return null;
            }

            $checkIsMessageCopy = Messages\Utils\getMessageCopyId([
                'messageData' => &$messageDetails,
            ]);

            if (!($checkIsMessageCopy['isSuccess'])) {
                return null;
            }

            return $checkIsMessageCopy['payload']['originalMessageId'];
        },
        $messages
    );
    $messageCopyIds = Collections\compact($messageCopyIds);

    return $messageCopyIds;
}

?>
