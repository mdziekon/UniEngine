<?php

namespace UniEngine\Engine\Modules\Flights\Components\FlightsList;

/**
 * @param object $params
 * @param string $params['fleetId']
 * @param number $params['eventTimestamp']
 */
function createFleetSortKey($params) {
    return implode('', [
        $params['eventTimestamp'],
        str_pad($params['fleetId'], 20, '0', STR_PAD_LEFT)
    ]);
}

//  Arguments
//      - $props (Object)
//          - flights
//          - targetOwnerId (String)
//          - isPhalanxView (Boolean)
//          - currentTimestamp (Number)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_EnginePath;

    $tplParams = [
        'flightsList' => null,
    ];

    $flights = $props['flights'];
    $targetOwnerId = $props['targetOwnerId'];
    $isPhalanxView = $props['isPhalanxView'];
    $currentTimestamp = $props['currentTimestamp'];

    if ($flights->num_rows === 0) {
        return [
            'componentHTML' => ''
        ];
    }

    include_once("{$_EnginePath}includes/functions/BuildFleetEventTable.php");

    $entryIdx = 0;
    $flightsListEntries = [];

    while ($flight = $flights->fetch_assoc()) {
        $entryIdx += 1;

        $fleetId = $flight['fleet_id'];
        $fleetStartTime = $flight['fleet_start_time'];
        $fleetHoldTime = $flight['fleet_end_stay'];
        $fleetEndTime = $flight['fleet_end_time'];

        $isFleetOwnedByTargetOwner = $flight['fleet_owner'] == $targetOwnerId;
        $isPartOfACSFlight = !empty($flight['fleets_id']);

        if ($isPhalanxView) {
            $flight['fleet_resource_metal'] = 0;
            $flight['fleet_resource_crystal'] = 0;
            $flight['fleet_resource_deuterium'] = 0;
        }

        if ($isPartOfACSFlight) {
            $flight['fleet_mission'] = 2;
        }

        if ($fleetStartTime > $currentTimestamp) {
            $entryKey = createFleetSortKey([
                'fleetId' => $fleetId,
                'eventTimestamp' => $fleetStartTime
            ]);
            $Label = 'fs';

            $flightsListEntries[$entryKey] = BuildFleetEventTable(
                $flight,
                0,
                $isFleetOwnedByTargetOwner,
                $Label,
                $entryIdx,
                $isPhalanxView
            );
        }

        // If the mission will eventually return to the origin place (not "stay")
        if ($flight['fleet_mission'] == 4) {
            continue;
        }

        if ($fleetHoldTime > $currentTimestamp) {
            $entryKey = createFleetSortKey([
                'fleetId' => $fleetId,
                'eventTimestamp' => $fleetHoldTime
            ]);
            $Label = 'ft';

            $flightsListEntries[$entryKey] = BuildFleetEventTable(
                $flight,
                1,
                $isFleetOwnedByTargetOwner,
                $Label,
                $entryIdx,
                $isPhalanxView
            );
        }
        if (
            $isFleetOwnedByTargetOwner &&
            $fleetEndTime > $currentTimestamp
        ) {
            $entryKey = createFleetSortKey([
                'fleetId' => $fleetId,
                'eventTimestamp' => $fleetEndTime
            ]);
            $Label = 'fe';

            $flightsListEntries[$entryKey] = BuildFleetEventTable(
                $flight,
                2,
                $isFleetOwnedByTargetOwner,
                $Label,
                $entryIdx,
                $isPhalanxView
            );
        }
    }

    ksort($flightsListEntries, SORT_STRING);

    $tplParams['flightsList'] = implode('', $flightsListEntries);

    return [
        'componentHTML' => $tplParams['flightsList']
    ];
}

?>
