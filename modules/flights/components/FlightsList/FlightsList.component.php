<?php

namespace UniEngine\Engine\Modules\Flights\Components\FlightsList;

use UniEngine\Engine\Modules\Flights\Components\FlightsList\Utils;

/**
 * @param object $params
 * @param string $params['fleetId']
 * @param number $params['eventTimestamp']
 */
function _createFleetSortKey($params) {
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

    $viewMode = Utils\ViewMode::Phalanx;
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

        $isTargetOwnersFleet = $flight['fleet_owner'] == $targetOwnerId;
        $isPartOfACSFlight = !empty($flight['fleets_id']);

        if ($isPhalanxView) {
            $flight['fleet_resource_metal'] = 0;
            $flight['fleet_resource_crystal'] = 0;
            $flight['fleet_resource_deuterium'] = 0;
        }

        if ($isPartOfACSFlight) {
            $flight['fleet_mission'] = 2;
        }

        if (
            $fleetStartTime > $currentTimestamp &&
            Utils\isFleetStartEntryVisible([
                'viewMode' => $viewMode,
                'flight' => $flight,
                'isTargetOwnersFleet' => $isTargetOwnersFleet
            ])
        ) {
            $entryKey = _createFleetSortKey([
                'fleetId' => $fleetId,
                'eventTimestamp' => $fleetStartTime
            ]);
            $Label = 'fs';

            $flightsListEntries[$entryKey] = BuildFleetEventTable(
                $flight,
                0,
                $isTargetOwnersFleet,
                $Label,
                $entryIdx,
                $isPhalanxView
            );
        }

        if (
            $fleetHoldTime > $currentTimestamp &&
            Utils\isFleetHoldEntryVisible([
                'viewMode' => $viewMode,
                'flight' => $flight,
                'isTargetOwnersFleet' => $isTargetOwnersFleet
            ])
        ) {
            $entryKey = _createFleetSortKey([
                'fleetId' => $fleetId,
                'eventTimestamp' => $fleetHoldTime
            ]);
            $Label = 'ft';

            $flightsListEntries[$entryKey] = BuildFleetEventTable(
                $flight,
                1,
                $isTargetOwnersFleet,
                $Label,
                $entryIdx,
                $isPhalanxView
            );
        }

        if (
            $fleetEndTime > $currentTimestamp &&
            Utils\isFleetEndEntryVisible([
                'viewMode' => $viewMode,
                'flight' => $flight,
                'isTargetOwnersFleet' => $isTargetOwnersFleet
            ])
        ) {
            $entryKey = _createFleetSortKey([
                'fleetId' => $fleetId,
                'eventTimestamp' => $fleetEndTime
            ]);
            $Label = 'fe';

            $flightsListEntries[$entryKey] = BuildFleetEventTable(
                $flight,
                2,
                $isTargetOwnersFleet,
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
