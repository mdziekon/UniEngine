<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\AvailableShipsList;

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Includes\Helpers\World\Elements;

//  Arguments
//      - $props (Object)
//          - planet (object)
//          - user (object)
//          - preselectedShips (Record<shipId: string, shipCount: number>)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang, $_Vars_ElementCategories;

    $planet = $props['planet'];
    $user = $props['user'];
    $preselectedShips = $props['preselectedShips'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'listElement' => $localTemplateLoader('listElement'),
    ];

    $tplBodyCache['listElement'] = str_replace(
        [
            'fl_fleetspeed',
            'fl_selmax',
            'fl_selnone',
        ],
        [
            $_Lang['fl_fleetspeed'],
            $_Lang['fl_selmax'],
            $_Lang['fl_selnone'],
        ],
        $tplBodyCache['listElement']
    );

    $listElements = array_map_withkeys($_Vars_ElementCategories['fleet'], function ($shipId) use ($planet, $user, $preselectedShips, &$_Lang) {
        $elementCurrentCount = Elements\getElementCurrentCount($shipId, $planet, $user);

        if (
            $elementCurrentCount <= 0 ||
            !hasAnyEngine($shipId)
        ) {
            return null;
        }

        $maxCountParts = explode('.', sprintf('%f', floor($elementCurrentCount)));

        $shipRowProps = [
            'ID'                => $shipId,
            'Speed'             => prettyNumber(getShipsCurrentSpeed($shipId, $user)),
            'Name'              => $_Lang['tech'][$shipId],
            'Count'             => prettyNumber($elementCurrentCount),
            'MaxCount'          => (string) $maxCountParts[0],
            'InsertShipCount'   => (
                !empty($preselectedShips[$shipId]) ?
                    prettyNumber($preselectedShips[$shipId]) :
                '0'
            ),
        ];

        return $shipRowProps;
    });
    $listElements = Collections\compact($listElements);

    $elementsListHTML = implode(
        '',
        array_map_withkeys($listElements, function ($listElement) use (&$tplBodyCache) {
            return parsetemplate($tplBodyCache['listElement'], $listElement);
        })
    );

    return [
        'componentHTML' => $elementsListHTML,
    ];
}

?>
