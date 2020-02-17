<?php

namespace UniEngine\Engine\Modules\Development\Utils;

use UniEngine\Engine\Common\Exceptions;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\World\Elements;

abstract class QueueType {
    const Planetary = 'QueueType::Planetary';
    const Research = 'QueueType::Research';
}

//  Arguments
//      - $props (Object)
//          - queue (Object)
//              - type (QueueType)
//              - content (Array<QueueElement>)
//          - user (Object)
//          - planet (Object)
//
//  Returns: Object
//      - queuedResourcesToUse (Object<resourceKey: string, value: number>)
//          How much resources are yet to be consumed by all of the queued
//          elements. Does not count the first element of the queue, because these
//          resources have already been "consumed".
//      - queuedElementLevelModifiers (Object<elementID: string, levelModifier: number>)
//          Difference between current state and the future state of queued element levels.
//      - queuedElementsCount (Number)
//          How many elements are there in queue.
//
function getQueueStateDetails ($props) {
    $planet = &$props['planet'];
    $user = &$props['user'];
    $queue = $props['queue']['content'];
    $queueType = $props['queue']['type'];

    $objectToModifyLevels = [];

    if ($queueType === QueueType::Planetary) {
        $objectToModifyLevels = &$planet;
    } else if ($queueType === QueueType::Research) {
        $objectToModifyLevels = &$user;
    } else {
        throw new Exceptions\UniEngineException("Invalid queue type ('{$queueType}')");
    }

    $resourceKeys = Resources\getKnownSpendableResourceKeys();
    $queuedResourcesToUse = array_map(
        function () { return 0; },
        array_flip($resourceKeys)
    );

    $queuedElementLevelModifiers = [];

    foreach ($queue as $queueIdx => $queueElement) {
        $elementID = $queueElement['elementID'];
        $elementMode = $queueElement['mode'];
        $isFirstQueueElement = ($queueIdx === 0);
        $isUpgrade = ($elementMode === 'build');
        $thisLevelModifier = ($isUpgrade ? 1 : -1);

        $elementKey = Elements\getElementKey($elementID);

        if (!isset($queuedElementLevelModifiers[$elementID])) {
            $queuedElementLevelModifiers[$elementID] = 0;
        }

        // Store the current level modifier before updating it in the accumulator
        $elementCurrentLevelModifier = $queuedElementLevelModifiers[$elementID];

        $queuedElementLevelModifiers[$elementID] += $thisLevelModifier;

        if ($isFirstQueueElement) {
            continue;
        }

        $objectToModifyLevels[$elementKey] += $elementCurrentLevelModifier;

        $elementPurchaseCost = Elements\calculatePurchaseCost(
            $elementID,
            Elements\getElementState($elementID, $planet, $user),
            [
                'purchaseMode' => (
                    $isUpgrade ?
                    Elements\PurchaseMode::Upgrade :
                    Elements\PurchaseMode::Downgrade
                )
            ]
        );

        foreach ($elementPurchaseCost as $costKey => $costValue) {
            if (!Resources\isSpendableResource($costKey)) {
                continue;
            }

            $queuedResourcesToUse[$costKey] += $costValue;
        }

        $objectToModifyLevels[$elementKey] -= $elementCurrentLevelModifier;
    }

    return [
        'queuedResourcesToUse' => $queuedResourcesToUse,
        'queuedElementLevelModifiers' => $queuedElementLevelModifiers,
        'queuedElementsCount' => count($queue),
    ];
}

?>
