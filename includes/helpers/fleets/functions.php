<?php

use UniEngine\Engine\Includes\Helpers\Users;

function getShipsEngines($shipID) {
    global $_Vars_Prices;

    if (empty($_Vars_Prices[$shipID]['engine'])) {
        return [];
    }

    return $_Vars_Prices[$shipID]['engine'];
}

function getShipsStorageCapacity($shipID) {
    global $_Vars_Prices;

    return $_Vars_Prices[$shipID]['capacity'];
}

function getShipsUsedEngineData($shipID, $user) {
    $engines = getShipsEngines($shipID);

    // The assumption here is that better engines come first.
    // If the engine's tech is not set, we assume that it's the only engine available.
    foreach ($engines as $engineIdx => $engineData) {
        if (!isset($engineData['tech'])) {
            return [
                'engineIdx' => $engineIdx,
                'data' => $engineData
            ];
        }

        $engineTechID = $engineData['tech'];
        $engineTechMinLevel = $engineData['minlevel'];
        $userTechLevel = Users\getUsersTechLevel($engineTechID, $user);

        if ($userTechLevel >= $engineTechMinLevel) {
            return [
                'engineIdx' => $engineIdx,
                'data' => $engineData
            ];
        }
    }

    return [
        'engineIdx' => -1,
        'data' => null
    ];
}

function getShipsCurrentSpeed($shipID, $user) {
    $usedEngine = getShipsUsedEngineData($shipID, $user);

    if (!$usedEngine['data']) {
        return 0;
    }

    $engineData = $usedEngine['data'];

    if (!isset($engineData['tech'])) {
        return $engineData['speed'];
    }

    $engineTechID = $engineData['tech'];
    $engineSpeedTechModifier = Users\getUsersEngineSpeedTechModifier($engineTechID, $user);

    // TODO: determine if the modifier should not be applied with a "base bias"
    // meaning that it starts "improving" it starting from the minimal tech level.
    return (
        $engineData['speed'] *
        $engineSpeedTechModifier
    );
}

function getShipsCurrentConsumption($shipID, $user) {
    $usedEngine = getShipsUsedEngineData($shipID, $user);

    if (!$usedEngine['data']) {
        return 0;
    }

    return $usedEngine['data']['consumption'];
}

function getFleetShipsSpeeds($fleetShips, $user) {
    $speedsPerShip = [];

    foreach ($fleetShips as $shipID => $_shipsCount) {
        $speedsPerShip[$shipID] = getShipsCurrentSpeed($shipID, $user);
    }

    return $speedsPerShip;
}

//  Arguments:
//      - $origin (Object)
//          - galaxy (Number)
//          - system (Number)
//          - planet (Number)
//      - $destination (Object)
//          - galaxy (Number)
//          - system (Number)
//          - planet (Number)
//
function getFlightDistanceBetween($origin, $destination) {
    $galaxiesDiff = ($origin['galaxy'] - $destination['galaxy']);

    if ($galaxiesDiff != 0) {
        return (abs($galaxiesDiff) * 20000);
    }

    $systemsDiff = ($origin['system'] - $destination['system']);

    if ($systemsDiff != 0) {
        return ((abs($systemsDiff) * 5 * 19) + 2700);
    }

    $planetsDiff = ($origin['planet'] - $destination['planet']);

    if ($planetsDiff != 0) {
        return ((abs($planetsDiff) * 5) + 1000);
    }

    return 5;
}

//  Arguments:
//      - $flightParams (Object)
//          - speedFactor (Number)
//          - distance (Number)
//          - maxShipsSpeed (Number)
//
function getFlightDuration($flightParams) {
    $uniFleetsSpeedFactor = getUniFleetsSpeedFactor();

    $flightSpeedFactor = $flightParams['speedFactor'];
    $flightMaxShipsSpeed = $flightParams['maxShipsSpeed'];
    $flightDistance = $flightParams['distance'];

    $duration = (
        (35000 / $flightSpeedFactor * sqrt($flightDistance * 10 / $flightMaxShipsSpeed) + 10) /
        $uniFleetsSpeedFactor
    );

    return round($duration);
}

//  Arguments:
//      - $flightParams (Object)
//          - ships (Object<shipID, count>)
//          - distance (Number)
//          - duration (Number)
//      - $user (Object)
//
function getFlightTotalConsumption($flightParams, $user) {
    $uniFleetsSpeedFactor = getUniFleetsSpeedFactor();

    $flightShips = $flightParams['ships'];
    $flightDistance = $flightParams['distance'];
    $flightDuration = $flightParams['duration'];

    $totalConsumption = 0;

    foreach ($flightShips as $shipID => $shipsCount) {
        if ($shipsCount <= 0) {
            continue;
        }

        $shipSpeed = getShipsCurrentSpeed($shipID, $user);
        $shipConsumption = getShipsCurrentConsumption($shipID, $user);

        $finalSpeed = 35000 / ($flightDuration * $uniFleetsSpeedFactor - 10) * sqrt($flightDistance * 10 / $shipSpeed);

        $allShipsBaseConsumption = ($shipConsumption * $shipsCount);

        $allShipsConsumption = $allShipsBaseConsumption * $flightDistance / 35000 * (($finalSpeed / 10) + 1) * (($finalSpeed / 10) + 1);

        $totalConsumption += $allShipsConsumption;
    }

    return (round($totalConsumption) + 1);
}

?>
