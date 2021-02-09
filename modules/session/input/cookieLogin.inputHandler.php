<?php

namespace UniEngine\Engine\Modules\Session\Input\CookieLogin;

use UniEngine\Engine\Modules\Session;

//  Arguments:
//      - $params
//
function handleCookieLogin($params) {
    global $_EnginePath;

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

    if (!(Session\Utils\Cookie\hasSessionCookie())) {
        return $createFailure([
            'code' => 'NO_COOKIE',
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
        ]);
    }

    include_once($_EnginePath . '/includes/functions/IPandUA_Logger.php');

    $UserData = $verificationResult['payload']['userEntity'];

    IPandUA_Logger($UserData);

    return $createSuccess([]);
}

?>
