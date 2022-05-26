<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Errors;

/**
 * @param object $error As returned by FlightControl\Utils\Validators\parseFleetArray
 */
function mapFleetArrayValidationErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['errorCode'];

    $knownErrorsByCode = [
        'INVALID_SHIP_ID'               => $_Lang['fl1_BadShipGiven'],
        'SHIP_WITH_NO_ENGINE'           => $_Lang['fl1_CantSendUnflyable'],
        'INVALID_SHIP_COUNT'            => $_Lang['fleet_generic_errors_invalidshipcount'],
        'SHIP_COUNT_EXCEEDS_AVAILABLE'  => $_Lang['fl1_NoEnoughShips'],
        'NO_SHIPS'                      => $_Lang['fl1_NoShipsGiven'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['fleet_generic_errors_unknown'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
