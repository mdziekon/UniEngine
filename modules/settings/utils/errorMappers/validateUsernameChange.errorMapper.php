<?php

namespace UniEngine\Engine\Modules\Settings\Utils\ErrorMappers;

/**
 * @param object $error As returned by Settings\Utils\Validators\validateUsernameChange
 */
function mapValidateUsernameChangeErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];

    $knownErrorsByCode = [
        'NOT_ENOUGH_DARK_ENERGY'            => $_Lang['NewNick_donthave_DE'],
        'NEW_USERNAME_SAME_AS_OLD'          => $_Lang['NewNick_is_like_old'],
        'NEW_USERNAME_TOO_SHORT'            => $_Lang['NewNick_is_tooshort'],
        'NEW_USERNAME_LINK_FORBIDDEN'       => $_Lang['NewNick_nolinks'],
        'NEW_USERNAME_INVALID_CHARACTERS'   => $_Lang['NewNick_badSigns'],
        'NEW_USERNAME_ALREADY_IN_USE'       => $_Lang['NewNick_already_taken'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['sys_unknownError'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
