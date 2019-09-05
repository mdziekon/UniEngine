<?php

function HandlePlanetQueue_TechnologySetNext(&$ThePlanet, &$TheUser, $CurrentTime, $RunStandAlone = false)
{
    global $_Lang, $_Vars_GameElements, $_Vars_MaxElementLevel, $_Vars_PremiumBuildings, $_Vars_PremiumBuildingPrices, $UserDev_Log, $HPQ_PlanetUpdatedFields, $HPQ_UserUpdatedFields, $HFUU_UsersToUpdate;

    $Return = false;
    $RemovedTime = 0;

    $NeedUpdate = false;
    $NeedUpdateUser = false;

    if($ThePlanet['techQueue_firstEndTime'] == 0 AND !empty($ThePlanet['techQueue']))
    {
        $NeedUpdate = true;
        $NeedQueueRebuild = false;
        $HPQ_PlanetUpdatedFields[] = 'techQueue';
        $HPQ_PlanetUpdatedFields[] = 'techQueue_firstEndTime';
        $HPQ_UserUpdatedFields[] = 'techQueue_Planet';
        $HPQ_UserUpdatedFields[] = 'techQueue_EndTime';
        $HFUU_UsersToUpdate[$TheUser['id']] = true;

        $Queue = explode(';', $ThePlanet['techQueue']);
        $QueueLength = count($Queue);
        for($i = 0; $i < $QueueLength; $i += 1)
        {
            $ElementData = explode(',', $Queue[$i]);
            $ElementID = $ElementData[0];
            $ElementLevel = $ElementData[1];
            $ElementTime = $ElementData[2];
            $ElementEndTime = $ElementData[3];

            $BlockResearch = false;
            $BlockReason = false;

            $HaveResources = IsElementBuyable($TheUser, $ThePlanet, $ElementID, false);
            if($HaveResources === true)
            {
                if(IsTechnologieAccessible($TheUser, $ThePlanet, $ElementID))
                {
                    if(isset($_Vars_MaxElementLevel[$ElementID]) && $ElementLevel > $_Vars_MaxElementLevel[$ElementID])
                    {
                        $BlockResearch = true;
                        $BlockReason = 1;
                    }
                    else if(isset($_Vars_PremiumBuildings[$ElementID]) && $_Vars_PremiumBuildings[$ElementID] == 1 && isset($_Vars_PremiumBuildingPrices[$ElementID]) AND $TheUser['darkEnergy'] < $_Vars_PremiumBuildingPrices[$ElementID])
                    {
                        $BlockResearch = true;
                        $BlockReason = 2;
                    }
                }
                else
                {
                    $BlockResearch = true;
                    $BlockReason = 3;
                }
            }
            else
            {
                $BlockResearch = true;
                $BlockReason = 0;
            }

            if($BlockResearch === false)
            {
                $NeedUpdate = true;
                $NeedUpdateUser = true;

                $RemoveRes = GetBuildingPrice($TheUser, $ThePlanet, $ElementID);
                $ThePlanet['metal'] -= $RemoveRes['metal'];
                $ThePlanet['crystal'] -= $RemoveRes['crystal'];
                $ThePlanet['deuterium'] -= $RemoveRes['deuterium'];
                if(isset($_Vars_PremiumBuildings[$ElementID]) && $_Vars_PremiumBuildings[$ElementID] == 1 && isset($_Vars_PremiumBuildingPrices[$ElementID]))
                {
                    $TheUser['darkEnergy'] -= $_Vars_PremiumBuildingPrices[$ElementID];
                    $HPQ_UserUpdatedFields[] = 'darkEnergy';
                }
                $ThePlanet['techQueue'] = implode(';', $Queue);
                $ThePlanet['techQueue_firstEndTime'] = $ElementEndTime;
                $TheUser['techQueue_Planet'] = $ThePlanet['id'];
                $TheUser['techQueue_EndTime'] = $ElementEndTime;

                $UserDev_Log[] = array('PlanetID' => $ThePlanet['id'], 'Date' => $CurrentTime, 'Place' => 4, 'Code' => 1, 'ElementID' => $ElementID);

                break;
            }
            else
            {
                $Message = false;
                $ElementName = $_Lang['tech'][$ElementID];

                if($BlockReason === 0)
                {
                    // No Resources
                    $Needed = GetBuildingPrice($TheUser, $ThePlanet, $ElementID);
                    $Message['msg_id'] = '085';
                    $Message['args'] = array
                    (
                        $ElementName, $ElementLevel, (($ThePlanet['planet_type'] == 1) ? $_Lang['on_planet'] : $_Lang['on_moon']), $ThePlanet['name'],
                        prettyNumber($ThePlanet['metal']), prettyNumber($ThePlanet['crystal']), prettyNumber($ThePlanet['deuterium']),
                        ($Needed['metal'] > $ThePlanet['metal'] ? 'red' : 'lime'), prettyNumber($Needed['metal']),
                        ($Needed['crystal'] > $ThePlanet['crystal'] ? 'red' : 'lime'), prettyNumber($Needed['crystal']),
                        ($Needed['deuterium'] > $ThePlanet['deuterium'] ? 'red' : 'lime'), prettyNumber($Needed['deuterium'])
                    );
                }
                elseif($BlockReason === 1)
                {
                    // Max level reached
                    $Message['msg_id'] = '086';
                    $Message['args'] = array($ElementName);
                }
                elseif($BlockReason === 2)
                {
                    // No enough DarkEnergy
                    $Message['msg_id'] = '087';
                    $Message['args'] = array($ElementName);
                }
                elseif($BlockReason === 3)
                {
                    // No Technology
                    $Message['msg_id'] = '088';
                    $Message['args'] = array($ElementName, (($ThePlanet['planet_type'] == 1) ? $_Lang['on_planet'] : $_Lang['on_moon']), $ThePlanet['name'], $ElementID);
                }
                else
                {
                    // Unknown reason
                    $Message['msg_id'] = '089';
                    $Message['args'] = array($ElementName, (($ThePlanet['planet_type'] == 1) ? $_Lang['on_planet'] : $_Lang['on_moon']), $ThePlanet['name']);
                }
                $Message = json_encode($Message);
                if(empty($SentMessages) OR !in_array($Message, $SentMessages))
                {
                    $SentMessages[] = $Message;
                    Cache_Message($TheUser['id'], 0, $CurrentTime, 50, '001', '001', $Message);
                }

                // Now rebuild Queue
                unset($Queue[$i]);
                $NeedQueueRebuild = true;
                $RemovedTime = $ElementTime;
                if(($i + 1) < $QueueLength)
                {
                    $TempUser = $TheUser;
                    foreach($Queue as &$Data)
                    {
                        if(empty($Data))
                        {
                            continue;
                        }
                        $Data = explode(',', $Data);
                        $TempTime = GetBuildingTime($TempUser, $ThePlanet, $Data[0]);
                        $TempUser[$_Vars_GameElements[$Data[0]]] += 1;
                        $RemovedTime += ($Data[2] - $TempTime);
                        $Data[1] = $TempUser[$_Vars_GameElements[$Data[0]]];
                        $Data[2] = $TempTime;
                        $Data[3] -= $RemovedTime;
                        $Data = implode(',', $Data);
                    }
                }
                $Return = true;
            }
        }

        if($NeedQueueRebuild === true)
        {
            foreach($Queue as $QueueRow)
            {
                if(!empty($QueueRow))
                {
                    $Temp[] = $QueueRow;
                }
            }
            if(!empty($Temp))
            {
                $ThePlanet['techQueue'] = implode(';', $Queue);
                $ThePlanet['techQueue_firstEndTime'] = $ElementEndTime;
                $TheUser['techQueue_Planet'] = $ThePlanet['id'];
                $TheUser['techQueue_EndTime'] = $ElementEndTime;
            }
            else
            {
                $ThePlanet['techQueue'] = '';
                $ThePlanet['techQueue_firstEndTime'] = '0';
                $TheUser['techQueue_Planet'] = '0';
                $TheUser['techQueue_EndTime'] = '0';
            }
        }
    }

    if($RunStandAlone === true)
    {
        $Return = false;
        if($NeedUpdate === true)
        {
            $Query_Update = '';
            $Query_Update .= "UPDATE {{table}} SET ";
            $Query_Update .= "`metal` = '{$ThePlanet['metal']}', `crystal` = '{$ThePlanet['crystal']}', `deuterium` = '{$ThePlanet['deuterium']}', ";
            $Query_Update .= "`techQueue` = '{$ThePlanet['techQueue']}', `techQueue_firstEndTime` = '{$ThePlanet['techQueue_firstEndTime']}' ";
            $Query_Update .= "WHERE `id` = {$ThePlanet['id']};";
            doquery($Query_Update, 'planets');

            $Return = true;
        }
        if($NeedUpdateUser === true)
        {
            doquery("UPDATE {{table}} SET `techQueue_EndTime` = '{$TheUser['techQueue_EndTime']}', `techQueue_Planet` = '{$TheUser['techQueue_Planet']}', `darkEnergy` = '{$TheUser['darkEnergy']}' WHERE `id` = {$TheUser['id']};", 'users');
        }
    }

    return $Return;
}

?>
