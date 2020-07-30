<?php

use UniEngine\Engine\Modules\Flights;

function MissionCaseSpy($FleetRow, &$_FleetCache)
{
    global    $_Lang, $_Vars_Prices, $_GameConfig, $_EnginePath, $UserStatsData, $UserDev_Log,
            $_User, $GlobalParsedTasks;
    static    $SpyTargetIncluded = false;

    $Return = array();
    $FleetDestroyed = false;
    $Now = time();

    if($FleetRow['calcType'] == 1)
    {
        if($SpyTargetIncluded !== true)
        {
            // Include SpyTarget Function
            include($_EnginePath.'includes/functions/SpyTarget.php');
            $SpyTargetIncluded = true;
        }

        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission_Time'] = $Now;

        // Select Attacked Planet & User from $_FleetCache
        $IsAbandoned = ($FleetRow['fleet_target_owner'] > 0 ? false : true);
        $TargetPlanet = &$_FleetCache['planets'][$FleetRow['fleet_end_id']];
        $TargetUser = &$_FleetCache['users'][$FleetRow['fleet_target_owner']];

        $Morale_IsEmptyReport = false;
        if(MORALE_ENABLED)
        {
            Morale_ReCalculate($FleetRow, $FleetRow['fleet_start_time']);

            if($FleetRow['morale_level'] <= MORALE_PENALTY_EMPTYSPYREPORT)
            {
                $Morale_RandomizeEmptySpyReport = mt_rand(1, 100);
                if($Morale_RandomizeEmptySpyReport <= MORALE_PENALTY_EMPTYSPYREPORT_CHANCE)
                {
                    $Morale_IsEmptyReport = true;
                }
            }
        }

        // Update planet before attack begins
        $UpdateResult = HandleFullUserUpdate($TargetUser, $TargetPlanet, $_FleetCache['planets'][$TargetUser['techQueue_Planet']], $FleetRow['fleet_start_time'], true, true);
        if(!empty($UpdateResult))
        {
            foreach($UpdateResult as $PlanetID => $Value)
            {
                if($Value === true)
                {
                    $_FleetCache['updatePlanets'][$PlanetID] = true;
                }
            }
        }

        $CurrentUserID = $FleetRow['fleet_owner'];
        $TargetUserID = $TargetPlanet['id_owner'];
        $CurrentSpyLvl = $FleetRow['tech_espionage'];
        $TargetSpyLvl = $TargetUser['tech_espionage'];
        if($TargetUser['spy_jam_time'] > $Now)
        {
            $TargetSpyLvl += 5;
        }

        // Select All Defending Fleets on the Orbit from $_FleetCache
        $DefendingFleets = array();
        if(!empty($_FleetCache['defFleets'][$FleetRow['fleet_end_id']]))
        {
            global $_Vars_GameElements;
            foreach($_FleetCache['defFleets'][$FleetRow['fleet_end_id']] as $FleetData)
            {
                if($_FleetCache['fleetRowStatus'][$FleetData['fleet_id']]['isDestroyed'] !== true)
                {
                    $TempShips = String2Array($FleetData['fleet_array']);
                    foreach($TempShips as $ShipID => $ShipCount)
                    {
                        if(!isset($DefendingFleets[$_Vars_GameElements[$ShipID]]))
                        {
                            $DefendingFleets[$_Vars_GameElements[$ShipID]] = 0;
                        }
                        $DefendingFleets[$_Vars_GameElements[$ShipID]] += $ShipCount;
                    }
                }
            }
        }

        // Trigger Tasks Check
        if($_User['id'] == $FleetRow['fleet_owner'])
        {
            $CurrentUser = $_User;
        }
        else
        {
            if(empty($GlobalParsedTasks[$FleetRow['fleet_owner']]['tasks_done_parsed']))
            {
                $GetUserTasksDone['tasks_done'] = $_FleetCache['userTasks'][$FleetRow['fleet_owner']];
                Tasks_CheckUservar($GetUserTasksDone);
                $GlobalParsedTasks[$FleetRow['fleet_owner']] = $GetUserTasksDone;
            }
            $CurrentUser = $GlobalParsedTasks[$FleetRow['fleet_owner']];
            $CurrentUser['id'] = $FleetRow['fleet_owner'];
        }
        Tasks_TriggerTask($CurrentUser, 'SPY_OTHER_USER');

        $ShipsCount = 0;
        $FleetArray = explode(';', $FleetRow['fleet_array']);
        foreach($FleetArray as $FleetData)
        {
            $FleetData = explode(',', $FleetData);
            if($FleetData[0] == '210')
            {
                $ShipsCount += $FleetData[1];
            }
        }

        if($ShipsCount > 0)
        {
            static $LangIncluded = false;
            if(!$LangIncluded)
            {
                includeLang('spyReport');
                $LangIncluded = true;
            }

            $SpyToolDebris = $ShipsCount * $_Vars_Prices[210]['crystal'] * ($_GameConfig['Fleet_Cdr'] / 100);

            $SimData = array();

            $MaterialsInfo = SpyTarget($TargetPlanet, 0, $_Lang['sys_spy_maretials'], array('uid' => $TargetUser['id'], 'username' => $TargetUser['username'], 'isEmptyReport' => $Morale_IsEmptyReport));
            $Materials = $MaterialsInfo['Array'];

            $PlanetFleetInfo = SpyTarget($TargetPlanet, 1, $_Lang['sys_spy_fleet']);
            $PlanetDefendingFleetsInfo = SpyTarget($DefendingFleets, 1, $_Lang['sys_spy_deffleet']);
            $PlanetFleet = $Materials;
            $PlanetFleet = array_merge($PlanetFleet, $PlanetFleetInfo['Array'], $PlanetDefendingFleetsInfo['Array']);

            $PlanetDefenInfo = SpyTarget($TargetPlanet, 2, $_Lang['sys_spy_defenses']);
            $PlanetDefense = $PlanetFleet;
            $PlanetDefense = array_merge($PlanetDefense, $PlanetDefenInfo['Array']);

            $PlanetBuildInfo = SpyTarget($TargetPlanet, 3, $_Lang['tech'][0]);
            $PlanetBuildings = $PlanetDefense;
            $PlanetBuildings = array_merge($PlanetBuildings, $PlanetBuildInfo['Array']);

            $TargetTechnInfo = SpyTarget($TargetUser, 4, $_Lang['tech'][100]);
            $TargetTechnos = $PlanetBuildings;
            $TargetTechnos = array_merge($TargetTechnos, $TargetTechnInfo['Array']);

            $TargetMoraleInfo = SpyTarget($TargetUser, 5, '', array('SpyTime' => $FleetRow['fleet_start_time']));
            $TargetMorale = $TargetTechnos;
            $TargetMorale = array_merge($TargetMorale, $TargetMoraleInfo['Array']);

            $Difference = $CurrentSpyLvl - $TargetSpyLvl;
            if($Difference == 0)
            {
                $Difference = 1;
            }
            elseif($Difference > 0)
            {
                $Difference = pow(2, $Difference);
            }
            else
            {
                $Difference = pow(2, ($Difference * -1));
                $Difference = 1 / $Difference;
            }
            $TargetForce = (($PlanetFleetInfo['Count'] + $PlanetDefendingFleetsInfo['Count']) * $ShipsCount) / (4 * $Difference);

            if($TargetForce > 100)
            {
                $TargetForce = 100;
            }
            $TargetChances = rand(0, $TargetForce);
            $SpyerChances = rand(1, 100);
            if($TargetChances >= $SpyerChances)
            {
                $DestProba = array('{spdes2}', $TargetChances);
                $FleetDestroyed = true;
            }
            elseif($TargetChances < $SpyerChances)
            {
                $DestProba = array('{spdes1}', $TargetChances);
            }

            if($Morale_IsEmptyReport)
            {
                $ST = -1;
            }
            else
            {
                if($TargetSpyLvl > $CurrentSpyLvl)
                {
                    $Diff = $TargetSpyLvl - $CurrentSpyLvl;
                    $ST = sqrt($ShipsCount) - ($Diff * $Diff);
                }
                elseif($TargetSpyLvl == $CurrentSpyLvl)
                {
                    $ST = sqrt($ShipsCount);
                }
                else
                {
                    $Diff = $CurrentSpyLvl - $TargetSpyLvl;
                    $ST = sqrt($ShipsCount) + ($Diff * $Diff);
                }
            }

            if($IsAbandoned AND $ST >= 7)
            {
                $ST = 6;
            }

            $General = array('{spatk}{GoToSimButton}', $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'], $FleetRow['fleet_end_type']);
            if($ST <= 1)
            {
                $Materials[] = $General;
                $Materials[] = $DestProba;
                $Message['msg_text'] = $Materials;
            }
            if($ST > 1 AND $ST <= 2)
            {
                $PlanetFleet[] = $General;
                $PlanetFleet[] = $DestProba;
                $Message['msg_text'] = $PlanetFleet;
                foreach($PlanetFleetInfo['Sim'] as $ID => $Count)
                {
                    $SimData[$ID] = $Count;
                }
            }
            if($ST > 2 AND $ST <= 4)
            {
                $PlanetDefense[] = $General;
                $PlanetDefense[] = $DestProba;
                $Message['msg_text'] = $PlanetDefense;
                foreach($PlanetFleetInfo['Sim'] as $ID => $Count)
                {
                    $SimData[$ID] = $Count;
                }
                foreach($PlanetDefenInfo['Sim'] as $ID => $Count)
                {
                    $SimData[$ID] = $Count;
                }
            }
            if($ST > 4 AND $ST < 7)
            {
                $PlanetBuildings[] = $General;
                $PlanetBuildings[] = $DestProba;
                $Message['msg_text'] = $PlanetBuildings;
                foreach($PlanetFleetInfo['Sim'] as $ID => $Count)
                {
                    $SimData[$ID] = $Count;
                }
                foreach($PlanetDefenInfo['Sim'] as $ID => $Count)
                {
                    $SimData[$ID] = $Count;
                }
            }
            if($ST >= 7 AND $ST < 14)
            {
                $TargetTechnos[] = $General;
                $TargetTechnos[] = $DestProba;
                $Message['msg_text'] = $TargetTechnos;
                foreach($PlanetFleetInfo['Sim'] as $ID => $Count)
                {
                    $SimData[$ID] = $Count;
                }
                foreach($PlanetDefenInfo['Sim'] as $ID => $Count)
                {
                    $SimData[$ID] = $Count;
                }
                foreach($TargetTechnInfo['Sim'] as $ID => $Count)
                {
                    $SimData[$ID] = $Count;
                }
            }
            if($ST >= 14)
            {
                $TargetMorale[] = $General;
                $TargetMorale[] = $DestProba;
                $Message['msg_text'] = $TargetMorale;
                foreach($PlanetFleetInfo['Sim'] as $ID => $Count)
                {
                    $SimData[$ID] = $Count;
                }
                foreach($PlanetDefenInfo['Sim'] as $ID => $Count)
                {
                    $SimData[$ID] = $Count;
                }
                foreach($TargetTechnInfo['Sim'] as $ID => $Count)
                {
                    $SimData[$ID] = $Count;
                }
            }

            if(!empty($SimData))
            {
                $Message['sim'] = '';
                foreach($SimData as $ID => $Count)
                {
                    $Message['sim'] .= "{$ID},{$Count};";
                }
            }

            $Message = json_encode($Message);
            Cache_Message($CurrentUserID, 0, $FleetRow['fleet_start_time'], 0, '003', '014', $Message);

            if(!$IsAbandoned)
            {
                $Message = false;
                $Message['msg_id'] = '076';
                $PlanetOrMoon = ($FleetRow['fleet_start_type'] == 1) ? $_Lang['sys_MIP_sending_planet'] : $_Lang['sys_MIP_sending_moon'];
                $PlanetOrMoonYour = ($TargetPlanet['planet_type'] == 1) ? $_Lang['near_your_planet'] : $_Lang['near_your_moon'];
                $Message['args'] = array
                (
                    $PlanetOrMoon, $FleetRow['attacking_planet_name'], $FleetRow['fleet_start_galaxy'],
                    $FleetRow['fleet_start_system'], $FleetRow['fleet_start_galaxy'], $FleetRow['fleet_start_system'],
                    $FleetRow['fleet_start_planet'], $CurrentUserID, $FleetRow['username'], $PlanetOrMoonYour, $TargetPlanet['name'], $TargetPlanet['galaxy'],
                    $TargetPlanet['system'], $TargetPlanet['galaxy'], $TargetPlanet['system'], $TargetPlanet['planet'],
                    $TargetChances.'%'.(($FleetDestroyed === true) ? '<br/>'.$_Lang['spy_dest_dest'] : '')
                );
                $Message = json_encode($Message);
                Cache_Message($TargetUserID, 0, $FleetRow['fleet_start_time'], 0, '006', '015', $Message);
            }

            if($FleetDestroyed === true)
            {
                if($FleetRow['ally_id'] == 0 OR ($TargetUser['ally_id'] != $FleetRow['ally_id']))
                {
                    if (empty($UserStatsData[$FleetRow['fleet_owner']])) {
                        $UserStatsData[$FleetRow['fleet_owner']] = Flights\Utils\Initializers\initUserStatsMap();
                    }
                    if (empty($UserStatsData[$TargetPlanet['id_owner']])) {
                        $UserStatsData[$TargetPlanet['id_owner']] = Flights\Utils\Initializers\initUserStatsMap();
                    }
                    $UserStatsData[$FleetRow['fleet_owner']]['lost_210'] += $ShipsCount;
                    $UserStatsData[$TargetPlanet['id_owner']]['destroyed_210'] += $ShipsCount;
                }

                if($SpyToolDebris <= 0)
                {
                    if($TargetPlanet['planet_type'] == 1)
                    {
                        $Query_UpdateGalaxy_SearchField = 'id_planet';
                        $CacheKey = 'byPlanet';
                    }
                    else
                    {
                        $Query_UpdateGalaxy_SearchField = 'id_moon';
                        $CacheKey = 'byMoon';
                    }

                    if(isset($_FleetCache['galaxyMap'][$CacheKey][$FleetRow['fleet_end_id']]) && $_FleetCache['galaxyMap'][$CacheKey][$FleetRow['fleet_end_id']] > 0)
                    {
                        if(!isset($_FleetCache['galaxy'][$_FleetCache['galaxyMap'][$CacheKey][$FleetRow['fleet_end_id']]]['metal']))
                        {
                            $_FleetCache['galaxy'][$_FleetCache['galaxyMap'][$CacheKey][$FleetRow['fleet_end_id']]]['metal'] = 0;
                        }
                        if(!isset($_FleetCache['galaxy'][$_FleetCache['galaxyMap'][$CacheKey][$FleetRow['fleet_end_id']]]['crystal']))
                        {
                            $_FleetCache['galaxy'][$_FleetCache['galaxyMap'][$CacheKey][$FleetRow['fleet_end_id']]]['crystal'] = 0;
                        }

                        $_FleetCache['galaxy'][$_FleetCache['galaxyMap'][$CacheKey][$FleetRow['fleet_end_id']]]['crystal'] += $SpyToolDebris;
                        $_FleetCache['galaxy'][$_FleetCache['galaxyMap'][$CacheKey][$FleetRow['fleet_end_id']]]['updated'] = true;
                        $_FleetCache['updated']['galaxy'] = true;
                    }
                    else
                    {
                        $Query_UpdateGalaxy = '';
                        $Query_UpdateGalaxy .= "UPDATE {{table}} SET `crystal` = `crystal` + {$SpyToolDebris} ";
                        $Query_UpdateGalaxy .= "WHERE `{$Query_UpdateGalaxy_SearchField}` = {$FleetRow['fleet_end_id']} LIMIT 1; ";
                        $Query_UpdateGalaxy .= "MISSION SPY [Q01][FID: {$FleetRow['fleet_id']}]";
                        doquery($Query_UpdateGalaxy, 'galaxy');
                    }
                }

                $UserDev_Log[] = array('UserID' => $FleetRow['fleet_owner'], 'PlanetID' => '0', 'Date' => $FleetRow['fleet_start_time'], 'Place' => 18, 'Code' => '1', 'ElementID' => '210', 'AdditionalData' => $ShipsCount);
                $Return['FleetsToDelete'][] = $FleetRow['fleet_id'];
                $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Destroyed'] = true;
                $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Destroyed_Reason'] = Flights\Enums\FleetDestructionReason::ESPIONAGE_SHOTDOWN;
                $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Info_HasLostShips'] = true;

                $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['isDestroyed'] = true;
            }
            else
            {
                if($_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['calcCount'] == 1)
                {
                    $_FleetCache['updateFleets'][$FleetRow['fleet_id']]['fleet_mess'] = 1;
                }
            }
        }
    }
    if($FleetRow['calcType'] == 3 && (!isset($_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['isDestroyed']) || $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['isDestroyed'] !== true))
    {
        $Return['FleetsToDelete'][] = $FleetRow['fleet_id'];
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_ComeBack'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_ComeBack_Time'] = $Now;
        RestoreFleetToPlanet($FleetRow, true, $_FleetCache);
    }

    return $Return;
}

?>
