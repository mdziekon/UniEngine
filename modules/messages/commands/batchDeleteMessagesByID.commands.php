<?php

namespace UniEngine\Engine\Modules\Messages\Commands;

use UniEngine\Engine\Modules\Messages;

/**
 * Soft deletes all messages of a specified user by message IDs.
 * Admin messages are excluded from being batch deleted.
 *
 * @param array $params
 * @param array<number> $params['messageIDs']
 *               Messages to be deleted.
 * @param number $params['userID']
 *               The ID of a user whose messages should be deleted.
 */
function batchDeleteMessagesByID($params) {
    $messageIDs = $params['messageIDs'];
    $ownerID = $params['userID'];

    $excludedMessageTypesString = Messages\Utils\getBatchActionsExcludedMessageTypesQueryString();
    $messageIDsString = implode(', ', $messageIDs);

    $softDeleteMessagesQuery = (
        "UPDATE {{table}} " .
        "SET " .
        "`deleted` = true, " .
        "`Thread_IsLast` = 0 " .
        "WHERE " .
        "`type` NOT IN ({$excludedMessageTypesString}) AND " .
        "`id` IN ({$messageIDsString}) AND " .
        "`id_owner` = {$ownerID} " .
        ";"
    );

    doquery($softDeleteMessagesQuery, 'messages');

    $deletedMessagesCount = getDBLink()->affected_rows;

    _updateMessageThreadsAffectedByBatchDeletionByID($params);

    return [
        'deletedMessagesCount' => $deletedMessagesCount,
    ];
}

/**
 * @param array $params
 *              See batchDeleteUserMessages().
 */
function _updateMessageThreadsAffectedByBatchDeletionByID($params) {
    $messageIDs = $params['messageIDs'];
    $ownerID = $params['userID'];

    $excludedMessageTypesString = Messages\Utils\getBatchActionsExcludedMessageTypesQueryString();
    $messageIDsString = implode(', ', $messageIDs);

    $fetchThreadedMessagesQuery = (
        "SELECT `Thread_ID` FROM {{table}} " .
        "WHERE " .
        "`type` NOT IN ({$excludedMessageTypesString}) AND " .
        "`id` IN ({$messageIDsString}) AND " .
        "`id_owner` = {$ownerID} AND " .
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
