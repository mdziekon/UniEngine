<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('stat');

$Now = time();

$parse = $_Lang;
$parse['stat_values'] = '';

$who = (isset($_POST['who']) ? $_POST['who'] : ((isset($_GET['who']) ? $_GET['who'] : null)));
if($who != 1 AND $who != 2)
{
    $who = 1;
}
$type = (isset($_POST['type']) ? $_POST['type'] : ((isset($_GET['type']) ? $_GET['type'] : null)));
if(!isset($type))
{
    $type= 1;
}
$range = (isset($_POST['range']) ? $_POST['range'] : ((isset($_GET['range']) ? $_GET['range'] : null)));
if(!isset($range))
{
    if(isset($_GET['start']))
    {
        $range = intval($_GET['start']);
    }
    else
    {
        $range = 1;
    }
}
if($range <= 0)
{
    $range = 1;
}

$parse['who'] = '';
$parse['who'] .= '<option value="1"'. (($who == 1) ? ' selected' : '') .'>'. $_Lang['stat_player'] .'</option>';
$parse['who'] .= '<option value="2"'. (($who == 2) ? ' selected' : '') .'>'. $_Lang['stat_allys'].'</option>';
$parse['LastWhoVal'] = $who;

$parse['type'] = '<option value="1"'. (($type == 1) ? ' selected' : '') .'>'. $_Lang['stat_main'] .'</option>';
$parse['type'] .= '<option value="2"'. (($type == 2) ? ' selected' : '') .'>'. $_Lang['stat_fleet'].'</option>';
$parse['type'] .= '<option value="3"'. (($type == 3) ? ' selected' : '') .'>'. $_Lang['stat_research'] .'</option>';
$parse['type'] .= '<option value="4"'. (($type == 4) ? ' selected' : '') .'>'. $_Lang['stat_building'] .'</option>';
$parse['type'] .= '<option value="5"'. (($type == 5) ? ' selected' : '') .'>'. $_Lang['stat_defenses'] .'</option>';

if($type == 2)
{
    $Order            = 'fleet_rank';
    $Points            = 'fleet_points';
    $Rank            = 'fleet_rank';
    $OldRank        = 'fleet_old_rank';
    $YesterdayRank    = 'fleet_yesterday_rank';
}
else if($type == 3)
{
    $Order            = 'tech_rank';
    $Points            = 'tech_points';
    $Rank            = 'tech_rank';
    $OldRank        = 'tech_old_rank';
    $YesterdayRank    = 'tech_yesterday_rank';
}
else if($type == 4)
{
    $Order            = 'build_rank';
    $Points            = 'build_points';
    $Rank            = 'build_rank';
    $OldRank        = 'build_old_rank';
    $YesterdayRank    = 'build_yesterday_rank';
}
else if($type == 5)
{
    $Order            = 'defs_rank';
    $Points            = 'defs_points';
    $Rank            = 'defs_rank';
    $OldRank        = 'defs_old_rank';
    $YesterdayRank    = 'defs_yesterday_rank';
}
else
{
        $Order            = 'total_rank';
    $Points            = 'total_points';
    $Rank            = 'total_rank';
    $OldRank        = 'total_old_rank';
    $YesterdayRank    = 'total_yesterday_rank';
}

$SelectCount = doquery("SELECT COUNT(`id_owner`) AS `count` FROM {{table}} WHERE `stat_type` = {$who};", 'statpoints', true);
$SelectCount = $SelectCount['count'];
if($SelectCount > 100)
{
    $LastPage = floor($SelectCount / 100);
    if($SelectCount / 100 == $LastPage)
    {
        $LastPage -= 1;
    }
    for($Page = 0; $Page <= $LastPage; $Page += 1)
    {
        $PageValue = ($Page * 100) + 1;
        if($Page == $LastPage)
        {
            $PageRange = $SelectCount;
        }
        else
        {
            $PageRange = $PageValue + 99;
        }
        $parse['range'] .= '<option value="'. $PageValue .'"'. (($range >= $PageValue AND $range <= $PageRange) ? ' selected' : '') .'>'. $PageValue .'-'. $PageRange .'</option>';
    }
    $parse['HideNoRangeSelector'] = 'display: none;';
}
else
{
    $parse['HideRangeSelector'] = 'display: none;';
    $parse['MaxPlace'] = $SelectCount;
}

$range -= 1;
$start = floor($range / 100) * 100;
if($start == 0)
{
    $start = '0';
}

if($who == 1)
{
    // UserStats
    $IsUser = true;
    $UseKey = 'player';
    $StatHeader = 'stat_playertable_header';
    $NeedenFields = "{{prefix}}users.id, {{prefix}}users.username, {{prefix}}users.old_username, {{prefix}}users.old_username_expire, `ally`.`ally_name`, {{prefix}}users.ally_id, {{prefix}}users.is_banned, {{prefix}}users.is_onvacation, {{prefix}}users.ban_endtime, `users_stats`.*";
    $GetQuery = "SELECT {{table}}.*, {$NeedenFields} FROM {{table}} LEFT JOIN {{prefix}}achievements_stats AS `users_stats` ON `users_stats`.`A_UserID` = {{table}}.id_owner LEFT JOIN {{prefix}}users ON {{prefix}}users.id = {{table}}.id_owner LEFT JOIN {{prefix}}alliance AS `ally` ON `ally`.`id` = {{prefix}}users.`ally_id` WHERE `stat_type` = '1' ORDER BY `{$Order}` ASC LIMIT {$start}, 100;";
    $RowTPL = gettemplate('stat_playertable');
    $ColSpan = 10;
}
else
{
    // AllyStats
    $IsUser = false;
    $UseKey = 'ally';
    $StatHeader = 'stat_alliancetable_header';
    $GetQuery = "SELECT {{table}}.*, {{prefix}}alliance.id AS `ally_id`, {{prefix}}alliance.ally_name, {{prefix}}alliance.ally_tag, {{prefix}}alliance.ally_members FROM {{table}} LEFT JOIN {{prefix}}alliance ON {{prefix}}alliance.id = {{table}}.id_owner WHERE `stat_type` = '2' ORDER BY `{$Order}` ASC LIMIT {$start}, 100;";
    $RowTPL = gettemplate('stat_alliancetable');
    $ColSpan = 7;
}
$parse['stat_header'] = parsetemplate(gettemplate($StatHeader), $parse);
$parse['stat_date'] = '<span class="lime">'.prettyDate('d m Y - H:i:s', $_GameConfig['last_update'], 1).'</span>';

$SQLResult = doquery($GetQuery, 'statpoints');

$start += 1;
if($SQLResult->num_rows > 0)
{
    while($StatRow = $SQLResult->fetch_assoc())
    {
        if($start % 2 == 1)
        {
            $parse['EvenOrOdd'] = 'odd';
        }
        else
        {
            $parse['EvenOrOdd'] = 'even';
        }

        $parse['Position'] = $start;

        // Quick Rank Change
        $rank_new = $StatRow[$Rank];
        $rank_old = $StatRow[$OldRank];
        $rank_yesterday = $StatRow[$YesterdayRank];
        if($rank_old == 0)
        {
            $ranking = 0;
        }
        else
        {
            $ranking = $rank_old - $rank_new;
        }
        if($ranking == 0)
        {
            $parse['quickchange'] = '*';
            $parse['QuickChange_Color'] = 'sky';
        }
        else if($ranking < 0)
        {
            $parse['quickchange'] = $ranking;
            $parse['QuickChange_Color'] = 'red';
        }
        else
        {
            $parse['quickchange'] = '+'.$ranking;
            $parse['QuickChange_Color'] = 'green';
        }
        // Daily Rank Change
        if($rank_yesterday == 0)
        {
            $ranking_daily = 0;
        }
        else
        {
            $ranking_daily = $rank_yesterday - $rank_new;
        }
        if($ranking_daily == 0)
        {
            $parse['daychange'] = '*';
            $parse['DayChange_Color'] = 'sky';
        }
        else if($ranking_daily < 0)
        {
            $parse['daychange'] = $ranking_daily;
            $parse['DayChange_Color'] = 'red';
        }
        else
        {
            $parse['daychange'] = '+'.$ranking_daily;
            $parse['DayChange_Color'] = 'green';
        }
        $parse['Points'] = prettyNumber($StatRow[$Points]);
        $parse['ally_id'] = $StatRow['ally_id'];

        if($IsUser)
        {
            // Parse UserRows
            $parse['player_id'] = $StatRow['id'];
            if($StatRow['id'] == $_User['id'])
            {
                $parse['Name'] = '<span class="lime">'.$StatRow['username'].'</span>';
            }
            else
            {
                $parse['Name'] = $StatRow['username'];
            }
            if($StatRow['old_username_expire'] > $Now)
            {
                $parse['Name'] .= ' <acronym class="point" title="'.$_Lang['Old_username_is'].': '.$StatRow['old_username'].'">(?)</acronym>';
            }
            if($StatRow['is_banned'] == 1)
            {
                $parse['Name'] = '<acronym title="'.$_Lang['User_is_banned'].'" class="red point">'.$parse['Name'].'</acronym>';
            }
            else if(isOnVacation ($StatRow))
            {
                $parse['Name'] = '<acronym title="'.$_Lang['User_is_on_vacations'].'" class="sky point">'.$parse['Name'].'</acronym>';
            }

            $parse['Fig_Won'] = prettyNumber($StatRow['ustat_raids_won']);
            $parse['Fig_Lost'] = prettyNumber($StatRow['ustat_raids_lost']);
            $parse['Fig_Draw'] = prettyNumber($StatRow['ustat_raids_draw']);
            $parse['player_mes'] = '<a href="messages.php?mode=write&amp;uid='.$StatRow['id_owner'].'"><img src="'.$_SkinPath.'img/m.gif" border="0" alt="'.$_Lang['Ecrire'].'"/></a>';
            if($StatRow['ally_id'] == $_User['ally_id'] AND $_User['ally_id'] > 0)
            {
                $parse['player_alliance'] = '<span class="ally">'.$StatRow['ally_name'].'</span>';
            }
            else
            {
                $parse['player_alliance'] = $StatRow['ally_name'];
            }
        }
        else
        {
            // Parse AllyRows
            if($StatRow['ally_id'] == $_User['ally_id'])
            {
                $parse['Name'] = '<span class="lime">'.$StatRow['ally_name'].'</span>';
            }
            else
            {
                $parse['Name'] = $StatRow['ally_name'];
            }

            $parse['ally_tag'] = $StatRow['ally_tag'];
            $parse['ally_members'] = $StatRow['ally_members'];
            if($StatRow['ally_members'] > 0)
            {
                $parse['ally_members_points']= prettyNumber(floor($StatRow[$Points] / $StatRow['ally_members']));
            }
            else
            {
                $parse['ally_members_points']= '0';
            }
        }
        $parse['stat_values'] .= parsetemplate($RowTPL, $parse);
        $start += 1;
    }
}
else
{
    $parse['stat_values'] = '<tr><th colspan="'.$ColSpan.'" class="red">'.$_Lang['No_Rows_Found'].'</th></tr>';
}

$page = parsetemplate(gettemplate('stat_body'), $parse);

display($page, $_Lang['stat_title']);

?>
