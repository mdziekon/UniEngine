<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param array $props['newFleet']
 * @param array $props['timestamp']
 * @param ref $props['user']
 * @param ref $props['destinationEntry']
 */
function validateJoinUnion ($props) {
    $isValid = function () {
        return [
            'isValid' => true,
        ];
    };
    $isInvalid = function ($errors) {
        return [
            'isValid' => false,
            'errors' => $errors,
        ];
    };

    $newFleet = $props['newFleet'];
    $timestamp = $props['timestamp'];
    $user = &$props['user'];
    $destinationEntry = &$props['destinationEntry'];

    if (!($newFleet['ACS_ID'] > 0)) {
        return $isInvalid([
            [ 'errorCode' => 'INVALID_UNION_ID', ],
        ]);
    }

    $unionJoinData = FlightControl\Utils\Helpers\getFleetUnionJoinData([
        'newFleet' => $newFleet,
    ]);

    if (!($unionJoinData)) {
        return $isInvalid([
            [ 'errorCode' => 'UNION_NOT_FOUND', ],
        ]);
    }

    if (
        !(
            $unionJoinData['owner_id'] == $user['id'] ||
            strstr($unionJoinData['users'], "|{$user['id']}|") !== false
        )
    ) {
        return $isInvalid([
            [ 'errorCode' => 'USER_CANT_JOIN', ],
        ]);
    }

    if (!($unionJoinData['end_target_id'] == $destinationEntry['id'])) {
        return $isInvalid([
            [ 'errorCode' => 'INVALID_DESTINATION_COORDINATES', ],
        ]);
    }

    if (!($unionJoinData['fleets_count'] < ACS_MAX_JOINED_FLEETS)) {
        return $isInvalid([
            [ 'errorCode' => 'UNION_JOINED_FLEETS_COUNT_EXCEEDED', ],
        ]);
    }

    if (!($unionJoinData['start_time'] > $timestamp)) {
        return $isInvalid([
            [ 'errorCode' => 'UNION_JOIN_TIME_EXCEEDED', ],
        ]);
    }

    return $isValid();
}

?>
