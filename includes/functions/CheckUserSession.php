<?php

function CheckUserSession()
{
    global $_EnginePath, $_MemCache, $_GameConfig, $_Lang, $_DontShowMenus;

    $Result = CheckUserSessionCookie();
    $Now = time();

    if($Result !== false)
    {
        if($Result['is_banned'] == 1)
        {
            if($Result['ban_endtime'] > $Now)
            {
                if(strstr($_SERVER['SCRIPT_NAME'], 'logout.php') !== false || strstr($_SERVER['SCRIPT_NAME'], 'contact.php') !== false)
                {
                    $GetScriptNameExplode = explode('/', $_SERVER['SCRIPT_NAME']);
                    $GetScriptName = end($GetScriptNameExplode);
                    if($GetScriptName == 'logout.php' || $GetScriptName == 'contact.php')
                    {
                        $DontBlockIfBanned = true;
                    }
                }

                if(!isset($DontBlockIfBanned))
                {
                    includeLang('system');
                    includeLang('bannedUser');
                    include("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");
                    $_SkinPath = UNIENGINE_DEFAULT_SKINPATH;

                    $Query_GetBanInfo = '';
                    $Query_GetBanInfo .= "SELECT `Ban`.*, `users`.`username` AS `GiverName` FROM {{table}} AS `Ban` ";
                    $Query_GetBanInfo .= "LEFT JOIN `{{prefix}}users` AS `users` ON `users`.`id` = `Ban`.`GiverID` AND `Ban`.`GiverID` > 0 ";
                    $Query_GetBanInfo .= "WHERE `UserID` = '{$Result['id']}' AND `Active` = 1 ORDER BY `ID` DESC LIMIT 1;";
                    $Ban = doquery($Query_GetBanInfo, 'bans', true);

                    $Parse = $_Lang;
                    $Parse['Insert_StartTime']    = prettyDate('d m Y \o H:i:s', $Ban['StartTime'], 1);
                    $Parse['Insert_EndTime']    = prettyDate('d m Y \o H:i:s', $Ban['EndTime'], 1);

                    $Parse['ChronoApplet']        = InsertJavaScriptChronoApplet('ban', '', $Ban['EndTime'], true);
                    $Parse['Insert_EndTimer']    = pretty_time($Ban['EndTime'] - $Now, true, 'D');
                    if(!empty($Ban['Reason']))
                    {
                        $Parse['Insert_Reason'] = $Ban['Reason'];
                    }
                    else
                    {
                        $Parse['Insert_Reason']        = $_Lang['Ban_Reason_NotSpecified'];
                    }
                    $Parse['Insert_Giver'] = ($Ban['GiverID'] > 0 ? "<b class=\"orange\">{$Ban['GiverName']}</b>" : "<b class=\"red\">{$_Lang['ban_bysys']}</b>");
                    $Parse['Insert_VacationInfo'] = ($Ban['With_Vacation'] == 1 ? $_Lang['Ban_WithVacation'] : $_Lang['Ban_WithoutVacation']);
                    if($Ban['Fleets_Retreated_Own'] == 1 AND $Ban['Fleets_Retreated_Others'] == 1)
                    {
                        $Parse['Insert_FleetsInfo'] = $_Lang['Ban_RetreatedAll'];
                    }
                    else if($Ban['Fleets_Retreated_Own'] == 1)
                    {
                        $Parse['Insert_FleetsInfo'] = $_Lang['Ban_RetreatedOwn'];
                    }
                    else if($Ban['Fleets_Retreated_Others'] == 1)
                    {
                        $Parse['Insert_FleetsInfo'] = $_Lang['Ban_RetreatedOthers'];
                    }
                    else
                    {
                        $Parse['Insert_FleetsInfo'] = $_Lang['Ban_RetreatedNone'];
                    }

                    if($Result['block_cookies'] == 1)
                    {
                        setcookie(COOKIE_BLOCK, (COOKIE_BLOCK_VAL.md5($Result['id'])), $Now + (TIME_YEAR * 30), '', '', false, true);
                    }
                    $_DontShowMenus = true;
                    display(parsetemplate(gettemplate('baninfo'), $Parse), $_Lang['Ban_Header'], false);
                }
            }
            else
            {
                doquery("UPDATE {{table}} SET `is_banned` = 0, `ban_endtime` = 0 WHERE `id` = {$Result['id']};", 'users');
                doquery("UPDATE {{table}} SET `Active` = 0, `Expired` = 1 WHERE `Active` = 1 AND `UserID` = {$Result['id']};", 'bans');
            }
        }
        $Return = $Result;
    }
    else
    {
        $Return = array();
    }

    // Online Record Handler
    $OnlineUsers = doquery("SELECT COUNT(`id`) AS `Count` FROM {{table}} WHERE `onlinetime` >= (UNIX_TIMESTAMP() - ".TIME_ONLINE.");", 'users', true);
    if($OnlineUsers['Count'] > $_GameConfig['rekord'])
    {
        doquery("UPDATE {{table}} SET `config_value` = '{$OnlineUsers['Count']}' WHERE `config_name` = 'rekord';", 'config');
        $_GameConfig['rekord'] = $OnlineUsers['Count'];
        $_MemCache->GameConfig = $_GameConfig;
    }

    return $Return;
}

?>
