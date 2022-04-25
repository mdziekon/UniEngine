<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Factories;

use UniEngine\Engine\Modules\Flights\Enums\FleetMission;

/**
 * @param array $props
 * @param array $props['availableMissions']
 * @param boolean $props['canUseQuantumGate']
 * @param boolean $props['canUseQuantumGateJump']
 */
function createQuantumGateFuelJSObject($props) {
    $availableMissions = $props['availableMissions'];
    $canUseQuantumGate = $props['canUseQuantumGate'];
    $canUseQuantumGateJump = $props['canUseQuantumGateJump'];

    $fuelRecord = object_map(
        $availableMissions,
        function ($missionType) use ($canUseQuantumGate, $canUseQuantumGateJump) {
            if (
                !$canUseQuantumGate ||
                $missionType == FleetMission::Attack ||
                $missionType == FleetMission::UnitedAttack ||
                $missionType == FleetMission::Spy ||
                $missionType == FleetMission::DestroyMoon
            ) {
                return [
                    0,
                    $missionType,
                ];
            }

            if (
                $canUseQuantumGateJump &&
                (
                    $missionType == FleetMission::Transport ||
                    $missionType == FleetMission::Station ||
                    $missionType == FleetMission::Hold
                )
            ) {
                return [
                    2,
                    $missionType,
                ];
            }

            return [
                1,
                $missionType,
            ];
        }
    );

    return $fuelRecord;
}

?>
