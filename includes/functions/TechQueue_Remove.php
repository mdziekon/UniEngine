<?php

function TechQueue_Remove(&$ThePlanet, &$TheUser, $ElementID, $CurrentTime)
{
    global $_Vars_GameElements, $UserDev_Log;

    $ElementID = intval($ElementID);
    if($ElementID < 0)
    {
        $ElementID = 0;
    }

    if(!empty($ThePlanet['techQueue']))
    {
        $Queue = explode(';', $ThePlanet['techQueue']);
        $QueueLength = count($Queue);
        if($QueueLength >= $ElementID)
        {
            $NewQueue = array();
            $TempUser = $TheUser;
            $RemovedTime = 0;
            foreach($Queue as $QueueID => $QueueElement)
            {
                $QueueElement = explode(',', $QueueElement);
                if($ElementID > $QueueID)
                {
                    $TempUser[$_Vars_GameElements[$QueueElement[0]]] += 1;
                    $NewQueue[] = implode(',', $QueueElement);
                    continue;
                }
                if($ElementID == $QueueID)
                {
                    $RemovedID = $QueueElement[0];
                    if($ElementID == 0)
                    {
                        $Needed = GetBuildingPrice($TempUser, $ThePlanet, $RemovedID);
                        $ThePlanet['metal'] += $Needed['metal'];
                        $ThePlanet['crystal'] += $Needed['crystal'];
                        $ThePlanet['deuterium'] += $Needed['deuterium'];
                        $UserDev_Log[] = array('PlanetID' => $ThePlanet['id'], 'Date' => $CurrentTime, 'Place' => 4, 'Code' => 2, 'ElementID' => $RemovedID);
                        $ThePlanet['techQueue_firstEndTime'] = 0;
                        if($QueueLength == 1)
                        {
                            $TheUser['techQueue_Planet'] = '0';
                            $TheUser['techQueue_EndTime'] = '0';
                        }
                        $RemovedTime = $QueueElement[3] - $CurrentTime;
                    }
                    else
                    {
                        $RemovedTime = $QueueElement[2];
                    }
                }
                else
                {
                    $TempTime = GetBuildingTime($TempUser, $ThePlanet, $QueueElement[0]);
                    $TempUser[$_Vars_GameElements[$QueueElement[0]]] += 1;
                    $RemovedTime += ($QueueElement[2] - $TempTime);
                    $QueueElement[1] = $TempUser[$_Vars_GameElements[$QueueElement[0]]];
                    $QueueElement[2] = $TempTime;
                    $QueueElement[3] -= $RemovedTime;
                    $NewQueue[] = implode(',', $QueueElement);
                }
            }
            $ThePlanet['techQueue'] = implode(';', $NewQueue);

            return isset($RemovedID) ? $RemovedID : null;
        }
    }

    return false;
}

?>
