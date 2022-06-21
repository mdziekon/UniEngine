<?php

namespace UniEngine\Engine\Modules\Settings\Utils\ErrorMappers;

/**
 * @param object $error As returned by Settings\Utils\Helpers\tryEnableVacation
 */
function mapTryEnableVacationErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];
    $errorParams = $error['params'];

    $knownErrorsByCode = [
        'VACATION_MODE_NOT_AVAILABLE_YET'       => $_Lang['Vacation_24hNotPassed'],
        'VACATION_CANNOT_START_FLYING_FLEETS'   => $_Lang['Vacation_FlyingFleets'],
        'VACATION_CANNOT_START_DEVELOPMENT'     => function ($params) use (&$_Lang) {
            $blockingPlanets = array_map_withkeys($params['blockingPlanets'], function ($planet) {
                return "{$planet['name']} [{$planet['galaxy']}:{$planet['system']}:{$planet['planet']}]";
            });
            $blockingPlanetLabels = implode(', ', $blockingPlanets);

            return sprintf($_Lang['Vacation_CannotBuildOrRes'], $blockingPlanetLabels);
        },
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['sys_unknownError'];
    }

    if (is_callable($knownErrorsByCode[$errorCode])) {
        return $knownErrorsByCode[$errorCode]($errorParams);
    }

    return $knownErrorsByCode[$errorCode];
}

?>
