<?php

namespace UniEngine\Engine\Modules\Info\Components\UnitStructuralParams;

use UniEngine\Engine\Includes\Helpers\World;

/**
 * @param array $props
 * @param number $props['elementId']
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_Lang, $_Vars_Prices, $_Vars_CombatData;

    $elementId = $props['elementId'];
    $user = &$props['user'];
    $planet = [];

    $baseHullValue = ($_Vars_Prices[$elementId]['metal'] + $_Vars_Prices[$elementId]['crystal']);
    $baseShieldValue = $_Vars_CombatData[$elementId]['shield'];

    $hullUpgradeTechnologyLevel = World\Elements\getElementCurrentLevel(111, $planet, $user);
    $shieldUpgradeTechnologyLevel = World\Elements\getElementCurrentLevel(110, $planet, $user);

    $hullModifier = (0.1 * $hullUpgradeTechnologyLevel);
    $shieldModifier = (0.1 * $shieldUpgradeTechnologyLevel);

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        [
            'lang_Hull' => $_Lang['nfo_struct_pt'],
            'lang_Shields' => $_Lang['nfo_shielf_pt'],

            'data_HullBaseValue' => prettyNumber($baseHullValue),
            'data_HullModifier' => $hullModifier * 100,
            'data_HullFinalValue' => prettyNumber($baseHullValue * (1 + $hullModifier)),
            'data_ShieldBaseValue' => prettyNumber($baseShieldValue),
            'data_ShieldModifier' => $shieldModifier * 100,
            'data_ShieldFinalValue' => prettyNumber($baseShieldValue * (1 + $shieldModifier)),
        ]
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
