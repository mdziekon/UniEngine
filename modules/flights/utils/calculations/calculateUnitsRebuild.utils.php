<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Calculations;

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Includes\Helpers\World\Elements;

/**
 * @param array $params
 * @param array $params['originalShips']
 * @param array $params['postCombatShips']
 * @param array $params['fleetRow']
 * @param array $params['targetUser']
 *
 * @todo The algorithm implemented below does not represent the real life behaviour
 *       of Ogame-like rebuild algorithm, which calculates the rebuild chance
 *       for each singular lost unit separately. This approach would lead to
 *       bad performance in high unit count scenarios.
 *       Investigate whether it's possible to apply statistical methods to achieve
 *       similar to Ogame-like games results.
 */
function calculateUnitsRebuild($params) {
    $originalShips = $params['originalShips'];
    $postCombatShips = $params['postCombatShips'];
    $fleetRow = $params['fleetRow'];
    $targetUser = $params['targetUser'];

    $isOfficerEngineerActive = ($targetUser['engineer_time'] >= $fleetRow['fleet_start_time']);

    return Collections\map(
        $originalShips,
        function ($shipOriginalCount, $shipID) use ($postCombatShips, $isOfficerEngineerActive) {
            if (!Elements\isDefenseSystem($shipID)) {
                return 0;
            }

            $shipPostCombatCount = (
                isset($postCombatShips[$shipID]) ?
                    $postCombatShips[$shipID] :
                    0
            );
            $shipLostCount = ($shipOriginalCount - $shipPostCombatCount);

            if ($shipLostCount === 0) {
                return 0;
            }

            $shipRebuildPercentage = _calculateRebuildPercentage([
                'isOfficerEngineerActive' => $isOfficerEngineerActive,
            ]);

            return round(
                $shipLostCount *
                ($shipRebuildPercentage / 100)
            );
        }
    );
}

/**
 * @param array $params
 * @param boolean $params['isOfficerEngineerActive']
 */
function _calculateRebuildPercentage($params) {
    $isOfficerEngineerActive = $params['isOfficerEngineerActive'];

    $CONST_REBUILD_CHANCE_BASE_MIN = 60;
    $CONST_REBUILD_CHANCE_BASE_MAX = 80;
    $CONST_REBUILD_CHANCE_BONUS_MAX_OFFICER_ENGINEER = 20;

    $shipRebuildChance = mt_rand(
        $CONST_REBUILD_CHANCE_BASE_MIN,
        (
            $CONST_REBUILD_CHANCE_BASE_MAX +
            (
                $isOfficerEngineerActive ?
                $CONST_REBUILD_CHANCE_BONUS_MAX_OFFICER_ENGINEER :
                0
            )
        )
    );
    $shipRebuildChanceFluctuation = min(mt_rand(-15, 15), 0);
    $shipRebuildPercentage = max(
        min(
            ($shipRebuildChance + $shipRebuildChanceFluctuation),
            100
        ),
        0
    );

    return $shipRebuildPercentage;
}


?>
