<?php

// -------------------------------------------
// WARNING - WARNING - WARNING
// -------------------------------------------
// This script is NOT COMPATIBLE with current implementation of FleetHandler function and it's supporting functions
// Usage of this script without any changed can (and probably will) cause damage to your DataBase
// Before using it, make sure that it's fully compatible with FleetHandler function
// -------------------------------------------

function MissionCaseExpedition($FleetRow)
{
    global $_Lang, $_Vars_Prices, $enforceSQLUpdate, $FleetArchivePattern, $UserStatsPattern, $UserStatsData;

    $FleetOwner = $FleetRow['fleet_owner'];
    $MessSender = '003';
    $MessTitle = '007';
    $fleetHasBeenDeleted = false;
    $ChangingFleetData = false;

    $AllowSendReturn = false;
    $return = $FleetArchivePattern;
    $return['fleet_id'] = $FleetRow['fleet_id'];

    if($FleetRow['fleet_mess'] == 0)
    {
        if($FleetRow['fleet_end_stay'] < time())
        {
            // Update user stats
            if(empty($UserStatsData[$FleetOwner]))
            {
                $UserStatsData[$FleetOwner] = $UserStatsPattern;
            }
            $UserStatsData[$FleetOwner]['other_expeditions_count'] += 1;

            $AllowSendReturn = true;
            $return['fleet_calculated'] = '1';

            $ChangingFleetData = true;

            // How many points give u every ship
            $PointsFlotte = array
            (
                202 => 1.0,// Small Cargo Ship
                203 => 1.5,// Big Cargo Ship
                204 => 0.5,// Light Hunter
                205 => 1.5,// Heavy Hunter
                206 => 2.0,// cruiser
                207 => 2.5,// Battle Ship
                208 => 0.5,// Colonisator
                209 => 1.0,// Recycler
                210 => 0.01, // Spy Probe
                211 => 3.0,// Bomber
                212 => 0.0,// Solar Satelite
                213 => 3.5,// Destroyer
                214 => 5.0,// Death Star
                215 => 3.2,// BattleShip
                216 => 0.0,// Orbital Station
                217 => 2.0,// Mega Cargo
                218 => 7.5,// Annihilator
                219 => 0.02// Space Shuttle
            );

            $RatioGain = array
            (
                202 => 0.25, // Small Cargo Ship
                203 => 0.2, // Big Cargo Ship
                204 => 0.25, // Light Hunter
                205 => 0.2, // Heavy Hunter
                206 => 0.155,// cruiser
                207 => 0.085, // Battle Ship
                208 => 0.1, // Colonisator
                209 => 0.2, // Recycler
                210 => 0.2, // Spy Probe
                211 => 0.0625,// Bomber
                //212 => 0.0, // Solar Satelite
                213 => 0.04,// Destroyer
                //214 => 0.0, // Death Star
                215 => 0.05,// BattleShip
                //216 => 0.0, // Orbital Station
                217 => 0.0625,// Mega Cargo
                //218 => 0.0, // Annihilator
                219 => 0.05// Space Shuttle
            );

            $FleetCapacity = -($FleetRow['fleet_resource_metal'] + $FleetRow['fleet_resource_crystal'] + $FleetRow['fleet_resource_deuterium']);

            $FleetPoints = 0;
            $FleetMetalCost = 0;
            $FleetCrystCost = 0;
            $FleetDeuteCost = 0;

            $FleetArrTemp = explode(';', $FleetRow['fleet_array']);
            foreach($FleetArrTemp as $Ships)
            {
                if(!empty($Ships))
                {
                    $ShipsTemp = explode(',', $Ships);
                    $FleetArray[$ShipsTemp[0]] = $ShipsTemp[1];

                    $FleetCapacity += $_Vars_Prices[$ShipsTemp[0]]['capacity'] * $ShipsTemp[1];
                    $FleetPoints += ($PointsFlotte[$ShipsTemp[0]] / 10) * $ShipsTemp[1];
                    $FleetMetalCost += $_Vars_Prices[$ShipsTemp[0]]['metal'] * $ShipsTemp[1];
                    $FleetCrystCost += $_Vars_Prices[$ShipsTemp[0]]['crystal'] * $ShipsTemp[1];
                    $FleetDeuteCost += $_Vars_Prices[$ShipsTemp[0]]['deuterium'] * $ShipsTemp[1];
                }
            }
            $FleetCount = $FleetRow['fleet_amount'];
            $FleetStayDuration = ($FleetRow['fleet_end_stay'] - $FleetRow['fleet_start_time']) / 3600;

            $WhatHappened = rand(1,100);

            if($WhatHappened >= 1 AND $WhatHappened <= 15)
            {
                // Partial/Complete Destruction
                $NewFleetCount = 0;

                $DestructionPercent = rand(1,100);
                foreach($FleetArray as $Ship => $Count)
                {
                    $NewCount = floor($Count * ((100 - $DestructionPercent) / 100));
                    $NewFleetArray[$Ship] = $NewCount;
                    $NewFleetCount += $NewCount;
                    if($NewCount < $FleetArray[$Ship])
                    {
                        $DestroyedArray[$Ship] = $FleetArray[$Ship] - $NewCount;
                    }
                }

                if($NewFleetCount < $FleetCount AND $NewFleetCount > 0)
                {
                    foreach($NewFleetArray as $Ship => $Count)
                    {
                        $NewFleetArrayString[] = $Ship.','.$Count;
                    }
                    $NewFleetArray = implode(';', $NewFleetArrayString);

                    $QryUpdateFleet = "UPDATE {{table}} SET ";
                    $QryUpdateFleet .= "`fleet_array` = '{$NewFleetArray}', ";
                    $QryUpdateFleet .= "`fleet_amount` = {$NewFleetCount}, ";
                    $QryUpdateFleet .= "`fleet_mess` = '1'";
                    $QryUpdateFleet .= "WHERE ";
                    $QryUpdateFleet .= "`fleet_id` = '". $FleetRow['fleet_id'] ."'; -- EXPEDITION QUERY 1";
                    doquery($QryUpdateFleet, 'fleets');

                    $return['fleet_end_amount'] = $NewFleetCount;
                    $return['fleet_end_array'] = '"'.$NewFleetArray.'"';

                    $Message = false;
                    $Message['msg_id'] = '0'.rand(42,44);
                    foreach($DestroyedArray as $Ship => $Count)
                    {
                        $Message['args'] .= '<br/>'.$_Lang['tech'][$Ship].' - '.$Count;
                    }

                    $Message = json_encode($Message);

                    Cache_Message($FleetOwner, 0, $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $Message);

                    $FleetRow['fleet_array'] = $NewFleetArray;
                    $FleetRow['fleet_amount']= $NewFleetCount;
                }
                else if($NewFleetCount == $FleetCount)
                {
                    $Message = false;
                    $Message['msg_id'] = '0'.rand(45,47);
                    $Message['args'] = array('');

                    $Message = json_encode($Message);

                    Cache_Message($FleetOwner, 0, $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $Message);
                }
                else
                {
                    $Message = false;
                    $Message['msg_id'] = '0'.rand(48,50);
                    $Message['args']= array('');

                    $Message = json_encode($Message);

                    Cache_Message($FleetOwner, 0, $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $Message);

                    doquery("DELETE FROM {{table}} WHERE `fleet_id` = {$FleetRow['fleet_id']}; -- EXPEDITION QUERY 2", 'fleets');
                    $fleetHasBeenDeleted = TRUE;

                    $return['fleet_destroyed'] = '1';
                    $return['fleet_destroy_reason'] = '"expedition"';
                }
            }
            else if($WhatHappened >= 16 AND $WhatHappened <= 55)
            {
                // Resources found
                $anyResourceMaxIncome = 10000000;

                $resourceTypesList = [
                    'Metal',
                    'Crystal',
                    'Deuterium'
                ];

                $maxResourcesIncome = [
                    'Metal' => ($FleetMetalCost * 0.2),
                    'Crystal' => ($FleetCrystCost * 0.2),
                    'Deuterium' => ($FleetDeuteCost * 0.2),
                ];

                foreach ($resourceTypesList as $resourceKey) {
                    if ($maxResourcesIncome[$resourceKey] <= $anyResourceMaxIncome) {
                        continue;
                    }

                    $maxResourcesIncome[$resourceKey] = $anyResourceMaxIncome;
                }

                $foundResources = [
                    'Metal' => rand(
                        round((($maxResourcesIncome['Metal'] * 0.001) + 1) * (pow(1.2, $FleetStayDuration) - 0.2)),
                        $maxResourcesIncome['Metal']
                    ),
                    'Crystal' => rand(
                        round((($maxResourcesIncome['Crystal'] * 0.001) + 1) * (pow(1.2, $FleetStayDuration) - 0.2)),
                        $maxResourcesIncome['Crystal']
                    ),
                    'Deuterium' => rand(
                        round((($maxResourcesIncome['Deuterium'] * 0.001) + 1) * (pow(1.2, $FleetStayDuration) - 0.2)),
                        $maxResourcesIncome['Deuterium']
                    ),
                ];

                $resourceOrderRolls = [
                    rand(0, 2),
                    rand(0, 1),
                    0
                ];

                $FleetInitCapacity = $FleetCapacity;

                $resourceTypesCount = count($resourceTypesList);

                for ($i = 0; $i < $resourceTypesCount; $i++) {
                    $resourceKeyRoll = $resourceOrderRolls[$i];
                    $resourceKey = $resourceTypesList[$resourceKeyRoll];
                    $thisResourceFound = $foundResources[$resourceKey];

                    if ($FleetCapacity < $thisResourceFound) {
                        $thisResourceFound = $FleetCapacity;
                        $foundResources[$resourceKey] = $FleetCapacity;
                    }

                    $FleetCapacity -= $thisResourceFound;

                    array_splice($resourceTypesList, $resourceKeyRoll, 1);
                }

                $Message = false;

                if($FleetCapacity < $FleetInitCapacity)
                {
                    if($foundResources['Metal'] > 0)
                    {
                        $QryUpdateResources[] = "`fleet_resource_metal` = `fleet_resource_metal` + {$foundResources['Metal']}";
                        $MsgGottenArray[] = $_Lang['Metal'].': '.prettyNumber($foundResources['Metal']);
                        $FleetRow['fleet_resource_metal'] += $foundResources['Metal'];
                    }
                    else
                    {
                        $foundResources['Metal'] = '0';
                    }
                    if($foundResources['Crystal'] > 0)
                    {
                        $QryUpdateResources[] = "`fleet_resource_crystal` = `fleet_resource_crystal` + {$foundResources['Crystal']}";
                        $MsgGottenArray[] = $_Lang['Crystal'].': '.prettyNumber($foundResources['Crystal']);
                        $FleetRow['fleet_resource_crystal'] += $foundResources['Crystal'];
                    }
                    else
                    {
                        $foundResources['Crystal'] = '0';
                    }
                    if($foundResources['Deuterium'] > 0)
                    {
                        $QryUpdateResources[] = "`fleet_resource_deuterium` = `fleet_resource_deuterium` + {$foundResources['Deuterium']}";
                        $MsgGottenArray[] = $_Lang['Deuterium'].': '.prettyNumber($foundResources['Deuterium']);
                        $FleetRow['fleet_resource_deuterium'] += $foundResources['Deuterium'];
                    }
                    else
                    {
                        $foundResources['Deuterium'] = '0';
                    }

                    $return['fleet_end_resource_metal'] = $foundResources['Metal'];
                    $return['fleet_end_resource_crystal'] = $foundResources['Crystal'];
                    $return['fleet_end_resource_deuterium'] = $foundResources['Deuterium'];

                    $QryUpdateFleet = "UPDATE {{table}} SET ";
                    $QryUpdateFleet .= implode(", ", $QryUpdateResources);
                    $QryUpdateFleet .= ", `fleet_mess` = 1 ";
                    $QryUpdateFleet .= "WHERE `fleet_id` = {$FleetRow['fleet_id']};";
                    doquery($QryUpdateFleet, 'fleets');

                    $Message['msg_id'] = '0'.rand(51, 53);
                    $Message['args'] = implode('<br/>', $MsgGottenArray);
                }
                else
                {
                    $Message['msg_id'] = '0'.rand(54, 56);
                    $Message['args'] = array('');
                }

                $Message = json_encode($Message);

                Cache_Message($FleetOwner, 0, $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $Message);
            }
            else if($WhatHappened >= 56 AND $WhatHappened <= 65)
            {
                // Found Ships
                $FoundShipsTotal = 0;

                $MaxShipsPoints = 20000;
                if($FleetPoints > $MaxShipsPoints)
                {
                    $FleetPoints = $MaxShipsPoints;
                }

                $HowManyShipsToRandom = rand(1,5);
                $RandomizedShips = array_rand($RatioGain, $HowManyShipsToRandom);
                $NewFleetArray = $FleetArray;
                if((array)$RandomizedShips === $RandomizedShips)
                {
                    foreach($RandomizedShips as $Ship)
                    {
                        $FoundShips[$Ship] = round((($FleetPoints * 5) * $RatioGain[$Ship] * (5 / $HowManyShipsToRandom)) * (pow(1.1, $FleetStayDuration) - 0.1));
                        if($FleetArray[$Ship] > 0)
                        {
                            $ThisShipMaxCount = ($FleetPoints / ($PointsFlotte[$Ship] / 10)) * $RatioGain[$Ship] * 0.8;
                        }
                        else
                        {
                            $ThisShipMaxCount = ($FleetArray[$Ship] * 0.3 * 0.25 * 0.75 * (mt_rand(75,130) / 100)) + ((($FleetPoints - (($PointsFlotte[$Ship] / 10) * $FleetArray[$Ship])) / ($PointsFlotte[$Ship] / 10)) * $RatioGain[$Ship] * 0.8);
                        }
                        $ThisShipMaxCount = floor($ThisShipMaxCount);
                        if($FoundShips[$Ship] > $ThisShipMaxCount)
                        {
                            $FoundShips[$Ship] = $ThisShipMaxCount;
                        }
                        $FoundShipsTotal += $FoundShips[$Ship];
                        $NewFleetArray[$Ship] += $FoundShips[$Ship];
                    }
                }
                else
                {
                    $FoundShips[$RandomizedShips] = round((($FleetPoints * 5) * $RatioGain[$RandomizedShips] * (5 / $HowManyShipsToRandom)) * (pow(1.1, $FleetStayDuration) - 0.1));
                    $FoundShipsTotal += $FoundShips[$RandomizedShips];
                    $NewFleetArray[$RandomizedShips] += $FoundShips[$RandomizedShips];
                }

                if($FoundShipsTotal > 0)
                {
                    foreach($NewFleetArray as $Ship => $Count)
                    {
                        $NewFleetArrayString[] = $Ship.','.$Count;
                    }
                    $NewFleetArray = implode(';', $NewFleetArrayString);

                    $QryUpdateFleet = "UPDATE {{table}} SET ";
                    $QryUpdateFleet .= "`fleet_array` = '{$NewFleetArray}', ";
                    $QryUpdateFleet .= "`fleet_amount` = `fleet_amount` + {$FoundShipsTotal}, ";
                    $QryUpdateFleet .= "`fleet_mess` = '1'";
                    $QryUpdateFleet .= "WHERE ";
                    $QryUpdateFleet .= "`fleet_id` = {$FleetRow['fleet_id']}; -- EXPEDITION QUERY 1";
                    doquery($QryUpdateFleet, 'fleets');

                    $return['fleet_end_amount'] = $FleetRow['fleet_amount'] + $FoundShipsTotal;
                    $return['fleet_end_array'] = '"'.$NewFleetArray.'"';

                    $Message = false;
                    $Message['msg_id'] = '0'.rand(57,58);
                    foreach($FoundShips as $Ship => $Count)
                    {
                        $Message['args'] .= '<br/>'.$_Lang['tech'][$Ship].' - '.$Count;
                    }

                    $FleetRow['fleet_array'] = $NewFleetArray;
                    $FleetRow['fleet_amount']+= $FoundShipsTotal;
                }
                else
                {
                    $Message = false;
                    $Message['msg_id'] = '0'.rand(59,60);
                    $Message['args'] = array('');
                }

                $Message = json_encode($Message);

                Cache_Message($FleetOwner, 0, $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $Message);
            }
            else if($WhatHappened >= 66 AND $WhatHappened <= 85)
            {
                // Nothing happened

                doquery("UPDATE {{table}} SET `fleet_mess` = '1' WHERE `fleet_id` = {$FleetRow['fleet_id']};", 'fleets');

                $Message = false;
                $Message['msg_id'] = '015';
                $Message['args'] = array('');

                $Message = json_encode($Message);

                Cache_Message($FleetOwner, 0, $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $Message);
            }
            else if($WhatHappened >= 86 AND $WhatHappened <= 100)
            {
                // Error in Navigation System

                $RandomTimeError = rand(900, TIME_DAY);
                doquery("UPDATE {{table}} SET `fleet_mess` = '1', `fleet_end_time` = `fleet_end_time` + {$RandomTimeError} WHERE `fleet_id` = {$FleetRow['fleet_id']};", 'fleets');

                $Message = false;
                $Message['msg_id'] = '061';
                $Message['args'] = array(pretty_time($RandomTimeError));

                $Message = json_encode($Message);

                Cache_Message($FleetOwner, 0, $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $Message);

                $FleetRow['fleet_end_time'] += $RandomTimeError;
            }
            else
            {
                // Nothing (yet)

                doquery("UPDATE {{table}} SET `fleet_mess` = '1' WHERE `fleet_id` = {$FleetRow['fleet_id']};", 'fleets');

                $Message = false;
                $Message['msg_id'] = '015';
                $Message['args'] = array('');

                $Message = json_encode($Message);

                Cache_Message($FleetOwner, 0, $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $Message);
            }
        }
    }

    if($FleetRow['fleet_end_time'] < time() AND $fleetHasBeenDeleted === false)
    {

        $AllowSendReturn = true;
        $return['fleet_came_back'] = '1';
        RestoreFleetToPlanet($FleetRow, true);
        doquery("DELETE FROM {{table}} WHERE `fleet_id` = {$FleetRow['fleet_id']}; -- EXPEDITION QUERY 2", 'fleets');

        $enforceSQLUpdate = 1;

        $Message = false;
        $Message['msg_id'] = '017';
        $Message['args'] = array('');

        $Message = json_encode($Message);

        Cache_Message($FleetOwner, 0, $FleetRow['fleet_end_time'], 15, $MessSender, $MessTitle, $Message);
    }

    if($AllowSendReturn)
    {
        return $return;
    }
}

?>
