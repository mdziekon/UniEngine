<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Errors;

/**
 * @param object $error As returned by FlightControl\Utils\Validators\validateFleetResources
 */
function mapFleetResourcesValidationErrorToReadableMessage($error, $mapperParams) {
    global $_Lang;

    $errorCode = $error['code'];
    $errorParams = $error['payload'];

    $knownErrorsByCode = [
        'INVALID_RESOURCE_AMOUNT' => $_Lang['fl3_BadResourcesGiven'],
        'ORIGIN_PLANET_INSUFFICIENT_RESOURCE_AMOUNT' => function ($params) use (&$_Lang) {
            $errorMessageKey = "fl3_PlanetNoEnough{$params['resourceKey']}";
            $errorMessage = $_Lang[$errorMessageKey];

            return $errorMessage;
        },
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['fleet_generic_errors_unknown'];
    }

    if (is_callable($knownErrorsByCode[$errorCode])) {
        return $knownErrorsByCode[$errorCode]($errorParams);
    }

    return $knownErrorsByCode[$errorCode];
}

?>
