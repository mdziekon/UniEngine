<?php

function MissionCaseStayAlly($FleetRow, &$_FleetCache)
{
    global $_Lang;

    $Return = array();
    $Now = time();

    $StartName = $FleetRow['attacking_planet_name'];
    $StartOwner = $FleetRow['attacking_planet_owner'];
    $TargetName = $FleetRow['endtarget_planet_name'];
    $TargetOwner = $FleetRow['endtarget_planet_owner'];

    if($FleetRow['calcType'] == 1)
    {
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission_Time'] = $Now;

        $Message = false;
        $Message['msg_id'] = '032';
        $Message['args'] = array
        (
            ($FleetRow['fleet_start_type'] == 1) ? $_Lang['from_planet'] : $_Lang['from_moon'], $TargetName,
            $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'],
            $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'],
            (($FleetRow['fleet_end_stay'] - $FleetRow['fleet_start_time']) / TIME_HOUR)
        );
        $Message = json_encode($Message);
        Cache_Message($StartOwner, 0, $FleetRow['fleet_start_time'], 5, '002', '002', $Message);

        $Message = false;
        $Message['msg_id'] = '033';
        $Message['args'] = array
        (
            ($FleetRow['fleet_start_type'] == 1) ? $_Lang['from_planet'] : $_Lang['from_moon'], $StartName,
            $FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'],
            $FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'], $FleetRow['fleet_start_planet'],
            ($FleetRow['fleet_end_type'] == 1) ? $_Lang['to_your_planet'] : $_Lang['to_your_moon'], $TargetName,
            $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'],
            $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'],
            (($FleetRow['fleet_end_stay'] - $FleetRow['fleet_start_time']) / TIME_HOUR)
        );
        $Message = json_encode($Message);
        Cache_Message($TargetOwner, 0, $FleetRow['fleet_start_time'], 5, '002', '002', $Message);

        $_FleetCache['fleetRowUpdate'][$FleetRow['fleet_id']]['fleet_mess'] = 1;
        $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['calcCounter'] += 1;
        $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['needUpdate'] = true;
    }

    if($FleetRow['calcType'] == 2)
    {
        $_FleetCache['fleetRowUpdate'][$FleetRow['fleet_id']]['fleet_mess'] = 2;
        $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['calcCounter'] += 1;
        $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['needUpdate'] = true;
    }

    if($FleetRow['calcType'] == 3)
    {
        $Return['FleetsToDelete'][] = $FleetRow['fleet_id'];
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_ComeBack'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_ComeBack_Time'] = $Now;

        $Message = false;
        if($FleetRow['fleet_resource_metal'] == 0 AND $FleetRow['fleet_resource_crystal'] == 0 AND $FleetRow['fleet_resource_deuterium'] == 0)
        {
            $Message['msg_id'] = '035';
            $Message['args'] = array
            (
                ($FleetRow['fleet_start_type'] == 1) ? $_Lang['to_planet'] : $_Lang['to_moon'], $StartName,
                $FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'],
                $FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'], $FleetRow['fleet_start_planet']
            );
        }
        else
        {
            $Message['msg_id'] = '034';
            $Message['args'] = array
            (
                ($FleetRow['fleet_start_type'] == 1) ? $_Lang['to_planet'] : $_Lang['to_moon'], $StartName,
                $FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'],
                $FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'], $FleetRow['fleet_start_planet'],
                prettyNumber($FleetRow['fleet_resource_metal']),
                prettyNumber($FleetRow['fleet_resource_crystal']),
                prettyNumber($FleetRow['fleet_resource_deuterium'])
            );
        }
        $Message = json_encode($Message);
        Cache_Message($StartOwner, 0, $FleetRow['fleet_end_time'], 5, '002', '003', $Message);

        RestoreFleetToPlanet($FleetRow, true, $_FleetCache);

        $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['calcCounter'] += 1;
        $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['needUpdate'] = false;
    }

    if($_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['calcCounter'] == $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['calcCount'])
    {
        if(isset($_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['needUpdate']) && $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['needUpdate'] === true)
        {
            if(!empty($_FleetCache['fleetRowUpdate'][$FleetRow['fleet_id']]))
            {
                foreach($_FleetCache['fleetRowUpdate'][$FleetRow['fleet_id']] as $Key => $Value)
                {
                    $FleetRow[$Key] = $Value;
                }
            }
        }
        if($FleetRow['calcType'] != 3)
        {
            $_FleetCache['updateFleets'][$FleetRow['fleet_id']]['fleet_mess'] = $FleetRow['fleet_mess'];
        }
    }

    return $Return;
}

?>
