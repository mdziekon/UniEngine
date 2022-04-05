<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Updaters;

/**
 * @param object $props
 * @param array $props['fleetIds']
 * @param number $props['flightAdditionalTime']
 */
function updateFleetArchiveACSEntries ($props) {
    $fleetIdsQueryVal = implode(', ', $props['fleetIds']);
    $flightAdditionalTime = $props['flightAdditionalTime'];

    $query = (
        "UPDATE {{table}} " .
        "SET " .
        "`Fleet_Time_ACSAdd` = `Fleet_Time_ACSAdd` + {$flightAdditionalTime} " .
        "WHERE " .
        "`Fleet_ID` IN ({$fleetIdsQueryVal}) " .
        ";"
    );

    doquery($query, 'fleet_archive');
}

?>
