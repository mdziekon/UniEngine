<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

use UniEngine\Engine\Modules\Messages;

function fetchOriginalMessagesForRefSystem($params) {
    global $_Lang;

    $originalMessageIds = $params['originalMessageIds'];

    $messagesById = [];

    if (empty($originalMessageIds)) {
        return $messagesById;
    }

    $getMassMessagesQuery = (
        "SELECT " .
        "`m`.*, `u`.`username`, `u`.`authlevel` " .
        "FROM {{table}} as `m` " .
        "LEFT JOIN " .
        "`{{prefix}}users` AS `u` " .
        "ON `u`.`id` = `m`.`id_sender` " .
        "WHERE " .
        "`m`.`id` IN (" . implode(', ', $originalMessageIds) . ") " .
        ";"
    );

    $getMassMessagesResult = doquery($getMassMessagesQuery, 'messages');

    while ($copyOriginalMessage = $getMassMessagesResult->fetch_assoc()) {
        $messageId = $copyOriginalMessage['id'];

        if (Messages\Utils\isSystemSentMessage($copyOriginalMessage)) {
            $messageObject = [
                'from' => $copyOriginalMessage['from'],
                'subject' => $_Lang['msg_const']['subjects']['019'],
                'text' => sprintf($_Lang['msg_const']['msgs']['err3'], $messageId),
            ];

            $messagesById[$messageId] = $messageObject;

            continue;
        }

        $messageObject = Messages\Utils\_buildTypedUserMessageDetails(
            $copyOriginalMessage,
            [
                'copyOriginalMessagesStorage' => [],
            ]
        );

        $messagesById[$messageId] = $messageObject;
    }

    return $messagesById;
}

?>
