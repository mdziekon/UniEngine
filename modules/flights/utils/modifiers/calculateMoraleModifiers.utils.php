<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Modifiers;

/**
 * @param array $props
 * @param number $props['moraleLevel']
 */
function calculateMoraleCombatModifiers ($props) {
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

/**
 * @param array $props
 * @param number $props['mainAttackerMoraleLevel']
 * @param number $props['mainDefenderMoraleLevel']
 * @param boolean $props['isMainDefenderIdle']
 * @param boolean $props['isTargetAbandoned']
 * @param boolean $props['areBonusModifiersDisabled'] Default: false
 */
function calculateMoralePillageModifiers ($props) {
    $mainAttackerMoraleLevel = $props['mainAttackerMoraleLevel'];
    $mainDefenderMoraleLevel = $props['mainDefenderMoraleLevel'];
    $isMainDefenderIdle = $props['isMainDefenderIdle'];
    $isTargetAbandoned = $props['isTargetAbandoned'];
    $areBonusModifiersDisabled = (
        isset($props['areBonusModifiersDisabled']) ?
            $props['areBonusModifiersDisabled'] :
            false
    );

    $modifiers = [];
    $pillageModifiers = [];

    if (!$areBonusModifiersDisabled) {
        if (
            $mainAttackerMoraleLevel >= MORALE_BONUS_SOLOIDLERSTEAL &&
            $isMainDefenderIdle
        ) {
            $pillageModifiers[] = MORALE_BONUS_SOLOIDLERSTEAL_STEALPERCENT;
        }
    }

    if (
        !$isTargetAbandoned &&
        $mainDefenderMoraleLevel <= MORALE_PENALTY_RESOURCELOSE
    ) {
        $pillageModifiers[] = MORALE_PENALTY_RESOURCELOSE_STEALPERCENT;
    }
    if ($mainAttackerMoraleLevel <= MORALE_PENALTY_STEAL) {
        $pillageModifiers[] = MORALE_PENALTY_STEAL_STEALPERCENT;
    } else if (
        $mainAttackerMoraleLevel <= MORALE_PENALTY_IDLERSTEAL &&
        $isMainDefenderIdle
    ) {
        $pillageModifiers[] = MORALE_PENALTY_IDLERSTEAL_STEALPERCENT;
    }

    if (!empty($pillageModifiers)) {
        $modifiers['pillageFactor'] = (
            (
                array_sum($pillageModifiers) /
                count($pillageModifiers)
            ) /
            100
        );
    }

    return $modifiers;
}

?>
