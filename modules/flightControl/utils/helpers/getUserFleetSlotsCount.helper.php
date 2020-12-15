<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

use UniEngine\Engine\Includes\Helpers\Users;

/**
 * @param array $props
 * @param ref $props['user']
 * @param number $props['timestamp']
 */
function getUserFleetSlotsCount ($props) {
    $user = &$props['user'];
    $timestamp = $props['timestamp'];

    $computerTechLevel = Users\getUsersTechLevel(108, $user);
    $isAdmiralActive = ($user['admiral_time'] > $timestamp);

    return (
        1 +
        $computerTechLevel +
        ($isAdmiralActive ? 2 : 0)
    );
}

?>
