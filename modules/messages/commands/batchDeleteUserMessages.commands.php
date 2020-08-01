<?php

namespace UniEngine\Engine\Modules\Messages\Commands;

/**
 * Soft deletes all messages of a specified user which were received up until the specified point in time.
 * Admin messages are excluded from being batch deleted.
 *
 * @param array $params
 * @param number $params['userID']
 *               The ID of a user whose messages should be deleted.
 * @param number $params['untilTimestamp']
 *               Determines the cut-off point for deletion, meaning that all messages
 *               prior to this point in time (inclusive) will be deleted.
 */
function batchDeleteUserMessages($params) {
    $ownerID = $params['userID'];
    $utilTimestamp = $params['untilTimestamp'];

    $excludedMessageTypesString = _getBatchDeletionExcludedMessageTypesQueryString();

    $softDeleteMessagesQuery = (
        "UPDATE {{table}} " .
        "SET " .
        "`deleted` = true " .
        "WHERE " .
        "`time` <= {$utilTimestamp} AND" .
        "`id_owner` = {$ownerID} AND " .
        "`type` NOT IN ({$excludedMessageTypesString}) " .
        ";"
    );

    doquery($softDeleteMessagesQuery, 'messages');

    $deletedMessagesCount = getDBLink()->affected_rows;

    _updateMessageThreadsAffectedByBatchDeletion($params);

    return [
        'deletedMessagesCount' => $deletedMessagesCount,
    ];
}

function _getBatchDeletionExcludedMessageTypes() {
    return [ 80 ];
}

function _getBatchDeletionExcludedMessageTypesQueryString() {
    $messageTypes = array_map(
        function ($value) { return strval($value); },
        _getBatchDeletionExcludedMessageTypes()
    );

    return implode(', ', $messageTypes);
}

/**
 * @param array $params
 *              See batchDeleteUserMessages().
 */
function _updateMessageThreadsAffectedByBatchDeletion($params) {
    $ownerID = $params['userID'];
    $utilTimestamp = $params['untilTimestamp'];

    $excludedMessageTypesString = _getBatchDeletionExcludedMessageTypesQueryString();

    $fetchThreadedMessagesQuery = (
        "SELECT `Thread_ID` FROM {{table}} " .
        "WHERE " .
        "`time` <= {$utilTimestamp} AND " .
        "`id_owner` = {$ownerID} AND " .
        "`type` NOT IN ({$excludedMessageTypesString}) AND " .
        "`Thread_ID` > 0 " .
        ";"
    );

    $threadedMessagesResult = doquery($fetchThreadedMessagesQuery, 'messages');

    if ($threadedMessagesResult->num_rows <= 0) {
        return;
    }

    $threadsToUpdate = [];

    while ($threadData = $threadedMessagesResult->fetch_assoc()) {
        $threadID = $threadData['Thread_ID'];

        if (in_array($threadID, $threadsToUpdate)) {
            continue;
        }

        $threadsToUpdate[] = $threadID;
    }

    $threadsToUpdateString = implode(', ', $threadsToUpdate);

    $fetchLastNonDeletedMessageIDsOfThreadsQuery = (
        "SELECT MAX(`id`) AS `id` " .
        "FROM {{table}} " .
        "WHERE " .
        "`Thread_ID` IN ({$threadsToUpdateString}) AND " .
        "`id_owner` = {$ownerID} AND " .
        "`deleted` = false " .
        "GROUP BY `Thread_ID` " .
        ";"
    );
    $fetchLastNonDeletedMessageIDsOfThreadsResult = doquery(
        $fetchLastNonDeletedMessageIDsOfThreadsQuery,
        'messages'
    );

    if ($fetchLastNonDeletedMessageIDsOfThreadsResult->num_rows <= 0) {
        return;
    }

    $threadMessagesToUpdate = [];

    while ($messageEntry = $fetchLastNonDeletedMessageIDsOfThreadsResult->fetch_assoc()) {
        $messageID = $messageEntry['id'];

        $threadMessagesToUpdate[] = $messageID;
    }

    $threadMessagesToUpdateString = implode(', ', $threadMessagesToUpdate);

    $updateThreadedMessagesQuery = (
        "UPDATE {{table}} " .
        "SET " .
        "`Thread_IsLast` = 1 " .
        "WHERE " .
        "`id` IN ({$threadMessagesToUpdateString}) " .
        ";"
    );

    doquery($updateThreadedMessagesQuery, 'messages');
}

?>
