<?php

function MissionCaseDestruction($FleetRow, &$_FleetCache)
{
    global    $_EnginePath, $_User, $_Vars_Prices, $_Lang, $_Vars_GameElements, $_Vars_ElementCategories, $_GameConfig, $ChangeCoordinatesForFleets,
            $UserStatsPattern, $UserStatsData, $UserDev_Log, $IncludeCombatEngine, $HPQ_PlanetUpdatedFields, $GlobalParsedTasks;

    $Return = array();
    $FleetDestroyedByMoon = false;
    $FleetHasBeenDestroyed = false;
    $DestructionDone = false;
    $MoonHasBeenCreated = false;

    $MoonHasBeenDestroyed = 0;

    $Now = time();

    if($FleetRow['calcType'] == 1)
    {
        $TriggerTasksCheck = array();

        if($IncludeCombatEngine !== true)
        {
            // Include Combat Engine & BattleReport Creator
            include($_EnginePath.'includes/functions/CreateBattleReport.php');
            include($_EnginePath.'includes/CombatEngineAres.php');
            $IncludeCombatEngine = true;
        }

        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_Mission_Time'] = $Now;

        // Select Attacked Planet & User from $_FleetCache
        $IsAbandoned = ($FleetRow['fleet_target_owner'] > 0 ? false : true);
        $TargetPlanet = &$_FleetCache['planets'][$FleetRow['fleet_end_id']];
        $TargetUser = &$_FleetCache['users'][$FleetRow['fleet_target_owner']];
        $IsAllyFight = (($FleetRow['ally_id'] == 0 OR ($FleetRow['ally_id'] != $TargetUser['ally_id'])) ? false : true);

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

        $TargetUserID = $TargetPlanet['id_owner'];
        $TargetPlanetGetName = $TargetPlanet['name'];
        $TargetPlanetID = $TargetPlanet['id'];

        if(!$IsAbandoned)
        {
            $IdleHours = floor(($FleetRow['fleet_start_time'] - $TargetUser['onlinetime']) / 3600);
            if($IdleHours > 0)
            {
                $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_End_Owner_IdleHours'] = $IdleHours;
            }
        }

        // Create data arrays for attacker and main defender
        $CurrentUserID = $FleetRow['fleet_owner'];
        $DefendersIDs[] = $TargetUser['id'];
        $AttackersIDs[] = $FleetRow['fleet_owner'];
        $AttackingFleets = array();
        $DefendingFleets = array();

        $DefendingTechs[0] = array
        (
            109 => $TargetUser['tech_weapons'],
            110 => $TargetUser['tech_armour'],
            111 => $TargetUser['tech_shielding'],
            120 => $TargetUser['tech_laser'],
            121 => $TargetUser['tech_ion'],
            122 => $TargetUser['tech_plasma'],
            125 => $TargetUser['tech_antimatter'],
            126 => $TargetUser['tech_disintegration'],
            199 => $TargetUser['tech_graviton']
        );
        $DefendersData[0] = array
        (
            'id' => $TargetUser['id'],
            'username' => $TargetUser['username'],
            'techs' => Array2String($DefendingTechs[0]),
            'pos' => "{$FleetRow['fleet_end_galaxy']}:{$FleetRow['fleet_end_system']}:{$FleetRow['fleet_end_planet']}"
        );
        if(!empty($TargetUser['ally_tag']))
        {
            $DefendersData[0]['ally'] = $TargetUser['ally_tag'];
        }

        $AttackingTechs[0] = array
        (
            109 => $FleetRow['tech_weapons'],
            110 => $FleetRow['tech_armour'],
            111 => $FleetRow['tech_shielding'],
            120 => $FleetRow['tech_laser'],
            121 => $FleetRow['tech_ion'],
            122 => $FleetRow['tech_plasma'],
            125 => $FleetRow['tech_antimatter'],
            126 => $FleetRow['tech_disintegration'],
            199 => $FleetRow['tech_graviton']
        );
        $AttackersData[0] = array
        (
            'id' => $FleetRow['fleet_owner'],
            'username' => $FleetRow['username'],
            'techs' => Array2String($AttackingTechs[0]),
            'pos' => "{$FleetRow['fleet_start_galaxy']}:{$FleetRow['fleet_start_system']}:{$FleetRow['fleet_start_planet']}"
        );
        if(!empty($FleetRow['ally_tag']))
        {
            $AttackersData[0]['ally'] = $FleetRow['ally_tag'];
        }

        // MoraleSystem Init
        if(MORALE_ENABLED)
        {
            if(!empty($_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]))
            {
                $FleetRow['morale_level'] = $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['level'];
                $FleetRow['morale_droptime'] = $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['droptime'];
                $FleetRow['morale_lastupdate'] = $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['lastupdate'];
            }
            Morale_ReCalculate($FleetRow, $FleetRow['fleet_start_time']);
            $AttackersData[0]['morale'] = $FleetRow['morale_level'];
            $AttackersData[0]['moralePoints'] = $FleetRow['morale_points'];

            // Bonuses
            if($FleetRow['morale_level'] >= MORALE_BONUS_FLEETPOWERUP1)
            {
                $AttackingTechs[0]['TotalForceFactor'] = MORALE_BONUS_FLEETPOWERUP1_FACTOR;
            }
            if($FleetRow['morale_level'] >= MORALE_BONUS_FLEETSHIELDUP1)
            {
                $AttackingTechs[0]['TotalShieldFactor'] = MORALE_BONUS_FLEETSHIELDUP1_FACTOR;
            }
            if($FleetRow['morale_level'] >= MORALE_BONUS_FLEETSDADDITION)
            {
                $AttackingTechs[0]['SDAdd'] = MORALE_BONUS_FLEETSDADDITION_VALUE;
            }
            // Penalties
            if($FleetRow['morale_level'] <= MORALE_PENALTY_FLEETPOWERDOWN1)
            {
                $AttackingTechs[0]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN1_FACTOR;
            }
            if($FleetRow['morale_level'] <= MORALE_PENALTY_FLEETPOWERDOWN2)
            {
                $AttackingTechs[0]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN2_FACTOR;
            }
            if($FleetRow['morale_level'] <= MORALE_PENALTY_FLEETSHIELDDOWN1)
            {
                $AttackingTechs[0]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN1_FACTOR;
            }
            if($FleetRow['morale_level'] <= MORALE_PENALTY_FLEETSHIELDDOWN2)
            {
                $AttackingTechs[0]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN2_FACTOR;
            }
            if($FleetRow['morale_level'] <= MORALE_PENALTY_FLEETSDDOWN)
            {
                $AttackingTechs[0]['SDFactor'] = MORALE_PENALTY_FLEETSDDOWN_FACTOR;
            }

            if(!$IsAbandoned)
            {
                if(!empty($_FleetCache['MoraleCache'][$TargetUser['id']]))
                {
                    $TargetUser['morale_level'] = $_FleetCache['MoraleCache'][$TargetUser['id']]['level'];
                    $TargetUser['morale_droptime'] = $_FleetCache['MoraleCache'][$TargetUser['id']]['droptime'];
                    $TargetUser['morale_lastupdate'] = $_FleetCache['MoraleCache'][$TargetUser['id']]['lastupdate'];
                }
                Morale_ReCalculate($TargetUser, $FleetRow['fleet_start_time']);
                $DefendersData[0]['morale'] = $TargetUser['morale_level'];
                $DefendersData[0]['moralePoints'] = $TargetUser['morale_points'];

                // Bonuses
                if($TargetUser['morale_level'] >= MORALE_BONUS_FLEETPOWERUP1)
                {
                    $DefendingTechs[0]['TotalForceFactor'] = MORALE_BONUS_FLEETPOWERUP1_FACTOR;
                }
                if($TargetUser['morale_level'] >= MORALE_BONUS_FLEETSHIELDUP1)
                {
                    $DefendingTechs[0]['TotalShieldFactor'] = MORALE_BONUS_FLEETSHIELDUP1_FACTOR;
                }
                if($TargetUser['morale_level'] >= MORALE_BONUS_FLEETSDADDITION)
                {
                    $DefendingTechs[0]['SDAdd'] = MORALE_BONUS_FLEETSDADDITION_VALUE;
                }
                // Penalties
                if($TargetUser['morale_level'] <= MORALE_PENALTY_FLEETPOWERDOWN1)
                {
                    $DefendingTechs[0]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN1_FACTOR;
                }
                if($TargetUser['morale_level'] <= MORALE_PENALTY_FLEETPOWERDOWN2)
                {
                    $DefendingTechs[0]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN2_FACTOR;
                }
                if($TargetUser['morale_level'] <= MORALE_PENALTY_FLEETSHIELDDOWN1)
                {
                    $DefendingTechs[0]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN1_FACTOR;
                }
                if($TargetUser['morale_level'] <= MORALE_PENALTY_FLEETSHIELDDOWN2)
                {
                    $DefendingTechs[0]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN2_FACTOR;
                }
                if($TargetUser['morale_level'] <= MORALE_PENALTY_FLEETSDDOWN)
                {
                    $DefendingTechs[0]['SDFactor'] = MORALE_PENALTY_FLEETSDDOWN_FACTOR;
                }
            }
        }

        // Select All Defending Fleets on the Orbit from $_FleetCache
        if(!empty($_FleetCache['defFleets'][$FleetRow['fleet_end_id']]))
        {
            $i = 1;
            foreach($_FleetCache['defFleets'][$FleetRow['fleet_end_id']] as $FleetData)
            {
                if($_FleetCache['fleetRowStatus'][$FleetData['fleet_id']]['isDestroyed'] !== true)
                {
                    $DefendingFleets[$i] = String2Array($FleetData['fleet_array']);
                    $DefendingFleetID[$i] = $FleetData['fleet_id'];
                    $DefendingTechs[$i] = array
                    (
                        109 => $FleetData['tech_weapons'],
                        110 => $FleetData['tech_armour'],
                        111 => $FleetData['tech_shielding'],
                        120 => $FleetData['tech_laser'],
                        121 => $FleetData['tech_ion'],
                        122 => $FleetData['tech_plasma'],
                        125 => $FleetData['tech_antimatter'],
                        126 => $FleetData['tech_disintegration'],
                        199 => $FleetData['tech_graviton']
                    );
                    $DefendersData[$i] = array
                    (
                        'id' => $FleetData['fleet_owner'],
                        'username' => $FleetData['username'],
                        'techs' => Array2String($DefendingTechs[$i]),
                        'pos' => "{$FleetData['fleet_start_galaxy']}:{$FleetData['fleet_start_system']}:{$FleetData['fleet_start_planet']}"
                    );
                    if(!empty($FleetData['ally_tag']))
                    {
                        $DefendersData[$i]['ally'] = $FleetData['ally_tag'];
                    }
                    if(!in_array($FleetData['fleet_owner'], $DefendersIDs))
                    {
                        $DefendersIDs[] = $FleetData['fleet_owner'];
                    }
                    $DefendingFleetOwners[$FleetData['fleet_id']] = $FleetData['fleet_owner'];

                    if(MORALE_ENABLED)
                    {
                        if(empty($_TempCache['MoraleCache'][$FleetData['fleet_owner']]))
                        {
                            if(!empty($_FleetCache['MoraleCache'][$FleetData['fleet_owner']]))
                            {
                                $FleetData['morale_level'] = $_FleetCache['MoraleCache'][$FleetData['fleet_owner']]['level'];
                                $FleetData['morale_droptime'] = $_FleetCache['MoraleCache'][$FleetData['fleet_owner']]['droptime'];
                                $FleetData['morale_lastupdate'] = $_FleetCache['MoraleCache'][$FleetData['fleet_owner']]['lastupdate'];
                            }
                            Morale_ReCalculate($FleetData, $FleetRow['fleet_start_time']);
                            $DefendersData[$i]['morale'] = $FleetData['morale_level'];
                            $DefendersData[$i]['moralePoints'] = $FleetData['morale_points'];

                            $_TempCache['MoraleCache'][$FleetData['fleet_owner']] = array
                            (
                                'level' => $FleetData['morale_level'],
                                'points' => $FleetData['morale_points']
                            );
                        }
                        else
                        {
                            $DefendersData[$i]['morale'] = $_TempCache['MoraleCache'][$FleetData['fleet_owner']]['level'];
                            $DefendersData[$i]['moralePoints'] = $_TempCache['MoraleCache'][$FleetData['fleet_owner']]['points'];
                        }

                        // Bonuses
                        if($DefendersData[$i]['morale'] >= MORALE_BONUS_FLEETPOWERUP1)
                        {
                            $DefendingTechs[$i]['TotalForceFactor'] = MORALE_BONUS_FLEETPOWERUP1_FACTOR;
                        }
                        if($DefendersData[$i]['morale'] >= MORALE_BONUS_FLEETSHIELDUP1)
                        {
                            $DefendingTechs[$i]['TotalShieldFactor'] = MORALE_BONUS_FLEETSHIELDUP1_FACTOR;
                        }
                        if($DefendersData[$i]['morale'] >= MORALE_BONUS_FLEETSDADDITION)
                        {
                            $DefendingTechs[$i]['SDAdd'] = MORALE_BONUS_FLEETSDADDITION_VALUE;
                        }
                        // Penalties
                        if($DefendersData[$i]['morale'] <= MORALE_PENALTY_FLEETPOWERDOWN1)
                        {
                            $DefendingTechs[$i]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN1_FACTOR;
                        }
                        if($DefendersData[$i]['morale'] <= MORALE_PENALTY_FLEETPOWERDOWN2)
                        {
                            $DefendingTechs[$i]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN2_FACTOR;
                        }
                        if($DefendersData[$i]['morale'] <= MORALE_PENALTY_FLEETSHIELDDOWN1)
                        {
                            $DefendingTechs[$i]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN1_FACTOR;
                        }
                        if($DefendersData[$i]['morale'] <= MORALE_PENALTY_FLEETSHIELDDOWN2)
                        {
                            $DefendingTechs[$i]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN2_FACTOR;
                        }
                        if($DefendersData[$i]['morale'] <= MORALE_PENALTY_FLEETSDDOWN)
                        {
                            $DefendingTechs[$i]['SDFactor'] = MORALE_PENALTY_FLEETSDDOWN_FACTOR;
                        }
                    }

                    $i += 1;
                }
            }
        }

        foreach($AttackersIDs as $ID)
        {
            if(empty($UserStatsData[$ID]))
            {
                $UserStatsData[$ID] = $UserStatsPattern;
            }
        }
        foreach($DefendersIDs as $ID)
        {
            if(empty($UserStatsData[$ID]))
            {
                $UserStatsData[$ID] = $UserStatsPattern;
            }
        }

        // Create main defender fleet array
        foreach($_Vars_ElementCategories['fleet'] as $ElementID)
        {
            if($TargetPlanet[$_Vars_GameElements[$ElementID]] > 0)
            {
                $DefendingFleets[0][$ElementID] = $TargetPlanet[$_Vars_GameElements[$ElementID]];
            }
        }
        foreach($_Vars_ElementCategories['defense'] as $ElementID)
        {
            if(in_array($ElementID, $_Vars_ElementCategories['rockets']))
            {
                continue;
            }
            if($TargetPlanet[$_Vars_GameElements[$ElementID]] > 0)
            {
                $DefendingFleets[0][$ElementID] = $TargetPlanet[$_Vars_GameElements[$ElementID]];
            }
        }

        // Create attacker fleet array
        $AttackingFleets[0] = String2Array($FleetRow['fleet_array']);

        $StartTime = microtime(true);

        // Now start Combat calculations
        $Combat = Combat($AttackingFleets, $DefendingFleets, $AttackingTechs, $DefendingTechs);

        // Get the calculations time
        $EndTime = microtime(true);
        $totaltime = sprintf('%0.6f', $EndTime - $StartTime);

        $RealDebrisMetalAtk = 0;
        $RealDebrisCrystalAtk = 0;
        $RealDebrisDeuteriumAtk = 0;
        $RealDebrisMetalDef = 0;
        $RealDebrisCrystalDef = 0;
        $RealDebrisDeuteriumDef = 0;
        $TotalMoonChance = 0;
        $TotalLostMetal = 0;
        $TotalLostCrystal = 0;
        $DebrisMetalDef = 0;
        $DebrisCrystalDef = 0;

        $MoonHasBeenCreated = false;
        $ThisDeathStarCount = 0;

        $RoundsData        = $Combat['rounds'];
        $Result            = $Combat['result'];
        $AtkShips        = $Combat['AttackerShips'];
        $DefShips        = $Combat['DefenderShips'];
        $AtkLost        = $Combat['AtkLose'];
        $DefLost        = $Combat['DefLose'];
        $DefSysLost        = $Combat['DefSysLost'];
        $ShotDown        = $Combat['ShotDown'];

        $FleetStorage = 0;

        // Parse result data - attacker fleet
        if(!empty($AtkShips[0]))
        {
            $QryUpdateFleets[0]['id'] = $FleetRow['fleet_id'];
            foreach($AtkShips[0] as $ID => $Count)
            {
                $QryUpdateFleets[0]['mess'] = '1';
                if($Count > 0)
                {
                    if($ID == 214)
                    {
                        $ThisDeathStarCount = $Count;
                    }
                    if(!isset($QryUpdateFleets[0]['count']))
                    {
                        $QryUpdateFleets[0]['count'] = 0;
                    }
                    $QryUpdateFleets[0]['array'][] = "{$ID},{$Count}";
                    $QryUpdateFleets[0]['count'] += $Count;
                }
                if($Result === COMBAT_ATK && (!isset($_Vars_Prices[$ID]['cantPillage']) || $_Vars_Prices[$ID]['cantPillage'] !== true))
                {
                    $FleetStorage += $_Vars_Prices[$ID]['capacity'] * $Count;
                }

                if($Count < $AttackingFleets[0][$ID])
                {
                    $UserDev_UpFl[$FleetRow['fleet_id']][] = $ID.','.($AttackingFleets[0][$ID] - $Count);
                }
            }

            foreach($AttackingFleets[0] as $ID => $Count)
            {
                $Difference = $Count;
                if(isset($AtkShips[0][$ID]))
                {
                    $Difference -= $AtkShips[0][$ID];
                }
                if($Difference > 0)
                {
                    $QryUpdateFleets[0]['array_lost'][] = "{$ID},{$Difference}";
                }
            }
            if(!empty($QryUpdateFleets[0]['array_lost']))
            {
                $QryUpdateFleets[0]['array_lost'] = implode(';', $QryUpdateFleets[0]['array_lost']);
            }

            if($Result === COMBAT_ATK)
            {
                $FleetStorage -= $FleetRow['fleet_resource_metal'];
                $FleetStorage -= $FleetRow['fleet_resource_crystal'];
                $FleetStorage -= $FleetRow['fleet_resource_deuterium'];

                if($FleetStorage > 0)
                {
                    $ResourceSteal_Factor = (COMBAT_RESOURCESTEAL_PERCENT / 100);
                    if(MORALE_ENABLED)
                    {
                        if(!$IsAbandoned AND $TargetUser['morale_level'] <= MORALE_PENALTY_RESOURCELOSE)
                        {
                            $ResourceSteal_NewFactor[] = MORALE_PENALTY_RESOURCELOSE_STEALPERCENT;
                        }
                        if($FleetRow['morale_level'] >= MORALE_BONUS_SOLOIDLERSTEAL AND $IdleHours >= (7 * 24))
                        {
                            $ResourceSteal_NewFactor[] = MORALE_BONUS_SOLOIDLERSTEAL_STEALPERCENT;
                        }
                        if($FleetRow['morale_level'] <= MORALE_PENALTY_STEAL)
                        {
                            $ResourceSteal_NewFactor[] = MORALE_PENALTY_STEAL_STEALPERCENT;
                        }
                        else if($FleetRow['morale_level'] <= MORALE_PENALTY_IDLERSTEAL AND $IdleHours >= (7 * 24))
                        {
                            $ResourceSteal_NewFactor[] = MORALE_PENALTY_IDLERSTEAL_STEALPERCENT;
                        }

                        if(!empty($ResourceSteal_NewFactor))
                        {
                            $ResourceSteal_Factor = (array_sum($ResourceSteal_NewFactor) / count($ResourceSteal_NewFactor)) / 100;
                        }
                    }

                    $StolenMet = 0;
                    $StolenCry = 0;
                    $StolenDeu = 0;

                    $AllowTakeMoreMet = 0;
                    $AllowTakeMoreCry = 0;
                    $AllowTakeMoreDeu = 0;

                    $MaxMetSteal = $TargetPlanet['metal'] * $ResourceSteal_Factor;
                    $MaxCrySteal = $TargetPlanet['crystal'] * $ResourceSteal_Factor;
                    $MaxDeuSteal = $TargetPlanet['deuterium'] * $ResourceSteal_Factor;

                    $StoragePerResource = $FleetStorage / 3;

                    // First - calculate, if any resource will leave free storage
                    if($MaxMetSteal < $StoragePerResource)
                    {
                        $AllowTakeMore = ($StoragePerResource - $MaxMetSteal) / 2;
                        $AllowTakeMoreCry += $AllowTakeMore;
                        $AllowTakeMoreDeu += $AllowTakeMore;
                        $GiveAwayMet = true;
                    }

                    if($MaxCrySteal < ($StoragePerResource + $AllowTakeMoreCry))
                    {
                        $AllowTakeMore = (($StoragePerResource + $AllowTakeMoreCry) - $MaxCrySteal) / 2;
                        if($GiveAwayMet == false)
                        {
                            $AllowTakeMoreMet += $AllowTakeMore;
                            $AllowTakeMoreDeu += $AllowTakeMore;
                        }
                        else
                        {
                            $AllowTakeMoreDeu += $AllowTakeMore * 2;
                        }
                        $GiveAwayCry = true;
                    }

                    if($MaxDeuSteal < ($StoragePerResource + $AllowTakeMoreDeu))
                    {
                        $AllowTakeMore = (($StoragePerResource + $AllowTakeMoreDeu) - $MaxDeuSteal) / 2;
                        if($GiveAwayCry == false)
                        {
                            $AllowTakeMoreMet += $AllowTakeMore;
                            $AllowTakeMoreCry += $AllowTakeMore;
                        }
                        else
                        {
                            $AllowTakeMoreMet += $AllowTakeMore * 2;
                        }
                    }

                    //Second - calculate stolen resources
                    if($MaxMetSteal > ($StoragePerResource + $AllowTakeMoreMet))
                    {
                        $StolenMet = $StoragePerResource + $AllowTakeMoreMet;
                    }
                    else
                    {
                        $StolenMet = $MaxMetSteal;
                    }
                    if($MaxCrySteal > ($StoragePerResource + $AllowTakeMoreCry))
                    {
                        $StolenCry = $StoragePerResource + $AllowTakeMoreCry;
                    }
                    else
                    {
                        $StolenCry = $MaxCrySteal;
                    }
                    if($MaxDeuSteal > ($StoragePerResource + $AllowTakeMoreDeu))
                    {
                        $StolenDeu = $StoragePerResource + $AllowTakeMoreDeu;
                    }
                    else
                    {
                        $StolenDeu = $MaxDeuSteal;
                    }

                    $StolenMet = floor($StolenMet);
                    $StolenCry = floor($StolenCry);
                    $StolenDeu = floor($StolenDeu);
                    if($StolenMet > 0)
                    {
                        $UserDev_UpFl[$FleetRow['fleet_id']][] = 'M,'.$StolenMet;
                        $TriggerTasksCheck['atk']['BATTLE_COLLECT_METAL'] = $StolenMet;
                    }
                    if($StolenCry > 0)
                    {
                        $UserDev_UpFl[$FleetRow['fleet_id']][] = 'C,'.$StolenCry;
                        $TriggerTasksCheck['atk']['BATTLE_COLLECT_CRYSTAL'] = $StolenCry;
                    }
                    if($StolenDeu > 0)
                    {
                        $UserDev_UpFl[$FleetRow['fleet_id']][] = 'D,'.$StolenDeu;
                        $TriggerTasksCheck['atk']['BATTLE_COLLECT_DEUTERIUM'] = $StolenDeu;
                    }

                    $QryUpdateFleets[0]['metal'] = $StolenMet;
                    $QryUpdateFleets[0]['crystal'] = $StolenCry;
                    $QryUpdateFleets[0]['deuterium'] = $StolenDeu;

                    $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_End_Res_Metal'] = $StolenMet;
                    $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_End_Res_Crystal'] = $StolenCry;
                    $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_End_Res_Deuterium'] = $StolenDeu;
                }

                //The aggressor has won the battle, let's check his chances of destroying this moon
                if($ThisDeathStarCount > 0)
                {
                    $DestructionDone = true;

                    $ThisMoon_DestructionChance = (100 - sqrt($TargetPlanet['diameter'])) * sqrt($ThisDeathStarCount);
                    if($ThisMoon_DestructionChance > 100)
                    {
                        $ThisMoon_DestructionChance = 100;
                    }
                    else if($ThisMoon_DestructionChance <= 0)
                    {
                        $ThisMoon_DestructionChance = 0;
                    }
                    else
                    {
                        $ThisMoon_DestructionChance = round($ThisMoon_DestructionChance);
                    }
                    $ThisRand_MoonDestruction = mt_rand(1, 100);

                    if($ThisRand_MoonDestruction <= $ThisMoon_DestructionChance)
                    {
                        $MoonHasBeenDestroyed = 1;
                        $Return['MoonDestroyed'] = true;
                        // Update User Stats
                        foreach($AttackersIDs as $UserID)
                        {
                            $UserStatsData[$UserID]['moons_destroyed'] += 1;
                        }

                        // Get PlanetData to Redirect Fleets (from $_FleetCache)
                        $PlanetID = $_FleetCache['moonPlanets'][$TargetPlanet['id']];
                        $PlanetName = $_FleetCache['planets'][$_FleetCache['moonPlanets'][$TargetPlanet['id']]]['name'];

                        //Now delete moon from planet and moons list, and then from galaxy
                        $_FleetCache['deleteMoons'][] = $TargetPlanet['id'];
                        $GalaxyMoonID_NeedsUpdate = true;

                        //Redirect fleets to a planet
                        $ChangeCoordinatesForFleets["{$PlanetID}<|>{$PlanetName}"] = $FleetRow['fleet_end_id'];

                        $GetIDsToChangeArchive = '';
                        $GetIDsToChangeArchive .= "SELECT `fleet_id`, `fleet_mission`, `fleet_start_id`, `fleet_end_id` FROM {{table}} ";
                        $GetIDsToChangeArchive .= "WHERE (`fleet_start_id` = {$FleetRow['fleet_end_id']} OR `fleet_end_id` = {$FleetRow['fleet_end_id']}) ";
                        $GetIDsToChangeArchive .= "AND `fleet_id` != {$FleetRow['fleet_id']}; -- MISSION DESTRUCTION [Q01][FID: {$FleetRow['fleet_id']}]";

                        $SQLResult_GetArchiveRowsToChange = doquery($GetIDsToChangeArchive, 'fleets');

                        if($SQLResult_GetArchiveRowsToChange->num_rows > 0)
                        {
                            while($ParseFleet = $SQLResult_GetArchiveRowsToChange->fetch_assoc())
                            {
                                $Pointer = &$Return['FleetArchive'][$ParseFleet['fleet_id']];
                                if($ParseFleet['fleet_start_id'] == $FleetRow['fleet_end_id'])
                                {
                                    $Pointer['Fleet_Start_Type_Changed'] = true;
                                    $Pointer['Fleet_Start_ID_Changed'] = $PlanetID;
                                }
                                if($ParseFleet['fleet_end_id'] == $FleetRow['fleet_end_id'])
                                {
                                    $Pointer['Fleet_End_Type_Changed'] = true;
                                    $Pointer['Fleet_End_ID_Changed'] = $PlanetID;
                                    if($ParseFleet['fleet_mission'] == 9)
                                    {
                                        $Pointer['Fleet_Mission_Changed'] = true;
                                    }
                                }
                            }
                        }

                        $Query_UpdateFleets = '';
                        $Query_UpdateFleets .= "UPDATE {{table}} SET ";
                        $Query_UpdateFleets .= "`fleet_start_type` = IF(`fleet_start_id` = {$FleetRow['fleet_end_id']}, '1', `fleet_start_type`), ";
                        $Query_UpdateFleets .= "`fleet_start_id` = IF(`fleet_start_id` = {$FleetRow['fleet_end_id']}, {$PlanetID}, `fleet_start_id`), ";
                        $Query_UpdateFleets .= "`fleet_end_type` = IF(`fleet_end_id` = {$FleetRow['fleet_end_id']}, '1', `fleet_end_type`), ";
                        $Query_UpdateFleets .= "`fleet_mission` = IF(`fleet_end_id` = {$FleetRow['fleet_end_id']}, IF(`fleet_mission` = 9, 1, `fleet_mission`), `fleet_mission`), ";
                        $Query_UpdateFleets .= "`fleet_end_id` = IF(`fleet_end_id` = {$FleetRow['fleet_end_id']}, {$PlanetID}, `fleet_end_id`) ";
                        $Query_UpdateFleets .= "WHERE ";
                        $Query_UpdateFleets .= "(`fleet_start_id` = {$FleetRow['fleet_end_id']} OR `fleet_end_id` = {$FleetRow['fleet_end_id']}) ";
                        $Query_UpdateFleets .= "AND `fleet_id` != {$FleetRow['fleet_id']}; ";
                        $Query_UpdateFleets .= "-- MISSION DESTRUCTION [Q02][FID: {$FleetRow['fleet_id']}]";
                        doquery($Query_UpdateFleets, 'fleets');

                        $QryChangeACS = "UPDATE {{table}} SET `end_type` = 1, `end_target_id` = {$PlanetID} WHERE `end_target_id` = {$FleetRow['fleet_end_id']}; -- MISSION DESTRUCTION [Q03][FID: {$FleetRow['fleet_id']}]";
                        doquery($QryChangeACS, 'acs');

                        //maintenant on va verifier si la vue du joueur n est pas calee sur la lune qui est detruite
                        if($TargetUser['current_planet'] == $TargetPlanet['id'] AND $TargetUser['id'] != $_User['id'])
                        {
                            $_FleetCache['moonUserUpdate'][$TargetUser['id']] = $PlanetID;
                        }
                    }

                    //Moon calculated so now check chances, that Fleet will be destroyed
                    $ThisFleet_DestructionChance = sqrt($TargetPlanet['diameter']) / 2;
                    if($ThisFleet_DestructionChance > 100)
                    {
                        $ThisFleet_DestructionChance = 100;
                    }
                    else if($ThisFleet_DestructionChance <= 0)
                    {
                        $ThisFleet_DestructionChance = 0;
                    }
                    else
                    {
                        $ThisFleet_DestructionChance = round($ThisFleet_DestructionChance);
                    }
                    $ThisRand_FleetDestruction = mt_rand(1, 100);

                    if($ThisRand_FleetDestruction <= $ThisFleet_DestructionChance)
                    {
                        $FleetHasBeenDestroyed = true;
                        $FleetDestroyedByMoon = true;

                        $DeleteFleet[] = $FleetRow['fleet_id'];
                        $UserDev_UpFl[$FleetRow['fleet_id']] = array();
                        foreach($AttackingFleets[0] as $ShipID => $ShipCount)
                        {
                            $UserDev_UpFl[$FleetRow['fleet_id']][] = "{$ShipID},{$ShipCount}";
                        }

                        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_End_Res_Metal'] = 0;
                        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_End_Res_Crystal'] = 0;
                        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_End_Res_Deuterium'] = 0;
                    }
                }
                else
                {
                    $MoonHasBeenDestroyed = -1;
                }
            }
        }
        else
        {
            $DeleteFleet[] = $FleetRow['fleet_id'];
            foreach($AttackingFleets[0] as $ShipID => $ShipCount)
            {
                $UserDev_UpFl[$FleetRow['fleet_id']][] = "{$ShipID},{$ShipCount}";
            }

            $FleetHasBeenDestroyed = true;
        }

        // Parse result data - Defenders
        $i = 1;
        if(!empty($DefendingFleets))
        {
            foreach($DefendingFleets as $User => $Ships)
            {
                if($User == 0)
                {
                    if($MoonHasBeenDestroyed !== 1)
                    {
                        $DefSysLostIDs = array_keys($DefSysLost);
                        $DefSysLostIDs[] = -1;

                        foreach($Ships as $ID => $Count)
                        {
                            if(in_array($ID, $DefSysLostIDs))
                            {
                                $Count = $DefShips[0][$ID];
                                $Chance = mt_rand(60, 80 + (($TargetUser['engineer_time'] >= $FleetRow['fleet_start_time']) ? 20 : 0));
                                $Fluctuation = mt_rand(-11, 11);
                                if($Fluctuation > 0)
                                {
                                    $Fluctuation = 0;
                                }
                                $Rebuilt[$ID] = round($DefSysLost[$ID] * (($Chance + $Fluctuation) / 100));
                                $Count += $Rebuilt[$ID];
                                if($DefendingFleets[0][$ID] < $Count)
                                {
                                    $Count = $DefendingFleets[0][$ID];
                                }
                                unset($DefSysLost[$ID]);
                            }
                            else
                            {
                                $Count = $DefShips[0][$ID];
                            }
                            if($Count == 0)
                            {
                                $Count = '0';
                            }
                            $TargetPlanet[$_Vars_GameElements[$ID]] = $Count;
                            if($Count < $DefendingFleets[0][$ID])
                            {
                                $UserDev_UpPl[] = $ID.','.($DefendingFleets[0][$ID] - $Count);
                                $_FleetCache['updatePlanets'][$TargetPlanet['id']] = true;
                                $HPQ_PlanetUpdatedFields[] = $_Vars_GameElements[$ID];
                            }
                        }
                    }
                }
                else
                {
                    $QryUpdateFleets[$i]['id'] = $DefendingFleetID[$User];
                    if(!empty($DefShips[$User]))
                    {
                        foreach($Ships as $ID => $Count)
                        {
                            $ThisCount = 0;
                            if(!empty($DefShips[$User][$ID]))
                            {
                                $OldCount = $Count;
                                $Count = $DefShips[$User][$ID];
                                $ThisCount = $Count;
                                if($Count > 0)
                                {
                                    if(!isset($QryUpdateFleets[$i]['count']))
                                    {
                                        $QryUpdateFleets[$i]['count'] = 0;
                                    }
                                    $QryUpdateFleets[$i]['array'][] = "{$ID},{$Count}";
                                    $QryUpdateFleets[$i]['count'] += $Count;
                                }
                                $Difference = $OldCount - $Count;
                                if($Difference > 0)
                                {
                                    $QryUpdateFleets[$i]['array_lost'][] = "{$ID},{$Difference}";
                                }
                            }

                            if($ThisCount < $DefendingFleets[$User][$ID])
                            {
                                $UserDev_UpFl[$DefendingFleetID[$User]][] = $ID.','.($DefendingFleets[$User][$ID] - $ThisCount);
                            }
                        }

                        if(!empty($QryUpdateFleets[$i]['array_lost']))
                        {
                            $QryUpdateFleets[$i]['array_lost'] = implode(';', $QryUpdateFleets[$i]['array_lost']);
                        }
                    }
                    else
                    {
                        $DeleteFleet[] = $DefendingFleetID[$User];
                        foreach($DefendingFleets[$User] as $ShipID => $ShipCount)
                        {
                            $UserDev_UpFl[$DefendingFleetID[$User]][] = "{$ShipID},{$ShipCount}";
                        }
                    }
                }
                $i += 1;
            }
        }

        if($MoonHasBeenDestroyed !== 1)
        {
            if($StolenMet > 0)
            {
                $TargetPlanet['metal'] -= $StolenMet;
                $UserDev_UpPl[] = 'M,'.$StolenMet;
                $_FleetCache['updatePlanets'][$TargetPlanet['id']] = true;
            }
            if($StolenCry > 0)
            {
                $TargetPlanet['crystal'] -= $StolenCry;
                $UserDev_UpPl[] = 'C,'.$StolenCry;
                $_FleetCache['updatePlanets'][$TargetPlanet['id']] = true;
            }
            if($StolenDeu > 0)
            {
                $TargetPlanet['deuterium'] -= $StolenDeu;
                $UserDev_UpPl[] = 'D,'.$StolenDeu;
                $_FleetCache['updatePlanets'][$TargetPlanet['id']] = true;
            }
        }
        else
        {
            unset($_FleetCache['planets'][$FleetRow['fleet_end_id']]);
            $_FleetCache['updatePlanets'][$TargetPlanet['id']] = false;
        }

        // Update all fleets (if necessary)
        if(!empty($QryUpdateFleets))
        {
            foreach($QryUpdateFleets as $Data)
            {
                if(!empty($Data))
                {
                    if($Data['metal'] <= 0)
                    {
                        $Data['metal'] = '0';
                    }
                    if($Data['crystal'] <= 0)
                    {
                        $Data['crystal'] = '0';
                    }
                    if($Data['deuterium'] <= 0)
                    {
                        $Data['deuterium'] = '0';
                    }

                    if(!empty($Data['array']))
                    {
                        $Data['array'] = implode(';', $Data['array']);
                        if(!empty($Data['array_lost']))
                        {
                            if(strlen($Data['array']) > strlen($Data['array_lost']))
                            {
                                $Return['FleetArchive'][$Data['id']]['Fleet_Array_Changes'] = "\"+D;{$Data['array_lost']}|\"";
                            }
                            else
                            {
                                $Return['FleetArchive'][$Data['id']]['Fleet_Array_Changes'] = "\"+L;{$Data['array']}|\"";
                            }
                            $Return['FleetArchive'][$Data['id']]['Fleet_Info_HasLostShips'] = '!true';
                        }
                        if($Data['id'] != $FleetRow['fleet_id'])
                        {
                            $_FleetCache['defFleets'][$FleetRow['fleet_end_id']][$Data['id']]['fleet_array'] = $Data['array'];
                        }
                    }

                    if($Data['id'] == $FleetRow['fleet_id'] AND $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['calcCount'] == 2)
                    {
                        // Update $_FleetCache, instead of sending additional Query to Update FleetState
                        // This fleet will be restored in this Calculation, so don't waste our time
                        $CachePointer = &$_FleetCache['fleetRowUpdate'][$Data['id']];
                        $CachePointer['fleet_array'] = $Data['array'];
                        $CachePointer['fleet_resource_metal'] = $FleetRow['fleet_resource_metal'] + $Data['metal'];
                        $CachePointer['fleet_resource_crystal'] = $FleetRow['fleet_resource_crystal'] + $Data['crystal'];
                        $CachePointer['fleet_resource_deuterium'] = $FleetRow['fleet_resource_deuterium'] + $Data['deuterium'];
                    }
                    else
                    {
                        // Create UpdateFleet record for $_FleetCache
                        $CachePointer = &$_FleetCache['updateFleets'][$Data['id']];
                        $CachePointer['fleet_array'] = $Data['array'];
                        $CachePointer['fleet_amount'] = $Data['count'];
                        $CachePointer['fleet_mess'] = $Data['mess'];
                        if(!isset($CachePointer['fleet_resource_metal']))
                        {
                            $CachePointer['fleet_resource_metal'] = 0;
                        }
                        if(!isset($CachePointer['fleet_resource_crystal']))
                        {
                            $CachePointer['fleet_resource_crystal'] = 0;
                        }
                        if(!isset($CachePointer['fleet_resource_deuterium']))
                        {
                            $CachePointer['fleet_resource_deuterium'] = 0;
                        }
                        $CachePointer['fleet_resource_metal'] += $Data['metal'];
                        $CachePointer['fleet_resource_crystal'] += $Data['crystal'];
                        $CachePointer['fleet_resource_deuterium'] += $Data['deuterium'];
                    }
                }
            }
        }

        if(!empty($UserDev_UpFl))
        {
            foreach($UserDev_UpFl as $FleetID => $DevArray)
            {
                if($FleetID == $FleetRow['fleet_id'])
                {
                    $SetCode = '2';
                    $FleetUserID = $FleetRow['fleet_owner'];
                }
                else
                {
                    $SetCode = '3';
                    $FleetUserID = $DefendingFleetOwners[$FleetID];
                }
                $UserDev_Log[] = array('UserID' => $FleetUserID, 'PlanetID' => '0', 'Date' => $FleetRow['fleet_start_time'], 'Place' => 15, 'Code' => $SetCode, 'ElementID' => $FleetID, 'AdditionalData' => implode(';', $DevArray));
            }
        }

        // Calculate Debris & Looses - Init
        $DebrisFactor_Fleet = $_GameConfig['Fleet_Cdr'] / 100;
        $DebrisFactor_Defense = $_GameConfig['Defs_Cdr'] / 100;

        // Calculate looses - attacker
        if(!empty($AtkLost))
        {
            $DebrisMetalAtk = 0;
            $DebrisCrystalAtk = 0;
            foreach($AtkLost as $ID => $Count)
            {
                if(in_array($ID, $_Vars_ElementCategories['fleet']))
                {
                    if($DebrisFactor_Fleet > 0)
                    {
                        $DebrisMetalAtk += floor($_Vars_Prices[$ID]['metal'] * $Count * $DebrisFactor_Fleet);
                        $DebrisCrystalAtk += floor($_Vars_Prices[$ID]['crystal'] * $Count * $DebrisFactor_Fleet);
                    }
                    $RealDebrisMetalAtk += floor($_Vars_Prices[$ID]['metal'] * $Count);
                    $RealDebrisCrystalAtk += floor($_Vars_Prices[$ID]['crystal'] * $Count);
                    $RealDebrisDeuteriumAtk += floor($_Vars_Prices[$ID]['deuterium'] * $Count);
                }
            }
            $TotalLostMetal = $DebrisMetalAtk;
            $TotalLostCrystal = $DebrisCrystalAtk;
        }

        // Calculate looses - defender
        if(!empty($DefLost))
        {
            foreach($DefLost as $ID => $Count)
            {
                if(in_array($ID, $_Vars_ElementCategories['fleet']))
                {
                    if($DebrisFactor_Fleet > 0)
                    {
                        $DebrisMetalDef += floor($_Vars_Prices[$ID]['metal'] * $Count * $DebrisFactor_Fleet);
                        $DebrisCrystalDef += floor($_Vars_Prices[$ID]['crystal'] * $Count * $DebrisFactor_Fleet);
                    }
                }
                elseif(in_array($ID, $_Vars_ElementCategories['defense']))
                {
                    if($DebrisFactor_Defense > 0)
                    {
                        $DebrisMetalDef += floor($_Vars_Prices[$ID]['metal'] * $Count * $DebrisFactor_Defense);
                        $DebrisCrystalDef += floor($_Vars_Prices[$ID]['crystal'] * $Count * $DebrisFactor_Defense);
                    }
                }
                $RealDebrisMetalDef += floor($_Vars_Prices[$ID]['metal'] * $Count);
                $RealDebrisCrystalDef += floor($_Vars_Prices[$ID]['crystal'] * $Count);
                $RealDebrisDeuteriumDef += floor($_Vars_Prices[$ID]['deuterium'] * $Count);
            }
            $TotalLostMetal += $DebrisMetalDef;
            $TotalLostCrystal += $DebrisCrystalDef;
        }

        // Delete fleets (if necessary)
        if(!empty($DeleteFleet))
        {
            foreach($DeleteFleet as $FleetID)
            {
                $_FleetCache['fleetRowStatus'][$FleetID]['isDestroyed'] = true;
                if(!empty($_FleetCache['updateFleets'][$FleetID]))
                {
                    unset($_FleetCache['updateFleets'][$FleetID]);
                }
                $Return['FleetsToDelete'][] = $FleetID;
                $Return['FleetArchive'][$FleetID]['Fleet_Destroyed'] = true;
                $Return['FleetArchive'][$FleetID]['Fleet_Info_HasLostShips'] = true;
                if($FleetID == $FleetRow['fleet_id'])
                {
                    if($FleetDestroyedByMoon)
                    {
                        $Return['FleetArchive'][$FleetID]['Fleet_Destroyed_Reason'] = 7;
                    }
                    else
                    {
                        if($Result === COMBAT_DEF AND ($RealDebrisMetalDef + $RealDebrisCrystalDef + $RealDebrisDeuteriumDef) <= 0)
                        {
                            if(count($RoundsData) == 2)
                            {
                                $Return['FleetArchive'][$FleetID]['Fleet_Destroyed_Reason'] = 1;
                            }
                            else
                            {
                                $Return['FleetArchive'][$FleetID]['Fleet_Destroyed_Reason'] = 11;
                            }
                        }
                        else
                        {
                            if(count($RoundsData) == 2)
                            {
                                $Return['FleetArchive'][$FleetID]['Fleet_Destroyed_Reason'] = 12;
                            }
                            else
                            {
                                $Return['FleetArchive'][$FleetID]['Fleet_Destroyed_Reason'] = 2;
                            }
                        }
                    }
                }
                else
                {
                    unset($_FleetCache['defFleets'][$FleetRow['fleet_end_id']][$FleetID]);
                    $Return['FleetArchive'][$FleetID]['Fleet_Destroyed_Reason'] = 3;
                }
            }
        }

        if($Result === COMBAT_DRAW AND (($RealDebrisMetalDef + $RealDebrisCrystalDef + $RealDebrisDeuteriumDef) <= 0))
        {
            $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Destroyed_Reason'] = 4;
        }

        // Create debris field on the orbit
        if($TotalLostMetal > 0 || $TotalLostCrystal > 0)
        {
            if($TotalLostCrystal == 0)
            {
                $TotalLostCrystal = '0';
            }
            if($TotalLostMetal == 0)
            {
                $TotalLostMetal = '0';
            }

            if(isset($_FleetCache['galaxyMap']['byMoon'][$FleetRow['fleet_end_id']]) && $_FleetCache['galaxyMap']['byMoon'][$FleetRow['fleet_end_id']] > 0)
            {
                if(!isset($_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byMoon'][$FleetRow['fleet_end_id']]]['metal']))
                {
                    $_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byMoon'][$FleetRow['fleet_end_id']]]['metal'] = 0;
                }
                if(!isset($_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byMoon'][$FleetRow['fleet_end_id']]]['crystal']))
                {
                    $_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byMoon'][$FleetRow['fleet_end_id']]]['crystal'] = 0;
                }

                $_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byMoon'][$FleetRow['fleet_end_id']]]['metal'] += $TotalLostMetal;
                $_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byMoon'][$FleetRow['fleet_end_id']]]['crystal'] += $TotalLostCrystal;
                $_FleetCache['galaxy'][$_FleetCache['galaxyMap']['byMoon'][$FleetRow['fleet_end_id']]]['updated'] = true;
                $_FleetCache['updated']['galaxy'] = true;
            }
            else
            {
                $Query_UpdateGalaxy = '';
                $Query_UpdateGalaxy .= "UPDATE {{table}} SET `metal` = `metal` + {$TotalLostMetal}, `crystal` = `crystal` + {$TotalLostCrystal} ";
                $Query_UpdateGalaxy .= "WHERE `id_moon` = {$FleetRow['fleet_end_id']} LIMIT 1; ";
                $Query_UpdateGalaxy .= "-- MISSION DESTRUCTION [Q05][FID: {$FleetRow['fleet_id']}]";
                doquery($Query_UpdateGalaxy, 'galaxy');
            }
        }

        // Check if Moon has been created
        $FleetDebris = $TotalLostCrystal + $TotalLostMetal;

        $MoonChance = floor($FleetDebris / COMBAT_MOONPERCENT_RESOURCES);
        if($MoonChance > 20)
        {
            $TotalMoonChance = $MoonChance;
            $MoonChance = 20;
        }
        if($MoonChance < 1)
        {
            $UserChance = 0;
        }
        elseif($MoonChance >= 1)
        {
            $UserChance = mt_rand(1, 100);
        }

        if($UserChance > 0 AND $UserChance <= $MoonChance AND $MoonHasBeenDestroyed === 1)
        {
            $CreatedMoonID = CreateOneMoonRecord($FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'], $TargetUserID, '', $MoonChance);
            if($CreatedMoonID !== false)
            {
                $TriggerTasksCheck['atk']['CREATE_MOON'] = true;
                $GalaxyMoonID_NeedsUpdate = false;
                $MoonHasBeenCreated = true;

                $UserDev_UpPl[] = "L,{$CreatedMoonID}";

                // Update User Stats
                foreach($AttackersIDs as $UserID)
                {
                    $UserStatsData[$UserID]['moons_created'] += 1;
                }
            }
        }

        if(isset($GalaxyMoonID_NeedsUpdate) && $GalaxyMoonID_NeedsUpdate === true)
        {
            $_FleetCache['moonGalaxyUpdate'][] = $PlanetID;
        }

        // Create DevLog Record (PlanetDefender's)
        if(!empty($UserDev_UpPl) AND !$IsAbandoned)
        {
            $UserDev_Log[] = array('UserID' => $TargetUserID, 'PlanetID' => $TargetPlanetID, 'Date' => $FleetRow['fleet_start_time'], 'Place' => 15, 'Code' => '1', 'ElementID' => ($MoonHasBeenDestroyed === 1 ? '1' : '0'), 'AdditionalData' => implode(';', $UserDev_UpPl));
        }

        // Morale System
        if(MORALE_ENABLED AND !$IsAbandoned AND !$IsAllyFight AND $IdleHours < (7 * 24))
        {
            $Morale_Factor = $FleetRow['morale_points'] / $TargetUser['morale_points'];
            if($Morale_Factor < 1)
            {
                $Morale_Factor = pow($Morale_Factor, -1);
                $Morale_AttackerStronger = false;
            }
            else
            {
                $Morale_AttackerStronger = true;
            }

            if($Morale_Factor > MORALE_MINIMALFACTOR)
            {
                if($Morale_AttackerStronger)
                {
                    $Morale_Update_Attacker_Type = MORALE_NEGATIVE;
                    if($Result === COMBAT_DEF OR $Result === COMBAT_DRAW)
                    {
                        $Morale_Update_Defender_Type = MORALE_POSITIVE;
                    }
                }
                else
                {
                    $Morale_Update_Attacker_Type = MORALE_POSITIVE;
                }

                $Morale_Updated = Morale_AddMorale($FleetRow, $Morale_Update_Attacker_Type, $Morale_Factor, 1, 1, $FleetRow['fleet_start_time']);
                if($Morale_Updated)
                {
                    $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['level'] = $FleetRow['morale_level'];
                    $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['droptime'] = $FleetRow['morale_droptime'];
                    $_FleetCache['MoraleCache'][$FleetRow['fleet_owner']]['lastupdate'] = $FleetRow['morale_lastupdate'];

                    $ReportData['morale'][$FleetRow['fleet_owner']] = array
                    (
                        'usertype' => 'atk',
                        'type' => $Morale_Update_Attacker_Type,
                        'factor' => $Morale_Factor,
                        'level' => $FleetRow['morale_level']
                    );
                }

                if($Morale_Update_Defender_Type !== null)
                {
                    if($Result === COMBAT_DRAW)
                    {
                        $Morale_LevelFactor = 1/2;
                        $Morale_TimeFactor = 1/2;
                    }
                    else
                    {
                        $Morale_LevelFactor = 1;
                        $Morale_TimeFactor = 1;
                    }

                    $Morale_Updated = Morale_AddMorale($TargetUser, $Morale_Update_Defender_Type, $Morale_Factor, $Morale_LevelFactor, $Morale_TimeFactor, $FleetRow['fleet_start_time']);
                    if($Morale_Updated)
                    {
                        $_FleetCache['MoraleCache'][$TargetUser['id']]['level'] = $TargetUser['morale_level'];
                        $_FleetCache['MoraleCache'][$TargetUser['id']]['droptime'] = $TargetUser['morale_droptime'];
                        $_FleetCache['MoraleCache'][$TargetUser['id']]['lastupdate'] = $TargetUser['morale_lastupdate'];

                        $ReportData['morale'][$TargetUser['id']] = array
                        (
                            'usertype' => 'def',
                            'type' => MORALE_POSITIVE,
                            'factor' => $Morale_Factor,
                            'level' => $TargetUser['morale_level']
                        );
                    }
                }
            }
        }

        // CREATE BATTLE REPORT
        $ReportData['init']['usr']['atk'] = $AttackersData;
        $ReportData['init']['usr']['def'] = $DefendersData;

        $ReportData['init']['time'] = $totaltime;
        $ReportData['init']['date'] = $FleetRow['fleet_start_time'];

        $ReportData['init']['result'] = $Result;
        $ReportData['init']['met'] = $StolenMet;
        $ReportData['init']['cry'] = $StolenCry;
        $ReportData['init']['deu'] = $StolenDeu;
        $ReportData['init']['deb_met'] = $TotalLostMetal;
        $ReportData['init']['deb_cry'] = $TotalLostCrystal;
        $ReportData['init']['moon_chance'] = $MoonChance;
        $ReportData['init']['total_moon_chance'] = $TotalMoonChance;
        $ReportData['init']['moon_created'] = $MoonHasBeenCreated;
        $ReportData['init']['moon_destroyed'] = $MoonHasBeenDestroyed;
        $ReportData['init']['moon_des_chance'] = $ThisMoon_DestructionChance;
        $ReportData['init']['fleet_destroyed'] = $FleetHasBeenDestroyed;
        $ReportData['init']['fleet_des_chance'] = $ThisFleet_DestructionChance;
        $ReportData['init']['planet_name'] = $TargetPlanetGetName;
        $ReportData['init']['onMoon'] = true;
        $ReportData['init']['atk_lost'] = $RealDebrisMetalAtk + $RealDebrisCrystalAtk + $RealDebrisDeuteriumAtk;
        $ReportData['init']['def_lost'] = $RealDebrisMetalDef + $RealDebrisCrystalDef + $RealDebrisDeuteriumDef;

        foreach($RoundsData as $RoundKey => $RoundData)
        {
            foreach($RoundData as $MainKey => $RoundData2)
            {
                if(!empty($RoundData2['ships']))
                {
                    foreach($RoundData2['ships'] as $UserKey => $UserData)
                    {
                        $RoundsData[$RoundKey][$MainKey]['ships'][$UserKey] = Array2String($UserData);
                    }
                }
            }
        }
        $ReportData['rounds'] = $RoundsData;

        if(count($RoundsData) <= 2 AND $Result === COMBAT_DEF)
        {
            $DisallowAttackers = true;
        }
        else
        {
            $DisallowAttackers = false;
        }

        $CreatedReport = CreateBattleReport($ReportData, array('atk' => $AttackersIDs, 'def' => $DefendersIDs), $DisallowAttackers);
        $ReportID = $CreatedReport['ID'];
        $ReportHasHLinkRelative = 'battlereport.php?hash='.$CreatedReport['Hash'];
        $ReportHasHLinkReal = GAMEURL.$ReportHasHLinkRelative;

        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_ReportID'] = $ReportID;
        if(!empty($DefendingFleetID))
        {
            foreach($DefendingFleetID as $FleetID)
            {
                $Return['FleetArchive'][$FleetID]['Fleet_DefenderReportIDs'] = "\"+,{$ReportID}\"";
            }
        }

        // Update battle stats & set Battle Report colors
        if(!$IsAllyFight)
        {
            if($Result === COMBAT_ATK)
            {
                foreach($AttackersIDs as $UserID)
                {
                    $UserStatsData[$UserID]['raids_won'] += 1;
                }
                foreach($DefendersIDs as $UserID)
                {
                    $UserStatsData[$UserID]['raids_lost'] += 1;
                }
                if($FleetDestroyedByMoon)
                {
                    $ReportColor = '#AD5CD6';
                }
                else
                {
                    $ReportColor = 'green';
                }
                if($MoonHasBeenDestroyed === 1)
                {
                    $ReportColor3 = 'green';
                }
                else
                {
                    $ReportColor3 = 'orange';
                }
                $ReportColor2 = 'red';
            }
            elseif($Result === COMBAT_DRAW)
            {
                foreach($AttackersIDs as $UserID)
                {
                    $UserStatsData[$UserID]['raids_draw'] += 1;
                }
                foreach($DefendersIDs as $UserID)
                {
                    $UserStatsData[$UserID]['raids_draw'] += 1;
                }
                $ReportColor = 'orange';
                $ReportColor2 = 'orange';
            }
            elseif($Result === COMBAT_DEF)
            {
                foreach($AttackersIDs as $UserID)
                {
                    $UserStatsData[$UserID]['raids_lost'] += 1;
                }
                foreach($DefendersIDs as $UserID)
                {
                    $UserStatsData[$UserID]['raids_won'] += 1;
                }
                $ReportColor = 'red';
                $ReportColor2 = 'green';
            }

            // Update User Destroyed & Lost Stats
            if(!empty($ShotDown))
            {
                foreach($ShotDown as $ThisType => $ThisData)
                {
                    foreach($ThisData as $ThisType2 => $ThisData2)
                    {
                        if($ThisType2 == 'd')
                        {
                            $ThisKey = 'destroyed_';
                        }
                        else
                        {
                            $ThisKey = 'lost_';
                        }
                        foreach($ThisData2 as $UserID => $DestShips)
                        {
                            if($UserID == 0)
                            {
                                if($ThisType == 'atk')
                                {
                                    $ThisUserID = $FleetRow['fleet_owner'];
                                }
                                else
                                {
                                    $ThisUserID = $TargetUser['id'];
                                }
                            }
                            else
                            {
                                if($ThisType == 'atk')
                                {
                                    $ThisUserID = $AttackingFleetOwners[$AttackingFleetID[$UserID]];
                                }
                                else
                                {
                                    $ThisUserID = $DefendingFleetOwners[$DefendingFleetID[$UserID]];
                                }
                            }
                            foreach($DestShips as $ShipID => $ShipCount)
                            {
                                $UserStatsData[$ThisUserID][$ThisKey.$ShipID] += $ShipCount;
                            }
                        }
                    }
                }
            }

            if(!empty($ShotDown))
            {
                if(!empty($ShotDown['atk']['d'][0]))
                {
                    if(!isset($TriggerTasksCheck['atk']['BATTLE_DESTROY_MILITARYUNITS']))
                    {
                        $TriggerTasksCheck['atk']['BATTLE_DESTROY_MILITARYUNITS'] = 0;
                    }
                    if(!isset($TriggerTasksCheck['atk']['BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO']))
                    {
                        $TriggerTasksCheck['atk']['BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO'] = 0;
                    }
                    foreach($ShotDown['atk']['d'][0] as $ShipID => $ShipCount)
                    {
                        $TriggerTasksCheck['atk']['BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO'] += (($_Vars_Prices[$ShipID]['metal'] + $_Vars_Prices[$ShipID]['crystal'] + $_Vars_Prices[$ShipID]['deuterium']) * $ShipCount);
                        if(in_array($ShipID, $_Vars_ElementCategories['units']['military']))
                        {
                            $TriggerTasksCheck['atk']['BATTLE_DESTROY_MILITARYUNITS'] += $ShipCount;
                        }
                    }
                }
            }

            if($Result === COMBAT_ATK)
            {
                $TriggerTasksCheck['atk']['BATTLE_WIN'] = true;
                $TriggerTasksCheck['atk']['BATTLE_WINORDRAW_SOLO_TOTALLIMIT'] = true;
                $TriggerTasksCheck['atk']['BATTLE_WINORDRAW_SOLO_LIMIT'] = true;
                $TriggerTasksCheck['atk']['BATTLE_WINORDRAW_LIMIT'] = true;
            }
            else if($Result === COMBAT_DRAW)
            {
                $TriggerTasksCheck['atk']['BATTLE_WINORDRAW_SOLO_TOTALLIMIT'] = true;
                $TriggerTasksCheck['atk']['BATTLE_WINORDRAW_SOLO_LIMIT'] = true;
                $TriggerTasksCheck['atk']['BATTLE_WINORDRAW_LIMIT'] = true;
            }
            else if($Result === COMBAT_DEF)
            {
                $TriggerTasksCheck['atk']['BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO'] = 0;
            }
            if($MoonHasBeenDestroyed === 1)
            {
                $TriggerTasksCheck['atk']['DESTROY_MOON'] = true;
            }

            if(!$IsAbandoned AND !$DestructionDone AND $AttackingFleets[0][214] >= 1000)
            {
                $TriggerTasksCheck['def']['BATTLE_BLOCK_MOONDESTROY'] = true;
            }
        }
        else
        {
            if($MoonHasBeenCreated)
            {
                $TriggerTasksCheck['atk']['CREATE_MOON_FRIENDLY'] = true;
            }
            unset($TriggerTasksCheck['atk']['BATTLE_COLLECT_METAL']);
            unset($TriggerTasksCheck['atk']['BATTLE_COLLECT_CRYSTAL']);
            unset($TriggerTasksCheck['atk']['BATTLE_COLLECT_DEUTERIUM']);

            foreach($AttackersIDs as $UserID)
            {
                $UserStatsData[$UserID]['raids_inAlly'] += 1;
            }
            foreach($DefendersIDs as $UserID)
            {
                $UserStatsData[$UserID]['raids_inAlly'] += 1;
            }
            if($Result === COMBAT_ATK)
            {
                if($FleetDestroyedByMoon)
                {
                    $ReportColor = '#AD5CD6';
                }
                else
                {
                    $ReportColor = 'green';
                }
                if($MoonHasBeenDestroyed === 1)
                {
                    $ReportColor3 = 'green';
                }
                else
                {
                    $ReportColor3 = 'orange';
                }
                $ReportColor2 = 'red';
            }
            elseif($Result === COMBAT_DRAW)
            {
                $ReportColor = 'orange';
                $ReportColor2 = 'orange';
            }
            elseif($Result === COMBAT_DEF)
            {
                $ReportColor = 'red';
                $ReportColor2 = 'green';
            }
        }

        $TargetTypeMsg = $_Lang['BR_Target_'.$FleetRow['fleet_end_type']];
        if(!empty($ReportColor3))
        {
            $TargetTypeMsg = "<span style=\"color: {$ReportColor3}\">{$TargetTypeMsg}</span>";
        }
        $Message['msg_id'] = '072';
        $Message['args'] = array
        (
            $ReportID, $ReportColor, $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'], $TargetTypeMsg,
            prettyNumber($RealDebrisMetalAtk + $RealDebrisCrystalAtk + $RealDebrisDeuteriumAtk),
            prettyNumber($RealDebrisCrystalDef + $RealDebrisMetalDef + $RealDebrisDeuteriumDef),
            ($FleetDestroyedByMoon != true ? prettyNumber($StolenMet) : 0),
            ($FleetDestroyedByMoon != true ? prettyNumber($StolenCry) : 0),
            ($FleetDestroyedByMoon != true ? prettyNumber($StolenDeu) : 0),
            prettyNumber(isset($TotalLostMetal) ? $TotalLostMetal : 0), prettyNumber(isset($TotalLostCrystal) ? $TotalLostCrystal : 0),
            $ReportHasHLinkRelative, $ReportHasHLinkReal
        );
        $Message = json_encode($Message);
        Cache_Message($CurrentUserID, 0, $FleetRow['fleet_start_time'], 3, '003', '012', $Message);

        if(!$IsAbandoned)
        {
            $Message = false;
            $Message['msg_id'] = '074';
            if(!empty($Rebuilt) AND (array)$Rebuilt === $Rebuilt)
            {
                foreach($Rebuilt as $SysID => $Count)
                {
                    $RebuildReport[] = '<b>'.$_Lang['tech'][$SysID].'</b> - '.$Count;
                }
                $RebuildReport = implode('<br/>', $RebuildReport);
            }
            else
            {
                if($MoonHasBeenDestroyed !== 1)
                {
                    if(!isset($DefSysLostIDs) || count($DefSysLostIDs) == 1)
                    {
                        $RebuildReport = $_Lang['no_loses_in_defence'];
                    }
                    else
                    {
                        $RebuildReport = $_Lang['nothing_have_been_rebuilt'];
                    }
                }
                else
                {
                    $RebuildReport = $_Lang['moon_has_been_destroyed'];
                }
            }
            $Message['args'] = array
            (
                $ReportID, $ReportColor2, $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'],
                $TargetTypeMsg, $RebuildReport, $ReportHasHLinkRelative, $ReportHasHLinkReal
            );
            $Message = json_encode($Message);
            Cache_Message($TargetUserID, 0, $FleetRow['fleet_start_time'], 3, '003', '012', $Message);
        }

        if(count($DefendersIDs) > 1)
        {
            $Message = false;
            $Message['msg_id'] = '075';
            $Message['args'] = array
            (
                $ReportID, $ReportColor2, $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'],
                $TargetTypeMsg, $ReportHasHLinkRelative, $ReportHasHLinkReal
            );
            $Message = json_encode($Message);
            unset($DefendersIDs[0]);
            Cache_Message($DefendersIDs, 0, $FleetRow['fleet_start_time'], 3, '003', '017', $Message);
        }

        if(!empty($TriggerTasksCheck))
        {
            global $GlobalParsedTasks;
        }

        if(!empty($TriggerTasksCheck['atk']))
        {
            // Parse Attacker Tasks
            if($_User['id'] == $FleetRow['fleet_owner'])
            {
                $ThisTaskUser = $_User;
            }
            else
            {
                if(empty($GlobalParsedTasks[$FleetRow['fleet_owner']]['tasks_done_parsed']))
                {
                    $GetUserTasksDone['tasks_done'] = $_FleetCache['userTasks'][$FleetRow['fleet_owner']];
                    Tasks_CheckUservar($GetUserTasksDone);
                    $GlobalParsedTasks[$FleetRow['fleet_owner']] = $GetUserTasksDone;
                }
                $ThisTaskUser = $GlobalParsedTasks[$FleetRow['fleet_owner']];
                $ThisTaskUser['id'] = $FleetRow['fleet_owner'];
            }

            if(isset($TriggerTasksCheck['atk']['BATTLE_WIN']))
            {
                Tasks_TriggerTask($ThisTaskUser, 'BATTLE_WIN', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser)
                    {
                        return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                    }
                ));
            }
            if(isset($TriggerTasksCheck['atk']['BATTLE_WINORDRAW_SOLO_TOTALLIMIT']) && $TotalMoonChance > 0)
            {
                Tasks_TriggerTask($ThisTaskUser, 'BATTLE_WINORDRAW_SOLO_TOTALLIMIT', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TotalMoonChance)
                    {
                        return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, $TotalMoonChance);
                    }
                ));
            }
            if(isset($TriggerTasksCheck['atk']['BATTLE_WINORDRAW_SOLO_LIMIT']) || isset($TriggerTasksCheck['atk']['BATTLE_WINORDRAW_LIMIT']))
            {
                $Debris_Total_Def = ($DebrisMetalDef + $DebrisCrystalDef) / COMBAT_MOONPERCENT_RESOURCES;
                if(isset($TriggerTasksCheck['atk']['BATTLE_WINORDRAW_SOLO_LIMIT']))
                {
                    Tasks_TriggerTask($ThisTaskUser, 'BATTLE_WINORDRAW_SOLO_LIMIT', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $Debris_Total_Def)
                        {
                            if($JobArray['minimalEnemyPercentLimit'] > $Debris_Total_Def)
                            {
                                return true;
                            }
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                        }
                    ));
                }
                if(isset($TriggerTasksCheck['atk']['BATTLE_WINORDRAW_LIMIT']))
                {
                    Tasks_TriggerTask($ThisTaskUser, 'BATTLE_WINORDRAW_LIMIT', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $Debris_Total_Def)
                        {
                            if($JobArray['minimalEnemyPercentLimit'] > $Debris_Total_Def)
                            {
                                return true;
                            }
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                        }
                    ));
                }
            }
            if(isset($TriggerTasksCheck['atk']['DESTROY_MOON']))
            {
                Tasks_TriggerTask($ThisTaskUser, 'DESTROY_MOON', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TargetPlanet)
                    {
                        if(isset($JobArray['minimalDiameter']) && $JobArray['minimalDiameter'] > $TargetPlanet['diameter'])
                        {
                            return true;
                        }
                        return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                    }
                ));
                if(!$FleetHasBeenDestroyed)
                {
                    Tasks_TriggerTask($ThisTaskUser, 'DESTROY_MOON_NOFLEETLOSS', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TargetPlanet)
                        {
                            if(isset($JobArray['minimalDiameter']) && $JobArray['minimalDiameter'] > $TargetPlanet['diameter'])
                            {
                                return true;
                            }
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                        }
                    ));
                }
            }
            if(isset($TriggerTasksCheck['atk']['BATTLE_COLLECT_METAL']) && $TriggerTasksCheck['atk']['BATTLE_COLLECT_METAL'] > 0 && !$FleetHasBeenDestroyed)
            {
                $TaskTemp = $TriggerTasksCheck['atk']['BATTLE_COLLECT_METAL'];
                Tasks_TriggerTask($ThisTaskUser, 'BATTLE_COLLECT_METAL', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TaskTemp)
                    {
                        return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, $TaskTemp);
                    }
                ));
            }
            if(isset($TriggerTasksCheck['atk']['BATTLE_COLLECT_CRYSTAL']) && $TriggerTasksCheck['atk']['BATTLE_COLLECT_CRYSTAL'] > 0 && !$FleetHasBeenDestroyed)
            {
                $TaskTemp = $TriggerTasksCheck['atk']['BATTLE_COLLECT_CRYSTAL'];
                Tasks_TriggerTask($ThisTaskUser, 'BATTLE_COLLECT_CRYSTAL', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TaskTemp)
                    {
                        return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, $TaskTemp);
                    }
                ));
            }
            if(isset($TriggerTasksCheck['atk']['BATTLE_COLLECT_DEUTERIUM']) && $TriggerTasksCheck['atk']['BATTLE_COLLECT_DEUTERIUM'] > 0 && !$FleetHasBeenDestroyed)
            {
                $TaskTemp = $TriggerTasksCheck['atk']['BATTLE_COLLECT_DEUTERIUM'];
                Tasks_TriggerTask($ThisTaskUser, 'BATTLE_COLLECT_DEUTERIUM', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TaskTemp)
                    {
                        return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, $TaskTemp);
                    }
                ));
            }
            if(isset($TriggerTasksCheck['atk']['CREATE_MOON']))
            {
                Tasks_TriggerTask($ThisTaskUser, 'CREATE_MOON', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser)
                    {
                        return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                    }
                ));
            }
            if(isset($TriggerTasksCheck['atk']['CREATE_MOON_FRIENDLY']))
            {
                Tasks_TriggerTask($ThisTaskUser, 'CREATE_MOON_FRIENDLY', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser)
                    {
                        return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                    }
                ));
            }
            if(isset($TriggerTasksCheck['atk']['BATTLE_DESTROY_MILITARYUNITS']) && $TriggerTasksCheck['atk']['BATTLE_DESTROY_MILITARYUNITS'] > 0)
            {
                $TaskTemp = $TriggerTasksCheck['atk']['BATTLE_DESTROY_MILITARYUNITS'];
                Tasks_TriggerTask($ThisTaskUser, 'BATTLE_DESTROY_MILITARYUNITS', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TaskTemp)
                    {
                        return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, $TaskTemp);
                    }
                ));
            }
            if(isset($TriggerTasksCheck['atk']['BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO']) && $TriggerTasksCheck['atk']['BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO'] > 0)
            {
                $TaskTemp2 = 0;
                foreach($AttackingFleets[0] as $ShipID => $ShipCount)
                {
                    $TaskTemp2 += (($_Vars_Prices[$ShipID]['metal'] + $_Vars_Prices[$ShipID]['crystal'] + $_Vars_Prices[$ShipID]['deuterium']) * $ShipCount);
                }
                $TaskTemp = $TriggerTasksCheck['atk']['BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO'];
                Tasks_TriggerTask($ThisTaskUser, 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_SOLO', array
                (
                    'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TaskTemp, $TaskTemp2)
                    {
                        if($JobArray['minimalEnemyCost'] > $TaskTemp)
                        {
                            return true;
                        }
                        if($TaskTemp2 > ($TaskTemp * $JobArray['maximalOwnValue']))
                        {
                            return true;
                        }
                        return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                    }
                ));
            }
        }

        if(!empty($TriggerTasksCheck['def']) AND !$IsAbandoned)
        {
            // Parse Defender Tasks
            if($_User['id'] == $FleetRow['fleet_target_owner'])
            {
                $ThisTaskUser = $_User;
            }
            else
            {
                if(empty($GlobalParsedTasks[$FleetRow['fleet_target_owner']]['tasks_done_parsed']))
                {
                    $GetUserTasksDone['tasks_done'] = $_FleetCache['userTasks'][$FleetRow['fleet_target_owner']];
                    Tasks_CheckUservar($GetUserTasksDone);
                    $GlobalParsedTasks[$FleetRow['fleet_target_owner']] = $GetUserTasksDone;
                }
                $ThisTaskUser = $GlobalParsedTasks[$FleetRow['fleet_target_owner']];
                $ThisTaskUser['id'] = $FleetRow['fleet_target_owner'];
            }

            if(isset($TriggerTasksCheck['def']['BATTLE_BLOCK_MOONDESTROY']))
            {
                Tasks_TriggerTask($ThisTaskUser, 'BATTLE_BLOCK_MOONDESTROY');
            }
        }
    }

    if($FleetRow['calcType'] == 3 && (!isset($_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['isDestroyed']) || $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['isDestroyed'] !== true))
    {
        if(!empty($_FleetCache['fleetRowUpdate'][$FleetRow['fleet_id']]))
        {
            foreach($_FleetCache['fleetRowUpdate'][$FleetRow['fleet_id']] as $Key => $Value)
            {
                $FleetRow[$Key] = $Value;
            }
        }
        $Return['FleetsToDelete'][] = $FleetRow['fleet_id'];
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_ComeBack'] = true;
        $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Calculated_ComeBack_Time'] = $Now;
        RestoreFleetToPlanet($FleetRow, true, $_FleetCache);
    }

    return $Return;
}

?>
