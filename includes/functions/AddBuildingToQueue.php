<?php

use UniEngine\Engine\Includes\Helpers\Planets;

//  Arguments:
//      - $planet (Object)
//      - $user (Object)
//      - $newElementID (String)
//      - $newElementIsUpgrading (Boolean)
//      - $params (Object)
//          - currentTimestamp (Number)
//
function AddBuildingToQueue(&$planet, $user, $newElementID, $newElementIsUpgrading, $params) {
    $currentTimestamp = $params['currentTimestamp'];

    $queueString = Planets\Queues\Structures\getQueueString($planet);
    $queue = Planets\Queues\Structures\parseQueueString($queueString);
    $queueLength = count($queue);
    $isFirstElement = ($queueLength === 0);

    $tempPlanet = $planet;

    foreach ($queue as $queueElement) {
        $elementID = $queueElement['elementID'];
        $elementPlanetKey = _getElementPlanetKey($elementID);
        $elementIsUpgrading = ($queueElement['mode'] == 'build');

        $tempPlanet[$elementPlanetKey] += ($elementIsUpgrading ? 1 : -1);
    }

    $newElementPlanetKey = _getElementPlanetKey($newElementID);
    $newElementLevel = (
        $tempPlanet[$newElementPlanetKey] +
        ($newElementIsUpgrading ? 1 : -1)
    );
    $newElementProgressTimeMultiplier = (
        $newElementIsUpgrading ?
        1 :
        (1 / 2)
    );
    $newElementProgressTime = (
        GetBuildingTime($user, $tempPlanet, $newElementID) *
        $newElementProgressTimeMultiplier
    );
    $newElementStartTimestamp = (
        $isFirstElement ?
        $currentTimestamp :
        $queue[$queueLength - 1]['endTimestamp']
    );
    $newElementEndTimestamp = ($newElementStartTimestamp + $newElementProgressTime);
    $newElementBuildModeLabel = ($newElementIsUpgrading ? 'build' : 'destroy');

    $newQueueElement = [
        'elementID' => $newElementID,
        'level' => $newElementLevel,
        'duration' => $newElementProgressTime,
        'endTimestamp' => $newElementEndTimestamp,
        'mode' => $newElementBuildModeLabel
    ];

    $queue[] = $newQueueElement;

    Planets\Queues\Structures\setQueueString(
        $planet,
        Planets\Queues\Structures\serializeQueue($queue)
    );

    return count($queue);
}

?>
