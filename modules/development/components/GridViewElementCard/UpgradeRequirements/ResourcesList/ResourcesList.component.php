<?php

namespace UniEngine\Engine\Modules\Development\Components\GridViewElementCard\UpgradeRequirements\ResourcesList;

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;

//  Arguments
//      - $props (Object)
//          - elementID (String)
//          - user (Object)
//          - planet (Object)
//          - isQueueActive (Boolean)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_SkinPath;

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyCache = [
        'resource_box' => $localTemplateLoader('resource_box'),
    ];

    $elementID = $props['elementID'];
    $planet = $props['planet'];
    $user = $props['user'];
    $isQueueActive = $props['isQueueActive'];


    $resourceIcons = [
        'metal'         => 'metall',
        'crystal'       => 'kristall',
        'deuterium'     => 'deuterium',
        'energy'        => 'energie',
        'energy_max'    => 'energie',
        'darkEnergy'    => 'darkenergy'
    ];

    $upgradeCost = Elements\calculatePurchaseCost(
        $elementID,
        Elements\getElementState($elementID, $planet, $user),
        [
            'purchaseMode' => Elements\PurchaseMode::Upgrade
        ]
    );

    $subcomponentsResourceBoxesHTML = [];

    foreach ($upgradeCost as $costResourceKey => $costValue) {
        $currentResourceState = Resources\getResourceState(
            $costResourceKey,
            $user,
            $planet
        );
        $resourceLeft = ($currentResourceState - $costValue);
        $hasResourceDeficit = ($resourceLeft < 0);

        $resourceCostColor = classNames([
            'orange' => ($hasResourceDeficit && $isQueueActive),
            'red' => ($hasResourceDeficit && !$isQueueActive),
        ]);
        $resourceDeficitColor = classNames([
            'red' => $hasResourceDeficit,
        ]);
        $resourceDeficitValue = (
            $hasResourceDeficit ?
            '(' . prettyNumber($resourceLeft) . ')' :
            '&nbsp;'
        );

        $resourceCostTPLData = [
            'SkinPath'      => $_SkinPath,
            'ResKey'        => $costResourceKey,
            'ResImg'        => $resourceIcons[$costResourceKey],
            'ResColor'      => $resourceCostColor,
            'Value'         => prettyNumber($costValue),
            'ResMinusColor' => $resourceDeficitColor,
            'MinusValue'    => $resourceDeficitValue,
        ];

        $subcomponentsResourceBoxesHTML[] = parsetemplate(
            $tplBodyCache['resource_box'],
            $resourceCostTPLData
        );
    }

    $componentHTML = implode('', $subcomponentsResourceBoxesHTML);

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
