<?php

function TechQueue_Add(&$ThePlanet, &$TheUser, $TechID)
{
    global $_Vars_GameElements;

    if($TheUser['techQueue_EndTime'] > 0 AND $TheUser['techQueue_Planet'] != $ThePlanet['id'])
    {
        return false;
    }

    if(!empty($ThePlanet['techQueue']))
    {
        $Queue = explode(';', $ThePlanet['techQueue']);
        $QueueLength = count($Queue);
    }
    else
    {
        $Queue = array();
        $QueueLength = 0;
    }

    $MaxLength = (isPro($TheUser) ? MAX_TECH_QUEUE_LENGTH_PRO : MAX_TECH_QUEUE_LENGTH);
    $Modifier = array();

    if($QueueLength + 1 <= $MaxLength)
    {
        if($QueueLength > 0)
        {
            foreach($Queue as $QueueElement)
            {
                $QueueElement = explode(',', $QueueElement);
                if(!isset($Modifier[$_Vars_GameElements[$QueueElement[0]]]))
                {
                    $Modifier[$_Vars_GameElements[$QueueElement[0]]] = 0;
                }
                $Modifier[$_Vars_GameElements[$QueueElement[0]]] += 1;
                $TheUser[$_Vars_GameElements[$QueueElement[0]]] += 1;
                $StartTime = $QueueElement[3];
            }
        }
        else
        {
            $StartTime = 0;
        }
        if($StartTime <= 0)
        {
            $StartTime = time();
        }
        $Time = GetBuildingTime($TheUser, $ThePlanet, $TechID);
        $EndTime = $StartTime + $Time;
        $NextLevel = $TheUser[$_Vars_GameElements[$TechID]] + 1;

        $Queue[] = "{$TechID},{$NextLevel},{$Time},{$EndTime}";

        $ThePlanet['techQueue'] = implode(';', $Queue);

        foreach($Modifier as $ElementField => $Value)
        {
            $TheUser[$ElementField] -= $Value;
        }

        return true;
    }

    return false;
}

?>
