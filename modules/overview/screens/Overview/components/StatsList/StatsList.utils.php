<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\StatsList\Utils;

/**
 * @param array $props
 * @param array $props['userId']
 */
function getUserGameStats($props) {
    $userId = $props['userId'];

    $query = (
        "SELECT * " .
        "FROM {{table}} " .
        "WHERE " .
        "`stat_type` = '1' AND " .
        "`id_owner` = {$userId} " .
        "LIMIT 1 " .
        ";"
    );

    return doquery($query, 'statpoints', true);
}

?>
