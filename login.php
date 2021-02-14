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

if ($_POST) {
    // TODO: Remove this useless block
} else if (Session\Utils\Cookie\hasSessionCookie()) {
    $loginAttemptResult = Session\Input\CookieLogin\handleCookieLogin([]);

    if ($loginAttemptResult['isSuccess']) {
        Session\Utils\Redirects\redirectToOverview();

        die();
    }

    $Search['mode'] = 2;

    switch ($loginAttemptResult['error']['code']) {
        case 'NO_COOKIE':
            $Search['error'] = 2;
            break;
        case 'INVALID_USER_ID':
            $Search['error'] = 2;
            break;
        case 'USER_NOT_FOUND':
            $Search['error'] = 3;
            break;
        case 'INVALID_PASSWORD':
            $Search['error'] = 4;
            break;
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

    $Search['mode'] = 1;

    Session\Utils\RateLimiter\updateLoginRateLimiterEntry([
        'ipHash' => $ipHash,
    ]);

    if (isset($loginAttemptResult['error']['userEntity'])) {
        $userEntity = $loginAttemptResult['error']['userEntity'];

        IPandUA_Logger($userEntity, true);
    }

    switch ($loginAttemptResult['error']['code']) {
        case 'INVALID_UNIVERSUM_CODE':
            $Search['error'] = 6;
            break;
        case 'UNIVERSUM_NOT_OPEN_YET':
            $Search['error'] = 7;
            break;
        case 'INVALID_USERNAME':
            $Search['error'] = 1;
            break;
        case 'LOGIN_ATTEMPTS_RATE_LIMITED':
            $Search['error'] = 5;
            break;
        case 'USER_NOT_FOUND':
            $Search['error'] = 3;
            break;
        case 'INVALID_PASSWORD':
            $Search['error'] = 4;
            break;
    }
}

if(!empty($Search['error']))
{
    if($Search['error'] == 1)
    {
        message($_Lang['Login_BadSignsUser'], $_Lang['Err_Title']);
    }
    elseif($Search['error'] == 2)
    {
        message($_Lang['Login_FailCookieUser'], $_Lang['Err_Title']);
    }
    elseif($Search['error'] == 3 AND $Search['mode'] == 1)
    {
        message($_Lang['Login_FailUser'], $_Lang['Err_Title']);
    }
    elseif($Search['error'] == 3 AND $Search['mode'] == 2)
    {
        message($_Lang['Login_FailCookieUser'], $_Lang['Err_Title']);
    }
    elseif($Search['error'] == 4 AND $Search['mode'] == 1)
    {
        message($_Lang['Login_FailPassword'], $_Lang['Err_Title']);
    }
    elseif($Search['error'] == 4 AND $Search['mode'] == 2)
    {
        message($_Lang['Login_FailCookiePassword'], $_Lang['Err_Title']);
    }
    elseif($Search['error'] == 5)
    {
        message($_Lang['Login_FailLoginProtection'], $_Lang['Err_Title']);
    }
    elseif($Search['error'] == 6) {
        message($_Lang['Login_BadUniversum'], $_Lang['Err_Title']);
    }
    elseif($Search['error'] == 7)
    {
        $errorMessage = $serverStartMessage = sprintf(
            $_Lang['Login_UniversumNotStarted'],
            prettyDate('d m Y', SERVER_MAINOPEN_TSTAMP, 1),
            date('H:i:s', SERVER_MAINOPEN_TSTAMP)
        );

        message($errorMessage, $_Lang['Err_Title']);
    }
    else
    {
        message($_Lang['Login_UnknownError'], $_Lang['Err_Title']);
    }
}

if(!LOGINPAGE_ALLOW_LOGINPHP)
{
    header("HTTP/1.1 301 Moved Permanently");
    header('Location: '.GAMEURL_STRICT);
    die();
}

$input_changelang = $_GET['lang'];
if (isset($input_changelang) && in_array($input_changelang, UNIENGINE_LANGS_AVAILABLE)) {
    setcookie(
        UNIENGINE_VARNAMES_COOKIE_LANG,
        $input_changelang,
        time() + (30 * TIME_DAY),
        '',
        GAMEURL_DOMAIN
    );

    $_COOKIE[UNIENGINE_VARNAMES_COOKIE_LANG] = $input_changelang;
    includeLang('login');
}

$pageView = Session\Screens\LoginView\render([]);

display($pageView['componentHTML'], $_Lang['Page_Title']);

?>
