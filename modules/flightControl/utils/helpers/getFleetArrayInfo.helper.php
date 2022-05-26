<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers\FleetArray;

function getAllShipsCount($fleetArray) {
    return array_sum($fleetArray);
}

function getShipsTotalStorage($fleetArray) {
    $storages = [
        'allPurpose' => 0,
        'fuelOnly' => 0,
    ];

    foreach ($fleetArray as $shipID => $shipCount) {
        $allShipsOfTypeStorage = getShipsStorageCapacity($shipID) * $shipCount;

        if (canShipPillage($shipID)) {
            $storages['allPurpose'] += $allShipsOfTypeStorage;
        } else {
            $storages['fuelOnly'] += $allShipsOfTypeStorage;
        }
    }

    return $storages;
}

?>
