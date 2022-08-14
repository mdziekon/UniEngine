<?php

namespace UniEngine\Engine\Modules\Info\Components\UnitDetailsTable;

use UniEngine\Engine\Includes\Helpers\World;
use UniEngine\Engine\Modules\Info;

/**
 * @param array $props
 * @param number $props['elementId']
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_Lang;

    $elementId = $props['elementId'];
    $user = &$props['user'];

    $isShip = World\Elements\isShip($elementId);

    $tplBodyParams = [
        'nfo_techdetails_title'     => $_Lang['nfo_techdetails_title'],
        'nfo_techdetails_base'      => $_Lang['nfo_techdetails_base'],
        'nfo_techdetails_modifier'  => $_Lang['nfo_techdetails_modifier'],
        'nfo_techdetails_modified'  => $_Lang['nfo_techdetails_modified'],
        'nfo_capacity'              => $_Lang['nfo_capacity'],
        'nfo_units'                 => $_Lang['nfo_units'],

        'component_unitStructuralParams'    => null,
        'component_unitForce'               => null,
        'component_unitEngines'             => null,
        'Insert_Storage_Base'               => null,
    ];

    $tplBodyParams['component_unitStructuralParams'] = Info\Components\UnitStructuralParams\render([
        'elementId' => $elementId,
        'user' => &$user,
    ])['componentHTML'];
    $tplBodyParams['component_unitForce'] = Info\Components\UnitForce\render([
        'elementId' => $elementId,
        'user' => &$user,
    ])['componentHTML'];

    if ($isShip) {
        $thisShipsStorageCapacity = getShipsStorageCapacity($elementId);

        $tplBodyParams['Insert_Storage_Base'] = prettyNumber($thisShipsStorageCapacity);

        $tplBodyParams['component_unitEngines'] = Info\Components\UnitEngines\render([
            'elementId' => $elementId,
            'user' => &$user,
        ])['componentHTML'];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBody = (
        $isShip ?
            $localTemplateLoader('bodyShip') :
            $localTemplateLoader('bodyDefenceSystem')
    );

    $componentHTML = parsetemplate($tplBody, $tplBodyParams);

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
