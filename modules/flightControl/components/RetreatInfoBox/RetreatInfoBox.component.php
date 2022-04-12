<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\RetreatInfoBox;

//  Arguments
//      - $props (Object)
//          - isVisible (Boolean)
//          - eventCode (String)
//          - eventColor (String)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $isVisible = $props['isVisible'];
    $eventCode = (
        isset($props['eventCode']) ?
            $props['eventCode'] :
            'default'
    );
    $eventColor = (
        isset($props['eventColor']) ?
            $props['eventColor'] :
            'default'
    );

    $eventCodeMapping = [
        'default' => $_Lang['fl_notback'],
        1 => $_Lang['fl_notback'],
        2 => $_Lang['fl_isback'],
        3 => $_Lang['fl_isback2'],
        4 => $_Lang['fl_missiles_cannot_go_back'],
        5 => $_Lang['fl_onlyyours'],
    ];
    $eventColorMapping = [
        'default' => "red",
        1 => "red",
        2 => "lime",
    ];

    $componentTPLData = [
        'container_hideclass' => (
            $isVisible ?
                null :
                ' class="hide"'
        ),
        'message_content' => (
            isset($eventCodeMapping[$eventCode]) ?
                $eventCodeMapping[$eventCode] :
                $eventCodeMapping['default']
        ),
        'message_color' => (
            isset($eventColorMapping[$eventColor]) ?
                $eventColorMapping[$eventColor] :
                $eventColorMapping['default']
        ),
    ];
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], $componentTPLData);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
