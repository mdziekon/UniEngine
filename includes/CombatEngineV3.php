<?php

/**
 * Combat Engine v.3
 *
 * @author      mdziekon
 * @version     3.0.0
 * @build       10
 * @status      Unused, incompatible with UniEngine
 * @copyright   2010 by mdziekon [for UniEngine]
 *
 */

function Combat($Attacker, $Defender, $AttackerTech, $DefenderTech, $UseRapidFire = true){
    global $_Vars_Prices, $_Vars_CombatData;

    $Rounds             = array();
    $AtkLoseCount       = array();
    $DefLoseCount       = array();
    $PlanetDefSysLost   = array();

    if(!empty($Defender)){
        //$DefendersCount = count($Defender);
        //$DefenderShips = array();
        foreach($DefenderTech as $User => &$Techs){
            $Techs['tech_armour']  = 1 + (0.1 * $Techs['tech_armour']);
            $Techs['tech_shielding']   = 1 + (0.1 * $Techs['tech_shielding']);
            $Techs['tech_weapons'] = 1 + (0.1 * $Techs['tech_weapons']);
        }
        foreach($Defender as $User => $Ships){
            $DefendersCount += 1;
            foreach($Ships as $ID => $Count){
                $DefendersShipTypes[$User]          += 1;
                $DefenderShips[$User][$ID]          += $Count;
                $DefenderShipList[$User][]          = $ID;
                $DefenderUsers[$User]               = true;
                if(empty($ShipsHullValues[$ID])){
                    $ShipsHullValues[$ID]           = ($_Vars_Prices[$ID]['metal'] + $_Vars_Prices[$ID]['crystal']) / 10;
                }
                $DefenderShipsForces[$User][$ID]    = $_Vars_CombatData[$ID]['attack'] * $DefenderTech[$User]['tech_weapons'];
                $DefenderShipsShield[$User][$ID]    = $_Vars_CombatData[$ID]['shield'] * $DefenderTech[$User]['tech_shielding'];
            }
        }
    } else {
        $DefendersCount = 1;
        $DefenderShips = false;
    }

    if(!empty($Attacker)){
        //$AttackersCount = count($Attacker);
        //$AttackerShips = array();
        foreach($AttackerTech as $User => &$Techs){
            $Techs['tech_armour']  = 1 + (0.1 * $Techs['tech_armour']);
            $Techs['tech_shielding']   = 1 + (0.1 * $Techs['tech_shielding']);
            $Techs['tech_weapons'] = 1 + (0.1 * $Techs['tech_weapons']);
        }
        foreach($Attacker as $User => $Ships){
            $AttackersCount += 1;
            foreach($Ships as $ID => $Count){
                $AttackersShipTypes[$User]          += 1;
                $AttackerShips[$User][$ID]          += $Count;
                $AttackerShipList[$User][]          = $ID;
                $AttackerUsers[$User]               = true;
                if(empty($ShipsHullValues[$ID])){
                    $ShipsHullValues[$ID] = ($_Vars_Prices[$ID]['metal'] + $_Vars_Prices[$ID]['crystal']) / 10;
                }
                $AttackerShipsForces[$User][$ID]    = $_Vars_CombatData[$ID]['attack'] * $AttackerTech[$User]['tech_weapons'];
                $AttackerShipsShield[$User][$ID]    = $_Vars_CombatData[$ID]['shield'] * $AttackerTech[$User]['tech_shielding'];
            }
        }
    } else {
        return array('result' => false, 'error' => 'NO_ATTACKER');
    }

    $RoundsLimit = BATTLE_MAX_ROUNDS + 1;
    for($i = 1; $i <= $RoundsLimit; $i += 1){
        $AttackerForce              = 0;
        $AttackerShield             = -1;
        $AttackerCount              = 0;
        $AttackerForReport          = array();
        $AttackerDestroyedTarget    = false;
        $AttackerLost               = array();
        $DefenderForce              = 0;
        $DefenderShield             = -1;
        $DefenderCount              = 0;
        $DefenderForReport          = array();
        $DefenderDestroyedTarget    = false;
        $DefenderLost               = array();
        $CalcAtkRapidFire           = true;
        $CalcDefRapidFire           = true;
        $Break                      = false;
        $DoNotCalcExplosion         = array();
        $AtkShipDestroyedInExplosion= false;
        $DefShipDestroyedInExplosion= false;
        // Clear Targets
        $AttackerTargets            = array();

        $Rounds[$i]['atk']['ships'] = $AttackerShips;
        $Rounds[$i]['def']['ships'] = $DefenderShips;

        if($i > BATTLE_MAX_ROUNDS){
            break;
        }

        if(empty($AttackerShips) OR empty($DefenderShips)){
            break;
        }

        // ------------------------------------------------------------------------------------------------------------------------------------
        // Calculate Attacker(s) Part
        // 1. Create attacking list
        foreach($AttackerShipList as $User => $Ships){
            foreach($Ships as $ID){
                if($DefendersCount == 1){
                    foreach($DefenderUsers as $UserID => $True){
                        $PickAttackerTargetUserID = $UserID;
                    }
                } else {
                    $PickAttackerTargetUserID = array_rand($DefenderUsers);
                }
                if($DefendersShipTypes[$PickAttackerTargetUserID] == 1){
                    foreach($DefenderShipList[$PickAttackerTargetUserID] as $ShipID){
                        $PickAttackerTargetShipID = $ShipID;
                    }
                } else {
                    $PickAttackerTargetShipID = $DefenderShipList[$PickAttackerTargetUserID][array_rand($DefenderShipList[$PickAttackerTargetUserID])];
                }
                $AttackerTargets[$PickAttackerTargetUserID][$PickAttackerTargetShipID][$User][] = $ID;
            }
        }
        // 2. Calculate Battle
        foreach($AttackerTargets as $DefUser => $DefShips){
            foreach($DefShips as $DefShipID => $AtkFiring){
                $InterLoop += 1;
                // 2.1 Here start calculation for each selected Target
                // - A. Initialization
                $AtkForce       = 0;
                $AtkCount       = 0;
                $Destroyed      = 0;
                $UseShieldInRF  = false;
                // - B. Calculate Attacker Force
                foreach($AtkFiring as $AtkUser => $ThisUShips){
                    foreach($ThisUShips as $ID){
                        $AtkForce += $AttackerShips[$AtkUser][$ID] * $AttackerShipsForces[$AtkUser][$ID];
                        $AtkCount += $AttackerShips[$AtkUser][$ID];
                    }
                }
                $Rounds[$i]['atk']['force'] += $AtkForce;
                $Rounds[$i]['atk']['count'] += $AtkCount;
                // - B. END
                // - C. Calculate Defender Shield
                if($AtkForce <= 0){
                    continue;
                }

                $DefShield = $DefenderShips[$DefUser][$DefShipID] * $DefenderShipsShield[$DefUser][$DefShipID];
                if($DefShield > 0 AND ($DefShield / $AtkForce) > 0.01){
                    if($DefShield > $AtkForce){
                        $Rounds[$i]['def']['shield'] += $AtkForce;
                    } else {
                        $Rounds[$i]['def']['shield'] += $DefShield;
                    }
                    $AtkForce   -= $DefShield;
                    $DefShield  -= $Rounds[$i]['def']['shield'];
                    if($DefShield > 0){
                        $UseShieldInRF = true;
                    }
                }

                if($AtkForce <= 0){
                    continue;
                }
                // - C. END
                // - D. Calculate Firing (Regular)
                $HowManyCouldDestroy = $AtkForce / ($ShipsHullValues[$DefShipID] * $DefenderTech[$DefUser]['tech_armour']);

                if($HowManyCouldDestroy >= $DefenderShips[$DefUser][$DefShipID]){
                    $Destroyed = $DefenderShips[$DefUser][$DefShipID];
                    $SaveHullDmg['def'][$DefUser][$DefShipID] = 0;
                    $AttackerDestroyedTarget = true;
                } else {
                    $HowManyCouldDestroyReal = floor($HowManyCouldDestroy);
                    $ChanceToBlow = $HowManyCouldDestroy - $HowManyCouldDestroyReal;

                    if($SaveHullDmg['def'][$DefUser][$DefShipID] > 0){
                        $ChanceToBlow += $SaveHullDmg['def'][$DefUser][$DefShipID];
                    }
                    if($ChanceToBlow >= 1){
                        $HowManyCouldDestroyReal += 1;
                        $SaveHullDmg['def'][$DefUser][$DefShipID] = $ChanceToBlow - 1;
                    } else {
                        $SaveHullDmg['def'][$DefUser][$DefShipID] = $ChanceToBlow;
                    }

                    if($HowManyCouldDestroyReal >= $DefenderShips[$DefUser][$DefShipID]){
                        $Destroyed = $DefenderShips[$DefUser][$DefShipID];
                        $AttackerDestroyedTarget = true;
                    } else {
                        $Destroyed = $HowManyCouldDestroyReal;
                    }
                }
                $DefenderLost[$DefUser][$DefShipID] += $Destroyed;
                $DefLoseCount[$DefShipID] += $Destroyed;
                if($AttackerTargetUser == 0 AND $AttackerTarget > 400 AND $AttackerTarget < 500){
                    $PlanetDefSysLost[$DefShipID] += $Destroyed;
                }
                if($AttackerDestroyedTarget){
                    $SaveHullDmg['def'][$DefUser][$DefShipID] = 0;
                }
                // - D. END
                // - E. Calculate Firing (Rapid Fire)
                if($UseRapidFire){
                    if(!$AttackerDestroyedTarget){
                        $RapidFireMaxShips  = 0;
                        $AtkForce           = 0;
                        $AttackerDestroyedTarget = false;
                        foreach($AtkFiring as $AtkUser => $ThisUShips){
                            foreach($ThisUShips as $ID){
                                if($_Vars_CombatData[$ID]['sd'][$DefShipID] > 1){
                                    if(!$SDChance[$ID][$DefShipID]){
                                        $SDChance[$ID][$DefShipID] = floor((($_Vars_CombatData[$ID]['sd'][$DefShipID] - 1) / $_Vars_CombatData[$ID]['sd'][$DefShipID]) * 100);
                                    }
                                    for($SDCounter = $AttackerShips[$AtkUser][$ID]; $SDCounter > 0; $SDCounter -= 1){
                                        while(mt_rand(1,100) <= $SDChance[$ID][$DefShipID]){
                                            $AtkForce += $AttackerShipsForces[$AtkUser][$ID];
                                            $Rounds[$i]['atk']['count'] += 1;
                                            $Rounds[$i]['atk']['force'] += $AttackerShipsForces[$AtkUser][$ID];
                                            $RapidFireMaxShips += 1;
                                        }
                                    }
                                }
                            }
                        }

                        if($AtkForce > 0){
                            $HowManyCouldDestroy = $AtkForce / ($ShipsHullValues[$DefShipID] * $DefenderTech[$DefUser]['tech_armour']);

                            if($HowManyCouldDestroy >= ($DefenderShips[$DefUser][$DefShipID] - $Destroyed)){
                                $Destroyed = ($DefenderShips[$DefUser][$DefShipID] - $Destroyed);
                                $SaveHullDmg['def'][$DefUser][$DefShipID] = 0;
                                $AttackerDestroyedTarget = true;
                            } else {
                                $HowManyCouldDestroyReal = floor($HowManyCouldDestroy);
                                $ChanceToBlow = $HowManyCouldDestroy - $HowManyCouldDestroyReal;

                                if($SaveHullDmg['def'][$DefUser][$DefShipID] > 0){
                                    $ChanceToBlow += $SaveHullDmg['def'][$DefUser][$DefShipID];
                                }
                                if($ChanceToBlow >= 1){
                                    $HowManyCouldDestroyReal += 1;
                                    $SaveHullDmg['def'][$DefUser][$DefShipID] = $ChanceToBlow - 1;
                                } else {
                                    $SaveHullDmg['def'][$DefUser][$DefShipID] = $ChanceToBlow;
                                }

                                if($HowManyCouldDestroyReal >= ($DefenderShips[$DefUser][$DefShipID] - $Destroyed)){
                                    $Destroyed = ($DefenderShips[$DefUser][$DefShipID] - $Destroyed);
                                    $AttackerDestroyedTarget = true;
                                } else {
                                    $Destroyed = $HowManyCouldDestroyReal;
                                }
                            }
                            $DefenderLost[$DefUser][$DefShipID] += $Destroyed;
                            $DefLoseCount[$DefShipID] += $Destroyed;
                            if($AttackerTargetUser == 0 AND $AttackerTarget > 400 AND $AttackerTarget < 500){
                                $PlanetDefSysLost[$DefShipID] += $Destroyed;
                            }
                            if($AttackerDestroyedTarget){
                                $SaveHullDmg['def'][$DefUser][$DefShipID] = 0;
                            }
                        }
                    }
                }
                // - E. END
            }
        }
        // ---------------------------------
        // ------------------------------------------------------------------------------------------------------------------------------------
        // Calculate Defender(s) Part
        // 1. Create attacking list
        foreach($DefenderShipList as $User => $Ships){
            foreach($Ships as $ID){
                if($AttackersCount == 1){
                    foreach($AttackerUsers as $UserID => $True){
                        $PickDefenderTargetUserID = $UserID;
                    }
                } else {
                    $PickDefenderTargetUserID = array_rand($AttackerUsers);
                }
                if($AttackersShipTypes[$PickDefenderTargetUserID] == 1){
                    foreach($AttackerShipList[$PickDefenderTargetUserID] as $ShipID){
                        $PickDefenderTargetShipID = $ShipID;
                    }
                } else {
                    $PickDefenderTargetShipID = $AttackerShipList[$PickDefenderTargetUserID][array_rand($AttackerShipList[$PickDefenderTargetUserID])];
                }
                $DefenderTargets[$PickDefenderTargetUserID][$PickDefenderTargetShipID][$User][] = $ID;
            }
        }
        // 2. Calculate Battle
        foreach($DefenderTargets as $AtkUser => $AtkShips){
            foreach($AtkShips as $AtkShipID => $DefFiring){
                // 2.1 Here start calculation for each selected Target
                // - A. Initialization
                $DefForce       = 0;
                $DefCount       = 0;
                $Destroyed      = 0;
                $UseShieldInRF  = false;
                // - B. Calculate Attacker Force
                foreach($DefFiring as $DefUser => $ThisUShips){
                    foreach($ThisUShips as $ID){
                        $DefForce += $DefenderShips[$DefUser][$ID] * $DefenderShipsForces[$DefUser][$ID];
                        $DefCount += $DefenderShips[$DefUser][$ID];
                    }
                }
                $Rounds[$i]['def']['force'] += $DefForce;
                $Rounds[$i]['def']['count'] += $DefCount;
                // - B. END
                // - C. Calculate Defender Shield
                if($DefForce <= 0){
                    continue;
                }

                $AtkShield = $AttackerShips[$AtkUser][$AtkShipID] * $AttackerShipsShield[$AtkUser][$AtkShipID];
                if($AtkShield > 0 AND ($AtkShield / $DefForce) > 0.01){
                    if($AtkShield > $DefForce){
                        $Rounds[$i]['atk']['shield'] += $DefForce;
                    } else {
                        $Rounds[$i]['atk']['shield'] += $AtkShield;
                    }
                    $DefForce   -= $AtkShield;
                    $AtkShield  -= $Rounds[$i]['atk']['shield'];
                    if($AtkShield > 0){
                        $UseShieldInRF = true;
                    }
                }

                if($DefForce <= 0){
                    continue;
                }
                // - C. END
                // - D. Calculate Firing (Regular)
                $HowManyCouldDestroy = $DefForce / ($ShipsHullValues[$AtkShipID] * $AttackerTech[$AtkUser]['tech_armour']);

                if($HowManyCouldDestroy >= $AttackerShips[$AtkUser][$AtkShipID]){
                    $Destroyed = $AttackerShips[$AtkUser][$AtkShipID];
                    $SaveHullDmg['atk'][$AtkUser][$AtkShipID] = 0;
                    $DefenderDestroyedTarget = true;
                } else {
                    $HowManyCouldDestroyReal = floor($HowManyCouldDestroy);
                    $ChanceToBlow = $HowManyCouldDestroy - $HowManyCouldDestroyReal;

                    if($SaveHullDmg['atk'][$AtkUser][$AtkShipID] > 0){
                        $ChanceToBlow += $SaveHullDmg['atk'][$AtkUser][$AtkShipID];
                    }
                    if($ChanceToBlow >= 1){
                        $HowManyCouldDestroyReal += 1;
                        $SaveHullDmg['atk'][$AtkUser][$AtkShipID] = $ChanceToBlow - 1;
                    } else {
                        $SaveHullDmg['atk'][$AtkUser][$AtkShipID] = $ChanceToBlow;
                    }

                    if($HowManyCouldDestroyReal >= $AttackerShips[$AtkUser][$AtkShipID]){
                        $Destroyed = $AttackerShips[$AtkUser][$AtkShipID];
                        $DefenderDestroyedTarget = true;
                    } else {
                        $Destroyed = $HowManyCouldDestroyReal;
                    }
                }
                $AttackerLost[$AtkUser][$AtkShipID] += $Destroyed;
                $AtkLoseCount[$AtkShipID] += $Destroyed;
                if($DefenderDestroyedTarget){
                    $SaveHullDmg['atk'][$AtkUser][$AtkShipID] = 0;
                }
                // - D. END
                // - E. Calculate Firing (Rapid Fire)
                if($UseRapidFire){
                    if(!$DefenderDestroyedTarget){
                        $RapidFireMaxShips  = 0;
                        $DefForce           = 0;
                        $DefenderDestroyedTarget = false;
                        foreach($DefFiring as $DefUser => $ThisUShips){
                            foreach($ThisUShips as $ID){
                                if($_Vars_CombatData[$ID]['sd'][$AtkShipID] > 1){
                                    if(!$SDChance[$ID][$AtkShipID]){
                                        $SDChance[$ID][$AtkShipID] = floor((($_Vars_CombatData[$ID]['sd'][$AtkShipID] - 1) / $_Vars_CombatData[$ID]['sd'][$AtkShipID]) * 100);
                                    }
                                    for($SDCounter = $DefenderShips[$DefUser][$ID]; $SDCounter > 0; $SDCounter -= 1){
                                        while(mt_rand(1,100) <= $SDChance[$ID][$AtkShipID]){
                                            $DefForce += $DefenderShipsForces[$DefUser][$ID];
                                            $Rounds[$i]['def']['count'] += 1;
                                            $Rounds[$i]['def']['force'] += $DefenderShipsForces[$DefUser][$ID];
                                            $RapidFireMaxShips += 1;
                                        }
                                    }
                                }
                            }
                        }

                        if($DefForce > 0){
                            $HowManyCouldDestroy = $DefForce / ($ShipsHullValues[$AtkShipID] * $AttackerTech[$AtkUser]['tech_armour']);

                            if($HowManyCouldDestroy >= ($AttackerShips[$AtkUser][$AtkShipID] - $Destroyed)){
                                $Destroyed = ($AttackerShips[$AtkUser][$AtkShipID] - $Destroyed);
                                $SaveHullDmg['atk'][$AtkUser][$AtkShipID] = 0;
                                $DefenderDestroyedTarget = true;
                            } else {
                                $HowManyCouldDestroyReal = floor($HowManyCouldDestroy);
                                $ChanceToBlow = $HowManyCouldDestroy - $HowManyCouldDestroyReal;

                                if($SaveHullDmg['atk'][$AtkUser][$AtkShipID] > 0){
                                    $ChanceToBlow += $SaveHullDmg['atk'][$AtkUser][$AtkShipID];
                                }
                                if($ChanceToBlow >= 1){
                                    $HowManyCouldDestroyReal += 1;
                                    $SaveHullDmg['atk'][$AtkUser][$AtkShipID] = $ChanceToBlow - 1;
                                } else {
                                    $SaveHullDmg['atk'][$AtkUser][$AtkShipID] = $ChanceToBlow;
                                }

                                if($HowManyCouldDestroyReal >= ($AttackerShips[$AtkUser][$AtkShipID] - $Destroyed)){
                                    $Destroyed = ($AttackerShips[$AtkUser][$AtkShipID] - $Destroyed);
                                    $DefenderDestroyedTarget = true;
                                } else {
                                    $Destroyed = $HowManyCouldDestroyReal;
                                }
                            }
                            $AttackerLost[$AtkUser][$AtkShipID] += $Destroyed;
                            $AtkLoseCount[$AtkShipID] += $Destroyed;
                            if($DefenderDestroyedTarget){
                                $SaveHullDmg['atk'][$AtkUser][$AtkShipID] = 0;
                            }
                        }
                    }
                }
                // - E. END
            }
        }

        // ------------------------------------------------------------------------------------------------------------------------------------
        // Common Parts

        // Calculate ships Explosion
        if(!empty($SaveHullDmg)){
            foreach($SaveHullDmg as $WhichPart => $Data){
                foreach($Data as $User => $Ships){
                    foreach($Ships as $Ship => $ExplosionChance){
                        if($ExplosionChance > 0.3){
                            if($ExplosionChance >= 1 OR mt_rand(1,100) <= ($ExplosionChance * 100)){
                                $SaveHullDmg[$WhichPart][$User][$Ship] = 0;
                                if($WhichPart == 'def'){
                                    $DefenderLost[$User][$Ship] += 1;
                                    $DefLoseCount[$Ship]        += 1;
                                    if($User == 0 AND $Ship > 400 AND $Ship < 500){
                                        $PlanetDefSysLost[$Ship] += 1;
                                    }

                                    if($DefenderLost[$User][$Ship] >= $DefenderShips[$User][$Ship]){
                                        unset($DefenderShipList[$User][$Ship]);
                                        if(empty($DefenderShipList[$User])){
                                            unset($DefenderShipList[$User]);
                                            unset($DefenderUsers[$User]);
                                        }
                                    }
                                } else {
                                    $AttackerLost[$User][$Ship] += 1;
                                    $AtkLoseCount[$Ship]        += 1;

                                    if($AttackerLost[$User][$Ship] >= $AttackerShips[$User][$Ship]){
                                        unset($AttackerShipList[$User][$Ship]);
                                        if(empty($AttackerShipList[$User])){
                                            unset($AttackerShipList[$User]);
                                            unset($AttackerUsers[$User]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // End of Calculations
        // --------------------------------------------

        if(!empty($AttackerLost)){
            foreach($AttackerLost as $User => $Ships){
                foreach($Ships as $ID => $Count){
                    if($Count > 0){
                        if($AttackerShips[$User][$ID] <= $Count){
                            unset($AttackerShips[$User][$ID]);
                            unset($AttackerShipList[$User][array_search($ID, $AttackerShipList[$User])]);
                            if(empty($AttackerShipList[$User])){
                                unset($AttackerShipList[$User]);
                                unset($AttackerUsers[$User]);
                                $AttackersCount -= 1;
                            }
                            $AttackersShipTypes[$User] -= 1;
                        } else {
                            $AttackerShips[$User][$ID] -= $Count;
                        }
                    }
                }
            }
        }

        if(!empty($DefenderLost)){

            foreach($DefenderLost as $User => $Ships){
                foreach($Ships as $ID => $Count){
                    if($Count > 0){
                        if($DefenderShips[$User][$ID] <= $Count){
                            unset($DefenderShips[$User][$ID]);
                            unset($DefenderShipList[$User][array_search($ID, $DefenderShipList[$User])]);
                            if(empty($DefenderShipList[$User])){
                                unset($DefenderShipList[$User]);
                                unset($DefenderUsers[$User]);
                                $DefendersCount -= 1;
                            }
                            $DefendersShipTypes[$User] -= 1;
                        } else {
                            $DefenderShips[$User][$ID] -= $Count;
                        }
                    }
                }
            }
        }

        if(empty($DefenderShipList)){
            unset($DefenderShips);
        }
        if(empty($AttackerShipList)){
            unset($AttackerShips);
        }

    }

    if((!empty($AttackerShips) AND !empty($DefenderShips)) OR (empty($AttackerShips) AND empty($DefenderShips))){
        var_dump($AttackerShips);
        var_dump($DefenderShips);
        $BattleResult = COMBAT_DRAW; // It's a Draw
    } elseif(empty($AttackerShips)){
        $BattleResult = COMBAT_DEF;  // Defenders Won!
    } elseif(empty($DefenderShips)){
        $BattleResult = COMBAT_ATK;  // Attackers Won!
    } else {
        return array('result' => false, 'error' => 'BAD_COMBAT_RESULT');
    }

    return array('return' => true, 'AttackerShips' => $AttackerShips, 'DefenderShips' => $DefenderShips, 'rounds' => $Rounds, 'result' => $BattleResult, 'AtkLose' => $AtkLoseCount, 'DefLose' => $DefLoseCount, 'DefSysLost' => $PlanetDefSysLost);
}

?>
