<?php

namespace UniEngine\Engine\Modules\Phalanx\Utils\Effects;

use UniEngine\Engine\Modules\Phalanx;

/**
 * @param array $params
 * @param number $params['scanCost']
 * @param arrayRef $params['phalanxMoon']
 * @param number $params['currentTimestamp']
 */
function updateMoonFuelOnUsage($params) {
    global $UserDev_Log;

    $scanCost = $params['scanCost'];
    $phalanxMoon = &$params['phalanxMoon'];
    $currentTimestamp = $params['currentTimestamp'];


    $phalanxMoon['deuterium'] -= $scanCost;

    Phalanx\Utils\Queries\updatePhalanxMoon([
        'scanCost' => $scanCost,
        'phalanxMoonId' => $phalanxMoon['id'],
    ]);

    $UserDev_Log[] = [
        'PlanetID' => $phalanxMoon['id'],
        'Date' => $currentTimestamp,
        'Place' => 29,
        'Code' => '0',
        'ElementID' => '0',
        'AdditionalData' => "",
    ];
}

?>
