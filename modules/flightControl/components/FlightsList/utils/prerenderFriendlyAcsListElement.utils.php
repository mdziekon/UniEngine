<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;

//  Arguments
//      - $listElement (ReturnType<typeof buildFriendlyAcsListElement>)
//
function prerenderFriendlyAcsListElement($listElement) {
    global $_Lang;

    $fleetShipsRowTpl = gettemplate('fleet_fdetail');

    $prerenderedParams = [
        'FleetDetails' => join(
            '',
            array_map_withkeys(
                $listElement['data']['ships'],
                function ($shipCount, $shipId) use (&$_Lang, &$fleetShipsRowTpl) {
                    return parsetemplate(
                        $fleetShipsRowTpl,
                        [
                            'Ship' => $_Lang['tech'][$shipId],
                            'Count' => prettyNumber($shipCount),
                        ]
                    );
                }
            )
        ),
    ];

    $listElement = array_merge($listElement, [ 'data' => null ], $prerenderedParams);

    return $listElement;
}

?>
