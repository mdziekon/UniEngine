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

function clearSessionCookie() {
    $cookieName = getSessionCookieKey();

    setcookie($cookieName, false, 0, '/', '');
}

function setNewSessionCookie($params) {
    $cookieName = getSessionCookieKey();

    $userId = $params['userId'];
    $username = $params['username'];
    $passwordHash = $params['passwordHash'];
    $isRememberMeEnabled = $params['isRememberMeActive'];
    $currentTimestamp = $params['currentTimestamp'];

    $sessionTokenValue = packSessionCookie([
        'userId' => $userId,
        'username' => $username,
        'obscuredPasswordHash' => createCookiePasswordHash([
            'passwordHash' => $passwordHash,
        ]),
        'isRememberMeActive' => ($isRememberMeEnabled ? 1 : 0),
    ]);
    $expirationTimestamp = (
        $isRememberMeEnabled ?
            ($currentTimestamp + TIME_YEAR) :
            0
    );

    setcookie(
        $cookieName,
        $sessionTokenValue,
        $expirationTimestamp,
        '/',
        '',
        false,
        true
    );
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

function getCookiePartsSeparator() {
    return '/%/';
}

function createCookiePasswordHash($params) {
    $passwordHash = $params['passwordHash'];

    $serverSecretWord = getServerSecretWord();

    return md5("{$passwordHash}--{$serverSecretWord}");
}

function packSessionCookie($cookieParams) {
    $cookieParts = [
        strval($cookieParams['userId']),
        $cookieParams['username'],
        $cookieParams['obscuredPasswordHash'],
        ($cookieParams['isRememberMeActive'] ? '1' : '0'),
    ];
    $partsSeparator = getCookiePartsSeparator();

    return implode($partsSeparator, $cookieParts);
}

function unpackSessionCookie($cookieValue) {
    $partsSeparator = getCookiePartsSeparator();
    $cookieParts = explode($partsSeparator, $cookieValue);

    return [
        'userId' => $cookieParts[0],
        'username' => $cookieParts[1],
        'obscuredPasswordHash' => $cookieParts[2],
        'isRememberMeActive' => $cookieParts[3],
    ];
}

function normalizeSessionCookie($unpackedCookie) {
    return [
        'userId' => intval($unpackedCookie['userId']),
        'username' => $unpackedCookie['username'],
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
    $userPasswordHash = $userEntity['password'];

    $obscuredUserPasswordHash = createCookiePasswordHash([ 'passwordHash' => $userPasswordHash ]);

    if ($obscuredUserPasswordHash !== $sessionData['obscuredPasswordHash']) {
        return $createFailure([
            'code' => 'INVALID_PASSWORD',
            'userEntity' => $userEntity,
        ]);
    }

    return $createSuccess([
        'rawCookieValue' => $cookieValue,
        'sessionData' => $sessionData,
        'userEntity' => $userEntity,
    ]);
}

?>
