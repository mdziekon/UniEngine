<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;

//  Arguments
//      - $listElement (ReturnType<typeof buildOwnListElement>)
//      - $params (Object)
//          - tplBodyCache (Ref)
//
function prerenderOwnListElement($listElement, $params) {
    global $_Lang;

    $tplBodyCache = &$params['tplBodyCache'];

    $fleetUnionSquadMainTpl = gettemplate('fleet_faddinfo');
    $fleetResourcesRowTpl = gettemplate('fleet_fresinfo');
    $fleetResourcesRowTpl = str_replace(
        [
            'TitleMain',
            'TitleMetal',
            'TitleCrystal',
            'TitleDeuterium',
        ],
        [
            $_Lang['fl_fleetinfo_resources'],
            $_Lang['Metal'],
            $_Lang['Crystal'],
            $_Lang['Deuterium'],
        ],
        $fleetResourcesRowTpl
    );

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
                function ($shipCount, $shipId) use (&$_Lang, &$tplBodyCache) {
                    return parsetemplate(
                        $tplBodyCache['listElementShipRow'],
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
                        function ($shipCount, $shipId) use (&$_Lang, &$tplBodyCache) {
                            return parsetemplate(
                                $tplBodyCache['listElementShipRow'],
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
        'FleetResInfo' => parsetemplate(
            $fleetResourcesRowTpl,
            [
                'FleetMetal' => prettyNumber($listElement['data']['resources']['metal']),
                'FleetCrystal' => prettyNumber($listElement['data']['resources']['crystal']),
                'FleetDeuterium' => prettyNumber($listElement['data']['resources']['deuterium']),
            ]
        ),
    ];

    $listElement = array_merge($listElement, [ 'data' => null ], $prerenderedParams);

    return $listElement;
}

?>
