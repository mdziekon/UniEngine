<?php

function GetElementPrice($TheUser, $ThePlanet, $ElementID, $userfactor = true)
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

    $text = $_Lang['Requires'] . ": ";
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
                $text .= "<b style=\"color:red;\"> <t title=\"-".prettyNumber($cost - $ThePlanet[$ResType])."\">";
                $text .= "<span class=\"noresources\">".prettyNumber($cost)."</span></t></b> ";
            }
            else
            {
                $text .= "<b style=\"color:lime;\"> <span class=\"noresources\">".prettyNumber($cost)."</span></b> ";
            }
        }
    }
    return $text;
}

?>
