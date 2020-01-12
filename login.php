<?php

define('INSIDE', true);
define('UEC_INLOGIN', true);

$_DontShowMenus = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

use UniEngine\Engine\Includes\Helpers\Users;

includeLang('login');

$sessionCookieKey = getSessionCookieKey();

if(!empty($_GET['post']))
{
    $_POST = unserialize(base64_decode($_GET['post']));
}
if($_POST)
{
    if($_POST['uniSelect'] != LOGINPAGE_UNIVERSUMCODE)
    {
        if(preg_match('/^[a-zA-Z0-9]{3,}$/D', $_POST['uniSelect']))
        {
            $PostRedirect = base64_encode(serialize(array
            (
                'uniSelect' => $_POST['uniSelect'],
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'rememberme' => $_POST['rememberme']
            )));
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: http://'.$_POST['uniSelect'].'.'.GAMEURL_DOMAIN.'/login.php?post='.$PostRedirect);
            die();
        }
        else
        {
            message($_Lang['Login_BadUniversum'], $_Lang['Err_Title']);
        }
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
    if(preg_match(REGEXP_USERNAME_ABSOLUTE, $Username))
    {
        $Search['mode'] = 1;
        $Search['where'] = "`username` = '{$Username}'";
        $Search['password'] = md5($_POST['password']);
        $Search['IPHash'] = md5(Users\Session\getCurrentIP());

        $Query_LoginProtection = "SELECT `FailCount` FROM {{table}} WHERE `IP` = '{$Search['IPHash']}' AND `Date` >= (UNIX_TIMESTAMP() - ".LOGINPROTECTION_LOCKTIME.") LIMIT 1;";
        $Result_LoginProtection = doquery($Query_LoginProtection, 'login_protection', true);
        if($Result_LoginProtection['FailCount'] >= LOGINPROTECTION_MAXATTEMPTS)
        {
            $Search['error'] = 5;
            $Search['where'] = '';
        }
    }
    else
    {
        $Search['error'] = 1;
    }
}
elseif(!empty($_COOKIE[$sessionCookieKey]))
{
    $explodeCookie = explode('/%/', $_COOKIE[$sessionCookieKey]);
    $UserID = intval($explodeCookie[0]);
    if($UserID > 0)
    {
        $Search['mode'] = 2;
        $Search['where'] = "`id` = {$UserID}";
        $Search['password'] = $explodeCookie[2];
    }
    else
    {
        $Search['error'] = 2;
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
        require($_EnginePath.'config.php');

        $PasswordOK = false;
        if($Search['mode'] == 1 AND $UserData['password'] == $Search['password'])
        {
            $PasswordOK = true;
        }
        elseif($Search['mode'] == 2 AND md5($UserData['password'].'--'.$__ServerConnectionSettings['secretword']) == $Search['password'])
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
                $Cookie_Set = $UserData['id'].'/%/'.$UserData['username'].'/%/'.md5($UserData['password'].'--'.$__ServerConnectionSettings['secretword']).'/%/'.$Cookie_Remember;
                setcookie($sessionCookieKey, $Cookie_Set, $Cookie_Expire, '/', '', false, true);
            }
            IPandUA_Logger($UserData);
            unset($__ServerConnectionSettings);
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
    if($Search['mode'] == 1 AND !empty($Search['IPHash']))
    {
        $Query_UpdateLoginProtection = '';
        $Query_UpdateLoginProtection .= "INSERT INTO {{table}} (`IP`, `Date`, `FailCount`) VALUES ('{$Search['IPHash']}', UNIX_TIMESTAMP(), 1) ";
        $Query_UpdateLoginProtection .= "ON DUPLICATE KEY UPDATE ";
        $Query_UpdateLoginProtection .= "`FailCount` = IF(`Date` < (UNIX_TIMESTAMP() - ".LOGINPROTECTION_LOCKTIME."), 1, IF(`FailCount` < ".LOGINPROTECTION_MAXATTEMPTS.", `FailCount` + 1, `FailCount`)), ";
        $Query_UpdateLoginProtection .= "`Date` = IF(`FailCount` < ".LOGINPROTECTION_MAXATTEMPTS." OR `Date` < (UNIX_TIMESTAMP() - ".LOGINPROTECTION_LOCKTIME."), UNIX_TIMESTAMP(), `Date`);";
        doquery($Query_UpdateLoginProtection, 'login_protection');
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

$_Lang['PHP_InsertUniCode'] = LOGINPAGE_UNIVERSUMCODE;
$_Lang['PHP_Insert_LangSelectors'] = [];

foreach (UNIENGINE_LANGS_AVAILABLE as $langKey) {
    $langData = $_Lang['LanguagesAvailable'][$langKey];

    $_Lang['PHP_Insert_LangSelectors'][] = (
        "<a href='?lang={$langKey}' title='{$langData['name']}'>" .
        "{$langData['flag_emoji']}" .
        "</a>"
    );
}
$_Lang['PHP_Insert_LangSelectors'] = implode('&nbsp;&nbsp;', $_Lang['PHP_Insert_LangSelectors']);

if($_GameConfig['game_disable'])
{
    $_Lang['type'] = 'button" onclick="alert(\''.str_replace('<br/>', "\n", $_GameConfig['close_reason']).'\')';
    $_Lang['LoginButton'] = $_Lang['Body_ServerOffline'];
}
else
{
    $_Lang['type'] = 'submit';
    $_Lang['LoginButton'] = $_Lang['Body_Submit'];
}
display(parsetemplate(gettemplate('login_body'), $_Lang), $_Lang['Page_Title']);

?>
