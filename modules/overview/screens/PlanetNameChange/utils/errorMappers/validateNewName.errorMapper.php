<?php

namespace UniEngine\Engine\Modules\Overview\Screens\PlanetNameChange\Utils\ErrorMappers;

/**
 * @param object $error As returned by Overview\Screens\PlanetNameChange\Utils\Validators\validateNewName
 */
function mapValidateNewNameErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];

    $knownErrorsByCode = [
        'NEW_NAME_EMPTY'        => $_Lang['RenamePlanet_0Lenght'],
        'NEW_NAME_SAME_AS_OLD'  => $_Lang['RenamePlanet_SameName'],
        'NEW_NAME_TOO_SHORT'    => $_Lang['RenamePlanet_TooShort'],
        'NEW_NAME_TOO_LONG'     => $_Lang['RenamePlanet_TooLong'],
        'NEW_NAME_INVALID'      => $_Lang['RenamePlanet_BadSigns'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['sys_unknownError'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
