<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_AllowInVacationMode = TRUE;
$_DontForceRulesAcceptance = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('settings');

$Now = time();
$LogonLIMIT    = 20;
$MaxEspionageProbesCount = 9999;

$_Lang['ServerSkins'] = '';
$_Lang['QuickRes_PlanetList'] = '';
$_Lang['CreateResSortList'] = '';

$_Lang['MD5OldPass'] = $_User['password'];
$_Lang['ShowOldEmail'] = $_User['email'];
$_User['skinpath'] = $_SkinPath;
$_Lang['skinpath'] = $_User['skinpath'];
$_Lang['VacationDays'] = 3;
$_Lang['PHP_Insert_VacationComeback'] = $Now + ($_Lang['VacationDays'] * TIME_DAY);
$_Lang['PHP_Insert_VacationComeback'] = date('d.m.Y', $_Lang['PHP_Insert_VacationComeback'])." {$_Lang['atHour']} ".date('h:i:s', $_Lang['PHP_Insert_VacationComeback']);
$_Lang['PHP_Insert_LanguageOptions'] = [];

foreach ($_Lang['LanguagesAvailable'] as $langKey => $langData) {
    $isSelectedHTMLAttr = ($langKey == getCurrentLang() ? "selected" : "");

    $_Lang['PHP_Insert_LanguageOptions'][] = (
        "<option value='{$langKey}' {$isSelectedHTMLAttr}>" .
            "{$langData["flag_emoji"]} {$langData["name"]}" .
        "</option>"
    );
}

$_Lang['PHP_Insert_LanguageOptions'] = implode('', $_Lang['PHP_Insert_LanguageOptions']);

$ForceGoingOnVacationMsg = false;

$ChangeNotDone = 0;
$ChangeSetCount = 0;

$SkinNames = array('xnova' => 'XNova', 'epicblue' => 'EpicBlue Fresh', 'epicblue_old' => 'EpicBlue Standard');

$SkinDir = scandir('./skins/');
$SkinCounter = 1;
if(!empty($SkinDir))
{
    foreach($SkinDir as $Element)
    {
        if(strstr($Element, '.') === FALSE)
        {
            if(is_dir('./skins/'.$Element))
            {
                if(empty($SkinNames[$Element]))
                {
                    $SkinNames[$Element] = $Element;
                }
                $_Lang['ServerSkins'] .= '<option value="skins/'.$Element.'/" {select_no'.$SkinCounter.'}>'.$SkinNames[$Element].'</option>';
                $AvailableSkins[$SkinCounter] = "skins/{$Element}";
                $SkinCounter += 1;
            }
        }
    }
}

$SQLResult_SelectAllPlanets = doquery(
    "SELECT `id`, `name`, `galaxy`, `system`, `planet` FROM {{table}} WHERE `id_owner` = {$_User['id']} AND `planet_type` = 1;",
    'planets'
);

while($Planets = $SQLResult_SelectAllPlanets->fetch_assoc())
{
    $_Lang['QuickRes_PlanetList'] .= "<option value=\"{$Planets['id']}\" {sel_planet_{$Planets['id']}}>{$Planets['name']} [{$Planets['galaxy']}:{$Planets['system']}:{$Planets['planet']}]</option>";
}

$Mode = (isset($_GET['mode']) ? $_GET['mode'] : null);

if(!isOnVacation())
{
    if(empty($Mode) OR $Mode == 'general')
    {
        // General View
        $CheckMailChange = doquery("SELECT `ID`, `Date` FROM {{table}} WHERE `UserID` = {$_User['id']} AND `ConfirmType` = 0 LIMIT 1;", 'mailchange', true);

        $Query_IgnoreSystem = '';
        $Query_IgnoreSystem .= "SELECT `ignore`.`IgnoredID`, `user`.`username` FROM {{table}} AS `ignore` ";
        $Query_IgnoreSystem .= "JOIN `{{prefix}}users` AS `user` ON `ignore`.`IgnoredID` = `user`.`id` ";
        $Query_IgnoreSystem .= "WHERE `ignore`.`OwnerID` = {$_User['id']};";

        $SQLResult_IgnoreSystem = doquery($Query_IgnoreSystem, 'ignoresystem');

        if($SQLResult_IgnoreSystem->num_rows > 0)
        {
            while($FetchData = $SQLResult_IgnoreSystem->fetch_assoc())
            {
                $_User['IgnoredUsers'][$FetchData['IgnoredID']] = $FetchData['username'];
            }
        }

        if((isset($_POST['save']) && $_POST['save'] == 'yes') || !empty($_GET['ignoreadd']))
        {
            if($_POST['saveType'] == $_Lang['SaveAll'])
            {
                $_Lang['SetActiveMarker'] = (isset($_POST['markActive']) ? $_POST['markActive'] : null);

                // Settings for Tab01
                if(isset($_POST['change_pass']) && $_POST['change_pass'] == 'on')
                {
                    $_POST['give_newpass'] = trim($_POST['give_newpass']);
                    if(md5($_POST['give_oldpass']) == $_User['password'])
                    {
                        if(strlen($_POST['give_newpass']) >= 4)
                        {
                            if(md5($_POST['give_newpass']) != $_User['password'])
                            {
                                if($_POST['give_newpass'] == $_POST['give_confirmpass'])
                                {
                                    $ChangeSet['password'] = md5($_POST['give_newpass']);
                                    $ChangeSetTypes['password'] = 's';
                                    $InfoMsgs[] = $_Lang['Pass_Changed_plzlogout'];
                                    setcookie(getSessionCookieKey(), '', $Now - 3600, '/', '');
                                }
                                else
                                {
                                    $WarningMsgs[] = $_Lang['Pass_Confirm_isbad'];
                                }
                            }
                            else
                            {
                                $WarningMsgs[] = $_Lang['Pass_same_as_old'];
                            }
                        }
                        else
                        {
                            $WarningMsgs[] = $_Lang['Pass_is_tooshort'];
                        }
                    }
                    else
                    {
                        $WarningMsgs[] = $_Lang['Pass_old_isbad'];
                    }
                }

                if(isset($_POST['change_mail']) && $_POST['change_mail'] == 'on')
                {
                    if($CheckMailChange['ID'] <= 0)
                    {
                        $_POST['give_newemail'] = getDBLink()->escape_string(
                            strip_tags(trim($_POST['give_newemail']))
                        );

                        $CheckMail = $_POST['give_newemail'];
                        $banned_domain_list = $_GameConfig['BannedMailDomains'];
                        $banned_domain_list = str_replace('.', '\.', $banned_domain_list);

                        if(is_email($CheckMail))
                        {
                            if($CheckMail !== $_User['email'])
                            {
                                if($CheckMail === $_POST['give_confirmemail'])
                                {
                                    if(empty($banned_domain_list) || !preg_match('#('.$banned_domain_list.')+#si', $CheckMail))
                                    {
                                        $CheckMailinDB = doquery("SELECT `id` FROM {{table}} WHERE `email` = '{$CheckMail}' LIMIT 1;", 'users', true);
                                        if($CheckMailinDB['id'] <= 0)
                                        {
                                            $RandomHash = md5($_User['id'].$_User['username'].mt_rand(0, 999999999));
                                            $RandomHashNew = md5($_User['id'].$_User['username'].mt_rand(0, 999999999));
                                            $ThisTime = $Now;

                                            $EmailParse = array
                                            (
                                                'EP_User' => $_User['username'],
                                                'EP_GameLink' => GAMEURL_STRICT,
                                                'EP_Link' => GAMEURL."email_change.php?hash=old&amp;key={$RandomHash}",
                                                'EP_Text' => $_Lang['Email_MailOld'],
                                                'EP_OldMail' => $_User['email'],
                                                'EP_NewMail' => $CheckMail,
                                                'EP_Date' => date('d.m.Y - H:i:s', $ThisTime),
                                                'EP_IP' => $_User['user_lastip'],
                                                'EP_ContactLink' => GAMEURL_STRICT.'/contact.php',
                                                'EP_Text2' => $_Lang['Email_WarnOld']
                                            );
                                            $EmailParseNew = array
                                            (
                                                'EP_User' => $_User['username'],
                                                'EP_GameLink' => GAMEURL_STRICT,
                                                'EP_Link' => GAMEURL."email_change.php?hash=new&amp;key={$RandomHashNew}",
                                                'EP_Text' => $_Lang['Email_MailNew'],
                                                'EP_OldMail' => $_User['email'],
                                                'EP_NewMail' => $CheckMail,
                                                'EP_Date' => date('d.m.Y - H:i:s', $ThisTime),
                                                'EP_IP' => $_User['user_lastip'],
                                                'EP_ContactLink' => GAMEURL_STRICT.'/contact.php',
                                                'EP_Text2' => $_Lang['Email_WarnNew']
                                            );

                                            include($_EnginePath.'includes/functions/SendMail.php');
                                            $EmailBody = parsetemplate($_Lang['Email_Body'], $EmailParse);
                                            $EmailBodyNew = parsetemplate($_Lang['Email_Body'], $EmailParseNew);
                                            $SendResult = SendMail($_User['email'], $_Lang['Email_Title'], $EmailBody, '', true);
                                            $SendResult2 = SendMail($CheckMail, $_Lang['Email_Title'], $EmailBodyNew);
                                            CloseMailConnection();

                                            if($SendResult === TRUE AND $SendResult2 === TRUE)
                                            {
                                                $ChangeSet['email_2'] = $CheckMail;
                                                $ChangeSetTypes['email_2'] = 's';

                                                doquery("INSERT INTO {{table}} VALUES (NULL, {$ThisTime}, {$_User['id']}, '{$_User['email']}', '{$CheckMail}', 0, 0, '{$RandomHash}', '{$RandomHashNew}');", 'mailchange');
                                                $CheckMailChange = array('ID' => 1, 'Date' => $ThisTime);
                                                $InfoMsgs[] = sprintf($_Lang['Mail_MailChange'], $_User['email']);
                                            }
                                            else
                                            {
                                                $WarningMsgs[] = sprintf($_Lang['Mail_SendMailError'], urlencode(str_pad(mt_rand(0,999), 3, 'a', STR_PAD_RIGHT).base64_encode($SendResult).'||'.base64_encode($SendResult2)));
                                            }
                                        }
                                        else
                                        {
                                            $WarningMsgs[] = $_Lang['Mail_some1_hasemail'];
                                        }
                                    }
                                    else
                                    {
                                        $WarningMsgs[] = $_Lang['Mail_banned_domain'];
                                    }
                                }
                                else
                                {
                                    $WarningMsgs[] = $_Lang['Mail_Confirm_isbad'];
                                }
                            }
                            else
                            {
                                $WarningMsgs[] = $_Lang['Mail_same_as_old'];
                            }
                        }
                        else
                        {
                            $WarningMsgs[] = $_Lang['Mail_badEmail'];
                        }
                    }
                    else
                    {
                        $WarningMsgs[] = $_Lang['Mail_alreadyInChange'];
                    }
                }

                if(isset($_POST['stop_email_change']) && $_POST['stop_email_change'] == 'on')
                {
                    if($CheckMailChange['ID'] > 0)
                    {
                        doquery("UPDATE {{table}} SET `ConfirmType` = 3 WHERE `ID` = {$CheckMailChange['ID']} LIMIT 1;", 'mailchange');
                        unset($CheckMailChange);
                        $InfoMsgs[] = $_Lang['Mail_PrcStopped'];
                    }
                    else
                    {
                        $WarningMsgs[] = $_Lang['Mail_PrcNoExists'];
                    }
                }

                if(isset($_POST['ipcheck_deactivate']) && $_POST['ipcheck_deactivate'] == 'on')
                {
                    $IPCheckDeactivate = '1';
                }
                else
                {
                    $IPCheckDeactivate = '0';
                }
                if($_User['noipcheck'] != $IPCheckDeactivate)
                {
                    $ChangeSet['noipcheck'] = $IPCheckDeactivate;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                // Settings for Tab02
                $PlanetSortMode = intval($_POST['planet_sort_mode']);
                if($PlanetSortMode <= 0 OR $PlanetSortMode > 2)
                {
                    $PlanetSortMode = '0';
                }
                if($PlanetSortMode != $_User['planet_sort'])
                {
                    $ChangeSet['planet_sort'] = $PlanetSortMode;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                $PlanetSortType = intval($_POST['planet_sort_type']);
                if($PlanetSortType != 1)
                {
                    $PlanetSortType = '0';
                }
                if($PlanetSortType != $_User['planet_sort_order'])
                {
                    $ChangeSet['planet_sort_order'] = $PlanetSortType;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['planet_sort_moons']) && $_POST['planet_sort_moons'] == 'on')
                {
                    $PlanetSortMoons = '1';
                }
                else
                {
                    $PlanetSortMoons = '0';
                }

                if($PlanetSortMoons != $_User['planet_sort_moons'])
                {
                    $ChangeSet['planet_sort_moons'] = $PlanetSortMoons;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                $uservals_gamelanguage = $_User['lang'];
                if (isset($_POST['lang'])) {
                    if (in_array($_POST['lang'], UNIENGINE_LANGS_AVAILABLE)) {
                        $uservals_gamelanguage = $_POST['lang'];
                    } else {
                        $WarningMsgs[] = $_Lang['Lang_UnavailableLang'];
                    }
                }
                if ($uservals_gamelanguage != $_User['lang']) {
                    $ChangeSet['lang'] = $uservals_gamelanguage;
                    $ChangeSetTypes['lang'] = 's';
                } else {
                    $ChangeNotDone += 1;
                }

                $SkinPath = getDBLink()->escape_string(
                    strip_tags(trim($_POST['skin_path']))
                );

                if(strstr($SkinPath, 'http://') === FALSE AND strstr($SkinPath, 'www.') === FALSE)
                {
                    if($SkinPath != '')
                    {
                        $SkinPath = ltrim($SkinPath, '/');
                        if(substr($SkinPath, strlen($SkinPath) - 1) != '/')
                        {
                            $SkinPath .= '/';
                        }
                        if(!file_exists('./'.$SkinPath.'formate.css'))
                        {
                            $WarningMsgs[] = $_Lang['Skin_NoLocalSkin'];
                            $SkinPath = $_User['skinpath'];
                        }
                    }
                    else
                    {
                        $_POST['use_skin'] = '';
                    }
                }
                else
                {
                    if(strstr($SkinPath, 'http://') === FALSE AND strstr($SkinPath, 'www.') !== FALSE)
                    {
                        $SkinPath = str_replace('www.', 'http://', $SkinPath);
                    }
                }
                if($SkinPath != $_User['skinpath'])
                {
                    $_SkinPath = $SkinPath;
                    $ChangeSet['skinpath'] = $SkinPath;
                    $_Lang['skinpath'] = $SkinPath;
                    $ChangeSetTypes['skinpath'] = 's';
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['use_skin']) && $_POST['use_skin'] == 'on')
                {
                    $UseSkin = '1';
                }
                else
                {
                    $UseSkin = '0';
                }
                if($UseSkin != $_User['design'])
                {
                    $ChangeSet['design'] = $UseSkin;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                $AvatarPath = getDBLink()->escape_string(
                    strip_tags(trim($_POST['avatar_path']))
                );

                if(strstr($AvatarPath, 'http://') === FALSE AND strstr($AvatarPath, 'www.') !== FALSE)
                {
                    $AvatarPath = str_replace('www.', 'http://', $AvatarPath);
                }
                if($AvatarPath != $_User['avatar'])
                {
                    $ChangeSet['avatar'] = $AvatarPath;
                    $ChangeSetTypes['avatar'] = 's';
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['development_old']) && $_POST['development_old'] == 'on')
                {
                    $DevelopmentOld = '1';
                }
                else
                {
                    $DevelopmentOld = '0';
                }
                if($DevelopmentOld != $_User['settings_DevelopmentOld'])
                {
                    $ChangeSet['settings_DevelopmentOld'] = $DevelopmentOld;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['build_expandedview_use']) && $_POST['build_expandedview_use'] == 'on')
                {
                    $BuildExpandedView = '1';
                }
                else
                {
                    $BuildExpandedView = '0';
                }
                if($BuildExpandedView != $_User['settings_ExpandedBuildView'])
                {
                    $ChangeSet['settings_ExpandedBuildView'] = $BuildExpandedView;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['pretty_fleet_use']) && $_POST['pretty_fleet_use'] == 'on')
                {
                    $UsePrettyFleet = '1';
                }
                else
                {
                    $UsePrettyFleet = '0';
                }
                if($UsePrettyFleet != $_User['settings_useprettyinputbox'])
                {
                    $ChangeSet['settings_useprettyinputbox'] = $UsePrettyFleet;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                $ChangeNotDone += 1;
                if(isset($_POST['resSort_changed']) && $_POST['resSort_changed'] == '1')
                {
                    $_POST['resSort_array'] = (isset($_POST['resSort_array']) ? trim($_POST['resSort_array']) : null);
                    if(preg_match('/^[a-z]{3}\,[a-z]{3}\,[a-z]{3}$/D', $_POST['resSort_array']))
                    {
                        $CheckData = explode(',', $_POST['resSort_array']);
                        foreach($CheckData as $Data2Check)
                        {
                            if($Data2Check != 'met' AND $Data2Check != 'cry' AND $Data2Check != 'deu')
                            {
                                $DisAllowResSort_Change = true;
                                break;
                            }
                        }
                        if(!isset($DisAllowResSort_Change))
                        {
                            $ChangeSet['settings_resSortArray'] = $_POST['resSort_array'];
                            $ChangeSetTypes['settings_resSortArray'] = 's';
                            $ChangeNotDone -= 1;
                        }
                    }
                }

                $ChangeNotDone += 1;
                if($_User['settings_mainPlanetID'] != $_POST['quickres_select'])
                {
                    $PlanetID = round(floatval($_POST['quickres_select']));
                    if($PlanetID > 0)
                    {
                        $CheckPlanetExist = doquery("SELECT `id_owner` FROM {{table}} WHERE `id` = {$PlanetID} LIMIT 1;", 'planets', true);
                        if($CheckPlanetExist['id_owner'] == $_User['id'])
                        {
                            $ChangeSet['settings_mainPlanetID'] = $PlanetID;
                            $ChangeNotDone -= 1;
                        }
                        else
                        {
                            $WarningMsgs[] = $_Lang['Msgs_PlanetNotYour'];
                        }
                    }
                }

                $MsgsPerPage = intval($_POST['msg_perpage']);
                if($MsgsPerPage != $_User['settings_msgperpage'])
                {
                    if(in_array($MsgsPerPage, array(5, 10, 15, 20, 25, 50, 75, 100, 150, 200)))
                    {
                        $ChangeSet['settings_msgperpage'] = $MsgsPerPage;
                    }
                    else
                    {
                        $WarningMsgs[] = $_Lang['Msgs_BadPerPageInt'];
                        $ChangeNotDone += 1;
                    }
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['msg_spyexpand']) && $_POST['msg_spyexpand'] == 'on')
                {
                    $MsgExpandSpyReports = '1';
                }
                else
                {
                    $MsgExpandSpyReports = '0';
                }
                if($MsgExpandSpyReports != $_User['settings_spyexpand'])
                {
                    $ChangeSet['settings_spyexpand'] = $MsgExpandSpyReports;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['msg_usethreads']) && $_POST['msg_usethreads'] == 'on')
                {
                    $MsgUseMsgThreads = '1';
                }
                else
                {
                    $MsgUseMsgThreads = '0';
                }
                if($MsgUseMsgThreads != $_User['settings_UseMsgThreads'])
                {
                    $ChangeSet['settings_UseMsgThreads'] = $MsgUseMsgThreads;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                // Settings for Tab03
                $SpyCount = intval($_POST['spy_count']);
                if($SpyCount < 1)
                {
                    $SpyCount = 1;
                }
                else if($SpyCount > $MaxEspionageProbesCount)
                {
                    $SpyCount = $MaxEspionageProbesCount;
                }
                if($SpyCount != $_User['settings_spyprobescount'])
                {
                    $ChangeSet['settings_spyprobescount'] = $SpyCount;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['use_ajaxgalaxy']) && $_POST['use_ajaxgalaxy'] == 'on')
                {
                    $UseAJAXGalaxy = '1';
                }
                else
                {
                    $UseAJAXGalaxy = '0';
                }
                if($UseAJAXGalaxy != $_User['settings_UseAJAXGalaxy'])
                {
                    $ChangeSet['settings_UseAJAXGalaxy'] = $UseAJAXGalaxy;
                }
                else
                {
                    $ChangeNotDone += 1;
                }
                if(isset($_POST['show_useravatars']) && $_POST['show_useravatars'] == 'on')
                {
                    $ShowUserAvatars = '1';
                }
                else
                {
                    $ShowUserAvatars = '0';
                }
                if($ShowUserAvatars != $_User['settings_Galaxy_ShowUserAvatars'])
                {
                    $ChangeSet['settings_Galaxy_ShowUserAvatars'] = $ShowUserAvatars;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['short_spy']) && $_POST['short_spy'] == 'on')
                {
                    $ShortcutSpy = '1';
                }
                else
                {
                    $ShortcutSpy = '0';
                }
                if($ShortcutSpy != $_User['settings_esp'])
                {
                    $ChangeSet['settings_esp'] = $ShortcutSpy;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['short_write']) && $_POST['short_write'] == 'on')
                {
                    $ShortcutWrite = '1';
                }
                else
                {
                    $ShortcutWrite = '0';
                }
                if($ShortcutWrite != $_User['settings_wri'])
                {
                    $ChangeSet['settings_wri'] = $ShortcutWrite;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['short_buddy']) && $_POST['short_buddy'] == 'on')
                {
                    $ShortcutBuddy = '1';
                }
                else
                {
                    $ShortcutBuddy = '0';
                }
                if($ShortcutBuddy != $_User['settings_bud'])
                {
                    $ChangeSet['settings_bud'] = $ShortcutBuddy;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                if(isset($_POST['short_rocket']) && $_POST['short_rocket'] == 'on')
                {
                    $ShortcutRocket = '1';
                }
                else
                {
                    $ShortcutRocket = '0';
                }
                if($ShortcutRocket != $_User['settings_mis'])
                {
                    $ChangeSet['settings_mis'] = $ShortcutRocket;
                }
                else
                {
                    $ChangeNotDone += 1;
                }

                // Settings for Tab04
                $_Lang['SetActiveMarker'] = '04';
                if((isset($_POST['vacation_activate']) && $_POST['vacation_activate'] == 'on') || (isset($_POST['delete_activate']) && $_POST['delete_activate'] == 'on'))
                {
                    if(isset($_POST['vacation_activate']) && $_POST['vacation_activate'] == 'on')
                    {
                        $allowVacation = true;
                        if(($_User['vacation_leavetime'] + TIME_DAY) >= $Now)
                        {
                            $allowVacation = false;
                            $WarningMsgs[] = $_Lang['Vacation_24hNotPassed'];
                        }

                        $checkFleets = doquery("SELECT COUNT(*) AS `Count` FROM {{table}} WHERE `fleet_owner` = {$_User['id']} OR `fleet_target_owner` = {$_User['id']};", 'fleets', true);
                        if($checkFleets['Count'] > 0)
                        {
                            $allowVacation = false;
                            $WarningMsgs[] = $_Lang['Vacation_FlyingFleets'];
                        }

                        if($allowVacation === true)
                        {
                            // Update All Planets/Moons before VacationMode (don't do that if previous conditions are not fulfilled)
                            $SQLResult_AllPlanets = doquery("SELECT * FROM {{table}} WHERE `id_owner` = {$_User['id']};", 'planets');

                            $Results['planets'] = array();
                            while($PlanetsData = $SQLResult_AllPlanets->fetch_assoc())
                            {
                                // Update Planet - Building Queue
                                $GeneratePlanetName[$PlanetsData['id']] = "{$PlanetsData['name']} [{$PlanetsData['galaxy']}:{$PlanetsData['system']}:{$PlanetsData['planet']}]";

                                if(HandlePlanetUpdate($PlanetsData, $_User, $Now, true) === true)
                                {
                                    $Results['planets'][] = $PlanetsData;
                                }
                                if($PlanetsData['buildQueue_firstEndTime'] > 0 OR $PlanetsData['shipyardQueue'] != 0)
                                {
                                    $FoundBlockingPlanets[$PlanetsData['id']] = $GeneratePlanetName[$PlanetsData['id']];
                                }
                            }
                            HandlePlanetUpdate_MultiUpdate($Results, $_User);
                            if($_User['techQueue_EndTime'] > 0)
                            {
                                $FoundBlockingPlanets[$PlanetsData['id']] = $GeneratePlanetName[$_User['techQueue_Planet']];
                            }

                            if(!empty($FoundBlockingPlanets))
                            {
                                $allowVacation = false;
                                $WarningMsgs[] = sprintf($_Lang['Vacation_CannotBuildOrRes'], implode(', ', $FoundBlockingPlanets));
                            }
                        }

                        if($allowVacation === true)
                        {
                            $ChangeSet['is_onvacation'] = '1';
                            $ChangeSet['vacation_starttime'] = $Now;
                            $ChangeSet['vacation_endtime'] = $Now + (MAXVACATIONS_REG * TIME_DAY);
                            $ChangeSet['vacation_type']    = '0';

                            $ChangeSetCount += 1;
                            $ForceGoingOnVacationMsg = true;

                            $UserDev_Log[] = array('PlanetID' => '0', 'Date' => $Now, 'Place' => 26, 'Code' => '1', 'ElementID' => '0');
                        }
                    }
                    if(isset($_POST['delete_activate']) && $_POST['delete_activate'] == 'on')
                    {
                        if($_User['is_ondeletion'] == 0)
                        {
                            if(md5($_POST['delete_confirm']) == $_User['password'])
                            {
                                $ChangeSet['is_ondeletion'] = '1';
                                $ChangeSet['deletion_endtime'] = $Now + (ACCOUNT_DELETION_TIME * TIME_DAY);
                                $ChangeSetCount += 1;

                                if($ForceGoingOnVacationMsg === true)
                                {
                                    $ShowDeletionInfo = true;
                                }
                            }
                            else
                            {
                                $WarningMsgs[] = $_Lang['Delete_Confirm_isbad'];
                            }
                        }
                    }
                }
                else
                {
                    if($_User['is_ondeletion'] == 1)
                    {
                        $ChangeSet['is_ondeletion'] = '0';
                        $ChangeSet['deletion_endtime'] = '0';
                        $ChangeSetCount += 1;
                    }
                }

                if(empty($_User['settings_FleetColors']))
                {
                    $_User['settings_FleetColors'] = [];

                    foreach($_Vars_FleetMissions['all'] as $MissionID)
                    {
                        $_User['settings_FleetColors']['ownfly'][$MissionID] = '';
                        $_User['settings_FleetColors']['owncb'][$MissionID] = '';
                        $_User['settings_FleetColors']['nonown'][$MissionID] = '';
                    }
                    $FleetColors_UserVar = $_User['settings_FleetColors'];
                    $_User['settings_FleetColors'] = null;
                }
                else
                {
                    $FleetColors_UserVar = json_decode($_User['settings_FleetColors'], true);
                }
                $FleetColors_NeedChange = false;
                foreach($FleetColors_UserVar as $TypeKey => $Missions)
                {
                    foreach($Missions as $MissionID => $MissionColor)
                    {
                        if(!empty($_POST['fleetColors'][$TypeKey][$MissionID]) AND preg_match(REGEXP_HEXCOLOR, $_POST['fleetColors'][$TypeKey][$MissionID]))
                        {
                            $ThisColor = $_POST['fleetColors'][$TypeKey][$MissionID];
                        }
                        else
                        {
                            $ThisColor = '';
                        }
                        if($ThisColor != $FleetColors_UserVar[$TypeKey][$MissionID])
                        {
                            $FleetColors_NeedChange = true;
                            $FleetColors_UserVar[$TypeKey][$MissionID] = $ThisColor;
                        }
                    }
                }
                if($FleetColors_NeedChange === true)
                {
                    $ChangeSet['settings_FleetColors'] = getDBLink()->escape_string(
                        json_encode($FleetColors_UserVar)
                    );
                    $ChangeSetTypes['settings_FleetColors'] = 's';
                }

                if(!empty($_POST['fleetColors']))
                {
                    foreach($_POST['fleetColors'] as $Key => $Values)
                    {
                        if(!empty($Values))
                        {
                            foreach($Values as $MissionID => $MissionColor)
                            {
                                if(!empty($MissionColor) AND preg_match(REGEXP_HEXCOLOR, $MissionColor))
                                {

                                }
                            }
                        }
                    }
                }
            }
            else if(!empty($_GET['ignoreadd']) || $_POST['saveType'] == 'ignore' || $_POST['saveType'] == 'delignore')
            {
                // Settings for Tab05 (IgnoreList Management)
                $DontShow_NoChanges = true;
                if(!empty($_GET['ignoreadd']))
                {
                    $_Lang['SetActiveMarker'] = '05';
                }

                if($_POST['saveType'] == 'delignore')
                {
                    if(!empty($_POST['del_ignore']) && (array)$_POST['del_ignore'] === $_POST['del_ignore'])
                    {
                        foreach($_POST['del_ignore'] as $Values)
                        {
                            $Values = intval($Values);
                            if($Values > 0)
                            {
                                $ToDelete[] = $Values;
                            }
                        }
                    }
                    if(!empty($ToDelete))
                    {
                        foreach($ToDelete as $ThisID)
                        {
                            if(!empty($_User['IgnoredUsers'][$ThisID]))
                            {
                                $IgnoreSystem_Deleted[] = $ThisID;
                                unset($_User['IgnoredUsers'][$ThisID]);
                            }
                        }

                        if(!empty($IgnoreSystem_Deleted))
                        {
                            $IgnoreSystem_Deleted_Count = count($IgnoreSystem_Deleted);
                            $IgnoreSystem_Deleted = implode(',', $IgnoreSystem_Deleted);

                            $Query_DeleteIgnores = '';
                            $Query_DeleteIgnores .= "DELETE FROM {{table}} WHERE ";
                            $Query_DeleteIgnores .= "`OwnerID` = {$_User['id']} AND `IgnoredID` IN ({$IgnoreSystem_Deleted}) LIMIT {$IgnoreSystem_Deleted_Count};";
                            doquery($Query_DeleteIgnores, 'ignoresystem');

                            $InfoMsgs[] = sprintf($_Lang['Ignore_DeletedXUsers'], $IgnoreSystem_Deleted_Count);
                        }
                        else
                        {
                            $WarningMsgs[] = $_Lang['Ignore_NothingDeleted'];
                        }
                    }
                    else
                    {
                        $WarningMsgs[] = $_Lang['Ignore_NothingSelected'];
                    }
                }
                else if(!empty($_POST['ignore_username']) OR !empty($_GET['ignoreadd']))
                {
                    if(!empty($_GET['ignoreadd']) AND empty($_POST['ignore_username']))
                    {
                        $IgnoreUser = intval($_GET['ignoreadd']);
                        $InputType = 'id';
                    }
                    else
                    {
                        $IgnoreUser = (isset($_POST['ignore_username']) ? trim($_POST['ignore_username']) : null);
                        $InputType = 'un';
                    }
                    if((strtolower($IgnoreUser) != strtolower($_User['username']) AND $InputType == 'un') OR ($IgnoreUser != $_User['id'] AND $InputType == 'id'))
                    {
                        if((preg_match(REGEXP_USERNAME_ABSOLUTE, $IgnoreUser) AND $InputType == 'un') OR ($IgnoreUser > 0 AND $InputType == 'id'))
                        {
                            $Query_CheckUser = '';
                            $Query_CheckUser .= "SELECT `id`, `username`, `authlevel` FROM {{table}} ";
                            $Query_CheckUser .= "WHERE ";
                            if($InputType == 'un')
                            {
                                $Query_CheckUser .= "`username` = '{$IgnoreUser}'";
                            }
                            else
                            {
                                $Query_CheckUser .= "`id` = {$IgnoreUser}";
                            }
                            $Query_CheckUser .= " LIMIT 1; -- settings.php|IgnoreSystem|CheckUser";
                            $Result_CheckUser = doquery($Query_CheckUser, 'users', true);
                            if($Result_CheckUser['id'] > 0)
                            {
                                if(!CheckAuth('user', AUTHCHECK_HIGHER, $Result_CheckUser))
                                {
                                    if(empty($_User['IgnoredUsers'][$Result_CheckUser['id']]))
                                    {
                                        $_User['IgnoredUsers'][$Result_CheckUser['id']] = $Result_CheckUser['username'];
                                        doquery("INSERT INTO {{table}} (`OwnerID`, `IgnoredID`) VALUES ({$_User['id']}, {$Result_CheckUser['id']}); -- settings.php|IgnoreSystem|Insert", 'ignoresystem');
                                        $InfoMsgs[] = $_Lang['Ignore_UserAdded'];
                                    }
                                    else
                                    {
                                        $NoticeMsgs[] = $_Lang['Ignore_ThisUserAlreadyIgnored'];
                                    }
                                }
                                else
                                {
                                    $WarningMsgs[] = $_Lang['Ignore_CannotIgnoreGameTeam'];
                                }
                            }
                            else
                            {
                                $WarningMsgs[] = $_Lang['Ignore_UserNoExists'];
                            }
                        }
                        else
                        {
                            $WarningMsgs[] = $_Lang['Ignore_BadSignsOrShort'];
                        }
                    }
                    else
                    {
                        $WarningMsgs[] = $_Lang['Ignore_CannotIgnoreYourself'];
                    }
                }
            }

            if(!empty($ChangeSet))
            {
                $UpdateQuery = [];

                foreach($ChangeSet as $Key => $Value)
                {
                    $_User[$Key] = $Value;

                    if(isset($ChangeSetTypes[$Key]) && $ChangeSetTypes[$Key] == 's')
                    {
                        $Value = "'{$Value}'";
                    }

                    $UpdateQuery[] = "`{$Key}` = {$Value}";
                }

                doquery("UPDATE {{table}} SET ".implode(', ', $UpdateQuery)." WHERE `id` = {$_User['id']};", 'users');
                if($ForceGoingOnVacationMsg === TRUE)
                {
                    message((isset($ShowDeletionInfo) ? $_Lang['Vacation_GoingOnVacationsWithDeletion'] : $_Lang['Vacation_GoingOnVacations']), $_Lang['Vacations_Title'], 'settings.php', 3);
                }

                $ChangeSetCounted = count($ChangeSet) - $ChangeSetCount;
                if($ChangeSetCounted > 0)
                {
                    $InfoMsgs[] = sprintf($_Lang['Info_SaveWellDone'], $ChangeSetCounted);
                }
                else
                {
                    $NoticeMsgs[] = $_Lang['Info_NoChanges'];
                }
            }
            else
            {
                if(!isset($DontShow_NoChanges) || $DontShow_NoChanges !== true)
                {
                    $NoticeMsgs[] = $_Lang['Info_NoChanges'];
                }
            }
        }

        if(!empty($WarningMsgs))
        {
            foreach($WarningMsgs as $Message)
            {
                $_Lang['InfoBoxMsgs'][] = "<li class=\"red\">{$Message}</li>";
            }
        }
        if(!empty($NoticeMsgs))
        {
            foreach($NoticeMsgs as $Message)
            {
                $_Lang['InfoBoxMsgs'][] = "<li class=\"orange\">{$Message}</li>";
            }
        }
        if(!empty($InfoMsgs))
        {
            foreach($InfoMsgs as $Message)
            {
                $_Lang['InfoBoxMsgs'][] = "<li class=\"lime\">{$Message}</li>";
            }
        }

        if(empty($_Lang['InfoBoxMsgs']))
        {
            $_Lang['InfoBoxShow'] = 'display: none;';
            $_Lang['InfoBoxMsgs'] = "<li></li>";
        }
        else
        {
            $_Lang['InfoBoxMsgs'] = implode('', $_Lang['InfoBoxMsgs']);
        }

        if($_User['noipcheck'] == 1)
        {
            $_Lang['ipcheck_deactivate_check'] = 'checked';
        }
        if($_User['planet_sort'] == 0)
        {
            $_Lang['planet_sort_mode_0'] = 'selected';
        }
        if($_User['planet_sort'] == 1)
        {
            $_Lang['planet_sort_mode_1'] = 'selected';
        }
        if($_User['planet_sort'] == 2)
        {
            $_Lang['planet_sort_mode_2'] = 'selected';
        }
        if($_User['planet_sort_order'] == 0)
        {
            $_Lang['planet_sort_type_asc'] = 'selected';
        }
        if($_User['planet_sort_order'] == 1)
        {
            $_Lang['planet_sort_type_desc'] = 'selected';
        }
        if($_User['planet_sort_moons'] == 1)
        {
            $_Lang['planet_sort_moons_check'] = 'checked';
        }
        $_Lang['skin_path'] = $_User['skinpath'];
        if($_User['design'] == 1)
        {
            $_Lang['use_skin_check'] = 'checked';
        }
        $_Lang['avatar_path'] = $_User['avatar'];
        if($_User['settings_DevelopmentOld'] == 1)
        {
            $_Lang['development_old_check'] = 'checked';
        }
        if($_User['settings_ExpandedBuildView'] == 1)
        {
            $_Lang['build_expandedview_use_check'] = 'checked';
        }

        if($_User['settings_useprettyinputbox'] == 1)
        {
            $_Lang['pretty_fleet_use_check'] = 'checked';
        }

        $_Lang['OldResSort_ArrayString'] = $_User['settings_resSortArray'];
        $ExplodeOldResSort = explode(',', $_User['settings_resSortArray']);
        foreach($ExplodeOldResSort as $ResKey => $ResData)
        {
            $ResKey += 1;
            $_Lang['CreateResSortList'] .= "<li id=\"resSort_{$ResData}\"><b id=\"resSort_{$ResData}Num\">{$ResKey}</b>. {$_Lang['ResSort_Resources'][$ResData]}</li>";
        }

        $_Lang['msg_perpage_sel_'.$_User['settings_msgperpage']] = 'selected';
        if($_User['settings_spyexpand'] == 1)
        {
            $_Lang['msg_spyexpand_check'] = 'checked';
        }
        if($_User['settings_UseMsgThreads'] == 1)
        {
            $_Lang['msg_usethreads_check'] = 'checked';
        }
        $_Lang['spy_count'] = $_User['settings_spyprobescount'];
        if($_User['settings_UseAJAXGalaxy'] == 1)
        {
            $_Lang['use_ajaxgalaxy_check'] = 'checked';
        }
        if($_User['settings_Galaxy_ShowUserAvatars'] == 1)
        {
            $_Lang['show_useravatars_check'] = 'checked';
        }
        if($_User['settings_esp'] == 1)
        {
            $_Lang['short_spy_check'] = 'checked';
        }
        if($_User['settings_wri'] == 1)
        {
            $_Lang['short_write_check'] = 'checked';
        }
        if($_User['settings_bud'] == 1)
        {
            $_Lang['short_buddy_check'] = 'checked';
        }
        if($_User['settings_mis'] == 1)
        {
            $_Lang['short_rocket_check'] = 'checked';
        }
        if($_User['is_ondeletion'] == 1)
        {
            $_Lang['delete_active_color'] = 'red';
            $_Lang['delete_activate_check'] = 'checked';
            $_Lang['DeleteMsg'] = sprintf($_Lang['DeleteMsg'], date('d.m.Y H:i:s', intval($_User['deletion_endtime'])));
            $_Lang['DeleteAccount'] = $_Lang['DeleteAccountOff'];
            $_Lang['DeleteConfirmShow'] = 'style="display: none;"';
        }
        else
        {
            $_Lang['DontShowDeleteWarning'] = 'display: none;';
            $_Lang['DeleteClickToRemoveShow'] = 'style="display: none;"';
            $_Lang['DeleteMsg'] = '';
        }
        if(strstr($_User['skinpath'], 'http://') === FALSE)
        {
            foreach($AvailableSkins as $Key => $Skin)
            {
                if(strstr($_User['skinpath'], $Skin))
                {
                    $_Lang['ServerSkins'] = str_replace('{select_no'.$Key.'}', 'selected', $_Lang['ServerSkins']);
                }
                else
                {
                    $_Lang['ServerSkins'] = str_replace('{select_no'.$Key.'}', '', $_Lang['ServerSkins']);
                }
            }
        }
        $_Lang['QuickRes_PlanetList'] = str_replace('{sel_planet_'.$_User['settings_mainPlanetID'].'}', 'selected', $_Lang['QuickRes_PlanetList']);
        $_Lang['QuickRes_PlanetList'] = preg_replace('#\{sel\_planet\_[0-9]{1,}\}#si', '', $_Lang['QuickRes_PlanetList']);

        if(!empty($_User['IgnoredUsers']))
        {
            foreach($_User['IgnoredUsers'] as $IgnoredID => $IgnoredName)
            {
                $_Lang['ParseIgnoreList'][] = "<input type=\"checkbox\" name=\"del_ignore[]\" value=\"{$IgnoredID}\" id=\"ignore{$IgnoredID}\" /> <label for=\"ignore{$IgnoredID}\">{$IgnoredName}</label>";
            }
            $_Lang['ParseIgnoreList'] = implode('<br/>', $_Lang['ParseIgnoreList']);
            if(count($_User['IgnoredUsers']) < 15)
            {
                $_Lang['IgnoreList_Hide2Del'] = 'style="display: none;"';
            }
        }
        else
        {
            $_Lang['ParseIgnoreList'] = "<center class=\"red\">{$_Lang['IgnoreList_NoIgnored']}</center>";
            $_Lang['IgnoreList_Hide1Del'] = 'style="display: none;"';
            $_Lang['IgnoreList_Hide2Del'] = 'style="display: none;"';
        }

        if($CheckMailChange['ID'] > 0)
        {
            $_Lang['EMChange1'] = 'style="display: none;"';
            $_Lang['EmailPrc_Active_Since'] = sprintf($_Lang['EmailPrc_Active_Since'], date('d.m.Y H:i:s', $CheckMailChange['Date']));
            $_Lang['OldEmail']= $_Lang['RealOldEmail'];
            $_Lang['EmailPrc_NewEmail'] = $_User['email_2'];
        }
        else
        {
            $_Lang['EMChange2'] = 'style="display: none;"';
        }

        if(empty($_Lang['SetActiveMarker']))
        {
            if(!empty($_GET['tab']))
            {
                if(in_array($_GET['tab'], array(1,2,3,4,5,6)))
                {
                    $_Lang['SetActiveMarker'] = str_pad($_GET['tab'], 2, '0', STR_PAD_LEFT);
                }
            }
        }

        // Logons List
        $Query_GetLogons = '';
        $Query_GetLogons .= "SELECT `Log`.*, `IPTable`.`Value` FROM {{table}} AS `Log` ";
        $Query_GetLogons .= "LEFT JOIN `{{prefix}}used_ip_and_ua` AS `IPTable` ON `Log`.`IP_ID` = `IPTable`.`ID` ";
        $Query_GetLogons .= "WHERE `Log`.`User_ID` = {$_User['id']} ";
        $Query_GetLogons .= "ORDER BY `Log`.`LastTime` DESC LIMIT {$LogonLIMIT};";

        $SQLResult_GetLogons = doquery($Query_GetLogons, 'user_enterlog');

        if($SQLResult_GetLogons->num_rows > 0)
        {
            while($LogonData = $SQLResult_GetLogons->fetch_assoc())
            {
                $LogonData['Times'] = array_reverse(explode(',', $LogonData['Times']));
                $LimitCounter = $LogonLIMIT;
                foreach($LogonData['Times'] as $Temp)
                {
                    if($LimitCounter <= 0)
                    {
                        break;
                    }
                    $Temp = explode('|', $Temp);
                    $ThisTime = SERVER_MAINOPEN_TSTAMP + $Temp[0];
                    $LogonList[] = array('Time' => $ThisTime, 'IP' => $LogonData['Value'], 'State' => (isset($Temp[1]) && $Temp[1] == 'F' ? false : true));
                    $LogonTimes[] = $ThisTime;
                    $LimitCounter -= 1;
                }
            }

            array_multisort($LogonList, SORT_DESC, $LogonTimes);
            $LimitCounter = $LogonLIMIT;
            foreach($LogonList as $LogonData)
            {
                if($LimitCounter <= 0)
                {
                    break;
                }
                if($LogonData['IP'] == $_User['user_lastip'])
                {
                    $LogonData['IPColor'] = 'lime';
                }
                if($LogonData['State'] === false)
                {
                    if($LogonData['IP'] != $_User['user_lastip'])
                    {
                        $LogonData['DateColor'] = 'red';
                        $LogonData['IPColor'] = 'red';
                        $LogonData['StateColor'] = 'red';
                    }
                    else
                    {
                        $LogonData['StateColor'] = 'orange';
                    }
                }


                $ThisRow = '<tr class="logon">';
                $ThisRow .= '<th'.(!empty($LogonData['DateColor']) ? ' class="'.$LogonData['DateColor'].'"' : '').'>'.prettyDate('d m Y, H:i:s', $LogonData['Time'], 1).'</th>';
                $ThisRow .= '<th'.(!empty($LogonData['DateColor']) ? ' class="'.$LogonData['DateColor'].'"' : '').'>'.pretty_time($Now - $LogonData['Time'], true, 'D').' '.$_Lang['Logons_ago'].'</th>';
                $ThisRow .= '<th'.(!empty($LogonData['IPColor']) ? ' class="'.$LogonData['IPColor'].'"' : '').'>'.$LogonData['IP'].'</th>';
                $ThisRow .= '<th'.(!empty($LogonData['StateColor']) ? ' class="'.$LogonData['StateColor'].'"' : '').'>'.($LogonData['State'] === true ? $_Lang['Logons_Success'] : $_Lang['Logons_Failed']).'</th>';
                $ThisRow .= '</tr>';
                $_Lang['ParseLogonsList'][] = $ThisRow;
                $LimitCounter -= 1;
            }
            $_Lang['ParseLogonsList'] = implode('', $_Lang['ParseLogonsList']);
        }
        else
        {
            $_Lang['ParseLogonsList'] = '<tr><th colspan="4">'.$_Lang['Logons_ListEmpty'].'</th></tr>';
        }

        // FleetColors - Pickers
        $TPL_FleetColors_Row = gettemplate('settings_fleetcolors_row');
        if(!empty($_User['settings_FleetColors']))
        {
            if(isset($FleetColors_NeedChange) && $FleetColors_NeedChange === true)
            {
                $_User['settings_FleetColors'] = stripslashes($_User['settings_FleetColors']);
            }
            $FleetColors = json_decode($_User['settings_FleetColors'], true);
        }
        foreach($_Vars_FleetMissions['all'] as $MissionID)
        {
            $_Lang['Insert_FleetColors_Pickers'][] = parsetemplate($TPL_FleetColors_Row, array
            (
                'MissionName'        => $_Lang['type_mission'][$MissionID],
                'MissionID'            => $MissionID,
                'Value_OwnFly'        => (isset($FleetColors['ownfly'][$MissionID]) ? $FleetColors['ownfly'][$MissionID] : null),
                'Value_OwnComeback' => (isset($FleetColors['owncb'][$MissionID]) ? $FleetColors['owncb'][$MissionID] : null),
                'Value_NonOwn'        => (isset($FleetColors['nonown'][$MissionID]) ? $FleetColors['nonown'][$MissionID] : null)
            ));
        }
        $_Lang['Insert_FleetColors_Pickers'] = implode('', $_Lang['Insert_FleetColors_Pickers']);

        $BodyTPL = gettemplate('settings_body');
        $Page = parsetemplate($BodyTPL, $_Lang);
        display($Page, $_Lang['Page_Title'], false);
    }
    else if($Mode == 'nickchange')
    {
        // User is trying to change his nickname
        if(!empty($_POST['newnick']))
        {
            // Nickname Change in progress
            if($_User['darkEnergy'] < 10)
            {
                message($_Lang['NewNick_donthave_DE'], $_Lang['NickChange_Title'], 'settings.php?mode=nickchange');
            }
            $NewNick = trim($_POST['newnick']);
            if($NewNick == $_User['username'])
            {
                message($_Lang['NewNick_is_like_old'], $_Lang['NickChange_Title'], 'settings.php?mode=nickchange');
            }
            if(strlen($NewNick) < 4)
            {
                message($_Lang['NewNick_is_tooshort'], $_Lang['NickChange_Title'], 'settings.php?mode=nickchange');
            }
            if(strstr($NewNick, 'http') OR strstr($NewNick, 'www.'))
            {
                message($_Lang['NewNick_nolinks'], $_Lang['NickChange_Title'], 'settings.php?mode=nickchange');
            }
            if(!preg_match(REGEXP_USERNAME_ABSOLUTE, $NewNick))
            {
                message($_Lang['NewNick_badSigns'], $_Lang['NickChange_Title'], 'settings.php?mode=nickchange');
            }
            $SelectNewNick = doquery("SELECT `id` FROM {{table}} WHERE `username` = '{$NewNick}' LIMIT 1;", 'users', true);
            if($SelectNewNick['id'] > 0)
            {
                message($_Lang['NewNick_already_taken'], $_Lang['NickChange_Title'], 'settings.php?mode=nickchange');
            }

            doquery("UPDATE {{table}} SET `darkEnergy` = `darkEnergy` - 10, `username` = '{$NewNick}', `old_username` = '{$_User['username']}', `old_username_expire` = UNIX_TIMESTAMP() + (7*24*60*60) WHERE `id` = {$_User['id']} LIMIT 1;", 'users');
            doquery("INSERT INTO {{table}} VALUES(NULL, {$_User['id']}, UNIX_TIMESTAMP(), '{$NewNick}', '{$_User['username']}');", 'nick_changelog');
            setcookie(getSessionCookieKey(), '', $Now - 3600, '/', '');
            message($_Lang['NewNick_saved'], $_Lang['NickChange_Title'], 'login.php');
        }
        else
        {
            $_Lang['skinpath'] = $_SkinPath;
            $_Lang['DarkEnergy_Counter'] = $_User['darkEnergy'];
            if($_User['darkEnergy'] >= 15)
            {
                $_Lang['DarkEnergy_Color'] = 'lime';
            }
            else if($_User['darkEnergy'] > 0)
            {
                $_Lang['DarkEnergy_Color'] = 'orange';
            }
            else
            {
                $_Lang['DarkEnergy_Color'] = 'red';
            }

            // Informations box
            display(parsetemplate(gettemplate('settings_changenick'), $_Lang), $_Lang['NickChange_Title'], false);
        }
    }
}
else
{
    // User is on Vacation
    if($Mode == 'exit')
    {
        // User is trying to remove Vacation mode
        if(isset($_POST['exit_modus']) && $_POST['exit_modus'] == 'on' && canTakeVacationOff($Now))
        {
            doquery("UPDATE {{table}} SET `is_onvacation` = '0', `vacation_starttime` = '0', `vacation_endtime` = '0', `vacation_leavetime` = IF(`vacation_type` = 2, 0, UNIX_TIMESTAMP()) WHERE `id` = {$_User['id']} LIMIT 1;", 'users');
            doquery("UPDATE {{table}} SET `last_update` = UNIX_TIMESTAMP() WHERE `id_owner` = {$_User['id']}", 'planets');
            $_Planet['last_update'] = $Now;

            $UserDev_Log[] = array('PlanetID' => '0', 'Date' => $Now, 'Place' => 26, 'Code' => '2', 'ElementID' => '0');

            message($_Lang['Vacation_GoOut'], $_Lang['Vacations_Title'], 'overview.php', 3);
        }
        else
        {
            message($_Lang['Vacation_CantGoOut'], $_Lang['Vacations_Title'], 'settings.php', 3);
        }
    }

    includeLang('common_vacationmode');

    if (canTakeVacationOffAnytime()) {
        $_Lang['Parse_Vacation_EndTime'] = $_Lang['VacationMode_EndTime_Anytime'];
    } else {
        $MinimalVacationTime = getUserMinimalVacationTime($_User);
        $MinimalVacationTimeColor = (
            $MinimalVacationTime <= $Now ?
            'lime' :
            'orange'
        );

        $_Lang['Parse_Vacation_EndTime'] = sprintf(
            $_Lang['VacationMode_EndTime_DefinedAs'],
            $MinimalVacationTimeColor,
            prettyDate('d m Y, H:i:s', $MinimalVacationTime, 1)
        );
    }

    display(parsetemplate(gettemplate('settings_vacations'), $_Lang), $_Lang['VacationMode_Title'], false);
}

?>
