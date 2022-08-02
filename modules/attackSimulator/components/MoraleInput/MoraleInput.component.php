<?php

namespace UniEngine\Engine\Modules\AttackSimulator\Components\MoraleInput;

/**
 * @param array $props
 * @param number $props['slotIdx']
 * @param enum ("attacker" | "defender") $props['columnType']
 * @param number $props['initialValue']
 */
function render($props) {
    $slotIdx = $props['slotIdx'];

    $inputNamePrefix = (
        $props['columnType'] === 'attacker' ?
            'atk_morale' :
            'def_morale'
    );

    $templateBodyProps = [
        'prop_tabIndex'     => (
            $props['columnType'] === 'attacker' ?
                '1' :
                '2'
        ),
        'prop_inputName'    => "{$inputNamePrefix}[{$slotIdx}]",
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
