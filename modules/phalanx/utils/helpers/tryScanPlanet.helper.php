<?php

namespace UniEngine\Engine\Modules\Phalanx\Utils\Helpers;

use UniEngine\Engine\Includes\Helpers\World;
use UniEngine\Engine\Modules\Flights;
use UniEngine\Engine\Modules\Phalanx;

/**
 * @note This function has side-effects when it calculates the World's Flights
 *
 * @param array $params
 * @param array $params['targetCoords']
 * @param arrayRef $params['phalanxMoon']
 * @param arrayRef $params['currentUser']
 * @param number $params['scanCost']
 */
function tryScanPlanet($params) {
    $executor = function ($input, $resultHelpers) {
        $targetCoords = $input['targetCoords'];
        $phalanxMoon = &$input['phalanxMoon'];
        $currentUser = &$input['currentUser'];
        $scanCost = $input['scanCost'];

        $isUserAllowedToBypassRangeChecks = CheckAuth('supportadmin', AUTHCHECK_NORMAL, $currentUser);
        $phalanxLevel = $phalanxMoon['sensor_phalanx'];

        if ($phalanxMoon['planet_type'] != World\Enums\PlanetType::Moon) {
            return $resultHelpers['createFailure']([
                'code' => 'SCAN_ATTEMPT_NOT_FROM_MOON',
            ]);
        }
        if (
            $phalanxLevel <= 0 &&
            !$isUserAllowedToBypassRangeChecks
        ) {
            return $resultHelpers['createFailure']([
                'code' => 'PHALANX_NOT_PRESENT',
            ]);
        }

        $isValidCoordinate = Flights\Utils\Checks\isValidCoordinate([
            'coordinate' => $targetCoords,
            'areExpeditionsExcluded' => true,
        ]);

        if (!$isValidCoordinate['isValid']) {
            return $resultHelpers['createFailure']([
                'code' => 'SCAN_TARGET_COORDS_INVALID',
            ]);
        }

        if (
            $targetCoords['galaxy'] != $phalanxMoon['galaxy'] &&
            !$isUserAllowedToBypassRangeChecks
        ) {
            return $resultHelpers['createFailure']([
                'code' => 'SCAN_TARGET_OUT_OF_RANGE_GALAXY',
            ]);
        }

        $isInPhalanxSystemRange = World\Checks\isTargetInRange([
            'originPosition' => $phalanxMoon['system'],
            'targetPosition' => $targetCoords['system'],
            'range' => GetPhalanxRange($phalanxLevel),
        ]);

        if (
            !$isInPhalanxSystemRange &&
            !$isUserAllowedToBypassRangeChecks
        ) {
            return $resultHelpers['createFailure']([
                'code' => 'SCAN_TARGET_OUT_OF_RANGE_SYSTEM',
            ]);
        }

        $targetDetails = Phalanx\Utils\Queries\getTargetDetails([
            'targetCoords' => $targetCoords,
        ]);

        if (!$targetDetails) {
            return $resultHelpers['createFailure']([
                'code' => 'TARGET_EMPTY',
            ]);
        }

        $originalPhalanxMoonId = $phalanxMoon['id'];

        FlyingFleetHandler($phalanxMoon, [ $targetDetails['id'] ]);

        if ($phalanxMoon['id'] != $originalPhalanxMoonId) {
            return $resultHelpers['createFailure']([
                'code' => 'PHALANX_MOON_DESTROYED',
            ]);
        }

        if ($phalanxMoon['deuterium'] < $scanCost) {
            return $resultHelpers['createFailure']([
                'code' => 'NOT_ENOUGH_FUEL',
                'params' => [
                    'scanCost' => $scanCost,
                ],
            ]);
        }

        return $resultHelpers['createSuccess']([
            'targetDetails' => $targetDetails,
        ]);
    };

    return createFuncWithResultHelpers($executor)($params);
}

?>
