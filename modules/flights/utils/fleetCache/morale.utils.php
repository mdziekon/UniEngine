<?php

namespace UniEngine\Engine\Modules\Flights\Utils\FleetCache;

/**
 * @param array $params
 * @param ref $params['fleetCache']
 * @param array $params['userID']
 * @param array $params['userMoraleData']
 */
function updateMoraleDataCache($params) {
    $fleetCache = &$params['fleetCache'];
    $userID = $params['userID'];
    $userMoraleData = $params['userMoraleData'];

    $fleetCache['MoraleCache'][$userID] = [
        'level' => $userMoraleData['morale_level'],
        'droptime' => $userMoraleData['morale_droptime'],
        'lastupdate' => $userMoraleData['morale_lastupdate'],
    ];
}

?>
