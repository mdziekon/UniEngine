<?php

namespace UniEngine\Engine\Modules\Overview\Screens\AbandonPlanet\Utils\Validators;

use UniEngine\Engine\Includes\Helpers\World;
use UniEngine\Engine\Modules\Session;

/**
 * @param array $params
 * @param array $params['input']
 * @param string $params['input']['confirmPassword']
 * @param arrayRef $params['user']
 * @param arrayRef $params['planet']
 */
function validateAbandonPlanet($params) {
    $user = &$params['user'];
    $planet = &$params['planet'];

    $executor = function ($input, $resultHelpers) use (&$user, &$planet) {
        $confirmPassword = $input['confirmPassword'];
        $abandonPlanetId = $planet['id'];

        if (empty($confirmPassword)) {
            return $resultHelpers['createFailure']([
                'code' => 'CONFIRM_PASSWORD_EMPTY',
            ]);
        }

        $inputPasswordHash = Session\Utils\LocalIdentityV1\hashPassword([
            'password' => $confirmPassword,
        ]);

        if ($inputPasswordHash != $user['password']) {
            return $resultHelpers['createFailure']([
                'code' => 'CONFIRM_PASSWORD_INVALID',
            ]);
        }

        if ($abandonPlanetId == $user['id_planet']) {
            return $resultHelpers['createFailure']([
                'code' => 'CANT_ABANDON_MOTHER_PLANET',
            ]);
        }
        if (
            $planet['planet_type'] != World\Enums\PlanetType::Planet &&
            $planet['planet_type'] != World\Enums\PlanetType::Moon
        ) {
            return $resultHelpers['createFailure']([
                'code' => 'INVALID_PLANET_TYPE',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($executor)($params['input']);
}

?>
