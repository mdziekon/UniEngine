<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Calculations;

use UniEngine\Engine\Modules\Flights;

/**
 * @param array $params
 * @param number $params['mainAttackerMoraleLevel']
 * @param number $params['mainDefenderMoraleLevel']
 * @param boolean $params['isMainDefenderIdle']
 * @param boolean $params['isTargetAbandoned']
 * @param array $params['attackerIDs']
 */
function calculatePillageFactor($params) {
    $basePillageFactor = _getBasePillageFactor();

    if (!MORALE_ENABLED) {
        return $basePillageFactor;
    }

    $moraleModifiedPillageFactor = _getMoraleModifiedPillageFactor($params);

    if ($moraleModifiedPillageFactor === null) {
        return $basePillageFactor;
    }

    return $moraleModifiedPillageFactor;
}

function _getBasePillageFactor() {
    return (COMBAT_RESOURCESTEAL_PERCENT / 100);
}

/**
 * @see calculatePillageFactor()
 */
function _getMoraleModifiedPillageFactor($params) {
    $hasOnlyOneAttacker = (count($params['attackerIDs']) === 1);

    $moralePillageModifiers = Flights\Utils\Modifiers\calculateMoralePillageModifiers([
        'mainAttackerMoraleLevel' => $params['mainAttackerMoraleLevel'],
        'mainDefenderMoraleLevel' => $params['mainDefenderMoraleLevel'],
        'isMainDefenderIdle' => $params['isMainDefenderIdle'],
        'isTargetAbandoned' => $params['isTargetAbandoned'],
        'areBonusModifiersDisabled' => !$hasOnlyOneAttacker,
    ]);

    if (!isset($moralePillageModifiers['pillageFactor'])) {
        return null;
    }

    return $moralePillageModifiers['pillageFactor'];
}

?>
