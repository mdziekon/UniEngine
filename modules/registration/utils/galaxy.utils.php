<?php

namespace UniEngine\Engine\Modules\Registration\Utils\Galaxy;

//  Arguments
//      - $params (Object)
//          - galaxy (Number)
//          - systemMin (Number)
//          - systemMax (Number)
//          - planetMin (Number)
//          - planetMax (Number)
//          - invertPlanetCoords (Boolean)
//
function _getPlanetsInGalaxyBetweenCoordinates($params) {
    $selectPlanetsQuery = (
        "SELECT " .
        "`system`, `planet` " .
        "FROM {{table}} " .
        "WHERE " .
        "`galaxy` = {$params['galaxy']} AND  " .
        (
            (
                isset($params['systemMin']) &&
                isset($params['systemMax'])
            ) ?
                "`system` BETWEEN {$params['systemMin']} AND {$params['systemMax']} AND " :
                ""
        ) .
        "`planet` " . (!empty($params['systemMax']) ? "NOT" : "") . " BETWEEN {$params['planetMin']} AND {$params['planetMax']} " .
        ";"
    );

    $selectPlanetsResult = doquery($selectPlanetsQuery, 'galaxy');

    $selectPlanets = mapQueryResults($selectPlanetsResult, function ($planetRow) {
        return "{$planetRow['system']}:{$planetRow['planet']}";
    });
    $selectPlanets = object_map($selectPlanets, function ($value, $key) {
        return [
            true,
            $value
        ];
    });

    return $selectPlanets;
}

//  Arguments
//      - $params (Object)
//          - preferredGalaxy (Number)
//
function findNewPlanetPosition($params) {
    $GalaxyNo = $params['preferredGalaxy'];

    $SystemsRange = 25;
    $SystemRandom = mt_rand(1, MAX_SYSTEM_IN_GALAXY);

    if (($SystemRandom + $SystemsRange) >= MAX_SYSTEM_IN_GALAXY) {
        $System_Lower = $SystemRandom - $SystemsRange;
    } else {
        $System_Lower = $SystemRandom;
    }

    $System_Higher = $System_Lower + $SystemsRange;
    $Planet_Lower = 4;
    $Planet_Higher = 12;

    // - Attempt 1: check random range of solar systems
    $Position_NonFree = _getPlanetsInGalaxyBetweenCoordinates([
        'galaxy' => $GalaxyNo,
        'systemMin' => $System_Lower,
        'systemMax' => $System_Higher,
        'planetMin' => $Planet_Lower,
        'planetMax' => $Planet_Higher,
    ]);
    $Position_NonFreeCount = count($Position_NonFree);
    $Position_TotalCount = (($System_Higher - $System_Lower) + 1) * (($Planet_Higher - $Planet_Lower) + 1);

    if (($Position_TotalCount - $Position_NonFreeCount) > 0) {
        // TODO: Figure out a better, more deterministic way
        while (true) {
            $System = mt_rand($System_Lower, $System_Higher);
            $Planet = mt_rand($Planet_Lower, $Planet_Higher);

            if (!isset($Position_NonFree["{$System}:{$Planet}"])) {
                return [
                    'galaxy' => $GalaxyNo,
                    'system' => $System,
                    'planet' => $Planet,
                ];
            }
        }
    }


    // - Attempt 2: check whole galaxy, if space not found earlier
    $Position_NonFree = _getPlanetsInGalaxyBetweenCoordinates([
        'galaxy' => $GalaxyNo,
        'planetMin' => $Planet_Lower,
        'planetMax' => $Planet_Higher,
    ]);
    $Position_NonFreeCount = count($Position_NonFree);
    $Position_TotalCount = MAX_SYSTEM_IN_GALAXY * (($Planet_Higher - $Planet_Lower) + 1);

    if (($Position_TotalCount - $Position_NonFreeCount) > 0) {
        // TODO: Figure out a better, more deterministic way
        while (true) {
            $System = mt_rand(1, MAX_SYSTEM_IN_GALAXY);
            $Planet = mt_rand($Planet_Lower, $Planet_Higher);

            if (!isset($Position_NonFree["{$System}:{$Planet}"])) {
                return [
                    'galaxy' => $GalaxyNo,
                    'system' => $System,
                    'planet' => $Planet,
                ];
            }
        }
    }

    // - Attempt 3: check whole galaxy and all slots which has not been checked
    $Planet_PosArray = [];
    for ($i = 1; $i < $Planet_Lower; $i += 1) {
        $Planet_PosArray[] = $i;
    }
    // TODO: Fix end range
    for ($i = $Planet_Higher; $i < MAX_PLANET_IN_SYSTEM; $i += 1) {
        $Planet_PosArray[] = $i;
    }

    $Position_NonFree = _getPlanetsInGalaxyBetweenCoordinates([
        'galaxy' => $GalaxyNo,
        'planetMin' => $Planet_Lower,
        'planetMax' => $Planet_Higher,
        'invertPlanetCoords' => true,
    ]);
    $Position_NonFreeCount = count($Position_NonFree);
    $Position_TotalCount = MAX_SYSTEM_IN_GALAXY * count($Planet_PosArray);

    if (($Position_TotalCount - $Position_NonFreeCount) > 0) {
        // TODO: Figure out a better, more deterministic way
        while (true) {
            $System = mt_rand(1, MAX_SYSTEM_IN_GALAXY);
            $Planet = $Planet_PosArray[array_rand($Planet_PosArray)];

            if (!isset($Position_NonFree["{$System}:{$Planet}"])) {
                return [
                    'galaxy' => $GalaxyNo,
                    'system' => $System,
                    'planet' => $Planet,
                ];
            }
        }
    }

    return null;
}

?>
