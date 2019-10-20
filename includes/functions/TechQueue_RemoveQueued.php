<?php

use UniEngine\Engine\Includes\Helpers\Planets;

//  Arguments:
//      - $planet (Object)
//      - $user (Object)
//      - $removedElementIdx (Number)
//          - Index of the item to be removed. Starts from 0.
//      - $params (Object)
//          - currentTimestamp (Number)
//
function TechQueue_RemoveQueued(&$planet, &$user, $removedElementIdx, $params) {
    $currentTimestamp = $params['currentTimestamp'];

    $queueString = Planets\Queues\Research\getQueueString($planet);
    $queue = Planets\Queues\Research\parseQueueString($queueString);

    $removedQueueElement = $queue[$removedElementIdx];
    $removedElementID = $removedQueueElement['elementID'];

    // Recalculate the queue, mostly its elements' durations
    // - there might have been a special technology queued that causes duration change
    $newQueue = [];

    $tempUser = $user;
    $summedDurationDifference = 0;

    foreach ($queue as $queueElementIdx => $queueElement) {
        if ($queueElementIdx == $removedElementIdx) {
            if ($queueElementIdx === 0) {
                $summedDurationDifference += (
                    $queueElement['endTimestamp'] -
                    $currentTimestamp
                );
            } else {
                $summedDurationDifference += $queueElement['duration'];
            }

            continue;
        }

        $elementID = $queueElement['elementID'];
        $elementUserKey = _getElementUserKey($elementID);

        if ($queueElementIdx < $removedElementIdx) {
            $tempUser[$elementUserKey] += 1;

            $newQueue[] = $queueElement;

            continue;
        }

        $elementProgressTimeMultiplier = 1;
        $elementNewProgressTime = (
            GetBuildingTime($tempUser, $planet, $elementID) *
            $elementProgressTimeMultiplier
        );
        $progressTimeDifference = ($queueElement['duration'] - $elementNewProgressTime);

        $summedDurationDifference += $progressTimeDifference;

        $tempUser[$elementUserKey] += 1;

        $newQueueElement = $queueElement;

        $newQueueElement['level'] = $tempUser[$elementUserKey];
        $newQueueElement['duration'] = $elementNewProgressTime;
        $newQueueElement['endTimestamp'] = (
            $queueElement['endTimestamp'] -
            $summedDurationDifference
        );

        $newQueue[] = $newQueueElement;
    }

    Planets\Queues\Research\setQueueString(
        $planet,
        Planets\Queues\Research\serializeQueue($newQueue)
    );

    return $removedElementID;
}

?>
