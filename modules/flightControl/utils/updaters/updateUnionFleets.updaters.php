<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Updaters;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

/**
 * @param object $props
 * @param string $props['union']
 * @param object $props['updates']
 * @param number $props['updates']['slowdown']
 */
function updateUnionFleets($props) {
    $union = $props['union'];
    $updates = $props['updates'];
    $slowdown = (
        !empty($updates['slowdown']) ?
            $updates['slowdown'] :
            0
    );

    $fieldsToUpdate = array_merge(
        [],
        (
            $slowdown > 0 ?
                [
                    "`fleet_start_time` = `fleet_start_time` + {$slowdown}",
                    "`fleet_end_time` = `fleet_end_time` + {$slowdown}",
                ] :
                []
        )
    );
    $fieldsToUpdate = Collections\compact($fieldsToUpdate);

    if (empty($fieldsToUpdate)) {
        return;
    }

    $fleetIds = array_merge(
        [
            $union['main_fleet_id'],
        ],
        array_map_withkeys(explode(',', $union['fleets_id']), function ($fleetEntry) {
            return str_replace('|', '', $fleetEntry);
        })
    );
    $fleetIdsString = implode(', ', $fleetIds);

    $query = (
        "UPDATE {{table}} " .
        "SET " .
        implode(', ', $fieldsToUpdate) .
        " " .
        "WHERE " .
        "`fleet_id` IN ({$fleetIdsString}) " .
        ";"
    );

    doquery($query, 'fleets');
}

?>
