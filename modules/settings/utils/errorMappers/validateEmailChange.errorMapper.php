<?php

namespace UniEngine\Engine\Modules\Settings\Utils\ErrorMappers;

/**
 * @param object $error As returned by Settings\Utils\Validators\validateEmailChange
 */
function mapValidateEmailChangeErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];

    $knownErrorsByCode = [
        'EMAIL_CHANGE_IN_PROGRESS'          => $_Lang['Mail_alreadyInChange'],
        'INVALID_EMAIL'                     => $_Lang['Mail_badEmail'],
        'NEW_EMAIL_SAME_AS_OLD'             => $_Lang['Mail_same_as_old'],
        'NEW_EMAIL_CONFIRMATION_INVALID'    => $_Lang['Mail_Confirm_isbad'],
        'BANNED_DOMAIN_USED'                => $_Lang['Mail_banned_domain'],
        'NEW_EMAIL_ALREADY_IN_USE'          => $_Lang['Mail_some1_hasemail'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['fleet_generic_errors_unknown'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
