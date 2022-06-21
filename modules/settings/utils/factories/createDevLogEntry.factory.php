<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Factories;

/**
 * @param array $params
 * @param number $params['currentTimestamp']
 */
function createVacationBeginDevLogEntry($params) {
    return [
        'PlanetID' => '0',
        'Date' => $params['currentTimestamp'],
        'Place' => 26,
        'Code' => '1',
        'ElementID' => '0',
    ];
}

/**
 * @param array $params
 * @param number $params['currentTimestamp']
 */
function createVacationFinishDevLogEntry($params) {
    return [
        'PlanetID' => '0',
        'Date' => $params['currentTimestamp'],
        'Place' => 26,
        'Code' => '2',
        'ElementID' => '0',
    ];
}

?>
