<?php

include('includes/phpBench.php'); $_BenchTool = new phpBench();
if(!empty($_BenchTool)){ $_BenchTool->simpleCountStart(false, 'telemetry__c'); }

session_start();
if($_SERVER['SERVER_ADDR'] == '127.0.0.1' OR $_SERVER['SERVER_ADDR'] == '::1')
{
    // We are on Localhost
    define('LOCALHOST', TRUE);
    define('TESTSERVER', FALSE);
}
else
{
    // We are not on Localhost
    define('LOCALHOST', FALSE);
    if($_SERVER['HTTP_HOST'] === GAMEURL_REMOTE_TESTSERVERHOST)
    {
        define('TESTSERVER', TRUE);
    }
    else
    {
        define('TESTSERVER', FALSE);
    }
}

ini_set('default_charset', 'UTF-8');

$_GameConfig = array();
$_User = array();
$_Lang = array();
$_DBLink = '';
$ForceIPnUALog = false;
$Common_TimeNow = time();

define('DEFAULT_SKINPATH', 'skins/epicblue/');
define('TEMPLATE_DIR', 'templates/');
define('TEMPLATE_NAME', 'default_template');
define('DEFAULT_LANG', 'pl');

include($_EnginePath.'includes/constants.php');
if(defined('INSTALL_NOTDONE'))
{
    header('Location: ./install/');
    die();
}
include($_EnginePath.'includes/functions.php');
include($_EnginePath.'includes/unlocalised.php');
include($_EnginePath.'includes/ingamefunctions.php');
include($_EnginePath.'class/UniEngine_Cache.class.php');

$_MemCache = new UniEngine_Cache();

$_POST = SecureInput($_POST);
$_GET = SecureInput($_GET);

include($_EnginePath.'includes/vars.php');
include($_EnginePath.'includes/db.php');
include($_EnginePath.'includes/strings.php');

// Load game configuration
if(isset($_MemCache->GameConfig))
{
    $_GameConfig = $_MemCache->GameConfig;
}
else
{
    $Query_GetGameConfig = "SELECT * FROM {{table}};";
    $Result_GetGameConfig = doquery($Query_GetGameConfig, 'config');
    while($FetchData = $Result_GetGameConfig->fetch_assoc())
    {
        $_GameConfig[$FetchData['config_name']] = $FetchData['config_value'];
    }
    $_MemCache->GameConfig = $_GameConfig;
}

define('VERSION', $_GameConfig['EngineInfo_Version']);
define('REVISION', $_GameConfig['EngineInfo_BuildNo']);

if(!defined('UEC_INLOGIN'))
{
    $_User = CheckUserSession();
}

if(defined('IN_ADMIN'))
{
    if(empty($_User['skinpath']))
    {
        $UsePath = DEFAULT_SKINPATH;
    }
    else
    {
        $UsePath = $_User['skinpath'];
    }
    if(strstr($UsePath, 'http:') === false)
    {
        $_SkinPath = $_EnginePath.$UsePath;
    }
    else
    {
        $_SkinPath = $UsePath;
    }
}
else
{
    $_SkinPath = (empty($_User['skinpath'])) ? DEFAULT_SKINPATH : $_User['skinpath'];
}

includeLang('tech');
includeLang('system');

if($_GameConfig['game_disable'] AND !CheckAuth('supportadmin'))
{
    $_DontShowMenus = true;
    if(!empty($_CommonSettings['gamedisable_callback']))
    {
        $_CommonSettings['gamedisable_callback']();
    }
    message(($_GameConfig['close_reason'] ? nl2br($_Lang['ServerIsClosed'].'<br/>'.$_GameConfig['close_reason']) : $_Lang['ServerIsClosed']), $_GameConfig['game_name']);
}

if(!isset($_SetAccessLogPath))
{
    $_SetAccessLogPath = '';
}
if(!isset($_SetAccessLogPreFilename))
{
    $_SetAccessLogPreFilename = '';
}
CreateAccessLog($_SetAccessLogPath, $_SetAccessLogPreFilename);

if(!empty($_GameConfig['banned_ip_list']))
{
    $BannedIPs = explode('|',$_GameConfig['banned_ip_list']);
    if(!empty($BannedIPs) AND (array)$BannedIPs === $BannedIPs)
    {
        if(!empty($_SERVER['REMOTE_ADDR']) AND in_array($_SERVER['REMOTE_ADDR'], $BannedIPs))
        {
            message($_Lang['Game_blocked_for_this_IP'], $_GameConfig['game_name']);
        }
    }
}

if(isLogged())
{
    if(TESTSERVER === TRUE)
    {
        if($_User['allowTestServer'] != 1 AND $_User['id'] != 1)
        {
            message($_Lang['sys_noaccess'], $_Lang['Title_System'], GAMEURL_STRICT, 3);
        }
    }

    if($_User['onlinetime'] > 0 AND $_User['onlinetime'] < ($Common_TimeNow - TIME_ONLINE))
    {
        $ForceIPnUALog = true;
    }

    if(empty($_SESSION['IP_check']))
    {
        $_SESSION['IP_check'] = $_SERVER['REMOTE_ADDR'];
    }
    else
    {
        if($_SERVER['REMOTE_ADDR'] != $_SESSION['IP_check'])
        {
            if($_User['noipcheck'] != 1)
            {
                unset($_SESSION['IP_check']);
                header('Location: logout.php?badip=1');
                safeDie();
            }
            else
            {
                $_SESSION['IP_check'] = $_SERVER['REMOTE_ADDR'];
                $ForceIPnUALog = true;
            }
        }
    }

    if($ForceIPnUALog)
    {
        include("{$_EnginePath}includes/functions/IPandUA_Logger.php");
        IPandUA_Logger($_User);
    }

    if(LOCALHOST === FALSE AND TESTSERVER === FALSE)
    {
        if($Common_TimeNow < SERVER_MAINOPEN_TSTAMP)
        {
            message(sprintf($_Lang['ServerStart_NotReached'], prettyDate('d m Y', SERVER_MAINOPEN_TSTAMP, 1), date('H:i:s', SERVER_MAINOPEN_TSTAMP)), $_Lang['Title_System']);
        }
    }

    if(!empty($_User['activation_code']) AND $_User['first_login'] > 0 AND ($Common_TimeNow - $_User['first_login']) > NONACTIVE_PLAYTIME)
    {
        $_DontShowMenus = true;
        message($_Lang['NonActiveBlock'], $_GameConfig['game_name']);
    }

    // Cookie Blockade
    $ShowBlockadeInfo_CookieStyle = false;
    if(!empty($_COOKIE[COOKIE_BLOCK]))
    {
        if($_COOKIE[COOKIE_BLOCK] === (COOKIE_BLOCK_VAL.md5($_User['id'])) AND $_User['block_cookies'] == 0)
        {
            setcookie(COOKIE_BLOCK, '', $Common_TimeNow - 100000, '', '', false, true);
            $_COOKIE[COOKIE_BLOCK] = '';
        }
        else
        {
            $ShowBlockadeInfo_CookieStyle = true;
        }
    }
    else
    {
        if($_User['block_cookies'] == 1)
        {
            setcookie(COOKIE_BLOCK, (COOKIE_BLOCK_VAL.md5($_User['id'])), $Common_TimeNow + (3 * TIME_YEAR), "", "", false, true);
            $ShowBlockadeInfo_CookieStyle = true;
        }
    }
    if($ShowBlockadeInfo_CookieStyle === true)
    {
        $_DontShowMenus = true;
        message($_Lang['GameBlock_CookieStyle'], $_GameConfig['game_name']);
    }

    if($_User['dokick'] == 1)
    {
        setcookie($_GameConfig['COOKIE_NAME'], '', $Common_TimeNow - 100000, '/', '', 0);
        doquery("UPDATE {{table}} SET `dokick` = 0 WHERE `id` = {$_User['id']};", 'users');

        header('Location: logout.php?kicked=1');
        safeDie();
    }

    // --- Handle Tasks ---
    if(!isset($_UseMinimalCommon) || $_UseMinimalCommon !== true)
    {
        if(!isset($_DontShowMenus) || $_DontShowMenus !== true)
        {
            if(!empty($_User['tasks_done_parsed']['locked']))
            {
                $TaskBoxParseData = includeLang('tasks_infobox', true);
                $DoneTasks = 0;

                foreach($_User['tasks_done_parsed']['locked'] as $CatID => $CatTasks)
                {
                    if(strstr($CatID, 's'))
                    {
                        $CatID = str_replace('s', '', $CatID);
                        $ThisCatSkiped = true;
                    }
                    else
                    {
                        $ThisCatSkiped = false;
                    }
                    foreach($CatTasks as $TaskID)
                    {
                        unset($_User['tasks_done_parsed']['jobs'][$CatID][$TaskID]);
                        if($ThisCatSkiped === false OR ($ThisCatSkiped === true AND $_Vars_TasksData[$CatID]['skip']['tasksrew'] === true))
                        {
                            $TaskBoxLinks[$CatID] = 'cat='.$CatID.'&amp;showtask='.$TaskID;
                            foreach($_Vars_TasksData[$CatID]['tasks'][$TaskID]['reward'] as $RewardData)
                            {
                                Tasks_ParseRewards($RewardData, $_Vars_TasksDataUpdate);
                            }
                        }
                        $DoneTasks += 1;
                    }
                    if(Tasks_IsCatDone($CatID, $_User))
                    {
                        unset($_User['tasks_done_parsed']['jobs'][$CatID]);
                        if($ThisCatSkiped === false OR ($ThisCatSkiped === true AND $_Vars_TasksData[$CatID]['skip']['catrew'] === true))
                        {
                            $TaskBoxLinks[$CatID] = 'mode=log&amp;cat='.$CatID;
                            foreach($_Vars_TasksData[$CatID]['reward'] as $RewardData)
                            {
                                Tasks_ParseRewards($RewardData, $_Vars_TasksDataUpdate);
                            }
                        }
                    }
                    else
                    {
                        if(empty($_User['tasks_done_parsed']['jobs']))
                        {
                            unset($_User['tasks_done_parsed']['jobs']);
                        }
                    }
                }

                if(empty($_User['tasks_done_parsed']['jobs']))
                {
                    unset($_User['tasks_done_parsed']['jobs']);
                }

                if(!empty($TaskBoxLinks))
                {
                    if($DoneTasks > 1)
                    {
                        $TaskBoxParseData['Task'] = $TaskBoxParseData['MoreTasks'];
                    }
                    else
                    {
                        $TaskBoxParseData['Task'] = $TaskBoxParseData['OneTask'];
                    }
                    foreach($TaskBoxLinks as $CatID => $LinkData)
                    {
                        $TaskBoxParseData['CatLinks'][] = sprintf($TaskBoxParseData['CatLink'], $LinkData, $TaskBoxParseData['Names'][$CatID]);
                    }
                    $TaskBoxParseData['CatLinks'] = implode(', ', $TaskBoxParseData['CatLinks']);
                    GlobalTemplate_AppendToTaskBox(parsetemplate(gettemplate('tasks_infobox'), $TaskBoxParseData));
                }

                unset($_User['tasks_done_parsed']['locked']);
                $_User['tasks_done'] = json_encode($_User['tasks_done_parsed']);

                GlobalTemplate_AppendToTaskBox(parsetemplate(gettemplate('tasks_infobox'), $TaskBoxParseData));

                if(!empty($_Vars_TasksDataUpdate['planet']))
                {
                    foreach($_Vars_TasksDataUpdate['planet'] as $Key => $Value2Add)
                    {
                        $_Vars_TasksDataUpdate['planet_array'][] = "`{$Key}` = `{$Key}` + {$Value2Add}";
                    }
                    doquery("UPDATE {{table}} SET ".implode(', ', $_Vars_TasksDataUpdate['planet_array'])." WHERE `id` = {$_User['id_planet']} LIMIT 1;", 'planets');

                    if(!empty($_Vars_TasksDataUpdate['devlog']))
                    {
                        foreach($_Vars_TasksDataUpdate['devlog'] as $DevLogKey => $DevLogRow)
                        {
                            $Insert2DevLog[] = "{$DevLogKey},{$DevLogRow}";
                        }
                        $UserDev_Log[] = array('PlanetID' => $_User['id_planet'], 'Date' => $Common_TimeNow, 'Place' => 30, 'Code' => '0', 'ElementID' => '0', 'AdditionalData' => implode(';', $Insert2DevLog));
                        unset($Insert2DevLog);
                    }
                }
                if(!empty($_Vars_TasksDataUpdate['free_premium']))
                {
                    foreach($_Vars_TasksDataUpdate['free_premium'] as $ItemID)
                    {
                        $_Vars_TasksDataUpdate['free_premium_array'][] = "(NULL, {$_User['id']}, UNIX_TIMESTAMP(), 0, 0, {$ItemID}, 0)";
                    }
                    doquery("INSERT INTO {{table}} VALUES ".implode(', ', $_Vars_TasksDataUpdate['free_premium_array']).";", 'premium_free');
                }
                if(!empty($_Vars_TasksDataUpdate['user']))
                {
                    foreach($_Vars_TasksDataUpdate['user'] as $Key => $Value2Add)
                    {
                        $_Vars_TasksDataUpdate['user_var'][] = "`{$Key}` = `{$Key}` + {$Value2Add}";
                        $_User[$Key] += $Value2Add;
                    }
                }

                $_Vars_TasksDataUpdate['user_var'][] = "`tasks_done` = '{$_User['tasks_done']}'";
                doquery("UPDATE {{table}} SET ".implode(',', $_Vars_TasksDataUpdate['user_var'])." WHERE `id` = {$_User['id']};", 'users');
            }
        }
    }
    // --- Handling Tasks ends here ---

    if(!isset($_AllowInVacationMode) || $_AllowInVacationMode != true)
    {
        // If this place do not allow User to be in VacationMode, show him a message if it's necessary
        if(isOnVacation())
        {
            $MinimalVacationTime = ($_User['pro_time'] > $_User['vacation_starttime'] ? MINURLOP_PRO : MINURLOP_FREE) + $_User['vacation_starttime'];
            $VacationMessage = sprintf($_Lang['VacationTill'], date('d.m.Y H:i:s', $MinimalVacationTime));
            if($MinimalVacationTime <= $Common_TimeNow)
            {
                $VacationMessage .= $_Lang['VacationSetOff'];
            }
            message($VacationMessage, $_Lang['Vacation']);
        }
    }

    if(!isset($_UseMinimalCommon) || $_UseMinimalCommon !== true)
    {
        // Change Planet (if user wants to do this)
        SetSelectedPlanet($_User);

        // Get PlanetRow
        $_Planet = doquery("SELECT * FROM {{table}} WHERE `id` = {$_User['current_planet']};", 'planets', true);
        if($_Planet['id'] <= 0 OR $_Planet['id_owner'] != $_User['id'])
        {
            // If this planet doesn't exist, try to go back to MotherPlanet
            SetSelectedPlanet($_User, $_User['id_planet']);
            $_Planet = doquery("SELECT * FROM {{table}} WHERE `id` = {$_User['id_planet']};", 'planets', true);
            if($_Planet['id'] <= 0)
            {
                // If MotherPlanet doesn't exist, ThrowError and don't allow go further
                message($_Lang['FatalError_PlanetRowEmpty'], 'FatalError');
            }
        }
        CheckPlanetUsedFields($_Planet);

        if($_Planet['planet_type'] == 1)
        {
            $GalaxyRowWhere = "`id_planet` = {$_Planet['id']}";
        }
        else
        {
            $GalaxyRowWhere = "`id_moon` = {$_Planet['id']}";
        }
        $_GalaxyRow = doquery("SELECT * FROM {{table}} WHERE {$GalaxyRowWhere};", 'galaxy', true);

        if(!isset($_BlockFleetHandler) || $_BlockFleetHandler !== true)
        {
            $FleetHandlerReturn = FlyingFleetHandler($_Planet);
            if(isset($FleetHandlerReturn['ThisMoonDestroyed']) && $FleetHandlerReturn['ThisMoonDestroyed'])
            {
                // Redirect User to Planet (from Destroyed Moon)
                SetSelectedPlanet($_User, $_User['id_planet']);
                $_Planet = doquery("SELECT * FROM {{table}} WHERE `id` = {$_User['id_planet']};", 'planets', true);
                if($_Planet['id'] <= 0)
                {
                    message($_Lang['FatalError_PlanetRowEmpty'], 'FatalError');
                }
                else
                {
                    if($_GalaxyRow['id_planet'] != $_Planet['id'])
                    {
                        $_GalaxyRow = doquery("SELECT * FROM {{table}} WHERE `id_planet` = {$_Planet['id']};", 'galaxy', true);
                    }
                }
            }
        }

        if(!isset($_DontForceRulesAcceptance) || $_DontForceRulesAcceptance !== true)
        {
            if($_GameConfig['enforceRulesAcceptance'] == '1' AND $_GameConfig['last_rules_changes'] > 0 AND $_User['rules_accept_stamp'] < $_GameConfig['last_rules_changes'])
            {
                if(isset($_DontShowRulesBox) && $_DontShowRulesBox === true)
                {
                    message($_Lang['RulesAcceptBox_CantUseFunction'], $_Lang['SystemInfo']);
                }
                else
                {
                    if(IN_RULES !== true)
                    {
                        header('Location: rules.php');
                        safeDie();
                    }
                    else
                    {
                        $_ForceRulesAcceptBox = true;
                    }
                }
            }
        }

        if(!isset($_DontCheckPolls) || $_DontCheckPolls !== true)
        {
            if($_User['isAI'] != 1 AND $_User['register_time'] < ($Common_TimeNow - TIME_DAY))
            {
                $Query_SelectPolls = '';
                $Query_SelectPolls .= "SELECT `votes`.`id` AS `vote_id` FROM {{table}} AS `polls` ";
                $Query_SelectPolls .= "LEFT JOIN `{{prefix}}poll_votes` AS `votes` ON `votes`.`poll_id` = `polls`.`id` AND `votes`.`user_id` = {$_User['id']} ";
                $Query_SelectPolls .= "WHERE `polls`.`open` = 1 AND `polls`.`obligatory` = 1;";
                $SelectObligatoryPolls = doquery($Query_SelectPolls, 'polls');
                if($SelectObligatoryPolls->num_rows > 0)
                {
                    $PollsCount = 0;
                    while($SelectObligatoryPollsData = $SelectObligatoryPolls->fetch_assoc())
                    {
                        if($SelectObligatoryPollsData['vote_id'] <= 0)
                        {
                            $PollsCount += 1;
                        }
                    }
                    if($PollsCount > 0)
                    {
                        message(sprintf($_Lang['YouHaveToVoteInSurveys'], $PollsCount), $_Lang['SystemInfo'], 'polls.php', 10);
                    }
                }
            }
        }
    }
}
else
{
    $_DontShowMenus = true;
}

if(!empty($_BenchTool)){ $_BenchTool->simpleCountStop(); }

?>
