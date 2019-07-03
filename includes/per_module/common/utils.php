<?php

function isIPBanned ($ipAddress, $_GameConfig) {
    if (empty($ipAddress)) {
        return false;
    }

    $bannedIPs = _getBannedIPsList($_GameConfig);

    return in_array($ipAddress, $bannedIPs);
}

//  Arguments:
//      - params (Object)
//          - user (&Object)
//          - enginePath (String)
//
function getSkinPath ($params) {
    $user = $params['user'];
    $enginePath = $params['enginePath'];

    $pathPrefix = (
        defined('IN_ADMIN') ?
        $enginePath :
        ""
    );

    $skinPath = (
        !empty($user['skinpath']) ?
        $user['skinpath'] :
        DEFAULT_SKINPATH
    );

    $isLocalPath = !(preg_match('/(http:|https:)/', $skinPath) == 1);

    if (!$isLocalPath) {
        return $skinPath;
    }

    return "{$pathPrefix}{$skinPath}";
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
