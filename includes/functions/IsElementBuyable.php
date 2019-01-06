<?php

function IsElementBuyable($TheUser, $ThePlanet, $ElementID, $Incremental = true, $ForDestroy = false, $GetPremiumData = false)
{
    global $_Vars_Prices, $_Vars_GameElements, $_Vars_ElementCategories;

    if(isOnVacation($TheUser))
    {
        return false;
    }

    if($Incremental)
    {
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
    }
    if($ForDestroy === true)
    {
        $level -= 1;
    }

    $array = array('metal', 'crystal', 'deuterium', 'energy_max');

    foreach($array as $ResType)
    {
        if(isset($_Vars_Prices[$ElementID][$ResType]) && $_Vars_Prices[$ElementID][$ResType] > 0)
        {
            if($Incremental)
            {
                $cost[$ResType] = floor($_Vars_Prices[$ElementID][$ResType] * pow($_Vars_Prices[$ElementID]['factor'], $level));
            }
            else
            {
                $cost[$ResType] = floor($_Vars_Prices[$ElementID][$ResType]);
            }

            if($ForDestroy)
            {
                $cost[$ResType] = floor($cost[$ResType] / 2);
            }

            if($cost[$ResType] > $ThePlanet[$ResType])
            {
                return false;
            }
        }
    }

    if($GetPremiumData)
    {
        global $_Vars_PremiumBuildingPrices;
        if(isset($_Vars_PremiumBuildingPrices[$ElementID]) && $_Vars_PremiumBuildingPrices[$ElementID] > $TheUser['darkEnergy'])
        {
            return false;
        }
    }

    return true;
}

?>
