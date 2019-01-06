<?php

function HandleFullUserUpdate(&$TheUser, &$ThePlanet, &$TheLabPlanet = false, $CurrentTime = false, $ThePlanetFullUpdate = false, $ForMultiUpdate = false)
{
    if($CurrentTime === false)
    {
        $CurrentTime = time();
    }

    if($TheUser['techQueue_EndTime'] > 0 && $TheUser['techQueue_Planet'] != $ThePlanet['id'])
    {
        if(empty($TheLabPlanet))
        {
            $TheLabPlanet = doquery("SELECT * FROM {{table}} WHERE `id` = {$TheUser['techQueue_Planet']} LIMIT 1;", 'planets', true);
        }
        if($ThePlanetFullUpdate === true)
        {
            $NeedUpdate[$ThePlanet['id']] = HandlePlanetUpdate($ThePlanet, $TheUser, $CurrentTime, true);
        }
        else
        {
            $NeedUpdate[$ThePlanet['id']] = HandlePlanetQueue($ThePlanet, $TheUser, $CurrentTime, true);
        }
        $NeedUpdate[$TheLabPlanet['id']] = HandlePlanetQueue($TheLabPlanet, $TheUser, $CurrentTime, true);
        if($ForMultiUpdate === true)
        {
            return $NeedUpdate;
        }
        else
        {
            $UpdateArray = array();
            foreach($NeedUpdate as $PlanetID => $Value)
            {
                if($Value === true)
                {
                    if($ThePlanet['id'] == $PlanetID)
                    {
                        $UpdateArray[] = $ThePlanet;
                    }
                    elseif($TheLabPlanet['id'] == $PlanetID)
                    {
                        $UpdateArray[] = $TheLabPlanet;
                    }
                }
            }
            HandlePlanetUpdate_MultiUpdate(array('planets' => $UpdateArray), $TheUser);
        }
    }
    else
    {
        if($ThePlanetFullUpdate === true)
        {
            $NeedUpdate[$ThePlanet['id']] = HandlePlanetUpdate($ThePlanet, $TheUser, $CurrentTime, $ForMultiUpdate);
        }
        else
        {
            $NeedUpdate[$ThePlanet['id']] = HandlePlanetQueue($ThePlanet, $TheUser, $CurrentTime, $ForMultiUpdate);
        }
        if($ForMultiUpdate === true)
        {
            return $NeedUpdate;
        }
    }
}

?>
