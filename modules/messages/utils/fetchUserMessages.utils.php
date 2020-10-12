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

    $queryTypeFilter = (
        isset($params['filterMessageType']) ?
            "`m`.`type` = {$params['filterMessageType']}" :
            "`m`.`type` NOT IN (80)"
    );
    $queryThreadingFilter = (
        _isMessagesThreadViewEnabled($params) ?
            "(`m`.`type` NOT IN (" . implode(', ', $threadableMessageTypes) . ") OR `m`.`Thread_ID` = 0 OR `m`.`Thread_IsLast` = 1)" :
            "(1 == 1)"
    );
    $queryLimitStart = (
        ($page > 1) ?
            (($page - 1) * $pageSize) :
            0
    );

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

?>
