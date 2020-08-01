<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Calculations;

/**
 * @param array $params
 * @param number $params['totalDebris']
 */
function calculateMoonCreationRoll($params) {
    $totalDebris = $params['totalDebris'];

    $resourcesPerPercent = _getMoonCreationChanceResourcesPerPercent();
    $creationChanceMax = _getMoonCreationMaxChance();

    $totalMoonChance = floor($totalDebris / $resourcesPerPercent);
    $boundedMoonChance = min($totalMoonChance, $creationChanceMax);

    $roll = mt_rand(1, 100);

    $hasMoonBeenCreated = (
        $boundedMoonChance > 0 &&
        $roll <= $boundedMoonChance
    );

    return [
        'hasMoonBeenCreated' => $hasMoonBeenCreated,
        'totalMoonChance' => $totalMoonChance,
        'boundedMoonChance' => $boundedMoonChance,
    ];
}

function _getMoonCreationChanceResourcesPerPercent() {
    return COMBAT_MOONPERCENT_RESOURCES;
}

function _getMoonCreationMaxChance() {
    return 20;
}

?>
