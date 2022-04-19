<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Factories;

/**
 * @param array $props
 * @param ref $props['unionOwner']
 * @param object $props['unionEntry']
 */
function createUnionInvitationMessage($props) {
    global $_Lang;

    $unionOwner = $props['unionOwner'];
    $unionEntry = $props['unionEntry'];
    $fleetEntry = $props['fleetEntry'];

    $message = [];
    $message['msg_id'] = '069';
    $message['args'] = [
        $unionOwner['username'],
        (
            ($fleetEntry['fleet_end_type'] == 1) ?
                $_Lang['to_planet'] :
                $_Lang['to_moon']
        ),
        $fleetEntry['fleet_end_target_name'],
        $fleetEntry['fleet_end_galaxy'],
        $fleetEntry['fleet_end_system'],
        $fleetEntry['fleet_end_galaxy'],
        $fleetEntry['fleet_end_system'],
        $fleetEntry['fleet_end_planet'],
        (
            ($fleetEntry['fleet_end_type'] == 1) ?
                $_Lang['to_this_planet'] :
                $_Lang['to_this_moon']
        ),
        $fleetEntry['fleet_end_galaxy'],
        $fleetEntry['fleet_end_system'],
        $fleetEntry['fleet_end_planet'],
        $fleetEntry['fleet_end_type'],
        $unionEntry['id'],
        $unionEntry['name'],
    ];

    return json_encode($message);
}

?>
