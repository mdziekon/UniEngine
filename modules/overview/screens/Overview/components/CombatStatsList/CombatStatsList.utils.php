<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\CombatStatsList\Utils;

/**
 * @param array $props
 * @param array $props['userId']
 */
function getUserCombatStats($props) {
    $userId = $props['userId'];

    $query = (
        "SELECT " .
        "`ustat_raids_won`, " .
        "`ustat_raids_draw`, " .
        "`ustat_raids_lost`, " .
        "`ustat_raids_acs_won`, " .
        "`ustat_raids_inAlly`, " .
        "`ustat_raids_missileAttack` " .
        "FROM {{table}} " .
        "WHERE " .
        "`A_UserID` = {$userId} " .
        "LIMIT 1 " .
        ";"
    );

    return doquery($query, 'achievements_stats', true);
}

?>
