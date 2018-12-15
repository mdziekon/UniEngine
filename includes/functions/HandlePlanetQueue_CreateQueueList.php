<?php

function HandlePlanetQueue_CreateQueueList($CurrentTime, $ThePlanet)
{
    $QueueList = array();
    $QueueListID = 0;

    if(!empty($ThePlanet['buildQueue']) AND $ThePlanet['buildQueue_firstEndTime'] <= $CurrentTime)
    {
        $ExplodeQueue = explode(';', $ThePlanet['buildQueue']);
        foreach($ExplodeQueue as $ElementData)
        {
            if(!empty($ElementData))
            {
                $ElementData = explode(',', $ElementData);
                if($ElementData[3] <= $CurrentTime)
                {
                    $KeyString = $ElementData[3].str_pad($QueueListID, 4, '0', STR_PAD_LEFT);
                    $QueueList[$KeyString] = array('type' => 1, 'endtime' => $ElementData[3]);
                    $QueueListID += 1;
                }
            }
        }
    }
    if(!empty($ThePlanet['techQueue']) AND $ThePlanet['techQueue_firstEndTime'] <= $CurrentTime)
    {
        $ExplodeQueue = explode(';', $ThePlanet['techQueue']);
        foreach($ExplodeQueue as $ElementData)
        {
            if(!empty($ElementData))
            {
                $ElementData = explode(',', $ElementData);
                if($ElementData[3] <= $CurrentTime)
                {
                    $KeyString = $ElementData[3].str_pad($QueueListID, 4, '0', STR_PAD_LEFT);
                    $QueueList[$KeyString] = array('type' => 2, 'endtime' => $ElementData[3]);
                    $QueueListID += 1;
                }
            }
        }
    }

    if(!empty($QueueList))
    {
        ksort($QueueList);
        foreach($QueueList as $QueueData)
        {
            $TempList[] = $QueueData;
        }
        $QueueList = $TempList;
    }
    else
    {
        $QueueList = array();
    }

    return $QueueList;
}

?>
