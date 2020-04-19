<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Factories;

abstract class MoraleEntryUserType {
    const Attacker = 'atk';
    const Defender = 'def';
}

/**
 * @param array $params
 * @param enum $params['userType']
 * @param enum $params['updateType']
 * @param number $params['combatMoraleFactor']
 * @param number $params['newMoraleLevel']
 */
function createCombatReportMoraleEntry($params) {
    return [
        'usertype' => $params['userType'],
        'type' => $params['updateType'],
        'factor' => $params['combatMoraleFactor'],
        'level' => $params['newMoraleLevel']
    ];
}

?>
