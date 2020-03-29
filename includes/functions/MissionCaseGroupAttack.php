<?php

use UniEngine\Engine\Modules\Flights;

function MissionCaseGroupAttack($FleetRow, &$_FleetCache)
{
    global    $_EnginePath, $_Vars_Prices, $_Lang, $_Vars_GameElements, $_Vars_ElementCategories, $_GameConfig, $UserStatsPattern, $UserStatsData, $UserDev_Log, $IncludeCombatEngine,
            $HPQ_PlanetUpdatedFields;

    $Return = array();
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
        $TargetPlanetGetName= $TargetPlanet['name'];
        $TargetPlanetID = $TargetPlanet['id'];

        if(!$IsAbandoned)
        {
            $IdleHours = floor(($FleetRow['fleet_start_time'] - $TargetUser['onlinetime']) / 3600);
            if($IdleHours > 0)
            {
                $UseIdleHours = true;
            }
        }

        $AttackingFleets = array();
        $DefendingFleets = array();
        // Create data arrays for attacker and main defender
        $DefendersIDs[] = $TargetUser['id'];

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

        $AttackersIDs[] = $FleetRow['fleet_owner'];
        $AttackingFleetOwners[$FleetRow['fleet_id']] = $FleetRow['fleet_owner'];
        $AttackingFleetID[0] = $FleetRow['fleet_id'];
        $AttackingFleetRes[$FleetRow['fleet_id']]['metal'] = (isset($FleetRow['fleet_resouce_metal']) ? $FleetRow['fleet_resouce_metal'] : 0);
        $AttackingFleetRes[$FleetRow['fleet_id']]['crystal'] = (isset($FleetRow['fleet_resouce_crystal']) ? $FleetRow['fleet_resouce_crystal'] : 0);
        $AttackingFleetRes[$FleetRow['fleet_id']]['deuterium'] = (isset($FleetRow['fleet_resouce_deuterium']) ? $FleetRow['fleet_resouce_deuterium'] : 0);

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
            $AttackersAllys[$FleetRow['fleet_owner']] = $FleetRow['ally_id'];
            $AttackersData[0]['ally'] = $FleetRow['ally_tag'];
        }

        // Select All Fleets from this ACS from $_FleetCache
        if(!empty($_FleetCache['acsFleets'][$FleetRow['fleet_id']]))
        {
            $i = 1;
            foreach($_FleetCache['acsFleets'][$FleetRow['fleet_id']] as $FleetData)
            {
                $AttackingFleets[$i] = String2Array($FleetData['fleet_array']);
                $AttackingFleetRes[$FleetData['fleet_id']]['metal'] = (isset($FleetData['fleet_resouce_metal']) ? $FleetData['fleet_resouce_metal'] : 0);
                $AttackingFleetRes[$FleetData['fleet_id']]['crystal'] = (isset($FleetData['fleet_resouce_crystal']) ? $FleetData['fleet_resouce_crystal'] : 0);
                $AttackingFleetRes[$FleetData['fleet_id']]['deuterium'] = (isset($FleetData['fleet_resouce_deuterium']) ? $FleetData['fleet_resouce_deuterium'] : 0);
                $AttackingFleetID[$i] = $FleetData['fleet_id'];
                $AttackingTechs[$i] = array
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
                $AttackersData[$i] = array
                (
                    'id' => $FleetData['fleet_owner'],
                    'username' => $FleetData['username'],
                    'techs' => Array2String($AttackingTechs[$i]),
                    'pos' => "{$FleetData['fleet_start_galaxy']}:{$FleetData['fleet_start_system']}:{$FleetData['fleet_start_planet']}"
                );
                if(!empty($FleetData['ally_tag']))
                {
                    $AttackersAllys[$FleetData['fleet_owner']] = $FleetData['ally_id'];
                    $AttackersData[$i]['ally'] = $FleetData['ally_tag'];
                }
                if(!in_array($FleetData['fleet_owner'], $AttackersIDs))
                {
                    $AttackersIDs[] = $FleetData['fleet_owner'];
                }
                $AttackingFleetOwners[$FleetData['fleet_id']] = $FleetData['fleet_owner'];

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
                        $AttackersData[$i]['morale'] = $FleetData['morale_level'];
                        $AttackersData[$i]['moralePoints'] = $FleetData['morale_points'];

                        $_TempCache['MoraleCache'][$FleetData['fleet_owner']] = array
                        (
                            'level' => $FleetData['morale_level'],
                            'points' => $FleetData['morale_points']
                        );

                        $AttackersMorale[$FleetData['fleet_owner']] = array
                        (
                            'morale_level' => $FleetData['morale_level'],
                            'morale_droptime' => $FleetData['morale_droptime'],
                            'morale_lastupdate' => $FleetData['morale_lastupdate'],
                            'morale_points' => $FleetData['morale_points']
                        );
                    }
                    else
                    {
                        $AttackersData[$i]['morale'] = $_TempCache['MoraleCache'][$FleetData['fleet_owner']]['level'];
                        $AttackersData[$i]['moralePoints'] = $_TempCache['MoraleCache'][$FleetData['fleet_owner']]['points'];
                    }

                    // Bonuses
                    if($AttackersData[$i]['morale'] >= MORALE_BONUS_FLEETPOWERUP1)
                    {
                        $AttackingTechs[$i]['TotalForceFactor'] = MORALE_BONUS_FLEETPOWERUP1_FACTOR;
                    }
                    if($AttackersData[$i]['morale'] >= MORALE_BONUS_FLEETSHIELDUP1)
                    {
                        $AttackingTechs[$i]['TotalShieldFactor'] = MORALE_BONUS_FLEETSHIELDUP1_FACTOR;
                    }
                    if($AttackersData[$i]['morale'] >= MORALE_BONUS_FLEETSDADDITION)
                    {
                        $AttackingTechs[$i]['SDAdd'] = MORALE_BONUS_FLEETSDADDITION_VALUE;
                    }
                    // Penalties
                    if($AttackersData[$i]['morale'] <= MORALE_PENALTY_FLEETPOWERDOWN1)
                    {
                        $AttackingTechs[$i]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN1_FACTOR;
                    }
                    if($AttackersData[$i]['morale'] <= MORALE_PENALTY_FLEETPOWERDOWN2)
                    {
                        $AttackingTechs[$i]['TotalForceFactor'] = MORALE_PENALTY_FLEETPOWERDOWN2_FACTOR;
                    }
                    if($AttackersData[$i]['morale'] <= MORALE_PENALTY_FLEETSHIELDDOWN1)
                    {
                        $AttackingTechs[$i]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN1_FACTOR;
                    }
                    if($AttackersData[$i]['morale'] <= MORALE_PENALTY_FLEETSHIELDDOWN2)
                    {
                        $AttackingTechs[$i]['TotalShieldFactor'] = MORALE_PENALTY_FLEETSHIELDDOWN2_FACTOR;
                    }
                    if($AttackersData[$i]['morale'] <= MORALE_PENALTY_FLEETSDDOWN)
                    {
                        $AttackingTechs[$i]['SDFactor'] = MORALE_PENALTY_FLEETSDDOWN_FACTOR;
                    }
                }

                $i += 1;
            }
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

            if(empty($AttackersMorale[$FleetRow['fleet_owner']]))
            {
                $AttackersMorale[$FleetRow['fleet_owner']] = array
                (
                    'morale_level' => $FleetRow['morale_level'],
                    'morale_droptime' => $FleetRow['morale_droptime'],
                    'morale_lastupdate' => $FleetRow['morale_lastupdate'],
                    'morale_points' => $FleetRow['morale_points']
                );
            }

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

        foreach($AttackingFleetID as $FleetID)
        {
            $Return['FleetArchive'][$FleetID]['Fleet_Calculated_Mission'] = true;
            $Return['FleetArchive'][$FleetID]['Fleet_Calculated_Mission_Time'] = $Now;
            if($UseIdleHours === true)
            {
                $Return['FleetArchive'][$FleetID]['Fleet_End_Owner_IdleHours'] = $IdleHours;
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

        $RoundsData            = $Combat['rounds'];
        $Result                = $Combat['result'];
        $AtkShips            = $Combat['AttackerShips'];
        $DefShips            = $Combat['DefenderShips'];
        $AtkLost            = $Combat['AtkLose'];
        $DefLost            = $Combat['DefLose'];
        $DefSysLost            = $Combat['DefSysLost'];
        $ShotDown            = $Combat['ShotDown'];
        $ForceContribution    = $Combat['ForceContribution'];

        $FleetStorage = 0;

        $i = 0;
        // Parse result data - attackers fleet
        $TotalMetStolen = 0;
        $TotalCryStolen = 0;
        $TotalDeuStolen = 0;

        $maxResourcesPillage = Flights\Utils\Missions\calculateMaxPlanetPillage([
            'planet' => $TargetPlanet,
            'maxPillagePercentage' => 0,
        ]);

        if(!empty($AtkShips))
        {
            if($Result === COMBAT_ATK)
            {
                $ResourceSteal_Factor = (COMBAT_RESOURCESTEAL_PERCENT / 100);
                if(MORALE_ENABLED)
                {
                    $ResourceSteal_NewFactor = [];
                    if(!$IsAbandoned AND $TargetUser['morale_level'] <= MORALE_PENALTY_RESOURCELOSE)
                    {
                        $ResourceSteal_NewFactor[] = MORALE_PENALTY_RESOURCELOSE_STEALPERCENT;
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

                $maxResourcesPillage = Flights\Utils\Missions\calculateMaxPlanetPillage([
                    'planet' => $TargetPlanet,
                    'maxPillagePercentage' => $ResourceSteal_Factor,
                ]);
            }

            foreach($AtkShips as $User => $Ships)
            {
                $FleetStorage = 0;
                $CalculatedAtkFleets[] = $AttackingFleetID[$User];
                if(!empty($Ships))
                {
                    $QryUpdateFleets[$i]['id'] = $AttackingFleetID[$User];
                    $QryUpdateFleets[$i]['mess'] = '1';
                    $QryUpdateFleets[$i]['mission'] = '1';

                    foreach($AttackingFleets[$User] as $ID => $Count)
                    {
                        $Difference = $Count;
                        if(isset($Ships[$ID]))
                        {
                            $Difference -= $Ships[$ID];
                        }
                        if($Difference > 0)
                        {
                            $QryUpdateFleets[$i]['array_lost'][] = "{$ID},{$Difference}";
                        }
                    }
                    if(!empty($QryUpdateFleets[$i]['array_lost']))
                    {
                        $QryUpdateFleets[$i]['array_lost'] = implode(';', $QryUpdateFleets[$i]['array_lost']);
                    }

                    foreach($Ships as $ID => $Count)
                    {
                        if($Count > 0)
                        {
                            if(!isset($QryUpdateFleets[$i]['count']))
                            {
                                $QryUpdateFleets[$i]['count'] = 0;
                            }
                            $QryUpdateFleets[$i]['array'][] = "{$ID},{$Count}";
                            $QryUpdateFleets[$i]['count'] += $Count;
                        }
                        if($Result === COMBAT_ATK && (!isset($_Vars_Prices[$ID]['cantPillage']) || $_Vars_Prices[$ID]['cantPillage'] !== true))
                        {
                            $FleetStorage += $_Vars_Prices[$ID]['capacity'] * $Count;
                        }

                        if($Count < $AttackingFleets[$User][$ID])
                        {
                            $UserDev_UpFl[$AttackingFleetID[$User]][] = $ID.','.($AttackingFleets[$User][$ID] - $Count);
                        }
                    }

                    if($Result === COMBAT_ATK)
                    {
                        $FleetStorage -= $AttackingFleetRes[$AttackingFleetID[$User]]['metal'];
                        $FleetStorage -= $AttackingFleetRes[$AttackingFleetID[$User]]['crystal'];
                        $FleetStorage -= $AttackingFleetRes[$AttackingFleetID[$User]]['deuterium'];

                        if($FleetStorage > 0)
                        {
                            $resourcesPillage = Flights\Utils\Missions\calculateEvenResourcesPillage([
                                'maxPillagePerResource' => $maxResourcesPillage,
                                'fleetTotalStorage' => $FleetStorage,
                            ]);

                            foreach ($resourcesPillage as $resourceKey => $resourcePillage) {
                                $maxResourcesPillage[$resourceKey] -= $resourcePillage;
                            }

                            $QryUpdateFleets[$i]['metal'] = $resourcesPillage['metal'];
                            $QryUpdateFleets[$i]['crystal'] = $resourcesPillage['crystal'];
                            $QryUpdateFleets[$i]['deuterium'] = $resourcesPillage['deuterium'];

                            $Return['FleetArchive'][$AttackingFleetID[$User]]['Fleet_End_Res_Metal'] = $resourcesPillage['metal'];
                            $Return['FleetArchive'][$AttackingFleetID[$User]]['Fleet_End_Res_Crystal'] = $resourcesPillage['crystal'];
                            $Return['FleetArchive'][$AttackingFleetID[$User]]['Fleet_End_Res_Deuterium'] = $resourcesPillage['deuterium'];

                            $TotalMetStolen += $resourcesPillage['metal'];
                            $TotalCryStolen += $resourcesPillage['crystal'];
                            $TotalDeuStolen += $resourcesPillage['deuterium'];

                            if($QryUpdateFleets[$i]['metal'] > 0)
                            {
                                if(!isset($TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_METAL']))
                                {
                                    $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_METAL'] = 0;
                                }
                                $UserDev_UpFl[$AttackingFleetID[$User]][] = 'M,'.$QryUpdateFleets[$i]['metal'];
                                $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_METAL'] += $QryUpdateFleets[$i]['metal'];
                            }
                            if($QryUpdateFleets[$i]['crystal'] > 0)
                            {
                                if(!isset($TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_CRYSTAL']))
                                {
                                    $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_CRYSTAL'] = 0;
                                }
                                $UserDev_UpFl[$AttackingFleetID[$User]][] = 'C,'.$QryUpdateFleets[$i]['crystal'];
                                $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_CRYSTAL'] += $QryUpdateFleets[$i]['crystal'];
                            }
                            if($QryUpdateFleets[$i]['deuterium'] > 0)
                            {
                                if(!isset($TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_DEUTERIUM']))
                                {
                                    $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_DEUTERIUM'] = 0;
                                }
                                $UserDev_UpFl[$AttackingFleetID[$User]][] = 'D,'.$QryUpdateFleets[$i]['deuterium'];
                                $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_DEUTERIUM'] += $QryUpdateFleets[$i]['deuterium'];
                            }
                        }
                    }
                }
                else
                {
                    $DeleteFleet[] = $AttackingFleetID[$User];
                    foreach($AttackingFleets[$User] as $ShipID => $ShipCount)
                    {
                        $UserDev_UpFl[$AttackingFleetID[$User]][] = "{$ShipID},{$ShipCount}";
                    }
                }
                $i += 1;
            }

            if(!empty($AttackingFleetID))
            {
                foreach($AttackingFleetID as $User => $ID)
                {
                    if(!in_array($ID, $CalculatedAtkFleets))
                    {
                        $DeleteFleet[] = $ID;
                        foreach($AttackingFleets[$User] as $ShipID => $ShipCount)
                        {
                            $UserDev_UpFl[$ID][] = "{$ShipID},{$ShipCount}";
                        }
                    }
                }
            }
        }
        else
        {
            if(!empty($AttackingFleetID))
            {
                foreach($AttackingFleetID as $User => $ID)
                {
                    $DeleteFleet[] = $ID;
                    foreach($AttackingFleets[$User] as $ShipID => $ShipCount)
                    {
                        $UserDev_UpFl[$ID][] = "{$ShipID},{$ShipCount}";
                    }
                }
            }
        }

        // Parse result data - Defenders
        if(!empty($DefendingFleets))
        {
            foreach($DefendingFleets as $User => $Ships)
            {
                if($User == 0)
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

        if($TotalMetStolen > 0)
        {
            $TargetPlanet['metal'] -= $TotalMetStolen;
            $UserDev_UpPl[] = 'M,'.$TotalMetStolen;
            $_FleetCache['updatePlanets'][$TargetPlanet['id']] = true;
        }
        if($TotalCryStolen > 0)
        {
            $TargetPlanet['crystal'] -= $TotalCryStolen;
            $UserDev_UpPl[] = 'C,'.$TotalCryStolen;
            $_FleetCache['updatePlanets'][$TargetPlanet['id']] = true;
        }
        if($TotalDeuStolen > 0)
        {
            $TargetPlanet['deuterium'] -= $TotalDeuStolen;
            $UserDev_UpPl[] = 'D,'.$TotalDeuStolen;
            $_FleetCache['updatePlanets'][$TargetPlanet['id']] = true;
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
                        if($Data['id'] != $FleetRow['fleet_id'] AND !empty($DefendingFleetID) AND in_array($Data['id'], $DefendingFleetID))
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
                        if($Data['id'] != $FleetRow['fleet_id'] AND (empty($DefendingFleetID) OR !in_array($Data['id'], $DefendingFleetID)))
                        {
                            $CachePointer = &$_FleetCache['fleetRowUpdate'][$Data['id']];
                            $CachePointer['fleet_array'] = $Data['array'];
                            $CachePointer['fleet_resource_metal'] = (isset($_FleetCache['acsFleets'][$Data['id']]['fleet_resource_metal']) ? $_FleetCache['acsFleets'][$Data['id']]['fleet_resource_metal'] : 0) + $Data['metal'];
                            $CachePointer['fleet_resource_crystal'] = (isset($_FleetCache['acsFleets'][$Data['id']]['fleet_resource_crystal']) ? $_FleetCache['acsFleets'][$Data['id']]['fleet_resource_crystal'] : 0) + $Data['crystal'];
                            $CachePointer['fleet_resource_deuterium'] = (isset($_FleetCache['acsFleets'][$Data['id']]['fleet_resource_deuterium']) ? $_FleetCache['acsFleets'][$Data['id']]['fleet_resource_deuterium'] : 0) + $Data['deuterium'];
                        }
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
                    if(!empty($DefendingFleetID) AND in_array($FleetID, $DefendingFleetID))
                    {
                        $SetCode = '3';
                        $FleetUserID = $DefendingFleetOwners[$FleetID];
                    }
                    else
                    {
                        $SetCode = '4';
                        $FleetUserID = $AttackingFleetOwners[$FleetID];
                    }
                }
                $UserDev_Log[] = array('UserID' => $FleetUserID, 'PlanetID' => '0', 'Date' => $FleetRow['fleet_start_time'], 'Place' => 13, 'Code' => $SetCode, 'ElementID' => $FleetID, 'AdditionalData' => implode(';', $DevArray));
            }
        }

        // Calculate Debris & Looses
        $debrisRecoveryPercentages = [
            'ships' => ($_GameConfig['Fleet_Cdr'] / 100),
            'defenses' => ($_GameConfig['Defs_Cdr'] / 100),
        ];

        $attackersResourceLosses = Flights\Utils\Calculations\calculateResourcesLoss([
            'unitsLost' => $AtkLost,
            'debrisRecoveryPercentages' => $debrisRecoveryPercentages,
        ]);

        $RealDebrisMetalAtk += $attackersResourceLosses['realLoss']['metal'];
        $RealDebrisCrystalAtk += $attackersResourceLosses['realLoss']['crystal'];
        $RealDebrisDeuteriumAtk += $attackersResourceLosses['realLoss']['deuterium'];
        $TotalLostMetal += $attackersResourceLosses['recoverableLoss']['metal'];
        $TotalLostCrystal += $attackersResourceLosses['recoverableLoss']['crystal'];

        $defendersResourceLosses = Flights\Utils\Calculations\calculateResourcesLoss([
            'unitsLost' => $DefLost,
            'debrisRecoveryPercentages' => $debrisRecoveryPercentages,
        ]);

        $RealDebrisMetalDef += $defendersResourceLosses['realLoss']['metal'];
        $RealDebrisCrystalDef += $defendersResourceLosses['realLoss']['crystal'];
        $RealDebrisDeuteriumDef += $defendersResourceLosses['realLoss']['deuterium'];
        $TotalLostMetal += $defendersResourceLosses['recoverableLoss']['metal'];
        $TotalLostCrystal += $defendersResourceLosses['recoverableLoss']['crystal'];
        $DebrisMetalDef += $defendersResourceLosses['recoverableLoss']['metal'];
        $DebrisCrystalDef += $defendersResourceLosses['recoverableLoss']['crystal'];

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
                            $Return['FleetArchive'][$FleetID]['Fleet_Destroyed_Reason'] = 5;
                        }
                    }
                }
                elseif(in_array($FleetID, $AttackingFleetID))
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
                            $Return['FleetArchive'][$FleetID]['Fleet_Destroyed_Reason'] = 6;
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

        if($Result === COMBAT_DRAW AND ($RealDebrisMetalDef + $RealDebrisCrystalDef + $RealDebrisDeuteriumDef) <= 0)
        {
            foreach($AttackingFleetID as $ThisFleetID)
            {
                if(empty($DeleteFleet) OR !in_array($ThisFleetID, $DeleteFleet))
                {
                    $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Destroyed_Reason'] = 4;
                }
                else
                {
                    $Return['FleetArchive'][$FleetRow['fleet_id']]['Fleet_Destroyed_Reason'] = 1;
                }
            }
        }

        // Create debris field on the orbit
        if ($TotalLostMetal > 0 || $TotalLostCrystal > 0) {
            Flights\Utils\FleetCache\updateGalaxyDebris([
                'debris' => [
                    'metal' => $TotalLostMetal,
                    'crystal' => $TotalLostCrystal,
                ],
                'targetPlanet' => $TargetPlanet,
                'fleetCache' => &$_FleetCache,
            ]);
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

        if(($UserChance > 0) AND ($UserChance <= $MoonChance))
        {
            if($TargetPlanet['planet_type'] == 1)
            {
                $CreatedMoonID = CreateOneMoonRecord($FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'], $TargetUserID, '', $MoonChance);
                if($CreatedMoonID !== false)
                {
                    foreach($AttackersIDs as $ThisID)
                    {
                        $TriggerTasksCheck[$ThisID]['CREATE_MOON'] = true;
                    }
                    $MoonHasBeenCreated = true;

                    $UserDev_UpPl[] = "L,{$CreatedMoonID}";

                    // Update User Stats
                    foreach($AttackersIDs as $UserID)
                    {
                        $UserStatsData[$UserID]['moons_created'] += 1;
                    }
                }
                else
                {
                    $MoonHasBeenCreated = false;
                }
            }
            else
            {
                $MoonHasBeenCreated = false;
            }
        }
        elseif($UserChance = 0 or $UserChance > $MoonChance)
        {
            $MoonHasBeenCreated = false;
        }

        // Create DevLog Record (PlanetDefender's)
        if(!empty($UserDev_UpPl) AND !$IsAbandoned)
        {
            $UserDev_Log[] = array('UserID' => $TargetUserID, 'PlanetID' => $TargetPlanetID, 'Date' => $FleetRow['fleet_start_time'], 'Place' => 13, 'Code' => '1', 'ElementID' => '0', 'AdditionalData' => implode(';', $UserDev_UpPl));
        }

        // Morale System
        if(MORALE_ENABLED AND !$IsAbandoned AND !$IsAllyFight AND $IdleHours < (7 * 24))
        {
            foreach($AttackersMorale as $ThisUserID => $ThisData)
            {
                $Morale_TotalFactor += $ThisData['morale_points'];
            }
            $Morale_TotalFactor /= $TargetUser['morale_points'];

            if($Morale_TotalFactor > MORALE_MINIMALFACTOR)
            {
                $Morale_UsedTotalFactor = true;
                $Morale_DefenderTotalFactor = $Morale_TotalFactor;
                foreach($AttackersMorale as $ThisUserID => &$ThisData)
                {
                    $ThisData['factor'] = $Morale_TotalFactor;
                }
            }
            else
            {
                foreach($AttackersMorale as $ThisUserID => &$ThisData)
                {
                    $ThisData['factor'] = $ThisData['morale_points'] / $TargetUser['morale_points'];
                }
            }

            foreach($AttackersMorale as $ThisUserID => &$ThisData)
            {
                $Morale_Factor = $ThisData['factor'];
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
                        if(($Result === COMBAT_DEF OR $Result === COMBAT_DRAW) AND !$Morale_UsedTotalFactor)
                        {
                            $Morale_DefenderTotalFactor += $Morale_Factor;
                        }
                    }
                    else
                    {
                        $Morale_Update_Attacker_Type = MORALE_POSITIVE;
                    }

                    $Morale_Updated = Morale_AddMorale($ThisData, $Morale_Update_Attacker_Type, $Morale_Factor, 1, 1, $FleetRow['fleet_start_time']);
                    if($Morale_Updated)
                    {
                        $_FleetCache['MoraleCache'][$ThisUserID]['level'] = $ThisData['morale_level'];
                        $_FleetCache['MoraleCache'][$ThisUserID]['droptime'] = $ThisData['morale_droptime'];
                        $_FleetCache['MoraleCache'][$ThisUserID]['lastupdate'] = $ThisData['morale_lastupdate'];

                        $ReportData['morale'][$ThisUserID] = array
                        (
                            'usertype' => 'atk',
                            'type' => $Morale_Update_Attacker_Type,
                            'factor' => $Morale_Factor,
                            'level' => $ThisData['morale_level']
                        );
                    }
                }
            }

            if($Morale_DefenderTotalFactor > 0)
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

                $Morale_Updated = Morale_AddMorale($TargetUser, MORALE_POSITIVE, $Morale_DefenderTotalFactor, $Morale_LevelFactor, $Morale_TimeFactor, $FleetRow['fleet_start_time']);
                if($Morale_Updated)
                {
                    $_FleetCache['MoraleCache'][$TargetUser['id']]['level'] = $TargetUser['morale_level'];
                    $_FleetCache['MoraleCache'][$TargetUser['id']]['droptime'] = $TargetUser['morale_droptime'];
                    $_FleetCache['MoraleCache'][$TargetUser['id']]['lastupdate'] = $TargetUser['morale_lastupdate'];

                    $ReportData['morale'][$TargetUser['id']] = array
                    (
                        'usertype' => 'def',
                        'type' => MORALE_POSITIVE,
                        'factor' => $Morale_DefenderTotalFactor,
                        'level' => $TargetUser['morale_level']
                    );
                }
            }
        }

        // CREATE BATTLE REPORT
        $ReportData['init']['usr']['atk'] = $AttackersData;
        $ReportData['init']['usr']['def'] = $DefendersData;

        $ReportData['init']['time'] = $totaltime;
        $ReportData['init']['date'] = $FleetRow['fleet_start_time'];

        $ReportData['init']['result'] = $Result;
        $ReportData['init']['met'] = $TotalMetStolen;
        $ReportData['init']['cry'] = $TotalCryStolen;
        $ReportData['init']['deu'] = $TotalDeuStolen;
        $ReportData['init']['deb_met'] = $TotalLostMetal;
        $ReportData['init']['deb_cry'] = $TotalLostCrystal;
        $ReportData['init']['moon_chance'] = $MoonChance;
        $ReportData['init']['total_moon_chance'] = $TotalMoonChance;
        $ReportData['init']['moon_created'] = $MoonHasBeenCreated;
        $ReportData['init']['moon_destroyed'] = false;
        $ReportData['init']['moon_des_chance'] = 0;
        $ReportData['init']['fleet_destroyed'] = false;
        $ReportData['init']['fleet_des_chance'] = 0;
        $ReportData['init']['planet_name'] = $TargetPlanetGetName;
        $ReportData['init']['onMoon'] = ($FleetRow['fleet_end_type'] == 3 ? true : false);
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

        foreach($AttackingFleetID as $FleetID)
        {
            $Return['FleetArchive'][$FleetID]['Fleet_ReportID'] = $ReportID;
        }
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
            $InACSBattle[$FleetRow['fleet_owner']] = $FleetRow['fleet_owner'];
            if($Result === COMBAT_ATK OR $Result === COMBAT_DRAW)
            {
                if(!empty($ForceContribution['atk']))
                {
                    $AttackersCount = count($AttackersIDs);
                    $ForceUsed_Total = array_sum($ForceContribution['atk']);
                    $ForceUsed_Array = array();
                    foreach($ForceContribution['atk'] as $UserID => $UsedForce)
                    {
                        if($UserID == 0)
                        {
                            $UserID = $FleetRow['fleet_owner'];
                        }
                        else
                        {
                            $UserID = $AttackingFleetOwners[$AttackingFleetID[$UserID]];
                        }
                        if(!isset($ForceUsed_Array[$UserID]))
                        {
                            $ForceUsed_Array[$UserID] = 0;
                        }
                        $ForceUsed_Array[$UserID] += $UsedForce;
                    }
                    $AttackersCount -= ($AttackersCount - count($ForceUsed_Array));
                }
                else
                {
                    $AttackersCount = 1;
                }
            }

            if($Result === COMBAT_ATK)
            {
                foreach($AttackersIDs as $UserID)
                {
                    $UserStatsData[$UserID]['raids_won'] += 1;
                    if($AttackersCount > 1)
                    {
                        if(($ForceUsed_Array[$UserID] / $ForceUsed_Total) >= ACS_MINIMALFORCECONTRIBUTION)
                        {
                            $InACSBattle[$UserID] = $UserID;
                            $UserStatsData[$UserID]['raids_acs_won'] += 1;
                        }
                    }
                }
                foreach($DefendersIDs as $UserID)
                {
                    $UserStatsData[$UserID]['raids_lost'] += 1;
                }
                $ReportColor = 'green';
                $ReportColor2 = 'red';
            }
            else if($Result === COMBAT_DRAW)
            {
                foreach($AttackersIDs as $UserID)
                {
                    $UserStatsData[$UserID]['raids_draw'] += 1;
                    if($AttackersCount > 1)
                    {
                        if(($ForceUsed_Array[$UserID] / $ForceUsed_Total) >= ACS_MINIMALFORCECONTRIBUTION)
                        {
                            $InACSBattle[$UserID] = $UserID;
                        }
                    }
                }
                foreach($DefendersIDs as $UserID)
                {
                    $UserStatsData[$UserID]['raids_draw'] += 1;
                }
                $ReportColor = 'orange';
                $ReportColor2 = 'orange';
            }
            else if($Result === COMBAT_DEF)
            {
                foreach($AttackersIDs as $UserID){
                    $UserStatsData[$UserID]['raids_lost'] += 1;
                }
                foreach($DefendersIDs as $UserID){
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

            $DestroyedDefendersShips_TotalPrice = 0;
            if(!empty($ShotDown['atk']['d']))
            {
                foreach($ShotDown['atk']['d'] as $UserID => $DestShips)
                {
                    if($UserID == 0)
                    {
                        $ThisUserID = $FleetRow['fleet_owner'];
                    }
                    else
                    {
                        $ThisUserID = $AttackingFleetOwners[$AttackingFleetID[$UserID]];
                    }
                    if(!isset($TriggerTasksCheck[$ThisUserID]['BATTLE_DESTROY_MILITARYUNITS']))
                    {
                        $TriggerTasksCheck[$ThisUserID]['BATTLE_DESTROY_MILITARYUNITS'] = 0;
                    }
                    foreach($DestShips as $ShipID => $ShipCount)
                    {
                        $DestroyedDefendersShips_TotalPrice += (($_Vars_Prices[$ShipID]['metal'] + $_Vars_Prices[$ShipID]['crystal'] + $_Vars_Prices[$ShipID]['deuterium']) * $ShipCount);
                        if(in_array($ShipID, $_Vars_ElementCategories['units']['military']))
                        {
                            $TriggerTasksCheck[$ThisUserID]['BATTLE_DESTROY_MILITARYUNITS'] += $ShipCount;
                        }
                    }
                }
            }

            $IsACSBattle = (count($InACSBattle) > 1 ? true : false);
            if(!$IsACSBattle)
            {
                $DestroyedDefendersShips_TotalPrice = 0;
            }

            if($Result === COMBAT_ATK OR $Result === COMBAT_DRAW)
            {
                if($Result === COMBAT_ATK)
                {
                    foreach($InACSBattle as $ThisUserID)
                    {
                        $TriggerTasksCheck[$ThisUserID]['BATTLE_WIN'] = true;
                    }
                }
                foreach($InACSBattle as $ThisUserID)
                {
                    $TriggerTasksCheck[$ThisUserID]['BATTLE_WINORDRAW_LIMIT'] = true;
                    $TriggerTasksCheck[$ThisUserID]['BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS'] = $DestroyedDefendersShips_TotalPrice;
                }
                if($IsACSBattle)
                {
                    foreach($InACSBattle as $ThisUserID)
                    {
                        $TriggerTasksCheck[$ThisUserID]['BATTLE_WINORDRAW_ACS_LIMIT'] = true;
                    }
                }
            }
        }
        else
        {
            if(!empty($TriggerTasksCheck))
            {
                foreach($TriggerTasksCheck as $ThisIndex => $ThisArray)
                {
                    unset($TriggerTasksCheck[$ThisIndex]['BATTLE_COLLECT_METAL']);
                    unset($TriggerTasksCheck[$ThisIndex]['BATTLE_COLLECT_CRYSTAL']);
                    unset($TriggerTasksCheck[$ThisIndex]['BATTLE_COLLECT_DEUTERIUM']);
                }
            }

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
                $ReportColor = 'green';
                $ReportColor2 = 'red';
            }
            else if($Result === COMBAT_DRAW)
            {
                $ReportColor = 'orange';
                $ReportColor2 = 'orange';
            }
            else if($Result === COMBAT_DEF)
            {
                $ReportColor = 'red';
                $ReportColor2 = 'green';
            }
        }

        if($MoonHasBeenCreated AND $TargetUser['ally_id'] > 0)
        {
            foreach($AttackersIDs as $ThisID)
            {
                if($AttackersAllys[$ThisID] == $TargetUser['ally_id'])
                {
                    $TriggerTasksCheck[$ThisID]['CREATE_MOON_FRIENDLY'] = true;
                }
            }
        }

        $TargetTypeMsg = $_Lang['BR_Target_'.$FleetRow['fleet_end_type']];

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
                if(!isset($DefSysLostIDs) || count($DefSysLostIDs) == 1)
                {
                    $RebuildReport = $_Lang['no_loses_in_defence'];
                }
                else
                {
                    $RebuildReport = $_Lang['nothing_have_been_rebuilt'];
                }
            }
            $Message['args'] = array($ReportID, $ReportColor2, $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'], $TargetTypeMsg, $RebuildReport, $ReportHasHLinkRelative, $ReportHasHLinkReal);
            $Message = json_encode($Message);
            Cache_Message($TargetUserID, 0, $FleetRow['fleet_start_time'], 3, '003', '012', $Message);
        }

        if(count($DefendersIDs) > 1)
        {
            $Message = false;
            $Message['msg_id'] = '075';
            $Message['args'] = array($ReportID, $ReportColor2, $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'], $TargetTypeMsg, $ReportHasHLinkRelative, $ReportHasHLinkReal);
            $Message = json_encode($Message);
            unset($DefendersIDs[0]);
            Cache_Message($DefendersIDs, 0, $FleetRow['fleet_start_time'], 3, '003', '017', $Message);
        }

        $Message = false;
        $Message['msg_id'] = '071';
        $Message['args'] = array
        (
            $ReportID, $ReportColor, $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'], $TargetTypeMsg,
            prettyNumber($RealDebrisMetalAtk + $RealDebrisCrystalAtk + $RealDebrisDeuteriumAtk),
            prettyNumber($RealDebrisCrystalDef + $RealDebrisMetalDef + $RealDebrisDeuteriumDef),
            prettyNumber($TotalMetStolen), prettyNumber($TotalCryStolen), prettyNumber($TotalDeuStolen),
            prettyNumber($TotalLostMetal), prettyNumber($TotalLostCrystal),
            $ReportHasHLinkRelative, $ReportHasHLinkReal
        );
        $Message = json_encode($Message);
        Cache_Message($AttackersIDs, 0, $FleetRow['fleet_start_time'], 3, '003', '017', $Message);

        $Return['DeleteACS'] = $FleetRow['acs_id'];

        if(!empty($TriggerTasksCheck))
        {
            global $GlobalParsedTasks, $_User;

            $Debris_Total_Def = ($DebrisMetalDef + $DebrisCrystalDef) / COMBAT_MOONPERCENT_RESOURCES;

            foreach($TriggerTasksCheck as $ThisTaskUserID => $TriggerTasksData)
            {
                if($_User['id'] == $ThisTaskUserID)
                {
                    $ThisTaskUser = $_User;
                }
                else
                {
                    if(empty($GlobalParsedTasks[$ThisTaskUserID]['tasks_done_parsed']))
                    {
                        $GetUserTasksDone = array();
                        $GetUserTasksDone['tasks_done'] = $_FleetCache['userTasks'][$ThisTaskUserID];
                        Tasks_CheckUservar($GetUserTasksDone);
                        $GlobalParsedTasks[$ThisTaskUserID] = $GetUserTasksDone;
                    }
                    $ThisTaskUser = $GlobalParsedTasks[$ThisTaskUserID];
                    $ThisTaskUser['id'] = $ThisTaskUserID;
                }
                if($FleetRow['fleet_owner'] == $ThisTaskUser['id'])
                {
                    $ThisTaskUser['isACSLeader'] = true;
                }

                if(isset($TriggerTasksData['BATTLE_WIN']))
                {
                    Tasks_TriggerTask($ThisTaskUser, 'BATTLE_WIN', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser)
                        {
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                        }
                    ));
                }
                if(isset($TriggerTasksData['BATTLE_WINORDRAW_LIMIT']))
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
                if(isset($TriggerTasksData['BATTLE_WINORDRAW_ACS_LIMIT']))
                {
                    Tasks_TriggerTask($ThisTaskUser, 'BATTLE_WINORDRAW_ACS_LIMIT', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $Debris_Total_Def)
                        {
                            if(isset($JobArray['hasToBeLeader']) && $JobArray['hasToBeLeader'] === true && $ThisTaskUser['isACSLeader'] !== true)
                            {
                                return true;
                            }
                            if($JobArray['minimalEnemyPercentLimit'] > $Debris_Total_Def)
                            {
                                return true;
                            }
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                        }
                    ));
                }
                if(isset($TriggerTasksData['BATTLE_COLLECT_METAL']) && $TriggerTasksData['BATTLE_COLLECT_METAL'] > 0)
                {
                    $TaskTemp = $TriggerTasksData['BATTLE_COLLECT_METAL'];
                    Tasks_TriggerTask($ThisTaskUser, 'BATTLE_COLLECT_METAL', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TaskTemp)
                        {
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, $TaskTemp);
                        }
                    ));
                }
                if(isset($TriggerTasksData['BATTLE_COLLECT_CRYSTAL']) && $TriggerTasksData['BATTLE_COLLECT_CRYSTAL'] > 0)
                {
                    $TaskTemp = $TriggerTasksData['BATTLE_COLLECT_CRYSTAL'];
                    Tasks_TriggerTask($ThisTaskUser, 'BATTLE_COLLECT_CRYSTAL', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TaskTemp)
                        {
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, $TaskTemp);
                        }
                    ));
                }
                if(isset($TriggerTasksData['BATTLE_COLLECT_DEUTERIUM']) && $TriggerTasksData['BATTLE_COLLECT_DEUTERIUM'] > 0)
                {
                    $TaskTemp = $TriggerTasksData['BATTLE_COLLECT_DEUTERIUM'];
                    Tasks_TriggerTask($ThisTaskUser, 'BATTLE_COLLECT_DEUTERIUM', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TaskTemp)
                        {
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, $TaskTemp);
                        }
                    ));
                }
                if(isset($TriggerTasksData['CREATE_MOON']) && $TriggerTasksData['CREATE_MOON'])
                {
                    Tasks_TriggerTask($ThisTaskUser, 'CREATE_MOON', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser)
                        {
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                        }
                    ));
                }
                if(isset($TriggerTasksData['CREATE_MOON_FRIENDLY']) && $TriggerTasksData['CREATE_MOON_FRIENDLY'])
                {
                    Tasks_TriggerTask($ThisTaskUser, 'CREATE_MOON_FRIENDLY', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser)
                        {
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, 1);
                        }
                    ));
                }
                if(isset($TriggerTasksData['BATTLE_DESTROY_MILITARYUNITS']) && $TriggerTasksData['BATTLE_DESTROY_MILITARYUNITS'] > 0)
                {
                    $TaskTemp = $TriggerTasksData['BATTLE_DESTROY_MILITARYUNITS'];
                    Tasks_TriggerTask($ThisTaskUser, 'BATTLE_DESTROY_MILITARYUNITS', array
                    (
                        'mainCheck' => function($JobArray, $ThisCat, $TaskID, $JobID) use ($ThisTaskUser, $TaskTemp)
                        {
                            return Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $ThisTaskUser, $TaskTemp);
                        }
                    ));
                }
                if(isset($TriggerTasksData['BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS']) && $TriggerTasksData['BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS'] > 0)
                {
                    $TaskTemp2 = 0;
                    foreach($AttackingFleets as $FleetIndex => $FleetArray)
                    {
                        if($AttackingFleetOwners[$AttackingFleetID[$FleetIndex]] != $ThisTaskUser['id'])
                        {
                            continue;
                        }
                        foreach($FleetArray as $ShipID => $ShipCount)
                        {
                            $TaskTemp2 += (($_Vars_Prices[$ShipID]['metal'] + $_Vars_Prices[$ShipID]['crystal'] + $_Vars_Prices[$ShipID]['deuterium']) * $ShipCount);
                        }
                    }
                    $TaskTemp = $TriggerTasksData['BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS'];
                    Tasks_TriggerTask($ThisTaskUser, 'BATTLE_DESTROY_MOREEXPENSIVEFLEET_ACS', array
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
