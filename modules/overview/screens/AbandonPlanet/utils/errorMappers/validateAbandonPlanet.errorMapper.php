<?php

namespace UniEngine\Engine\Modules\Overview\Screens\AbandonPlanet\Utils\ErrorMappers;

/**
 * @param object $error As returned by Overview\Screens\AbandonPlanet\Utils\Validators\validateAbandonPlanet
 */
function mapValidateAbandonPlanetErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];

    $knownErrorsByCode = [
        'CONFIRM_PASSWORD_EMPTY'        => $_Lang['Abandon_NoPassword'],
        'CONFIRM_PASSWORD_INVALID'      => $_Lang['Abandon_BadPassword'],
        'CANT_ABANDON_MOTHER_PLANET'    => $_Lang['Abandon_CantAbandonMother'],
        'INVALID_PLANET_TYPE'           => $_Lang['Abandon_BadPlanetrowData'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['sys_unknownError'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
