<?php

namespace UniEngine\Engine\Modules\Phalanx\Utils\Errors;

/**
 * @param object $error As returned by Phalanx\Utils\Errors\tryScanPlanet()
 */
function mapTryScanPlanetErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];
    $errorParams = $error['params'];

    $knownErrorsByCode = [
        'SCAN_ATTEMPT_NOT_FROM_MOON'        => $_Lang['PhalanxError_ScanOnlyFromMoon'],
        'PHALANX_NOT_PRESENT'               => $_Lang['PhalanxError_NoPhalanxHere'],
        'SCAN_TARGET_COORDS_INVALID'        => $_Lang['PhalanxError_BadCoordinates'],
        'SCAN_TARGET_OUT_OF_RANGE_GALAXY'   => $_Lang['PhalanxError_GalaxyOutOfRange'],
        'SCAN_TARGET_OUT_OF_RANGE_SYSTEM'   => $_Lang['PhalanxError_TargetOutOfRange'],
        'TARGET_EMPTY'                      => $_Lang['PhalanxError_CoordsEmpty'],
        'PHALANX_MOON_DESTROYED'            => $_Lang['PhalanxError_MoonDestroyed'],
        'NOT_ENOUGH_FUEL'                   => function ($params) use (&$_Lang) {
            return sprintf(
                $_Lang['PhalanxError_NoEnoughFuel'],
                prettyNumber($params['scanCost'])
            );
        },
    ];

    if (is_callable($knownErrorsByCode[$errorCode])) {
        return $knownErrorsByCode[$errorCode]($errorParams);
    }

    return $knownErrorsByCode[$errorCode];
}

?>
