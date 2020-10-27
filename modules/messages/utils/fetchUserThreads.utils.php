<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

/**
 * @param array $params
 * @param number[] $params['threadIds']
 * @param number[] $params['alreadyFetchedMessageIds']
 * @param arrayRef $params['user']
 */
function _fetchThreadMessages($params) {
    $threadIds = $params['threadIds'];
    $excludeMessageIds = $params['alreadyFetchedMessageIds'];
    $user = &$params['user'];

    $threadIdsFilterValue = implode(', ', $threadIds);
    $excludedMessageIdsFilterValue = implode(', ', $excludeMessageIds);

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
        "`m`.`read` = false AND " .
        "`m`.`Thread_ID` IN ({$threadIdsFilterValue}) AND " .
        "`m`.`id` NOT IN ({$excludedMessageIdsFilterValue}) " .
        "ORDER BY `m`.`id` DESC " .
        ";"
    );

    return doquery($query_GetMessages, 'messages');
}

/**
 * @param array $params
 * @param map $params['oldestMessageIdByThreadId']
 * @param arrayRef $params['user']
 */
function _fetchThreadLengths($params) {
    $oldestMessageIdByThreadId = $params['oldestMessageIdByThreadId'];
    $user = &$params['user'];

    $threadFilters = [];

    foreach ($oldestMessageIdByThreadId as $threadId => $oldestMessageId) {
        $threadFilters[] = (
            "(`Thread_ID` = {$threadId} AND `id` <= {$oldestMessageId})"
        );
    }

    $threadFilterValue = implode(' OR ', $threadFilters);

    $query_GetThreadLengths = (
        "SELECT " .
        "`Thread_ID`, COUNT(*) AS `Count` " .
        "FROM {{table}} " .
        "WHERE " .
        "({$threadFilterValue}) AND " .
        "(`id_sender` = {$user['id']} OR `deleted` = false) " .
        "GROUP BY `Thread_ID` " .
        ";"
    );

    return doquery($query_GetThreadLengths, 'messages');
}

?>
