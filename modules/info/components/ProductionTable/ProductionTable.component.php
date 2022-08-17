<?php

namespace UniEngine\Engine\Modules\Info\Components\ProductionTable;

use UniEngine\Engine\Includes\Helpers\World;
use UniEngine\Engine\Modules\Info;

/**
 * @param array $props
 * @param number $props['elementId']
 * @param arrayRef $props['user']
 * @param arrayRef $props['planet']
 * @param number $props['currentTimestamp']
 */
function render($props) {
    global $_Lang;

    $elementId = $props['elementId'];
    $user = &$props['user'];
    $planet = &$props['planet'];
    $currentTimestamp = $props['currentTimestamp'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $SOLAR_PLANT_ELEMENTID = 4;
    $FUSION_PLANT_ELEMENTID = 12;
    $IMPULSE_DRIVE_ELEMENTID = 117;
    $PHALANX_ELEMENTID = 42;

    $rowsHTML = '';

    if (World\Elements\isProductionRelated($elementId)) {
        $rowsHTML = Info\Components\ResourceProductionTable\render([
            'elementId' => $elementId,
            'planet' => &$planet,
            'user' => &$user,
            'currentTimestamp' => $currentTimestamp,
        ])['componentHTML'];
    } else if (World\Elements\isStorageStructure($elementId)) {
        $rowsHTML = Info\Components\ResourceStorageTable\render([
            'elementId' => $elementId,
            'planet' => &$planet,
        ])['componentHTML'];
    } else if ($elementId == $IMPULSE_DRIVE_ELEMENTID) {
        $rowsHTML = Info\Components\MissileRangeTable\render([
            'elementId' => $elementId,
            'planet' => &$planet,
            'user' => &$user,
        ])['componentHTML'];
    } else if ($elementId == $PHALANX_ELEMENTID) {
        $rowsHTML = Info\Components\PhalanxRangeTable\render([
            'elementId' => $elementId,
            'planet' => &$planet,
            'user' => &$user,
        ])['componentHTML'];
    }

    $headerTpl = '';

    if (World\Elements\isPlanetaryMine($elementId)) {
        $headerTpl = $localTemplateLoader('headerMineProduction');
    } else if ($elementId == $SOLAR_PLANT_ELEMENTID) {
        $headerTpl = $localTemplateLoader('headerSolarPlantProduction');
    } else if ($elementId == $FUSION_PLANT_ELEMENTID) {
        $headerTpl = $localTemplateLoader('headerFusionPlantProduction');
    } else if (World\Elements\isStorageStructure($elementId)) {
        $headerTpl = $localTemplateLoader('headerStorageCapacity');
    } else if ($elementId == $PHALANX_ELEMENTID) {
        $headerTpl = $localTemplateLoader('headerPhalanxRange');
    } else if ($elementId == $IMPULSE_DRIVE_ELEMENTID) {
        $headerTpl = $localTemplateLoader('headerInterplanetaryMissileRange');
    }

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        [
            'props_TableHeader' => parsetemplate($headerTpl, $_Lang),
            'props_TableRows' => $rowsHTML,
        ]
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
