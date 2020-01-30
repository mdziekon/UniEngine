<?php

use UniEngine\Engine\Includes\Helpers\Planets;

//  Arguments:
//      - $planet (Object)
//      - $user (Object)
//      - $listID (Number)
//          - Index of the item to be removed.
//            WARNING: starts from 1 instead of 0
//      - $params (Object)
//          - currentTimestamp (Number)
//
function RemoveBuildingFromQueue(&$planet, $user, $listID, $params) {
    $removedElementIdx = $listID - 1;
    $currentTimestamp = $params['currentTimestamp'];

    $queueString = Planets\Queues\Structures\getQueueString($planet);
    $queue = Planets\Queues\Structures\parseQueueString($queueString);

    $removedQueueElement = $queue[$removedElementIdx];
    $removedElementID = $removedQueueElement['elementID'];

    // Recalculate the queue, mostly its elements' durations
    // - removing element with mode "build" might have changed next "destroy" elements
    // - removing robo or nano factory might have increased upgrade durations
    $newQueue = [];

    $tempPlanet = $planet;
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
        $elementPlanetKey = _getElementPlanetKey($elementID);
        $isUpgrading = ($queueElement['mode'] == 'build');

        if ($queueElementIdx < $removedElementIdx) {
            $tempPlanet[$elementPlanetKey] += ($isUpgrading ? 1 : -1);

            $newQueue[] = $queueElement;

            continue;
        }

        $elementProgressTimeMultiplier = (
            $isUpgrading ?
            1 :
            (1 / 2)
        );
        $elementNewProgressTime = (
            GetBuildingTime($user, $tempPlanet, $elementID) *
            $elementProgressTimeMultiplier
        );
        $progressTimeDifference = ($queueElement['duration'] - $elementNewProgressTime);

        $summedDurationDifference += $progressTimeDifference;

        $tempPlanet[$elementPlanetKey] += ($isUpgrading ? 1 : -1);

        $newQueueElement = $queueElement;

        $newQueueElement['level'] = $tempPlanet[$elementPlanetKey];
        $newQueueElement['duration'] = $elementNewProgressTime;
        $newQueueElement['endTimestamp'] = (
            $queueElement['endTimestamp'] -
            $summedDurationDifference
        );

        $newQueue[] = $newQueueElement;
    }

    Planets\Queues\Structures\setQueueString(
        $planet,
        Planets\Queues\Structures\serializeQueue($newQueue)
    );

    return $removedElementID;
}

?>
