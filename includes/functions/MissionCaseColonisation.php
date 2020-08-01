<?php

use UniEngine\Engine\Modules\Flights;

function MissionCaseColonisation($FleetRow, &$_FleetCache)
{
    global $_Lang, $UserDev_Log, $_User, $GlobalParsedTasks, $UserTasksUpdate, $_Vars_TasksData;

    $fleetHasBeenDeleted = false;
    $UpdateFleet = array();

    $Now = time();

    if($FleetRow['calcType'] == 1)
    {
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission_Time'] = $Now;

        $Query_CheckGalaxy = '';
        $Query_CheckGalaxy .= "SELECT `galaxy_id` FROM {{table}} WHERE ";
        $Query_CheckGalaxy .= "`galaxy` = {$FleetRow['fleet_end_galaxy']} AND `system` = {$FleetRow['fleet_end_system']} AND `planet` = {$FleetRow['fleet_end_planet']} ";
        $Query_CheckGalaxy .= "LIMIT 1; -- MISSION COLONIZATION [Q01]";
        $CheckGalaxy = doquery($Query_CheckGalaxy, 'galaxy', true);
        if($CheckGalaxy['galaxy_id'] <= 0)
        {
            $Query_GetUserData = '';
            $Query_GetUserData .= "SELECT COUNT(*) AS `Data`, 1 AS `Type` FROM `{{prefix}}planets` WHERE `id_owner` = {$FleetRow['fleet_owner']} AND `planet_type` = 1 ";
            if($_User['id'] != $FleetRow['fleet_owner'])
            {
                if(empty($_FleetCache['users'][$FleetRow['fleet_owner']]))
                {
                    $Query_GetUserData .= "UNION ";
                    $Query_GetUserData .= "SELECT `additional_planets` AS `Data`, 2 AS `Type` FROM `{{prefix}}users` WHERE `id` = {$FleetRow['fleet_owner']}";
                }
                else
                {
                    $MaxPlanets = MAX_PLAYER_PLANETS + $_FleetCache['users'][$FleetRow['fleet_owner']]['additional_planets'];
                }
            }
            else
            {
                $MaxPlanets = MAX_PLAYER_PLANETS + $_User['additional_planets'];
            }
            $Query_GetUserData .= "; -- MISSION COLONIZATION [Q02]";
            $GetUserData = doquery($Query_GetUserData, '');
            while($FetchData = $GetUserData->fetch_assoc())
            {
                if($FetchData['Type'] == 1)
                {
                    $UserPlanetsCount = $FetchData['Data'];
                }
                elseif($FetchData['Type'] == 2)
                {
                    $MaxPlanets = MAX_PLAYER_PLANETS + $FetchData['Data'];
                }
            }

            if($UserPlanetsCount >= $MaxPlanets)
            {
                $Message = false;
                $Message['msg_id'] = '018';
                $Message['args'] = array($FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'], $MaxPlanets);
                $Message = json_encode($Message);
                Cache_Message($FleetRow['fleet_owner'], 0, $FleetRow['fleet_start_time'], 4, '003', '008', $Message);

                $UpdateFleet['fleet_mess'] = 1;
            }
            else
            {
                $NewPlanetRecord = CreateOnePlanetRecord
                (
                    $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'],
                    $FleetRow['fleet_owner'], $_Lang['sys_colo_defaultname'], false,
                    array
                    (
                        'metal' => $FleetRow['fleet_resource_metal'],
                        'crystal' => $FleetRow['fleet_resource_crystal'],
                        'deuterium' => $FleetRow['fleet_resource_deuterium']
                    ), true, true
                );
                if($NewPlanetRecord !== false)
                {
                    $Message = false;
                    $Message['msg_id'] = '019';
                    $Message['args'] = array($FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet']);
                    $Message = json_encode($Message);
                    Cache_Message($FleetRow['fleet_owner'], 0, $FleetRow['fleet_start_time'], 4, '003', '008', $Message);

                    if($FleetRow['fleet_amount'] == 1)
                    {
                        $fleetHasBeenDeleted = true;
                        $Return['FleetsToDelete'][] = $FleetRow['fleet_id'];
                        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Destroyed'] = true;
                        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Destroyed_Reason'] = Flights\Enums\FleetDestructionReason::COLONIZATION;
                        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Info_HasLostShips'] = true;
                        $SetCode = '1';
                    }
                    else
                    {
                        $FleetRebuild = explode(';', $FleetRow['fleet_array']);
                        foreach($FleetRebuild as $Index => &$Ships)
                        {
                            if(!empty($Ships))
                            {
                                $ExplodeShips = explode(',', $Ships);
                                if($ExplodeShips[0] == 208)
                                {
                                    $ExplodeShips[1] -= 1;
                                    if($ExplodeShips[1] > 0)
                                    {
                                        $Ships = implode(',', $ExplodeShips);
                                        break;
                                    }
                                    else
                                    {
                                        unset($FleetRebuild[$Index]);
                                    }
                                }
                            }
                            else
                            {
                                unset($FleetRebuild[$Index]);
                            }
                        }
                        $NewFleet = implode(';', $FleetRebuild);

                        $UpdateFleet['fleet_mess'] = 1;
                        $UpdateFleet['fleet_array'] = $NewFleet;
                        $UpdateFleet['fleet_amount'] = $FleetRow['fleet_amount'] - 1;
                        $UpdateFleet['fleet_resource_metal'] = -$FleetRow['fleet_resource_metal'];
                        $UpdateFleet['fleet_resource_crystal'] = -$FleetRow['fleet_resource_crystal'];
                        $UpdateFleet['fleet_resource_deuterium'] = -$FleetRow['fleet_resource_deuterium'];

                        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Array_Changes'] = '"D;208,1"';
                        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Info_HasLostShips'] = true;

                        $SetCode = '2';
                    }

                    // Trigger Tasks Check
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
                    Tasks_TriggerTask($CurrentUser, 'COLONIZE_PLANET', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($CurrentUser)
                        {
                            global $UserTasksUpdate;
                            if(!empty($UserTasksUpdate[$CurrentUser['id']]['status'][$ThisCat][$TaskID][$JobID]))
                            {
                                $CurrentUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$CurrentUser['id']]['status'][$ThisCat][$TaskID][$JobID];
                            }
                            if(!isset($CurrentUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID]))
                            {
                                $CurrentUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = 0;
                            }
                            $CurrentUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] += 1;
                            if($CurrentUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] < $JobArray['count'])
                            {
                                $UserTasksUpdate[$CurrentUser['id']]['status'][$ThisCat][$TaskID][$JobID] = $CurrentUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID];
                                return true;
                            }
                        }
                    ));

                    $UserDev_Log[] = array('UserID' => $FleetRow['fleet_owner'], 'PlanetID' => $NewPlanetRecord['ID'], 'Date' => $FleetRow['fleet_start_time'], 'Place' => 14, 'Code' => $SetCode, 'ElementID' => $FleetRow['fleet_id'], 'AdditionalData' => "M,{$NewPlanetRecord['metal']};C,{$NewPlanetRecord['crystal']};D,{$NewPlanetRecord['deuterium']};T,{$NewPlanetRecord['temp_max']}");
                }
                else
                {
                    $Message = false;
                    $Message['msg_id'] = '020';
                    $Message['args'] = array($FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet']);
                    $Message = json_encode($Message);
                    Cache_Message($FleetRow['fleet_owner'], 0, $FleetRow['fleet_start_time'], 4, '003', '008', $Message);

                    $UpdateFleet['fleet_mess'] = 1;
                }
            }
        }
        else
        {
            $Message = false;
            $Message['msg_id'] = '021';
            $Message['args'] = array($FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet']);
            $Message = json_encode($Message);
            Cache_Message($FleetRow['fleet_owner'], 0, $FleetRow['fleet_end_time'], 4, '003', '008', $Message);

            $UpdateFleet['fleet_mess'] = 1;
        }

        if($fleetHasBeenDeleted !== true)
        {
            if($_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['calcCount'] == 1)
            {
                // Create UpdateFleet record for $_FleetCache
                $CachePointer = &$_FleetCache['updateFleets'][$FleetRow['fleet_id']];
                $CachePointer['fleet_array'] = $UpdateFleet['fleet_array'];
                $CachePointer['fleet_amount'] = $UpdateFleet['fleet_amount'];
                $CachePointer['fleet_mess'] = $UpdateFleet['fleet_mess'];
                if(!isset($CachePointer['fleet_resource_metal']))
                {
                    $CachePointer['fleet_resource_metal'] = 0;
                }
                if(!isset($CachePointer['fleet_resource_crystal']))
                {
                    $CachePointer['fleet_resource_crystal'] = 0;
                }
                if(!isset($CachePointer['fleet_resource_deuterium']))
                {
                    $CachePointer['fleet_resource_deuterium'] = 0;
                }
                $CachePointer['fleet_resource_metal'] += $UpdateFleet['fleet_resource_metal'];
                $CachePointer['fleet_resource_crystal'] += $UpdateFleet['fleet_resource_crystal'];
                $CachePointer['fleet_resource_deuterium'] += $UpdateFleet['fleet_resource_deuterium'];
            }
            else
            {
                foreach($UpdateFleet as $Key => $Data)
                {
                    $_FleetCache['fleetRowUpdate'][$FleetRow['fleet_id']][$Key] = $Data;
                }
            }
        }
        else
        {
            $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['isDestroyed'] = true;
        }
    }

    if($FleetRow['calcType'] == 3 && (!isset($_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['isDestroyed']) || $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['isDestroyed'] !== true))
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
        RestoreFleetToPlanet($FleetRow, true, $_FleetCache);
    }

    return $Return;
}

?>
