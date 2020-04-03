<?php

namespace UniEngine\Engine\Modules\Flights\Utils\FleetCache;

use UniEngine\Engine\Includes\Helpers\World\Elements;

abstract class WorldElementCounterType {
    const ElementDestroyed = 0;
    const ElementLost = 1;
}

/**
 * @param array $params
 * @param &array $params['userStats']
 * @param string $params['userID']
 * @param number $params['elementID']
 * @param number $params['elementCount']
 * @param WorldElementCounterType $params['counterType']
 */
function incrementUserStatsWorldElementCounter($params) {
    $elementID = $params['elementID'];

    if (
        !Elements\isShip($elementID) &&
        !Elements\isDefenseSystem($elementID)
    ) {
        return;
    }

    $entryKey = (
        $params['counterType'] === WorldElementCounterType::ElementDestroyed ?
        "destroyed_{$elementID}" :
        "lost_{$elementID}"
    );

    _incrementUserStatsEntry(
        $params['userStats'],
        $params['userID'],
        $entryKey,
        $params['elementCount']
    );
}

function _incrementUserStatsEntry(
    &$userStatsObj,
    $userID,
    $entryKey,
    $incrementValue
) {
    $userStatsObj[$userID][$entryKey] += $incrementValue;
}

?>
