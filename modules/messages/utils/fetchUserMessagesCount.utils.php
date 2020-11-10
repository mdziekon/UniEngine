<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param boolean | null $params['filterMessageType']
 */
function fetchUserMessagesCount($params) {
    $user = &$params['user'];

    $threadableMessageTypes = _getThreadableMessageTypes();

    $queryTypeFilter = (
        isset($params['filterMessageType']) ?
            "`type` = {$params['filterMessageType']}" :
            "`type` NOT IN (80)"
    );
    $queryThreadingFilter = (
        _isMessagesThreadViewEnabled($params) ?
            "(`type` NOT IN (" . implode(', ', $threadableMessageTypes) . ") OR `Thread_ID` = 0 OR `Thread_IsLast` = 1)" :
            "(1 = 1)"
    );

    $query_GetMessagesCount = (
        "SELECT " .
        "COUNT(`id`) as `count` " .
        "FROM {{table}} " .
        "WHERE " .
        "`deleted` = false AND " .
        "`id_owner` = {$user['id']} AND " .
        "{$queryTypeFilter} AND " .
        "{$queryThreadingFilter} " .
        ";"
    );

    $result_GetMessagesCount = doquery($query_GetMessagesCount, 'messages', true);

    return $result_GetMessagesCount['count'];
}

/**
 * @param array $params
 * @param arrayRef $params['user']
 */
function _isMessagesThreadViewEnabled($params) {
    $user = &$params['user'];

    return ($user['settings_UseMsgThreads'] == 1);
}

function _getThreadableMessageTypes() {
    return [
        1,
        80,
        100,
    ];
}

function _isMessageThreadable($messageData) {
    return _isMessageTypeThreadable($messageData['type']);
}

function _isMessageTypeThreadable($messageType) {
    return in_array($messageType, _getThreadableMessageTypes());
}

?>
