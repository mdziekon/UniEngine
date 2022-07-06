<?php

namespace UniEngine\Engine\Modules\Overview\Screens\AbandonPlanet\Utils\ErrorMappers;

/**
 * @param object $error As returned by Overview\Screens\AbandonPlanet\Utils\Validators\validateAbandonPlanet
 *                      As returned by Overview\Screens\AbandonPlanet\Utils\Effects\tryAbandonPlanet
 */
function mapTryAbandonPlanetErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];

    $knownErrorsByCode = [
        'CONFIRM_PASSWORD_EMPTY'                    => $_Lang['Abandon_NoPassword'],
        'CONFIRM_PASSWORD_INVALID'                  => $_Lang['Abandon_BadPassword'],
        'CANT_ABANDON_MOTHER_PLANET'                => $_Lang['Abandon_CantAbandonMother'],
        'INVALID_PLANET_TYPE'                       => $_Lang['Abandon_BadPlanetrowData'],
        'ABANDON_IMPOSSIBLE_TECH_IN_PROGRESS'       => $_Lang['Abandon_TechHere'],
        'ABANDON_ERROR_SQL'                         => $_Lang['Abandon_SQLError'],
        'ABANDON_IMPOSSIBLE_FLIGHTS_IN_PROGRESS'    => $_Lang['Abandon_FlyingFleetsHere'],
        'ABANDON_IMPOSSIBLE_FLIGHTS_ON_MOON'        => $_Lang['Abandon_FlyingFleetsMoon'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['sys_unknownError'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
