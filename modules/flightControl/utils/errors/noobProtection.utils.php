<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Errors;

/**
 * @param object $error As returned by FlightControl\Utils\Validators\validateNoobProtection
 */
function mapNoobProtectionValidationErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];
    $errorParams = $error['params'];

    $knownErrorsByCode = [
        'ATTACKER_STATISTICS_UNAVAILABLE' => $_Lang['fl3_ProtectURStatNotCalc'],
        'TARGET_STATISTICS_UNAVAILABLE' => $_Lang['fl3_ProtectHIStatNotCalc'],
        'ATTACKER_NOOBPROTECTION_ENDTIME_NOT_REACHED' => function ($params) use (&$_Lang) {
            return sprintf(
                $_Lang['fl3_ProtectNewTimeYou'],
                pretty_time($params['timeLeft'])
            );
        },
        'TARGET_NEVER_LOGGED_IN' => $_Lang['fl3_ProtectNewTimeHe2'],
        'TARGET_NOOBPROTECTION_ENDTIME_NOT_REACHED' => function ($params) use (&$_Lang) {
            return sprintf(
                $_Lang['fl3_ProtectNewTimeHe'],
                pretty_time($params['timeLeft'])
            );
        },
        'ATTACKER_NOOBPROTECTION_BASIC_LIMIT_NOT_REACHED' => function ($params) use (&$_Lang) {
            return sprintf(
                $_Lang['fl3_ProtectURWeak'],
                prettyNumber($params['basicLimit'])
            );
        },
        'TARGET_NOOBPROTECTION_BASIC_LIMIT_NOT_REACHED' => function ($params) use (&$_Lang) {
            return sprintf(
                $_Lang['fl3_ProtectHIWeak'],
                prettyNumber($params['basicLimit'])
            );
        },
        'TARGET_NOOBPROTECTION_TOO_WEAK_BY_MULTIPLIER' => function ($params) use (&$_Lang) {
            return sprintf(
                $_Lang['fl3_ProtectUR2Strong'],
                prettyNumber($params['weakMultiplier'])
            );
        },
        'ATTACKER_NOOBPROTECTION_TOO_WEAK_BY_MULTIPLIER' => function ($params) use (&$_Lang) {
            return sprintf(
                $_Lang['fl3_ProtectHI2Strong'],
                prettyNumber($params['weakMultiplier'])
            );
        },
    ];

    if (is_callable($knownErrorsByCode[$errorCode])) {
        return $knownErrorsByCode[$errorCode]($errorParams);
    }

    return $knownErrorsByCode[$errorCode];
}

?>
