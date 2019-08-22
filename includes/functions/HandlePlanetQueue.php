<?php

function HandlePlanetQueue(&$ThePlanet, &$TheUser, $CurrentTime = false, $ForMultiUpdate = false)
{
    global $HPQ_PlanetUpdatedFields, $HPQ_UserUpdatedFields, $_Vars_GameElements, $UserDev_Log;

    if(empty($HPQ_PlanetUpdatedFields))
    {
        $HPQ_PlanetUpdatedFields = array('metal', 'crystal', 'deuterium', 'last_update', 'metal_perhour', 'crystal_perhour', 'deuterium_perhour', 'energy_used', 'energy_max');
    }
    if(empty($HPQ_UserUpdatedFields))
    {
        $HPQ_UserUpdatedFields = array();
    }

    if($CurrentTime === false)
    {
        $CurrentTime = time();
    }

    // 1st part - Create Mixed QueueList from Structures Queue & Technology Queue
    $QueueList = HandlePlanetQueue_CreateQueueList($CurrentTime, $ThePlanet);

    // 2nd part - Handle created QueueList
    $NeedUpdate = false;
    $IDX = 0;
    while(!empty($QueueList))
    {
        // Calculate income just before we apply the upgrades
        PlanetResourceUpdate($TheUser, $ThePlanet, $QueueList[$IDX]['endtime'], true);

        $NeedQueueListRebuild = false;
        if($QueueList[$IDX]['type'] === 1)
        {
            // Queue Type is: Structures on ThePlanet
            $NeedUpdate = true;
            if(HandlePlanetQueue_OnStructureBuildEnd($ThePlanet, $TheUser, $QueueList[$IDX]['endtime']))
            {
                $LastPlanetUpdate = $ThePlanet['last_update'];
                PlanetResourceUpdate($TheUser, $ThePlanet, $QueueList[$IDX]['endtime'], true);
                $BuiltShips = HandleShipyardQueue($TheUser, $ThePlanet, $QueueList[$IDX]['endtime'] - $LastPlanetUpdate, $QueueList[$IDX]['endtime']);
                if(!empty($BuiltShips))
                {
                    $HPQ_PlanetUpdatedFields[] = 'shipyardQueue';
                    $HPQ_PlanetUpdatedFields[] = 'shipyardQueue_additionalWorkTime';
                    foreach($BuiltShips as $KeyID => $Count)
                    {
                        $HPQ_PlanetUpdatedFields[] = $_Vars_GameElements[$KeyID];
                    }
                }
                $NeedQueueListRebuild = HandlePlanetQueue_StructuresSetNext($ThePlanet, $TheUser, $QueueList[$IDX]['endtime']);
            }
        }
        elseif($QueueList[$IDX]['type'] === 2)
        {
            // Queue Type is: Technology on ThePlanet
            $NeedUpdate = true;
            if(HandlePlanetQueue_OnTechnologyEnd($ThePlanet, $TheUser, $QueueList[$IDX]['endtime']))
            {
                $LastPlanetUpdate = $ThePlanet['last_update'];
                PlanetResourceUpdate($TheUser, $ThePlanet, $QueueList[$IDX]['endtime'], true);
                $NeedQueueListRebuild = HandlePlanetQueue_TechnologySetNext($ThePlanet, $TheUser, $QueueList[$IDX]['endtime']);
            }
        }

        if($NeedQueueListRebuild !== true)
        {
            unset($QueueList[$IDX]);
            $IDX += 1;
        }
        else
        {
            $QueueList = HandlePlanetQueue_CreateQueueList($CurrentTime, $ThePlanet);
            $IDX = 0;
        }
    }

    if($ForMultiUpdate === true)
    {
        if($NeedUpdate === true)
        {
            return true;
        }
    }
    else
    {
        if($NeedUpdate === true)
        {
            $HPQ_PlanetUpdatedFields = array_unique($HPQ_PlanetUpdatedFields);
            foreach($HPQ_PlanetUpdatedFields as $Value)
            {
                $Query_Update_Arr[] = "`{$Value}` = '{$ThePlanet[$Value]}'";
            }
            $Query_Update = "UPDATE {{table}} SET ".implode(', ', $Query_Update_Arr)." WHERE `id` = {$ThePlanet['id']} LIMIT 1;";
            doquery($Query_Update, 'planets');
            $HPQ_PlanetUpdatedFields = array();
        }
        if(!empty($HPQ_UserUpdatedFields))
        {
            $HPQ_UserUpdatedFields = array_unique($HPQ_UserUpdatedFields);
            $UpdateFields = array();
            foreach($HPQ_UserUpdatedFields as $Value)
            {
                $UpdateFields[] = "`{$Value}` = '{$TheUser[$Value]}'";
            }
            $Query_Update = "UPDATE {{table}} SET ".implode(', ', $UpdateFields)." WHERE `id` = {$TheUser['id']};";
            doquery($Query_Update, 'users');
            $HPQ_UserUpdatedFields = array();
        }
    }
}

?>
