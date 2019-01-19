<?php

function AddBuildingToQueue(&$CurrentPlanet, $CurrentUser, $Element, $AddMode = true)
{
    global $_Vars_GameElements;

    $CurrentQueue = $CurrentPlanet['buildQueue'];
    if($CurrentQueue != 0)
    {
        $QueueArray = explode(';', $CurrentQueue);
        $ActualCount = count($QueueArray);
    }
    else
    {
        $QueueArray = [];
        $ActualCount = 0;
    }

    if($AddMode == true)
    {
        $BuildMode = 'build';
    }
    else
    {
        $BuildMode = 'destroy';
    }

    if($ActualCount < ((isPro($CurrentUser)) ? MAX_BUILDING_QUEUE_SIZE_PRO : MAX_BUILDING_QUEUE_SIZE))
    {
        $QueueID = $ActualCount + 1;
    }
    else
    {
        $QueueID = false;
    }

    if($QueueID !== false)
    {
        $TempPlanet = $CurrentPlanet;
        if($QueueID > 1)
        {
            foreach($QueueArray as $QueueElement)
            {
                $QueueElement = explode(',', $QueueElement);
                if($QueueElement[4] == 'build')
                {
                    $TempPlanet[$_Vars_GameElements[$QueueElement[0]]] += 1;
                }
                else
                {
                    $TempPlanet[$_Vars_GameElements[$QueueElement[0]]] -= 1;
                }
            }
        }

        $BuildTime = GetBuildingTime($CurrentUser, $TempPlanet, $Element);
        if($AddMode == true)
        {
            $BuildLevel = $TempPlanet[$_Vars_GameElements[$Element]] + 1;
        }
        else
        {
            $BuildLevel = $TempPlanet[$_Vars_GameElements[$Element]] - 1;
            $BuildTime /= 2;
        }

        if($QueueID == 1)
        {
            $BuildEndTime = time() + $BuildTime;
        }
        else
        {
            $PrevBuild = explode(',', $QueueArray[$ActualCount - 1]);
            $BuildEndTime = $PrevBuild[3] + $BuildTime;
        }
        $QueueArray[$ActualCount] = "{$Element},{$BuildLevel},{$BuildTime},{$BuildEndTime},{$BuildMode}";
        $NewQueue = implode(';', $QueueArray);
        $CurrentPlanet['buildQueue'] = $NewQueue;
    }
    return $QueueID;
}

?>
