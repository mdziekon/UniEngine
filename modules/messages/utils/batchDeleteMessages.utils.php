<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

/**
 * Soft deletes all messages that match to the specified criteria.
 *
 * @param array $params
 * @param array $params['criteria']
 * @param number $params['criteria']['userID']
 *               The ID of a user whose messages should be deleted.
 * @param array<number>|null $params['criteria']['messageIDs']
 *               (Optional) Narrows the messages to be deleted to the specified IDs only.
 * @param array<number>|null $params['criteria']['includeTypeIDs']
 *               (Optional) Narrows the messages to be deleted to the specified types.
 * @param array<number>|null $params['criteria']['excludeTypeIDs']
 *               (Optional) Excludes the specified types from batch deletion.
 * @param number|null $params['criteria']['untilTimestamp']
 *               (Optional) Determines the cut-off point for deletion,
 *               meaning that only messages prior to this point in time (inclusive)
 *               will be deleted.
 */
function batchDeleteMessages($params) {
    $matchingQueryPart = _createMessagesMatchingQueryPart($params['criteria']);

    $softDeleteMessagesQuery = (
        "UPDATE {{table}} SET `deleted` = true WHERE {$matchingQueryPart};"
    );

    doquery($softDeleteMessagesQuery, 'messages');

    $deletedMessagesCount = getDBLink()->affected_rows;

    _updateMessageThreadsAffectedByBatchDeletion($params);

    return [
        'deletedMessagesCount' => $deletedMessagesCount,
    ];
}

function _createMessagesMatchingQueryPart($params) {
    $ownerUserID = $params['userID'];
    $untilTimestamp = (
        isset($params['untilTimestamp']) ?
        $params['untilTimestamp'] :
        null
    );
    $messageIDs = (
        isset($params['messageIDs']) ?
        $params['messageIDs'] :
        []
    );
    $includeTypeIDs = (
        isset($params['includeTypeIDs']) ?
        $params['includeTypeIDs'] :
        []
    );
    $excludeTypeIDs = (
        isset($params['excludeTypeIDs']) ?
        $params['excludeTypeIDs'] :
        []
    );

    $matchers = [
        "`id_owner` = {$ownerUserID}"
    ];

    if ($untilTimestamp !== null) {
        $matchers[] = "`time` <= {$untilTimestamp}";
    }

    if (!empty($messageIDs)) {
        $messageIDsString = implode(', ', $messageIDs);

        $matchers[] = "`id` IN ({$messageIDsString})";
    }

    if (!empty($includeTypeIDs)) {
        $includeTypeIDsString = implode(', ', $includeTypeIDs);

        $matchers[] = "`type` IN ({$includeTypeIDsString})";
    } else if (!empty($excludeTypeIDs)) {
        $excludeTypeIDsString = implode(', ', $excludeTypeIDs);

        $matchers[] = "`type` NOT IN ({$excludeTypeIDsString})";
    }

    return implode(' AND ', $matchers);
}

/**
 * @param array $params
 *              See batchDeleteUserMessages().
 */
function _updateMessageThreadsAffectedByBatchDeletion($params) {
    $ownerUserID = $params['criteria']['userID'];
    $matchingQueryPart = _createMessagesMatchingQueryPart($params['criteria']);

    $fetchThreadedMessagesQuery = (
        "SELECT `Thread_ID` FROM {{table}} WHERE {$matchingQueryPart} AND `Thread_ID` > 0;"
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
        "`id_owner` = {$ownerUserID} AND " .
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
