<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Factories;

use UniEngine\Engine\Includes\Helpers\World\Resources;

/**
 * @param array $params
 * @param string $params['fleetID']
 * @param string $params['state']
 *               (default: `null`)
 * @param array $params['originalShips']
 *              Ships that have participated in the combat
 * @param array $params['postCombatShips']
 *              (default: `[]`)
 *              Ships that have survived the combat
 * @param array $params['resourcesPillage']
 *              (default: `Record<PillagableResourceKey, 0>`)
 */
function createFleetUpdateEntry($params) {
    $state = (
        isset($params['state']) ?
            $params['state'] :
            null
    );
    $originalShips = $params['originalShips'];
    $postCombatShips = (
        isset($params['postCombatShips']) ?
            $params['postCombatShips'] :
            []
    );

    $updateEntry = [
        'fleet_id' => $params['fleetID'],
        'fleet_mess' => $state,
        'fleet_amount' => 0,
        'fleet_array' => [],
        'fleet_array_lost' => [],
        // fleet_resource_*
    ];

    $pillagableResourceKeys = Resources\getKnownPillagableResourceKeys();

    foreach ($pillagableResourceKeys as $resourceKey) {
        $entryKey = "fleet_resource_{$resourceKey}";

        $updateEntry[$entryKey] = (
            isset($params['resourcesPillage'][$resourceKey]) ?
                $params['resourcesPillage'][$resourceKey] :
                0
        );
    }

    foreach ($postCombatShips as $shipID => $shipCount) {
        if ($shipCount <= 0) {
            continue;
        }

        $updateEntry['fleet_amount'] += $shipCount;
        $updateEntry['fleet_array'][$shipID] = $shipCount;
    }
    foreach ($originalShips as $shipID => $shipOriginalCount) {
        $shipPostCombatCount = (
            isset($postCombatShips[$shipID]) ?
                $postCombatShips[$shipID] :
                0
        );
        $shipLostCount = ($shipOriginalCount - $shipPostCombatCount);

        if ($shipLostCount <= 0) {
            continue;
        }

        $updateEntry['fleet_array_lost'][$shipID] = $shipLostCount;
    }

    return $updateEntry;
}

?>
