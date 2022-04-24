<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Updaters;

/**
 * @param object $props
 * @param string $props['unionName']
 * @param object $props['fleetIds']
 */
function createUnionEntry($props) {
    $unionName = $props['unionName'];
    $mainFleetEntry = $props['mainFleetEntry'];

    $query = (
        "INSERT INTO {{table}} " .
        "SET " .
        "`name` = '{$unionName}',  " .
        "`main_fleet_id` = {$mainFleetEntry['fleet_id']},  " .
        "`owner_id` = {$mainFleetEntry['fleet_owner']}, " .
        "`start_time_org` = {$mainFleetEntry['fleet_start_time']}, " .
        "`start_time` = {$mainFleetEntry['fleet_start_time']}, " .
        "`end_target_id` = {$mainFleetEntry['fleet_end_id']}, " .
        "`end_galaxy` = {$mainFleetEntry['fleet_end_galaxy']}, " .
        "`end_system` = {$mainFleetEntry['fleet_end_system']}, " .
        "`end_planet` = {$mainFleetEntry['fleet_end_planet']}, " .
        "`end_type` = {$mainFleetEntry['fleet_end_type']} " .
        ";"
    );

    doquery($query, 'acs');

    $unionId = getLastInsertId();

    return [
        'id' => $unionId,
        'name' => $unionName,
        'main_fleet_id' => $mainFleetEntry['fleet_id'],
        'owner_id' => $mainFleetEntry['fleet_owner'],
        'start_time_org' => $mainFleetEntry['fleet_start_time'],
        'start_time' => $mainFleetEntry['fleet_start_time'],
        'end_target_id' => $mainFleetEntry['fleet_end_id'],
        'end_galaxy' => $mainFleetEntry['fleet_end_galaxy'],
        'end_system' => $mainFleetEntry['fleet_end_system'],
        'end_planet' => $mainFleetEntry['fleet_end_planet'],
        'end_type' => $mainFleetEntry['fleet_end_type'],
    ];
}

?>
