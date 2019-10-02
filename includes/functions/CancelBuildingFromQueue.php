<?php

function CancelBuildingFromQueue(&$CurrentPlanet, $CurrentUser)
{
    global $_Vars_GameElements, $UserDev_Log;

    $Element = null;

    $CurrentQueue = $CurrentPlanet['buildQueue'];
    if($CurrentQueue != 0)
    {
        // "Translate" Queue string to Queue Array
        $QueueArray = explode(';', $CurrentQueue);
        // Get how many elements are in Queue
        $ActualCount = count($QueueArray);
        // This Time
        $Now = time();

        // Get Info about canceled element
        $CanceledIDArray = explode(',', $QueueArray[0]);
        $Element = $CanceledIDArray[0]; // ElementID
        $RemovedTime = $CanceledIDArray[3] - $Now;
        $BuildMode = $CanceledIDArray[4]; // Element Build Mode (build/destroy)

        if($ActualCount > 1)
        {
            array_shift($QueueArray);
            // Now update all other elements
            $TempPlanet = $CurrentPlanet;
            foreach($QueueArray as &$Data)
            {
                $Data = explode(',', $Data);
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
                $Data = implode(',', $Data);
            }
            $NewQueue = implode(';', $QueueArray);
            $ReturnValue = true;
        }
        else
        {
            $NewQueue = '0';
            $ReturnValue = false;
        }
        $BuildEndTime = '0';

        // Now let's return used resources
        // First - check mode
        if($BuildMode == 'destroy')
        {
             $ForDestroy = true;
        }
        else
        {
             $ForDestroy = false;
        }

        if($Element != false)
        {
             $Needed = GetBuildingPrice($CurrentUser, $CurrentPlanet, $Element, true, $ForDestroy);
             $CurrentPlanet['metal'] += $Needed['metal'];
             $CurrentPlanet['crystal'] += $Needed['crystal'];
             $CurrentPlanet['deuterium'] += $Needed['deuterium'];

            if($ForDestroy)
            {
                $SetCode = 2;
            }
            else
            {
                $SetCode = 1;
            }
            $UserDev_Log[] = array('PlanetID' => $CurrentPlanet['id'], 'Date' => $Now, 'Place' => 2, 'Code' => $SetCode, 'ElementID' => $Element);
        }
    }
    else
    {
        $NewQueue = '0';
        $BuildEndTime = '0';
        $ReturnValue = false;
    }

    $CurrentPlanet['buildQueue'] = $NewQueue;
    $CurrentPlanet['buildQueue_firstEndTime'] = $BuildEndTime;

    return $Element;
}

?>
