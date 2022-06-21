<?php

namespace UniEngine\Engine\Modules\Settings\Utils\ErrorMappers;

/**
 * @param object $error As returned by Settings\Utils\Validators\validatePasswordChange
 */
function mapValidatePasswordChangeErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];

    $knownErrorsByCode = [
        'OLD_PASSWORD_INCORRECT'            => $_Lang['Pass_old_isbad'],
        'NEW_PASSWORD_TOO_SHORT'            => $_Lang['Pass_is_tooshort'],
        'NEW_PASSWORD_SAME_AS_OLD'          => $_Lang['Pass_same_as_old'],
        'NEW_PASSWORD_CONFIRMATION_INVALID' => $_Lang['Pass_Confirm_isbad'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['sys_unknownError'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
