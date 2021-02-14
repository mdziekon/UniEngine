<?php

namespace UniEngine\Engine\Modules\Session\Screens\LoginView;

use UniEngine\Engine\Modules\Session;
use UniEngine\Engine\Includes\Helpers\Users;

//  Arguments
//      - $props (Object)
//          - currentTimestamp (number)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_EnginePath, $_Lang;

    includeLang('login');

    $currentTimestamp = $props['currentTimestamp'];

    // Handle user input
    $loginAttemptResult = null;

    if ($_GET && !$_POST) {
        $langChangeAttemptResult = Session\Input\Language\handleLanguageChange([
            'input' => &$_GET,
            'currentTimestamp' => $currentTimestamp,
        ]);

        if (
            $langChangeAttemptResult['isSuccess'] &&
            $langChangeAttemptResult['payload']['hasLangChanged']
        ) {
            includeLang('login');
        }
    } else if ($_POST) {
        $ipHash = Users\Session\getCurrentIPHash();

        $loginAttemptResult = Session\Input\LocalIdentityLogin\handleLocalIdentityLogin([
            'input' => &$_POST,
            'ipHash' => $ipHash,
            'currentTimestamp' => $currentTimestamp,
        ]);

        if (!$loginAttemptResult['isSuccess']) {
            Session\Utils\RateLimiter\updateLoginRateLimiterEntry([
                'ipHash' => $ipHash,
            ]);
        }
    } else if (Session\Utils\Cookie\hasSessionCookie()) {
        $loginAttemptResult = Session\Input\CookieLogin\handleCookieLogin([]);
    }

    // Internal errors handling
    if (
        $loginAttemptResult &&
        !$loginAttemptResult['isSuccess'] &&
        isset($loginAttemptResult['error']['userEntity'])
    ) {
        include_once($_EnginePath . '/includes/functions/IPandUA_Logger.php');

        $userEntity = $loginAttemptResult['error']['userEntity'];

        IPandUA_Logger($userEntity, true);
    }

    // Successful login attempt handling
    if (
        $loginAttemptResult &&
        $loginAttemptResult['isSuccess']
    ) {
        include_once($_EnginePath . '/includes/functions/IPandUA_Logger.php');

        $userEntity = $loginAttemptResult['payload']['userEntity'];

        IPandUA_Logger($userEntity, false);

        Session\Utils\Redirects\redirectToOverview();

        die();
    }

    // Handle input errors
    if (
        $loginAttemptResult &&
        !$loginAttemptResult['isSuccess']
    ) {
        $errorCode = $loginAttemptResult['error']['code'];

        switch ($errorCode) {
            case 'NO_COOKIE':
            case 'INVALID_USER_ID':
            case 'USER_NOT_FOUND':
            case 'INVALID_PASSWORD':
                $errorMessage = $_Lang['Login_InvalidCredentials'];
                break;
            case 'INVALID_UNIVERSUM_CODE':
                $errorMessage = $_Lang['Login_BadUniversum'];
                break;
            case 'INVALID_USERNAME_CHARACTERS':
                $errorMessage = $_Lang['Login_BadSignsUser'];
                break;
            case 'UNIVERSUM_NOT_OPEN_YET':
                $serverOpeningTimestamp = $loginAttemptResult['error']['openingTimestamp'];

                $errorMessage = sprintf(
                    $_Lang['Login_UniversumNotStarted'],
                    prettyDate('d m Y', $serverOpeningTimestamp, 1),
                    date('H:i:s', $serverOpeningTimestamp)
                );
                break;
            case 'LOGIN_ATTEMPTS_RATE_LIMITED':
                $errorMessage = $_Lang['Login_FailLoginProtection'];
                break;
            default:
                $errorMessage = $_Lang['Login_UnknownError'];
                break;
        }

        message($errorMessage, $_Lang['Err_Title']);
    }

    $viewProps = [];
    $viewComponent = Components\LoginForm\render($viewProps);

    return [
        'componentHTML' => $viewComponent['componentHTML'],
    ];
}

?>
