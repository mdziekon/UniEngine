<?php

use UniEngine\Engine\Includes\Helpers\Planets;

//  Arguments:
//      - $planet (Object)
//      - $user (Object)
//      - $newElementID (String)
//      - $params (Object)
//          - currentTimestamp (Number)
//
function TechQueue_Add(&$planet, &$user, $newElementID, $params) {
    $currentTimestamp = $params['currentTimestamp'];

    $queueString = Planets\Queues\Research\getQueueString($planet);
    $queue = Planets\Queues\Research\parseQueueString($queueString);
    $queueLength = count($queue);
    $isFirstElement = ($queueLength === 0);

    $tempUser = $user;

    foreach ($queue as $queueElement) {
        $elementID = $queueElement['elementID'];
        $elementPlanetKey = _getElementUserKey($elementID);

        $tempUser[$elementPlanetKey] += 1;
    }

    $newElementPlanetKey = _getElementUserKey($newElementID);
    $newElementLevel = ($tempUser[$newElementPlanetKey] + 1);
    $newElementProgressTimeMultiplier = 1;
    $newElementProgressTime = (
        GetBuildingTime($tempUser, $planet, $newElementID) *
        $newElementProgressTimeMultiplier
    );
    $newElementStartTimestamp = (
        $isFirstElement ?
        $currentTimestamp :
        $queue[$queueLength - 1]['endTimestamp']
    );
    $newElementEndTimestamp = ($newElementStartTimestamp + $newElementProgressTime);

    $newQueueElement = [
        'elementID' => $newElementID,
        'level' => $newElementLevel,
        'duration' => $newElementProgressTime,
        'endTimestamp' => $newElementEndTimestamp
    ];

    $queue[] = $newQueueElement;

    Planets\Queues\Research\setQueueString(
        $planet,
        Planets\Queues\Research\serializeQueue($queue)
    );
}

?>
