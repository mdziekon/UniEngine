<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

/**
 * @param object $props
 * @param string $props['union']
 */
function extractUnionFleetIds($props) {
    $union = $props['union'];

    $rawJoinedFleetIds = explode(',', $union['fleets_id']);
    $joinedFleetIds = array_map_withkeys($rawJoinedFleetIds, function ($fleetEntry) {
        return str_replace('|', '', $fleetEntry);
    });

    $fleetIds = array_merge(
        [
            $union['main_fleet_id'],
        ],
        $joinedFleetIds
    );
    $fleetIds = Collections\compact($fleetIds);

    return $fleetIds;
}

?>
