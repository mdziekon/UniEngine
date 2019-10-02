<?php

use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;

//  Arguments:
//      - $planet (Object)
//      - $user (Object)
//      - $params (Object)
//          - currentTimestamp (Number)
//
function CancelBuildingFromQueue(&$planet, $user, $params) {
    global $UserDev_Log;

    $currentTimestamp = $params['currentTimestamp'];

    $queue = Planets\Queues\parseStructuresQueueString($planet['buildQueue']);

    $firstQueueElement = $queue[0];
    $elementID = $firstQueueElement['elementID'];
    $isUpgrading = ($firstQueueElement['mode'] === 'build');


    RemoveBuildingFromQueue(
        $planet,
        $user,
        1,
        [ 'currentTimestamp' => $currentTimestamp ]
    );

    $purchaseCost = Elements\calculatePurchaseCost(
        $elementID,
        Elements\getElementState($elementID, $planet, $user),
        [
            'purchaseMode' => (
                $isUpgrading ?
                Elements\PurchaseMode::Upgrade :
                Elements\PurchaseMode::Downgrade
            )
        ]
    );

    foreach ($purchaseCost as $costResourceKey => $costValue) {
        if (
            !Resources\isPlanetaryResource($costResourceKey) ||
            !Resources\isSpendableResource($costResourceKey)
        ) {
            continue;
        }

        $planet[$costResourceKey] += $costValue;
    }

    $planet['buildQueue_firstEndTime'] = '0';

    $newDevlogEntry = [
        'PlanetID'  => $planet['id'],
        'Date'      => $currentTimestamp,
        'Place'     => 2,
        'Code'      => ($isUpgrading ? 1 : 2),
        'ElementID' => $elementID
    ];
    $UserDev_Log[] = $newDevlogEntry;

    return $elementID;
}

?>
