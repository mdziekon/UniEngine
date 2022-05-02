<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Inputs;

/**
 * @param array $props
 * @param objectRef $props['fleetEntry']
 */
function normalizeFleetResourcesInputs($props) {
    $fleetEntry = &$props['fleetEntry'];

    foreach ($fleetEntry['resources'] as &$resourceAmount) {
        $normalizedAmount = floor(floatval(str_replace('.', '', $resourceAmount)));

        if ($normalizedAmount == 0) {
            $normalizedAmount = '0';
        }

        $resourceAmount = $normalizedAmount;
    }
}

?>
