<?php

namespace UniEngine\Engine\Modules\FlightControl\Screens\SendWizardStepOne\Components\UnionManagement\Utils;

use UniEngine\Engine\Modules\Flights;
use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param array $props['unionOwner']
 * @param array $props['input']
 * @param number $props['currentTimestamp']
 */
function getBaseUnionData($props) {
    $unionOwner = $props['unionOwner'];
    $userId = $unionOwner['id'];
    $currentTimestamp = $props['currentTimestamp'];
    $input = $props['input'];

    $inputFleetId = (
        isset($input['fleet_id']) ?
            intval($input['fleet_id'], 10) :
            0
    );

    if ($inputFleetId <= 0) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'INVALID_FLEET_ID',
            ],
        ];
    }

    $unionMainFleet = FlightControl\Utils\Fetchers\fetchUnionFleet([
        'fleetId' => $inputFleetId,
    ]);

    if (
        $unionMainFleet['fleet_id'] != $inputFleetId ||
        $unionMainFleet['fleet_owner'] != $userId
    ) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'FLEET_DOES_NOT_EXIST',
            ],
        ];
    }

    if (
        $unionMainFleet['fleet_mission'] != Flights\Enums\FleetMission::Attack ||
        $unionMainFleet['fleet_mess'] != 0
    ) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'FLEET_INCORRECT_PARAMS',
            ],
        ];
    }

    if ($unionMainFleet['fleet_start_time'] <= $currentTimestamp) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'FLEET_REACHED_TARGET',
            ],
        ];
    }

    return [
        'isSuccess' => true,
        'payload' => [
            'unionMainFleet' => $unionMainFleet,
        ],
    ];
}

?>
