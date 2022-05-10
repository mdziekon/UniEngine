<?php

namespace UniEngine\Engine\Modules\FlightControl\Screens\SendWizardStepOne\Components\FlightsList\Utils;

//  Arguments
//      - $listElement (ReturnType<typeof buildFriendlyAcsListElement>)
//      - $params (Object)
//          - tplBodyCache (Ref)
//
function prerenderFriendlyAcsListElement($listElement, $params) {
    global $_Lang;

    $tplBodyCache = &$params['tplBodyCache'];

    $ordersTpls = [
        'joinUnion' => function ($params) use (&$_Lang) {
            return buildDOMElementHTML([
                'tagName'           => 'input',
                'contentHTML'       => (
                    '<br/>' .
                    $_Lang['fl_acs_joinnow']
                ),
                'attrs'             => [
                    'type'          => 'radio',
                    'value'         => $params['acsId'],
                    'class'         => 'setACS_ID pad5',
                    'name'          => 'acs_select',
                    'checked'       => (
                        $params['isJoiningThisUnion'] ?
                            'checked' :
                            null
                    ),
                ],
            ]);
        },
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
        'FleetOrders' => implode(
            '',
            array_map_withkeys(
                $listElement['data']['orders'],
                function ($orderData) use (&$ordersTpls) {
                    $orderType = $orderData['orderType'];
                    $templateFn = $ordersTpls[$orderType];

                    return $templateFn($orderData['params']);
                }
            )
        ),
    ];

    $listElement = array_merge($listElement, [ 'data' => null ], $prerenderedParams);

    return $listElement;
}

?>
