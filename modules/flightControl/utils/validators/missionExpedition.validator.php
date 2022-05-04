<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param array $props['fleetEntry']
 */
function validateMissionExpedition($validationParams) {
    $validator = function ($input, $resultHelpers) {
        $fleetEntry = $input['fleetEntry'];

        $availableTimeOptions = FlightControl\Utils\Helpers\getAvailableExpeditionTimes();

        if (!(in_array($fleetEntry['ExpeTime'], $availableTimeOptions))) {
            return $resultHelpers['createFailure']([
                'code' => 'INVALID_EXPEDITION_TIME',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($validationParams);
}

?>
