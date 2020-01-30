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
function TechQueue_Remove(&$planet, &$user, $params) {
    global $UserDev_Log;

    $currentTimestamp = $params['currentTimestamp'];

    $queueString = Planets\Queues\Research\getQueueString($planet);
    $queue = Planets\Queues\Research\parseQueueString($queueString);

    $queueLength = count($queue);
    $firstQueueElement = $queue[0];
    $elementID = $firstQueueElement['elementID'];

    TechQueue_RemoveQueued(
        $planet,
        $user,
        0,
        [ "currentTimestamp" => $currentTimestamp ]
    );

    $purchaseCost = Elements\calculatePurchaseCost(
        $elementID,
        Elements\getElementState($elementID, $planet, $user),
        [
            'purchaseMode' => Elements\PurchaseMode::Upgrade
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

    $planet['techQueue_firstEndTime'] = 0;
    if ($queueLength === 1) {
        $user['techQueue_Planet'] = '0';
        $user['techQueue_EndTime'] = '0';
    }

    $newDevlogEntry = [
        'PlanetID'  => $planet['id'],
        'Date'      => $currentTimestamp,
        'Place'     => 4,
        'Code'      => 2,
        'ElementID' => $elementID
    ];
    $UserDev_Log[] = $newDevlogEntry;

    return $elementID;
}

?>
