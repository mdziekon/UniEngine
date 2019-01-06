<?php

function MissionCheckCalculation($FleetRow, $Now)
{
    $Return = array();
    if($FleetRow['fleet_start_time'] <= $Now && $FleetRow['fleet_mess'] == 0 && $FleetRow['fleet_mission'] != 2)
    {
        $Return['timeSort'][$FleetRow['fleet_start_time'].str_pad($FleetRow['fleet_id'], 20, '0', STR_PAD_LEFT).'01'] = 1;

        if(in_array($FleetRow['fleet_mission'], array(1, 6, 9, 10)))
        {
            // Get TargetPlanet & TargetUser
            $Return['planets'] = $FleetRow['fleet_end_id'];
            if($FleetRow['fleet_target_owner'] > 0)
            {
                $Return['users'] = $FleetRow['fleet_target_owner'];
            }
            if($FleetRow['fleet_mission'] == 9)
            {
                // Get Additional Planets, just in case, if Moon will be destroyed
                $Return['addPlanets'] = $FleetRow['fleet_end_id'];
            }
            if($FleetRow['fleet_mission'] != 10)
            {
                // Get DefendingFleets
                $Return['defFleets'] = $FleetRow['fleet_end_id'];
            }
        }
        if(in_array($FleetRow['fleet_mission'], array(1, 5, 6, 7, 8, 9)))
        {
            // Get UserTasksData
            $Return['taskData'][] = $FleetRow['fleet_owner'];
            if($FleetRow['fleet_mission'] == 9 && $FleetRow['fleet_target_owner'] > 0)
            {
                $Return['taskData'][] = $FleetRow['fleet_target_owner'];
            }
        }
        if($FleetRow['fleet_mission'] == 1 && $FleetRow['fleets_count'] > 0)
        {
            $Temp = explode(',', str_replace('|', '', $FleetRow['fleets_id']));
            $Return['acsFleets'][$FleetRow['fleet_id']] = $Temp;
            // Get FleetsFromACS
        }
        if($FleetRow['fleet_mission'] == 8)
        {
            // Get GalaxyRow
            $Return['galaxy'] = $FleetRow['fleet_end_id_galaxy'];
        }
    }
    if($FleetRow['fleet_end_stay'] > 0 && $FleetRow['fleet_end_stay'] <= $Now && $FleetRow['fleet_mess'] != 2)
    {
        $Return['timeSort'][$FleetRow['fleet_end_stay'].str_pad($FleetRow['fleet_id'], 20, '0', STR_PAD_LEFT).'02'] = 2;
    }
    if($FleetRow['fleet_end_time'] <= $Now)
    {
        if(($FleetRow['fleet_mission'] == 4 && !empty($Return['timeSort'])) || $FleetRow['fleet_mission'] == 10)
        {
            // If this Fleet's order is to "Stay" or it's Missile Attack, don't force to recalculate it
        }
        else
        {
            $Return['timeSort'][$FleetRow['fleet_end_time'].str_pad($FleetRow['fleet_id'], 20, '0', STR_PAD_LEFT).'03'] = 3;
        }
    }
    return $Return;
}

?>
