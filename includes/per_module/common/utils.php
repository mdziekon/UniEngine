<?php

function isIPBanned ($ipAddress, $_GameConfig) {
    if (empty($ipAddress)) {
        return false;
    }

    $bannedIPs = _getBannedIPsList($_GameConfig);

    return in_array($ipAddress, $bannedIPs);
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
