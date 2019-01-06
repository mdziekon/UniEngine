<?php

function HandlePlanetUpdate(&$ThePlanet, &$TheUser, $CurrentTime = false, $ForMultiUpdate = false)
{
    global $HPQ_PlanetUpdatedFields, $HPQ_UserUpdatedFields, $_Vars_GameElements, $UserDev_Log;

    if($CurrentTime === false)
    {
        $CurrentTime = time();
    }

    $NeedPlanetUpdate = HandlePlanetQueue($ThePlanet, $TheUser, $CurrentTime, true);
    $LastPlanetUpdate = $ThePlanet['last_update'];
    $ResourceUpdateResult = PlanetResourceUpdate($TheUser, $ThePlanet, $CurrentTime, true);
    if($ResourceUpdateResult === true)
    {
        $NeedPlanetUpdate = true;
    }
    $BuiltShips = HandleShipyardQueue($TheUser, $ThePlanet, $CurrentTime - $LastPlanetUpdate, $CurrentTime);
    if(!empty($BuiltShips))
    {
        $NeedPlanetUpdate = true;
        $HPQ_PlanetUpdatedFields[] = 'shipyardQueue';
        $HPQ_PlanetUpdatedFields[] = 'shipyardQueue_additionalWorkTime';
        foreach($BuiltShips as $KeyID => $Count)
        {
            $HPQ_PlanetUpdatedFields[] = $_Vars_GameElements[$KeyID];
        }
    }

    if($ForMultiUpdate === true)
    {
        if($NeedPlanetUpdate === true)
        {
            return true;
        }
    }
    else
    {
        if(!empty($HPQ_PlanetUpdatedFields))
        {
            $HPQ_PlanetUpdatedFields = array_unique($HPQ_PlanetUpdatedFields);
            foreach($HPQ_PlanetUpdatedFields as $Value)
            {
                $Query_Update_Arr[] = "`{$Value}` = '{$ThePlanet[$Value]}'";
            }
            $Query_Update = "UPDATE {{table}} SET ".implode(', ', $Query_Update_Arr)." WHERE `id` = {$ThePlanet['id']} LIMIT 1;";
            doquery($Query_Update, 'planets');
            $HPQ_PlanetUpdatedFields = array();
        }
        if(!empty($HPQ_UserUpdatedFields))
        {
            $HPQ_UserUpdatedFields = array_unique($HPQ_UserUpdatedFields);
            $UpdateFields = array();
            foreach($HPQ_UserUpdatedFields as $Value)
            {
                $UpdateFields[] = "`{$Value}` = '{$TheUser[$Value]}'";
            }
            $Query_Update = "UPDATE {{table}} SET ".implode(', ', $UpdateFields)." WHERE `id` = {$TheUser['id']};";
            doquery($Query_Update, 'users');
            $HPQ_UserUpdatedFields = array();
        }
    }
}

?>
