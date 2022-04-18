<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;

//  Arguments
//      - $listElement (ReturnType<typeof buildOwnListElement>)
//
function prerenderOwnListElement($listElement) {
    global $_Lang;

    $fleetShipsRowTpl = gettemplate('fleet_fdetail');
    $fleetUnionSquadMainTpl = gettemplate('fleet_faddinfo');

    $ordersTpls = [
        'retreat' => gettemplate('fleet_orders_retreat'),
        'createUnion' => gettemplate('fleet_orders_acs'),
        'joinUnion' => gettemplate('fleet_orders_jointoacs'),
        'joinUnionOnManagement' => '{Text}',
    ];

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
        'FleetAddShipsInfo' => (
            !empty($listElement['data']['extraShipsInUnion']) ?
            (
                parsetemplate($fleetUnionSquadMainTpl, $_Lang) .
                join(
                    '',
                    array_map_withkeys(
                        $listElement['data']['extraShipsInUnion'],
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
                )
            ) :
            ''
        ),
        'FleetOrders' => implode(
            '',
            array_map_withkeys(
                $listElement['data']['orders'],
                function ($orderData) use (&$ordersTpls) {
                    $orderType = $orderData['orderType'];
                    $template = $ordersTpls[$orderType];

                    return parsetemplate($template, $orderData['params']);
                }
            )
        ),
    ];

    $listElement = array_merge($listElement, [ 'data' => null ], $prerenderedParams);

    return $listElement;
}

?>
