<?php

function GetBuildingTime($TheUser, $ThePlanet, $ElementID)
{
    global $_Vars_Prices, $_Vars_GameElements, $_Vars_ElementCategories, $_GameConfig, $_Vars_BuildingsFixedBuildTime;

    $level = 0;
    if(in_array($ElementID, $_Vars_ElementCategories['tech']))
    {
        if(isset($TheUser[$_Vars_GameElements[$ElementID]]))
        {
            $level = $TheUser[$_Vars_GameElements[$ElementID]];
        }
    }
    else
    {
        if(isset($ThePlanet[$_Vars_GameElements[$ElementID]]))
        {
            $level = $ThePlanet[$_Vars_GameElements[$ElementID]];
        }
    }

    if(in_array($ElementID, $_Vars_ElementCategories['build']))
    {
        $cost_metal = floor($_Vars_Prices[$ElementID]['metal'] * pow($_Vars_Prices[$ElementID]['factor'], $level));
        $cost_crystal = floor($_Vars_Prices[$ElementID]['crystal'] * pow($_Vars_Prices[$ElementID]['factor'], $level));
        $timeBase = (isset($_Vars_BuildingsFixedBuildTime[$ElementID])) ? $_Vars_BuildingsFixedBuildTime[$ElementID] : (($cost_crystal) + ($cost_metal));
        $time = ($timeBase / $_GameConfig['game_speed']) * (1 / ($ThePlanet[$_Vars_GameElements['14']] + 1)) * pow(0.5, $ThePlanet[$_Vars_GameElements['15']]);
        $time = floor($time * 60 * 60);
    }
    else if(in_array($ElementID, $_Vars_ElementCategories['tech']))
    {
        $cost_metal = floor($_Vars_Prices[$ElementID]['metal'] * pow($_Vars_Prices[$ElementID]['factor'], $level));
        $cost_crystal = floor($_Vars_Prices[$ElementID]['crystal'] * pow($_Vars_Prices[$ElementID]['factor'], $level));
        $intergal_lab = $TheUser[$_Vars_GameElements[123]];
        if($intergal_lab < 1)
        {
            $lablevel = $ThePlanet[$_Vars_GameElements[31]];
        }
        else if($intergal_lab >= 1)
        {
            $lablevel = 0;

            $SQLResult_GetPlanetsWithLab = doquery(
                "SELECT `{$_Vars_GameElements[31]}` FROM {{table}} WHERE `id_owner` = {$TheUser['id']} AND `{$_Vars_GameElements[31]}` > 0 ORDER BY `{$_Vars_GameElements[31]}` DESC LIMIT ".($intergal_lab + 1).";",
                'planets'
            );

            if($SQLResult_GetPlanetsWithLab->num_rows > 1)
            {
                while($colony = $SQLResult_GetPlanetsWithLab->fetch_assoc())
                {
                    $lablevel += $colony[$_Vars_GameElements[31]];
                }
            }
            else
            {
                $lablevel = $ThePlanet[$_Vars_GameElements[31]];
            }
        }
        $timeBase = $cost_crystal + $cost_metal;
        $time = ($timeBase / $_GameConfig['game_speed']) / (($lablevel + 1) * 2);
        $time = floor($time * 60 * 60 * (($TheUser['technocrat_time'] > time()) ? 0.8 : 1));
    }
    else if(in_array($ElementID, $_Vars_ElementCategories['defense']))
    {
        $timeBase = $_Vars_Prices[$ElementID]['metal'] + $_Vars_Prices[$ElementID]['crystal'];
        $time = ($timeBase / $_GameConfig['game_speed']) * (1 / ($ThePlanet[$_Vars_GameElements['21']] + 1)) * pow(1 / 2, $ThePlanet[$_Vars_GameElements['15']]);
        $time = floor($time * 60 * 60);
    }
    else if(in_array($ElementID, $_Vars_ElementCategories['fleet']))
    {
        $timeBase = $_Vars_Prices[$ElementID]['metal'] + $_Vars_Prices[$ElementID]['crystal'];
        $time = ($timeBase / $_GameConfig['game_speed']) * (1 / ($ThePlanet[$_Vars_GameElements['21']] + 1)) * pow(1 / 2, $ThePlanet[$_Vars_GameElements['15']]);
        $time = floor($time * 60 * 60);
    }

    if($time < 0)
    {
        $time = 0;
    }

    return $time;
}

?>
