<?php

namespace UniEngine\Engine\Modules\Flights\Utils\FleetCache;

use UniEngine\Engine\Includes\Helpers\World\Resources;

/**
 * Updates galaxy entry by adding debris.
 * Either updates the cache, or the DB itself (when no cache entry exists).
 *
 * @param array $params
 * @param array $params['debris']
 * @param array $params['targetPlanet']
 * @param ref $params['fleetCache']
 */
function updateGalaxyDebris($params) {
    $cacheUpdateResult = _updateFleetCacheGalaxyDebris($params);

    if (
        !($cacheUpdateResult['isSuccess']) &&
        $cacheUpdateResult['hasNoFleetCacheGalaxyMapping']
    ) {
        _updateDBEntryGalaxyDebris($params);
    }
}

/**
 * @see updateGalaxyDebris()
 */
function _updateFleetCacheGalaxyDebris($params) {
    $debris = $params['debris'];
    $targetPlanet = $params['targetPlanet'];
    $fleetCache = &$params['fleetCache'];

    $targetID = $targetPlanet['id'];
    $galaxyMapIndexBy = (
        $targetPlanet['planet_type'] == 1 ?
        'byPlanet' :
        'byMoon'
    );

    if (
        !isset($fleetCache['galaxyMap'][$galaxyMapIndexBy][$targetID]) ||
        !($fleetCache['galaxyMap'][$galaxyMapIndexBy][$targetID] > 0)
    ) {
        return [
            'isSuccess' => false,
            'hasNoFleetCacheGalaxyMapping' => true,
        ];
    }

    $galaxyEntryID = $fleetCache['galaxyMap'][$galaxyMapIndexBy][$targetID];

    $fleetCacheGalaxyEntry = &$fleetCache['galaxy'][$galaxyEntryID];

    foreach (Resources\getKnownDebrisRecoverableResourceKeys() as $resourceKey) {
        if (!isset($fleetCacheGalaxyEntry[$resourceKey])) {
            $fleetCacheGalaxyEntry[$resourceKey] = 0;
        }
        if (!isset($debris[$resourceKey])) {
            continue;
        }

        $fleetCacheGalaxyEntry[$resourceKey] += $debris[$resourceKey];
    }

    $fleetCacheGalaxyEntry['updated'] = true;
    $fleetCache['updated']['galaxy'] = true;

    return [
        'isSuccess' => true,
    ];
}

/**
 * @see updateGalaxyDebris()
 */
function _updateDBEntryGalaxyDebris($params) {
    $debris = $params['debris'];
    $targetPlanet = $params['targetPlanet'];

    $targetID = $targetPlanet['id'];
    $galaxyEntryIndexName = (
        $targetPlanet['planet_type'] == 1 ?
        'id_planet' :
        'id_moon'
    );

    $query_UpdateGalaxyResources = [];

    foreach (Resources\getKnownDebrisRecoverableResourceKeys() as $resourceKey) {
        if (
            !isset($debris[$resourceKey]) ||
            !($debris[$resourceKey] > 0)
        ) {
            continue;
        }

        $query_UpdateGalaxyResources[] = "`{$resourceKey}` = `{$resourceKey}` + {$debris[$resourceKey]}";
    }

    if (empty($query_UpdateGalaxyResources)) {
        return [
            'isSuccess' => false,
            'isUpdateUnnecessary' => true,
        ];
    }

    $query_UpdateGalaxy = '';
    $query_UpdateGalaxy .= "UPDATE {{table}} SET ";
    $query_UpdateGalaxy .= implode(', ', $query_UpdateGalaxyResources) . " ";
    $query_UpdateGalaxy .= "WHERE `{$galaxyEntryIndexName}` = {$targetID} LIMIT 1; ";
    $query_UpdateGalaxy .= "-- MISSION ATTACK / DESTROY / GROUP ATTACK [Q02][FID: {$targetID}]";
    doquery($query_UpdateGalaxy, 'galaxy');

    return [
        'isSuccess' => true,
    ];
}

?>
