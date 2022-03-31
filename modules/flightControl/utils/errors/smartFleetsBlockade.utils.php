<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Errors;

/**
 * @param object $error As returned by FlightControl\Utils\Validators\validateSmartFleetsBlockadeState
 */
function mapSmartFleetsBlockadeValidationErrorToReadableMessage($error, $mapperParams) {
    global $_Lang;

    $errorCode = $error['blockType'];
    $errorParams = $error['details'];

    $knownErrorsByCode = [
        'GLOBAL_ENDTIME' => $_Lang['SFB_Stop_GlobalBlockade'],
        'GLOBAL_POSTENDTIME' => function ($params) use (&$_Lang) {
            $errorMessage = sprintf(
                $_Lang['SFB_Stop_GlobalPostBlockade'],
                prettyDate('d m Y, H:i:s', $params['hardEndTime'], 1)
            );

            return $errorMessage;
        },
        'USER' => function ($params) use (&$_Lang, &$mapperParams) {
            $reasonMessage = (
                empty($params['reason']) ?
                    $_Lang['SFB_Stop_ReasonNotGiven'] :
                    "\"{$params['reason']}\""
            );

            $errorMessage = sprintf(
                (
                    $params['userId'] == $mapperParams['user']['id'] ?
                        $_Lang['SFB_Stop_UserBlockadeOwn'] :
                        $_Lang['SFB_Stop_UserBlockade']
                ),
                prettyDate('d m Y', $params['endTime'], 1),
                date('H:i:s', $params['endTime']),
                $reasonMessage
            );

            return $errorMessage;
        },
        'PLANET' => function ($params) use (&$_Lang, &$mapperParams) {
            $reasonMessage = (
                empty($params['reason']) ?
                    $_Lang['SFB_Stop_ReasonNotGiven'] :
                    "\"{$params['reason']}\""
            );
            $errorMessageTemplate = (
                $params['planetId'] == $mapperParams['originPlanet']['id'] ?
                (
                    $mapperParams['originPlanet']['planet_type'] == 1 ?
                    $_Lang['SFB_Stop_PlanetBlockadeOwn_Planet'] :
                    $_Lang['SFB_Stop_PlanetBlockadeOwn_Moon']
                ) :
                (
                    $mapperParams['targetPlanet']['type'] == 1 ?
                    $_Lang['SFB_Stop_PlanetBlockade_Planet'] :
                    $_Lang['SFB_Stop_PlanetBlockade_Moon']
                )
            );

            $errorMessage = sprintf(
                $errorMessageTemplate,
                prettyDate('d m Y', $params['endTime'], 1),
                date('H:i:s', $params['endTime']),
                $reasonMessage
            );

            return $errorMessage;
        },
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['fleet_generic_errors_unknown'];
    }

    if (is_callable($knownErrorsByCode[$errorCode])) {
        return $knownErrorsByCode[$errorCode]($errorParams);
    }

    return $knownErrorsByCode[$errorCode];
}

?>
