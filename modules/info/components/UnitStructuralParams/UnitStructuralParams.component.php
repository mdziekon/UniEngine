<?php

namespace UniEngine\Engine\Modules\Info\Components\UnitStructuralParams;

/**
 * @param array $props
 * @param number $props['elementId']
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_Lang, $_Vars_Prices, $_Vars_CombatData, $_Vars_GameElements;

    $elementId = $props['elementId'];
    $user = &$props['user'];

    $baseHullValue = ($_Vars_Prices[$elementId]['metal'] + $_Vars_Prices[$elementId]['crystal']);
    $baseShieldValue = $_Vars_CombatData[$elementId]['shield'];

    $hullModifier = (0.1 * $user[$_Vars_GameElements[111]]);
    $shieldModifier = (0.1 * $user[$_Vars_GameElements[110]]);

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
