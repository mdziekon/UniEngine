<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param array $props['newFleet']
 */
function validateMissionHold ($props) {
    $isValid = function () {
        return [
            'isValid' => true,
        ];
    };
    $isInvalid = function ($errors) {
        return [
            'isValid' => false,
            'errors' => $errors,
        ];
    };

    $newFleet = $props['newFleet'];

    $availableHoldTimes = FlightControl\Utils\Helpers\getAvailableHoldTimes([]);

    if (!(in_array($newFleet['HoldTime'], $availableHoldTimes))) {
        return $isInvalid([
            [ 'errorCode' => 'INVALID_HOLD_TIME', ],
        ]);
    }

    return $isValid();
}

?>
