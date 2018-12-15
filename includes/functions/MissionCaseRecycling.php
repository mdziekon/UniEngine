<?php

function MissionCaseRecycling($FleetRow, &$_FleetCache)
{
    global $_Vars_Prices, $UserDev_Log, $_User, $GlobalParsedTasks;

    $Return = array();
    $Now = time();

    if($FleetRow['calcType'] == 1)
    {
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission_Time'] = $Now;

        $TargetGalaxy = $_FleetCache['galaxy'][$FleetRow['fleet_end_id_galaxy']];
        if($TargetGalaxy['metal'] > 0 OR $TargetGalaxy['crystal'] > 0)
        {
            $RecyclerCapacity = 0;
            $OtherFleetCapacity = 0;
            $FleetRecord = String2Array($FleetRow['fleet_array']);
            foreach($FleetRecord as $ShipID => $ShipCount)
            {
                if($ShipID == 209)
                {
                    $RecyclerCapacity += $_Vars_Prices[$ShipID]['capacity'] * $ShipCount;
                }
                else
                {
                    $OtherFleetCapacity += $_Vars_Prices[$ShipID]['capacity'] * $ShipCount;
                }
            }

            $IncomingFleetGoods = $FleetRow['fleet_resource_metal'] + $FleetRow['fleet_resource_crystal'] + $FleetRow['fleet_resource_deuterium'];
            if($IncomingFleetGoods > $OtherFleetCapacity)
            {
                $RecyclerCapacity -= ($IncomingFleetGoods - $OtherFleetCapacity);
            }

            // Storage Management here
            $MaxStorage_Metal = $MaxStorage_Crystal = $RecyclerCapacity / 2;
            if($TargetGalaxy['metal'] < $MaxStorage_Metal)
            {
                $MaxStorage_Crystal += ($MaxStorage_Metal - $TargetGalaxy['metal']);
            }
            if($TargetGalaxy['crystal'] < $MaxStorage_Crystal)
            {
                $MaxStorage_Metal += ($MaxStorage_Crystal - $TargetGalaxy['crystal']);
            }
            if($TargetGalaxy['metal'] > $MaxStorage_Metal)
            {
                $RecycledGoods['metal'] = $MaxStorage_Metal;
            }
            else
            {
                $RecycledGoods['metal'] = $TargetGalaxy['metal'];
            }
            if($TargetGalaxy['crystal'] > $MaxStorage_Crystal)
            {
                $RecycledGoods['crystal'] = $MaxStorage_Crystal;
            }
            else
            {
                $RecycledGoods['crystal'] = $TargetGalaxy['crystal'];
            }

            if(!isset($_FleetCache['galaxy'][$FleetRow['fleet_end_id_galaxy']]['metal']))
            {
                $_FleetCache['galaxy'][$FleetRow['fleet_end_id_galaxy']]['metal'] = 0;
            }
            if(!isset($_FleetCache['galaxy'][$FleetRow['fleet_end_id_galaxy']]['crystal']))
            {
                $_FleetCache['galaxy'][$FleetRow['fleet_end_id_galaxy']]['crystal'] = 0;
            }
            $_FleetCache['galaxy'][$FleetRow['fleet_end_id_galaxy']]['metal'] -= $RecycledGoods['metal'];
            $_FleetCache['galaxy'][$FleetRow['fleet_end_id_galaxy']]['crystal'] -= $RecycledGoods['crystal'];
            $_FleetCache['galaxy'][$FleetRow['fleet_end_id_galaxy']]['updated'] = true;
            $_FleetCache['updated']['galaxy'] = true;

            $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_End_Res_Metal'] = $RecycledGoods['metal'];
            $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_End_Res_Crystal'] = $RecycledGoods['crystal'];

            // Trigger Tasks Check
            if($_User['id'] == $FleetRow['fleet_owner'])
            {
                $ThisTaskUser = $_User;
            }
            else
            {
                if(empty($GlobalParsedTasks[$FleetRow['fleet_owner']]['tasks_done_parsed']))
                {
                    $GetUserTasksDone['tasks_done'] = $_FleetCache['userTasks'][$FleetRow['fleet_owner']];
                    Tasks_CheckUservar($GetUserTasksDone);
                    $GlobalParsedTasks[$FleetRow['fleet_owner']] = $GetUserTasksDone;
                }
                $ThisTaskUser = $GlobalParsedTasks[$FleetRow['fleet_owner']];
                $ThisTaskUser['id'] = $FleetRow['fleet_owner'];
            }

            Tasks_TriggerTask($ThisTaskUser, 'RECYCLE_DEBRIS');
            if($RecycledGoods['metal'] > 0)
            {
                $TaskTemp = $RecycledGoods['metal'];
                Tasks_TriggerTask($ThisTaskUser, 'DEBRIS_COLLECT_METAL', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TaskTemp)
                    {
                        global $UserTasksUpdate;
                        if(!empty($UserTasksUpdate[$ThisTaskUser['id']]['status'][$ThisCat][$TaskID][$JobID]))
                        {
                            $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$ThisTaskUser['id']]['status'][$ThisCat][$TaskID][$JobID];
                        }
                        if(!isset($ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID]))
                        {
                            $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = 0;
                        }
                        $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] += $TaskTemp;
                        if($ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] < $JobArray['count'])
                        {
                            $UserTasksUpdate[$ThisTaskUser['id']]['status'][$ThisCat][$TaskID][$JobID] = $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID];
                            return true;
                        }
                    }
                ));
            }
            if($RecycledGoods['crystal'] > 0)
            {
                $TaskTemp = $RecycledGoods['crystal'];
                Tasks_TriggerTask($ThisTaskUser, 'DEBRIS_COLLECT_CRYSTAL', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TaskTemp)
                    {
                        global $UserTasksUpdate;
                        if(!empty($UserTasksUpdate[$ThisTaskUser['id']]['status'][$ThisCat][$TaskID][$JobID]))
                        {
                            $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$ThisTaskUser['id']]['status'][$ThisCat][$TaskID][$JobID];
                        }
                        if(!isset($ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID]))
                        {
                            $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = 0;
                        }
                        $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] += $TaskTemp;
                        if($ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] < $JobArray['count'])
                        {
                            $UserTasksUpdate[$ThisTaskUser['id']]['status'][$ThisCat][$TaskID][$JobID] = $ThisTaskUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID];
                            return true;
                        }
                    }
                ));
            }

            $UserDev_Log[] = array('UserID' => $FleetRow['fleet_owner'], 'PlanetID' => '0', 'Date' => $FleetRow['fleet_start_time'], 'Place' => 17, 'Code' => '0', 'ElementID' => '0', 'AdditionalData' => "M,{$RecycledGoods['metal']};C,{$RecycledGoods['crystal']}");
        }

        if($RecycledGoods['metal'] == 0)
        {
            $RecycledGoods['metal'] = '0';
        }
        if($RecycledGoods['crystal'] == 0)
        {
            $RecycledGoods['crystal'] = '0';
        }

        $Message['msg_id'] = '011';
        $Message['args'] = array($FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'], prettyNumber($RecycledGoods['metal']), prettyNumber($RecycledGoods['crystal']));
        $Message = json_encode($Message);
        Cache_Message($FleetRow['fleet_owner'], 0, $FleetRow['fleet_start_time'], 4, '002', '005', $Message);

        if($_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['calcCount'] == 1)
        {
            // Create UpdateFleet record for $_FleetCache
            $CachePointer = &$_FleetCache['updateFleets'][$FleetRow['fleet_id']];
            $CachePointer['fleet_mess'] = 1;
            if(!isset($CachePointer['fleet_resource_metal']))
            {
                $CachePointer['fleet_resource_metal'] = 0;
            }
            if(!isset($CachePointer['fleet_resource_crystal']))
            {
                $CachePointer['fleet_resource_crystal'] = 0;
            }
            $CachePointer['fleet_resource_metal'] += $RecycledGoods['metal'];
            $CachePointer['fleet_resource_crystal'] += $RecycledGoods['crystal'];
        }
        else
        {
            if($RecycledGoods['metal'] > 0 || $RecycledGoods['crystal'] > 0)
            {
                $_FleetCache['fleetRowUpdate'][$FleetRow['fleet_id']] = array
                (
                    'fleet_resource_metal'        => $FleetRow['fleet_resource_metal'] + $RecycledGoods['metal'],
                    'fleet_resource_crystal'    => $FleetRow['fleet_resource_crystal'] + $RecycledGoods['crystal'],
                );
            }
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

        // Return fleet back to the planet
        $Return['FleetsToDelete'][] = $FleetRow['fleet_id'];
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_ComeBack'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_ComeBack_Time'] = $Now;

        RestoreFleetToPlanet($FleetRow, true, $_FleetCache);
    }

    return $Return;
}

?>
