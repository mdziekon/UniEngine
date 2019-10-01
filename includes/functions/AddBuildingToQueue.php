<?php

use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

function AddBuildingToQueue(&$planet, $user, $newElementID, $newElementIsUpgrading) {
    $queue = Planets\Queues\parseStructuresQueueString($planet['buildQueue']);
    $queueLength = count($queue);

    $currentTimestamp = time();
    $isFirstElement = ($queueLength === 0);

    $userMaxQueueLength = Users\getMaxStructuresQueueLength($user);

    if ($queueLength >= $userMaxQueueLength) {
        return false;
    }

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
    $newElementBuildModeLabel = ($newElementIsUpgrading ? "build" : "destroy");

    $newQueueElement = [
        'elementID' => $newElementID,
        'level' => $newElementLevel,
        'duration' => $newElementProgressTime,
        'endTimestamp' => $newElementEndTimestamp,
        'mode' => $newElementBuildModeLabel
    ];

    $queue[] = $newQueueElement;

    $planet['buildQueue'] = Planets\Queues\serializeStructuresQueue($queue);

    return count($queue);
}

?>
