<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\TargetsSelector;

use UniEngine\Engine\Modules\FlightControl\Components\TargetOptionLabel;

//  Arguments
//      - $props (Object)
//          - targets (Object[])
//          - selectorId (String)
//          - isDisabled (Boolean)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $targets = $props['targets'];
    $selectorId = $props['selectorId'];
    $isDisabled = $props['isDisabled'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $listElements = array_map_withkeys($targets, function ($target) use (&$_Lang) {
        $elementJSParts = [
            $target['galaxy'],
            $target['system'],
            $target['planet'],
            $target['planet_type'],
        ];

        $elementLabel = TargetOptionLabel\render([
            'target' => $target,
        ])['componentHTML'];

        return [
            'txt' => $elementLabel,
            'js' => implode(',', $elementJSParts),
        ];
    });

    if (empty($listElements)) {
        return [
            'componentHTML' => '',
        ];
    }

    $listElementsHTML = array_map_withkeys($listElements, function ($listElement) {
        return buildDOMElementHTML([
            'tagName' => 'option',
            'attrs' => [
                'value' => $listElement['js'],
            ],
            'contentHTML' => $listElement['txt'],
        ]);
    });

    $componentTPLData = [
        'selectorId' => $selectorId,
        'isDisabledClass' => (
            $isDisabled ?
                'disabled' :
                ''
        ),
        'defaultOptionLabel' => $_Lang['fl_dropdown_select'],
        'listElementsHTML' => implode('', $listElementsHTML),
    ];

    return [
        'componentHTML' => parsetemplate($tplBodyCache['body'], $componentTPLData),
    ];
}

?>
