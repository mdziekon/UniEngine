<?php

namespace UniEngine\Engine\Includes\Helpers\Planets\Queues\Structures;

function getQueueString($planet) {
    return $planet['buildQueue'];
}

function setQueueString(&$planet, $queueString) {
    $planet['buildQueue'] = $queueString;
}

function getQueueLength($planet) {
    $queueString = getQueueString($planet);
    $queue = parseQueueString($queueString);

    return count($queue);
}

function getFirstQueueElement($planet) {
    $queueString = getQueueString($planet);
    $queue = parseQueueString($queueString);

    if (count($queue) < 1) {
        return null;
    }

    return $queue[0];
}

function hasElementInQueue($planet, $elementID) {
    $queueString = getQueueString($planet);
    $elementIDString = "{$elementID}";

    $elementIDStringLength = strlen($elementIDString);

    return (
        substr($queueString, 0, ($elementIDStringLength + 1)) == "{$elementIDString}," ||
        strstr($queueString, ";{$elementIDString},") !== false
    );
}

function parseQueueString($queueString) {
    if (empty($queueString)) {
        return [];
    }

    $queueElementStrings = explode(";", $queueString);

    return array_map(
        function ($queueElementString) {
            $queueElementData = explode(",", $queueElementString);

            return [
                'elementID' => $queueElementData[0],
                'level' => $queueElementData[1],
                'duration' => $queueElementData[2],
                'endTimestamp' => $queueElementData[3],
                'mode' => $queueElementData[4]
            ];
        },
        $queueElementStrings
    );
}

function serializeQueue($queue) {
    $serializedElements = array_map(
        function ($queueElement) {
            $detailsAsArray = [
                $queueElement['elementID'],
                $queueElement['level'],
                $queueElement['duration'],
                $queueElement['endTimestamp'],
                $queueElement['mode']
            ];

            return implode(",", $detailsAsArray);
        },
        $queue
    );

    return implode(";", $serializedElements);
}

?>
