<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

/**
 * @param array $props
 * @param array $props['newFleet']
 */
function getFleetUnionJoinData ($props) {
    $newFleet = $props['newFleet'];

    $fetchUnionDataQuery = (
        "SELECT " .
        "{{table}}.*, `fleets`.`fleet_send_time` AS `mf_start_time` " .
        "FROM {{table}} " .
        "LEFT JOIN {{prefix}}fleets AS `fleets` " .
        "ON " .
        "`fleets`.`fleet_id` = {{table}}.`main_fleet_id` " .
        "WHERE " .
        "{{table}}.`id` = {$newFleet['ACS_ID']} " .
        "LIMIT 1 " .
        ";"
    );
    $fetchUnionDataResult = doquery($fetchUnionDataQuery, 'acs', true);

    return $fetchUnionDataResult;
}

?>
