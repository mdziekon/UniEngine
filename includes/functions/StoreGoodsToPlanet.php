<?php

function StoreGoodsToPlanet($FleetRow, $Start = false, &$_FleetCache = array())
{
    $UpdatePlanet = array();
    if($FleetRow['fleet_resource_metal'] > 0)
    {
        $UpdatePlanet['metal'] = $FleetRow['fleet_resource_metal'];
    }
    if($FleetRow['fleet_resource_crystal'] > 0)
    {
        $UpdatePlanet['crystal'] = $FleetRow['fleet_resource_crystal'];
    }
    if($FleetRow['fleet_resource_deuterium'] > 0)
    {
        $UpdatePlanet['deuterium'] = $FleetRow['fleet_resource_deuterium'];
    }

    if(!empty($UpdatePlanet))
    {
        if($Start === true)
        {
            $PlanetID = $FleetRow['fleet_start_id'];
        }
        else
        {
            $PlanetID = $FleetRow['fleet_end_id'];
        }

        if(!empty($_FleetCache['planets'][$PlanetID]))
        {
            global $HPQ_PlanetUpdatedFields;
            $_FleetCache['updatePlanets'][$PlanetID] = true;
            foreach($UpdatePlanet as $Key => $Value)
            {
                $HPQ_PlanetUpdatedFields[] = $Key;
                $_FleetCache['planets'][$PlanetID][$Key] += $Value;
            }
        }
        else
        {
            foreach($UpdatePlanet as $Key => $Value)
            {
                if(empty($_FleetCache['addToPlanets']['fields']) OR !in_array($Key, $_FleetCache['addToPlanets']['fields']))
                {
                    $_FleetCache['addToPlanets']['fields'][] = $Key;
                }
                if(!isset($_FleetCache['addToPlanets']['data'][$PlanetID][$Key]))
                {
                    $_FleetCache['addToPlanets']['data'][$PlanetID][$Key] = 0;
                }
                $_FleetCache['addToPlanets']['data'][$PlanetID][$Key] += $Value;
            }
        }

        return true;
    }
    return false;
}

?>
