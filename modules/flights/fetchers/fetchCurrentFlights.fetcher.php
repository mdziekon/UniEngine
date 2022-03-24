<?php

namespace UniEngine\Engine\Modules\Flights\Fetchers;

/**
 * @param object $params
 * @param string | null $params['targetId']
 * @param string | null $params['userId']
 */
function fetchCurrentFlights($params) {
    $conditions = "";

    if (!empty($params['targetId'])) {
        $fleetTargetId = $params['targetId'];

        $conditions = (
            "`fleet`.`fleet_start_id` = {$fleetTargetId} OR " .
            "`fleet`.`fleet_end_id` = {$fleetTargetId} "
        );
    }
    if (!empty($params['userId'])) {
        $fleetUserId = $params['userId'];

        $conditions = (
            "`fleet`.`fleet_owner` = {$fleetUserId} OR " .
            "`fleet`.`fleet_target_owner` = {$fleetUserId} "
        );
    }

    $fetchFleetsQuery = (
        "SELECT" .
        "`fleet`.*, " .
        "`origin_planet`.`name` as `start_name`, " .
        "`destination_planet`.`name` as `end_name`, " .
        "`get_acs`.`fleets_id`, " .
        "`fleet_owner`.`username` AS `owner_name` " .
        "FROM {{table}} AS `fleet` " .
        "LEFT JOIN " .
        "{{prefix}}planets AS `origin_planet` " .
        "ON " .
        "`origin_planet`.`id` = `fleet`.`fleet_start_id` " .
        "LEFT JOIN " .
        "{{prefix}}planets AS `destination_planet` " .
        "ON" .
        "`destination_planet`.`id` = `fleet`.`fleet_end_id` " .
        "LEFT JOIN " .
        "{{prefix}}users AS `fleet_owner` " .
        "ON " .
        "`fleet_owner`.`id` = `fleet`.`fleet_owner`" .
        "LEFT JOIN " .
        "{{prefix}}acs AS `get_acs` " .
        "ON " .
        "`get_acs`.`main_fleet_id` = `fleet`.`fleet_id`" .
        "WHERE " .
        $conditions .
        "; -- fetchCurrentFlights|GetFleets"
    );

    return doquery($fetchFleetsQuery, 'fleets');
}

?>
