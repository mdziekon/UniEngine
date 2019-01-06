<?php

function GetMaxConstructibleElements($Element, $Ressources)
{
    global $_Vars_Prices;

    if($_Vars_Prices[$Element]['metal'] != 0)
    {
        $ResType_1_Needed = $_Vars_Prices[$Element]['metal'];
        $Buildable = floor($Ressources["metal"] / $ResType_1_Needed);
        $MaxElements = $Buildable;
    }

    if($_Vars_Prices[$Element]['crystal'] != 0)
    {
        $ResType_2_Needed = $_Vars_Prices[$Element]['crystal'];
        $Buildable = floor($Ressources["crystal"] / $ResType_2_Needed);
    }
    if(!isset($MaxElements))
    {
        $MaxElements = $Buildable;
    }
    else if($MaxElements > $Buildable)
    {
        $MaxElements = $Buildable;
    }

    if($_Vars_Prices[$Element]['deuterium'] != 0)
    {
        $ResType_3_Needed = $_Vars_Prices[$Element]['deuterium'];
        $Buildable = floor($Ressources["deuterium"] / $ResType_3_Needed);
    }
    if(!isset($MaxElements))
    {
        $MaxElements = $Buildable;
    }
    else if($MaxElements > $Buildable)
    {
        $MaxElements = $Buildable;
    }

    if($_Vars_Prices[$Element]['energy'] != 0)
    {
        $ResType_4_Needed = $_Vars_Prices[$Element]['energy'];
        $Buildable = floor($Ressources["energy_max"] / $ResType_4_Needed);
    }
    if($Buildable < 1)
    {
        $MaxElements = 0;
    }

    return $MaxElements;
}

?>
