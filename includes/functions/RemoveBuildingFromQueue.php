<?php

use UniEngine\Engine\Includes\Helpers\Planets;

//  Arguments:
//      - $planet (Object)
//      - $user (Object)
//      - $listID (Number)
//          - Index of the item to be removed.
//            WARNING: starts from 1 instead of 0
//
function RemoveBuildingFromQueue(&$planet, $user, $listID) {
    $removedElementIdx = $listID - 1;

    $queue = Planets\Queues\parseStructuresQueueString($planet['buildQueue']);

    $removedQueueElement = $queue[$removedElementIdx];
    $removedElementID = $removedQueueElement['elementID'];

    // Recalculate the queue, mostly its elements' durations
    // - removing element with mode "build" might have changed next "destroy" elements
    // - removing robo or nano factory might have increased upgrade durations
    $newQueue = [];

    // TODO: accept argument that allows to configure this variable
    $currentTimestamp = time();
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

    $planet['buildQueue'] = Planets\Queues\serializeStructuresQueue($newQueue);

    return $removedElementID;
}

?>
