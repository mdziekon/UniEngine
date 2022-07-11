<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');
include_once($_EnginePath . 'modules/session/_includes.php');
include_once($_EnginePath . 'modules/flights/_includes.php');
include_once($_EnginePath . 'modules/flightControl/_includes.php');
include_once($_EnginePath . 'modules/overview/_includes.php');

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Modules\Flights;
use UniEngine\Engine\Modules\FlightControl;
use UniEngine\Engine\Modules\Overview;

loggedCheck();

$Now = time();

if ($_User['first_login'] == 0) {
    Overview\Screens\FirstLogin\render([
        'user' => &$_User,
        'currentTimestamp' => $Now,
    ]);

    die();
}

$mode = (isset($_GET['mode']) ? $_GET['mode'] : '');

includeLang('resources');
includeLang('overview');

switch($mode)
{
    case 'rename':
        Overview\Screens\PlanetNameChange\render([
            'input' => &$_POST,
            'user' => &$_User,
            'planet' => &$_Planet,
        ]);

        break;
    case 'abandon':
        Overview\Screens\AbandonPlanet\render([
            'input' => &$_POST,
            'user' => &$_User,
            'planet' => &$_Planet,
            'currentTimestamp' => $Now,
        ]);

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
        if (!isUserAccountActivated($_User)) {
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
        $parse['AdminInfoBox'] = Overview\Screens\Overview\Components\AdminAlerts\render([
            'user' => &$_User,
        ])['componentHTML'];

        // --- MailChange Box ------------------------------------------------------------------------------------
        $parse['EmailChangeInfoBox'] = Overview\Screens\Overview\Components\EmailChangeInfo\render([
            'user' => &$_User,
            'currentTimestamp' => $Now,
        ])['componentHTML'];

        // Fleet Blockade Info (here, only for Global Block)
        $parse['P_SFBInfobox'] = FlightControl\Components\SmartFleetBlockadeInfoBox\render()['componentHTML'];

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
        $parse['NewMsgBox'] = Overview\Screens\Overview\Components\NewMessagesInfo\render([
            'userId' => $_User['id'],
        ])['componentHTML'];

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

        // --- Render UserStats ---
        $StatRecord = doquery("SELECT * FROM {{table}} WHERE `stat_type` = '1' AND `id_owner` = {$_User['id']} LIMIT 1;", 'statpoints', true);

        $parse['Component_StatsList'] = Overview\Screens\Overview\Components\StatsList\render([
            'stats' => $StatRecord,
        ])['componentHTML'];

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
        $parse['Component_QuickTransport'] = Overview\Screens\Overview\Components\ResourcesTransport\render([
            'user' => &$_User,
            'planet' => &$_Planet,
        ])['componentHTML'];

        // --- Flying Fleets Table ---
        $Result_GetFleets = Flights\Fetchers\fetchCurrentFlights([ 'userId' => $_User['id'] ]);

        $parse['fleet_list'] = Flights\Components\FlightsList\render([
            'viewMode' => Flights\Components\FlightsList\Utils\ViewMode::Overview,
            'flights' => $Result_GetFleets,
            'viewingUserId' => $_User['id'],
            'targetOwnerId' => null,
            'currentTimestamp' => $Now,
        ])['componentHTML'];

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
        $parse['OtherPlanets'] = [];

        $SQLResult_GetAllOtherPlanets = doquery($QryPlanets, 'planets');

        if ($SQLResult_GetAllOtherPlanets->num_rows > 0) {
            while ($PlanetsData = $SQLResult_GetAllOtherPlanets->fetch_assoc()) {
                // Update Planet - Building Queue
                if (HandlePlanetUpdate($PlanetsData, $_User, $Now, true) === true) {
                    $Results['planets'][] = $PlanetsData;
                }

                if (
                    !empty($DontShowPlanet) &&
                    in_array($PlanetsData['id'], $DontShowPlanet)
                ) {
                    continue;
                }

                $parse['OtherPlanets'][] = Overview\Screens\Overview\Components\PlanetsListElement\render([
                    'planet' => &$PlanetsData,
                    'currentTimestamp' => $Now,
                ])['componentHTML'];
            }
        } else {
            $parse['hide_other_planets'] = 'style="display: none;"';
        }

        $parse['OtherPlanets'] = implode('', $parse['OtherPlanets']);

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
