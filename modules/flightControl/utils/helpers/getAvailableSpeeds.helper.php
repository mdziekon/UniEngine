<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

/**
 * @param array $props
 * @param ref $props['user']
 * @param number $props['timestamp']
 */
function getAvailableSpeeds ($props) {
    $user = &$props['user'];
    $timestamp = $props['timestamp'];

    $speedsAvailable = [
        10,
        9,
        8,
        7,
        6,
        5,
        4,
        3,
        2,
        1,
    ];

    if ($user['admiral_time'] > $timestamp) {
        $speedsAvailable[] = 12;
        $speedsAvailable[] = 11;
        $speedsAvailable[] = 0.5;
        $speedsAvailable[] = 0.25;
    }

    if (MORALE_ENABLED) {
        $maxSpeedAvailable = max($speedsAvailable);

        if ($user['morale_level'] >= MORALE_BONUS_FLEETSPEEDUP1) {
            $speedBoostValue = $maxSpeedAvailable + (MORALE_BONUS_FLEETSPEEDUP1_VALUE / 10);

            $speedsAvailable[] = $speedBoostValue;
        }

        if ($user['morale_level'] >= MORALE_BONUS_FLEETSPEEDUP2) {
            $speedBoostValue = $maxSpeedAvailable + (MORALE_BONUS_FLEETSPEEDUP2_VALUE / 10);

            $speedsAvailable[] = $speedBoostValue;
        }
    }

    rsort($speedsAvailable);

    return $speedsAvailable;
}

?>
