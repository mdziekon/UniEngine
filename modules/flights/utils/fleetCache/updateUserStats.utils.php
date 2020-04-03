<?php

namespace UniEngine\Engine\Modules\Flights\Utils\FleetCache;

use UniEngine\Engine\Includes\Helpers\World\Elements;

abstract class WorldElementCounterType {
    const ElementDestroyed = 0;
    const ElementLost = 1;
}

/**
 * @param array $params
 * @param &array $params['userStats']
 * @param string $params['userID']
 * @param number $params['elementID']
 * @param number $params['elementCount']
 * @param WorldElementCounterType $params['counterType']
 */
function incrementUserStatsWorldElementCounter($params) {
    $elementID = $params['elementID'];

    if (
        !Elements\isShip($elementID) &&
        !Elements\isDefenseSystem($elementID)
    ) {
        return;
    }

    $entryKey = (
        $params['counterType'] === WorldElementCounterType::ElementDestroyed ?
        "destroyed_{$elementID}" :
        "lost_{$elementID}"
    );

    _incrementUserStatsEntry(
        $params['userStats'],
        $params['userID'],
        $entryKey,
        $params['elementCount']
    );
}

function _incrementUserStatsEntry(
    &$userStatsObj,
    $userID,
    $entryKey,
    $incrementValue
) {
    $userStatsObj[$userID][$entryKey] += $incrementValue;
}

/**
 * @param array $params
 * @param &array $params['userStats']
 * @param array $params['combatShotdownResult']
 * @param number $params['mainAttackerUserID']
 * @param number $params['mainDefenderUserID']
 * @param array $params['attackingFleetIDs']
 * @param array $params['attackingFleetOwnerIDs']
 * @param array $params['defendingFleetIDs']
 * @param array $params['defendingFleetOwnerIDs']
 */
function applyCombatUnitStats($params) {
    if (empty($params['combatShotdownResult'])) {
        return;
    }

    $mainAttackerUserID = $params['mainAttackerUserID'];
    $mainDefenderUserID = $params['mainDefenderUserID'];
    $attackingFleetIDs = $params['attackingFleetIDs'];
    $attackingFleetOwnerIDs = $params['attackingFleetOwnerIDs'];
    $defendingFleetIDs = $params['defendingFleetIDs'];
    $defendingFleetOwnerIDs = $params['defendingFleetOwnerIDs'];

    foreach ($params['combatShotdownResult'] as $usersType => $usersResults) {
        $areUsersAttackers = ($usersType === 'atk');

        foreach ($usersResults as $resultsCounterType => $elementsData) {
            $counterType = (
                $resultsCounterType == 'd' ?
                WorldElementCounterType::ElementDestroyed :
                WorldElementCounterType::ElementLost
            );

            foreach($elementsData as $fleetIdx => $elements) {
                $fleetOwnerID = (
                    $fleetIdx == 0 ?
                    (
                        $areUsersAttackers ?
                        $mainAttackerUserID :
                        $mainDefenderUserID
                    ) :
                    (
                        $areUsersAttackers ?
                        $attackingFleetOwnerIDs[$attackingFleetIDs[$fleetIdx]] :
                        $defendingFleetOwnerIDs[$defendingFleetIDs[$fleetIdx]]
                    )
                );

                foreach ($elements as $elementID => $elementCount) {
                    incrementUserStatsWorldElementCounter([
                        'userStats' => &$params['userStats'],
                        'userID' => $fleetOwnerID,
                        'elementID' => $elementID,
                        'elementCount' => $elementCount,
                        'counterType' => $counterType,
                    ]);
                }
            }
        }
    }
}

?>
