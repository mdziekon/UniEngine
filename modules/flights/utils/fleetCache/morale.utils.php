<?php

namespace UniEngine\Engine\Modules\Flights\Utils\FleetCache;

/**
 * @param array $params
 * @param ref $params['fleetCache']
 * @param array $params['userID']
 */
function hasCachedMoraleData($params) {
    $userID = $params['userID'];

    return !empty($params['fleetCache']['MoraleCache'][$userID]);
}

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

/**
 * @param array $params
 * @param ref $params['destination']
 * @param ref $params['fleetCache']
 * @param array $params['userID']
 */
function loadMoraleDataFromCache($params) {
    $destination = &$params['destination'];
    $userID = $params['userID'];
    $cacheEntry = $params['fleetCache']['MoraleCache'][$userID];

    if (!hasCachedMoraleData($params)) {
        return;
    }

    $destination['morale_level'] = $cacheEntry['level'];
    $destination['morale_droptime'] = $cacheEntry['droptime'];
    $destination['morale_lastupdate'] = $cacheEntry['lastupdate'];
}

?>
