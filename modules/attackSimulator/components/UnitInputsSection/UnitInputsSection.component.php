<?php

namespace UniEngine\Engine\Modules\AttackSimulator\Components\UnitInputsSection;

use UniEngine\Engine\Includes\Helpers\World;
use UniEngine\Engine\Modules\AttackSimulator;

/**
 * @param array $props
 * @param number $props['slotIdx']
 * @param arrayRef $props['input']
 */
function render($props) {
    global $_Lang, $_Vars_ElementCategories;

    $slotIdx = $props['slotIdx'];
    $input = &$props['input'];

    $isMainFleetsSlot = $slotIdx === 1;

    $units = array_merge(
        $_Vars_ElementCategories['fleet'],
        (
            $isMainFleetsSlot ?
                $_Vars_ElementCategories['defense'] :
                []
        )
    );
    $units = array_filter($units, function ($elementId) {
        return !World\Elements\isMissile($elementId);
    });

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'mainFleetsBody'    => $localTemplateLoader('mainFleetsBody'),
        'unitedFleetsBody'  => $localTemplateLoader('unitedFleetsBody'),
        'defenseUnitRow'    => $localTemplateLoader('defenseUnitRow'),
        'dualUnitRow'       => $localTemplateLoader('dualUnitRow'),
    ];

    $unitRows = object_map(
        $units,
        function ($elementId) use ($slotIdx, &$input, &$_Lang, &$tplBodyCache) {
            $formInputValueAttacker = (
                isset($input['atk_ships'][$slotIdx][$elementId]) ?
                    $input['atk_ships'][$slotIdx][$elementId] :
                    null
            );
            $formInputValueDefender = (
                isset($input['def_ships'][$slotIdx][$elementId]) ?
                    $input['def_ships'][$slotIdx][$elementId] :
                    null
            );

            $rowTemplateBodyCommonProps = [
                'prop_unitName' => $_Lang['tech'][$elementId],

                'prop_DefenderInput'    => AttackSimulator\Components\ShipInput\render([
                    'slotIdx'       => $slotIdx,
                    'elementId'     => $elementId,
                    'columnType'    => 'defender',
                    'initialValue'  => $formInputValueDefender,
                ])['componentHTML'],
            ];

            if (!hasAnyEngine($elementId)) {
                return [
                    parsetemplate(
                        $tplBodyCache['defenseUnitRow'],
                        $rowTemplateBodyCommonProps
                    ),
                    $elementId
                ];
            }

            $rowTemplateBodyProps = [
                'prop_AttackerInput'    => AttackSimulator\Components\ShipInput\render([
                    'slotIdx'       => $slotIdx,
                    'elementId'     => $elementId,
                    'columnType'    => 'attacker',
                    'initialValue'  => $formInputValueAttacker,
                ])['componentHTML'],
            ];

            return [
                parsetemplate(
                    $tplBodyCache['dualUnitRow'],
                    array_merge(
                        $rowTemplateBodyCommonProps,
                        $rowTemplateBodyProps
                    )
                ),
                $elementId
            ];
        }
    );
    $shipRows = array_filter_withkeys($unitRows, function ($elementHTML, $elementId) {
        return World\Elements\isShip($elementId);
    });
    $defenseRows = array_filter_withkeys($unitRows, function ($elementHTML, $elementId) {
        return World\Elements\isDefenseSystem($elementId);
    });

    $templateBodyProps = [
        'lang_Fleets'               => $_Lang['Fleets'],
        'lang_Defense'              => $_Lang['Defense'],
        'lang_FillMyFleets'         => $_Lang['FillMyFleets'],
        'lang_Fill_Clean'           => $_Lang['Fill_Clean'],

        'prop_shipUnitRowsHTML'     => implode('', $shipRows),
        'prop_defenseUnitRowsHTML'  => implode('', $defenseRows),
    ];

    $bodyTpl = (
        $isMainFleetsSlot ?
            $tplBodyCache['mainFleetsBody'] :
            $tplBodyCache['unitedFleetsBody']
    );

    $componentHTML = parsetemplate(
        $bodyTpl,
        $templateBodyProps
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
