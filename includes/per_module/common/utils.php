<?php

function isGameClosed ($_GameConfig) {
    return (
        $_GameConfig['game_disable'] &&
        !CheckAuth('supportadmin')
    );
}

function isGameStartTimeReached ($timestamp) {
    return ($timestamp >= SERVER_MAINOPEN_TSTAMP);
}

function isIPBanned ($ipAddress, $_GameConfig) {
    if (empty($ipAddress)) {
        return false;
    }

    $bannedIPs = _getBannedIPsList($_GameConfig);

    return in_array($ipAddress, $bannedIPs);
}

function getGameCloseReason ($_GameConfig) {
    global $_Lang;

    $message = '';
    $message .= $_Lang['ServerIsClosed'];

    if (isset($_GameConfig['close_reason']) && !empty($_GameConfig['close_reason'])) {
        $message .= '<br/><br/>';
        $message .= nl2br($_GameConfig['close_reason']);
    }

    return $message;
}

//  Arguments:
//      - params (Object)
//          - user (&Object)
//          - enginePath (String)
//
function getSkinPath ($params) {
    $user = &$params['user'];
    $enginePath = $params['enginePath'];

    $pathPrefix = (
        defined('IN_ADMIN') ?
        $enginePath :
        ""
    );

    $skinPath = (
        !empty($user['skinpath']) ?
        $user['skinpath'] :
        UNIENGINE_DEFAULT_SKINPATH
    );

    $isLocalPath = !(preg_match('/(http:|https:)/', $skinPath) == 1);

    if (!$isLocalPath) {
        return $skinPath;
    }

    return "{$pathPrefix}{$skinPath}";
}

function getPlanetChangeRequestedID(&$input) {
    if (!isset($input['cp'])) {
        return null;
    }

    $requestedID = intval($input['cp']);

    if ($requestedID <= 0) {
        return null;
    }

    return $requestedID;
}

//  Arguments:
//      - params (Object)
//          - cache (&Object)
//
function loadGameConfig ($params) {
    $cache = &$params['cache'];

    if (isset($cache->GameConfig)) {
        return $cache->GameConfig;
    }

    $config = [];

    $query_GetConfig = "SELECT * FROM {{table}};";
    $result_GetConfig = doquery($query_GetConfig, 'config');

    while ($entry = $result_GetConfig->fetch_assoc()) {
        $config[$entry['config_name']] = $entry['config_value'];
    }

    $cache->GameConfig = $config;

    return $config;
}

function _getBannedIPsList ($_GameConfig) {
    $bannedIPsFromConfig = $_GameConfig['banned_ip_list'];

    if (empty($bannedIPsFromConfig)) {
        return [];
    }

    $bannedIPs = explode('|', $bannedIPsFromConfig);

    if (
        empty($bannedIPs) ||
        !is_array($bannedIPs)
    ) {
        return [];
    }

    return $bannedIPs;
}

?>
