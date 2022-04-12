<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\RetreatInfoBox;

use UniEngine\Engine\Modules\FlightControl\Enums\RetreatResultType;

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
        'default'                                       => $_Lang['fl_notback'],
        RetreatResultType::ErrorCantRetreatAnymore      => $_Lang['fl_notback'],
        RetreatResultType::SuccessTurnedBack            => $_Lang['fl_isback'],
        RetreatResultType::SuccessRetreated             => $_Lang['fl_isback2'],
        RetreatResultType::ErrorMissileStrikeRetreat    => $_Lang['fl_missiles_cannot_go_back'],
        RetreatResultType::ErrorIsNotOwner              => $_Lang['fl_onlyyours'],
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
