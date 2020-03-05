<?php

namespace UniEngine\Engine\Modules\Development\Components\ListViewElementRow\UpgradeResourcesRest;

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;

//  Arguments
//      - $props (Object)
//          - elementID (String)
//          - user (Object)
//          - planet (Object)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'resource_rest' => $localTemplateLoader('resource_rest'),
    ];

    $elementID = $props['elementID'];
    $user = $props['user'];
    $planet = $props['planet'];

    $resourceLabels = [
        'metal'         => $_Lang['Metal'],
        'crystal'       => $_Lang['Crystal'],
        'deuterium'     => $_Lang['Deuterium'],
        'energy'        => $_Lang['Energy'],
        'energy_max'    => $_Lang['Energy'],
        'darkEnergy'    => $_Lang['DarkEnergy']
    ];

    $resourceRestListHTMLs = [];

    $upgradeCost = Elements\calculatePurchaseCost(
        $elementID,
        Elements\getElementState($elementID, $planet, $user),
        [
            'purchaseMode' => Elements\PurchaseMode::Upgrade
        ]
    );

    foreach ($upgradeCost as $costResourceKey => $costValue) {
        $currentResourceState = Resources\getResourceState(
            $costResourceKey,
            $user,
            $planet
        );

        $resourceLeft = $currentResourceState - $costValue;
        $hasResourceDeficit = ($resourceLeft < 0);

        $resourceRestTPLData = [
            'ResourceName'              => $resourceLabels[$costResourceKey],
            'ResourceStateColorClass'   => classNames([
                'rgb(127, 95, 96)' => ($hasResourceDeficit),
                'rgb(95, 127, 108)' => (!$hasResourceDeficit),
            ]),
            'ResourceCurrentState'      => prettyNumber($resourceLeft),
        ];

        $resourceRestListHTMLs[] = parsetemplate(
            $tplBodyCache['resource_rest'],
            $resourceRestTPLData
        );
    }

    return [
        'componentHTML' => implode('', $resourceRestListHTMLs)
    ];
}

?>
