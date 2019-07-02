<?php

function SecureInput($Input)
{
    if(!empty($Input))
    {
        if((array)$Input === $Input)
        {
            foreach($Input as $Key => &$Val)
            {
                if((array)$Val === $Val)
                {
                    $Val = SecureInput($Val);
                }
                else
                {
                    $Val = addslashes($Val);
                }
            }
        }
        else
        {
            $Input = addslashes($Input);
        }
    }

    return $Input;
}

function isPro($_User = false)
{
    if($_User === false)
    {
        global $_User;
    }
    if($_User['pro_time'] > time() OR BETA === TRUE)
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

function isLogged()
{
    global $_User;

    if(isset($_User['id']) && $_User['id'] > 0)
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

function loggedCheck($noAlert = false)
{
    global $_Lang, $_DontShowMenus;

    if(!isLogged())
    {
        $_DontShowMenus = true;
        if($noAlert === true)
        {
            die();
        }
        else
        {
            message($_Lang['YouAreNotLogged'], $_Lang['NotLoggedTitle'], 'login.php', 3);
        }
    }
}

function isOnVacation($_User = false)
{
    if($_User === false)
    {
        global $_User;
    }
    if($_User['is_onvacation'] == 1)
    {
        return true;
    }
    return false;
}

function canTakeVacationOff($time = false)
{
    global $_User;
    if($_User['vacation_type'] != 0)
    {
        return true;
    }
    if($time === false)
    {
        $time = time();
    }
    $MinimalVacationTime = ($_User['pro_time'] > $_User['vacation_starttime'] ? MINURLOP_PRO : MINURLOP_FREE) + $_User['vacation_starttime'];
    if($MinimalVacationTime <= $time)
    {
        return true;
    }
    return false;
}

// AuthLevel Checks
function CheckAuth($Type, $CheckMode = AUTHCHECK_NORMAL, $TheUser = false)
{
    $TypesArray = array
    (
        'gameowner'            => AUTHLEVEL_GAMEOWNER,
        'owner'                => AUTHLEVEL_GAMEOWNER,
        'programmer'        => AUTHLEVEL_PROGRAMMER,
        'mainadmin'            => AUTHLEVEL_MAINADMIN,
        'supportadmin'        => AUTHLEVEL_SUPPORTADMIN,
        'supergameoperator' => AUTHLEVEL_SUPERGAMEOPERATOR,
        'sgo'                => AUTHLEVEL_SUPERGAMEOPERATOR,
        'gameoperator'        => AUTHLEVEL_GAMEOPERATOR,
        'go'                => AUTHLEVEL_GAMEOPERATOR,
        'forumteam'            => AUTHLEVEL_FORUMTEAM,
        'user'                => AUTHLEVEL_USER
    );

    if(!in_array($Type, array_keys($TypesArray)) OR !in_array($CheckMode, array(1, 2)))
    {
        return null;
    }

    if($TheUser === false)
    {
        global $_User;
        $TheUser = &$_User;
    }

    if($CheckMode == AUTHCHECK_NORMAL)
    {
        if(isset($TheUser['authlevel']) && $TheUser['authlevel'] >= $TypesArray[$Type])
        {
            return true;
        }
    }
    else if($CheckMode == AUTHCHECK_HIGHER)
    {
        if(isset($TheUser['authlevel']) && $TheUser['authlevel'] > $TypesArray[$Type])
        {
            return true;
        }
    }
    else if($CheckMode == AUTHCHECK_EXACT)
    {
        if(isset($TheUser['authlevel']) && $TheUser['authlevel'] == $TypesArray[$Type])
        {
            return true;
        }
    }
    return false;
}

function GetAuthLabel($TheUser)
{
    if($TheUser['authlevel'] >= AUTHLEVEL_GAMEOWNER)
    {
        return AUTHLEVEL_GAMEOWNER;
    }
    if($TheUser['authlevel'] >= AUTHLEVEL_PROGRAMMER)
    {
        return AUTHLEVEL_PROGRAMMER;
    }
    if($TheUser['authlevel'] >= AUTHLEVEL_MAINADMIN)
    {
        return AUTHLEVEL_MAINADMIN;
    }
    if($TheUser['authlevel'] >= AUTHLEVEL_SUPPORTADMIN)
    {
        return AUTHLEVEL_SUPPORTADMIN;
    }
    if($TheUser['authlevel'] >= AUTHLEVEL_SUPERGAMEOPERATOR)
    {
        return AUTHLEVEL_SUPERGAMEOPERATOR;
    }
    if($TheUser['authlevel'] >= AUTHLEVEL_GAMEOPERATOR)
    {
        return AUTHLEVEL_GAMEOPERATOR;
    }
    if($TheUser['authlevel'] >= AUTHLEVEL_FORUMTEAM)
    {
        return AUTHLEVEL_FORUMTEAM;
    }
    return AUTHLEVEL_USER;
}

function is_email($email)
{
    return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email));
}

function CalculateMaxPlanetFields($planet)
{
    global $_Vars_GameElements;
    return $planet['field_max'] + ($planet[$_Vars_GameElements[33]] * FIELDS_ADDED_BY_TERRAFORMER);
}

// MoraleSystem Functions
function Morale_ReCalculate(&$TheUser, $TheTime = false)
{
    if($TheUser['morale_level'] != 0)
    {
        if($TheTime === false)
        {
            $TheTime = time();
        }

        if($TheUser['morale_droptime'] < $TheTime)
        {
            if($TheUser['morale_lastupdate'] > $TheUser['morale_droptime'])
            {
                $TimeDiff = $TheTime - $TheUser['morale_lastupdate'];
                $TimeStart = $TheUser['morale_lastupdate'];
            }
            else
            {
                $TimeDiff = $TheTime - $TheUser['morale_droptime'];
                $TimeStart = $TheUser['morale_droptime'];
            }

            $MoraleDropInterval = ($TheUser['morale_level'] > 0 ? MORALE_DROPINTERVAL_POSITIVE : MORALE_DROPINTERVAL_NEGATIVE);
            $MoraleDrop = floor($TimeDiff / $MoraleDropInterval);

            if($MoraleDrop != 0)
            {
                if($TheUser['morale_level'] > 0)
                {
                    $TheUser['morale_level'] -= $MoraleDrop;
                    if($TheUser['morale_level'] < 0)
                    {
                        $TheUser['morale_level'] = 0;
                    }
                }
                else
                {
                    $TheUser['morale_level'] += $MoraleDrop;
                    if($TheUser['morale_level'] > 0)
                    {
                        $TheUser['morale_level'] = 0;
                    }
                }

                if($TheUser['morale_level'] == 0)
                {
                    $TheUser['morale_droptime'] = 0;
                    $TheUser['morale_lastupdate'] = 0;
                }
                else
                {
                    $TheUser['morale_lastupdate'] = $TimeStart + ($MoraleDrop * $MoraleDropInterval);
                }

                return true;
            }
        }
    }

    return false;
}

function Morale_AddMorale(&$TheUser, $Type, $Factor, $LevelFactor = 1, $TimeFactor = 1, $TheTime = false)
{
    $effectiveFactor = ($Factor > MORALE_MAXIMALFACTOR ? MORALE_MAXIMALFACTOR : $Factor);

    $AddLevel = floor(($effectiveFactor / 2) * $LevelFactor) * $Type;
    if($AddLevel == 0)
    {
        return false;
    }

    if($TheTime === false)
    {
        $TheTime = time();
    }

    $AddTime = floor($effectiveFactor * 3600 * $TimeFactor);

    $NewMoralePositive = ($AddLevel > 0 ? true : false);
    $OldMoralePositive = ($TheUser['morale_level'] > 0 ? true : false);

    if($NewMoralePositive === $OldMoralePositive)
    {
        $TheUser['morale_level'] += $AddLevel;
        if($TheUser['morale_droptime'] <= $TheTime)
        {
            $TheUser['morale_droptime'] = $TheTime + $AddTime;
        }
        else
        {
            $TheUser['morale_droptime'] += $AddTime;
        }
    }
    else
    {
        $DropTimeDiff = $TheUser['morale_droptime'] - $TheTime;
        if($AddTime > $DropTimeDiff)
        {
            if($DropTimeDiff < 0)
            {
                $DropTimeDiff = 0;
            }
            $TheUser['morale_droptime'] = $TheTime;
            $TheUser['morale_level'] += $AddLevel;
            if(($NewMoralePositive AND $TheUser['morale_level'] > 0) OR (!$NewMoralePositive AND $TheUser['morale_level'] < 0))
            {
                $TheUser['morale_droptime'] += floor(($TheUser['morale_level'] / $AddLevel) * ($AddTime - $DropTimeDiff));
            }
        }
        else
        {
            $TheUser['morale_droptime'] -= $AddTime;
        }
    }

    if($TheUser['morale_level'] > 100)
    {
        $TheUser['morale_level'] = 100;
    }
    else if($TheUser['morale_level'] < -100)
    {
        $TheUser['morale_level'] = -100;
    }

    $DropTimeDiff = $TheUser['morale_droptime'] - $TheTime;
    $ThisMoralePositive = ($TheUser['morale_level'] > 0 ? true : false);
    if($ThisMoralePositive AND $DropTimeDiff > MORALE_MAXDROPTIME_POSITIVE)
    {
        $TheUser['morale_droptime'] -= ($DropTimeDiff - MORALE_MAXDROPTIME_POSITIVE);
    }
    else if(!$ThisMoralePositive AND $DropTimeDiff > MORALE_MAXDROPTIME_NEGATIVE)
    {
        $TheUser['morale_droptime'] -= ($DropTimeDiff - MORALE_MAXDROPTIME_NEGATIVE);
    }

    return true;
}

// Display Functions
function AdminMessage($mes, $title = 'Error', $dest = '', $time = 3)
{
    message($mes, $title, $dest, $time, true);
}

function message($Message, $Title = 'Error', $RedirectLocation = '', $RedirectTime = '3', $IsAdminMsg = false)
{
    global $page;

    $parse['title'] = $Title;
    $parse['mes'] = $Message;

    DisplayHelper_DoRedirect($RedirectLocation, $RedirectTime);
    $page .= parsetemplate(gettemplate('sysmessage_body'), $parse);
    display($page, $Title, false, $IsAdminMsg);
}

function display($PageCode, $PageTitle = '', $ShowTopResourceBar = true, $IsAdminPage = false)
{
    global  $_GameConfig, $_User, $_SkinPath, $_Planet, $_DisplaySettings,
            $_DontShowMenus, $NewMSGCount, $_BenchTool, $_Vars_AllyRankLabels;

    if(!empty($_BenchTool)){ $_BenchTool->simpleCountStart(false, 'telemetry__d'); }

    $DisplayPage = '';
    if(empty($_SkinPath))
    {
        $_SkinPath = DEFAULT_SKINPATH;
    }

    $ProbablyOnAdminPage = false;
    preg_match('#(admin\/|ajax\/)?([^\/]{1,})\.php#si', $_SERVER['SCRIPT_NAME'], $match);
    $pageurl = $match[2];
    if(empty($pageurl))
    {
        $pageurl = 'unknown';
    }
    else
    {
        if($match[1] == 'admin/')
        {
            $pageurl = 'admin/'.$pageurl;
            $ProbablyOnAdminPage = true;
        }
        if($match[1] == 'ajax/')
        {
            $pageurl = 'ajax/'.$pageurl;
        }
        $pageurl .= '.php';
    }
    $pageurl = preg_replace('#[^a-zA-Z0-9\.\_\-\/]{1,}#si', '', $pageurl);

    Handler_MailCache();

    if($ShowTopResourceBar)
    {
        $DisplayPage .= ShowTopNavigationBar($_User, $_Planet);
    }
    else
    {
        if(isLogged() AND $_DontShowMenus !== true)
        {
            if(!defined('IN_ERROR'))
            {
                $NewMSGCount = doquery("SELECT COUNT(`id`) as `count` FROM {{table}} WHERE `id_owner` = {$_User['id']} AND `deleted` = false AND `read` = false;", 'messages', true);
                $NewMSGCount = $NewMSGCount['count'];
            }
            else
            {
                $NewMSGCount = 0;
            }
        }
    }

    Handler_UserDevLogs();
    Handler_UserTasksUpdate();
    Handler_SystemAlerts();

    $Parse['TaskInfoBar'] = GlobalTemplate_GetTaskBox();
    $Parse['SkinPath'] = $_SkinPath;
    $Parse['PHP_CurrentLangISOCode'] = getCurrentLangISOCode();
    $Parse['PHP_InjectAfterBody'] = GlobalTemplate_GetAfterBody()."\n";
    $Parse['PHP_InjectIntoBottomMenu'] = GlobalTemplate_GetBottomMenuInjection();
    $Parse['PHP_Meta'] = DisplayHelper_GetRedirect();

    if(!empty($_BenchTool) AND (isset($_GET['showbenchresult']) && $_GET['showbenchresult'] == 'true'))
    {
        $PageCode .= '{PLACE_BENCHRESULT_HERE}';
    }

    $DisplayPage .= $PageCode;

    $TopMenuAdmin = false;
    if($_DontShowMenus === true)
    {
        $TPL = gettemplate('game_main_body_simple');
        if($IsAdminPage OR (CheckAuth('user', AUTHCHECK_HIGHER) AND $ProbablyOnAdminPage))
        {
            $TopMenuAdmin = true;
            $Parse['AdminBack'] = '../';
        }
        if(empty($PageTitle))
        {
            $Parse['title'] = $_GameConfig['game_name'];
        }
        else
        {
            $Parse['title'] = $PageTitle.' - '.$_GameConfig['game_name'];
        }
        $Parse['game_content_replace'] = $DisplayPage;
        $Page = parsetemplate($TPL, $Parse);
    }
    else
    {
        $TPL = gettemplate('game_main_body');
        if($IsAdminPage OR (CheckAuth('user', AUTHCHECK_HIGHER) AND $ProbablyOnAdminPage))
        {
            $TopMenuAdmin = true;
            $Parse['AdminBack'] = '../';
        }

        if($_User['id'] > 0)
        {
            $Parse['InsertChatMsgCount'] = '0';
            $Parse['Insert_AllyChat_MsgCount'] = '0';

            if(!defined('IN_ERROR'))
            {
                if($_DisplaySettings['dontShow_MainChat_MsgCount'] !== true)
                {
                    $Query_Counters[0] = '';
                    $Query_Counters[0] .= "(SELECT '1' AS `Type`, COUNT(`msg`.`ID`) AS `Count` ";
                    $Query_Counters[0] .= "FROM `{{prefix}}chat_messages` AS `msg` ";
                    $Query_Counters[0] .= "LEFT JOIN `{{prefix}}chat_online` AS `visit` ON `visit`.`RID` = 0 AND `visit`.`UID` = {$_User['id']} ";
                    $Query_Counters[0] .= "WHERE `msg`.`RID` = 0 AND `msg`.`TimeStamp_Add` > IF(`visit`.`LastOnline` > 0, `visit`.`LastOnline`, 0))";
                }
                if($_User['ally_ChatRoom_ID'] > 0 AND $_DisplaySettings['dontShow_AllyChat_MsgCount'] !== true)
                {
                    $Query_Counters[1] = '';
                    $Query_Counters[1] .= "(SELECT '2' AS `Type`, COUNT(`msg`.`ID`) AS `Count` ";
                    $Query_Counters[1] .= "FROM `{{prefix}}chat_messages` AS `msg` ";
                    $Query_Counters[1] .= "LEFT JOIN `{{prefix}}chat_online` AS `visit` ON `visit`.`RID` = {$_User['ally_ChatRoom_ID']} AND `visit`.`UID` = {$_User['id']} ";
                    $Query_Counters[1] .= "WHERE `msg`.`RID` = {$_User['ally_ChatRoom_ID']} AND `msg`.`TimeStamp_Add` > IF(`visit`.`LastOnline` > 0, `visit`.`LastOnline`, 0))";
                }
                $Query_Counters[2] = "(SELECT '3' AS `Type`, COUNT(*) AS `Count` FROM `{{prefix}}buddy` WHERE `owner` = {$_User['id']} AND `active` = 0)";

                $Query_Counters = implode(' UNION ', $Query_Counters);
                $SQLResult_GetCounters = doquery($Query_Counters, '');
                while($CountersData = $SQLResult_GetCounters->fetch_assoc())
                {
                    if($CountersData['Type'] == 1)
                    {
                        $Parse['InsertChatMsgCount'] = prettyNumber($CountersData['Count']);
                        if($CountersData['Count'] > 0)
                        {
                            $Parse['InsertChatMsgCount'] = '<b class="orange">'.$Parse['InsertChatMsgCount'].'</b>';
                        }
                    }
                    elseif($CountersData['Type'] == 2)
                    {
                        $Parse['Insert_AllyChat_MsgCount'] = prettyNumber($CountersData['Count']);
                        if($CountersData['Count'] > 0)
                        {
                            $Parse['Insert_AllyChat_MsgCount'] = '<b class="orange">'.$Parse['Insert_AllyChat_MsgCount'].'</b>';
                        }
                    }
                    elseif($CountersData['Type'] == 3)
                    {
                        if($CountersData['Count'] > 0)
                        {
                            $Parse['InsertBuddyCount'] = ' (<b class="orange">'.prettyNumber($CountersData['Count']).'</b>)';
                        }
                    }
                }
            }

            if($_User['ally_ChatRoom_ID'] > 0 && (!isset($_DisplaySettings['dontShow_AllyChat_Link']) || $_DisplaySettings['dontShow_AllyChat_Link'] !== true))
            {
                $InsertAllyChatLink = false;
                if($_User['ally_owner'] == $_User['id'])
                {
                    $InsertAllyChatLink = true;
                }
                else
                {
                    $Temp1 = json_decode($_User['ally_ranks'], true);
                    foreach($Temp1 as $RankID => $RankData)
                    {
                        if($RankID != $_User['ally_rank_id'])
                        {
                            continue;
                        }
                        foreach($RankData as $DataID => $DataVal)
                        {
                            $Temp2[$_Vars_AllyRankLabels[$DataID]] = $DataVal;
                        }
                    }
                    if($Temp2['canusechat'] === true)
                    {
                        $InsertAllyChatLink = true;
                    }
                }
                if($InsertAllyChatLink === true)
                {
                    global $_LeftMenuSettings;
                    $_LeftMenuSettings['LM_Insert_AllyChatLink'] = array('rid' => $_User['ally_ChatRoom_ID'], 'count' => $Parse['Insert_AllyChat_MsgCount']);
                }
            }
        }

        $TopMenuLang = includeLang('topmenu', true);
        $Parse = array_merge($Parse, $TopMenuLang);

        $Parse['UserID'] = $_User['id'];
        $Parse['Username'] = $_User['username'];

        if(isPro())
        {
            $Parse['AccType_Color'] = 'red';
            $Parse['AccType_Title'] = $Parse['ProTitle'];
            $Parse['AccType_Name']= $Parse['Pro'];
        }
        else
        {
            $Parse['AccType_Color'] = 'orange';
            $Parse['AccType_Title'] = $Parse['FreeTitle'];
            $Parse['AccType_Name']= $Parse['Free'];
        }
        if(CheckAuth('go'))
        {
            $GetAuthLabel = GetAuthLabel($_User);
            if($GetAuthLabel == 50)
            {
                $Parse['AdminLinkTitle']= $Parse['GOLinkTitle'];
                $Parse['admin'] = $Parse['go_cp'];
            }
            elseif($GetAuthLabel == 70)
            {
                $Parse['AdminLinkTitle']= $Parse['SGOLinkTitle'];
                $Parse['admin'] = $Parse['sgo_cp'];
            }
            else
            {
                $Parse['admin'] = $Parse['admin_cp'];
            }

            if($TopMenuAdmin === true)
            {
                $Parse['AdminLink'] = '<a style="margin-left: 12px;" href="../overview.php" title="'.$Parse['AdminLinkTitle'].'">'.$Parse['admin'].'</a>';
            }
            else
            {
                $Parse['AdminLink'] = '<a style="margin-left: 12px;" href="admin/overview.php" title="'.$Parse['AdminLinkTitle'].'">'.$Parse['admin'].'</a>';
            }
        }

        if($_User['total_rank'] > 0)
        {
            $Parse['userpoints'] = $_User['total_rank'];
        }
        else
        {
            $Parse['userpoints'] = '0';
        }
        $Parse['now'] = date('d.m.Y <b>H:i:s</b>');
        $Parse['ServerTimestamp'] = time();

        if($IsAdminPage OR (CheckAuth('user', AUTHCHECK_HIGHER) AND $ProbablyOnAdminPage === true))
        {
            $GetAuthLabel = GetAuthLabel($_User);
            if($GetAuthLabel == 50)
            {
                $AdminTPL = 'left_menu_go';
            }
            elseif($GetAuthLabel == 70)
            {
                $AdminTPL = 'left_menu_sgo';
            }
            elseif($GetAuthLabel >= 90)
            {
                $AdminTPL = 'left_menu';
            }
            else
            {
                $AdminTPL = 'left_menu_safe';
            }
            $Parse['left_menu_replace'] = ShowLeftMenu('admin/'.$AdminTPL);
        }
        else
        {
            $Parse['left_menu_replace'] = ShowLeftMenu();
        }
        $Parse['game_content_replace'] = $DisplayPage;
        $Parse['title'] = (!empty($PageTitle) ? $PageTitle.' - ' : '').$_GameConfig['game_name'];
        $Page = parsetemplate($TPL, $Parse);
    }

    if(!empty($_BenchTool)){ $_BenchTool->simpleCountStop(); }

    if(!empty($_BenchTool))
    {
        $BenchResult = $_BenchTool->ReturnResult();
        if(isset($_GET['showbenchresult']) && $_GET['showbenchresult'] == 'true')
        {
            $Page = str_replace('{PLACE_BENCHRESULT_HERE}', $BenchResult, $Page);
        }
    }
    Handler_Telemetry($pageurl);

    closeDBLink();

    echo $Page;

    die();
}

function safeDie($DieMsg = '')
{
    global $_BenchTool;

    preg_match('#(admin\/|ajax\/)?([^\/]{1,})\.php#si', $_SERVER['SCRIPT_NAME'], $match);
    $pageurl = $match[2];
    if(empty($pageurl))
    {
        $pageurl = 'unknown';
    }
    else
    {
        if($match[1] == 'admin/')
        {
            $pageurl = 'admin/'.$pageurl;
        }
        if($match[1] == 'ajax/')
        {
            $pageurl = 'ajax/'.$pageurl;
        }
        $pageurl .= '.php';
    }
    $pageurl = preg_replace('#[^a-zA-Z0-9\.\_\-\/]{1,}#si', '', $pageurl);

    Handler_MailCache();
    Handler_UserDevLogs();
    Handler_UserTasksUpdate();
    Handler_SystemAlerts();

    if(!empty($_BenchTool))
    {
        $BenchResult = $_BenchTool->ReturnResult();
    }
    Handler_Telemetry($pageurl);

    closeDBLink();

    die($DieMsg);
}

function ShowLeftMenu($Template = 'left_menu')
{
    global $_Lang, $_SkinPath, $_GameConfig, $NewMSGCount, $_LeftMenuSettings;

    includeLang('leftmenu');
    if(strstr($Template, 'admin/'))
    {
        includeLang('admin');
    }

    $Since = 2010;
    $MenuTPL = gettemplate($Template);
    $parse = $_Lang;
    $parse['GameVersion'] = VERSION;
    $parse['GameBuild'] = REVISION;
    $parse['skinpath'] = $_SkinPath;

    if($NewMSGCount > 0)
    {
        $parse['Messages_Color'] = 'orange';
        $parse['Messages_AddCounter'] = '<b id="lm_msgc">('.$NewMSGCount.')</b>';
    }
    if(!empty($_LeftMenuSettings['LM_Insert_AllyChatLink']))
    {
        $parse['LM_Insert_AllyChatLink'] = "<a href=\"chat.php?rid={$_LeftMenuSettings['LM_Insert_AllyChatLink']['rid']}\" class=\"AllyChatLink\" title=\"{$_Lang['AllyChat_Title']}\">({$_LeftMenuSettings['LM_Insert_AllyChatLink']['count']})</a>";
    }

    $parse['servername'] = $_GameConfig['game_name'];
    $parse['CopyTime'] = ($Since == date('Y')) ? $Since : $Since.' - '.date('Y');
    $Menu = parsetemplate( $MenuTPL, $parse);

    return $Menu;
}

function DisplayHelper_DoRedirect($Location, $Delay)
{
    global $UEV_DisplayHelper_DoRedirect;

    $UEV_DisplayHelper_DoRedirect = array('location' => $Location, 'delay' => $Delay);
}
function DisplayHelper_GetRedirect()
{
    global $UEV_DisplayHelper_DoRedirect;

    if(!empty($UEV_DisplayHelper_DoRedirect['location']))
    {
        return '<meta http-equiv="refresh" content="'.$UEV_DisplayHelper_DoRedirect['delay'].';URL=\''.$UEV_DisplayHelper_DoRedirect['location'].'\';">';
    }
    else
    {
        return '';
    }
}
function GlobalTemplate_AppendToAfterBody($code)
{
    global $_Templates_InsertJustAfterBody;

    $_Templates_InsertJustAfterBody .= $code;
}
function GlobalTemplate_AppendToBottomMenuInjection($code)
{
    global $_Templates_BottomMenuInjection;

    $_Templates_BottomMenuInjection .= $code;
}
function GlobalTemplate_AppendToTaskBox($code)
{
    global $_Templates_TaskBox;

    $_Templates_TaskBox .= $code;
}
function GlobalTemplate_GetAfterBody()
{
    global $_Templates_InsertJustAfterBody;

    return $_Templates_InsertJustAfterBody;
}
function GlobalTemplate_GetBottomMenuInjection()
{
    global $_Templates_BottomMenuInjection;

    return $_Templates_BottomMenuInjection;
}
function GlobalTemplate_GetTaskBox()
{
    global $_Templates_TaskBox;

    return $_Templates_TaskBox;
}

////////////////////////////////////////////////
////////////////////////////////////////////////
////////////////////////////////////////////////

function multidim2onedim($array, $first_time = true)
{
    static $temp;

    foreach($array as $val)
    {
        if(is_array($val))
        {
            multidim2onedim($val, false);
        }
        else
        {
            $temp[] = $val;
        }
    }

    $return = $temp;
    if($first_time === true)
    {
        $temp = false;
    }
    return $return;
}

function innerReplace($array, $replaceArray)
{
    foreach($array as $key => $val)
    {
        if(isset($array[$key]))
        {
            $val = preg_replace_callback(
                '#\{([a-z0-9\-_]*?)\}\[([a-z0-9\-_]*?)\]#Ssi',
                function ($matches) use ($replaceArray) {
                    return (
                        isset($replaceArray[$matches[1]][$matches[2]]) ?
                        $replaceArray[$matches[1]][$matches[2]] :
                        ""
                    );
                },
                $val
            );
            $val = preg_replace_callback(
                '#\{([a-z0-9\-_]*?)\}#Ssi',
                function ($matches) use ($replaceArray) {
                    return (
                        isset($replaceArray[$matches[1]]) ?
                        $replaceArray[$matches[1]] :
                        ""
                    );
                },
                $val
            );

            $ocurence = substr_count($val, '%s');

            if($ocurence > 0)
            {
                for($i = 0; $i < $ocurence; $i += 1)
                {
                    $ArgArr[] = $array[($key + $i + 1)];
                    unset($array[($key + $i + 1)]);
                }
                $val = vsprintf($val, $ArgArr);
                $ArgArr = false;
            }

            $temp[] = $val;
        }
    }

    return $temp;
}

function ServerStamp($TimeStamp = false)
{
    if($TimeStamp !== false AND $TimeStamp > SERVER_MAINOPEN_TSTAMP)
    {
        $Stamp = $TimeStamp;
    }
    else
    {
        $Stamp = time();
    }
    return $Stamp - SERVER_MAINOPEN_TSTAMP;
}

function CreateAccessLog($RootPath = '', $Prepend2Filename = '')
{
    return;

    global $_User, $_SERVER, $_POST;

    // --- Look for or Create LogDir ---
    if(isset($_User['id']) && $_User['id'] > 0)
    {
        $Log_UserID = str_pad($_User['id'], 6, '0', STR_PAD_LEFT);
    }
    else
    {
        $Log_UserID = '000000';
    }
    $Log_DirName = "{$RootPath}action_logs/{$Log_UserID}";

    if(!(file_exists($Log_DirName) AND is_dir($Log_DirName)))
    {
        mkdir($Log_DirName);
        file_put_contents($Log_DirName.'/index.php', '<?php header("Location: ../index.php"); ?>');
    }

    // --- Get Current Date and Time ---
    $Date = explode('_', date('Y_m_d_H_i_s'));
    $CDay = "{$Date[0]}_{$Date[1]}_{$Date[2]}";
    $CTim = ($Date[3] * 3600) + ($Date[4] * 60) + $Date[5];

    // --- Create Post Hash ---
    if(!empty($_POST))
    {
        $PackPOST = json_encode($_POST);
        $PostHash = md5($PackPOST);
    }
    else
    {
        $PackPOST = 'N';
        $PostHash = '8d9c307cb7f3c4a32822a51922d1ceaa';
    }
    // --- Create other data ---
    $CurrentBrowser = addslashes(trim($_SERVER['HTTP_USER_AGENT']));
    $CurrentScreen = (isset($_User['new_screen_settings']) ? $_User['new_screen_settings'] : '');

    // --- Get CurrentFile Name ---
    $ScriptRequestData = explode('/', $_SERVER['SCRIPT_NAME']);
    $FileName = $Prepend2Filename.end($ScriptRequestData).str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);
    $PageHash = md5($FileName);

    // --- Set CurrentLog Name & Path ---
    $Log_FileName = "Log_U_{$Log_UserID}_D_{$CDay}.php";
    $Log_FilePath = "{$Log_DirName}/{$Log_FileName}";
    $LastDataFilepath = $Log_DirName.'/GetLastData.php';
    $WriteLogDataFile = false;
    if(!file_exists($Log_FilePath))
    {
        // This Log is not created yet
        $AddDie = true;
        $WriteLogDataFile = true;
    }
    else
    {
        // This Log was already created
        $AddDie = false;
        if(file_exists($LastDataFilepath))
        {
            // Data about last log are not empty
            include($LastDataFilepath);
        }
        else
        {
            // Data about last log are empty
            $WriteLogDataFile = true;
        }
    }

    if(!isset($LastLoggedIP))
    {
        $LastLoggedIP = '';
    }
    if(!isset($LastBrowser))
    {
        $LastBrowser = '';
    }
    if(!isset($LastScreenSettings))
    {
        $LastScreenSettings = '';
    }
    if(!isset($LastPageHash))
    {
        $LastPageHash = '';
    }
    if(!isset($LastPostHash))
    {
        $LastPostHash = '';
    }

    $LogFile = fopen($Log_FilePath, 'a');
    $FileSize = filesize($Log_FilePath);
    $LogDataNow = '';
    if($AddDie === false)
    {
        ftruncate($LogFile, $FileSize - 4);
    }
    else
    {
        $LogDataNow = '<?php header("Location: ../index.php"); die(\'\');/*'."\n";
    }
    if($LastLoggedIP != $_SERVER['REMOTE_ADDR'] OR $LastPageHash != $PageHash OR $LastPostHash != $PostHash OR $CurrentBrowser != $LastBrowser OR $CurrentScreen != $LastScreenSettings)
    {
        if($LastLoggedIP != $_SERVER['REMOTE_ADDR'])
        {
            $ToBracket[] = 'A'.$_SERVER['REMOTE_ADDR'];
        }
        if($CurrentBrowser != $LastBrowser)
        {
            $ToBracket[] = 'B'.$CurrentBrowser;
        }
        if($CurrentScreen != $LastScreenSettings)
        {
            $ToBracket[] = 'S'.$CurrentScreen;
        }
        if(!empty($ToBracket))
        {
            $LogDataNow .= '['.implode('|', $ToBracket)."]\n";
        }
        $WriteLogDataFile = true;
    }
    if($WriteLogDataFile === true)
    {
        file_put_contents($LastDataFilepath, "<?php \$LastLoggedIP = '{$_SERVER['REMOTE_ADDR']}'; \$LastBrowser = '{$CurrentBrowser}'; \$LastScreenSettings = '{$CurrentScreen}'; \$LastPageHash = '{$PageHash}'; \$LastPostHash = '{$PostHash}'; ?>");
    }
    if($LastPageHash === $PageHash)
    {
        $FileName = 'R';
        if($LastPostHash === $PostHash)
        {
            $PackPOST = '';
        }
    }
    if(!empty($PackPOST))
    {
        $PackPOST = '|'.$PackPOST;
    }
    $LogDataNow .= "{$CTim}|{$FileName}{$PackPOST}";
    fwrite($LogFile, "{$LogDataNow}\n*/?>");
    fclose($LogFile);
}

function Handler_MailCache()
{
    global $_Cache;

    if(!empty($_Cache['Messages']))
    {
        SendSimpleMultipleMessages($_Cache['Messages']);
    }
}

function Handler_Telemetry($pageurl)
{
    global $_User, $_BenchTool, $_GameConfig, $DisableTelemetry, $_GET, $_POST, $_MemCache;

    if($_GameConfig['TelemetryEnabled'] == 1 AND !empty($_BenchTool) AND $DisableTelemetry !== true)
    {
        $TimeArray = $_BenchTool->ReturnTimeArray();
        $SimpleArray = $_BenchTool->ReturnSimpleCountArray();

        $TelemetryData['_t'] = end($TimeArray);
        foreach($SimpleArray as $Data)
        {
            if(strstr($Data['name'], 'telemetry_'))
            {
                if(isset($Data['result']) && $Data['result'] > 0)
                {
                    $TelemetryData[str_replace('telemetry_', '', $Data['name'])] = $Data['result'];
                }
            }
        }

        $TelemetryPage['page'] = $TelemetryPage['hash'] = $pageurl;
        $TelemetryPage['hash'] .= '|';
        foreach($_GET as $Key => $Value)
        {
            if(in_array($Key, array('mode', 'cmd', 'type')))
            {
                if(is_string($Value) OR is_numeric($Value))
                {
                    $TelemetryPage['get'][$Key] = $Key.'='.preg_replace('#[^a-zA-Z0-9\.\_\-\/]{1,}#si', '*', $Value);
                }
                else
                {
                    $TelemetryPage['get'][$Key] = $Key.'=(utype)';
                }
                $TelemetryPage['hash'] .= $TelemetryPage['get'][$Key].'|';
            }
        }
        if(!empty($_POST))
        {
            $TelemetryPage['post'] = '1';
        }
        else
        {
            $TelemetryPage['post'] = '0';
        }
        $TelemetryPage['hash'] .= 'post'.$TelemetryPage['post'];
        $TelemetryPage['hash'] = md5($TelemetryPage['hash']);
        $TelemetryPage['cacheKey'] = 'Telemetry_pid_'.$TelemetryPage['hash'];
        $TelemetryPage['id'] = false;

        if(UNIENGINE_HASAPC)
        {
            $TelemetryPage['id'] = $_MemCache->$TelemetryPage['cacheKey'];
            $FromCache = true;
        }
        if($TelemetryPage['id'] === false)
        {
            $FromCache = false;
            $SelectPageID = doquery("SELECT `ID` FROM {{table}} WHERE `Hash` = '{$TelemetryPage['hash']}' LIMIT 1;", 'telemetry_pages', true);

            if(!($SelectPageID['ID'] > 0))
            {
                if(!empty($TelemetryPage['get']))
                {
                    $TelemetryPage['implodeget'] = implode('&', $TelemetryPage['get']);
                }
                else
                {
                    $TelemetryPage['implodeget'] = '';
                }
                doquery("INSERT INTO {{table}} VALUES (NULL, '{$TelemetryPage['page']}', '{$TelemetryPage['implodeget']}', '{$TelemetryPage['hash']}', {$TelemetryPage['post']});", 'telemetry_pages');
                $SelectPageID = doquery("SELECT LAST_INSERT_ID() as `ID`;", '', true);
                $TelemetryPage['id'] = $SelectPageID['ID'];
            }
            else
            {
                $TelemetryPage['id'] = $SelectPageID['ID'];
            }
        }
        if($TelemetryPage['id'] > 0)
        {
            $ThisUserID = (isset($_User['id']) && $_User['id'] > 0) ? $_User['id'] : 0;

            if($FromCache !== true AND UNIENGINE_HASAPC)
            {
                $_MemCache->$TelemetryPage['cacheKey'] = $TelemetryPage['id'];
            }
            foreach($TelemetryData as $Key => $Data)
            {
                $TelemetryData[$Key] = sprintf('%0.6f', $Data);
            }
            $TelemetryQuery = "INSERT INTO {{table}} VALUES (NULL, {$TelemetryPage['id']}, {$ThisUserID}, UNIX_TIMESTAMP(), '".json_encode($TelemetryData)."');";
            doquery($TelemetryQuery, 'telemetry_data');
        }
    }
}

function Handler_UserDevLogs()
{
    global $UserDev_Log, $_User;

    if(!empty($UserDev_Log))
    {
        $UserDev_Log_Query = "INSERT INTO {{table}} (`ID`, `UserID`, `Date`, `Place`, `PlanetID`, `Code`, `ElementID`, `AdditionalData`) VALUES ";
        foreach($UserDev_Log as $ThisData)
        {
            if(empty($ThisData['AdditionalData']))
            {
                $ThisData['AdditionalData'] = 'NULL';
            }
            else
            {
                $ThisData['AdditionalData'] = "'{$ThisData['AdditionalData']}'";
            }
            if(isset($ThisData['UserID']) && $ThisData['UserID'] > 0)
            {
                $SetUser = $ThisData['UserID'];
            }
            else
            {
                $SetUser = $_User['id'];
            }
            $ThisData['Date'] = $ThisData['Date'] - SERVER_MAINOPEN_TSTAMP;
            $UserDev_Log_Query_Array[] = "(NULL, {$SetUser}, {$ThisData['Date']}, {$ThisData['Place']}, {$ThisData['PlanetID']}, {$ThisData['Code']}, {$ThisData['ElementID']}, {$ThisData['AdditionalData']})";
        }
        $UserDev_Log_Query .= implode(', ', $UserDev_Log_Query_Array);
        doquery($UserDev_Log_Query, 'user_developmentlog');
    }
}

function Handler_UserTasksUpdate()
{
    global $_User, $UserTasksUpdate, $GlobalParsedTasks, $_Vars_TasksData;

    if(!empty($UserTasksUpdate))
    {
        foreach($UserTasksUpdate as $UserID => $UserData)
        {
            if($UserID == $_User['id'])
            {
                $UserParsedTasks = $UserParsedTasksOrg = $_User['tasks_done_parsed'];
            }
            else
            {
                $UserParsedTasks = $UserParsedTasksOrg = $GlobalParsedTasks[$UserID]['tasks_done_parsed'];
            }

            if(!empty($UserData['done']))
            {
                foreach($UserData['done'] as $CatID => $CatData)
                {
                    foreach($CatData as $TaskID => $TaskJobs)
                    {
                        $TotalCount = count($TaskJobs);
                        if(isset($UserParsedTasks['jobs'][$CatID][$TaskID]))
                        {
                            $TotalCount += count($UserParsedTasks['jobs'][$CatID][$TaskID]);
                        }
                        if($TotalCount == count($_Vars_TasksData[$CatID]['tasks'][$TaskID]['jobs']))
                        {
                            $UserParsedTasks['locked'][$CatID][] = $TaskID;
                            unset($UserParsedTasks['jobs'][$CatID][$TaskID]);
                            unset($UserParsedTasks['status'][$CatID][$TaskID]);
                            unset($UserParsedTasks['jobdata'][$CatID][$TaskID]);
                        }
                        else
                        {
                            foreach($TaskJobs as $TaskJobDone)
                            {
                                $UserParsedTasks['jobs'][$CatID][$TaskID][] = $TaskJobDone;
                                unset($UserParsedTasks['status'][$CatID][$TaskID][$TaskJobDone]);
                                unset($UserParsedTasks['jobdata'][$CatID][$TaskID][$TaskJobDone]);
                            }
                        }
                    }

                    if(isset($UserParsedTasks['status'][$CatID]) AND empty($UserParsedTasks['status'][$CatID]))
                    {
                        unset($UserParsedTasks['status'][$CatID]);
                        unset($UserParsedTasks['jobdata'][$CatID]);
                    }
                }
            }
            if(!empty($UserData['status']))
            {
                foreach($UserData['status'] as $CatID => $CatData)
                {
                    foreach($CatData as $TaskID => $TaskJobs)
                    {
                        foreach($TaskJobs as $JobID => $JobStatus)
                        {
                            $UserParsedTasks['status'][$CatID][$TaskID][$JobID] = $JobStatus;
                        }
                    }
                }
            }
            if(!empty($UserData['jobdata']))
            {
                foreach($UserData['jobdata'] as $CatID => $CatData)
                {
                    foreach($CatData as $TaskID => $TaskJobs)
                    {
                        foreach($TaskJobs as $JobID => $JobStatus)
                        {
                            $UserParsedTasks['jobdata'][$CatID][$TaskID][$JobID] = $JobStatus;
                        }
                    }
                }
            }
            if(empty($UserParsedTasks['status']))
            {
                unset($UserParsedTasks['status']);
            }
            if(empty($UserParsedTasks['jobdata']))
            {
                unset($UserParsedTasks['jobdata']);
            }

            // Protection against multiplied Tasks and Buggy-Unlock
            if(!empty($UserParsedTasks['locked']))
            {
                foreach($UserParsedTasks['locked'] as $CatID => $_Vars_TasksData)
                {
                    if(strstr($CatID, 's'))
                    {
                        $CatID = str_replace('s', '', $CatID);
                    }
                    foreach($_Vars_TasksData as $TaskID)
                    {
                        if(!empty($UserParsedTasks[$CatID]))
                        {
                            $KeySearch = array_keys($UserParsedTasks[$CatID], $TaskID);
                            if(!empty($KeySearch[0]))
                            {
                                unset($UserParsedTasks[$CatID][$KeySearch[0]]);
                            }
                        }
                    }
                    if(empty($UserParsedTasks[$CatID]))
                    {
                        unset($UserParsedTasks[$CatID]);
                    }
                }
            }

            $UserParsedTasks = json_encode($UserParsedTasks);
            $UserParsedTasksOrg = json_encode($UserParsedTasksOrg);

            if($UserParsedTasks != $UserParsedTasksOrg)
            {
                $UserTasks_UpdateArray[] = "({$UserID}, '{$UserParsedTasks}')";
            }
        }
        if(!empty($UserTasks_UpdateArray))
        {
            $UserTasks_UpdateQuery = '';
            $UserTasks_UpdateQuery .= "INSERT INTO {{table}} (`id`, `tasks_done`) VALUES ";
            $UserTasks_UpdateQuery .= implode(', ', $UserTasks_UpdateArray);
            $UserTasks_UpdateQuery .= " ON DUPLICATE KEY UPDATE `tasks_done` = VALUES(`tasks_done`);";
            doquery($UserTasks_UpdateQuery, 'users');
        }
    }
}

function Alerts_Add($Sender, $Date, $Type, $Code, $Importance, $UserID, $OtherData = '')
{
    global $_SystemAlerts;

    $_SystemAlerts[] = array
    (
        'Sender' => $Sender,
        'Date' => $Date,
        'Type' => $Type,
        'Code' => $Code,
        'Importance' => $Importance,
        'User_ID' => $UserID,
        'Other_Data' => $OtherData
    );
}

function Handler_SystemAlerts()
{
    global $_SystemAlerts;

    if(!empty($_SystemAlerts))
    {
        foreach($_SystemAlerts as $Data)
        {
            if(!empty($Data['Other_Data']))
            {
                $Data['Other_Data'] = getDBLink()->escape_string(json_encode($Data['Other_Data']));
            }
            $Query_Insert_Values[] = "(NULL, {$Data['Sender']}, {$Data['Date']}, {$Data['Type']}, {$Data['Code']}, {$Data['Importance']}, 0, {$Data['User_ID']}, '{$Data['Other_Data']}')";
        }

        if(!empty($Query_Insert_Values))
        {
            $Query_Insert = '';
            $Query_Insert .= "INSERT INTO {{table}} (`ID`, `Sender`, `Date`, `Type`, `Code`, `Importance`, `Status`, `User_ID`, `Other_Data`) VALUES ";
            $Query_Insert .= implode(', ', $Query_Insert_Values);
            doquery($Query_Insert, 'system_alerts');
        }
    }
}

?>
