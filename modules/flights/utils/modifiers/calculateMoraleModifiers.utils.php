<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Modifiers;

/**
 * @param array $props
 * @param number $props['moraleLevel']
 */
function calculateMoraleModifiers ($props) {
    $moraleLevel = $props['moraleLevel'];

    $moraleCombatModifiers = [];

    if ($moraleLevel >= MORALE_BONUS_FLEETPOWERUP1) {
        $moraleCombatModifiers['TotalForceFactor'] = MORALE_BONUS_FLEETPOWERUP1_FACTOR;
    }
    if ($moraleLevel >= MORALE_BONUS_FLEETSHIELDUP1) {
        $moraleCombatModifiers['TotalShieldFactor'] = MORALE_BONUS_FLEETSHIELDUP1_FACTOR;
    }
    if ($moraleLevel >= MORALE_BONUS_FLEETSDADDITION) {
        $moraleCombatModifiers['SDAdd'] = MORALE_BONUS_FLEETSDADDITION_VALUE;
    }
    // Penalties
    if ($moraleLevel <= MORALE_PENALTY_FLEETPOWERDOWN1) {
        $moraleCombatModifiers['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN1_FACTOR;
    }
    if ($moraleLevel <= MORALE_PENALTY_FLEETPOWERDOWN2) {
        $moraleCombatModifiers['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN2_FACTOR;
    }
    if ($moraleLevel <= MORALE_PENALTY_FLEETSHIELDDOWN1) {
        $moraleCombatModifiers['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN1_FACTOR;
    }
    if ($moraleLevel <= MORALE_PENALTY_FLEETSHIELDDOWN2) {
        $moraleCombatModifiers['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN2_FACTOR;
    }
    if ($moraleLevel <= MORALE_PENALTY_FLEETSDDOWN) {
        $moraleCombatModifiers['SDFactor'] = MORALE_PENALTY_FLEETSDDOWN_FACTOR;
    }

    return $moraleCombatModifiers;
}

?>
