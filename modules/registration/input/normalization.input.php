<?php

namespace UniEngine\Engine\Modules\Registration\Input;

/**
 * @param $_GET|$_POST $input
 * @param String $input['username']
 * @param String $input['password']
 * @param String $input['email']
 * @param String $input['rules']
 * @param String $input['galaxy']
 * @param String $input['lang']
 */
function normalizeUserInput(&$input) {
    $normalizedUsername = (
        isset($input['username']) ?
        trim($input['username']) :
        null
    );
    $normalizedPassword = (
        isset($input['password']) ?
        trim($input['password']) :
        null
    );
    $normalizedEmail = (
        isset($input['email']) ?
        trim($input['email']) :
        null
    );
    $escapedEmail = getDBLink()->escape_string($normalizedEmail);
    $normalizedHasAcceptedRules = (
        isset($input['rules']) ?
        ($input['rules'] == 'on') :
        false
    );
    $normalizedGalaxyNo = (
        isset($input['galaxy']) ?
        intval($input['galaxy']) :
        null
    );
    $normalizedLangCode = (
        (
            isset($input['lang']) &&
            in_array($input['lang'], UNIENGINE_LANGS_AVAILABLE)
        ) ?
        $input['lang'] :
        null
    );
    $normalizedCaptchaResponse = (
        isset($input['captcha_response']) ?
        $input['captcha_response'] :
        null
    );

    return [
        'username' => $normalizedUsername,
        'password' => $normalizedPassword,
        'email' => [
            'original' => $normalizedEmail,
            'escaped' => $escapedEmail
        ],
        'hasAcceptedRules' => $normalizedHasAcceptedRules,
        'galaxyNo' => $normalizedGalaxyNo,
        'langCode' => $normalizedLangCode,
        'captchaResponse' => $normalizedCaptchaResponse,
    ];
}

?>
