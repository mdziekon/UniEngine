<?php

function Tasks_CheckUservar(&$UserVar)
{
    if(empty($UserVar['tasks_done_parsed']))
    {
        if(!empty($UserVar['tasks_done']))
        {
            $UserVar['tasks_done_parsed'] = json_decode($UserVar['tasks_done'], true);
            if(!empty($UserVar['tasks_done_parsed']))
            {
                if(!empty($UserVar['tasks_done_parsed']['locked']))
                {
                    foreach($UserVar['tasks_done_parsed']['locked'] as $CatID => $_Vars_TasksData)
                    {
                        if(strstr($CatID, 's'))
                        {
                            $CatID = str_replace('s', '', $CatID);
                        }
                        foreach($_Vars_TasksData as $TaskID)
                        {
                            $UserVar['tasks_done_parsed']['done'][$CatID][] = $TaskID;
                        }
                    }
                }
            }
        }

        if(empty($UserVar['tasks_done_parsed']['done']))
        {
            $UserVar['tasks_done_parsed']['done'] = array(0 => array(0));
        }
        return true;
    }
    return false;
}

function Tasks_GetTaskCatID($TaskID)
{
    global $_Vars_TasksData;

    foreach($_Vars_TasksData as $_Vars_TasksDataCatID => $_Vars_TasksDataList)
    {
        if(!empty($_Vars_TasksDataList['tasks'][$TaskID]))
        {
            return $_Vars_TasksDataCatID;
        }
    }
}

function Tasks_GetAvailableTasks($UserVar, $CheckJobType = false)
{
    global $_Vars_TasksData;

    $Return = array('tasks' => array());

    foreach($_Vars_TasksData as $CatID => $CatData)
    {
        if(Tasks_IsCatDone($CatID, $UserVar))
        {
            continue;
        }
        if(!Tasks_CheckCatAvailable($CatID, $UserVar))
        {
            continue;
        }
        foreach($CatData['tasks'] as $TaskID => $TaskData)
        {
            if(Tasks_IsDone($TaskID, $UserVar))
            {
                continue;
            }
            if(!Tasks_CheckAvailable($TaskID, $UserVar, $CatID))
            {
                continue;
            }
            if($CheckJobType !== false AND strstr($TaskData['jobtypes'], $CheckJobType) === false)
            {
                continue;
            }

            $Return['tasks'][] = $TaskID;
            $Return['cats'][$TaskID] = $CatID;
        }
    }

    return $Return;
}

function Tasks_IsCatDone($CatID, $UserVar)
{
    global $_Vars_TasksData;

    if(empty($UserVar['tasks_done_parsed']['done'][$CatID]))
    {
        return false;
    }
    if(count($UserVar['tasks_done_parsed']['done'][$CatID]) != count($_Vars_TasksData[$CatID]['tasks']))
    {
        return false;
    }
    return true;
}

function Tasks_IsDone($TaskID, $UserVar)
{
    foreach($UserVar['tasks_done_parsed']['done'] as $CatDoneTasks)
    {
        if(in_array($TaskID, $CatDoneTasks))
        {
            return true;
        }
    }
    return false;
}

function Tasks_CheckCatAvailable($CatID, $UserVar)
{
    global $_Vars_TasksData;

    if(!empty($_Vars_TasksData[$CatID]['requirements']))
    {
        foreach($_Vars_TasksData[$CatID]['requirements'] as $TaskCatReq)
        {
            if($TaskCatReq['type'] == 'CATEGORY')
            {
                if(!Tasks_IsCatDone($TaskCatReq['elementID'], $UserVar))
                {
                    return false;
                }
            }
            elseif($TaskCatReq['type'] == 'TASK')
            {
                if(!Tasks_IsDone($TaskCatReq['elementID'], $UserVar))
                {
                    return false;
                }
            }
        }
    }
    return true;
}

function Tasks_CheckAvailable($TaskID, $UserVar, $TaskCatID = false)
{
    global $_Vars_TasksData;

    if($TaskCatID === false)
    {
        $TaskCatID = Tasks_GetTaskCatID($TaskID);
    }
    $Pointer = &$_Vars_TasksData[$TaskCatID]['tasks'][$TaskID];
    if(!empty($Pointer['requirements']))
    {
        foreach($Pointer['requirements'] as $TaskReq)
        {
            if($TaskReq['type'] == 'CATEGORY')
            {
                if(!Tasks_IsCatDone($TaskReq['elementID'], $UserVar))
                {
                    return false;
                }
            }
            elseif($TaskReq['type'] == 'TASK')
            {
                if(!Tasks_IsDone($TaskReq['elementID'], $UserVar))
                {
                    return false;
                }
            }
        }
    }
    return true;
}

function Tasks_ParseRewards($RewardData, &$UpdateArray)
{
    global $_Vars_GameElements;
    if($RewardData['type'] == 'RESOURCES')
    {
        // Add Resources
        if(isset($RewardData['met']))
        {
            if(!isset($UpdateArray['planet']['metal']))
            {
                $UpdateArray['planet']['metal'] = 0;
            }
            if(!isset($UpdateArray['devlog']['M']))
            {
                $UpdateArray['devlog']['M'] = 0;
            }
            $UpdateArray['planet']['metal'] += $RewardData['met'];
            $UpdateArray['devlog']['M'] += $RewardData['met'];
        }
        if(isset($RewardData['cry']))
        {
            if(!isset($UpdateArray['planet']['crystal']))
            {
                $UpdateArray['planet']['crystal'] = 0;
            }
            if(!isset($UpdateArray['devlog']['C']))
            {
                $UpdateArray['devlog']['C'] = 0;
            }
            $UpdateArray['planet']['crystal'] += $RewardData['cry'];
            $UpdateArray['devlog']['C'] += $RewardData['cry'];
        }
        if(isset($RewardData['deu']))
        {
            if(!isset($UpdateArray['planet']['deuterium']))
            {
                $UpdateArray['planet']['deuterium'] = 0;
            }
            if(!isset($UpdateArray['devlog']['D']))
            {
                $UpdateArray['devlog']['D'] = 0;
            }
            $UpdateArray['planet']['deuterium'] += $RewardData['deu'];
            $UpdateArray['devlog']['D'] += $RewardData['deu'];
        }
    }
    else if($RewardData['type'] == 'PLANET_ELEMENT')
    {
        // Add Ships or Defenses
        if(!isset($UpdateArray['planet'][$_Vars_GameElements[$RewardData['elementID']]]))
        {
            $UpdateArray['planet'][$_Vars_GameElements[$RewardData['elementID']]] = 0;
        }
        if(!isset($UpdateArray['devlog'][$RewardData['elementID']]))
        {
            $UpdateArray['devlog'][$RewardData['elementID']] = 0;
        }
        $UpdateArray['planet'][$_Vars_GameElements[$RewardData['elementID']]] += $RewardData['count'];
        $UpdateArray['devlog'][$RewardData['elementID']] += $RewardData['count'];
    }
    else if($RewardData['type'] == 'PREMIUM_ITEM')
    {
        // Add Free PremiumItem
        $UpdateArray['free_premium'][] = $RewardData['elementID'];
    }
    else if($RewardData['type'] == 'PREMIUM_RESOURCE')
    {
        // Add Free Dark Energy
        if(!isset($UpdateArray['devlog']['DE']))
        {
            $UpdateArray['devlog']['DE'] = 0;
        }
        if(!isset($UpdateArray['user']['darkEnergy']))
        {
            $UpdateArray['user']['darkEnergy'] = 0;
        }
        $UpdateArray['devlog']['DE'] += $RewardData['value'];
        $UpdateArray['user']['darkEnergy'] += $RewardData['value'];
    }
}

function Tasks_TriggerTask($TheUser, $JobType, $Callbacks = array())
{
    global $_Vars_TasksData, $UserTasksUpdate;

    $CheckTasks = Tasks_GetAvailableTasks($TheUser, $JobType);
    foreach($CheckTasks['tasks'] as $TaskID)
    {
        $ThisCat = $CheckTasks['cats'][$TaskID];
        foreach($_Vars_TasksData[$ThisCat]['tasks'][$TaskID]['jobs'] as $JobID => $JobArray)
        {
            if($JobArray['type'] == $JobType)
            {
                if(!empty($Callbacks['preCheck']) AND $Callbacks['preCheck']($JobArray, $ThisCat, $TaskID, $JobID) === true)
                {
                    continue;
                }
                if(!empty($TheUser['tasks_done_parsed']['jobs'][$ThisCat][$TaskID]))
                {
                    if(in_array($JobID, $TheUser['tasks_done_parsed']['jobs'][$ThisCat][$TaskID]))
                    {
                        continue;
                    }
                }
                if(!empty($UserTasksUpdate[$TheUser['id']]['done'][$ThisCat][$TaskID]))
                {
                    if(in_array($JobID, $UserTasksUpdate[$TheUser['id']]['done'][$ThisCat][$TaskID]))
                    {
                        continue;
                    }
                }
                if(!empty($Callbacks['mainCheck']) AND $Callbacks['mainCheck']($JobArray, $ThisCat, $TaskID, $JobID) === true)
                {
                    continue;
                }
                $UserTasksUpdate[$TheUser['id']]['done'][$ThisCat][$TaskID][] = $JobID;
            }
        }
    }
}

function Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, &$ThisUser, $Value)
{
    global $UserTasksUpdate;
    if(!empty($UserTasksUpdate[$ThisUser['id']]['status'][$ThisCat][$TaskID][$JobID]))
    {
        $ThisUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$ThisUser['id']]['status'][$ThisCat][$TaskID][$JobID];
    }
    if(!isset($ThisUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID]))
    {
        $ThisUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = 0;
    }
    $ThisUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] += $Value;
    if($ThisUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] < $JobArray[$JobArray['statusField']])
    {
        $UserTasksUpdate[$ThisUser['id']]['status'][$ThisCat][$TaskID][$JobID] = $ThisUser['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID];
        return true;
    }
}

function Tasks_GetTaskCategoryData($TaskCatID) {
    global $_Vars_TasksData;

    return $_Vars_TasksData[$TaskCatID];
}

function Tasks_GetTaskData($TaskCatID, $TaskID) {
    $CategoryData = Tasks_GetTaskCategoryData($TaskCatID);

    return $CategoryData['tasks'][$TaskID];
}

function Tasks_GetTaskImagePath($TaskCatID, $TaskID) {
    global $_SkinPath;

    $TaskData = Tasks_GetTaskData($TaskCatID, $TaskID);

    return ($_SkinPath . $TaskData['details']['img']);
}

function Tasks_GenerateRewardsStrings($rewardsData, $_Lang) {
    $parts = [];

    foreach ($rewardsData as $rewardDetails) {
        $rewardType = $rewardDetails['type'];

        $parts[] = $_Lang['TaskRewards'][$rewardType]($rewardDetails, $_Lang);
    }

    return $parts;
}

?>
