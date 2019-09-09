<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

use UniEngine\Engine\Includes\Helpers\Users;

loggedCheck();

$Now = time();

if($_User['first_login'] == 0)
{
    // Show First Login Message
    includeLang('firstlogin');
    $TPL = gettemplate('firstlogin');
    $_DontShowMenus = true;

    $Search = array
    (
        '{GameSpeed}', '{ResSpeed}', '{FleetSpeed}', '{FleetDebris}', '{DefFlDebris}', '{DefMiDebris}',
        '{MotherSize}', '{OpenTime}', '{Protection_NewPlayerTime}', '{Protection_PointsLimit}'
    );
    $Replace = array
    (
        prettyNumber($_GameConfig['game_speed'] / 2500),
        prettyNumber($_GameConfig['resource_multiplier']),
        prettyNumber($_GameConfig['fleet_speed'] / 2500),
        $_GameConfig['Fleet_Cdr'],
        $_GameConfig['Defs_Cdr'],
        $_GameConfig['Debris_Def_Rocket'],
        $_GameConfig['initial_fields'],
        prettyDate('d m Y - H:i:s', SERVER_MAINOPEN_TSTAMP, 1),
        prettyNumber($_GameConfig['Protection_NewPlayerTime'] / 3600),
        prettyNumber($_GameConfig['no_noob_protect'] * 1000)
    );

    $_Lang['LoginPage_Text'] = str_replace($Search, $Replace, $_Lang['LoginPage_Text']);

    doquery("INSERT IGNORE INTO {{table}} VALUES (0, {$_User['id']}, {$Now});", 'chat_online');
    doquery("UPDATE {{table}} SET `last_update` = {$Now} WHERE `id_owner` = {$_User['id']} LIMIT 1;", 'planets');

    $NewUserProtectionTime = $Now + $_GameConfig['Protection_NewPlayerTime'];
    $Query_UpdateUser = '';
    $Query_UpdateUser .= "UPDATE {{table}} SET ";
    $Query_UpdateUser .= "`first_login` = {$Now}, ";
    $Query_UpdateUser .= "`NoobProtection_EndTime` = {$NewUserProtectionTime} ";
    $Query_UpdateUser .= "WHERE `id` = {$_User['id']} LIMIT 1;";
    doquery($Query_UpdateUser, 'users');

    if($_User['referred'] > 0)
    {
        // Update Referrer Tasks
        if(empty($GlobalParsedTasks[$_User['referred']]['tasks_done_parsed']))
        {
            $GetUserTasksDone = doquery("SELECT `id`, `tasks_done` FROM {{table}} WHERE `id` = {$_User['referred']} LIMIT 1;", 'users', true);
            if($GetUserTasksDone['id'] == $_User['referred'])
            {
                unset($GetUserTasksDone['id']);
                Tasks_CheckUservar($GetUserTasksDone);
                $GlobalParsedTasks[$_User['referred']] = $GetUserTasksDone;
                $ThisTaskUser        = $GlobalParsedTasks[$_User['referred']];
                $ThisTaskUser['id'] = $_User['referred'];
            }
        }
        else
        {
            $ThisTaskUser        = $GlobalParsedTasks[$_User['referred']];
            $ThisTaskUser['id'] = $_User['referred'];
        }

        if(!empty($ThisTaskUser))
        {
            Tasks_TriggerTask($ThisTaskUser, 'NEWUSER_REGISTER', array
            (
                'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use (&$ThisTaskUser)
                {
                    $Return = Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                    $ThisTaskUser['TaskData'][] = array
                    (
                        'TaskID' => $TaskID,
                        'TaskStatus' => $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID],
                        'TaskLimit' => $JobArray[$JobArray['statusField']]
                    );
                    return $Return;
                }
            ));
        }

        // Check IP Intersection
        $_Included_AlertSystemUtilities = true;
        include($_EnginePath.'includes/functions/AlertSystemUtilities.php');
        $CheckIntersection = AlertUtils_IPIntersect($_User['id'], $_User['referred'], array
        (
            'LastTimeDiff' => (TIME_DAY * 60),
            'ThisTimeDiff' => (TIME_DAY * 60),
            'ThisTimeStamp' => ($Now - SERVER_MAINOPEN_TSTAMP)
        ));
        if($CheckIntersection !== false)
        {
            $FiltersData = array();
            $FiltersData['place'] = 4;
            $FiltersData['alertsender'] = 4;
            $FiltersData['users'] = array($_User['id'], $_User['referred']);
            $FiltersData['ips'] = $CheckIntersection['Intersect'];
            $FiltersData['newuser'] = $_User['id'];
            $FiltersData['referrer'] = $_User['referred'];
            foreach($CheckIntersection['Intersect'] as $IP)
            {
                $FiltersData['logcount'][$IP][$_User['id']] = $CheckIntersection['IPLogData'][$_User['id']][$IP]['Count'];
                $FiltersData['logcount'][$IP][$_User['referred']] = $CheckIntersection['IPLogData'][$_User['referred']][$IP]['Count'];
            }

            $FilterResult = AlertUtils_CheckFilters($FiltersData, array('Save' => true));
            if($FilterResult['SendAlert'])
            {
                $_Alert['Data']['ReferrerID'] = $_User['referred'];
                foreach($CheckIntersection['Intersect'] as $ThisIPID)
                {
                    $_Alert['Data']['Intersect'][] = array
                    (
                        'IPID' => $ThisIPID,
                        'NewUser' => $CheckIntersection['IPLogData'][$_User['id']][$ThisIPID],
                        'OldUser' => $CheckIntersection['IPLogData'][$_User['referred']][$ThisIPID]
                    );
                }
                if(!empty($ThisTaskUser['TaskData']))
                {
                    $_Alert['Data']['Tasks'] = $ThisTaskUser['TaskData'];
                }

                $Query_AlertOtherUsers .= "SELECT DISTINCT `User_ID` FROM {{table}} WHERE ";
                $Query_AlertOtherUsers .= "`User_ID` NOT IN ({$_User['id']}, {$_User['referred']}) AND ";
                $Query_AlertOtherUsers .= "`IP_ID` IN (".implode(', ', $CheckIntersection['Intersect']).") AND ";
                $Query_AlertOtherUsers .= "`Count` > `FailCount`;";
                $Result_AlertOtherUsers = doquery($Query_AlertOtherUsers, 'user_enterlog');
                if($Result_AlertOtherUsers->num_rows > 0)
                {
                    while($FetchData = $Result_AlertOtherUsers->fetch_assoc())
                    {
                        $_Alert['Data']['OtherUsers'][] = $FetchData['User_ID'];
                    }
                }

                Alerts_Add(4, $Now, 1, 2, 8, $_User['id'], $_Alert['Data']);
            }
        }
    }

    // Check, if this IP is Proxy
    $usersIP = Users\Session\getCurrentIP();
    $IPHash = md5($usersIP);
    $Query_CheckProxy = "SELECT `ID`, `isProxy` FROM {{table}} WHERE `ValueHash` = '{$IPHash}' LIMIT 1;";
    $Result_CheckProxy = doquery($Query_CheckProxy, 'used_ip_and_ua', true);
    if($Result_CheckProxy['ID'] > 0 AND $Result_CheckProxy['isProxy'] == 1)
    {
        if(!isset($_Included_AlertSystemUtilities))
        {
            include($_EnginePath.'includes/functions/AlertSystemUtilities.php');
            $_Included_AlertSystemUtilities = true;
        }
        $FiltersData = array();
        $FiltersData['place'] = 4;
        $FiltersData['alertsender'] = 5;
        $FiltersData['users'] = array($_User['id']);
        $FiltersData['ips'] = array($Result_CheckProxy['ID']);

        $FilterResult = AlertUtils_CheckFilters($FiltersData, array('DontLoad' => true, 'DontLoad_OnlyIfCacheEmpty' => true));
        if($FilterResult['SendAlert'])
        {
            $_Alert['Data']['IPID'] = $Result_CheckProxy['ID'];
            if($usersIP == $_User['ip_at_reg'])
            {
                $_Alert['Data']['RegIP'] = true;
            }

            Alerts_Add(5, $Now, 1, 3, 8, $_User['id'], $_Alert['Data']);
        }
    }

    // Give Free ProAccount for 7 days
    //doquery("INSERT INTO {{table}} VALUES (NULL, {$_User['id']}, UNIX_TIMESTAMP(), 0, 0, 11, 0);", 'premium_free');

    // Create DevLog Dump
    define('IN_USERFIRSTLOGIN', true);
    $InnerUIDSet = $_User['id'];
    $SkipDumpMsg = true;
    include($_EnginePath.'admin/scripts/script.createUserDevDump.php');

    display(parsetemplate($TPL, $_Lang), $_Lang['FirstLogin_Title'], false);
}

$mode = (isset($_GET['mode']) ? $_GET['mode'] : '');

includeLang('resources');
includeLang('overview');

switch($mode)
{
    case 'rename':
        // --- Rename Planet Page ---
        $parse = $_Lang;

        $parse['Rename_Ins_MsgHide'] = 'style="display: none;"';
        $parse['Rename_Ins_MsgTxt'] = false;

        if($_Planet['planet_type'] == 1)
        {
            $parse['Rename_CurrentName'] = sprintf($parse['Rename_CurrentName'], $parse['Rename_Planet']);
        }
        else
        {
            $parse['Rename_CurrentName'] = sprintf($parse['Rename_CurrentName'], $parse['Rename_Moon']);
        }

        if(isset($_POST['action']) && $_POST['action'] == 'do')
        {
            // User wants to change planets name
            $NewName = trim($_POST['set_newname']);
            if(!empty($NewName))
            {
                // Update only, when name is not the same as old
                if($_Planet['name'] != $NewName)
                {
                    // Check if planet new name is correct
                    $NewNameLength = strlen($NewName);
                    if($NewNameLength < 3)
                    {
                        $parse['Rename_Ins_MsgColor'] = 'red';
                        $parse['Rename_Ins_MsgTxt'] = $_Lang['RenamePlanet_TooShort'];
                    }
                    elseif($NewNameLength > 20)
                    {
                        $parse['Rename_Ins_MsgColor'] = 'red';
                        $parse['Rename_Ins_MsgTxt'] = $_Lang['RenamePlanet_TooLong'];
                    }
                    elseif(!preg_match(REGEXP_PLANETNAME_ABSOLUTE, $NewName))
                    {
                        $parse['Rename_Ins_MsgColor'] = 'red';
                        $parse['Rename_Ins_MsgTxt'] = $_Lang['RenamePlanet_BadSigns'];
                    }
                    if($parse['Rename_Ins_MsgTxt'] === false)
                    {
                        //Save the new name in Script Memory
                        $_Planet['name'] = $NewName;
                        //Now save it in DataBase
                        doquery("UPDATE {{table}} SET `name` = '{$NewName}' WHERE `id` = {$_User['current_planet']} LIMIT 1;", 'planets');
                        $parse['Rename_Ins_MsgColor'] = 'lime';
                        $parse['Rename_Ins_MsgTxt'] = $_Lang['RenamePlanet_NameSaved'];
                    }
                }
                else
                {
                    $parse['Rename_Ins_MsgColor'] = 'orange';
                    $parse['Rename_Ins_MsgTxt'] = $_Lang['RenamePlanet_SameName'];
                }
            }
            else
            {
                $parse['Rename_Ins_MsgColor'] = 'red';
                $parse['Rename_Ins_MsgTxt'] = $_Lang['RenamePlanet_0Lenght'];
            }
        }

        if($parse['Rename_Ins_MsgTxt'] !== false)
        {
            $parse['Rename_Ins_MsgHide'] = '';
        }

        $parse['Rename_Ins_CurrentName'] = "{$_Planet['name']} <a href=\"galaxy.php?mode=3&amp;galaxy={$_Planet['galaxy']}&amp;system={$_Planet['system']}&amp;planet={$_Planet['planet']}\">[{$_Planet['galaxy']}:{$_Planet['system']}:{$_Planet['planet']}]</a>";

        $page = parsetemplate(gettemplate('overview_rename'), $parse);
        display($page, $_Lang['Rename_TitleMain']);
        break;
    case 'abandon':
        // --- Abandon Colony ---
        if(isOnVacation())
        {
            message($_Lang['Vacation_WarnMsg'], $_Lang['Vacation']);
        }

        $parse = $_Lang;
        $parse['Abandon_Ins_MsgHide'] = 'style="display: none;"';
        $parse['Abandon_Ins_MsgTxt'] = false;

        if(isset($_POST['action']) && $_POST['action'] == 'do')
        {
            $parse['Abandon_Ins_MsgColor'] = 'red';
            // Check if given password is good
            if(!empty($_POST['give_passwd']))
            {
                if(md5($_POST['give_passwd']) == $_User['password'])
                {
                    if($_User['id_planet'] != $_User['current_planet'])
                    {
                        if($_Planet['planet_type'] == 1 OR $_Planet['planet_type'] == 3)
                        {
                            include($_EnginePath.'includes/functions/DeleteSelectedPlanetorMoon.php');
                            $DeleteResult = DeleteSelectedPlanetorMoon();
                            if($DeleteResult['result'] === true)
                            {
                                // Prevent abandoning Planet to make mission faster
                                Tasks_TriggerTask($_User, 'COLONIZE_PLANET', array
                                (
                                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($_User)
                                    {
                                        global $UserTasksUpdate;
                                        if(!empty($UserTasksUpdate[$_User['id']]['status'][$ThisCat][$TaskID][$JobID]))
                                        {
                                            $_User['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$_User['id']]['status'][$ThisCat][$TaskID][$JobID];
                                        }
                                        if($_User['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] <= 0)
                                        {
                                            return true;
                                        }
                                        $_User['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] -= 1;
                                        $UserTasksUpdate[$_User['id']]['status'][$ThisCat][$TaskID][$JobID] = $_User['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID];
                                        return true;
                                    }
                                ));

                                // User Development Log
                                $UserDev_Log[] = array('PlanetID' => $_Planet['id'], 'Date' => $Now, 'Place' => 25, 'Code' => '0', 'ElementID' => '0');
                                if(count($DeleteResult['ids']) > 1)
                                {
                                    $UserDev_Log[] = array('PlanetID' => $DeleteResult['ids'][1], 'Date' => $Now, 'Place' => 25, 'Code' => '0', 'ElementID' => '0');
                                }

                                header('Location: overview.php?showmsg=abandon');
                                safeDie();
                            }
                            else
                            {
                                if($DeleteResult['reason'] == 'tech')
                                {
                                    $parse['Abandon_Ins_MsgTxt'] = $_Lang['Abandon_TechHere'];
                                }
                                elseif($DeleteResult['reason'] == 'sql')
                                {
                                    $parse['Abandon_Ins_MsgTxt'] = $_Lang['Abandon_SQLError'];
                                }
                                elseif($DeleteResult['reason'] == 'fleet_current')
                                {
                                    $parse['Abandon_Ins_MsgTxt'] = $_Lang['Abandon_FlyingFleetsHere'];
                                }
                                elseif($DeleteResult['reason'] == 'fleet_moon')
                                {
                                    $parse['Abandon_Ins_MsgTxt'] = $_Lang['Abandon_FlyingFleetsMoon'];
                                }
                            }
                        }
                        else
                        {
                            $parse['Abandon_Ins_MsgTxt'] = $_Lang['Abandon_BadPlanetrowData'];
                        }
                    }
                    else
                    {
                        $parse['Abandon_Ins_MsgTxt'] = $_Lang['Abandon_CantAbandonMother'];
                    }
                }
                else
                {
                    $parse['Abandon_Ins_MsgTxt'] = $_Lang['Abandon_BadPassword'];
                }
            }
            else
            {
                $parse['Abandon_Ins_MsgTxt'] = $_Lang['Abandon_NoPassword'];
            }
        }

        if($parse['Abandon_Ins_MsgTxt'] !== false)
        {
            $parse['Abandon_Ins_MsgHide'] = '';
        }
        $parse['Abandon_Desc'] = sprintf($parse['Abandon_Desc'], ($_Planet['planet_type'] == 1 ? $_Lang['Abandon_Planet'] : $_Lang['Abandon_Moon']), $_Planet['name'], "<a class=\"orange\" href=\"galaxy.php?mode=3&amp;galaxy={$_Planet['galaxy']}&amp;system={$_Planet['system']}&amp;planet={$_Planet['planet']}\">[{$_Planet['galaxy']}:{$_Planet['system']}:{$_Planet['planet']}]</a>");
        $parse['Abandon_Ins_Pass'] = $_User['password'];

        $page = parsetemplate(gettemplate('overview_deleteplanet'), $parse);
        display($page, $_Lang['Abandon_TitleMain']);
        break;
    default:
        $parse = &$_Lang;
        include($_EnginePath.'includes/functions/InsertJavaScriptChronoApplet.php');
        InsertJavaScriptChronoApplet(false, false, false);
        $InsertJSChronoApplet_GlobalIncluded = true;

        // --- Vacation Mode Box
        if(isOnVacation())
        {
            $parse['VacationModeBox'] = '<tr><th class="c pad5 orange" colspan="3">'.$_Lang['VacationModeBox_Text'].'</th></tr><tr><th style="visibility: hidden;">&nbsp;</th></tr>';
        }

        // --- Activation Box
        if(!empty($_User['activation_code']))
        {
            $parse['ActivationInfoBox'] = '<tr><th class="c pad5 orange" colspan="3">'.$_Lang['ActivationInfo_Text'].'</th></tr><tr><th style="visibility: hidden;">&nbsp;</th></tr>';
        }

        // --- New User Protection Box
        if($_User['NoobProtection_EndTime'] > $Now)
        {
            if(isset($_GET['cancelprotection']) && $_GET['cancelprotection'] == '1')
            {
                $_User['NoobProtection_EndTime'] = $Now;
                $Query_UpdateUser = "UPDATE {{table}} SET `NoobProtection_EndTime` = {$Now} WHERE `id` = {$_User['id']} LIMIT 1;";
                doquery($Query_UpdateUser, 'users');

                $parse['NewUserBox'] = '<tr><th class="c pad5 lime" colspan="3">'.$_Lang['NewUserProtection_Canceled'].'</th></tr><tr><th style="visibility: hidden;">&nbsp;</th></tr>';
            }
            else
            {
                $ProtectTimeLeft = $_User['NoobProtection_EndTime'] - $Now;
                $parse['NewUserBox'] = InsertJavaScriptChronoApplet('newprotect', '', $ProtectTimeLeft).'<tr><th class="c pad5 lime" colspan="3">'.sprintf($_Lang['NewUserProtection_Text'], pretty_time($ProtectTimeLeft, true, 'dhms')).'</th></tr><tr><th style="visibility: hidden;">&nbsp;</th></tr>';
            }
        }

        // --- Admin Info Box ------------------------------------------------------------------------------------
        if(CheckAuth('supportadmin'))
        {
            $Query_AdminBoxCheck[] = "SELECT COUNT(*) AS `Count`, 1 AS `Type` FROM `{{prefix}}reports` WHERE `status` = 0";
            $Query_AdminBoxCheck[] = "SELECT COUNT(*) AS `Count`, 2 AS `Type` FROM `{{prefix}}declarations` WHERE `status` = 0";
            $Query_AdminBoxCheck[] = "SELECT COUNT(*) AS `Count`, 3 AS `Type` FROM `{{prefix}}system_alerts` WHERE `status` = 0";
            $Query_AdminBoxCheck = implode(' UNION ', $Query_AdminBoxCheck);
            $Result_AdminBoxCheck = doquery($Query_AdminBoxCheck, '');

            $AdminBoxTotalCount = 0;
            while($AdminBoxData = $Result_AdminBoxCheck->fetch_assoc())
            {
                $AdminBox[$AdminBoxData['Type']] = $AdminBoxData['Count'];
                $AdminBoxTotalCount += $AdminBoxData['Count'];
            }
            if($AdminBoxTotalCount > 0)
            {
                $AdminAlerts = sprintf($_Lang['AdminAlertsBox'], $AdminBox[1], $AdminBox[2], $AdminBox[3]);
                $parse['AdminInfoBox'] = '<tr><th style="border-color: #FFA366; background-color: #FF8533; color: black;" class="c pad2" colspan="3">'.$AdminAlerts.'</th></tr><tr><th class="inv">&nbsp;</th></tr>';
            }
        }

        // --- MailChange Box ------------------------------------------------------------------------------------
        $parse['MailChange_Hide'] = 'display: none;';
        if($_User['email'] != $_User['email_2'])
        {
            $MailChange = doquery("SELECT * FROM {{table}} WHERE `UserID` = {$_User['id']} AND `ConfirmType` = 0 LIMIT 1;", 'mailchange', true);
            if($MailChange['ID'] > 0)
            {
                $ChangeTime = $MailChange['Date'] + (TIME_DAY * 7);

                $parse['MailChange_Hide'] = '';
                $parse['MailChange_Box'] = sprintf($_Lang['MailChange_Text']);
                if($MailChange['ConfirmHashNew'] == '')
                {
                    if($ChangeTime < $Now)
                    {
                        $parse['MailChange_Box'] .= "<br/><br/><form action=\"email_change.php?hash=none\" method=\"post\"><input type=\"submit\" style=\"font-weight: bold;\" value=\"{$_Lang['MailChange_Buto']}\" /></form>";
                    }
                    else
                    {
                        $parse['MailChange_Box'] .= "<br/><br/>".sprintf($_Lang['MailChange_Inf2'], date('d.m.Y H:i:s', $ChangeTime));
                    }
                }
                else
                {
                    $parse['MailChange_Box'] .= "<br/><br/>{$_Lang['MailChange_Inf1']}";
                }
            }
        }

        // Fleet Blockade Info (here, only for Global Block)
        $GetSFBData = doquery("SELECT `ID`, `EndTime`, `BlockMissions`, `DontBlockIfIdle`, `Reason` FROM {{table}} WHERE `Type` = 1 AND `StartTime` <= UNIX_TIMESTAMP() AND (`EndTime` > UNIX_TIMESTAMP() OR `PostEndTime` > UNIX_TIMESTAMP()) ORDER BY `EndTime` DESC LIMIT 1;", 'smart_fleet_blockade', true);
        if($GetSFBData['ID'] > 0)
        {
            // Fleet Blockade is Active
            include($_EnginePath.'includes/functions/CreateSFBInfobox.php');
            $parse['P_SFBInfobox'] = CreateSFBInfobox($GetSFBData, array('standAlone' => true, 'Width' => 750, 'MarginBottom' => 10));
        }

        // --- Free Premium Items Info Box -----------------------------------------------------------------------
        $GetFreeItems = doquery("SELECT COUNT(`ID`) as `Count` FROM {{table}} WHERE `UserID` = {$_User['id']} AND `Used` = false;", 'premium_free', true);
        if($GetFreeItems['Count'] > 0)
        {
            $parse['FreePremiumItemsBox'] = '<tr><th colspan="3"><a class="orange" href="galacticshop.php?show=free">'.sprintf($_Lang['FreePremItem_Text'], $GetFreeItems['Count']).'</a></th></tr>';
        }

        // --- System Messages Box -------------------------------------------------------------------------------
        if(!empty($_GET['showmsg']))
        {
            $SysMsgLoop = 0;
            if($_GET['showmsg'] == 'abandon')
            {
                $ShowSystemMsg[$SysMsgLoop]['txt'] = $_Lang['Abandon_ColonyAbandoned'];
                $ShowSystemMsg[$SysMsgLoop]['col'] = 'lime';
                $SysMsgLoop += 1;
            }
        }

        if(!empty($ShowSystemMsg))
        {
            $parse['SystemMsgBox'] = '';
            foreach($ShowSystemMsg as $SystemMsg)
            {
                $parse['SystemMsgBox'] .= '<tr><th colspan="3" class="pad5 '.$SystemMsg['col'].'">'.$SystemMsg['txt'].'</th></tr>';
            }
        }

        // --- New Messages Information Box ----------------------------------------------------------------------
        $NewMsg = doquery("SELECT COUNT(`id`) as `count` FROM {{table}} WHERE `deleted` = false AND `read` = false AND `id_owner` = {$_User['id']};", 'messages', true);
        if($NewMsg['count'] > 0)
        {
            if($NewMsg['count'] == 1)
            {
                $MsgBox_NewSurfix = $_Lang['MsgBox_New_1'];
                $MsgBox_UnreadenSurfix= $_Lang['MsgBox_Unreaden_1'];
                $MsgBox_Msg_s = $_Lang['MsgBox_Msg'];
            }
            elseif($NewMsg['count'] > 1 AND $NewMsg['count'] < 5)
            {
                $MsgBox_NewSurfix = $_Lang['MsgBox_New_2_4'];
                $MsgBox_UnreadenSurfix= $_Lang['MsgBox_Unreaden_2_4'];
                $MsgBox_Msg_s = $_Lang['MsgBox_Msgs'];
            }
            else
            {
                $MsgBox_NewSurfix = $_Lang['MsgBox_New_5'];
                $MsgBox_UnreadenSurfix= $_Lang['MsgBox_Unreaden_5'];
                $MsgBox_Msg_s = $_Lang['MsgBox_Msgs'];
            }
            $MsgBoxText = $_Lang['MsgBox_YouHave'].' '.prettyNumber($NewMsg['count']).' '.$_Lang['MsgBox_New'].$MsgBox_NewSurfix.', '.$_Lang['MsgBox_Unreaden'].$MsgBox_UnreadenSurfix.' '.$MsgBox_Msg_s.'!';

            $NewMsgBox = '<tr><th colspan="3"><a href="messages.php">'.$MsgBoxText.'</a></th></tr>';
            $parse['NewMsgBox'] = $NewMsgBox;
        }

        // --- New Polls Information Box -------------------------------------------------------------------------
        $SQLResult_GetPolls = doquery("SELECT {{table}}.`id`, `votes`.`id` AS `vote_id` FROM {{table}} LEFT JOIN {{prefix}}poll_votes AS `votes` ON `votes`.`poll_id` = {{table}}.id AND `votes`.`user_id` = {$_User['id']} WHERE {{table}}.`open` = 1 ORDER BY {{table}}.`time` DESC;", 'polls');
        if($SQLResult_GetPolls->num_rows > 0)
        {
            $AvailablePolls = 0;
            while($PollData = $SQLResult_GetPolls->fetch_assoc())
            {
                if($PollData['vote_id'] <= 0)
                {
                    $AvailablePolls += 1;
                }
            }
            if($AvailablePolls > 0)
            {
                $parse['NewPollsBox'] = '<tr><th colspan="3"><a style="color: orange;" href="polls.php">'.vsprintf($_Lang['PollBox_You_can_vote_in_new_polls'], ($AvailablePolls > 1) ? $_Lang['PollBox_More'] : $_Lang['PollBox_One']).'</a></th></tr>';
            }
        }

        // --- Get users activity informations -----------------------------------------------------------
        $TodaysStartTimeStamp = mktime(0, 0, 0);

        $SQLResult_GetOnlineUsers = doquery(
            "SELECT IF(`onlinetime` >= (UNIX_TIMESTAMP() - (".TIME_ONLINE.")), 1, 0) AS `current_online` FROM {{table}} WHERE `onlinetime` >= {$TodaysStartTimeStamp};",
            'users'
        );

        $TodayActive = $SQLResult_GetOnlineUsers->num_rows;
        $CurrentOnline = 0;

        if($TodayActive > 0)
        {
            while($ActiveData = $SQLResult_GetOnlineUsers->fetch_assoc())
            {
                if($ActiveData['current_online'] == 1)
                {
                    $CurrentOnline += 1;
                }
            }
        }

        $parse['CurrentOnline'] = prettyNumber($CurrentOnline);
        $parse['TodayOnline'] = prettyNumber($TodayActive);
        $parse['TotalPlayerCount'] = prettyNumber($_GameConfig['users_amount']);
        $parse['ServerRecord'] = prettyNumber($_GameConfig['rekord']);

        // --- Get last Stats and Records UpdateTime -----------------------------------------------------
        $parse['LastStatsRecount'] = date('d.m.Y H:i:s', $_GameConfig['last_update']);

        // --- MoraleSystem Box ---
        if(MORALE_ENABLED)
        {
            Morale_ReCalculate($_User);
            $UserMoraleLevel = $_User['morale_level'];

            $parse['Insert_Morale_Level'] = $UserMoraleLevel;
            if($UserMoraleLevel > 0)
            {
                $parse['Insert_Morale_Color'] = 'lime';
            }
            else if($UserMoraleLevel < 0)
            {
                if($UserMoraleLevel <= -50)
                {
                    $parse['Insert_Morale_Color'] = 'red';
                }
                else
                {
                    $parse['Insert_Morale_Color'] = 'orange';
                }
            }

            if($UserMoraleLevel == 0)
            {
                $parse['Insert_Morale_Status'] = $_Lang['Box_Morale_NoChanges'];
            }
            else
            {
                if($UserMoraleLevel > 0)
                {
                    $Temp_MoraleStatus = 'Pos';
                }
                else
                {
                    $Temp_MoraleStatus = 'Neg';
                }
                if($_User['morale_droptime'] > $Now)
                {
                    GlobalTemplate_AppendToAfterBody(InsertJavaScriptChronoApplet('morale', '', $_User['morale_droptime'], true));
                    $parse['Insert_Morale_Status'] = sprintf($_Lang['Box_Morale_DropStartIn_'.$Temp_MoraleStatus], pretty_time($_User['morale_droptime'] - $Now, true, 'D'));
                }
                else
                {
                    if($UserMoraleLevel > 0)
                    {
                        $Temp_MoraleDropInterval = MORALE_DROPINTERVAL_POSITIVE;
                    }
                    else
                    {
                        $Temp_MoraleDropInterval = MORALE_DROPINTERVAL_NEGATIVE;
                    }
                    if($_User['morale_lastupdate'] == 0)
                    {
                        $Temp_MoraleNextDrop = $_User['morale_droptime'] + $Temp_MoraleDropInterval;
                    }
                    else
                    {
                        $Temp_MoraleNextDrop = $_User['morale_lastupdate'] + $Temp_MoraleDropInterval;
                    }
                    GlobalTemplate_AppendToAfterBody(InsertJavaScriptChronoApplet('morale', '', $Temp_MoraleNextDrop, true));
                    $parse['Insert_Morale_Status'] = sprintf($_Lang['Box_Morale_Dropping_'.$Temp_MoraleStatus], pretty_time($Temp_MoraleNextDrop - $Now, true, 'D'));
                }
            }
            $_Lang['Box_Morale_Points'] = sprintf($_Lang['Box_Morale_Points'], prettyNumber($_User['morale_points']));

            $parse['Insert_MoraleBox'] = parsetemplate(gettemplate('overview_body_morale'), $parse);
        }

        // --- Get Register Date -
        $RegisterDays = floor(($Now - $_User['register_time']) / (24*60*60));
        if($RegisterDays == 1)
        {
            $parse['RegisterDaysTxt'] = $parse['_youPlaySince_1day'];
        }
        else
        {
            $parse['RegisterDaysTxt'] = $parse['_youPlaySince_2days'];
        }
        $parse['RegisterDays'] = prettyNumber($RegisterDays);
        $parse['RegisterDate'] = date('d.m.Y', $_User['register_time']);

        // --- ProAccount Box ---
        $parse['ProAccountInfoText'] = ($_User['pro_time'] > $Now) ? $_Lang['ProAccTill'].'<span class="orange">'.date("d.m.Y\<\b\\r\/\>H:i:s", $_User['pro_time']).'</span>' : (($_User['pro_time'] == 0) ? $_Lang['NoProAccEver'] : $_Lang['NoProAccSince'].'<span class="red">'.date("d.m.Y\<\b\\r\/\>H:i:s", $_User['pro_time']).'</span>');
        $parse['ProAccLink'] = ($_User['pro_time'] > $Now) ? $_Lang['ProAccBuyMore'] : (($_User['pro_time'] == 0) ? $_Lang['ProAccBuyFirst'] : $_Lang['ProAccBuyNext']);

        // --- Get Reffered Count --
        $Referred = doquery("SELECT COUNT(`id`) as `count` FROM {{table}} WHERE `referrer_id` = {$_User['id']};", 'referring_table', true);
        $parse['RefferedCounter'] = prettyNumber((($Referred['count'] > 0) ? $Referred['count'] : '0'));

        // --- Get UserStats ---
        $StatRecord = doquery("SELECT * FROM {{table}} WHERE `stat_type` = '1' AND `id_owner` = {$_User['id']} LIMIT 1;", 'statpoints', true);
        $parse['user_points'] = prettyNumber($StatRecord['build_points']);
        $parse['user_fleet'] = prettyNumber($StatRecord['fleet_points']);
        $parse['user_defs'] = prettyNumber($StatRecord['defs_points']);
        $parse['player_points_tech'] = prettyNumber($StatRecord['tech_points']);
        $parse['total_points'] = prettyNumber($StatRecord['total_points']);

        // Total Rank changes
        if($StatRecord['total_rank'] > 0)
        {
            $ile = $StatRecord['total_old_rank'] - $StatRecord['total_rank'];
            if($ile > 0)
            {
                $ile = "<span class=\"lime\">(+{$ile})</span>";
            }
            elseif($ile < 0)
            {
                $ile = "<span class=\"red\">({$ile})</span>";
            }
            else
            {
                $ile = '<span class="lightblue">(*)</span>';
            }
            $parse['user_total_rank'] = '<a href="stats.php?range='.$StatRecord['total_rank'].'">'.$StatRecord['total_rank'].'</a> '.$ile;
        }
        else
        {
            $parse['user_total_rank'] = 0;
            $StatRecord['total_rank'] = '0';
        }
        $parse['set_user_total_rank'] = $StatRecord['total_rank'];

        // Build Rank changes
        if(isset($StatRecord['build_rank']) && $StatRecord['build_rank'] > 0)
        {
            $ile = $StatRecord['build_old_rank'] - $StatRecord['build_rank'];
            if($ile > 0)
            {
                $ile = "<span class=\"lime\">(+{$ile})</span>";
            }
            elseif($ile < 0)
            {
                $ile = "<span class=\"red\">({$ile})</span>";
            }
            else
            {
                $ile = '<span class="lightblue">(*)</span>';
            }
            $parse['user_br'] = '<a href="stats.php?range='.$StatRecord['build_rank'].'&amp;type=4">'.$StatRecord['build_rank'].'</a> '.$ile;
        }
        else
        {
            $parse['user_br'] = 0;
            $StatRecord['build_rank'] = '0';
        }
        $parse['set_user_br'] = $StatRecord['build_rank'];

        // Fleet rank changes
        if(isset($StatRecord['fleet_rank']) && $StatRecord['fleet_rank'] > 0)
        {
            $ile = $StatRecord['fleet_old_rank'] - $StatRecord['fleet_rank'];
            if($ile > 0)
            {
                $ile = "<span class=\"lime\">(+{$ile})</span>";
            }
            else if($ile < 0)
            {
                $ile = "<span class=\"red\">({$ile})</span>";
            }
            else
            {
                $ile = '<span class="lightblue">(*)</span>';
            }
            $parse['user_fr'] = '<a href="stats.php?range='.$StatRecord['fleet_rank'].'&amp;type=2">'.$StatRecord['fleet_rank'].'</a> '.$ile;
        }
        else
        {
            $parse['user_fr'] = 0;
            $StatRecord['fleet_rank'] = '0';
        }
        $parse['set_user_fr'] = $StatRecord['fleet_rank'];

        // Defense rank changes
        if(isset($StatRecord['defs_rank']) && $StatRecord['defs_rank'] > 0)
        {
            $ile = $StatRecord['defs_old_rank'] - $StatRecord['defs_rank'];
            if($ile > 0)
            {
                $ile = "<span class=\"lime\">(+{$ile})</span>";
            }
            else if($ile < 0)
            {
                $ile = "<span class=\"red\">({$ile})</span>";
            }
            else
            {
                $ile = '<span class="lightblue">(*)</span>';
            }
            $parse['user_dr'] = '<a href="stats.php?range='.$StatRecord['defs_rank'].'&amp;type=5">'.$StatRecord['defs_rank'].'</a> '.$ile;
        }
        else
        {
            $parse['user_dr'] = 0;
            $StatRecord['defs_rank'] = '0';
        }
        $parse['set_user_dr'] = $StatRecord['defs_rank'];

        // Research rank changes
        if(isset($StatRecord['tech_rank']) && $StatRecord['tech_rank'] > 0)
        {
            $ile = $StatRecord['tech_old_rank'] - $StatRecord['tech_rank'];
            if($ile > 0)
            {
                $ile = "<span class=\"lime\">(+{$ile})</span>";
            }
            else if($ile < 0)
            {
                $ile = "<span class=\"red\">({$ile})</span>";
            }
            else
            {
                $ile = '<span class="lightblue">(*)</span>';
            }
            $parse['user_tr'] = '<a href="stats.php?range='.$StatRecord['tech_rank'].'&amp;type=3">'.$StatRecord['tech_rank'].'</a> '.$ile;
        }
        else
        {
            $parse['user_tr'] = 0;
            $StatRecord['tech_rank'] = '0';
        }
        $parse['set_user_tr'] = $StatRecord['tech_rank'];

        // Get User Achievements
        $GetStats_Fields = '`ustat_raids_won`, `ustat_raids_draw`, `ustat_raids_lost`, `ustat_raids_acs_won`, `ustat_raids_inAlly`, `ustat_raids_missileAttack`';
        $GetStats = doquery("SELECT {$GetStats_Fields} FROM {{table}} WHERE `A_UserID` = {$_User['id']} LIMIT 1;", 'achievements_stats', true);
        $parse['raids']                    = prettyNumber($GetStats['ustat_raids_won'] + $GetStats['ustat_raids_draw'] + $GetStats['ustat_raids_lost'] + $GetStats['ustat_raids_inAlly']);
        $parse['raidswin']                = prettyNumber($GetStats['ustat_raids_won']);
        $parse['raidsdraw']                = prettyNumber($GetStats['ustat_raids_draw']);
        $parse['raidsloose']            = prettyNumber($GetStats['ustat_raids_lost']);
        $parse['raidacswin']            = prettyNumber($GetStats['ustat_raids_acs_won']);
        $parse['raidsinally']            = prettyNumber($GetStats['ustat_raids_inAlly']);
        $parse['raidsmissileattacks']    = prettyNumber($GetStats['ustat_raids_missileAttack']);

        // --- Planet Data ---------
        if($_Planet['planet_type'] == 1)
        {
            $parse['ShowWhatsOnOrbit'] = '<b style="color: grey;">'.$_Lang['_emptyOrbit'].'</b>';
            if($_GalaxyRow['id_moon'] > 0)
            {
                $MoonRow = doquery("SELECT `id`, `name` FROM {{table}} WHERE `id` = {$_GalaxyRow['id_moon']} LIMIT 1;", 'planets', true);
                if($MoonRow['id'] > 0)
                {
                    $parse['ShowWhatsOnOrbit'] = "<a class=\"tipTipTitle moon\" href=\"?cp={$MoonRow['id']}&re=0\" title=\"{$_Lang['TipTip_Switch2Moon']}\">{$MoonRow['name']}</a>";
                }
            }
        }
        else
        {
            $PlanetData = doquery("SELECT `id`, `name` FROM {{table}} WHERE `id` = {$_GalaxyRow['id_planet']} LIMIT 1;", 'planets', true);
            $parse['ShowWhatsOnOrbit'] = "<a class=\"tipTipTitle planet\" href=\"?cp={$PlanetData['id']}&re=0\" title=\"{$_Lang['TipTip_Switch2Planet']}\">{$PlanetData['name']}</a>";
            $DontShowPlanet[] = $PlanetData['id'];
        }
        if(empty($parse['onOrbit_img']))
        {
            $parse['hide_orbit_view'] = 'style="display: none;"';
        }

        $MaxPlanetFields = CalculateMaxPlanetFields($_Planet);
        $parse['skinpath'] = $_SkinPath;
        $parse['planet_image'] = $_Planet['image'];
        $parse['planet_name'] = $_Planet['name'];
        $parse['planet_diameter'] = prettyNumber($_Planet['diameter']);
        $parse['planet_field_current'] = $_Planet['field_current'];
        $parse['planet_field_max']= $MaxPlanetFields;
        $parse['planet_temp_min'] = $_Planet['temp_min'];
        $parse['planet_temp_max'] = $_Planet['temp_max'];
        $parse['galaxy_galaxy'] = $_Planet['galaxy'];
        $parse['galaxy_planet'] = $_Planet['planet'];
        $parse['galaxy_system'] = $_Planet['system'];
        if($_Planet['id'] == $_User['id_planet'])
        {
            $parse['HideAbandonLink'] = ' style="display: none"';
        }
        $parse['_planetData_type'] = ($_Planet['planet_type'] == 1) ? $parse['_planetData_planet'] : $parse['_planetData_moon'];
        $parse['overvier_type'] = ($_Planet['planet_type'] == 1) ? $parse['_overview_planet'] : $parse['_overview_moon'];
        $parse['planet_field_used_percent'] = round(($_Planet['field_current'] / $MaxPlanetFields) * 100);
        $parse['metal_debris'] = prettyNumber($_GalaxyRow['metal']);
        $parse['crystal_debris'] = prettyNumber($_GalaxyRow['crystal']);
        if($_GalaxyRow['metal'] <= 0 AND $_GalaxyRow['crystal'] <= 0)
        {
            $parse['hide_debris'] = 'style="display: none;"';
        }
        else
        {
            $parse['hide_nodebris'] = 'display: none;';
        }

        // --- Transporters ----------------------------
        $SmallRequired = ceil(($_Planet['metal'] + $_Planet['crystal'] + $_Planet['deuterium']) / $_Vars_Prices[202]['capacity']);
        $BigRequired = ceil(($_Planet['metal'] + $_Planet['crystal'] + $_Planet['deuterium']) / $_Vars_Prices[203]['capacity']);
        $MegaRequired = ceil(($_Planet['metal'] + $_Planet['crystal'] + $_Planet['deuterium']) / $_Vars_Prices[217]['capacity']);

        $SmallMissStay = -($SmallRequired - $_Planet[$_Vars_GameElements[202]]);
        $BigMissStay = -($BigRequired - $_Planet[$_Vars_GameElements[203]]);
        $MegaMissStay = -($MegaRequired - $_Planet[$_Vars_GameElements[217]]);

        $parse['small_cargo_count'] = prettyNumber($SmallRequired);//Small cargo ship
        $parse['big_cargo_count'] = prettyNumber($BigRequired);//Big cargo ship
        $parse['mega_cargo_count'] = prettyNumber($MegaRequired); //Mega cargo ship

        $parse['small_cargo_miss_stay'] = str_replace('-', '', prettyColorNumber($SmallMissStay, true));
        $parse['big_cargo_miss_stay'] = str_replace('-', '', prettyColorNumber($BigMissStay, true));
        $parse['mega_cargo_miss_stay'] = str_replace('-', '', prettyColorNumber($MegaMissStay, true));

        $parse['small_trans'] = $_Lang['tech'][202];
        $parse['big_trans'] = $_Lang['tech'][203];
        $parse['mega_trans'] = $_Lang['tech'][217];

        if(isPro() AND $_User['current_planet'] != $_User['settings_mainPlanetID'])
        {
            $GetQuickResPlanet = doquery("SELECT `name`, `galaxy`, `system`, `planet` FROM {{table}} WHERE `id` = {$_User['settings_mainPlanetID']};", 'planets', true);
            $parse['QuickResSend_Button'] = sprintf($_Lang['QuickResSend_Button'], $GetQuickResPlanet['name'], $GetQuickResPlanet['galaxy'], $GetQuickResPlanet['system'], $GetQuickResPlanet['planet']);
        }
        else
        {
            $parse['Hide_QuickResButton'] = ' style="display: none;"';
        }

        // --- Flying Fleets Table ---
        $Query_GetFleets = '';
        $Query_GetFleets .= "SELECT `fl`.*, `pl1`.`name` AS `start_name`, `pl2`.`name` AS `end_name`, `acs`.`fleets_id`, `usr`.`username` AS `owner_name` ";
        $Query_GetFleets .= "FROM {{table}} AS `fl`";
        $Query_GetFleets .= "LEFT JOIN `{{prefix}}planets` AS `pl1` ON `pl1`.`id` = `fl`.`fleet_start_id` ";
        $Query_GetFleets .= "LEFT JOIN `{{prefix}}planets` AS `pl2` ON `pl2`.`id` = `fl`.`fleet_end_id` ";
        $Query_GetFleets .= "LEFT JOIN `{{prefix}}users` AS `usr` ON `usr`.`id` = `fl`.`fleet_owner` ";
        $Query_GetFleets .= "LEFT JOIN `{{prefix}}acs` AS `acs` ON `acs`.`main_fleet_id` = `fl`.`fleet_id` ";
        $Query_GetFleets .= "WHERE `fl`.`fleet_owner` = '{$_User['id']}' OR `fl`.`fleet_target_owner` = '{$_User['id']}';";
        $Result_GetFleets = doquery($Query_GetFleets, 'fleets');

        $FleetIndex1 = 0;
        $FleetIndex2 = 2000;
        if($Result_GetFleets->num_rows > 0)
        {
            include($_EnginePath.'includes/functions/BuildFleetEventTable.php');
            while($FleetRow = $Result_GetFleets->fetch_assoc())
            {
                if($FleetRow['fleet_owner'] == $_User['id'])
                {
                    $FleetIndex1 += 1;

                    $StartTime = $FleetRow['fleet_start_time'];
                    $StayTime = $FleetRow['fleet_end_stay'];
                    $EndTime = $FleetRow['fleet_end_time'];
                    // If this is ACS Fleet, change Mission (for AttackLeader Fleet)
                    if(!empty($FleetRow['fleets_id']))
                    {
                        $FleetRow['fleet_mission'] = 2;
                    }

                    if($StartTime > $Now)
                    {
                        $Fleets[$StartTime.'_'.$FleetRow['fleet_id']] = BuildFleetEventTable($FleetRow, 0, true, 'fs', $FleetIndex1);
                    }

                    if($FleetRow['fleet_mission'] != 4 OR ($StartTime < $Now AND $FleetRow['fleet_mission'] == 4 AND $EndTime > $Now))
                    {
                        if($FleetRow['fleet_mission'] != 4)
                        {
                            if($StayTime > $Now)
                            {
                                $Fleets[$StayTime.'_'.$FleetRow['fleet_id']] = BuildFleetEventTable($FleetRow, 1, true, 'ft', $FleetIndex1);
                            }
                        }
                        if($FleetRow['fleet_mission'] == 7 AND $FleetRow['fleet_mess'] == 0 AND $FleetRow['fleet_amount'] == 1)
                        {
                            // Dont show ComeBack when this is a colonization mission
                        }
                        else
                        {
                            if($EndTime > $Now)
                            {
                                $Fleets[$EndTime.'_'.$FleetRow['fleet_id']] = BuildFleetEventTable($FleetRow, 2, true, 'fe', $FleetIndex1);
                            }
                        }
                    }
                }
                else
                {
                    if($FleetRow['fleet_mission'] != 8)
                    {
                        $FleetIndex2 += 1;
                        $StartTime = $FleetRow['fleet_start_time'];
                        $StayTime = $FleetRow['fleet_end_stay'];
                        if(!empty($FleetRow['fleets_id']))
                        {
                            $FleetRow['fleet_mission'] = 2;
                        }

                        if($StartTime > $Now)
                        {
                            $Fleets[$StartTime.'_'.$FleetRow['fleet_id']] = BuildFleetEventTable($FleetRow, 0, false, 'ofs', $FleetIndex2);
                        }
                        if($FleetRow['fleet_mission'] == 5)
                        {
                            if($StayTime > $Now)
                            {
                                $Fleets[$StayTime.'_'.$FleetRow['fleet_id']] = BuildFleetEventTable($FleetRow, 1, false, 'oft', $FleetIndex2);
                            }
                        }
                    }
                }
            }
        }
        if(!empty($Fleets))
        {
            ksort($Fleets);
            $parse['fleet_list'] = implode('', $Fleets);
        }

        // --- Create other planets thumbnails ---
        $Results['planets'] = array();

        $Order = ($_User['planet_sort_order'] == 1) ? 'DESC' : 'ASC' ;
        $Sort = $_User['planet_sort'];

        $QryPlanets = "SELECT * FROM {{table}} WHERE `id_owner` = {$_User['id']} AND `id` != {$_Planet['id']} AND `planet_type` != 3 ORDER BY ";
        if($Sort == 0)
        {
            $QryPlanets .= "`id` {$Order}";
        }
        else if($Sort == 1)
        {
            $QryPlanets .= "`galaxy`, `system`, `planet`, `planet_type` {$Order}";
        }
        else if($Sort == 2)
        {
            $QryPlanets .= "`name` {$Order}";
        }
        $parse['OtherPlanets'] = '';

        $SQLResult_GetAllOtherPlanets = doquery($QryPlanets, 'planets');

        if($SQLResult_GetAllOtherPlanets->num_rows > 0)
        {
            $InCurrentRow = 0;
            $InNextRow = false;

            while($PlanetsData = $SQLResult_GetAllOtherPlanets->fetch_assoc())
            {
                // Show Planet on List
                if(empty($DontShowPlanet) OR !in_array($PlanetsData['id'], $DontShowPlanet))
                {
                    $DontShowThisPlanet = false;
                }
                else
                {
                    $DontShowThisPlanet = true;
                }
                if($DontShowThisPlanet === false)
                {
                    if($InCurrentRow == 0)
                    {
                        $parse['OtherPlanets'] .= '<tr>';
                    }
                    $parse['OtherPlanets'] .= '<th>'.$PlanetsData['name'].'<br/>';
                    $parse['OtherPlanets'] .= "<a href=\"?cp={$PlanetsData['id']}&re=0\" title=\"{$PlanetsData['name']}\"><img src=\"{$_SkinPath}planeten/small/s_{$PlanetsData['image']}.jpg\" height=\"50\" width=\"50\"></a><br>";
                    $parse['OtherPlanets'] .= '<center>';
                }
                // Update Planet - Building Queue
                if(HandlePlanetUpdate($PlanetsData, $_User, $Now, true) === true)
                {
                    $Results['planets'][] = $PlanetsData;
                }
                if($PlanetsData['buildQueue_firstEndTime'] > 0)
                {
                    if($DontShowThisPlanet === false)
                    {
                        $BuildQueue = $PlanetsData['buildQueue'];
                        $QueueArray = explode (';', $BuildQueue);
                        $CurrentBuild = explode (',', $QueueArray[0]);
                        $BuildElement = $CurrentBuild[0];
                        $BuildLevel = $CurrentBuild[1];
                        $BuildRestTime = pretty_time($CurrentBuild[3] - $Now);

                        $parse['OtherPlanets'] .= $_Lang['tech'][$BuildElement].' ('.$BuildLevel.')';
                        $parse['OtherPlanets'] .= '<br><span style="color: #7f7f7f;">('.$BuildRestTime.')</span>';
                    }
                }
                else
                {
                    if($DontShowThisPlanet === false)
                    {
                        $parse['OtherPlanets'] .= $_Lang['Free'].'<br/>&nbsp;';
                    }
                }

                if($DontShowThisPlanet === false)
                {
                    $parse['OtherPlanets'] .= '</center></th>';
                }

                if($DontShowThisPlanet === false)
                {
                    $InCurrentRow += 1;
                    if($InCurrentRow >= 5)
                    {
                        $InCurrentRow = 0;
                        $InNextRow = true;
                        if($DontShowThisPlanet == false)
                        {
                            $parse['OtherPlanets'] .= '</tr>';
                        }
                    }
                }
            }

            if($InNextRow === true AND $InCurrentRow > 0)
            {
                $Difference = 5 - $InCurrentRow;
                for($i = 0; $i < $Difference; $i += 1)
                {
                    $parse['OtherPlanets'] .= '<th>&nbsp;</th>';
                }
                $parse['OtherPlanets'] .= '</tr>';
            }
        }
        else
        {
            $parse['hide_other_planets'] = 'style="display: none;"';
        }

        // Update this planet (if necessary)
        if(HandlePlanetUpdate($_Planet, $_User, $Now, true) === true)
        {
            $Results['planets'][] = $_Planet;
        }
        if($_Planet['buildQueue_firstEndTime'] > 0)
        {
            $BuildQueue = explode(';', $_Planet['buildQueue']);
            $CurrBuild = explode(',', $BuildQueue[0]);
            $RestTime = $_Planet['buildQueue_firstEndTime'] - $Now;
            $PlanetID = $_Planet['id'];

            $Build = '';
            $Build .= InsertJavaScriptChronoApplet(
                'pl',
                'this',
                $RestTime,
                false,
                false,
                'function () { onQueuesFirstElementFinished(' . $PlanetID . '); }'
            );
            $Build .= $_Lang['tech'][$CurrBuild[0]].' ('.$CurrBuild[1].')';
            $Build .= '<br /><div id="bxxplthis" class="z">'.pretty_time($RestTime, true).'</div>';
            if(isset($_Vars_PremiumBuildings[$CurrBuild[0]]) && $_Vars_PremiumBuildings[$CurrBuild[0]] == 1)
            {
                $Build .= '<div id="dlink"><a class="red" style="cursor: pointer;" onclick="alert(\''.$_Lang['CannotDeletePremiumBuilding_Warning'].'\')">'.$_Lang['DelFirstQueue'].'</a></div>';
            }
            else
            {
                $Build .= '<div id="dlink"><a href="buildings.php?listid=1&amp;cmd=cancel&amp;planet='.$PlanetID.'">'.$_Lang['DelFirstQueue'].'</a></div>';
            }

            $parse['building'] = $Build;
        }
        else
        {
            $parse['building'] = $_Lang['Free'];
        }

        // Now update all the planets (if it's necessary)
        HandlePlanetUpdate_MultiUpdate($Results, $_User);

        // News Frame ...
        if($_GameConfig['OverviewNewsFrame'] == '1')
        {
            $parse['FromAdmins'] = nl2br($_GameConfig['OverviewNewsText']);
        }
        if($_GameConfig['OverviewBanner'] == '1')
        {
            $parse['TopLists_box'] = nl2br($_GameConfig['OverviewClickBanner']);
        }

        $parse['referralLink2'] = GAMEURL.'index.php?r='.$_User['id'];
        $parse['referralLink1'] = '[url='.$parse['referralLink2'].'][img]'.GAMEURL.'generate_sig.php?uid='.$_User['id'].'[/img][/url]';
        $parse['UserUID'] = $_User['id'];

        $page = parsetemplate(gettemplate('overview_body'), $parse);
        display($page, $_Lang['Overview']);
        break;
}

?>
