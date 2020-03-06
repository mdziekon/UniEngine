<?php

namespace UniEngine\Engine\Modules\Development\Utils\Structures;

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;

/**
 * @param object $props
 * @param string $props.elementID
 * @param object $props.planet
 * @param object $props.user
 * @param boolean $props.isQueueActive
 *
 * @return $details
 * @return $details.resources
 * @return $details.destructionTime
 */
function getDestructionDetails($props) {
    global $_Lang;

    $elementID = $props['elementID'];
    $planet = $props['planet'];
    $user = $props['user'];
    $isQueueActive = $props['isQueueActive'];

    $resourceLabels = [
        'metal'         => $_Lang['Metal'],
        'crystal'       => $_Lang['Crystal'],
        'deuterium'     => $_Lang['Deuterium'],
        'energy'        => $_Lang['Energy'],
        'energy_max'    => $_Lang['Energy'],
        'darkEnergy'    => $_Lang['DarkEnergy']
    ];


    $downgradeCost = Elements\calculatePurchaseCost(
        $elementID,
        Elements\getElementState($elementID, $planet, $user),
        [
            'purchaseMode' => Elements\PurchaseMode::Downgrade
        ]
    );

    $elementDowngradeResources = [];

    foreach ($downgradeCost as $costResourceKey => $costValue) {
        $currentResourceState = Resources\getResourceState(
            $costResourceKey,
            $user,
            $planet
        );

        $resourceLeft = ($currentResourceState - $costValue);
        $hasResourceDeficit = ($resourceLeft < 0);

        $resourceCostColor = classNames([
            'red' => ($hasResourceDeficit && !$isQueueActive),
            'orange' => ($hasResourceDeficit && $isQueueActive),
        ]);

        $elementDowngradeResources[] = [
            'name' => $resourceLabels[$costResourceKey],
            'color' => $resourceCostColor,
            'value' => prettyNumber($costValue)
        ];
    }

    $destructionTime = GetBuildingTime($user, $planet, $elementID) / 2;

    return [
        'resources' => $elementDowngradeResources,
        'destructionTime' => pretty_time($destructionTime)
    ];
}

?>
