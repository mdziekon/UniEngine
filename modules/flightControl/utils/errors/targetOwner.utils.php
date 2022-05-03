<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Errors;

/**
 * @param object $error As returned by FlightControl\Utils\Validators\validateTargetOwner
 */
function mapTargetOwnerValidationErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];
    $errorParams = (
        isset($error['params']) ?
            $error['params'] :
            []
    );

    $knownErrorsByCode = [
        'TARGET_USER_BANNED' => $_Lang['fl3_CantSendBanned'],
        'TARGET_USER_ON_VACATION' => $_Lang['fl3_CantSendVacation'],
        'TARGET_ALLY_PROTECTION' => $_Lang['fl3_CantSendAlly'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['fleet_generic_errors_unknown'];
    }

    if (is_callable($knownErrorsByCode[$errorCode])) {
        return $knownErrorsByCode[$errorCode]($errorParams);
    }

    return $knownErrorsByCode[$errorCode];
}

?>
