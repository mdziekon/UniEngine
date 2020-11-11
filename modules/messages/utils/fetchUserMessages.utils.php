<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param boolean | null $params['filterMessageType']
 * @param number $params['page']
 * @param number $params['pageSize']
 * @param number $params['messagesCount']
 */
function fetchUserMessages($params) {
    $user = &$params['user'];

    $threadableMessageTypes = _getThreadableMessageTypes();

    $page = _normalizeCurrentPageNo($params);
    $pageSize = $params['pageSize'];

    $queryTypeFilter = (
        isset($params['filterMessageType']) ?
            "`m`.`type` = {$params['filterMessageType']}" :
            "`m`.`type` NOT IN (80)"
    );
    $queryThreadingFilter = (
        _isMessagesThreadViewEnabled($params) ?
            "(`m`.`type` NOT IN (" . implode(', ', $threadableMessageTypes) . ") OR `m`.`Thread_ID` = 0 OR `m`.`Thread_IsLast` = 1)" :
            "(1 = 1)"
    );
    $queryLimitStart = (($page - 1) * $pageSize);

    $query_GetMessages = (
        "SELECT " .
        "`m`.*, `u`.`username`, `u`.`authlevel` " .
        "FROM {{table}} as `m` " .
        "LEFT JOIN " .
        "`{{prefix}}users` AS `u` " .
        "ON `u`.`id` = `m`.`id_sender` " .
        "WHERE " .
        "`m`.`deleted` = false AND " .
        "`m`.`id_owner` = {$user['id']} AND " .
        "{$queryTypeFilter} AND " .
        "{$queryThreadingFilter} " .
        "ORDER BY `m`.`time` DESC, `m`.`id` DESC " .
        "LIMIT {$queryLimitStart}, {$pageSize} " .
        ";"
    );

    return doquery($query_GetMessages, 'messages');
}

/**
 * @param array $params
 * @param number $params['page']
 * @param number $params['pageSize']
 * @param number $params['messagesCount']
 */
function _normalizeCurrentPageNo($params) {
    $page = $params['page'];
    $pageSize = $params['pageSize'];
    $messagesCount = $params['messagesCount'];

    $maxPages = (
        $messagesCount > 0 ?
            ceil($messagesCount / $pageSize) :
            1
    );

    if ($page > $maxPages) {
        $page = $maxPages;
    }
    if ($page < 1) {
        $page = 1;
    }

    return $page;
}

/**
 * @param array $params
 * @param queryResultRef $params['getMessagesDbResult']
 * @param boolean $params['shouldGatherThreadInfo']
 */
function _unpackFetchedMessages($params) {
    $getMessagesDbResult = &$params['getMessagesDbResult'];
    $shouldGatherThreadInfo = $params['shouldGatherThreadInfo'];

    $result = [
        'messages' => [],
        'threads' => [
            'ids' => [],
            'alreadyFetchedMessageIds' => [],
            'oldestMessageIdByThreadId' => [],
        ],
    ];

    while ($messageRow = $getMessagesDbResult->fetch_assoc()) {
        $result['messages'][] = $messageRow;

        if (
            !$shouldGatherThreadInfo ||
            !($messageRow['Thread_ID'] > 0) ||
            !_isMessageThreadable($messageRow)
        ) {
            continue;
        }

        $threadId = $messageRow['Thread_ID'];

        $result['threads']['ids'][] = $threadId;
        $result['threads']['alreadyFetchedMessageIds'][] = $messageRow['id'];
        $result['threads']['oldestMessageIdByThreadId'][$threadId] = $messageRow['id'];
    }

    return $result;
}

?>
