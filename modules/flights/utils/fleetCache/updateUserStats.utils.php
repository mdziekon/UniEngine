<?php

namespace UniEngine\Engine\Modules\Flights\Utils\FleetCache;

// use UniEngine\Engine\Common\Exceptions;
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

abstract class _CombatParticipantUserType {
    const Attacker = 0;
    const Defender = 1;
}

function _getCombatResultStatKey($combatResultType, $userType) {
    switch ($combatResultType) {
        case COMBAT_DRAW:
            return 'raids_draw';
        case COMBAT_ATK:
            return (
                $userType === _CombatParticipantUserType::Attacker ?
                    'raids_won' :
                    'raids_lost'
            );
        case COMBAT_DEF:
            return (
                $userType === _CombatParticipantUserType::Defender ?
                    'raids_won' :
                    'raids_lost'
            );
        default:
            // throw new Exceptions\UniEngineException("Invalid combat result type '{$combatResultType}'");
            return '';
    }
}

/**
 * @param array $params
 * @param ref $params['userStats']
 * @param string $params['combatResultType']
 * @param array $params['attackerIDs']
 * @param array $params['defenderIDs']
 */
function applyCombatResultStats($params) {
    $userStats = &$params['userStats'];
    $combatResultType = $params['combatResultType'];
    $attackerIDs = $params['attackerIDs'];
    $defenderIDs = $params['defenderIDs'];

    $attackersCombatResultStatKey = _getCombatResultStatKey(
        $combatResultType,
        _CombatParticipantUserType::Attacker
    );
    $defendersCombatResultStatKey = _getCombatResultStatKey(
        $combatResultType,
        _CombatParticipantUserType::Defender
    );

    foreach ($attackerIDs as $userID) {
        $userStats[$userID][$attackersCombatResultStatKey] += 1;
    }
    foreach ($defenderIDs as $userID) {
        $userStats[$userID][$defendersCombatResultStatKey] += 1;
    }
}

?>
