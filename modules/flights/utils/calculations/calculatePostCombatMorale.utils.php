<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Calculations;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

/**
 * @param array $params
 * @param array $params['attackersMorale']
 * @param array $params['defendersMorale']
 */
function calculateCombatAttackersMoraleDetails($params) {
    $attackersMorale = $params['attackersMorale'];
    $defendersMorale = $params['defendersMorale'];

    $totalAttackersMoralePoints = 0;
    $totalDefendersMoralePoints = 0;

    foreach ($attackersMorale as $userMoraleData) {
        $totalAttackersMoralePoints += $userMoraleData['morale_points'];
    }
    foreach ($defendersMorale as $userMoraleData) {
        $totalDefendersMoralePoints += $userMoraleData['morale_points'];
    }

    $totalAttackersMoraleFactor = (
        $totalAttackersMoralePoints /
        $totalDefendersMoralePoints
    );
    $isAttackersUnionSignificantlyStronger = ($totalAttackersMoraleFactor > MORALE_MINIMALFACTOR);

    $attackersMoraleFactorGetter = (
        $isAttackersUnionSignificantlyStronger ?
            function ($userMoraleData) use ($totalAttackersMoraleFactor) {
                return [
                    'factor' => $totalAttackersMoraleFactor,
                    'isAttackerStronger' => true,
                ];
            } :
            function ($userMoraleData) use ($totalDefendersMoralePoints) {
                $factor = ($userMoraleData['morale_points'] / $totalDefendersMoralePoints);
                $isUserStronger = ($factor >= 1);

                $normalizedFactor = (
                    $factor >= 1 ?
                    $factor :
                    pow($factor, -1)
                );

                return [
                    'factor' => $normalizedFactor,
                    'isAttackerStronger' => $isUserStronger,
                ];
            }
    );

    return [
        'attackersFactors' => array_map(
            $attackersMoraleFactorGetter,
            $attackersMorale
        ),
        'isAttackersUnionSignificantlyStronger' => $isAttackersUnionSignificantlyStronger,
    ];
}

/**
 * @param array $params
 * @param enum $params['combatResult']
 * @param array $params['attackersFactors']
 * @param boolean $params['isAttackersUnionSignificantlyStronger']
 */
function calculateCombatDefendersMoraleDetails($params) {
    return [
        'totalDefendersMoraleFactor' => _calculateTotalDefendersMoraleFactor($params),
    ];
}

/**
 * @see $params - calculateCombatAttackersMoraleDetails($params)
 */
function _calculateTotalDefendersMoraleFactor($params) {
    if ($params['combatResult'] === COMBAT_ATK) {
        return 0;
    }

    if ($params['isAttackersUnionSignificantlyStronger']) {
        return Collections\firstN($params['attackersFactors'], 1)[0]['factor'];
    }

    return array_reduce(
        $params['attackersFactors'],
        function ($accumulator, $factorObject) {
            if (
                !$factorObject['isAttackerStronger'] ||
                $factorObject['factor'] <= MORALE_MINIMALFACTOR
            ) {
                return $accumulator;
            }

            return $accumulator + $factorObject['factor'];
        },
        0
    );
}

?>
