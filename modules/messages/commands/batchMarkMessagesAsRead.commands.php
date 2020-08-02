<?php

namespace UniEngine\Engine\Modules\Messages\Commands;

use UniEngine\Engine\Modules\Messages;

/**
 * Marks as read all messages of a specified user by message IDs.
 * Admin messages are excluded from being batch marked.
 *
 * @param array $params
 * @param number $params['userID']
 *               The ID of a user whose messages should be updated.
 * @param number|null $params['messageTypeID']
 *               (Optional) Narrows the messages to be updated to the specified type.
 * @param number $params['untilTimestamp']
 *               Determines the cut-off point for update, meaning that only messages
 *               prior to this point in time (inclusive) will be updated.
 */
function batchMarkMessagesAsRead($params) {
    $ownerID = $params['userID'];
    $untilTimestamp = $params['untilTimestamp'];
    $messageTypeID = (
        isset($params['messageTypeID']) ?
        $params['messageTypeID'] :
        null
    );

    $excludedMessageTypesString = Messages\Utils\getBatchActionsExcludedMessageTypesQueryString();

    $updateMessagesQuery = (
        "UPDATE {{table}} " .
        "SET " .
        "`read` = true " .
        "WHERE " .
        (
            $messageTypeID !== null ?
            "`type` = {$messageTypeID} AND " :
            "`type` NOT IN ({$excludedMessageTypesString}) AND "
        ) .
        "`time` <= {$untilTimestamp} AND " .
        "`id_owner` = {$ownerID} " .
        ";"
    );

    doquery($updateMessagesQuery, 'messages');

    $markedMessagesCount = getDBLink()->affected_rows;

    return [
        'markedMessagesCount' => $markedMessagesCount,
    ];
}

?>
