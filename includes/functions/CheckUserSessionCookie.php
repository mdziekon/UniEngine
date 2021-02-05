<?php

use UniEngine\Engine\Includes\Helpers\Users;

// TODO: Replace with better method of inclusion
include_once($_EnginePath . 'modules/session/_includes.php');

use UniEngine\Engine\Modules\Session;

// TODO: Do not store this like this, it's ugly...
function setPreviousLastIPValue(&$user) {
    global $_InMem_CheckUserSessionCookie_PreviousLastIP;

    $_InMem_CheckUserSessionCookie_PreviousLastIP = $user['user_lastip'];
}

function getPreviousLastIPValue() {
    global $_InMem_CheckUserSessionCookie_PreviousLastIP;

    return $_InMem_CheckUserSessionCookie_PreviousLastIP;
}

function CheckUserSessionCookie() {
    global $_Lang, $_DontShowMenus;

    if (!Session\Utils\Cookie\hasSessionCookie()) {
        return false;
    }

    $Init['$_DontShowMenus'] = $_DontShowMenus;
    $_DontShowMenus = true;

    $sessionCookieKey = getSessionCookieKey();
    $verificationResult = Session\Utils\Cookie\verifySessionCookie([
        'userEntityFetcher' => function ($fetcherParams) {
            $userId = $fetcherParams['userId'];

            $Query_GetUser  = '';
            $Query_GetUser .= "SELECT `user`.*, `stats`.`total_rank`, `stats`.`total_points`, `ally`.`ally_name`, `ally`.`ally_owner`, `ally`.`ally_ranks`, `ally`.`ally_ChatRoom_ID` ";
            $Query_GetUser .= "FROM {{table}} AS `user` ";
            $Query_GetUser .= "LEFT JOIN `{{prefix}}statpoints` AS `stats` ON `user`.`id` = `stats`.`id_owner` AND `stats`.`stat_type` = '1' ";
            $Query_GetUser .= "LEFT JOIN `{{prefix}}alliance` AS `ally` ON `ally`.`id` = `user`.`ally_id` ";
            $Query_GetUser .= "WHERE `user`.`id` = {$userId} LIMIT 1;";

            return doquery($Query_GetUser, 'users');
        },
    ]);

    if (!$verificationResult['isSuccess']) {
        includeLang('cookies');

        $errorMessage = 'UNKNOWN_ERROR';

        switch ($verificationResult['error']['code']) {
            case 'INVALID_USER_ID':
                $errorMessage = $_Lang['cookies']['Error1'];
                break;
            case 'USER_NOT_FOUND':
                $errorMessage = $_Lang['cookies']['Error2'];
                break;
            case 'INVALID_PASSWORD':
                $errorMessage = $_Lang['cookies']['Error3'];
                break;
        }

        message($errorMessage, $_Lang['cookies']['Title']);
    }

    $rawCookieValue = $verificationResult['payload']['rawCookieValue'];
    $sessionData = $verificationResult['payload']['sessionData'];
    $userRow = $verificationResult['payload']['userEntity'];

    setPreviousLastIPValue($userRow);

    if ($sessionData['isRememberMeActive']) {
        $ExpireTime = time() + 31536000;
    } else {
        $ExpireTime = 0;
    }

    if (
        !isset($_COOKIE['var_1124']) ||
        !preg_match('/^[0-9]{1,4}\_[0-9]{1,4}\_[0-9]{1,3}$/D', $_COOKIE['var_1124'])
    ) {
        $_COOKIE['var_1124'] = '';
    } else {
        $userRow['new_screen_settings'] = $_COOKIE['var_1124'];
    }

    $Query_UpdateUser = '';
    $Query_UpdateUser .= "UPDATE {{table}} SET ";
    $Query_UpdateUser .= "`onlinetime` = UNIX_TIMESTAMP(), ";
    $Query_UpdateUser .= "`current_page` = '" . (getDBLink()->escape_string($_SERVER['REQUEST_URI'])) . "', ";
    $Query_UpdateUser .= "`user_lastip` = '" . (Users\Session\getCurrentIP()) . "', ";
    $Query_UpdateUser .= "`user_agent` = '" . (getDBLink()->escape_string($_SERVER['HTTP_USER_AGENT'])) . "', ";
    $Query_UpdateUser .= "`screen_settings` = '".preg_replace('#[^0-9\_]{1,}#si', '', $_COOKIE['var_1124'])."' ";
    $Query_UpdateUser .= "WHERE `id` = {$userRow['id']} LIMIT 1;";
    doquery($Query_UpdateUser, 'users');

    Tasks_CheckUservar($userRow);

    setcookie($sessionCookieKey, FALSE, 0, '/', '.' . GAMEURL_DOMAIN);
    setcookie($sessionCookieKey, $rawCookieValue, $ExpireTime, '/', '', false, true);

    $_DontShowMenus = $Init['$_DontShowMenus'];

    return $userRow;
}

?>
