<?php

namespace UniEngine\Engine\Modules\Settings\Utils\ErrorMappers;

/**
 * @param object $error As returned by Settings\Utils\Helpers\tryIgnoreUser
 */
function mapTryIgnoreUserErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];

    $knownErrorsByCode = [
        'CANT_IGNORE_YOURSELF'          => $_Lang['Ignore_CannotIgnoreYourself'],
        'INVALID_USER_SELECTOR'         => $_Lang['Ignore_BadSignsOrShort'],
        'USER_NOT_FOUND'                => $_Lang['Ignore_UserNoExists'],
        'CANT_IGNORE_GAMETEAM_MEMBER'   => $_Lang['Ignore_CannotIgnoreGameTeam'],
        'USER_ALREADY_IGNORED'          => $_Lang['Ignore_ThisUserAlreadyIgnored'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['fleet_generic_errors_unknown'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
