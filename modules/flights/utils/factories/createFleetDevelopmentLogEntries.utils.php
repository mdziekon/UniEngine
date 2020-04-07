<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Factories;

/**
 * @param array $params
 * @param array $params['originalShips'] Ships that have participated in the combat
 * @param array $params['postCombatShips'] Ships that have survived the combat
 * @param array $params['resourcesPillage']
 *              (default: `[]`)
 */
function createFleetDevelopmentLogEntries($params) {
    $originalShips = $params['originalShips'];
    $postCombatShips = (
        isset($params['postCombatShips']) ?
            $params['postCombatShips'] :
            []
    );
    $resourcesPillage = (
        isset($params['resourcesPillage']) ?
            $params['resourcesPillage'] :
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

    $pillagableResourceKeyMapping = [
        'metal' => 'M',
        'crystal' => 'C',
        'deuterium' => 'D',
    ];

    foreach ($resourcesPillage as $resourceKey => $pillagedAmount) {
        if ($pillagedAmount <= 0) {
            continue;
        }
        if (!isset($pillagableResourceKeyMapping[$resourceKey])) {
            // No mapping available yet,
            // probably because of a custom resource added to the game
            continue;
        }

        $newEntryParams = [
            $pillagableResourceKeyMapping[$resourceKey],
            $pillagedAmount,
        ];
        $entries[] = implode(',', $newEntryParams);
    }

    return $entries;
}

?>
