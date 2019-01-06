<?php

function GetElementRessources($Element, $Count)
{
    global $_Vars_Prices;

    $ResType['metal']        = ($_Vars_Prices[$Element]['metal'] * $Count);
    $ResType['crystal']        = ($_Vars_Prices[$Element]['crystal'] * $Count);
    $ResType['deuterium']    = ($_Vars_Prices[$Element]['deuterium'] * $Count);

    return $ResType;
}

?>
