<?php

namespace UniEngine\Engine\Modules\Settings\Screens\UsernameChange\Utils;

use UniEngine\Engine\Modules\Session;
use UniEngine\Engine\Modules\Settings;

/**
 * @param array $params
 * @param arrayRef $params['input']
 * @param arrayRef $params['user']
 */
function handleScreenInput($params) {
    $input = &$params['input'];
    $user = &$params['user'];

    if (empty($input['newnick'])) {
        return;
    }

    $normalizedNewUsername = trim($input['newnick']);

    $usernameChangeValidationResult = Settings\Utils\Validators\validateUsernameChange([
        'input' => [
            'newUsername' => $normalizedNewUsername,
        ],
        'currentUser' => &$user,
    ]);

    if (!$usernameChangeValidationResult['isSuccess']) {
        return [
            'isSuccess' => false,
            'error' => $usernameChangeValidationResult['error'],
        ];
    }

    Settings\Utils\Queries\updateUserOnUsernameChange([
        'newUsername' => $normalizedNewUsername,
        'currentUser' => &$user,
    ]);
    Settings\Utils\Queries\createUsernameChangeEntry([
        'newUsername' => $normalizedNewUsername,
        'currentUser' => &$user,
    ]);

    Session\Utils\Cookie\clearSessionCookie();

    return [
        'isSuccess' => true,
        'payload' => [],
    ];
}

?>
