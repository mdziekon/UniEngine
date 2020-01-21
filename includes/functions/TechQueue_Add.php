<?php

use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

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

    // TODO: Remove these checks from here,
    // they should be performed on the cmd pre-check level
    if (Users\isConductingResearch($user) && $user['current_planet'] != $planet['id']) {
        return;
    }
    if ($queueLength >= Users\getMaxResearchQueueLength($user)) {
        return;
    }

    $tempUser = $user;

    foreach ($queue as $queueElement) {
        $elementID = $queueElement['elementID'];
        $elementUserKey = _getElementUserKey($elementID);

        $tempUser[$elementUserKey] += 1;
    }

    $newElementUserKey = _getElementUserKey($newElementID);
    $newElementLevel = ($tempUser[$newElementUserKey] + 1);
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
