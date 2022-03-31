<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

use UniEngine\Engine\Modules\Flights\Enums;

function validateQuantumGate($validationParams) {
    $disallowedMissionTypes = [
        Enums\FleetMission::Attack,
        Enums\FleetMission::UnitedAttack,
        Enums\FleetMission::Spy,
        Enums\FleetMission::DestroyMoon,
    ];

    $validator = function ($input, $resultHelpers) use ($disallowedMissionTypes) {
        $fleet = &$input['fleet'];
        $originPlanet = &$input['originPlanet'];
        $targetPlanet = &$input['targetPlanet'];
        $targetData = &$input['targetData'];
        $isTargetOccupied = $input['isTargetOccupied'];
        $isTargetOwnPlanet = $input['isTargetOwnPlanet'];
        $isTargetOwnedByFriend = $input['isTargetOwnedByFriend'];
        $isTargetOwnedByFriendlyMerchant = $input['isTargetOwnedByFriendlyMerchant'];
        $currentTimestamp = $input['currentTimestamp'];

        if ($originPlanet['quantumgate'] != 1) {
            return $resultHelpers['createFailure']([
                'code' => 'PLANET_NO_QUANTUM_GATE'
            ]);
        }
        if (in_array($fleet['Mission'], $disallowedMissionTypes)) {
            return $resultHelpers['createFailure']([
                'code' => 'FLEET_MISSION_DISALLOWED'
            ]);
        }

        if (
            $isTargetOccupied &&
            $targetPlanet['quantumgate'] == 1 &&
            (
                $isTargetOwnPlanet ||
                $isTargetOwnedByFriend ||
                $isTargetOwnedByFriendlyMerchant
            )
        ) {
            return $resultHelpers['createSuccess']([
                'useType' => 1
            ]);
        }

        if ($originPlanet['galaxy'] != $targetData['galaxy']) {
            return $resultHelpers['createFailure']([
                'code' => 'TARGET_PLANET_NOT_SAME_GALAXY'
            ]);
        }

        $nextQuantumGateAllowedUseAt = (
            $originPlanet['quantumgate_lastuse'] +
            (QUANTUMGATE_INTERVAL_HOURS * 60 * 60)
        );

        if ($nextQuantumGateAllowedUseAt > $currentTimestamp) {
            return $resultHelpers['createFailure']([
                'code' => 'ORIGIN_PLANET_QUANTUMGATE_NOT_READY',
                'params' => [
                    'nextUseAt' => $nextQuantumGateAllowedUseAt,
                ],
            ]);
        }

        return $resultHelpers['createSuccess']([
            'useType' => 2
        ]);
    };

    return createFuncWithResultHelpers($validator)($validationParams);
}

?>
