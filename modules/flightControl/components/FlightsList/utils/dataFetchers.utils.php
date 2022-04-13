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
        "FROM {{table}}  " .
        "WHERE " .
        "`fleet_owner` = {$userId} " .
        "ORDER BY " .
        "`fleet_start_time` ASC, " .
        "`fleet_id` ASC " .
        "; "
    );

    return doquery($fetchQuery, 'fleets');
}

?>
