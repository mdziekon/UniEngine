<?php

function HandleShipyardQueue($TheUser, &$ThePlanet, $ProductionTime, $EndTime)
{
    global $_Vars_GameElements;
    $Builded = array();

    if($ThePlanet['shipyardQueue'] != 0)
    {
        $ThePlanet['shipyardQueue_additionalWorkTime'] += $ProductionTime;

        $BuildQueue = explode(';', $ThePlanet['shipyardQueue']);
        foreach($BuildQueue as $Array)
        {
            if($Array != '')
            {
                $Item = explode(',', $Array);
                $BuildArray[] = array($Item[0], $Item[1], GetBuildingTime($TheUser, $ThePlanet, $Item[0]));
            }
        }
        $ThePlanet['shipyardQueue'] = '';
        $WorkTime = $ThePlanet['shipyardQueue_additionalWorkTime'];

        foreach($BuildArray as $Item)
        {
            $ElementID = $Item[0];
            $Count = $Item[1];
            $BuildTime = $Item[2];

            if($BuildTime != 0)
            {
                $AllBuilded = floor($WorkTime / $BuildTime);
            }
            else
            {
                if($WorkTime >= 0)
                {
                    $AllBuilded = $Count;
                }
                else
                {
                    $AllBuilded = 0;
                }
            }
            if($AllBuilded > $Count)
            {
                $AllBuilded = $Count;
            }
            elseif($AllBuilded < 0)
            {
                $AllBuilded = 0;
            }
            $AllBuildedTime = $AllBuilded * $BuildTime;
            $LeftToBuild = $Count - $AllBuilded;
            if($AllBuilded > 0)
            {
                $ThePlanet['shipyardQueue_additionalWorkTime'] -= $AllBuildedTime;
                $WorkTime -= $AllBuildedTime;
                if(!isset($Builded[$ElementID]))
                {
                    $Builded[$ElementID] = 0;
                }
                $Builded[$ElementID] += $AllBuilded;
                $ThePlanet[$_Vars_GameElements[$ElementID]] += $AllBuilded;
                if($LeftToBuild == 0)
                {
                    continue;
                }
                else
                {
                    $ThePlanet['shipyardQueue'] .= "{$ElementID},{$LeftToBuild};";
                    $WorkTime = -1;
                    continue;
                }
            }
            else
            {
                $ThePlanet['shipyardQueue'] .= "{$ElementID},{$LeftToBuild};";
                $WorkTime = -1;
            }
        }

        if(!empty($Builded))
        {
            global $UserDev_Log, $UserTasksUpdate, $_Vars_TasksData;
            $Temp = Array2String($Builded);
            $UserDev_Log[] = array('UserID' => $TheUser['id'], 'PlanetID' => $ThePlanet['id'], 'Date' => $EndTime, 'Place' => 8, 'Code' => '1', 'ElementID' => '0', 'AdditionalData' => $Temp);
            foreach($Builded as $Key => $Value)
            {
                $BuildedIDs[] = $Key;
            }

            // Trigger Tasks Check
            if(empty($TheUser['tasks_done_parsed']))
            {
                global $GlobalParsedTasks;
                Tasks_CheckUservar($TheUser);
                $GlobalParsedTasks[$TheUser['id']]['tasks_done_parsed'] = $TheUser['tasks_done_parsed'];
            }
            Tasks_TriggerTask($TheUser, 'CONSTRUCT_SHIPS_OR_DEFENSE', array
            (
                'preCheck' => function($JobArray) use ($BuildedIDs)
                {
                    return !in_array($JobArray['elementID'], $BuildedIDs);
                },
                'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($TheUser, $Builded)
                {
                    global $UserTasksUpdate;
                    if(isset($UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID]) && !empty($UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID]))
                    {
                        $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID];
                    }

                    if(!isset($TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID]))
                    {
                        $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = 0;
                    }
                    $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] += $Builded[$JobArray['elementID']];
                    if($TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] < $JobArray['count'])
                    {
                        $UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID] = $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID];
                        return true;
                    }
                }
            ));
            foreach($Builded as $ThisID => $ThisCount)
            {
                Tasks_TriggerTask($TheUser, 'CONSTRUCT_SHIPS_OR_DEFENSE_MULTIID', array
                (
                    'preCheck' => function($JobArray) use ($ThisID)
                    {
                        return !in_array($ThisID, $JobArray['elementIDs']);
                    },
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($TheUser, $ThisCount)
                    {
                        global $UserTasksUpdate;
                        if(!empty($UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID]))
                        {
                            $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID];
                        }
                        if(!isset($TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID]))
                        {
                            $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = 0;
                        }
                        $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] += $ThisCount;
                        if($TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] < $JobArray['count'])
                        {
                            $UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID] = $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID];
                            return true;
                        }
                    }
                ));
            }
        }
    }
    else
    {
        $ThePlanet['shipyardQueue_additionalWorkTime'] = 0;
    }

    return $Builded;
}

?>
