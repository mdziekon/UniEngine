<?php

function HandlePlanetQueue_OnStructureBuildEnd(&$ThePlanet, &$TheUser, $CurrentTime)
{
    global $_Vars_GameElements, $_Vars_ElementCategories, $UserDev_Log, $UserTasksUpdate, $HPQ_PlanetUpdatedFields;

    if(!empty($ThePlanet['buildQueue']))
    {
        $Queue            = explode(';', $ThePlanet['buildQueue']);
        $ThisElement    = explode(',', $Queue[0]);

        $BuildEndTime    = $ThisElement[3];
        $BuildMode        = $ThisElement[4];
        $ElementID        = $ThisElement[0];
        $ForDestroy        = ($BuildMode === 'build' ? false : true);

        if($BuildEndTime <= $CurrentTime)
        {
            array_shift($Queue);
            $QueueLength = count($Queue);
            $QueueEnd = end($Queue);
            if(empty($QueueEnd))
            {
                $QueueLength -= 1;
                array_pop($Queue);
            }

            if($ForDestroy === false)
            {
                $ThePlanet['field_current'] += 1;
                $ThePlanet[$_Vars_GameElements[$ElementID]] += 1;
            }
            else
            {
                $ThePlanet['field_current'] -= 1;
                $ThePlanet[$_Vars_GameElements[$ElementID]] -= 1;
            }
            if($ThePlanet['planet_type'] == 3 AND $ElementID == 41)
            {
                $ThePlanet['field_max'] += FIELDS_BY_MOONBASIS_LEVEL;
                $HPQ_PlanetUpdatedFields[] = 'field_max';
            }
            $HPQ_PlanetUpdatedFields[] = 'field_current';
            $HPQ_PlanetUpdatedFields[] = $_Vars_GameElements[$ElementID];
            $HPQ_PlanetUpdatedFields[] = 'buildQueue_firstEndTime';
            $HPQ_PlanetUpdatedFields[] = 'buildQueue';

            if($ForDestroy === true)
            {
                $SetCode = 2;
            }
            else
            {
                $SetCode = 1;
            }
            $UserDev_Log[] = array('PlanetID' => $ThePlanet['id'], 'Date' => $BuildEndTime, 'Place' => 3, 'Code' => $SetCode, 'ElementID' => $ElementID);

            // Trigger Tasks Check
            if(empty($TheUser['tasks_done_parsed']))
            {
                global $GlobalParsedTasks;
                Tasks_CheckUservar($TheUser);
                $GlobalParsedTasks[$TheUser['id']]['tasks_done_parsed'] = $TheUser['tasks_done_parsed'];
            }
            if(!$ForDestroy)
            {
                if($ElementID == 33)
                {
                    Tasks_TriggerTask($TheUser, 'REACH_TERRAFORMING_LEVEL', array
                    (
                        'mainCheck' => function($JobArray) use ($ThePlanet)
                        {
                            if(CalculateMaxPlanetFields($ThePlanet) < $JobArray['fields'])
                            {
                                return true;
                            }
                        }
                    ));
                }
                if(in_array($ElementID, $_Vars_ElementCategories['prod']))
                {
                    Tasks_TriggerTask($TheUser, 'REACH_EXTRACTION_LEVEL', array
                    (
                        'preCheck' => function($JobArray) use ($ElementID)
                        {
                            return ($JobArray['buildingID'] != $ElementID);
                        },
                        'mainCheck' => function($JobArray) use ($ThePlanet, $ElementID)
                        {
                            $emptyUser = [];

                            $elementProduction = getElementProduction(
                                $ElementID,
                                $ThePlanet,
                                $emptyUser,
                                [
                                    'useCurrentBoosters' => false,
                                    'customProductionFactor' => 10
                                ]
                            );

                            $thisResourceKey = $JobArray['resource'];
                            $thisResourceExtraction = $elementProduction[$thisResourceKey];

                            return ($thisResourceExtraction < $JobArray['level']);
                        }
                    ));
                    Tasks_TriggerTask($TheUser, 'REACH_TOTAL_EXTRACTION_LEVEL', array
                    (
                        'preCheck' => function($JobArray) use ($ElementID)
                        {
                            return ($JobArray['buildingID'] != $ElementID);
                        },
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThePlanet, $TheUser, $ElementID)
                        {
                            global $_Vars_GameElements;

                            $oldElementProduction = getElementProduction(
                                $ElementID,
                                $ThePlanet,
                                $TheUser,
                                [
                                    'useCurrentBoosters' => false,
                                    'customLevel' => ($ThePlanet[$_Vars_GameElements[$ElementID]] - 1),
                                    'customProductionFactor' => 10
                                ]
                            );
                            $newElementProduction = getElementProduction(
                                $ElementID,
                                $ThePlanet,
                                $TheUser,
                                [
                                    'useCurrentBoosters' => false,
                                    'customProductionFactor' => 10
                                ]
                            );

                            $thisResourceKey = $JobArray['resource'];
                            $oldResourceExtraction = $oldElementProduction[$thisResourceKey];
                            $newResourceExtraction = $newElementProduction[$thisResourceKey];

                            $extractionDifference = ($newResourceExtraction - $oldResourceExtraction);

                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $TheUser, $extractionDifference);
                        }
                    ));
                }
                Tasks_TriggerTask($TheUser, 'CONSTRUCTION_END', array
                (
                    'preCheck' => function($JobArray) use ($ElementID)
                    {
                        return ($JobArray['elementID'] != $ElementID);
                    },
                    'mainCheck' => function($JobArray) use ($ThePlanet, $_Vars_GameElements)
                    {
                        if($ThePlanet[$_Vars_GameElements[$JobArray['elementID']]] < $JobArray['level'])
                        {
                            return true;
                        }
                    }
                ));
                Tasks_TriggerTask($TheUser, 'MULTIPLE_CONSTRUCTION_END', array
                (
                    'preCheck' => function($JobArray) use ($ElementID)
                    {
                        return ($JobArray['elementID'] != $ElementID);
                    },
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($TheUser, $ThePlanet, $_Vars_GameElements)
                    {
                        if($ThePlanet[$_Vars_GameElements[$JobArray['elementID']]] != $JobArray['level'])
                        {
                            return true;
                        }
                        return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $TheUser, 1);
                    }
                ));
            }
            else
            {
                // Prevent building and destroying to progress / complete missions
                if(in_array($ElementID, $_Vars_ElementCategories['prod']))
                {
                    Tasks_TriggerTask($TheUser, 'REACH_TOTAL_EXTRACTION_LEVEL', array
                    (
                        'preCheck' => function($JobArray) use ($ElementID)
                        {
                            return ($JobArray['buildingID'] != $ElementID);
                        },
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThePlanet, $TheUser, $ElementID)
                        {
                            global $_Vars_GameElements, $UserTasksUpdate;

                            $oldElementProduction = getElementProduction(
                                $ElementID,
                                $ThePlanet,
                                $TheUser,
                                [
                                    'useCurrentBoosters' => false,
                                    'customLevel' => ($ThePlanet[$_Vars_GameElements[$ElementID]] + 1),
                                    'customProductionFactor' => 10
                                ]
                            );
                            $newElementProduction = getElementProduction(
                                $ElementID,
                                $ThePlanet,
                                $TheUser,
                                [
                                    'useCurrentBoosters' => false,
                                    'customProductionFactor' => 10
                                ]
                            );

                            $thisResourceKey = $JobArray['resource'];
                            $oldResourceExtraction = $oldElementProduction[$thisResourceKey];
                            $newResourceExtraction = $newElementProduction[$thisResourceKey];

                            $extractionDifference = ($oldResourceExtraction - $newResourceExtraction);

                            if(!empty($UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID])) {
                                $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID];
                            }
                            if($TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] <= 0) {
                                return true;
                            }

                            $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] -= $extractionDifference;
                            $UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID] = $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID];

                            return true;
                        }
                    ));
                }
                Tasks_TriggerTask($TheUser, 'MULTIPLE_CONSTRUCTION_END', array
                (
                    'preCheck' => function($JobArray) use ($ElementID)
                    {
                        return ($JobArray['elementID'] != $ElementID);
                    },
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($TheUser, $ThePlanet, $_Vars_GameElements)
                    {
                        global $UserTasksUpdate;
                        if(!empty($UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID]))
                        {
                            $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID];
                        }
                        if($TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] <= 0)
                        {
                            return true;
                        }
                        if(($ThePlanet[$_Vars_GameElements[$JobArray['elementID']]] + 1) != $JobArray['level'])
                        {
                            return true;
                        }
                        $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] -= 1;
                        $UserTasksUpdate[$TheUser['id']]['status'][$ThisCat][$TaskID][$JobID] = $TheUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID];
                        return true;
                    }
                ));
            }

            $HPQ_PlanetUpdatedFields[] = 'buildQueue_firstEndTime';
            $HPQ_PlanetUpdatedFields[] = 'buildQueue';
            $ThePlanet['buildQueue_firstEndTime'] = '0';
            if($QueueLength > 0)
            {
                $ThePlanet['buildQueue'] = implode(';', $Queue);
                return true;
            }
            else
            {
                $ThePlanet['buildQueue'] = '0';
            }
        }
    }
    return false;
}

?>
