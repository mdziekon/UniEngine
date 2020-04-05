<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Factories;

/**
 * @param array $params
 * @param array $params['originalShips'] Ships that have participated in the combat
 * @param array $params['postCombatShips'] Ships that have survived the combat
 */
function createFleetDevelopmentLogEntries($params) {
    $originalShips = $params['originalShips'];
    $postCombatShips = (
        isset($params['postCombatShips']) ?
            $params['postCombatShips'] :
            []
    );

    $entries = [];

    foreach ($originalShips as $shipID => $shipOriginalCount) {
        $shipPostCombatCount = (
            isset($postCombatShips[$shipID]) ?
                $postCombatShips[$shipID] :
                0
        );
        $shipCountDifference = ($shipOriginalCount - $shipPostCombatCount);

        if ($shipCountDifference <= 0) {
            continue;
        }

        $entries[] = implode(',', [ $shipID, $shipCountDifference ]);
    }

    return $entries;
}

?>
