<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\NoobProtectionInfoBox\Utils;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param number $params['currentTimestamp']
 */
function isProtectedByNoobProtection($props) {
    return ($props['user']['NoobProtection_EndTime'] > $props['currentTimestamp']);
}

?>
