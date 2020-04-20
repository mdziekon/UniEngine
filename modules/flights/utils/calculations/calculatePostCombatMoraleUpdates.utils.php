<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Calculations;

use UniEngine\Engine\Modules\Flights;

/**
 * @param array $params
 * @param enum $params['combatResult']
 * @param array $params['fleetRow']
 * @param array $params['attackersMorale']
 * @param array $params['defendersMorale']
 * @param ref $params['fleetCache']
 */
function calculatePostCombatMoraleUpdates($params) {
    $combatResult = $params['combatResult'];
    $fleetRow = $params['fleetRow'];
    $attackersMorale = $params['attackersMorale'];
    $defendersMorale = $params['defendersMorale'];
    $fleetCache = &$params['fleetCache'];

    $combatAttackersMoraleDetails = Flights\Utils\Calculations\calculateCombatAttackersMoraleDetails([
        'attackersMorale' => $attackersMorale,
        'defendersMorale' => $defendersMorale,
    ]);

    $reportMoraleEntries = [];

    foreach ($attackersMorale as $userID => $moraleRawData) {
        $moraleDetails = $combatAttackersMoraleDetails['attackersFactors'][$userID];
        $moraleFactor = $moraleDetails['factor'];

        if ($moraleFactor <= MORALE_MINIMALFACTOR) {
            continue;
        }

        $moraleUpdateType = (
            $moraleDetails['isAttackerStronger'] ?
                MORALE_NEGATIVE :
                MORALE_POSITIVE
        );

        $hasUpdatedMoraleData = Morale_AddMorale(
            $moraleRawData,
            $moraleUpdateType,
            $moraleFactor,
            1,
            1,
            $fleetRow['fleet_start_time']
        );

        if (!$hasUpdatedMoraleData) {
            continue;
        }

        Flights\Utils\FleetCache\updateMoraleDataCache([
            'fleetCache' => &$fleetCache,
            'userID' => $userID,
            'userMoraleData' => $moraleRawData,
        ]);
        $reportMoraleEntries[$userID] = Flights\Utils\Factories\createCombatReportMoraleEntry([
            'userType' => Flights\Utils\Factories\MoraleEntryUserType::Attacker,
            'updateType' => $moraleUpdateType,
            'combatMoraleFactor' => $moraleFactor,
            'newMoraleLevel' => $moraleRawData['morale_level'],
        ]);
    }

    $combatDefendersMoraleDetails = Flights\Utils\Calculations\calculateCombatDefendersMoraleDetails([
        'combatResult' => $combatResult,
        'attackersFactors' => $combatAttackersMoraleDetails['attackersFactors'],
        'isAttackersUnionSignificantlyStronger' => $combatAttackersMoraleDetails['isAttackersUnionSignificantlyStronger'],
    ]);
    $totalDefendersMoraleFactor = $combatDefendersMoraleDetails['totalDefendersMoraleFactor'];

    foreach ($defendersMorale as $userID => $moraleRawData) {
        if ($totalDefendersMoraleFactor <= 0) {
            continue;
        }

        $moraleLevelMultiplier = (
            $combatResult === COMBAT_DRAW ?
                1 / 2 :
                1
        );
        $moraleTimeMultiplier = (
            $combatResult === COMBAT_DRAW ?
                1 / 2 :
                1
        );
        $moraleUpdateType = MORALE_POSITIVE;

        $hasUpdatedMoraleData = Morale_AddMorale(
            $moraleRawData,
            $moraleUpdateType,
            $totalDefendersMoraleFactor,
            $moraleLevelMultiplier,
            $moraleTimeMultiplier,
            $fleetRow['fleet_start_time']
        );

        if (!$hasUpdatedMoraleData) {
            continue;
        }

        Flights\Utils\FleetCache\updateMoraleDataCache([
            'fleetCache' => &$fleetCache,
            'userID' => $userID,
            'userMoraleData' => $moraleRawData,
        ]);
        $reportMoraleEntries[$userID] = Flights\Utils\Factories\createCombatReportMoraleEntry([
            'userType' => Flights\Utils\Factories\MoraleEntryUserType::Defender,
            'updateType' => $moraleUpdateType,
            'combatMoraleFactor' => $totalDefendersMoraleFactor,
            'newMoraleLevel' => $moraleRawData['morale_level'],
        ]);
    }

    return [
        'reportMoraleEntries' => $reportMoraleEntries,
    ];
}

?>
