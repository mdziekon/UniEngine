<?php

function HandlePlanetQueue_StructuresSetNext(&$ThePlanet, &$TheUser, $CurrentTime, $RunStandAlone = false)
{
    global    $_Vars_GameElements, $_Lang, $_GameConfig, $_Vars_IndestructibleBuildings, $_Vars_MaxElementLevel,
            $_Vars_PremiumBuildings, $_Vars_PremiumBuildingPrices,
            $UserDev_Log, $HPQ_PlanetUpdatedFields, $HPQ_UserUpdatedFields, $HFUU_UsersToUpdate;

    $Return = false;
    $RemovedTime = 0;

    if($ThePlanet['buildQueue_firstEndTime'] == 0 AND !empty($ThePlanet['buildQueue']))
    {
        $NeedUpdate = true;
        $HPQ_PlanetUpdatedFields[] = 'buildQueue';
        $HPQ_PlanetUpdatedFields[] = 'buildQueue_firstEndTime';

        $Queue = explode(';', $ThePlanet['buildQueue']);
        $QueueEnd = end($Queue);
        if(empty($QueueEnd))
        {
            array_pop($Queue);
        }
        $MaxFields = $ThePlanet['field_max'] + ($ThePlanet[$_Vars_GameElements[33]] * FIELDS_ADDED_BY_TERRAFORMER);
        $QueueLength = count($Queue);

        for($i = 0; $i < $QueueLength; $i += 1)
        {
            $ElementData = explode(',', $Queue[$i]);
            $ElementID = $ElementData[0];
            $ElementLevel = $ElementData[1];
            $ElementTime = $ElementData[2];
            $ElementEndTime = $ElementData[3];
            $ElementMode = $ElementData[4];
            $ForDestroy = ($ElementMode === 'build' ? false : true);

            $BlockBuilding = false;
            $BlockReason = false;

            $HaveResources = IsElementBuyable($TheUser, $ThePlanet, $ElementID, $ForDestroy);
            if($HaveResources === true)
            {
                if($ForDestroy === true)
                {
                    if(!isset($_Vars_IndestructibleBuildings[$ElementID]) || $_Vars_IndestructibleBuildings[$ElementID] != 1)
                    {
                        if($ThePlanet[$_Vars_GameElements[$ElementID]] <= 0)
                        {
                            $BlockBuilding = true;
                            $BlockReason = 1;
                        }
                    }
                    else
                    {
                        $BlockBuilding = true;
                        $BlockReason = 2;
                    }
                }
                else
                {
                    if(IsTechnologieAccessible($TheUser, $ThePlanet, $ElementID))
                    {
                        if($ThePlanet['field_current'] < $MaxFields)
                        {
                            if(isset($_Vars_MaxElementLevel[$ElementID]) && $ElementLevel > $_Vars_MaxElementLevel[$ElementID])
                            {
                                $BlockBuilding = true;
                                $BlockReason = 5;
                            }
                            else if(isset($_Vars_PremiumBuildings[$ElementID]) && $_Vars_PremiumBuildings[$ElementID] == 1 && isset($_Vars_PremiumBuildingPrices[$ElementID]) && $_Vars_PremiumBuildingPrices[$ElementID] > 0 AND $TheUser['darkEnergy'] < $_Vars_PremiumBuildingPrices[$ElementID])
                            {
                                $BlockBuilding = true;
                                $BlockReason = 6;
                            }
                            else if($ElementID == 31 AND $TheUser['techQueue_Planet'] > 0 AND $TheUser['techQueue_EndTime'] > 0 AND $_GameConfig['BuildLabWhileRun'] != 1)
                            {
                                $BlockBuilding = true;
                                $BlockReason = 7;
                            }
                        }
                        else
                        {
                            $BlockBuilding = true;
                            $BlockReason = 4;
                        }
                    }
                    else
                    {
                        $BlockBuilding = true;
                        $BlockReason = 3;
                    }
                }
            }
            else
            {
                $BlockBuilding = true;
                $BlockReason = 0;
            }

            if($BlockBuilding === false)
            {
                $RemoveRes = GetBuildingPrice($TheUser, $ThePlanet, $ElementID, true, $ForDestroy);
                $ThePlanet['metal'] -= $RemoveRes['metal'];
                $ThePlanet['crystal'] -= $RemoveRes['crystal'];
                $ThePlanet['deuterium'] -= $RemoveRes['deuterium'];
                if(isset($_Vars_PremiumBuildings[$ElementID]) && $_Vars_PremiumBuildings[$ElementID] == 1 && isset($_Vars_PremiumBuildingPrices[$ElementID]) && $_Vars_PremiumBuildingPrices[$ElementID] > 0)
                {
                    $NeedUpdateUser = true;
                    $TheUser['darkEnergy'] -= $_Vars_PremiumBuildingPrices[$ElementID];
                    $HPQ_UserUpdatedFields[] = 'darkEnergy';
                    $HFUU_UsersToUpdate[$TheUser['id']] = true;
                }
                $ThePlanet['buildQueue_firstEndTime'] = $ElementEndTime;

                if($ForDestroy === true)
                {
                    $SetCode = 2;
                }
                else
                {
                    $SetCode = 1;
                }
                $UserDev_Log[] = array('PlanetID' => $ThePlanet['id'], 'Date' => $CurrentTime, 'Place' => 1, 'Code' => $SetCode, 'ElementID' => $ElementID);

                break;
            }
            else
            {
                $Message = false;
                $ElementName = $_Lang['tech'][$ElementID];

                if($BlockReason === 0)
                {
                    // No Resources
                    $Needed = GetBuildingPrice($TheUser, $ThePlanet, $ElementID, true, $ForDestroy);
                    $Message['msg_id'] = '082';
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
                    // Destroy 0 level
                    $Message['msg_id'] = '001';
                    $Message['args'] = array($ElementName);
                }
                elseif($BlockReason === 2)
                {
                    // Undestroyable
                    $Message['msg_id'] = '004';
                    $Message['args'] = array($ElementName);
                }
                elseif($BlockReason === 3)
                {
                    // No Technology
                    $Message['msg_id'] = '070';
                    $Message['args'] = array($ElementName, (($ThePlanet['planet_type'] == 1) ? $_Lang['on_planet'] : $_Lang['on_moon']), $ThePlanet['name'], $ElementID);
                }
                elseif($BlockReason === 4)
                {
                    // No Free Fields
                    $Message['msg_id'] = '037';
                    $Message['args'] = array((($ThePlanet['planet_type'] == 1) ? $_Lang['on_planet'] : $_Lang['on_moon']), $ThePlanet['name'], $ElementName);
                }
                elseif($BlockReason === 5)
                {
                    // Max level reached
                    $Message['msg_id'] = '083';
                    $Message['args'] = array((($ThePlanet['planet_type'] == 1) ? $_Lang['on_planet'] : $_Lang['on_moon']), $ThePlanet['name'], $ElementName);
                }
                elseif($BlockReason === 6)
                {
                    // No enough DarkEnergy
                    $Message['msg_id'] = '003';
                    $Message['args'] = array($ElementName, $_Vars_PremiumBuildingPrices[$ElementID], $TheUser['darkEnergy']);
                }
                elseif($BlockReason === 7)
                {
                    // Lab in ResearchMode
                    $Message['msg_id'] = '084';
                    $Message['args'] = array($ElementName, (($ThePlanet['planet_type'] == 1) ? $_Lang['on_planet'] : $_Lang['on_moon']), $ThePlanet['name']);
                }
                else
                {
                    // Unknown reason
                    $Message['msg_id'] = '081';
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
                    $TempPlanet = $ThePlanet;
                    foreach($Queue as &$Data)
                    {
                        if(empty($Data))
                        {
                            continue;
                        }
                        $Data = explode(',', $Data);
                        $TempTime = GetBuildingTime($TheUser, $TempPlanet, $Data[0]);
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
                }
                $Return = true;
            }
        }

        if(isset($NeedQueueRebuild))
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
                $ThePlanet['buildQueue'] = implode(';', $Temp);
            }
            else
            {
                $ThePlanet['buildQueue'] = '';
                $ThePlanet['buildQueue_firstEndTime'] = '0';
            }
        }
    }

    if($RunStandAlone === true)
    {
        $Return = false;
        if(isset($NeedUpdate))
        {
            $Query_Update = '';
            $Query_Update .= "UPDATE {{table}} SET ";
            $Query_Update .= "`metal` = '{$ThePlanet['metal']}', `crystal` = '{$ThePlanet['crystal']}', `deuterium` = '{$ThePlanet['deuterium']}', ";
            $Query_Update .= "`buildQueue` = '{$ThePlanet['buildQueue']}', `buildQueue_firstEndTime` = '{$ThePlanet['buildQueue_firstEndTime']}' ";
            $Query_Update .= "WHERE `id` = {$ThePlanet['id']};";
            doquery($Query_Update, 'planets');

            $Return = true;
        }
        if(isset($NeedUpdateUser))
        {
            doquery("UPDATE {{table}} SET `darkEnergy` = '{$TheUser['darkEnergy']}' WHERE `id` = {$TheUser['id']};", 'users');
        }
    }

    return $Return;
}

?>
