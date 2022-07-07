<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\ResourcesTransport;

use UniEngine\Engine\Includes\Helpers\World\Elements;

/**
 * @param array $props
 * @param arrayRef $props['user']
 * @param arrayRef $props['planet']
 */
function render($props) {
    global $_Lang, $_Vars_ElementCategories;

    $user = &$props['user'];
    $planet = &$props['planet'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
        'shipRow' => $localTemplateLoader('shipRow'),
    ];

    $resourcesTotalSum = (
        $planet['metal'] +
        $planet['crystal'] +
        $planet['deuterium']
    );

    $shipRows = [];

    foreach ($_Vars_ElementCategories['units']['transport'] as $shipId) {
        $requiredShipsCount = ceil($resourcesTotalSum / getShipsStorageCapacity($shipId));
        $currentShipsCount = Elements\getElementCurrentCount($shipId, $planet, $user);
        $remainingShipsCount = $currentShipsCount - $requiredShipsCount;

        $shipRows[] = parsetemplate($tplBodyCache['shipRow'], [
            'shipName' => $_Lang['tech'][$shipId],
            'requiredCount' => prettyNumber($requiredShipsCount),
            'remainingCount' => str_replace('-', '', prettyColorNumber($remainingShipsCount, true)),
        ]);
    }

    $shouldDisplayQuickTransportBtn = (
        isPro($user) &&
        $user['current_planet'] != $user['settings_mainPlanetID']
    );
    $quickTransportBtnText = '';

    if ($shouldDisplayQuickTransportBtn) {
        $getQuickTransportTargetPlanetQuery = (
            "SELECT " .
            "`name`, `galaxy`, `system`, `planet` " .
            "FROM {{table}} " .
            "WHERE " .
            "`id` = {$user['settings_mainPlanetID']} " .
            ";"
        );
        $quickTransportTargetPlanet = doquery($getQuickTransportTargetPlanetQuery, 'planets', true);

        $quickTransportBtnText = sprintf(
            $_Lang['QuickResSend_Button'],
            $quickTransportTargetPlanet['name'],
            $quickTransportTargetPlanet['galaxy'],
            $quickTransportTargetPlanet['system'],
            $quickTransportTargetPlanet['planet']
        );
    }

    $tplBodyParams = [
        'shipRowsHTML'          => implode('', $shipRows),
        'Hide_QuickResButton'   => (
            $shouldDisplayQuickTransportBtn ?
                '' :
                'style="display: none;"'
        ),
        'QuickResSend_Button'   => $quickTransportBtnText,
    ];
    $tplBodyParams = array_merge($_Lang, $tplBodyParams);

    $componentHTML = parsetemplate(
        $tplBodyCache['body'],
        $tplBodyParams
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
