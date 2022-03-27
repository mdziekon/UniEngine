<?php

namespace UniEngine\Engine\Modules\Flights\Components\FlightsList;

use UniEngine\Engine\Modules\Flights\Components\FlightsList\Utils;

//  Arguments
//      - $props (Object)
//          - viewMode (Utils\ViewMode)
//          - flights
//          - viewingUserId (String)
//          - targetOwnerId (String)
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

    $viewMode = $props['viewMode'];
    $flights = $props['flights'];
    $viewingUserId = $props['viewingUserId'];
    $targetOwnerId = $props['targetOwnerId'];
    $currentTimestamp = $props['currentTimestamp'];
    $isPhalanxView = $viewMode === Utils\ViewMode::Phalanx;

    if ($flights->num_rows === 0) {
        return [
            'componentHTML' => ''
        ];
    }

    include_once("{$_EnginePath}includes/functions/BuildFleetEventTable.php");

    $flightsListEntries = [];

    while ($flight = $flights->fetch_assoc()) {
        $fleetId = $flight['fleet_id'];
        $fleetStartTime = $flight['fleet_start_time'];
        $fleetHoldTime = $flight['fleet_end_stay'];
        $fleetEndTime = $flight['fleet_end_time'];

        $isViewingUserFleetOwner = $flight['fleet_owner'] == $viewingUserId;
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
                'isViewingUserFleetOwner' => $isViewingUserFleetOwner,
                'isTargetOwnersFleet' => $isTargetOwnersFleet,
                'currentTimestamp' => $currentTimestamp
            ])
        ) {
            $entryKey = Utils\createFleetSortKey([
                'fleetId' => $fleetId,
                'eventTimestamp' => $fleetStartTime
            ]);

            $flightsListEntries[$entryKey] = BuildFleetEventTable(
                $flight,
                0,
                $isTargetOwnersFleet,
                $isPhalanxView
            );
        }

        if (
            $fleetHoldTime > $currentTimestamp &&
            Utils\isFleetHoldEntryVisible([
                'viewMode' => $viewMode,
                'flight' => $flight,
                'isViewingUserFleetOwner' => $isViewingUserFleetOwner,
                'isTargetOwnersFleet' => $isTargetOwnersFleet,
                'currentTimestamp' => $currentTimestamp
            ])
        ) {
            $entryKey = Utils\createFleetSortKey([
                'fleetId' => $fleetId,
                'eventTimestamp' => $fleetHoldTime
            ]);

            $flightsListEntries[$entryKey] = BuildFleetEventTable(
                $flight,
                1,
                $isTargetOwnersFleet,
                $isPhalanxView
            );
        }

        if (
            $fleetEndTime > $currentTimestamp &&
            Utils\isFleetEndEntryVisible([
                'viewMode' => $viewMode,
                'flight' => $flight,
                'isViewingUserFleetOwner' => $isViewingUserFleetOwner,
                'isTargetOwnersFleet' => $isTargetOwnersFleet,
                'currentTimestamp' => $currentTimestamp
            ])
        ) {
            $entryKey = Utils\createFleetSortKey([
                'fleetId' => $fleetId,
                'eventTimestamp' => $fleetEndTime
            ]);

            $flightsListEntries[$entryKey] = BuildFleetEventTable(
                $flight,
                2,
                $isTargetOwnersFleet,
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
