<?php

function RemoveBuildingFromQueue(&$CurrentPlanet, $CurrentUser, $QueueID)
{
    global $_Vars_GameElements;

    if($QueueID > 1)
    {
        $CurrentQueue = $CurrentPlanet['buildQueue'];
        if($CurrentQueue != 0)
        {
            $Queue = explode(';', $CurrentQueue);
            $QueueArray = [];
            foreach($Queue as $ID => $Data)
            {
                if(!empty($Data))
                {
                    $Data = explode(',', $Data);
                    $QueueArray[] = $Data;
                }
            }
            $ElementCount = count($QueueArray);
            if($QueueID <= $ElementCount)
            {
                if($QueueID == $ElementCount)
                {
                    $RemovedQID = $QueueID - 1;
                    $ReturnID = $QueueArray[$RemovedQID][0];
                    unset($QueueArray[$RemovedQID]);
                    foreach($QueueArray as &$Data)
                    {
                        $Data = implode(',', $Data);
                    }
                    $NewQueue = implode(';', $QueueArray);
                }
                else
                {
                    $RemovedQID = $QueueID - 1;
                    $RemovedTime = $QueueArray[$RemovedQID][2];
                    $RemovedID = $QueueArray[$RemovedQID][0];
                    $ReturnID = $RemovedID;

                    $TempPlanet = $CurrentPlanet;
                    foreach($QueueArray as $ID => &$Data)
                    {
                        if($ID < $RemovedQID)
                        {
                            if($Data[4] == 'build')
                            {
                                $TempPlanet[$_Vars_GameElements[$Data[0]]] += 1;
                            }
                            else
                            {
                                $TempPlanet[$_Vars_GameElements[$Data[0]]] -= 1;
                            }
                            continue;
                        }
                        elseif($ID > $RemovedQID)
                        {
                            $TempTime = GetBuildingTime($CurrentUser, $TempPlanet, $Data[0]);
                            if($Data[4] == 'build')
                            {
                                $TempPlanet[$_Vars_GameElements[$Data[0]]] += 1;
                            }
                            else
                            {
                                $TempTime /= 2;
                                $TempPlanet[$_Vars_GameElements[$Data[0]]] -= 1;
                            }
                            $RemovedTime += ($Data[2] - $TempTime);
                            $Data[1] = $TempPlanet[$_Vars_GameElements[$Data[0]]];
                            $Data[2] = $TempTime;
                            $Data[3] -= $RemovedTime;
                        }
                    }
                    unset($QueueArray[$RemovedQID]);
                    foreach($QueueArray as &$Data)
                    {
                        $Data = implode(',', $Data);
                    }
                    $NewQueue = implode(';', $QueueArray);
                }
                $CurrentPlanet['buildQueue'] = $NewQueue;
            }
        }
    }

    return $ReturnID;
}

?>
