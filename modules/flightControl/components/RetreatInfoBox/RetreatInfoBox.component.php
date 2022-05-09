<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\RetreatInfoBox;

use UniEngine\Engine\Modules\FlightControl\Enums\RetreatResultType;

//  Arguments
//      - $props (Object)
//          - eventCode (String)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $eventCode = (
        isset($props['eventCode']) ?
            intval($props['eventCode'], 10) :
            0
    );

    $eventCodeMapping = [
        0                                               => $_Lang['fl_notback'],
        RetreatResultType::ErrorCantRetreatAnymore      => $_Lang['fl_notback'],
        RetreatResultType::SuccessTurnedBack            => $_Lang['fl_isback'],
        RetreatResultType::SuccessRetreated             => $_Lang['fl_isback2'],
        RetreatResultType::ErrorMissileStrikeRetreat    => $_Lang['fl_missiles_cannot_go_back'],
        RetreatResultType::ErrorIsNotOwner              => $_Lang['fl_onlyyours'],
    ];
    $eventColor = (
        (
            $eventCode === RetreatResultType::SuccessTurnedBack ||
            $eventCode === RetreatResultType::SuccessRetreated
        ) ?
            "lime" :
            "red"
    );
    $isKnownErrorCode = in_array($eventCode, array_keys($eventCodeMapping));

    $componentTPLData = [
        'container_hideclass' => (
            $isKnownErrorCode ?
                null :
                ' class="hide"'
        ),
        'message_content' => (
            isset($eventCodeMapping[$eventCode]) ?
                $eventCodeMapping[$eventCode] :
                $eventCodeMapping['default']
        ),
        'message_color' => $eventColor,
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
