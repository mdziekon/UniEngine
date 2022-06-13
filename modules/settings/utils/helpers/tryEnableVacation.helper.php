<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Helpers;

use UniEngine\Engine\Modules\Settings;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param number $params['currentTimestamp']
 */
function tryEnableVacation($params) {
    $executor = function ($input, $resultHelpers) {
        $user = &$input['user'];
        $currentTimestamp = $input['currentTimestamp'];

        $userId = $user['id'];
        $nextVacationAvailableAt = ($user['vacation_leavetime'] + TIME_DAY);

        if ($currentTimestamp <= $nextVacationAvailableAt) {
            return $resultHelpers['createFailure']([
                'code' => 'VACATION_MODE_NOT_AVAILABLE_YET',
                'params' => [
                    'nextAvailableAt' => $nextVacationAvailableAt,
                ],
            ]);
        }

        $movingFleetsCount = Settings\Utils\Queries\getMovingFleetsCount([ 'userId' => $userId ]);

        if ($movingFleetsCount > 0) {
            return $resultHelpers['createFailure']([
                'code' => 'VACATION_CANNOT_START_FLYING_FLEETS',
            ]);
        }

        $userPlanets = doquery("SELECT * FROM {{table}} WHERE `id_owner` = {$userId};", 'planets');
        $updateResults = [
            'planets' => [],
        ];
        $planetLabelDetails = [];
        $blockingPlanets = [];

        mapQueryResults($userPlanets, function ($planet) use (&$user, $currentTimestamp, &$updateResults, &$planetLabelDetails, &$blockingPlanets) {
            $planetId = $planet['id'];
            $planetLabelDetails[$planetId] = [
                'name' => $planet['name'],
                'galaxy' => $planet['galaxy'],
                'system' => $planet['system'],
                'planet' => $planet['planet'],
            ];;

            $hasPlanetBeenUpdated = HandlePlanetUpdate($planet, $user, $currentTimestamp, true) === true;

            if ($hasPlanetBeenUpdated) {
                $updateResults['planets'][] = $planet;
            }
            if (
                $planet['buildQueue_firstEndTime'] > 0 ||
                $planet['shipyardQueue'] != 0
            ) {
                $blockingPlanets[$planetId] = $planetLabelDetails[$planetId];
            }
        });

        HandlePlanetUpdate_MultiUpdate($updateResults, $user);

        if ($user['techQueue_EndTime'] > 0) {
            $techPlanetId = $user['techQueue_Planet'];
            $blockingPlanets[$techPlanetId] = $planetLabelDetails[$techPlanetId];
        }

        if (!empty($blockingPlanets)) {
            return $resultHelpers['createFailure']([
                'code' => 'VACATION_CANNOT_START_DEVELOPMENT',
                'params' => [
                    'blockingPlanets' => $blockingPlanets,
                ],
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($executor)($params);
}

?>
