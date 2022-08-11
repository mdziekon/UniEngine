<?php

namespace UniEngine\Engine\Modules\Info\Components\UnitForce;

use UniEngine\Engine\Modules\Info;

/**
 * @param array $props
 * @param number $props['elementId']
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_Lang, $_Vars_CombatData, $_Vars_GameElements;

    $elementId = $props['elementId'];
    $user = &$props['user'];

    $baseForceValue = $_Vars_CombatData[$elementId]['attack'];

    $forceModifier = (0.1 * $user[$_Vars_GameElements[109]]);

    if (!empty($_Vars_CombatUpgrades[$elementId])) {
        foreach ($_Vars_CombatUpgrades[$elementId] as $weaponTechId => $upgradeRequiredLevel) {
            $currentTechLevel = $user[$_Vars_GameElements[$weaponTechId]];

            if ($currentTechLevel > $upgradeRequiredLevel) {
                $forceModifier += ($currentTechLevel - $upgradeRequiredLevel) * 0.05;
            }
        }
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        [
            'lang_WeaponTypes' => $_Lang['nfo_attack_type'],
            'lang_Force' => $_Lang['nfo_attack_pt'],

            'data_WeaponTypesList' => Info\Components\UnitWeapons\render([
                'elementId' => $elementId,
            ])['componentHTML'],
            'data_ForceBaseValue' => prettyNumber($baseForceValue),
            'data_ForceModifier' => $forceModifier * 100,
            'data_ForceFinalValue' => prettyNumber($baseForceValue * (1 + $forceModifier)),
        ]
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
