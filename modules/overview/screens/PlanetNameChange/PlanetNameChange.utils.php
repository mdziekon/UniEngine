<?php

namespace UniEngine\Engine\Modules\Overview\Screens\PlanetNameChange;

use UniEngine\Engine\Modules\Overview\Screens\PlanetNameChange;

/**
 * @param array $params
 * @param arrayRef $params['input']
 * @param arrayRef $params['user']
 * @param arrayRef $params['planet']
 */
function runEffects($props) {
    global $_Lang;

    $input = &$props['input'];
    $user = &$props['user'];
    $planet = &$props['planet'];

    if (
        !isset($input['action']) ||
        $input['action'] != 'do'
    ) {
        return [
            'isSuccess' => null,
        ];
    }

    $newName = (
        isset($input['set_newname']) ?
            trim($input['set_newname']) :
            ''
    );

    $nameChangeValidationResult = PlanetNameChange\Utils\Validators\validateNewName([
        'input' => [
            'newName' => $newName,
        ],
        'planet' => &$planet,
    ]);

    if (!$nameChangeValidationResult['isSuccess']) {
        $errorMessage = PlanetNameChange\Utils\ErrorMappers\mapValidateNewNameErrorToReadableMessage(
            $nameChangeValidationResult['error']
        );

        return [
            'isSuccess' => false,
            'payload' => [
                'message' => $errorMessage,
                'color' => 'red',
            ],
        ];
    }

    $planet['name'] = $newName;

    doquery("UPDATE {{table}} SET `name` = '{$newName}' WHERE `id` = {$user['current_planet']} LIMIT 1;", 'planets');

    return [
        'isSuccess' => true,
        'payload' => [
            'message' => $_Lang['RenamePlanet_NameSaved'],
            'color' => 'lime',
        ],
    ];
}

?>
