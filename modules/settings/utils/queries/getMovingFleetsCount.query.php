<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param string $params['userId']
 */
function getMovingFleetsCount($params) {
    $userId = $params['userId'];

    $query = (
        "SELECT " .
        "COUNT(*) AS `fleetsCount` " .
        "FROM {{table}} " .
        "WHERE " .
        "`fleet_owner` = {$userId} OR " .
        "`fleet_target_owner` = {$userId} " .
        ";"
    );

    $result = doquery($query, 'fleets', true);

    return $result['fleetsCount'];
}

?>
