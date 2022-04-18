<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;

//  Arguments
//      - $props (Object)
//          - userId (String)
//
function fetchUserFleets ($props) {
    $userId = $props['userId'];

    $fetchQuery = (
        "SELECT " .
        "`fleet_id`, `fleet_mess`, `fleet_mission`, `fleet_array`, `fleet_amount`, " .
        "`fleet_start_time`, `fleet_end_time`, `fleet_end_stay`, `fleet_send_time`, " .
        "`fleet_start_galaxy`, `fleet_start_system`, `fleet_start_planet`, `fleet_start_type`, " .
        "`fleet_end_stay`, `fleet_end_galaxy`, `fleet_end_system`, `fleet_end_planet`, `fleet_end_type`, " .
        "`fleet_resource_metal`, `fleet_resource_crystal`, `fleet_resource_deuterium` " .
        "FROM {{table}} " .
        "WHERE " .
        "`fleet_owner` = {$userId} " .
        "ORDER BY " .
        "`fleet_start_time` ASC, " .
        "`fleet_id` ASC " .
        "; "
    );

    return doquery($fetchQuery, 'fleets');
}

//  Arguments
//      - $props (Object)
//          - userId (String)
//
function fetchRelatedAcsUnions ($props) {
    $userId = $props['userId'];

    $fetchQuery = (
        "SELECT " .
        "`t`.`id`, `t`.`main_fleet_id`, `t`.`owner_id`, `t`.`fleets_id`, `t`.`start_time`, " .
        "`t`.`end_galaxy`, `t`.`end_system`, `t`.`end_planet`, `t`.`end_type`, " .
        "`userst`.`username`, " .
        "`fleets`.`fleet_amount`, `fleets`.`fleet_array`, " .
        "`fleet_start_galaxy`, `fleet_start_system`, `fleet_start_planet`, `fleet_start_type`, " .
        "`fleet_start_time`, `fleets`.`fleet_send_time` " .
        "FROM {{table}} AS `t` " .
        "LEFT JOIN " .
        "{{prefix}}users as `userst` " .
        "ON " .
        "`owner_id` = `userst`.`id` " .
        "LEFT JOIN {{prefix}}fleets as `fleets` " .
        "ON " .
        "`main_fleet_id` = `fleets`.`fleet_id` " .
        "WHERE " .
        "(" .
            "`users` LIKE '%|{$userId}|%' OR " .
            "`owner_id` = {$userId} " .
        ") AND " .
        "`t`.`start_time` > UNIX_TIMESTAMP() " .
        "ORDER BY " .
        "`t`.`id` ASC " .
        "; "
    );

    return doquery($fetchQuery, 'acs');
}

//  Arguments
//      - $props (Object)
//          - fleetIds (String[])
//
function fetchRelatedAcsFleetsSquadDetails ($props) {
    $fleetIds = $props['fleetIds'];
    $fleetIdsJoined = implode(', ', $fleetIds);

    $fetchQuery = (
        "SELECT " .
        "`fleet_id`, `fleet_array`, `fleet_amount` " .
        "FROM {{table}} " .
        "WHERE " .
        "`fleet_id` IN ({$fleetIdsJoined}) " .
        "; "
    );

    return doquery($fetchQuery, 'fleets');
}

?>
