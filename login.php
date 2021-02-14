<?php

define('INSIDE', true);
define('UEC_INLOGIN', true);

$_DontShowMenus = true;

$_EnginePath = './';
include($_EnginePath.'common.php');
include_once($_EnginePath . 'modules/session/_includes.php');

use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Modules\Session;

includeLang('login');

$loginAttemptResult = null;

if ($_POST) {
    // TODO: Remove this useless block
} else if (Session\Utils\Cookie\hasSessionCookie()) {
    $loginAttemptResult = Session\Input\CookieLogin\handleCookieLogin([]);

    if ($loginAttemptResult['isSuccess']) {
        Session\Utils\Redirects\redirectToOverview();

        die();
    }
}

if ($_POST) {
    include_once($_EnginePath . '/includes/functions/IPandUA_Logger.php');

    $ipHash = md5(Users\Session\getCurrentIP());

    $loginAttemptResult = Session\Input\LocalIdentityLogin\handleLocalIdentityLogin([
        'input' => &$_POST,
        'ipHash' => $ipHash,
        'currentTimestamp' => time(),
    ]);

    if ($loginAttemptResult['isSuccess']) {
        $userEntity = $loginAttemptResult['payload']['userEntity'];

        IPandUA_Logger($userEntity, false);

        Session\Utils\Redirects\redirectToOverview();

        die();
    }

    Session\Utils\RateLimiter\updateLoginRateLimiterEntry([
        'ipHash' => $ipHash,
    ]);

    if (isset($loginAttemptResult['error']['userEntity'])) {
        $userEntity = $loginAttemptResult['error']['userEntity'];

        IPandUA_Logger($userEntity, true);
    }
}

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
        case 'UNIVERSUM_NOT_OPEN_YET':
            $serverOpeningTimestamp = $loginAttemptResult['error']['openingTimestamp'];

            $errorMessage = sprintf(
                $_Lang['Login_UniversumNotStarted'],
                prettyDate('d m Y', $serverOpeningTimestamp, 1),
                date('H:i:s', $serverOpeningTimestamp)
            );
            break;
        case 'INVALID_USERNAME':
            $errorMessage = $_Lang['Login_BadSignsUser'];
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

if (!LOGINPAGE_ALLOW_LOGINPHP) {
    Session\Utils\Redirects\permaRedirectToMainDomain();

    die();
}

if ($_GET && !$_POST) {
    $langChangeAttemptResult = Session\Input\Language\handleLanguageChange([
        'input' => &$_GET,
        'currentTimestamp' => time(),
    ]);

    if (
        $langChangeAttemptResult['isSuccess'] &&
        $langChangeAttemptResult['payload']['hasLangChanged']
    ) {
        includeLang('login');
    }
}

$pageView = Session\Screens\LoginView\render([]);

display($pageView['componentHTML'], $_Lang['Page_Title']);

?>
