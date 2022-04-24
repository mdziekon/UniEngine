<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\TargetsSelector;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

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
        $targetCustomName = (
            !empty($target['own_name']) ?
                "{$target['own_name']} -" :
                null
        );
        $targetOriginalName = $target['name'];
        $targetTypeLabel = [
            '1' => $_Lang['planet_sign'],
            '2' => $_Lang['moon_sign'],
            '3' => $_Lang['debris_sign'],
        ][$target['planet_type']];
        $targetTypeMarker = "({$targetTypeLabel})";
        $targetPos = "[{$target['galaxy']}:{$target['system']}:{$target['planet']}]";

        $elementLabelParts = Collections\compact([
            $targetCustomName,
            $targetOriginalName,
            $targetTypeMarker,
            $targetPos
        ]);
        $elementJSParts = [
            $target['galaxy'],
            $target['system'],
            $target['planet'],
            $target['planet_type'],
        ];

        return [
            'txt' => implode(' ', $elementLabelParts),
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
