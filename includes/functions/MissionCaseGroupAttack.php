<?php

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Modules\Flights;

function MissionCaseGroupAttack($FleetRow, &$_FleetCache)
{
    global $_EnginePath, $_Vars_Prices, $_Vars_GameElements, $_Vars_ElementCategories,
        $_GameConfig, $UserStatsData, $UserDev_Log, $IncludeCombatEngine, $HPQ_PlanetUpdatedFields;

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

        $DefendingTechs[0] = Flights\Utils\Initializers\initCombatTechnologiesMap([
            'user' => $TargetUser,
        ]);

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
        if (!empty($_FleetCache['defFleets'][$FleetRow['fleet_end_id']])) {
            $_TempCache = [
                'MoraleCache' => [],
            ];

            $i = 1;

            foreach ($_FleetCache['defFleets'][$FleetRow['fleet_end_id']] as $fleetData) {
                if ($_FleetCache['fleetRowStatus'][$fleetData['fleet_id']]['isDestroyed']) {
                    continue;
                }

                $defenderDetails = Flights\Utils\Initializers\initDefenderDetails([
                    'combatTimestamp' => $FleetRow['fleet_start_time'],
                    'fleetData' => $fleetData,
                    'fleetCache' => &$_FleetCache,
                    'localCache' => &$_TempCache,
                ]);
                $defenderUserID = $defenderDetails['userData']['id'];

                $DefendingFleets[$i] = $defenderDetails['ships'];
                $DefendingFleetID[$i] = $defenderDetails['fleetID'];
                $DefendingTechs[$i] = $defenderDetails['combatTechnologies'];
                $DefendersData[$i] = $defenderDetails['userData'];

                $DefendingFleetOwners[$defenderDetails['fleetID']] = $defenderUserID;

                if (!in_array($defenderUserID, $DefendersIDs)) {
                    $DefendersIDs[] = $defenderUserID;
                }

                $i += 1;
            }
        }

        $attackingFleetRowsById = [];

        $AttackersIDs[] = $FleetRow['fleet_owner'];
        $AttackingFleetOwners[$FleetRow['fleet_id']] = $FleetRow['fleet_owner'];
        $AttackingFleetID[0] = $FleetRow['fleet_id'];

        $attackingFleetRowsById[$FleetRow['fleet_id']] = $FleetRow;

        $AttackingTechs[0] = Flights\Utils\Initializers\initCombatTechnologiesMap([
            'user' => $FleetRow,
        ]);

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
                $attackingFleetRowsById[$FleetData['fleet_id']] = $FleetData;

                $AttackingFleetID[$i] = $FleetData['fleet_id'];
                $AttackingTechs[$i] = Flights\Utils\Initializers\initCombatTechnologiesMap([
                    'user' => $FleetData,
                ]);
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

                    $moraleCombatModifiers = Flights\Utils\Modifiers\calculateMoraleCombatModifiers([
                        'moraleLevel' => $AttackersData[$i]['morale'],
                    ]);

                    $AttackingTechs[$i] = array_merge(
                        $AttackingTechs[$i],
                        $moraleCombatModifiers
                    );
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

            $moraleCombatModifiers = Flights\Utils\Modifiers\calculateMoraleCombatModifiers([
                'moraleLevel' => $FleetRow['morale_level'],
            ]);

            $AttackingTechs[0] = array_merge(
                $AttackingTechs[0],
                $moraleCombatModifiers
            );

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

                $moraleCombatModifiers = Flights\Utils\Modifiers\calculateMoraleCombatModifiers([
                    'moraleLevel' => $TargetUser['morale_level'],
                ]);

                $DefendingTechs[0] = array_merge(
                    $DefendingTechs[0],
                    $moraleCombatModifiers
                );
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

        foreach ($AttackersIDs as $userID) {
            if (empty($UserStatsData[$userID])) {
                $UserStatsData[$userID] = Flights\Utils\Initializers\initUserStatsMap();
            }
        }
        foreach ($DefendersIDs as $userID) {
            if (empty($UserStatsData[$userID])) {
                $UserStatsData[$userID] = Flights\Utils\Initializers\initUserStatsMap();
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
        $ShotDown            = $Combat['ShotDown'];
        $ForceContribution    = $Combat['ForceContribution'];

        $i = 0;
        // Parse result data - attackers fleet
        $TotalMetStolen = 0;
        $TotalCryStolen = 0;
        $TotalDeuStolen = 0;

        $maxResourcesPillage = Flights\Utils\Missions\calculateMaxPlanetPillage([
            'planet' => $TargetPlanet,
            'maxPillagePercentage' => 0,
        ]);

        $QryUpdateFleets = [];
        $UserDev_UpFl = [];
        $resourcesPillageByFleetID = [];

        if(!empty($AtkShips))
        {
            if($Result === COMBAT_ATK)
            {
                $pillageFactor = Flights\Utils\Calculations\calculatePillageFactor([
                    'mainAttackerMoraleLevel' => $FleetRow['morale_level'],
                    'mainDefenderMoraleLevel' => $TargetUser['morale_level'],
                    'isMainDefenderIdle' => ($IdleHours >= (7 * 24)),
                    'isTargetAbandoned' => $IsAbandoned,
                    'attackerIDs' => $AttackersIDs,
                ]);

                $maxResourcesPillage = Flights\Utils\Missions\calculateMaxPlanetPillage([
                    'planet' => $TargetPlanet,
                    'maxPillagePercentage' => $pillageFactor,
                ]);
            }

            foreach ($AtkShips as $User => $Ships) {
                $resourcesPillage = null;

                $thisFleetID = $AttackingFleetID[$User];

                $CalculatedAtkFleets[] = $thisFleetID;

                if (!empty($Ships)) {
                    if ($Result === COMBAT_ATK) {
                        $fleetPillageStorage = Flights\Utils\Calculations\calculatePillageStorage([
                            'fleetRow' => $attackingFleetRowsById[$thisFleetID],
                            'ships' => $Ships,
                        ]);

                        if ($fleetPillageStorage > 0) {
                            $resourcesPillage = Flights\Utils\Missions\calculateEvenResourcesPillage([
                                'maxPillagePerResource' => $maxResourcesPillage,
                                'fleetTotalStorage' => $fleetPillageStorage,
                            ]);
                            $resourcesPillageByFleetID[$thisFleetID] = $resourcesPillage;

                            foreach ($resourcesPillage as $resourceKey => $resourcePillage) {
                                $maxResourcesPillage[$resourceKey] -= $resourcePillage;
                            }

                            $Return['FleetArchive'][$AttackingFleetID[$User]]['Fleet_End_Res_Metal'] = $resourcesPillage['metal'];
                            $Return['FleetArchive'][$AttackingFleetID[$User]]['Fleet_End_Res_Crystal'] = $resourcesPillage['crystal'];
                            $Return['FleetArchive'][$AttackingFleetID[$User]]['Fleet_End_Res_Deuterium'] = $resourcesPillage['deuterium'];

                            $TotalMetStolen += $resourcesPillage['metal'];
                            $TotalCryStolen += $resourcesPillage['crystal'];
                            $TotalDeuStolen += $resourcesPillage['deuterium'];

                            if ($resourcesPillage['metal'] > 0) {
                                if(!isset($TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_METAL']))
                                {
                                    $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_METAL'] = 0;
                                }
                                $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_METAL'] += $resourcesPillage['metal'];
                            }
                            if ($resourcesPillage['crystal'] > 0) {
                                if(!isset($TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_CRYSTAL']))
                                {
                                    $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_CRYSTAL'] = 0;
                                }
                                $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_CRYSTAL'] += $resourcesPillage['crystal'];
                            }
                            if ($resourcesPillage['deuterium'] > 0) {
                                if(!isset($TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_DEUTERIUM']))
                                {
                                    $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_DEUTERIUM'] = 0;
                                }
                                $TriggerTasksCheck[$AttackingFleetOwners[$AttackingFleetID[$User]]]['BATTLE_COLLECT_DEUTERIUM'] += $resourcesPillage['deuterium'];
                            }
                        }
                    }

                    $QryUpdateFleets[$thisFleetID] = Flights\Utils\Factories\createFleetUpdateEntry([
                        'fleetID' => $thisFleetID,
                        'state' => '1',
                        'originalShips' => $AttackingFleets[$User],
                        'postCombatShips' => $AtkShips[$User],
                        'resourcesPillage' => $resourcesPillage,
                    ]);
                } else {
                    $DeleteFleet[] = $AttackingFleetID[$User];
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
                }
            }
        }

        foreach ($AttackingFleets as $fleetIndex => $fleetShips) {
            $thisFleetID = $AttackingFleetID[$fleetIndex];
            $resourcesPillage = (
                isset($resourcesPillageByFleetID[$thisFleetID]) ?
                $resourcesPillageByFleetID[$thisFleetID] :
                []
            );

            $UserDev_UpFl[$thisFleetID] = Flights\Utils\Factories\createFleetDevelopmentLogEntries([
                'originalShips' => $AttackingFleets[$fleetIndex],
                'postCombatShips' => $AtkShips[$fleetIndex],
                'resourcesPillage' => $resourcesPillage,
            ]);
        }

        $rebuiltDefenseSystems = [];

        // Parse result data - Defenders
        if (!empty($DefendingFleets)) {
            foreach ($DefendingFleets as $User => $Ships) {
                if($User == 0) {
                    $rebuiltDefenseSystems = Flights\Utils\Calculations\calculateUnitsRebuild([
                        'originalShips' => $DefendingFleets[0],
                        'postCombatShips' => $DefShips[0],
                        'fleetRow' => $FleetRow,
                        'targetUser' => $TargetUser,
                    ]);

                    foreach($Ships as $ID => $Count) {
                        $Count = ($DefShips[0][$ID] + $rebuiltDefenseSystems[$ID]);

                        if ($Count == 0) {
                            $Count = '0';
                        }
                        $TargetPlanet[$_Vars_GameElements[$ID]] = $Count;
                        if ($Count < $DefendingFleets[0][$ID]) {
                            $UserDev_UpPl[] = $ID.','.($DefendingFleets[0][$ID] - $Count);
                            $_FleetCache['updatePlanets'][$TargetPlanet['id']] = true;
                            $HPQ_PlanetUpdatedFields[] = $_Vars_GameElements[$ID];
                        }
                    }
                }
                else
                {
                    $QryUpdateFleets[$DefendingFleetID[$User]] = Flights\Utils\Factories\createFleetUpdateEntry([
                        'fleetID' => $DefendingFleetID[$User],
                        'originalShips' => $DefendingFleets[$User],
                        'postCombatShips' => $DefShips[$User],
                    ]);
                    $UserDev_UpFl[$DefendingFleetID[$User]] = Flights\Utils\Factories\createFleetDevelopmentLogEntries([
                        'originalShips' => $DefendingFleets[$User],
                        'postCombatShips' => $DefShips[$User],
                    ]);

                    if (empty($DefShips[$User])) {
                        $DeleteFleet[] = $DefendingFleetID[$User];
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

        foreach ($QryUpdateFleets as $fleetUpdateID => $fleetUpdateEntry) {
            $serializedFleetArray = Array2String($fleetUpdateEntry['fleet_array']);
            $serializedFleetArrayLost = Array2String($fleetUpdateEntry['fleet_array_lost']);

            if (!empty($fleetUpdateEntry['fleet_array'])) {
                if (!empty($fleetUpdateEntry['fleet_array_lost'])) {
                    if (strlen($serializedFleetArray) > strlen($serializedFleetArrayLost)) {
                        $Return['FleetArchive'][$fleetUpdateID]['Fleet_Array_Changes'] = "\"+D;{$serializedFleetArrayLost}|\"";
                    } else {
                        $Return['FleetArchive'][$fleetUpdateID]['Fleet_Array_Changes'] = "\"+L;{$serializedFleetArray}|\"";
                    }

                    $Return['FleetArchive'][$fleetUpdateID]['Fleet_Info_HasLostShips'] = '!true';
                }

                if (
                    $fleetUpdateID != $FleetRow['fleet_id'] &&
                    !empty($DefendingFleetID) &&
                    in_array($fleetUpdateID, $DefendingFleetID)
                ) {
                    $_FleetCache['defFleets'][$FleetRow['fleet_end_id']][$fleetUpdateID]['fleet_array'] = $serializedFleetArray;
                }
            }

            // TODO: Handle returning joined fleets the same way,
            // preventing unnecessary Updating Query
            if (
                $fleetUpdateID == $FleetRow['fleet_id'] &&
                $_FleetCache['fleetRowStatus'][$FleetRow['fleet_id']]['calcCount'] == 2
            ) {
                // Update $_FleetCache, instead of sending additional Query to Update FleetState
                // This fleet will be restored in this Calculation, so don't waste our time
                $CachePointer = &$_FleetCache['fleetRowUpdate'][$fleetUpdateID];
                $CachePointer['fleet_array'] = $serializedFleetArray;
                $CachePointer['fleet_resource_metal'] = (
                    $FleetRow['fleet_resource_metal'] +
                    $fleetUpdateEntry['fleet_resource_metal']
                );
                $CachePointer['fleet_resource_crystal'] = (
                    $FleetRow['fleet_resource_crystal'] +
                    $fleetUpdateEntry['fleet_resource_crystal']
                );
                $CachePointer['fleet_resource_deuterium'] = (
                    $FleetRow['fleet_resource_deuterium'] +
                    $fleetUpdateEntry['fleet_resource_deuterium']
                );
            } else {
                // Create UpdateFleet record for $_FleetCache
                $CachePointer = &$_FleetCache['updateFleets'][$fleetUpdateID];
                $CachePointer['fleet_array'] = $serializedFleetArray;
                $CachePointer['fleet_amount'] = $fleetUpdateEntry['fleet_amount'];
                $CachePointer['fleet_mess'] = $fleetUpdateEntry['fleet_mess'];
                $CachePointer['fleet_resource_metal'] = (
                    isset($CachePointer['fleet_resource_metal']) ?
                    $CachePointer['fleet_resource_metal'] + $fleetUpdateEntry['fleet_resource_metal'] :
                    $fleetUpdateEntry['fleet_resource_metal']
                );
                $CachePointer['fleet_resource_crystal'] = (
                    isset($CachePointer['fleet_resource_crystal']) ?
                    $CachePointer['fleet_resource_crystal'] + $fleetUpdateEntry['fleet_resource_crystal'] :
                    $fleetUpdateEntry['fleet_resource_crystal']
                );
                $CachePointer['fleet_resource_deuterium'] = (
                    isset($CachePointer['fleet_resource_deuterium']) ?
                    $CachePointer['fleet_resource_deuterium'] + $fleetUpdateEntry['fleet_resource_deuterium'] :
                    $fleetUpdateEntry['fleet_resource_deuterium']
                );

                if (
                    $fleetUpdateID != $FleetRow['fleet_id'] &&
                    (
                        empty($DefendingFleetID) ||
                        !in_array($fleetUpdateID, $DefendingFleetID)
                    )
                ) {
                    $CachePointer = &$_FleetCache['fleetRowUpdate'][$fleetUpdateID];
                    $CachePointer['fleet_array'] = $serializedFleetArray;
                    $CachePointer['fleet_resource_metal'] = (
                        isset($_FleetCache['acsFleets'][$fleetUpdateID]['fleet_resource_metal']) ?
                        (
                            $_FleetCache['acsFleets'][$fleetUpdateID]['fleet_resource_metal'] +
                            $fleetUpdateEntry['fleet_resource_metal']
                        ) :
                        $fleetUpdateEntry['fleet_resource_metal']
                    );
                    $CachePointer['fleet_resource_crystal'] = (
                        isset($_FleetCache['acsFleets'][$fleetUpdateID]['fleet_resource_crystal']) ?
                        (
                            $_FleetCache['acsFleets'][$fleetUpdateID]['fleet_resource_crystal'] +
                            $fleetUpdateEntry['fleet_resource_crystal']
                        ) :
                        $fleetUpdateEntry['fleet_resource_crystal']
                    );
                    $CachePointer['fleet_resource_deuterium'] = (
                        isset($_FleetCache['acsFleets'][$fleetUpdateID]['fleet_resource_deuterium']) ?
                        (
                            $_FleetCache['acsFleets'][$fleetUpdateID]['fleet_resource_deuterium'] +
                            $fleetUpdateEntry['fleet_resource_deuterium']
                        ) :
                        $fleetUpdateEntry['fleet_resource_deuterium']
                    );
                }
            }
        }

        if(!empty($UserDev_UpFl))
        {
            foreach($UserDev_UpFl as $FleetID => $DevArray)
            {
                if (empty($DevArray)) {
                    continue;
                }

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
                    if($AttackersCount > 1)
                    {
                        if(($ForceUsed_Array[$UserID] / $ForceUsed_Total) >= ACS_MINIMALFORCECONTRIBUTION)
                        {
                            $InACSBattle[$UserID] = $UserID;
                            $UserStatsData[$UserID]['raids_acs_won'] += 1;
                        }
                    }
                }
            }
            else if($Result === COMBAT_DRAW)
            {
                foreach($AttackersIDs as $UserID)
                {
                    if($AttackersCount > 1)
                    {
                        if(($ForceUsed_Array[$UserID] / $ForceUsed_Total) >= ACS_MINIMALFORCECONTRIBUTION)
                        {
                            $InACSBattle[$UserID] = $UserID;
                        }
                    }
                }
            }

            Flights\Utils\FleetCache\applyCombatResultStats([
                'userStats' => &$UserStatsData,
                'combatResultType' => $Result,
                'attackerIDs' => $AttackersIDs,
                'defenderIDs' => $DefendersIDs,
            ]);

            // Update User Destroyed & Lost Stats
            Flights\Utils\FleetCache\applyCombatUnitStats([
                'userStats' => &$UserStatsData,
                'combatShotdownResult' => $ShotDown,
                'mainAttackerUserID' => $FleetRow['fleet_owner'],
                'mainDefenderUserID' => $TargetUser['id'],
                'attackingFleetIDs' => $AttackingFleetID,
                'attackingFleetOwnerIDs' => $AttackingFleetOwners,
                'defendingFleetIDs' => $DefendingFleetID,
                'defendingFleetOwnerIDs' => $DefendingFleetOwners,
            ]);

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

        if (!$IsAbandoned) {
            $messageJSON = Flights\Utils\Factories\createCombatResultForMainDefenderMessage([
                'report' => $CreatedReport,
                'combatResult' => $Result,
                'fleetRow' => $FleetRow,
                'rebuiltElements' => Collections\compact($rebuiltDefenseSystems),
                'hasLostAnyDefenseSystems' => Flights\Utils\Helpers\hasLostAnyDefenseSystem([
                    'originalShips' => $DefendingFleets,
                    'postCombatShips' => $DefShips,
                ]),
            ]);

            Cache_Message($TargetUserID, 0, $FleetRow['fleet_start_time'], 3, '003', '012', $messageJSON);
        }

        if (count($DefendersIDs) > 1) {
            $messageJSON = Flights\Utils\Factories\createCombatResultForAlliedDefendersMessage([
                'report' => $CreatedReport,
                'combatResult' => $Result,
                'fleetRow' => $FleetRow,
            ]);

            unset($DefendersIDs[0]);

            Cache_Message($DefendersIDs, 0, $FleetRow['fleet_start_time'], 3, '003', '017', $messageJSON);
        }

        $messageJSON = Flights\Utils\Factories\createCombatResultForAttackersMessage([
            'missionType' => 2,
            'report' => $CreatedReport,
            'combatResult' => $Result,
            'totalAttackersResourcesLoss' => $attackersResourceLosses,
            'totalDefendersResourcesLoss' => $defendersResourceLosses,
            'totalResourcesPillage' => array_reduce(
                $resourcesPillageByFleetID,
                function ($totalResourcesPillage, $resourcesPillage) {
                    if ($totalResourcesPillage === null) {
                        return $resourcesPillage;
                    }

                    foreach ($resourcesPillage as $resourceKey => $resourceValue) {
                        $totalResourcesPillage[$resourceKey] += $resourceValue;
                    }

                    return $totalResourcesPillage;
                }
            ),
            'fleetRow' => $FleetRow,
        ]);

        Cache_Message($AttackersIDs, 0, $FleetRow['fleet_start_time'], 3, '003', '017', $messageJSON);

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
