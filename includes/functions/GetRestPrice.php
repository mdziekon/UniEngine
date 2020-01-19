<?php

function GetRestPrice($TheUser, $ThePlanet, $ElementID, $userfactor = true)
{
    global $_Vars_Prices, $_Vars_GameElements, $_Vars_ElementCategories, $_Lang;

    $level = 0;
    if($userfactor)
    {
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
    }

    $array = array
    (
        'metal'            => $_Lang["Metal"],
        'crystal'        => $_Lang["Crystal"],
        'deuterium'        => $_Lang["Deuterium"],
        'energy_max'    => $_Lang["Energy"]
    );

    $text = "<br><font color=\"#7f7f7f\">{$_Lang['ResourcesLeft']}: ";
    foreach($array as $ResType => $ResTitle)
    {
        if(isset($_Vars_Prices[$ElementID][$ResType]) && $_Vars_Prices[$ElementID][$ResType] != 0)
        {
            $text .= $ResTitle . ": ";
            if($userfactor)
            {
                $cost = floor($_Vars_Prices[$ElementID][$ResType] * pow($_Vars_Prices[$ElementID]['factor'], $level));
            }
            else
            {
                $cost = floor($_Vars_Prices[$ElementID][$ResType]);
            }
            if($cost > $ThePlanet[$ResType])
            {
                $text .= "<b style=\"color: rgb(127, 95, 96);\">". prettyNumber($ThePlanet[$ResType] - $cost) ."</b> ";
            }
            else
            {
                $text .= "<b style=\"color: rgb(95, 127, 108);\">". prettyNumber($ThePlanet[$ResType] - $cost) ."</b> ";
            }
        }
    }
    $text .= "</font>";

    return $text;
}

?>
