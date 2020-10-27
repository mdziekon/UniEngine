<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

abstract class MessageReadStatus {
    const Read = 0;
    const Unread = 1;
}

function updateMessagesReadStatus($messageIds, $newStatus) {
    if (empty($messageIds)) {
        return [
            'isSuccess' => true,
        ];
    }

    $messageIdsFilterValue = implode(', ', $messageIds);
    $newStatusQueryValue = 'false';

    switch ($newStatus) {
        case MessageReadStatus::Read:
            $newStatusQueryValue = 'true';
        break;
        case MessageReadStatus::Unread:
            $newStatusQueryValue = 'false';
        break;
        default:
            return [
                'isSuccess' => true,
                'error' => [
                    'invalidNewStatus' => true,
                ],
            ];
    }

    $updateQuery = (
        "UPDATE {{table}} " .
        "SET " .
        "`read` = {$newStatusQueryValue} " .
        "WHERE " .
        "`id` IN ({$messageIdsFilterValue}) AND " .
        "`deleted` = false " .
        ";"
    );

    doquery($updateQuery, 'messages');

    return [
        'isSuccess' => true,
    ];
}

?>
