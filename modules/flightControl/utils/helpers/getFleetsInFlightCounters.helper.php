<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

/**
 * @param array $props
 * @param number $props['userId']
 */
function getFleetsInFlightCounters ($props) {
    $userId = $props['userId'];

    $aggressiveMissionTypes = [ 1, 2, 9 ];

    $counters = [
        'allFleetsInFlight' => 0,
        'expeditionsInFlight' => 0,
        'aggressiveFleetsInFlight' => [
            'byTargetId' => [],
            'byTargetOwnerId' => [],
        ],
    ];

    $query = (
        "SELECT " .
        "`fleet_mission`, `fleet_target_owner`, `fleet_end_id`, `fleet_mess` " .
        "FROM {{table}} " .
        "WHERE " .
        "`fleet_owner` = {$userId} " .
        ";"
    );
    $queryResult = doquery($query, 'fleets');

    while ($fleetData = $queryResult->fetch_assoc()) {
        $counters['allFleetsInFlight'] += 1;

        if ($fleetData['fleet_mission'] == 15) {
            $counters['expeditionsInFlight'] += 1;
        }

        // Don't increment counters if fleet has been already calculated
        if ($fleetData['fleet_mess'] != 0) {
            continue;
        }

        // Skip if fleet is non-aggressive
        if (!in_array($fleetData['fleet_mission'], $aggressiveMissionTypes)) {
            continue;
        }

        $targetId = $fleetData['fleet_end_id'];
        $targetOwnerId = $fleetData['fleet_target_owner'];

        if (!isset($counters['aggressiveFleetsInFlight']['byTargetId'][$targetId])) {
            $counters['aggressiveFleetsInFlight']['byTargetId'][$targetId] = 0;
        }
        if (!isset($counters['aggressiveFleetsInFlight']['byTargetOwnerId'][$targetOwnerId])) {
            $counters['aggressiveFleetsInFlight']['byTargetOwnerId'][$targetOwnerId] = 0;
        }

        $counters['aggressiveFleetsInFlight']['byTargetId'][$targetId] += 1;
        $counters['aggressiveFleetsInFlight']['byTargetOwnerId'][$targetOwnerId] += 1;
    }

    return $counters;
}

?>
