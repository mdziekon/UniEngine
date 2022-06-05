<?php

namespace UniEngine\Engine\Modules\Phalanx\Utils\Queries;

/**
 * @param array $params
 * @param number $params['scanCost']
 * @param number $params['phalanxMoonId']
 */
function updatePhalanxMoon($params) {
    $scanCost = $params['scanCost'];
    $phalanxMoonId = $params['phalanxMoonId'];

    $query = (
        "UPDATE {{table}} " .
        "SET " .
        "`deuterium` = `deuterium` - {$scanCost} " .
        "WHERE " .
        "`id` = {$phalanxMoonId} " .
        "LIMIT 1 " .
        "; -- Phalanx\Utils\Queries\updatePhalanxMoon()"
    );

    doquery($query, 'planets');
}

?>
