<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Errors;

/**
 * @param object $error As returned by FlightControl\Utils\Validators\validateBashLimit
 */
function mapBashLimitValidationErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];
    $errorParams = $error['params'];

    $limitType = $errorParams['limitType'];
    $limitTypeLabel = (
        $limitType === 'bash' ?
            $_Lang['fl3_Protect_AttackLimit_bash'] :
            $_Lang['fl3_Protect_AttackLimit_farm']
    );

    $knownErrorsByCode = [
        'ATTACK_LIMIT_PERPLAYER_REACHED' => function ($params) use (&$_Lang, $limitTypeLabel) {
            return sprintf(
                $_Lang['fl3_Protect_AttackLimitTotal'],
                $limitTypeLabel
            );
        },
        'ATTACK_AND_FLIGHTS_LIMIT_PERPLAYER_REACHED' => function ($params) use (&$_Lang, $limitTypeLabel) {
            return sprintf(
                $_Lang['fl3_Protect_AttackLimitTotalFly'],
                $limitTypeLabel
            );
        },
        'ATTACK_LIMIT_PERPLANET_REACHED' => function ($params) use (&$_Lang, $limitTypeLabel) {
            return sprintf(
                $_Lang['fl3_Protect_AttackLimitSingle'],
                $limitTypeLabel
            );
        },
        'ATTACK_AND_FLIGHTS_LIMIT_PERPLAYER_REACHED' => function ($params) use (&$_Lang, $limitTypeLabel) {
            return sprintf(
                $_Lang['fl3_Protect_AttackLimitSingleFly'],
                $limitTypeLabel
            );
        },
    ];

    if (is_callable($knownErrorsByCode[$errorCode])) {
        return $knownErrorsByCode[$errorCode]($errorParams);
    }

    return $knownErrorsByCode[$errorCode];
}

?>
