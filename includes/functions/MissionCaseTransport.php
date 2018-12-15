<?php

function MissionCaseTransport($FleetRow, &$_FleetCache)
{
    global $_Lang, $UserDev_Log;

    $Now = time();

    $StartName = $FleetRow['attacking_planet_name'];
    $StartOwner = $FleetRow['attacking_planet_owner'];

    if($FleetRow['calcType'] == 1)
    {
        $TargetName = $FleetRow['endtarget_planet_name'];
        $TargetOwner = $FleetRow['endtarget_planet_owner'];

        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission_Time'] = $Now;

        StoreGoodsToPlanet($FleetRow, false, $_FleetCache);

        $Message['msg_id'] = '030';
        $Message['args'] = array
        (
            ($FleetRow['fleet_end_type'] == 1) ? $_Lang['to_planet'] : $_Lang['to_moon'], $TargetName,
            $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'],
            $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'],
            prettyNumber($FleetRow['fleet_resource_metal']),
            prettyNumber($FleetRow['fleet_resource_crystal']),
            prettyNumber($FleetRow['fleet_resource_deuterium'])
        );
        $Message = json_encode($Message);
        Cache_Message($StartOwner, 0, $FleetRow['fleet_start_time'], 5, '002', '002', $Message);

        if($TargetOwner != $StartOwner)
        {
            $Message = false;
            $Message['msg_id'] = '031';
            $Message['args'] = array
            (
                ($FleetRow['fleet_start_type'] == 1) ? $_Lang['from_planet'] : $_Lang['from_moon'], $StartName,
                $FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'],
                $FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'], $FleetRow['fleet_start_planet'],
                ($FleetRow['fleet_end_type'] == 1) ? $_Lang['to_your_planet'] : $_Lang['to_your_moon'], $TargetName,
                $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'],
                $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'],
                prettyNumber($FleetRow['fleet_resource_metal']),
                prettyNumber($FleetRow['fleet_resource_crystal']),
                prettyNumber($FleetRow['fleet_resource_deuterium'])
            );
            $Message = json_encode($Message);
            Cache_Message($TargetOwner, 0, $FleetRow['fleet_start_time'], 5, '002', '002', $Message);
        }

        if($FleetRow['fleet_resource_metal'] == 0)
        {
            $FleetRow['fleet_resource_metal'] = '0';
        }
        if($FleetRow['fleet_resource_crystal'] == 0)
        {
            $FleetRow['fleet_resource_crystal'] = '0';
        }
        if($FleetRow['fleet_resource_deuterium'] == 0)
        {
            $FleetRow['fleet_resource_deuterium'] = '0';
        }

        $UserDev_Log[] = array('UserID' => $FleetRow['fleet_owner'], 'PlanetID' => $FleetRow['fleet_end_id'], 'Date' => $FleetRow['fleet_start_time'], 'Place' => 19, 'Code' => '1', 'ElementID' => '0', 'AdditionalData' => "M,{$FleetRow['fleet_resource_metal']};C,{$FleetRow['fleet_resource_crystal']};D,{$FleetRow['fleet_resource_deuterium']}");
        if($FleetRow['fleet_owner'] != $TargetOwner)
        {
            $UserDev_Log[] = array('UserID' => $TargetOwner, 'PlanetID' => $FleetRow['fleet_end_id'], 'Date' => $FleetRow['fleet_start_time'], 'Place' => 19, 'Code' => '2', 'ElementID' => '0', 'AdditionalData' => "M,{$FleetRow['fleet_resource_metal']};C,{$FleetRow['fleet_resource_crystal']};D,{$FleetRow['fleet_resource_deuterium']}");
        }

        if($_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['calcCount'] == 1)
        {
            $CachePointer = &$_FleetCache['updateFleets'][$FleetRow['fleet_id']];
            $CachePointer['fleet_mess'] = 1;
            $CachePointer['fleet_resource_metal'] = -$FleetRow['fleet_resource_metal'];
            $CachePointer['fleet_resource_crystal'] = -$FleetRow['fleet_resource_crystal'];
            $CachePointer['fleet_resource_deuterium'] = -$FleetRow['fleet_resource_deuterium'];
        }
        else
        {
            $_FleetCache['fleetRowUpdate'][$FleetRow['fleet_id']] = array
            (
                'fleet_resource_metal'        => 0,
                'fleet_resource_crystal'    => 0,
                'fleet_resource_deuterium'    => 0,
            );
            $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['needUpdate'] = false;
        }
    }

    if($FleetRow['calcType'] == 3)
    {
        if(!empty($_FleetCache['fleetRowUpdate'][$FleetRow['fleet_id']]))
        {
            foreach($_FleetCache['fleetRowUpdate'][$FleetRow['fleet_id']] as $Key => $Value)
            {
                $FleetRow[$Key] = $Value;
            }
        }

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
    }

    return $Return;
}

?>
