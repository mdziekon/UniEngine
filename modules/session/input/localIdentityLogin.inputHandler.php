<?php

namespace UniEngine\Engine\Modules\Session\Input\LocalIdentityLogin;

use UniEngine\Engine\Modules\Session;

//  Arguments:
//      - $params
//
function handleLocalIdentityLogin($params) {
    $createSuccess = function ($payload) {
        return [
            'isSuccess' => true,
            'payload' => $payload,
        ];
    };
    $createFailure = function ($error) {
        return [
            'isSuccess' => false,
            'error' => $error,
        ];
    };

    $input = &$params['input'];
    $ipHash = $params['ipHash'];
    $currentTimestamp = $params['currentTimestamp'];

    if ($input['uniSelect'] != LOGINPAGE_UNIVERSUMCODE) {
        return $createFailure([
            'code' => 'INVALID_UNIVERSUM_CODE',
        ]);
    }

    $serverOpeningTimestamp = SERVER_MAINOPEN_TSTAMP;

    if ($currentTimestamp < $serverOpeningTimestamp) {
        return $createFailure([
            'code' => 'UNIVERSUM_NOT_OPEN_YET',
            'openingTimestamp' => $serverOpeningTimestamp,
        ]);
    }

    $inputUsername = trim($input['username']);

    if (!preg_match(REGEXP_USERNAME_ABSOLUTE, $inputUsername)) {
        return $createFailure([
            'code' => 'INVALID_USERNAME_CHARACTERS',
        ]);
    }

    $rateLimitVerificationResult = Session\Utils\RateLimiter\verifyLoginRateLimit([
        'ipHash' => $ipHash,
    ]);

    if ($rateLimitVerificationResult['isIpRateLimited']) {
        return $createFailure([
            'code' => 'LOGIN_ATTEMPTS_RATE_LIMITED',
        ]);
    }

    $Query_User_Fields = "`id`, `username`, `password`, `isAI`";
    $Query_User_GetData = "SELECT {$Query_User_Fields} FROM {{table}} WHERE `username` = '{$inputUsername}' LIMIT 1;";
    $userEntity = doquery($Query_User_GetData, 'users', true);

    if (
        !$userEntity ||
        $userEntity['id'] <= 0
    ) {
        return $createFailure([
            'code' => 'USER_NOT_FOUND',
        ]);
    }

    $inputPassword = $input['password'];
    $inputPasswordHash = md5($inputPassword);
    $dbPasswordHash = $userEntity['password'];

    if ($inputPasswordHash !== $dbPasswordHash) {
        return $createFailure([
            'code' => 'INVALID_PASSWORD',
            'userEntity' => $userEntity,
        ]);
    }

    $isRememberMeEnabled = ($input['rememberme'] == 'on');

    Session\Utils\Cookie\setNewSessionCookie([
        'userId' => $userEntity['id'],
        'username' => $userEntity['username'],
        'passwordHash' => $dbPasswordHash,
        'isRememberMeActive' => $isRememberMeEnabled,
        'currentTimestamp' => $currentTimestamp,
    ]);

    return $createSuccess([
        'userEntity' => $userEntity,
    ]);
}

?>
