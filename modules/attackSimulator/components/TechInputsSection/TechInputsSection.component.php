<?php

namespace UniEngine\Engine\Modules\AttackSimulator\Components\TechInputsSection;

use UniEngine\Engine\Modules\AttackSimulator;

/**
 * @param array $props
 * @param number $props['slotIdx']
 * @param arrayRef $props['input']
 */
function render($props) {
    global $_Lang;

    $slotIdx = $props['slotIdx'];
    $input = &$props['input'];

    $combatTechs = AttackSimulator\Utils\CombatTechs\getTechsList();

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
        'techRow' => $localTemplateLoader('techRow'),
    ];

    $techRows = array_map_withkeys(
        $combatTechs,
        function ($elementId) use ($slotIdx, &$input, &$_Lang, &$tplBodyCache) {
            $formInputValueAttacker = (
                isset($input['atk_techs'][$slotIdx][$elementId]) ?
                    $input['atk_techs'][$slotIdx][$elementId] :
                    null
            );
            $formInputValueDefender = (
                isset($input['def_techs'][$slotIdx][$elementId]) ?
                    $input['def_techs'][$slotIdx][$elementId] :
                    null
            );

            $rowTemplateBodyProps = [
                'prop_techName'         => $_Lang['tech'][$elementId],
                'prop_AttackerInput'    => AttackSimulator\Components\TechInput\render([
                    'slotIdx'       => $slotIdx,
                    'elementId'     => $elementId,
                    'columnType'    => 'attacker',
                    'initialValue'  => $formInputValueAttacker,
                ])['componentHTML'],
                'prop_DefenderInput'    => AttackSimulator\Components\TechInput\render([
                    'slotIdx'       => $slotIdx,
                    'elementId'     => $elementId,
                    'columnType'    => 'defender',
                    'initialValue'  => $formInputValueDefender,
                ])['componentHTML'],
            ];

            return parsetemplate(
                $tplBodyCache['techRow'],
                $rowTemplateBodyProps
            );
        }
    );

    $templateBodyProps = [
        'lang_Technology'   => $_Lang['Technology'],
        'lang_FillMyTechs'  => $_Lang['FillMyTechs'],
        'lang_Fill_Clean'   => $_Lang['Fill_Clean'],

        'prop_techRowsHTML' => implode('', $techRows),
    ];

    $componentHTML = parsetemplate(
        $tplBodyCache['body'],
        $templateBodyProps
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
