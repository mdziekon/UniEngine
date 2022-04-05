<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Updaters;

/**
 * @param object $props
 * @param object $props['ownerUser']
 * @param object $props['ownerPlanet']
 * @param object $props['fleetEntry']
 * @param object $props['targetPlanet']
 * @param object $props['targetCoords']
 * @param number $props['currentTime']
 */
function insertFleetEntry ($props) {
    $fleetArray = Array2String($props['fleetEntry']['array']);
    $targetId = (
        !empty($props['targetPlanet']['id']) ?
            $props['targetPlanet']['id'] :
            '0'
    );
    $targetOwnerId = (
        !empty($props['targetPlanet']['owner']) ?
            $props['targetPlanet']['owner'] :
            '0'
    );
    $targetGalaxyId = (
        !empty($props['targetPlanet']['galaxy_id']) ?
            $props['targetPlanet']['galaxy_id'] :
            '0'
    );

    $query = (
        "INSERT INTO {{table}} " .
        "SET " .
        "`fleet_owner` = {$props['ownerUser']['id']}, " .
        "`fleet_mission` = {$props['fleetEntry']['Mission']}, " .
        "`fleet_amount` = {$props['fleetEntry']['count']}, " .
        "`fleet_array` = '{$fleetArray}', " .
        "`fleet_start_time` = {$props['fleetEntry']['SetCalcTime']}, " .
        "`fleet_start_id` = {$props['ownerPlanet']['id']}, " .
        "`fleet_start_galaxy` = {$props['ownerPlanet']['galaxy']}, " .
        "`fleet_start_system` = {$props['ownerPlanet']['system']}, " .
        "`fleet_start_planet` = {$props['ownerPlanet']['planet']}, " .
        "`fleet_start_type` = {$props['ownerPlanet']['planet_type']}, " .
        "`fleet_end_time` = {$props['fleetEntry']['SetBackTime']}, " .
        "`fleet_end_id` = {$targetId}, " .
        "`fleet_end_id_galaxy` = {$targetGalaxyId}, " .
        "`fleet_end_stay` = {$props['fleetEntry']['SetStayTime']}, " .
        "`fleet_end_galaxy` = {$props['targetCoords']['galaxy']}, " .
        "`fleet_end_system` = {$props['targetCoords']['system']}, " .
        "`fleet_end_planet` = {$props['targetCoords']['planet']}, " .
        "`fleet_end_type` = {$props['targetCoords']['type']}, " .
        "`fleet_resource_metal` = {$props['fleetEntry']['resources']['metal']}, " .
        "`fleet_resource_crystal` = {$props['fleetEntry']['resources']['crystal']}, " .
        "`fleet_resource_deuterium` = {$props['fleetEntry']['resources']['deuterium']}, " .
        "`fleet_target_owner` = {$targetOwnerId}, " .
        "`fleet_send_time` = {$props['currentTime']} " .
        ";"
    );

    doquery($query, 'fleets');

    return getLastInsertId();
}

?>
