<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Updaters;

/**
 * @param object $props
 * @param string $props['fleetId']
 * @param string $props['newAcsId']
 */
function updateFleetArchiveAcsId($props) {
    $fleetId = $props['fleetId'];
    $newAcsId = $props['newAcsId'];

    $query = (
        "UPDATE {{table}} " .
        "SET " .
        "`Fleet_ACSID` = {$newAcsId} " .
        "WHERE " .
        "`Fleet_ID` = {$fleetId} " .
        "LIMIT 1 " .
        ";"
    );

    doquery($query, 'fleet_archive');
}

?>
