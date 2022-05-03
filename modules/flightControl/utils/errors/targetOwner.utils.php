<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Errors;

use UniEngine\Engine\Modules\FlightControl\Utils\Errors;

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
        'NOOB_PROTECTION_VALIDATION_ERROR' => function ($params) {
            $errorMessage = Errors\mapNoobProtectionValidationErrorToReadableMessage($params);

            return $errorMessage;
        },
        'ADMIN_CANNOT_BE_AGGRESSIVE' => $_Lang['fl3_ProtectAdminCant'],
        'ADMIN_IS_PROTECTED_AGAINST_AGGRESSION' => $_Lang['fl3_ProtectCantAdmin'],
        'BASH_PROTECTION_VALIDATION_ERROR' => function ($params) {
            $errorMessage = Errors\mapBashLimitValidationErrorToReadableMessage($params);

            return $errorMessage;
        },
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
