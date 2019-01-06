<?php

function HandlePlanetQueue_OnTechnologyEnd(&$ThePlanet, &$TheUser, $CurrentTime)
{
    global $_Vars_GameElements, $UserDev_Log, $UserTasksUpdate, $_Vars_TasksData, $HPQ_PlanetUpdatedFields, $HPQ_UserUpdatedFields, $HFUU_UsersToUpdate;

    if(!empty($ThePlanet['techQueue']))
    {
        $Queue = explode(';', $ThePlanet['techQueue']);
        $ThisElement = explode(',', $Queue[0]);

        $BuildEndTime = $ThisElement[3];
        $ElementID = $ThisElement[0];

        if($BuildEndTime <= $CurrentTime)
        {
            array_shift($Queue);
            $QueueLength = count($Queue);

            $TheUser[$_Vars_GameElements[$ElementID]] += 1;
            $TheUser['techQueue_Planet'] = '0';
            $TheUser['techQueue_EndTime'] = '0';
            $HPQ_UserUpdatedFields[] = $_Vars_GameElements[$ElementID];
            $HPQ_UserUpdatedFields[] = 'techQueue_Planet';
            $HPQ_UserUpdatedFields[] = 'techQueue_EndTime';
            $HFUU_UsersToUpdate[$TheUser['id']] = true;

            $UserDev_Log[] = array('PlanetID' => '0', 'Date' => $BuildEndTime, 'Place' => 5, 'Code' => '0', 'ElementID' => $ElementID);

            // Trigger Tasks Check
            if(empty($TheUser['tasks_done_parsed']))
            {
                global $GlobalParsedTasks;
                Tasks_CheckUservar($TheUser);
                $GlobalParsedTasks[$TheUser['id']]['tasks_done_parsed'] = $TheUser['tasks_done_parsed'];
            }
            Tasks_TriggerTask($TheUser, 'RESEARCH_END', array
            (
                'mainCheck' => function($JobArray) use ($TheUser, $_Vars_GameElements)
                {
                    if($TheUser[$_Vars_GameElements[$JobArray['elementID']]] < $JobArray['level'])
                    {
                        return true;
                    }
                }
            ));
            Tasks_TriggerTask($TheUser, 'REACH_TECHPOINTS_LEVEL', array
            (
                'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThePlanet, $TheUser, $ElementID)
                {
                    global $_Vars_GameElements, $_GameConfig;
                    $TheUser[$_Vars_GameElements[$ElementID]] -= 1;
                    $ThisPoints = GetBuildingPrice($TheUser, $ThePlanet, $ElementID);
                    $ThisPoints = ($ThisPoints['metal'] + $ThisPoints['crystal'] + $ThisPoints['deuterium']) / $_GameConfig['stat_settings'];
                    return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $TheUser, $ThisPoints);
                }
            ));

            $HPQ_PlanetUpdatedFields[] = 'techQueue_firstEndTime';
            $HPQ_PlanetUpdatedFields[] = 'techQueue';
            $ThePlanet['techQueue_firstEndTime'] = '0';
            if($QueueLength > 0)
            {
                $ThePlanet['techQueue'] = implode(';', $Queue);
                return true;
            }
            else
            {
                $ThePlanet['techQueue'] = '0';
            }
        }
    }
    return false;
}

?>
