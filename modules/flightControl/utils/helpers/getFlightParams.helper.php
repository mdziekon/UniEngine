<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

function _getFleetParamsNormalFlight($params) {
    $durationToDestination = getFlightDuration([
        'speedFactor' => $params['fleetSpeed'],
        'distance' => $params['distance'],
        'maxShipsSpeed' => $params['maxFleetSpeed']
    ]);
    $durationBackToOrigin = $durationToDestination;

    $fuelConsumption = getFlightTotalConsumption(
        [
            'ships' => $params['fleet']['array'],
            'distance' => $params['distance'],
            'duration' => $durationToDestination,
        ],
        $params['user']
    );

    return [
        'duration' => [
            'toDestination' => $durationToDestination,
            'backToOrigin' => $durationBackToOrigin,
        ],
        'consumption' => $fuelConsumption,
    ];
}

function _getFleetParamsUsingQuantumGate($params) {
    if ($params['quantumGateUseType'] == 1) {
        return [
            'duration' => [
                'toDestination' => 1,
                'backToOrigin' => 1,
            ],
            'consumption' => 0,
        ];
    }

    $durationToDestination = 1;
    $durationBackToOrigin = getFlightDuration([
        'speedFactor' => $params['fleetSpeed'],
        'distance' => $params['distance'],
        'maxShipsSpeed' => $params['maxFleetSpeed']
    ]);

    $fuelConsumption = getFlightTotalConsumption(
        [
            'ships' => $params['fleet']['array'],
            'distance' => $params['distance'],
            'duration' => $durationBackToOrigin,
        ],
        $params['user']
    );
    $fuelConsumption = $fuelConsumption / 2;

    return [
        'duration' => [
            'toDestination' => $durationToDestination,
            'backToOrigin' => $durationBackToOrigin,
        ],
        'consumption' => $fuelConsumption,
    ];
}

/**
 * @param array $props
 * @param array $props['user']
 * @param array $props['fleet']
 * @param array $props['fleetSpeed']
 * @param array $props['distance']
 * @param array $props['maxFleetSpeed']
 * @param array $props['isUsingQuantumGate']
 * @param array $props['quantumGateUseType']
 */
function getFleetParams($params) {
    return (
        $params['isUsingQuantumGate'] ?
            _getFleetParamsUsingQuantumGate($params) :
            _getFleetParamsNormalFlight($params)
    );
}

?>
