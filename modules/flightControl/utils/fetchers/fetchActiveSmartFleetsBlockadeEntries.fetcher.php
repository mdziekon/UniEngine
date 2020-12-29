<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Fetchers;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

/**
 * @param array $props
 * @param number $props['timestamp']
 * @param array $props['fleetOwnerDetails']
 * @param number $props['fleetOwnerDetails']['userId']
 * @param number $props['fleetOwnerDetails']['planetId']
 * @param array $props['targetOwnerDetails']
 * @param number $props['targetOwnerDetails']['userId']
 * @param number $props['targetOwnerDetails']['planetId']
 */
function fetchActiveSmartFleetsBlockadeEntries ($props) {
    $timestamp = $props['timestamp'];
    $targetIsOccupied = !empty($props['targetOwnerDetails']);

    $userIdsToCheck = Collections\compact([
        $props['fleetOwnerDetails']['userId'],
        (
            $targetIsOccupied ?
            $props['targetOwnerDetails']['userId'] :
            null
        ),
    ]);
    $planetIdsToCheck = Collections\compact([
        $props['fleetOwnerDetails']['planetId'],
        (
            $targetIsOccupied ?
            $props['targetOwnerDetails']['planetId'] :
            null
        ),
    ]);
    $userIdsToCheckString = implode(', ', $userIdsToCheck);
    $planetIdsToCheckString = implode(', ', $planetIdsToCheck);

    $query = (
        "SELECT " .
        "`Type`, `BlockMissions`, `Reason`, `StartTime`, `EndTime`, `PostEndTime`, `ElementID`, `DontBlockIfIdle` " .
        "FROM {{table}} " .
        "WHERE " .
        "`StartTime` <= {$timestamp} AND " .
        "( " .
            "(`Type` = 1 AND (`EndTime` > {$timestamp} OR `PostEndTime` > {$timestamp})) OR " .
            "(`Type` = 2 AND `ElementID` IN ({$userIdsToCheckString}) AND `EndTime` > {$timestamp}) OR " .
            "(`Type` = 3 AND `ElementID` IN ({$planetIdsToCheckString}) AND `EndTime` > {$timestamp}) " .
        ") " .
        "ORDER BY `Type` ASC, `EndTime` DESC " .
        ";"
    );

    return doquery($query, 'smart_fleet_blockade');
}

?>
