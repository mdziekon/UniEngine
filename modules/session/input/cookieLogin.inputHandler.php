<?php

namespace UniEngine\Engine\Modules\Session\Input\CookieLogin;

use UniEngine\Engine\Modules\Session;

//  Arguments:
//      - $params
//
function handleCookieLogin($params) {
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

    $ipHash = $params['ipHash'];

    if (!(Session\Utils\Cookie\hasSessionCookie())) {
        return $createFailure([
            'code' => 'NO_COOKIE',
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

    $verificationResult = Session\Utils\Cookie\verifySessionCookie([
        'userEntityFetcher' => function ($fetcherParams) {
            $userId = $fetcherParams['userId'];

            $Query_GetUser  = '';
            $Query_GetUser .= "SELECT `id`, `username`, `password`, `isAI` ";
            $Query_GetUser .= "FROM {{table}} ";
            $Query_GetUser .= "WHERE `id` = {$userId} LIMIT 1;";

            return doquery($Query_GetUser, 'users');
        },
    ]);

    if (!$verificationResult['isSuccess']) {
        // TODO: Side effect, move elsewhere (?)
        Session\Utils\Cookie\clearSessionCookie();

        return $createFailure([
            'code' => $verificationResult['error']['code'],
            'userEntity' => (
                isset($verificationResult['error']['userEntity']) ?
                    $verificationResult['error']['userEntity'] :
                    null
            ),
        ]);
    }

    return $createSuccess([
        'userEntity' => $verificationResult['payload']['userEntity'],
    ]);
}

?>
