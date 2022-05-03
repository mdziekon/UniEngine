<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

/**
 * @param array $props
 * @param object $props['fleetEntry']
 * @param object $props['fleetOriginPlanet']
 */
function validateFleetResources($validationParams) {
    $validator = function ($input, $resultHelpers) {
        $fleetEntry = $input['fleetEntry'];
        $fleetOriginPlanet = $input['fleetOriginPlanet'];

        foreach ($fleetEntry['resources'] as $resourceKey => $resourceAmount) {
            if ($resourceAmount < 0) {
                return $resultHelpers['createFailure']([
                    'code' => 'INVALID_RESOURCE_AMOUNT',
                    'payload' => [
                        'resourceKey' => $resourceKey,
                    ],
                ]);
            }
            if ($resourceAmount > $fleetOriginPlanet[$resourceKey]) {
                return $resultHelpers['createFailure']([
                    'code' => 'ORIGIN_PLANET_INSUFFICIENT_RESOURCE_AMOUNT',
                    'payload' => [
                        'resourceKey' => $resourceKey,
                    ],
                ]);
            }
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($validationParams);
}

?>
