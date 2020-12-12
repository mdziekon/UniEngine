<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

/**
 * @param array $props
 */
function getAvailableHoldTimes ($props) {
    $availableHoldTimes = [
        1,
        2,
        4,
        8,
        16,
        32,
    ];

    return $availableHoldTimes;
}

?>
