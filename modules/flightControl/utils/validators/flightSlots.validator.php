<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

use UniEngine\Engine\Modules\Flights;
use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param object $props['user']
 * @param object $props['fleetEntry']
 * @param object $props['fleetsInFlightCounters']
 * @param number $props['currentTimestamp']
 */
function validateFlightSlots($validationParams) {
    $validator = function ($input, $resultHelpers) {
        $user = $input['user'];
        $fleetEntry = $input['fleetEntry'];
        $fleetsInFlightCounters = $input['fleetsInFlightCounters'];
        $currentTimestamp = $input['currentTimestamp'];

        $maxAllFleetsSlots = FlightControl\Utils\Helpers\getUserFleetSlotsCount([
            'user' => $user,
            'timestamp' => $currentTimestamp,
        ]);
        $maxExpeditionFleetsSlots = FlightControl\Utils\Helpers\getUserExpeditionSlotsCount([
            'user' => $user,
        ]);

        $currentAllFleetsCount = $fleetsInFlightCounters['allFleetsInFlight'];
        $currentExpeditionFleetsCount = $fleetsInFlightCounters['expeditionsInFlight'];

        if ($currentAllFleetsCount >= $maxAllFleetsSlots) {
            return $resultHelpers['createFailure']([
                'code' => 'MAX_FLIGHTS_LIMIT_REACHED',
            ]);
        }
        if (
            $fleetEntry['Mission'] == Flights\Enums\FleetMission::Expedition &&
            $currentExpeditionFleetsCount >= $maxExpeditionFleetsSlots
        ) {
            return $resultHelpers['createFailure']([
                'code' => 'MAX_EXPEDITIONS_LIMIT_REACHED',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($validationParams);
}

?>
