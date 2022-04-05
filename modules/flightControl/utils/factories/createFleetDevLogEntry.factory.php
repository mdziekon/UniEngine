<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Factories;

/**
 * @param array $props
 * @param ref $props['currentPlanet']
 * @param number $props['newFleetId']
 * @param number $props['timestamp']
 * @param array $props['fleetData']
 * @param number $props['fuelUsage']
 */
function createFleetDevLogEntry ($props) {
    $currentPlanet = &$props['currentPlanet'];
    $newFleetId = $props['newFleetId'];
    $timestamp = $props['timestamp'];
    $fleetData = $props['fleetData'];
    $fuelUsage = $props['fuelUsage'];

    $entryAdditionalData = [
        Array2String($fleetData['array']),
    ];

    if ($fleetData['resources']['metal'] > 0) {
        $entryAdditionalData[] = 'M,'.$fleetData['resources']['metal'];
    }
    if ($fleetData['resources']['crystal'] > 0) {
        $entryAdditionalData[] = 'C,'.$fleetData['resources']['crystal'];
    }
    if ($fleetData['resources']['deuterium'] > 0) {
        $entryAdditionalData[] = 'D,'.$fleetData['resources']['deuterium'];
    }
    if ($fuelUsage > 0) {
        $entryAdditionalData[] = 'F,'.$fuelUsage;
    }

    return [
        'PlanetID' => $currentPlanet['id'],
        'Date' => $timestamp,
        'Place' => 10,
        'Code' => $fleetData['Mission'],
        'ElementID' => $newFleetId,
        'AdditionalData' => implode(';', $entryAdditionalData),
    ];
}

?>
