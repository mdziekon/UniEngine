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
    global $__ServerConnectionSettings;

    require($_EnginePath . 'config.php');

    $secretWord = (
        isset($__ServerConnectionSettings['secretword']) ?
            $__ServerConnectionSettings['secretword'] :
            ''
    );

    unset($__ServerConnectionSettings);

    return $secretWord;
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

?>
