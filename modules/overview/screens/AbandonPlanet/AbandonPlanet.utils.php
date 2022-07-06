<?php

namespace UniEngine\Engine\Modules\Overview\Screens\AbandonPlanet;

use UniEngine\Engine\Modules\Overview\Screens\AbandonPlanet;

/**
 * @param array $params
 * @param arrayRef $params['input']
 * @param arrayRef $params['user']
 * @param arrayRef $params['planet']
 * @param number $params['currentTimestamp']
 */
function runEffects($props) {
    global $_Lang;

    $input = &$props['input'];
    $user = &$props['user'];
    $planet = &$props['planet'];
    $currentTimestamp = $props['currentTimestamp'];

    if (
        !isset($input['action']) ||
        $input['action'] != 'do'
    ) {
        return [
            'isSuccess' => null,
        ];
    }

    $confirmPassword = (
        isset($input['give_passwd']) ?
            $input['give_passwd'] :
            ''
    );

    $tryAbandonPlanetResult = AbandonPlanet\Utils\Effects\tryAbandonPlanet([
        'input' => [
            'confirmPassword' => $confirmPassword,
        ],
        'user' => &$user,
        'planet' => &$planet,
    ]);

    if (!$tryAbandonPlanetResult['isSuccess']) {
        $errorMessage = AbandonPlanet\Utils\ErrorMappers\mapTryAbandonPlanetErrorToReadableMessage(
            $tryAbandonPlanetResult['error']
        );

        return [
            'isSuccess' => false,
            'payload' => [
                'message' => $errorMessage,
            ],
        ];
    }

    AbandonPlanet\Utils\Effects\triggerUserTasksUpdates([
        'user' => &$user,
        'planet' => &$planet,
    ]);
    AbandonPlanet\Utils\Effects\updateUserDevLog([
        'abandonedPlanetIds' => $tryAbandonPlanetResult['payload']['deleteResult']['ids'],
        'currentTimestamp' => $currentTimestamp,
    ]);

    return [
        'isSuccess' => true,
        'payload' => [
            'redirectUrl' => 'overview.php?showmsg=abandon',
        ],
    ];
}

?>
