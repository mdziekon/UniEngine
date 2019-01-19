<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_AllowInVacationMode = TRUE;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

$Now = time();
$Hide = ' class="hide"';

includeLang('profile');
includeLang('messageSystem');
includeLang('spyReport');
includeLang('FleetMission_MissileAttack');

$PageTitle = $_Lang['PageTitle'];
$_Lang['skinpath'] = $_SkinPath;

$UserID = (isset($_GET['uid']) ? round($_GET['uid']) : 0);
if($UserID <= 0)
{
    message($_Lang['Error_NoUIDGiven'], $PageTitle);
}
if($UserID == $_User['id'])
{
    $GetUser = $_User;
}
else
{
    $Query_GetUser = '';
    $Query_GetUser .= "SELECT {{table}}.*, `stats`.`total_rank`, `stats`.`total_points` FROM {{table}} ";
    $Query_GetUser .= "LEFT JOIN {{prefix}}statpoints AS `stats` ON {{table}}.`id` = `id_owner` AND `stat_type` = '1' ";
    $Query_GetUser .= "WHERE `id` = {$UserID};";
    $GetUser = doquery($Query_GetUser, 'users', true);
}
if($GetUser['id'] != $UserID)
{
    message($_Lang['Error_UserNoExists'], $PageTitle);
}
$BodyTPL = gettemplate('profile');

$GetMotherPlanet = doquery("SELECT `name` FROM {{table}} WHERE `id` = {$GetUser['id_planet']};", 'planets', true);
if($GetUser['ally_id'] > 0)
{
    $GetAlly = doquery("SELECT * FROM {{table}} WHERE `id` = {$GetUser['ally_id']};", 'alliance', true);
    $GetAlly['ally_ranks'] = json_decode($GetAlly['ally_ranks'], true);
}
$UserIsBuddy = false;
$IsIgnored = false;
if($UserID == $_User['id'])
{
    $UserIsBuddy = true;
    $_Lang['HideIgnore'] = $_Lang['HideReport'] = $_Lang['HideBuddy'] = $_Lang['HideAllyInvite'] = $Hide;
}
else
{
    $CheckBuddy = doquery("SELECT `active` FROM {{table}} WHERE (`sender` = {$_User['id']} AND `owner` = {$UserID}) OR (`owner` = {$_User['id']} AND `sender` = {$UserID}) LIMIT 1;", 'buddy', true);
    if($CheckBuddy['active'] == 1)
    {
        $UserIsBuddy = true;
    }
    $CheckIgnore = doquery("SELECT COUNT(*) AS `Count` FROM {{table}} WHERE `OwnerID` = {$_User['id']} AND `IgnoredID` = {$UserID};", 'ignoresystem', true);
    if($CheckIgnore['Count'] > 0)
    {
        $IsIgnored = true;
    }

    if($_User['ally_id'] <= 0 OR $GetUser['ally_id'] > 0)
    {
        $_Lang['HideAllyInvite'] = $Hide;
    }
}
if(CheckAuth('go'))
{
    if(CheckAuth('sgo'))
    {
        $_Lang['HideReport'] = $Hide;
    }
    $_Lang['Insert_AdminInfoLink'] = '<tr><th colspan="2" class="pad5"><a href="admin/user_info.php?uid='.$UserID.'"><img src="images/user.png" class="icon"/>'.$_Lang['Table_AdminInfo'].'</a></th></tr>';
}

$SelectAchievements = doquery("SELECT * FROM {{table}} WHERE `A_UserID` = {$UserID} LIMIT 1;", 'achievements_stats', true);
$GetStats = doquery("SELECT * FROM {{table}} WHERE `id_owner` = {$UserID} AND `stat_type` = 1;", 'statpoints', true);

if(empty($GetUser['avatar']))
{
    $_Lang['User_Avatar'] = $_EnginePath.'images/noavatar.png';
}
else
{
    $_Lang['User_Avatar'] = $GetUser['avatar'];
    $_Lang['HideNoAvatar'] = $Hide;
}
$_Lang['User_ID'] = $UserID;
$_Lang['User_Username'] = $GetUser['username'];
$_Lang['User_Gamerank'] = $_Lang['msg_const']['senders']['rangs'][GetAuthLabel($GetUser)];
$_Lang['User_Allyname'] = (!empty($GetAlly['ally_name']) ? "<a href=\"alliance.php?mode=ainfo&a={$GetUser['ally_id']}\">{$GetAlly['ally_name']} [{$GetAlly['ally_tag']}]</a>" : '-');
$_Lang['User_Allyrank'] = (!empty($GetAlly['ally_ranks'][$GetUser['ally_rank_id']][0]) ? $GetAlly['ally_ranks'][$GetUser['ally_rank_id']][0] : '-');
$_Lang['User_PlanetGalaxy'] = $GetUser['galaxy'];
$_Lang['User_PlanetSystem'] = $GetUser['system'];
$_Lang['User_PlanetPlanet'] = $GetUser['planet'];
$_Lang['User_MotherPlanet'] = $GetMotherPlanet['name'];
if($UserIsBuddy)
{
    if($GetUser['onlinetime'] >= ($Now - TIME_ONLINE))
    {
        $_Lang['User_ShowBuddyOption'] = $_Lang['Table_Buddy'].' <a class="lime" href="buddy.php">'.$_Lang['Table_Online'].'</a>';
    }
    else
    {
        $_Lang['User_ShowBuddyOption'] = $_Lang['Table_Buddy'].' <a class="red" href="buddy.php">'.$_Lang['Table_Offline'].'</a>';
    }
}
else
{
    $_Lang['User_ShowBuddyOption'] = "<a href=\"buddy.php?cmd=add&uid={$UserID}\"><img src=\"{$_SkinPath}img/b.gif\" style=\"vertical-align: bottom; padding-right: 5px;\"/>{$_Lang['Table_Add2Buddy']}</a>";
}
if(!$IsIgnored)
{
    $_Lang['User_IgnoreLink'] = 'ignoreadd='.$UserID;
    $_Lang['User_IgnoreText'] = $_Lang['Table_Ignore'];
}
else
{
    $_Lang['User_IgnoreLink'] = 'tab=5';
    $_Lang['User_IgnoreText'] = $_Lang['Table_UnIgnore'];
}
if(!isOnVacation($GetUser))
{
    $_Lang['HideVacations'] = $Hide;
}
if($GetUser['is_banned'] != 1)
{
    $_Lang['HideBanned'] = $Hide;
}
$TotalFightsNumber = $SelectAchievements['ustat_raids_won'] + $SelectAchievements['ustat_raids_draw'] + $SelectAchievements['ustat_raids_lost'] + $SelectAchievements['ustat_raids_inAlly'];
$_Lang['User_FightsTotal'] = prettyNumber($TotalFightsNumber);
$_Lang['User_FightsWon'] = prettyNumber($SelectAchievements['ustat_raids_won']);
$_Lang['User_FightsWonACS'] = prettyNumber($SelectAchievements['ustat_raids_acs_won']);
$_Lang['User_FightsDraw'] = prettyNumber($SelectAchievements['ustat_raids_draw']);
$_Lang['User_FightsLost'] = prettyNumber($SelectAchievements['ustat_raids_lost']);
$_Lang['User_FightsInAlly'] = prettyNumber($SelectAchievements['ustat_raids_inAlly']);
if($TotalFightsNumber > 0)
{
    $_Lang['User_FightsWonP'] = intval(($SelectAchievements['ustat_raids_won'] / $TotalFightsNumber) * 100);
    $_Lang['User_FightsWonACSP'] = intval(($SelectAchievements['ustat_raids_acs_won'] / $TotalFightsNumber) * 100);
    $_Lang['User_FightsDrawP'] = intval(($SelectAchievements['ustat_raids_draw'] / $TotalFightsNumber) * 100);
    $_Lang['User_FightsLostP'] = intval(($SelectAchievements['ustat_raids_lost'] / $TotalFightsNumber) * 100);
    $_Lang['User_FightsInAllyP'] = intval(($SelectAchievements['ustat_raids_inAlly'] / $TotalFightsNumber) * 100);
}
else
{
    $_Lang['User_FightsWonP'] = '0';
    $_Lang['User_FightsWonACSP'] = '0';
    $_Lang['User_FightsDrawP'] = '0';
    $_Lang['User_FightsLostP'] = '0';
    $_Lang['User_FightsInAllyP'] = '0';
}
$_Lang['User_MissileAttacks'] = prettyNumber($SelectAchievements['ustat_raids_missileAttack']);
$_Lang['User_MoonsCreated'] = prettyNumber($SelectAchievements['ustat_moons_created']);
$_Lang['User_MoonsDestroyed'] = prettyNumber($SelectAchievements['ustat_moons_destroyed']);

$_Lang['User_ShotDownUnits'] = 0;
$_Lang['User_LostUnits'] = 0;
$_Lang['User_WarBalance'] = 0;

if(!empty($SelectAchievements))
{
    foreach($SelectAchievements as $Key => $Value)
    {
        if(strstr($Key, 'destroyed_') !== false)
        {
            $UnitID = str_replace('destroyed_', '', $Key);
            $_Lang['User_ShotDownUnits'] += $Value;
            $_Lang['User_WarBalance'] += (($_Vars_Prices[$UnitID]['metal'] + $_Vars_Prices[$UnitID]['crystal'] + $_Vars_Prices[$UnitID]['deuterium']) * $Value) / 1000;
        }
        else if(strstr($Key, 'lost_') !== false)
        {
            $UnitID = str_replace('lost_', '', $Key);
            $_Lang['User_LostUnits'] += $Value;
            $_Lang['User_WarBalance'] -= (($_Vars_Prices[$UnitID]['metal'] + $_Vars_Prices[$UnitID]['crystal'] + $_Vars_Prices[$UnitID]['deuterium']) * $Value) / 1000;
        }
    }
}
if($_Lang['User_WarBalance'] == 0)
{
    $_Lang['User_WarBalanceColor'] = 'orange';
    $_Lang['User_WarBalance'] = prettyNumber($_Lang['User_WarBalance']);
}
else if($_Lang['User_WarBalance'] > 0)
{
    $_Lang['User_WarBalanceColor'] = 'lime';
    $_Lang['User_WarBalance'] = '+ '.prettyNumber($_Lang['User_WarBalance']);
}
else
{
    $_Lang['User_WarBalanceColor'] = 'red';
    $_Lang['User_WarBalance'] = '- '.prettyNumber($_Lang['User_WarBalance'] * -1);
}
$_Lang['User_ShotDownUnits'] = prettyNumber($_Lang['User_ShotDownUnits']);
$_Lang['User_LostUnits'] = prettyNumber($_Lang['User_LostUnits']);

if($GetStats['total_rank'] <= 0)
{
    $_Lang['User_StatRange'] = '0';
    $_Lang['User_GlobalPosition']= $_Lang['Table_PosZero'];
}
else
{
    $_Lang['User_StatRange'] = $GetStats['total_rank'];
    $_Lang['User_GlobalPosition'] = prettyNumber($GetStats['total_rank']);
}
$_Lang['User_Points']= prettyNumber($GetStats['total_points']);

if(MORALE_ENABLED)
{
    $_Lang['User_MoralePoints'] = prettyNumber($GetUser['morale_points']);
}
else
{
    $_Lang['Insert_MoraleHide'] = 'hide';
}

if($GetStats['fleet_rank'] <= 0)
{
    $_Lang['User_FleetsRange'] = '0';
    $_Lang['User_FleetsPosition'] = $_Lang['Table_PosZero'];
}
else
{
    $_Lang['User_FleetsRange'] = $GetStats['fleet_rank'];
    $_Lang['User_FleetsPosition'] = prettyNumber($GetStats['fleet_rank']);
}
$_Lang['User_PointsFleets'] = prettyNumber($GetStats['fleet_points']);

if($GetStats['tech_rank'] <= 0)
{
    $_Lang['User_ResearchRange'] = '0';
    $_Lang['User_ResearchPosition'] = $_Lang['Table_PosZero'];
}
else
{
    $_Lang['User_ResearchRange'] = $GetStats['tech_rank'];
    $_Lang['User_ResearchPosition'] = prettyNumber($GetStats['tech_rank']);
}
$_Lang['User_PointsResearch'] = prettyNumber($GetStats['tech_points']);

if($GetStats['build_rank'] <= 0)
{
    $_Lang['User_BuildingsRange'] = '0';
    $_Lang['User_BuildingsPosition'] = $_Lang['Table_PosZero'];
}
else
{
    $_Lang['User_BuildingsRange'] = $GetStats['build_rank'];
    $_Lang['User_BuildingsPosition'] = prettyNumber($GetStats['build_rank']);
}
$_Lang['User_PointsBuildings'] = prettyNumber($GetStats['build_points']);

if($GetStats['defs_rank'] <= 0)
{
    $_Lang['User_DefenseRange'] = '0';
    $_Lang['User_DefensePosition'] = $_Lang['Table_PosZero'];
}
else
{
    $_Lang['User_DefenseRange'] = $GetStats['defs_rank'];
    $_Lang['User_DefensePosition'] = prettyNumber($GetStats['defs_rank']);
}
$_Lang['User_PointsDefense'] = prettyNumber($GetStats['defs_points']);

$Page = parsetemplate($BodyTPL, $_Lang);

display($Page, $PageTitle, false);

?>
