<?php

namespace UniEngine\Engine\Modules\AttackSimulator\Components\ShipInput;

/**
 * @param array $props
 * @param number $props['slotIdx']
 * @param number $props['elementId']
 * @param enum ("attacker" | "defender") $props['columnType']
 * @param number $props['initialValue']
 */
function render($props) {
    global $_Lang;

    $slotIdx = $props['slotIdx'];
    $elementId = $props['elementId'];

    $inputNamePrefix = (
        $props['columnType'] === 'attacker' ?
            'atk_ships' :
            'def_ships'
    );

    $templateBodyProps = [
        'prop_tabIndex'     => (
            $props['columnType'] === 'attacker' ?
                '1' :
                '2'
        ),
        'prop_inputName'    => "{$inputNamePrefix}[{$slotIdx}][{$elementId}]",
        'prop_initialValue' => $props['initialValue'],
        'lang_Button_Min'   => $_Lang['Button_Min'],
        'lang_Button_Max'   => $_Lang['Button_Max'],
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
