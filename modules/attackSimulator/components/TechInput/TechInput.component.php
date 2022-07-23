<?php

namespace UniEngine\Engine\Modules\AttackSimulator\Components\TechInput;

/**
 * @param array $props
 * @param number $props['slotIdx']
 * @param number $props['elementId']
 * @param enum ("attacker" | "defender") $props['columnType']
 * @param number $props['initialValue']
 */
function render($props) {
    $slotIdx = $props['slotIdx'];
    $elementId = $props['elementId'];

    $inputNamePrefix = (
        $props['columnType'] === 'attacker' ?
            'atk_techs' :
            'def_techs'
    );

    $templateBodyProps = [
        'prop_tabIndex'     => (
            $props['columnType'] === 'attacker' ?
                '1' :
                '2'
        ),
        'prop_inputName'    => "{$inputNamePrefix}[{$slotIdx}][{$elementId}]",
        'prop_initialValue' => $props['initialValue'],
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
