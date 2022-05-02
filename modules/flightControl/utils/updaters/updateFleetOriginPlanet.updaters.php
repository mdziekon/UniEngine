<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Updaters;

/**
 * @param object $props
 * @param objectRef $props['originPlanet']
 * @param object $props['fleetEntry']
 * @param number $props['fuelConsumption']
 * @param object $props['quantumGateUsage']
 * @param boolean $props['quantumGateUsage']['isUsing']
 * @param number $props['quantumGateUsage']['usageType']
 * @param number $props['currentTimestamp']
 */
function updateFleetOriginPlanet($props) {
    $originPlanet = &$props['originPlanet'];
    $fleetEntry = $props['fleetEntry'];
    $fuelConsumption = $props['fuelConsumption'];
    $quantumGateUsage = $props['quantumGateUsage'];
    $currentTimestamp = $props['currentTimestamp'];

    $shouldUpdateQuantumGate = (
        $quantumGateUsage['isUsing'] &&
        $quantumGateUsage['usageType'] == 2
    );

    // Update in-memory representation
    $originPlanet['metal'] -= $fleetEntry['resources']['metal'];
    $originPlanet['crystal'] -= $fleetEntry['resources']['crystal'];
    $originPlanet['deuterium'] -= ($fleetEntry['resources']['deuterium'] + $fuelConsumption);

    foreach ($fleetEntry['array'] as $shipId => $shipCount) {
        $elementPlanetKey = _getElementPlanetKey($shipId);
        $originPlanet[$elementPlanetKey] -= $shipCount;
    }

    if ($shouldUpdateQuantumGate) {
        $originPlanet['quantumgate_lastuse'] = $currentTimestamp;
    }

    // Prepare in-db representation update
    $shipUpdates = array_map_withkeys($fleetEntry['array'], function ($shipCount, $shipId) {
        $elementPlanetKey = _getElementPlanetKey($shipId);

        return "`{$elementPlanetKey}` = `{$elementPlanetKey}` - $shipCount";
    });
    $shipUpdatesString = implode(", ", $shipUpdates);

    $query = (
        "UPDATE {{table}} " .
        "SET " .
        "{$shipUpdatesString}, " .
        (
            $shouldUpdateQuantumGate ?
                "`quantumgate_lastuse` = {$currentTimestamp}, " :
                ""
        ) .
        "`metal` = '{$originPlanet['metal']}', " .
        "`crystal` = '{$originPlanet['crystal']}', " .
        "`deuterium` = '{$originPlanet['deuterium']}' " .
        "WHERE " .
        "`id` = {$originPlanet['id']} " .
        "LIMIT 1 " .
        ";"
    );

    doquery('LOCK TABLE {{table}} WRITE', 'planets');
    doquery($query, 'planets');
    doquery('UNLOCK TABLES', '');
}

?>
