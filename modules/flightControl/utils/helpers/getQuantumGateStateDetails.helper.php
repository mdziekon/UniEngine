<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

use UniEngine\Engine\Modules\Flights\Enums\FleetMission;

/**
 * @param array $props
 * @param array $props['availableMissions']
 * @param array $props['originPlanet']
 * @param array $props['targetCoords']
 * @param array $props['targetInfo']
 * @param array $props['targetPlanetDetails']
 * @param number $props['currentTimestamp']
 */
function getQuantumGateStateDetails($props) {
    $availableMissions = $props['availableMissions'];
    $originPlanet = $props['originPlanet'];
    $targetCoords = $props['targetCoords'];
    $targetInfo = $props['targetInfo'];
    $targetPlanetDetails = $props['targetPlanetDetails'];
    $currentTimestamp = $props['currentTimestamp'];

    if (
        empty($availableMissions) ||
        $originPlanet['quantumgate'] != 1
    ) {
        return [
            'canUseQuantumGate' => false,
            'canUseQuantumGateJump' => false,
        ];
    }

    if (
        (
            $targetInfo['isPlanetOwnedByFleetOwner'] ||
            $targetInfo['isPlanetOwnerFriendly'] ||
            $targetInfo['isPlanetOwnerFriendlyMerchant']
        ) &&
        $targetPlanetDetails['quantumgate'] == 1 &&
        (
            in_array(FleetMission::Transport, $availableMissions) ||
            in_array(FleetMission::Station, $availableMissions) ||
            in_array(FleetMission::Hold, $availableMissions)
        )
    ) {
        return [
            'canUseQuantumGate' => true,
            'canUseQuantumGateJump' => true,
        ];
    }

    if (
        $originPlanet['galaxy'] == $targetCoords['galaxy'] &&
        ($originPlanet['quantumgate_lastuse'] + (QUANTUMGATE_INTERVAL_HOURS * 3600)) <= $currentTimestamp
    ) {
        return [
            'canUseQuantumGate' => true,
            'canUseQuantumGateJump' => false,
        ];
    }

    return [
        'canUseQuantumGate' => false,
        'canUseQuantumGateJump' => false,
    ];
}

?>
