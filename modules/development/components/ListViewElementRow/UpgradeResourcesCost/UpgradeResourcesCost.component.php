<?php

namespace UniEngine\Engine\Modules\Development\Components\ListViewElementRow\UpgradeResourcesCost;

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;

//  Arguments
//      - $props (Object)
//          - elementID (String)
//          - user (Object)
//          - planet (Object)
//          - isQueueActive (Number)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'resource_cost' => $localTemplateLoader('resource_cost'),
    ];

    $elementID = $props['elementID'];
    $user = $props['user'];
    $planet = $props['planet'];
    $isQueueActive = $props['isQueueActive'];

    $resourceLabels = [
        'metal'         => $_Lang['Metal'],
        'crystal'       => $_Lang['Crystal'],
        'deuterium'     => $_Lang['Deuterium'],
        'energy'        => $_Lang['Energy'],
        'energy_max'    => $_Lang['Energy'],
        'darkEnergy'    => $_Lang['DarkEnergy']
    ];

    $resourceCostListHTMLs = [];

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

        $resourceCostTPLData = [
            'ResourceName'              => $resourceLabels[$costResourceKey],
            'ResourceStateColorClass'   => classNames([
                'orange' => ($hasResourceDeficit && $isQueueActive),
                'red' => ($hasResourceDeficit && !$isQueueActive),
            ]),
            'ResourceCurrentState'      => prettyNumber($costValue),
        ];

        $resourceCostListHTMLs[] = parsetemplate(
            $tplBodyCache['resource_cost'],
            $resourceCostTPLData
        );
    }

    return [
        'componentHTML' => implode('', $resourceCostListHTMLs)
    ];
}

?>
