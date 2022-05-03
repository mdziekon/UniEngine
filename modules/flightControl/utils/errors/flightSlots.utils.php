<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Errors;

/**
 * @param object $error As returned by FlightControl\Utils\Validators\validateFlightSlots
 */
function mapFlightSlotsValidationErrorToReadableMessage($error, $mapperParams) {
    global $_Lang;

    $errorCode = $error['code'];

    $knownErrorsByCode = [
        'MAX_FLIGHTS_LIMIT_REACHED' => $_Lang['fl3_NoMoreFreeSlots'],
        'MAX_EXPEDITIONS_LIMIT_REACHED' => $_Lang['fl3_NoMoreFreeExpedSlots'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['fleet_generic_errors_unknown'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
