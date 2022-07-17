<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\NoobProtectionInfoBox\Utils\Effects;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param number $params['currentTimestamp']
 */
function turnOffProtection($props) {
    $user = &$props['user'];
    $currentTimestamp = $props['currentTimestamp'];

    $protectionPropKey = 'NoobProtection_EndTime';

    $user[$protectionPropKey] = $currentTimestamp;

    $updateQuery = (
        "UPDATE {{table}} " .
        "SET " .
        "`{$protectionPropKey}` = {$currentTimestamp} " .
        "WHERE " .
        "`id` = {$user['id']} " .
        "LIMIT 1 " .
        ";"
    );

    doquery($updateQuery, 'users');
}

?>
