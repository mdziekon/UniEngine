<?php

namespace UniEngine\Engine\Modules\Overview\Screens\AbandonPlanet\Utils\Effects;

/**
 * @param array $params
 * @param array $params['abandonedPlanetIds']
 * @param number $params['currentTimestamp']
 */
function updateUserDevLog($props) {
    global $UserDev_Log;

    $abandonedPlanetIds = $props['abandonedPlanetIds'];
    $currentTimestamp = $props['currentTimestamp'];

    foreach ($abandonedPlanetIds as $planetId) {
        $UserDev_Log[] = [
            'PlanetID' => $planetId,
            'Date' => $currentTimestamp,
            'Place' => 25,
            'Code' => '0',
            'ElementID' => '0',
        ];
    }
}

?>
