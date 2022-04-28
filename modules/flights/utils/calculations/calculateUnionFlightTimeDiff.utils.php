<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Calculations;

define('MAX_UNION_FLIGHT_SLOWDOWN_PERCENTAGE', 0.3);

/**
 * @param array $params
 * @param number $params['fleetAtDestinationTimestamp']
 * @param object $params['union']
 */
function calculateUnionFlightTimeDiff($params) {
    $fleetAtDestinationTimestamp = $params['fleetAtDestinationTimestamp'];
    $union = $params['union'];

    $mainUnionFleetOriginalFlightTime = $union['start_time_org'] - $union['mf_start_time'];
    $newFleetFlightTimeDifference = $fleetAtDestinationTimestamp - $union['start_time_org'];

    /**
     * TODO: this is most likely impossible, but add protection against
     * division by zero just in case, like the old code did.
     */
    if ($mainUnionFleetOriginalFlightTime == 0) {
        $mainUnionFleetOriginalFlightTime = 1;
    }
    if ($newFleetFlightTimeDifference == 0) {
        $newFleetFlightTimeDifference = 1;
    }

    if (($newFleetFlightTimeDifference / $mainUnionFleetOriginalFlightTime) > MAX_UNION_FLIGHT_SLOWDOWN_PERCENTAGE) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'FLIGHT_TOO_SLOW',
            ],
        ];
    }

    if ($fleetAtDestinationTimestamp <= $union['start_time']) {
        $fleetSlowdown = ($union['start_time'] - $fleetAtDestinationTimestamp);

        return [
            'isSuccess' => true,
            'payload' => [
                'newFleetSlowDownBy' => $fleetSlowdown,
            ],
        ];
    }

    $unionSlowdown = ($fleetAtDestinationTimestamp - $union['start_time']);

    return [
        'isSuccess' => true,
        'payload' => [
            'unionSlowDownBy' => $unionSlowdown,
        ],
    ];
}

?>
