<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Errors;

/**
 * @param object $error As returned by FlightControl\Utils\Helpers\tryJoinUnion
 */
function mapTryJoinUnionErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];

    $knownErrorsByCode = [
        'INVALID_UNION_ID'          => $_Lang['fl1_ACSNoExist'],
        'UNION_JOIN_TIME_EXPIRED'   => $_Lang['fl1_ACSTimeUp'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['fleet_generic_errors_unknown'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
