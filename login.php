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

$sessionCookieKey = getSessionCookieKey();

if($_POST)
{
    if ($_POST['uniSelect'] != LOGINPAGE_UNIVERSUMCODE) {
        message($_Lang['Login_BadUniversum'], $_Lang['Err_Title']);
    }

    if (time() < SERVER_MAINOPEN_TSTAMP) {
        $serverStartMessage = sprintf(
            $_Lang['Login_UniversumNotStarted'],
            prettyDate('d m Y', SERVER_MAINOPEN_TSTAMP, 1),
            date('H:i:s', SERVER_MAINOPEN_TSTAMP)
        );

        message($serverStartMessage, $_Lang['Page_Title']);
    }

    $Username = trim($_POST['username']);
    if (preg_match(REGEXP_USERNAME_ABSOLUTE, $Username)) {
        $Search['mode'] = 1;
        $Search['where'] = "`username` = '{$Username}'";
        $Search['password'] = md5($_POST['password']);
        $Search['IPHash'] = md5(Users\Session\getCurrentIP());

        $rateLimitVerificationResult = Session\Utils\RateLimiter\verifyLoginRateLimit([
            'ipHash' => $Search['IPHash'],
        ]);

        if ($rateLimitVerificationResult['isIpRateLimited']) {
            $Search['error'] = 5;
            $Search['where'] = '';
        }
    } else {
        $Search['error'] = 1;
    }
} else if (!empty($_COOKIE[$sessionCookieKey])) {
    $Search['mode'] = 2;

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
        switch ($verificationResult['error']['code']) {
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

        setcookie($sessionCookieKey, false, 0, '/', '');
    } else {
        include_once($_EnginePath . '/includes/functions/IPandUA_Logger.php');

        $UserData = $verificationResult['payload']['userEntity'];

        IPandUA_Logger($UserData);

        header("Location: ./overview.php");
        die();
    }
}

if(!empty($Search['where']))
{
    $Query_User_Fields = "`id`, `username`, `password`, `isAI`";
    $Query_User_GetData = "SELECT {$Query_User_Fields} FROM {{table}} WHERE {$Search['where']} LIMIT 1;";
    $UserData = doquery($Query_User_GetData, 'users', true);
    if($UserData['id'] > 0)
    {
        include_once($_EnginePath.'/includes/functions/IPandUA_Logger.php');

        $PasswordOK = false;
        if($Search['mode'] == 1 AND $UserData['password'] == $Search['password'])
        {
            $PasswordOK = true;
        }
        if($PasswordOK === true)
        {
            // User is ready to Login
            if($Search['mode'] == 1)
            {
                if($_POST['rememberme'] == 'on')
                {
                    $Cookie_Expire = time() + TIME_YEAR;
                    $Cookie_Remember = 1;
                }
                else
                {
                    $Cookie_Expire = 0;
                    $Cookie_Remember = 0;
                }

                $Cookie_Set = Session\Utils\Cookie\packSessionCookie([
                    'userId' => $UserData['id'],
                    '__unknown_1' => $UserData['username'],
                    'obscuredPasswordHash' => Session\Utils\Cookie\createCookiePasswordHash([
                        'passwordHash' => $UserData['password'],
                    ]),
                    'isRememberMeActive' => $Cookie_Remember,
                ]);

                setcookie($sessionCookieKey, $Cookie_Set, $Cookie_Expire, '/', '', false, true);
            }

            IPandUA_Logger($UserData);
            header("Location: ./overview.php");
            die();
        }
        else
        {
            $Search['error'] = 4;
        }
    }
    else
    {
        $Search['error'] = 3;
    }
}
if(!empty($Search['error']))
{
    if (
        $Search['mode'] == 1 &&
        !empty($Search['IPHash'])
    ) {
        Session\Utils\RateLimiter\updateLoginRateLimiterEntry([
            'ipHash' => $Search['IPHash'],
        ]);
    }

    if($UserData['id'] > 0)
    {
        IPandUA_Logger($UserData, true);
    }
    if($Search['mode'] == 2)
    {
        setcookie($sessionCookieKey, false, 0, '/', '');
    }
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
