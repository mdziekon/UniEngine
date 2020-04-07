<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Calculations;

use UniEngine\Engine\Includes\Helpers\World\Resources;

/**
 * @param array $params
 * @param number $params['fleetRow']
 * @param number $params['ships'] Ships that have survided the combat
 */
function calculatePillageStorage($params) {
    $fleetRow = $params['fleetRow'];
    $ships = $params['ships'];

    $pillageStorage = 0;

    foreach ($ships as $shipID => $shipCount) {
        $shipPillageStorage = getShipsPillageStorageCapacity($shipID);

        $pillageStorage += ($shipPillageStorage * $shipCount);
    }

    $pillagableResourceKeys = Resources\getKnownPillagableResourceKeys();

    foreach ($pillagableResourceKeys as $resourceKey) {
        $fleetResourcePropKey = "fleet_resource_{$resourceKey}";

        if (!isset($fleetRow[$fleetResourcePropKey])) {
            continue;
        }

        $pillageStorage -= $fleetRow[$fleetResourcePropKey];
    }

    return max($pillageStorage, 0);
}

?>
