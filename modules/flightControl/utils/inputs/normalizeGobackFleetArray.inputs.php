<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Inputs;

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Includes\Helpers\World\Elements;

/**
 * @param array $props
 * @param string $props['fleetArray']
 */
function normalizeGobackFleetArrayInput($props) {
    $fleetArray = $props['fleetArray'];

    $gobackFleet = String2Array($fleetArray);
    $gobackFleet = object_map($gobackFleet, function ($shipCount, $shipId) {
        if (!Elements\isShip($shipId)) {
            return [ null, $shipId ];
        }

        return [ $shipCount, $shipId ];
    });
    $gobackFleet = Collections\compact($gobackFleet);

    return $gobackFleet;
}

?>
