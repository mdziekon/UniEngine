<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Fetchers;

/**
 * @param array $props
 * @param string $props['fleetId']
 */
function fetchUnionFleet($props) {
    $query = (
        "SELECT " .
        "`fleet`.*, " .
        "`planet`.`name` as `fleet_end_target_name` " .
        "FROM {{table}} AS `fleet` " .
        "LEFT JOIN {{prefix}}planets AS `planet` " .
        "ON " .
        "`planet`.`id` = `fleet`.`fleet_end_id` " .
        "WHERE " .
        "`fleet`.`fleet_id` = {$props['fleetId']} " .
        "LIMIT 1 " .
        ";"
    );
    $result = doquery($query, 'fleets', true);

    return $result;
}

?>
