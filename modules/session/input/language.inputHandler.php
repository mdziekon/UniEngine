<?php

namespace UniEngine\Engine\Modules\Session\Input\Language;

//  Arguments:
//      - $params
//
function handleLanguageChange($params) {
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
    $currentTimestamp = $params['currentTimestamp'];

    $nextLang = (
        isset($input['lang']) ?
            $input['lang'] :
            null
    );

    if ($nextLang === null) {
        return $createSuccess([
            'hasLangChanged' => false,
        ]);
    }
    if (!in_array($nextLang, UNIENGINE_LANGS_AVAILABLE)) {
        return $createFailure([
            'code' => 'INVALID_LANG_CODE'
        ]);
    }

    $langCookieExpirationTimestamp = ($currentTimestamp + (30 * TIME_DAY));

    setcookie(
        UNIENGINE_VARNAMES_COOKIE_LANG,
        $nextLang,
        $langCookieExpirationTimestamp,
        '',
        GAMEURL_DOMAIN
    );

    $_COOKIE[UNIENGINE_VARNAMES_COOKIE_LANG] = $nextLang;

    return $createSuccess([
        'hasLangChanged' => true,
        'nextLangCode' => $nextLang,
    ]);
}

?>
