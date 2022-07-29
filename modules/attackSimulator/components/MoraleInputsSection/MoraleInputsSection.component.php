<?php

namespace UniEngine\Engine\Modules\AttackSimulator\Components\MoraleInputsSection;

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

    $templateBodyProps = [
        'lang_Morale'           => $_Lang['Morale'],
        'lang_Morale_Level'     => $_Lang['Morale_Level'],

        'prop_AttackerInput'    => AttackSimulator\Components\MoraleInput\render([
            'slotIdx'       => $slotIdx,
            'columnType'    => 'attacker',
            'initialValue'  => $input['atk_morale'][$slotIdx],
        ])['componentHTML'],
        'prop_DefenderInput'    => AttackSimulator\Components\MoraleInput\render([
            'slotIdx'       => $slotIdx,
            'columnType'    => 'defender',
            'initialValue'  => $input['def_morale'][$slotIdx],
        ])['componentHTML'],
    ];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        $templateBodyProps
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
