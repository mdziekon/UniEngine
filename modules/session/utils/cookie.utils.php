<?php

namespace UniEngine\Engine\Modules\Session\Utils\Cookie;

function hasSessionCookie() {
    $cookieName = getSessionCookieKey();

    return isset($_COOKIE[$cookieName]);
}

function getSessionCookieValue() {
    $cookieName = getSessionCookieKey();

    return $_COOKIE[$cookieName];
}

function getServerSecretWord() {
    global $_EnginePath, $__ServerConnectionSettings;

    require($_EnginePath . 'config.php');

    $secretWord = (
        isset($__ServerConnectionSettings['secretword']) ?
            $__ServerConnectionSettings['secretword'] :
            ''
    );

    unset($__ServerConnectionSettings);

    return $secretWord;
}

function createCookiePasswordHash($params) {
    $password = $params['password'];

    $serverSecretWord = getServerSecretWord();

    return md5("{$password}--{$serverSecretWord}");
}

function unpackSessionCookie($cookieValue) {
    $cookieParts = explode('/%/', $cookieValue);

    return [
        'userId' => $cookieParts[0],
        '__unknown_1' => $cookieParts[1],
        'obscuredPasswordHash' => $cookieParts[2],
        'isRememberMeActive' => $cookieParts[3],
    ];
}

function normalizeSessionCookie($unpackedCookie) {
    return [
        'userId' => intval($unpackedCookie['userId']),
        '__unknown_1' => $unpackedCookie['__unknown_1'],
        'obscuredPasswordHash' => $unpackedCookie['obscuredPasswordHash'],
        'isRememberMeActive' => ($unpackedCookie['isRememberMeActive'] == 1),
    ];
}

function verifySessionCookie($params) {
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

    $userEntityFetcher = $params['userEntityFetcher'];

    $cookieValue = getSessionCookieValue();

    $unpackedCookie = unpackSessionCookie($cookieValue);
    $sessionData = normalizeSessionCookie($unpackedCookie);

    if ($sessionData['userId'] <= 0) {
        return $createFailure([
            'code' => 'INVALID_USER_ID',
        ]);
    }

    $userEntityFetcherResult = $userEntityFetcher([
        'userId' => $sessionData['userId'],
    ]);

    if ($userEntityFetcherResult->num_rows != 1) {
        return $createFailure([
            'code' => 'USER_NOT_FOUND',
        ]);
    }

    $userEntity = $userEntityFetcherResult->fetch_assoc();
    $userPassword = $userEntity['password'];

    $obscuredUserPasswordHash = createCookiePasswordHash([ 'password' => $userPassword ]);

    if ($obscuredUserPasswordHash !== $sessionData['obscuredPasswordHash']) {
        return $createFailure([
            'code' => 'INVALID_PASSWORD',
        ]);
    }

    return $createSuccess([
        'rawCookieValue' => $cookieValue,
        'sessionData' => $sessionData,
        'userEntity' => $userEntity,
    ]);
}

?>
