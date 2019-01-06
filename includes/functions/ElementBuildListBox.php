<?php

function ElementBuildListBox($CurrentUser, $CurrentPlanet)
{
    global $_Lang;

    $ElementQueue = explode(';', $CurrentPlanet['shipyardQueue']);
    $NbrePerType = array();
    $NamePerType = array();
    $TimePerType = array();
    $QueueTime = 0;

    foreach($ElementQueue as $ElementLine => $Element)
    {
        if($Element != '')
        {
            $Element = explode(',', $Element);
            $ElementTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $Element[0]);
            $QueueTime += $ElementTime * $Element[1];
            $TimePerType[] = $ElementTime;
            $NamePerType[] = "'".html_entity_decode($_Lang['tech'][$Element[0]])."'";
            $NbrePerType[] = $Element[1];
        }
    }

    $parse = $_Lang;
    $parse['a'] = implode(',', $NbrePerType);
    $parse['b'] = implode(',', $NamePerType);
    $parse['c'] = implode(',', $TimePerType);
    $parse['b_hangar_id_plus'] = $CurrentPlanet['shipyardQueue_additionalWorkTime'];

    $parse['pretty_time_b_hangar'] = pretty_time($QueueTime - $CurrentPlanet['shipyardQueue_additionalWorkTime']);

    return parsetemplate(gettemplate('buildings_script'), $parse);
}

?>
