<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

use UniEngine\Engine\Modules\Flights;

loggedCheck();

if(!isPro())
{
    message($_Lang['ThisPageOnlyForPro'], $_Lang['ProAccount']);
}

includeLang('attackslist');

$AllowedPerPlanet = 5;
$AllowedPerUser = 15;

$BodyTPL = gettemplate('attackslist_body');
$RowTPL = gettemplate('attackslist_row');

$TodayIs = explode('.', date('d.m.Y'));
$TodayTimestamp = mktime(0, 0, 0, $TodayIs[1], $TodayIs[0], $TodayIs[2]);
if($TodayTimestamp <= 0)
{
    $TodayTimestamp = 0;
}

$SelectFields = '`Fleet_End_ID`, `Fleet_End_Owner`, `Fleet_End_ID_Changed`, `Fleet_End_Type_Changed`, `Fleet_End_Galaxy`, `Fleet_End_System`, `Fleet_End_Planet`, `Fleet_End_Type`, `Fleet_ACSID`';

$excludedDestructionReasons = [
    strval(Flights\Enums\FleetDestructionReason::INBATTLE_FIRSTROUND_NODAMAGE),
    strval(Flights\Enums\FleetDestructionReason::DRAW_NOBASH),
    strval(Flights\Enums\FleetDestructionReason::INBATTLE_OTHERROUND_NODAMAGE),
];
$excludedDestructionReasonsStr = implode(', ', $excludedDestructionReasons);

$SQLResult_GetAttacks = doquery(
    "SELECT {$SelectFields} " .
    "FROM {{table}} " .
    "WHERE " .
    "(`Fleet_Time_Start` + `Fleet_Time_ACSAdd`) >= {$TodayTimestamp} AND " .
    "`Fleet_Owner` = {$_User['id']} AND " .
    "`Fleet_Mission` IN (1, 2, 9) AND " .
    "`Fleet_End_Owner_IdleHours` < 168 AND " .
    "`Fleet_ReportID` > 0 AND " .
    "`Fleet_End_Owner` > 0 AND " .
    "`Fleet_Destroyed_Reason` NOT IN ({$excludedDestructionReasonsStr}) " .
    ";",
    'fleet_archive'
);

if($SQLResult_GetAttacks->num_rows > 0)
{
    $GetUsernames = array();
    $GetPlanetnames = array();
    $SkipACSID = array();

    while($Fleet = $SQLResult_GetAttacks->fetch_assoc())
    {
        if($Fleet['Fleet_ACSID'] > 0)
        {
            if(in_array($Fleet['Fleet_ACSID'], $SkipACSID))
            {
                continue;
            }
            else
            {
                $SkipACSID[] = $Fleet['Fleet_ACSID'];
            }
        }
        $Pointer = &$Records[$Fleet['Fleet_End_Owner']];
        if(!isset($Pointer['TotalCount']))
        {
            $Pointer['TotalCount'] = 0;
        }
        $Pointer['TotalCount'] += 1;
        $GetEndID = $Fleet['Fleet_End_ID'];
        if($Fleet['Fleet_End_ID_Changed'] > 0)
        {
            $GetEndID = $Fleet['Fleet_End_ID_Changed'];
        }
        if(!isset($Pointer['Planets'][$GetEndID]['Count']))
        {
            $Pointer['Planets'][$GetEndID]['Count'] = 0;
        }
        $Pointer['Planets'][$GetEndID]['Count'] += 1;

        if(!in_array($Fleet['Fleet_End_Owner'], $GetUsernames))
        {
            $GetUsernames[] = $Fleet['Fleet_End_Owner'];
        }
        if(!in_array($GetEndID, $GetPlanetnames))
        {
            if($Fleet['Fleet_End_Type_Changed'] == 1)
            {
                $Fleet['Fleet_End_Type'] = 1;
            }
            $GetPlanetnames[$GetEndID] = array
            (
                'id' => $GetEndID,
                'galaxy' => $Fleet['Fleet_End_Galaxy'],
                'system' => $Fleet['Fleet_End_System'],
                'planet' => $Fleet['Fleet_End_Planet'],
                'type' => $Fleet['Fleet_End_Type']
            );
        }
    }
}

if(!empty($Records))
{
    $_Lang['PHP_HideNoAttacks'] = 'class="hide"';
    if(!empty($GetUsernames))
    {
        $SQLResult_GetUsernames = doquery(
            "SELECT `user`.`id`, `user`.`username`, `user`.`ally_id`, `ally`.`ally_tag` FROM {{table}} AS `user` LEFT JOIN {{prefix}}alliance AS `ally` ON `ally`.`id` = `user`.`ally_id` WHERE `user`.`id` IN (".implode(', ', $GetUsernames).");",
            'users'
        );

        if($SQLResult_GetUsernames->num_rows > 0)
        {
            while($Username = $SQLResult_GetUsernames->fetch_assoc())
            {
                if($Username['ally_id'] > 0 AND !empty($Username['ally_tag']))
                {
                    $FoundUsernames[$Username['id']] = "{$Username['username']} [<a href=\"ainfo.php?a={$Username['ally_id']}\" target=\"_blank\">{$Username['ally_tag']}</a>]";
                }
                else
                {
                    $FoundUsernames[$Username['id']] = $Username['username'];
                }
            }
        }
        foreach($GetUsernames as $UID)
        {
            if(empty($FoundUsernames[$UID]))
            {
                $Users[$UID] = $_Lang['User_Deleted'];
            }
            else
            {
                $Users[$UID] = $FoundUsernames[$UID];
            }
        }
    }

    if(!empty($GetPlanetnames))
    {
        foreach($GetPlanetnames as $Data)
        {
            $GetPlanetIDs[] = $Data['id'];
        }

        $SQLResult_GetPlanetsNames = doquery(
            "SELECT `id`, `name` FROM {{table}} WHERE `id` IN (".implode(', ', $GetPlanetIDs).");",
            'planets'
        );

        if($SQLResult_GetPlanetsNames->num_rows > 0)
        {
            while($Planet = $SQLResult_GetPlanetsNames->fetch_assoc())
            {
                $FoundPlanets[$Planet['id']] = $Planet['name'];
            }
        }

        $Planets = $GetPlanetnames;
        foreach($GetPlanetnames as $PlanetData)
        {
            if(empty($FoundPlanets[$PlanetData['id']]))
            {
                $Planets[$PlanetData['id']]['name'] = $_Lang['Planet_Deleted'];
            }
            else
            {
                $Planets[$PlanetData['id']]['name'] = $FoundPlanets[$PlanetData['id']];
            }
        }
    }

    $RowsLoop = 0;
    foreach($Records as $AtkUser => $AtkData)
    {
        $This = array();
        if($AtkData['TotalCount'] < $AllowedPerUser)
        {
            $This['AttacksStatus'] = sprintf($_Lang['Bash_StatusOKLeft'], $AllowedPerUser - $AtkData['TotalCount']);
            $This['CountColor'] = 'yellow';
        }
        else if($AtkData['TotalCount'] == $AllowedPerUser)
        {
            $This['AttacksStatus'] = $_Lang['Bash_Status0Left'];
            $This['CountColor'] = 'darkorange';
        }
        else
        {
            $This['AttacksStatus'] = $_Lang['Bash_StatusExceededUser'];
            $This['CountColor'] = 'maroon';
        }
        $_Lang['Rows'][$RowsLoop][] = parsetemplate($RowTPL, array('Username_Planet' => $Users[$AtkUser], 'AttacksCount' => $AtkData['TotalCount'], 'AllowedAttacks' => $AllowedPerUser, 'CountColor' => $This['CountColor'], 'AttacksStatus' => $This['AttacksStatus']));
        foreach($AtkData['Planets'] as $PlanetID => $PlanetAtkCount)
        {
            $This = array();
            if($PlanetAtkCount['Count'] < $AllowedPerPlanet)
            {
                $This['AttacksStatus'] = sprintf($_Lang['Bash_StatusOKLeft'], $AllowedPerPlanet - $PlanetAtkCount['Count']);
                $This['CountColor'] = 'lime';
            }
            else if($PlanetAtkCount['Count'] == $AllowedPerPlanet)
            {
                $This['AttacksStatus'] = $_Lang['Bash_Status0Left'];
                $This['CountColor'] = 'orange';
            }
            else
            {
                $This['AttacksStatus'] = sprintf($_Lang['Bash_StatusExceededPlanet'], ($Planets[$PlanetID]['type'] == 1 ? $_Lang['Bash_Exceeded_Planet'] : $_Lang['Bash_Exceeded_Moon']));
                $This['CountColor'] = 'red';
            }
            $_Lang['Rows'][$RowsLoop][] = parsetemplate($RowTPL, array('PlanetMargin' => '<b class="m10">&#187;</b>', 'Username_Planet' => $Planets[$PlanetID]['name'], 'Planet_Link' => '<a href="galaxy.php?mode=3&amp;galaxy='.$Planets[$PlanetID]['galaxy'].'&amp;system='.$Planets[$PlanetID]['system'].'&amp;planet='.$Planets[$PlanetID]['planet'].'">['.$Planets[$PlanetID]['galaxy'].':'.$Planets[$PlanetID]['system'].':'.$Planets[$PlanetID]['planet'].']</a><b class="'.($Planets[$PlanetID]['type'] == 1 ? 'planet' : 'moon').'"></b>', 'AttacksCount' => $PlanetAtkCount['Count'], 'AllowedAttacks' => $AllowedPerPlanet, 'CountColor' => $This['CountColor'], 'AttacksStatus' => $This['AttacksStatus']));
        }
        $RowsLoop += 1;
    }

    if(!empty($_Lang['Rows']))
    {
        foreach($_Lang['Rows'] as $RowID => $RowSubrows)
        {
            $TempRows[] = implode('', $RowSubrows);
        }
        $_Lang['Rows'] = implode('<tr><th class="fsmall" colspan="3">&nbsp;</th></tr>', $TempRows);
    }

}
else
{
    $_Lang['PHP_HideRecords'] = 'class="hide"';
}

$Page = parsetemplate($BodyTPL, $_Lang);

display($Page, $_Lang['Bash_Title'], true, false);

?>
