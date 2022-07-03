<?php

namespace UniEngine\Engine\Modules\Overview\Screens\FirstLogin\Utils\Effects;

/**
 * @param array $params
 * @param number $params['userId']
 * @param number $params['currentTimestamp']
 */
function updateUserOnFirstLogin($props) {
    global $_GameConfig;

    $userId = $props['userId'];
    $currentTimestamp = $props['currentTimestamp'];

    $newUserProtectionEndTimestamp = $currentTimestamp + $_GameConfig['Protection_NewPlayerTime'];

    $updateChatPresenceQuery = (
        "INSERT IGNORE INTO {{table}} " .
        "VALUES " .
        "(0, {$userId}, {$currentTimestamp}) " .
        ";"
    );
    $updatePlanetsStateQuery = (
        "UPDATE {{table}} " .
        "SET " .
        "`last_update` = {$currentTimestamp} " .
        "WHERE " .
        "`id_owner` = {$userId} " .
        ";"
    );
    $updateUserStateQuery = (
        "UPDATE {{table}} " .
        "SET " .
        "`first_login` = {$currentTimestamp}, " .
        "`NoobProtection_EndTime` = {$newUserProtectionEndTimestamp} " .
        "WHERE " .
        "`id` = {$userId} " .
        "LIMIT 1 " .
        ";"
    );

    doquery($updateChatPresenceQuery, 'chat_online');
    doquery($updatePlanetsStateQuery, 'planets');
    doquery($updateUserStateQuery, 'users');
}

?>
