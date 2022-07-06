<?php

namespace UniEngine\Engine\Modules\Overview\Screens\AbandonPlanet\Utils\Effects;

use UniEngine\Engine\Modules\Overview\Screens\AbandonPlanet;

/**
 * @param array $params
 * @param array $params['input']
 * @param string $params['input']['confirmPassword']
 * @param arrayRef $params['user']
 * @param arrayRef $params['planet']
 */
function tryAbandonPlanet($params) {
    $user = &$params['user'];
    $planet = &$params['planet'];

    $executor = function ($input, $resultHelpers) use (&$user, &$planet) {
        global $_EnginePath;

        $abandonPlanetValidationResult = AbandonPlanet\Utils\Validators\validateAbandonPlanet([
            'input' => $input,
            'user' => &$user,
            'planet' => &$planet,
        ]);

        if (!$abandonPlanetValidationResult['isSuccess']) {
            return $resultHelpers['createFailure'](
                $abandonPlanetValidationResult['error']
            );
        }

        include("{$_EnginePath}includes/functions/DeleteSelectedPlanetorMoon.php");
        $DeleteResult = DeleteSelectedPlanetorMoon();

        if ($DeleteResult['result'] !== true) {
            switch ($DeleteResult['reason']) {
                case 'tech':
                    return $resultHelpers['createFailure']([
                        'code' => 'ABANDON_IMPOSSIBLE_TECH_IN_PROGRESS',
                    ]);
                case 'sql':
                    return $resultHelpers['createFailure']([
                        'code' => 'ABANDON_ERROR_SQL',
                    ]);
                case 'fleet_current':
                    return $resultHelpers['createFailure']([
                        'code' => 'ABANDON_IMPOSSIBLE_FLIGHTS_IN_PROGRESS',
                    ]);
                case 'fleet_moon':
                    return $resultHelpers['createFailure']([
                        'code' => 'ABANDON_IMPOSSIBLE_FLIGHTS_ON_MOON',
                    ]);
            }
        }

        return $resultHelpers['createSuccess']([
            'deleteResult' => $DeleteResult,
        ]);
    };

    return createFuncWithResultHelpers($executor)($params['input']);
}

?>
