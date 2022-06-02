<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

/**
 * @param array $params
 * @param Array<{ speed: number }> $params['shipsDetails']
 * @param ref $params['user']
 */
function getSlowestShipSpeed($params) {
    $shipsDetails = $params['shipsDetails'];
    $user = &$params['user'];

    $slowestShipSpeed = min(
        array_map_withkeys($shipsDetails, function ($shipDetails) {
            return $shipDetails['speed'];
        })
    );

    if (
        MORALE_ENABLED &&
        $user['morale_level'] <= MORALE_PENALTY_FLEETSLOWDOWN
    ) {
        $slowestShipSpeed *= MORALE_PENALTY_FLEETSLOWDOWN_VALUE;
    }

    return $slowestShipSpeed;
}

?>
