<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Errors;

/**
 * @param object $error As returned by FlightControl\Utils\Validators\validateJoinUnion
 */
function mapJoinUnionValidationErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['errorCode'];

    $knownErrorsByCode = [
        'INVALID_UNION_ID'                      => $_Lang['fl_acs_bad_group_id'],
        'UNION_NOT_FOUND'                       => $_Lang['fl_acs_bad_group_id'],
        'USER_CANT_JOIN'                        => $_Lang['fl_acs_cannot_join_this_group'],
        'INVALID_DESTINATION_COORDINATES'       => $_Lang['fl_acs_badcoordinates'],
        'UNION_JOINED_FLEETS_COUNT_EXCEEDED'    => $_Lang['fl_acs_fleetcount_extended'],
        'UNION_JOIN_TIME_EXCEEDED'              => $_Lang['fl_acs_cannot_join_time_extended'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['fleet_generic_errors_unknown'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
