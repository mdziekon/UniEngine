<?php

function MissionCaseStay($FleetRow, &$_FleetCache)
{
    global $_Lang, $_User, $GlobalParsedTasks;

    $Return = array();
    $DoFleetDelete = false;
    $LandingTime = 0;
    $Now = time();

    if($FleetRow['calcType'] == 1)
    {
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission_Time'] = $Now;

        $FleetArray = explode(';', $FleetRow['fleet_array']);
        foreach($FleetArray as $ShipRow)
        {
            $ShipRow = explode(',', $ShipRow);
            $Ships[] = $_Lang['tech'][$ShipRow[0]].' (<b>'.prettyNumber($ShipRow[1]).'</b>)';
        }

        $Message['msg_id'] = '009';
        $Message['args'] = array
        (
            ($FleetRow['fleet_end_type'] == 1) ? $_Lang['to_planet'] : $_Lang['to_moon'], $FleetRow['endtarget_planet_name'],
            $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'],
            $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'],
            implode(', ', $Ships), $_Lang['Metal'], prettyNumber($FleetRow['fleet_resource_metal']), $_Lang['Crystal'],
            prettyNumber($FleetRow['fleet_resource_crystal']), $_Lang['Deuterium'], prettyNumber($FleetRow['fleet_resource_deuterium'])
        );
        $Message = json_encode($Message);
        Cache_Message($FleetRow['fleet_owner'], 0, $FleetRow['fleet_start_time'], 5, '003', '004', $Message);

        RestoreFleetToPlanet($FleetRow, false, $_FleetCache);
        $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['isDestroyed'] = true;
        $DoFleetDelete = true;
        $LandingTime = $FleetRow['fleet_start_time'];
    }

    if($FleetRow['calcType'] == 3 && (!isset($_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['isDestroyed']) || $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['isDestroyed'] !== true))
    {
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_ComeBack'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_ComeBack_Time'] = $Now;

        $Message = false;
        $Message['msg_id'] = '010';
        $Message['args'] = array
        (
            ($FleetRow['fleet_end_type'] == 1) ? $_Lang['to_planet'] : $_Lang['to_moon'], $FleetRow['attacking_planet_name'],
            $FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'],
            $FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'], $FleetRow['fleet_start_planet'],
            $_Lang['Metal'], prettyNumber($FleetRow['fleet_resource_metal']),
            $_Lang['Crystal'], prettyNumber($FleetRow['fleet_resource_crystal']),
            $_Lang['Deuterium'], prettyNumber($FleetRow['fleet_resource_deuterium'])
        );
        $Message = json_encode($Message);
        Cache_Message($FleetRow['fleet_owner'], 0, $FleetRow['fleet_end_time'], 5, '003', '003', $Message);

        RestoreFleetToPlanet($FleetRow, true, $_FleetCache);
        $DoFleetDelete = true;
        $LandingTime = $FleetRow['fleet_end_time'];
    }

    if($DoFleetDelete === true)
    {
        $Return['FleetsToDelete'][] = $FleetRow['fleet_id'];
        // Check Task
        if($FleetRow['fleet_start_type'] == 3 AND $FleetRow['fleet_end_type'] == 3 AND ($LandingTime - $FleetRow['fleet_send_time']) > 21600)
        {
            if($_User['id'] == $FleetRow['fleet_owner'])
            {
                $CurrentUser = $_User;
            }
            else
            {
                if(empty($GlobalParsedTasks[$FleetRow['fleet_owner']]['tasks_done_parsed']))
                {
                    $GetUserTasksDone['tasks_done'] = $_FleetCache['userTasks'][$FleetRow['fleet_owner']];
                    Tasks_CheckUservar($GetUserTasksDone);
                    $GlobalParsedTasks[$FleetRow['fleet_owner']] = $GetUserTasksDone;
                }
                $CurrentUser = $GlobalParsedTasks[$FleetRow['fleet_owner']];
                $CurrentUser['id'] = $FleetRow['fleet_owner'];
            }
            Tasks_TriggerTask($CurrentUser, 'INTRODUCTION_FLEETSAVE_END');
        }
    }

    return $Return;
}

?>
