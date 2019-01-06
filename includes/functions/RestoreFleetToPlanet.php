<?php

function RestoreFleetToPlanet($FleetRow, $Start = true, &$_FleetCache = array())
{
    global $_Vars_GameElements, $UserDev_Log;

    $UpdatePlanet = array();
    $FleetArray = explode(';', $FleetRow['fleet_array']);
    foreach($FleetArray as $ThisShip)
    {
        $ThisShip = explode(',', $ThisShip);
        if($ThisShip[1] > 0)
        {
            $UpdatePlanet[$_Vars_GameElements[$ThisShip[0]]] = $ThisShip[1];
            $DevArray[] = "{$ThisShip[0]},{$ThisShip[1]}";
        }
    }

    if($FleetRow['fleet_resource_metal'] > 0)
    {
        $DevArray[] = 'M,'.$FleetRow['fleet_resource_metal'];
        $UpdatePlanet['metal'] = $FleetRow['fleet_resource_metal'];
    }
    if($FleetRow['fleet_resource_crystal'] > 0)
    {
        $DevArray[] = 'C,'.$FleetRow['fleet_resource_crystal'];
        $UpdatePlanet['crystal'] = $FleetRow['fleet_resource_crystal'];
    }
    if($FleetRow['fleet_resource_deuterium'] > 0)
    {
        $DevArray[] = 'D,'.$FleetRow['fleet_resource_deuterium'];
        $UpdatePlanet['deuterium'] = $FleetRow['fleet_resource_deuterium'];
    }

    if(!empty($UpdatePlanet))
    {
        if($Start === true)
        {
            $PlanetID = $FleetRow['fleet_start_id'];
            $DevLogTime = $FleetRow['fleet_end_time'];
            $SetCode = '1';
        }
        else
        {
            $PlanetID = $FleetRow['fleet_end_id'];
            $DevLogTime = $FleetRow['fleet_start_time'];
            $SetCode = '2';
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

        $UserDev_Log[] = array
        (
            'UserID' => $FleetRow['fleet_owner'], 'PlanetID' => $PlanetID, 'Date' => $DevLogTime, 'Place' => 21,
            'Code' => $SetCode, 'ElementID' => $FleetRow['fleet_id'], 'AdditionalData' => implode(';', $DevArray)
        );

        return true;
    }
    return false;
}

?>
