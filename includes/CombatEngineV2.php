<?php

/**
 * Combat Engine v.2
 *
 * @author      mdziekon
 * @version     2.2.2
 * @build       15
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
        $DefendersCount = count($Defender);
        //$DefenderShips = array();
        foreach($Defender as $User => $Ships){
            foreach($Ships as $ID => $Count){
                $DefenderShips[$User][$ID]  += $Count;
                $DefenderShipList[$User][]  = $ID;
                $DefenderUsers[$User]       = true;
                if(empty($ShipsHullValues[$ID])){
                    $ShipsHullValues[$ID] = ($_Vars_Prices[$ID]['metal'] + $_Vars_Prices[$ID]['crystal']) / 10;
                }
            }
        }
        foreach($DefenderTech as $User => &$Techs){
            $Techs['tech_armour']  = 1 + (0.1 * $Techs['tech_armour']);
            $Techs['tech_shielding']   = 1 + (0.1 * $Techs['tech_shielding']);
            $Techs['tech_weapons'] = 1 + (0.1 * $Techs['tech_weapons']);
        }
    } else {
        $DefendersCount = 1;
        $DefenderShips = false;
    }

    if(!empty($Attacker)){
        $AttackersCount = count($Attacker);
        //$AttackerShips = array();
        foreach($Attacker as $User => $Ships){
            foreach($Ships as $ID => $Count){
                $AttackerShips[$User][$ID]  += $Count;
                $AttackerShipList[$User][]  = $ID;
                $AttackerUsers[$User]       = true;
                if(empty($ShipsHullValues[$ID])){
                    $ShipsHullValues[$ID] = ($_Vars_Prices[$ID]['metal'] + $_Vars_Prices[$ID]['crystal']) / 10;
                }
            }
        }
        foreach($AttackerTech as $User => &$Techs){
            $Techs['tech_armour']  = 1 + (0.1 * $Techs['tech_armour']);
            $Techs['tech_shielding']   = 1 + (0.1 * $Techs['tech_shielding']);
            $Techs['tech_weapons'] = 1 + (0.1 * $Techs['tech_weapons']);
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

        $Rounds[$i]['atk']['ships'] = $AttackerShips;
        $Rounds[$i]['def']['ships'] = $DefenderShips;

        if($i > BATTLE_MAX_ROUNDS){
            break;
        }

        if(empty($AttackerShips) OR empty($DefenderShips)){
            break;
        }

        // Calculate Attacker(s) Force
        // --------------------------------------------
        // 1. Get Target
        $AttackerTargetUser     = array_rand($DefenderUsers);
        $AttackerTarget         = array_rand($DefenderShipList[$AttackerTargetUser]);
        $AttackerTargetIndex    = $AttackerTarget;
        $AttackerTarget         = $DefenderShipList[$AttackerTargetUser][$AttackerTarget];

        // 2. Calculate Force
        foreach($AttackerShips as $User => $Ships){
            foreach($Ships as $ID => $Count){
                $AttackerForce += $Count * $_Vars_CombatData[$ID]['attack'] * $AttackerTech[$User]['tech_weapons'];
                $AttackerCount += $Count;
            }
        }
        // --------------------------------------------
        // End of Calculations for Attacker(s)


        // Calculate Defender(s) Force
        // --------------------------------------------
        // 1. Get Target
        $DefenderTargetUser     = array_rand($AttackerUsers);
        $DefenderTarget         = array_rand($AttackerShipList[$DefenderTargetUser]);
        $DefenderTargetIndex    = $DefenderTarget;
        $DefenderTarget         = $AttackerShipList[$DefenderTargetUser][$DefenderTarget];

        // 2. Calculate Force
        foreach($DefenderShips as $User => $Ships){
            foreach($Ships as $ID => $Count){
                $DefenderForce += $Count * $_Vars_CombatData[$ID]['attack'] * $DefenderTech[$User]['tech_weapons'];
                $DefenderCount += $Count;
            }
        }
        // --------------------------------------------
        // End of Calculations for Defender(s)

        // --------------------------------------------
        // Calculate Destruction - Now firing Attacker
        $Rounds[$i]['atk']['force'] = $AttackerForce;
        $Rounds[$i]['atk']['count'] = $AttackerCount;
        while($AttackerForce > 0){
            $ClearForce = false;
            $Destroyed = 0;
            $AttackerDestroyedTarget = false;

            if($DefenderShield < 0){
                $DefenderShield = $DefenderShips[$AttackerTargetUser][$AttackerTarget] * $_Vars_CombatData[$AttackerTarget]['shield'] * $DefenderTech[$AttackerTargetUser]['tech_shielding'];
                if($DefenderShield > $AttackerForce){
                    $Rounds[$i]['def']['shield'] = $AttackerForce;
                } else {
                    $Rounds[$i]['def']['shield'] = $DefenderShield;
                }
                $AttackerForce  -= $DefenderShield;
                $DefenderShield -= $Rounds[$i]['def']['shield'];
            }
            if($AttackerForce <= 0){
                break;
            }
            $HowManyCouldDestroy = $AttackerForce / ($ShipsHullValues[$AttackerTarget] * $DefenderTech[$AttackerTargetUser]['tech_armour']);

            if($HowManyCouldDestroy >= $DefenderShips[$AttackerTargetUser][$AttackerTarget]){
                $Destroyed = $DefenderShips[$AttackerTargetUser][$AttackerTarget];
                $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget] = 0;
                $AttackerDestroyedTarget = true;
            } else {
                $HowManyCouldDestroyReal = floor($HowManyCouldDestroy);
                $ChanceToBlow = $HowManyCouldDestroy - $HowManyCouldDestroyReal;

                if($ChanceToBlow < 1){
                    $ClearForce = true;
                }

                if($HowManyCouldDestroy < 1 AND $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget] > 0){
                    $ChanceToBlow += $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget];
                }
                if($ChanceToBlow > 0.3){
                    if($ChanceToBlow >= 1 OR mt_rand(1,100) <= ($ChanceToBlow * 100)){
                        $HowManyCouldDestroyReal += 1;
                        $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget] = 0;
                        $DefShipDestroyedInExplosion = true;
                    } else {
                        $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget] = $ChanceToBlow;
                    }
                    $DoNotCalcExplosion['def'][$AttackerTargetUser][$AttackerTarget] = 1;
                } else {
                    $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget] = $ChanceToBlow;
                }
                if($HowManyCouldDestroyReal >= $DefenderShips[$AttackerTargetUser][$AttackerTarget]){
                    $Destroyed = $DefenderShips[$AttackerTargetUser][$AttackerTarget];
                    $AttackerDestroyedTarget = true;
                } else {
                    $Destroyed = $HowManyCouldDestroyReal;
                }
            }
            $DefenderLost[$AttackerTargetUser][$AttackerTarget] += $Destroyed;
            $DefLoseCount[$AttackerTarget] += $Destroyed;
            if($AttackerTargetUser == 0 AND $AttackerTarget > 400 AND $AttackerTarget < 500){
                $PlanetDefSysLost[$AttackerTarget] += $Destroyed;
            }
            if($ClearForce){
                if($HowManyCouldDestroyReal <= $Destroyed){
                    $AttackerForce = 0;
                }
            } else {
                $AttackerForce -= ($ShipsHullValues[$AttackerTarget] * $DefenderTech[$AttackerTargetUser]['tech_armour']) * $Destroyed;
            }
            if($AttackerDestroyedTarget){
                unset($DefenderShipList[$AttackerTargetUser][$AttackerTargetIndex]);
                if(empty($DefenderShipList[$AttackerTargetUser])){
                    unset($DefenderShipList[$AttackerTargetUser]);
                    unset($DefenderUsers[$AttackerTargetUser]);
                }
                if(empty($DefenderShipList)){
                    $CalcAtkRapidFire = false;
                    break;
                }
                if($AttackerForce > 0 OR ($UseRapidFire == TRUE AND $CalcAtkRapidFire == TRUE)){
                    $AttackerTargetUser     = array_rand($DefenderUsers);
                    $AttackerTarget         = array_rand($DefenderShipList[$AttackerTargetUser]);
                    $AttackerTargetIndex    = $AttackerTarget;
                    $AttackerTarget         = $DefenderShipList[$AttackerTargetUser][$AttackerTarget];
                    $DefenderShield         = -1;
                }
            }
        }


        // Calculate Rapid Fire
        if($UseRapidFire){
            if($CalcAtkRapidFire){
                $RapidFireMaxShips = 0;
                foreach($AttackerShips as $User => $Ships){
                    foreach($Ships as $ID => $Count){
                        if($_Vars_CombatData[$ID]['sd'][$AttackerTarget] > 1){
                            $Chance = floor((($_Vars_CombatData[$ID]['sd'][$AttackerTarget] - 1) / $_Vars_CombatData[$ID]['sd'][$AttackerTarget]) * 100);
                            for($SDCounter = 0; $SDCounter < $Count; $SDCounter += 1){
                                while(mt_rand(1,100) <= $Chance){
                                    $CalcForce = $_Vars_CombatData[$ID]['attack'] * $AttackerTech[$AttackerTargetUser]['tech_weapons'];
                                    $AttackerForce += $CalcForce;
                                    $Rounds[$i]['atk']['count'] += 1;
                                    $Rounds[$i]['atk']['force'] += $CalcForce;
                                    $RapidFireMaxShips += 1;
                                }
                            }
                        }
                    }
                }

                if($AttackerForce > 0){
                    $Destroyed = 0;
                    $AttackerDestroyedTarget = false;

                    $DefenderShipsLeft = $DefenderShips[$AttackerTargetUser][$AttackerTarget] - $DefenderLost[$AttackerTargetUser][$AttackerTarget];

                    if($DefenderShield < 0){
                        $DefenderShield = $DefenderShipsLeft * $_Vars_CombatData[$AttackerTarget]['shield'] * $DefenderTech[$AttackerTargetUser]['tech_shielding'];
                        if($DefenderShield > $AttackerForce){
                            $Rounds[$i]['def']['shield'] += $AttackerForce;
                        } else {
                            $Rounds[$i]['def']['shield'] += $DefenderShield;
                        }
                        $AttackerForce -= $DefenderShield;
                    } elseif($DefenderShield > 0){
                        if($DefenderShield > $AttackerForce){
                            $Rounds[$i]['def']['shield'] += $AttackerForce;
                        } else {
                            $Rounds[$i]['def']['shield'] += $DefenderShield;
                        }
                        $AttackerForce -= $DefenderShield;
                    }

                    $HowManyCouldDestroy = $AttackerForce / ($ShipsHullValues[$AttackerTarget] * $DefenderTech[$AttackerTargetUser]['tech_armour']);
                    if($HowManyCouldDestroy > $RapidFireMaxShips){
                        $HowManyCouldDestroy = $RapidFireMaxShips;
                    }

                    if($HowManyCouldDestroy >= $DefenderShipsLeft){
                        $Destroyed = $DefenderShipsLeft;
                        $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget] = 0;
                        $AttackerDestroyedTarget = true;
                    } else {
                        $HowManyCouldDestroyReal = floor($HowManyCouldDestroy);
                        $ChanceToBlow = $HowManyCouldDestroy - $HowManyCouldDestroyReal;
                        if($HowManyCouldDestroy < 1 AND $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget] > 0){
                            $ChanceToBlow += $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget];
                        }
                        if($ChanceToBlow > 0.3){
                            if($ChanceToBlow >= 1 OR mt_rand(1,100) <= ($ChanceToBlow * 100)){
                                $HowManyCouldDestroyReal += 1;
                                $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget] = 0;
                            } else {
                                $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget] = $ChanceToBlow;
                            }
                                $DoNotCalcExplosion['def'][$AttackerTargetUser][$AttackerTarget] = 1;
                        } else {
                            $SaveHullDmg['def'][$AttackerTargetUser][$AttackerTarget] = $ChanceToBlow;
                        }
                        if($HowManyCouldDestroyReal >= $DefenderShipsLeft){
                            $Destroyed = $DefenderShipsLeft;
                            $AttackerDestroyedTarget = true;
                        } else {
                            $Destroyed = $HowManyCouldDestroyReal;
                        }
                        $ClearForce = true;
                    }
                    $DefenderLost[$AttackerTargetUser][$AttackerTarget] += $Destroyed;
                    $DefLoseCount[$AttackerTarget] += $Destroyed;
                    if($AttackerTargetUser == 0 AND $AttackerTarget > 400 AND $AttackerTarget < 500){
                        $PlanetDefSysLost[$AttackerTarget] += $Destroyed;
                    }
                    if($AttackerDestroyedTarget){
                        unset($DefenderShipList[$AttackerTargetUser][$AttackerTargetIndex]);
                        if(empty($DefenderShipList[$AttackerTargetUser])){
                            unset($DefenderShipList[$AttackerTargetUser]);
                            unset($DefenderUsers[$AttackerTargetUser]);
                        }
                    }
                }
            }
        }


        // Calculate ships Explosion (Def Ships)
        if(!empty($SaveHullDmg['def'])){
            foreach($SaveHullDmg['def'] as $User => $Ships){
                foreach($Ships as $Ship => $ExplosionChance){
                    if($DoNotCalcExplosion['def'][$User][$Ship] != 1){
                        if($ExplosionChance > 0.3){
                            if($ExplosionChance >= 1 OR mt_rand(1,100) <= ($ExplosionChance * 100)){
                                $SaveHullDmg['def'][$User][$Ship] = 0;
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
                            }
                        }
                    }
                }
            }
        }


        // End of Calculations
        // --------------------------------------------

        // --------------------------------------------
        // Calculate Destruction - Now firing Defender

        $Rounds[$i]['def']['force'] = $DefenderForce;
        $Rounds[$i]['def']['count'] = $DefenderCount;
        while($DefenderForce > 0){
            $ClearForce = false;
            $Destroyed = 0;
            $DefenderDestroyedTarget = false;

            if($AttackerShield < 0){
                $AttackerShield = $AttackerShips[$DefenderTargetUser][$DefenderTarget] * $_Vars_CombatData[$DefenderTarget]['shield'] * $DefenderTech[$DefenderTargetUser]['tech_shielding'];
                if($AttackerShield > $DefenderForce){
                    $Rounds[$i]['atk']['shield'] = $DefenderForce;
                } else {
                    $Rounds[$i]['atk']['shield'] = $AttackerShield;
                }
                $DefenderForce  -= $AttackerShield;
                $AttackerShield -= $Rounds[$i]['atk']['shield'];
            }
            if($DefenderForce <= 0){
                break;
            }
            $HowManyCouldDestroy = $DefenderForce / ($ShipsHullValues[$DefenderTarget] * $AttackerTech[$DefenderTargetUser]['tech_armour']);

            if($HowManyCouldDestroy >= $AttackerShips[$DefenderTargetUser][$DefenderTarget]){
                $Destroyed = $AttackerShips[$DefenderTargetUser][$DefenderTarget];
                $SaveHullDmg['atk'][$DefenderTargetUser][$DefenderTarget] = 0;
                $DefenderDestroyedTarget = true;
            } else {
                $HowManyCouldDestroyReal = floor($HowManyCouldDestroy);
                $ChanceToBlow = $HowManyCouldDestroy - $HowManyCouldDestroyReal;

                if($ChanceToBlow < 1){
                    $ClearForce = true;
                }

                if($HowManyCouldDestroy < 1 AND $SaveHullDmg['atk'][$DefenderTargetUser][$DefenderTarget] > 0){
                    $ChanceToBlow += $SaveHullDmg['atk'][$DefenderTargetUser][$DefenderTarget];
                }
                if($ChanceToBlow > 0.3){
                    if($ChanceToBlow >= 1 OR mt_rand(1,100) <= ($ChanceToBlow * 100)){
                        $HowManyCouldDestroyReal += 1;
                        $SaveHullDmg['atk'][$DefenderTargetUser][$DefenderTarget] = 0;
                        $AtkShipDestroyedInExplosion = true;
                    } else {
                        $SaveHullDmg['atk'][$DefenderTargetUser][$DefenderTarget] = $ChanceToBlow;
                    }
                    $DoNotCalcExplosion['atk'][$DefenderTargetUser][$DefenderTarget] = 1;
                } else {
                    $SaveHullDmg['atk'][$DefenderTargetUser][$DefenderTarget] = $ChanceToBlow;
                }
                if($HowManyCouldDestroyReal >= $AttackerShips[$DefenderTargetUser][$DefenderTarget]){
                    $Destroyed = $AttackerShips[$DefenderTargetUser][$DefenderTarget];
                    $DefenderDestroyedTarget = true;
                } else {
                    $Destroyed = $HowManyCouldDestroyReal;
                }
            }
            $AttackerLost[$DefenderTargetUser][$DefenderTarget] += $Destroyed;
            $AtkLoseCount[$DefenderTarget] += $Destroyed;
            if($ClearForce){
                if($HowManyCouldDestroyReal <= $Destroyed){
                    $DefenderForce = 0;
                }
            } else {
                $DefenderForce -= ($ShipsHullValues[$DefenderTarget] * $AttackerTech[$DefenderTargetUser]['tech_armour']) * $Destroyed;
            }
            if($DefenderDestroyedTarget){
                unset($AttackerShipList[$DefenderTargetUser][$DefenderTargetIndex]);
                if(empty($AttackerShipList[$DefenderTargetUser])){
                    unset($AttackerShipList[$DefenderTargetUser]);
                    unset($AttackerUsers[$DefenderTargetUser]);
                }
                if(empty($AttackerShipList)){
                    $CalcDefRapidFire = false;
                    break;
                }
                if($DefenderForce > 0 OR ($UseRapidFire == TRUE AND $CalcDefRapidFire == TRUE)){
                    $DefenderTargetUser     = array_rand($AttackerUsers);
                    $DefenderTarget         = array_rand($AttackerShipList[$DefenderTargetUser]);
                    $DefenderTargetIndex    = $DefenderTarget;
                    $DefenderTarget         = $AttackerShipList[$DefenderTargetUser][$DefenderTarget];
                    $AttackerShield         = -1;
                }
            }
        }


        // Calculate Rapid Fire
        if($UseRapidFire){
            if($CalcDefRapidFire){
                $RapidFireMaxShips = 0;
                foreach($DefenderShips as $User => $Ships){
                    foreach($Ships as $ID => $Count){
                        if($_Vars_CombatData[$ID]['sd'][$DefenderTarget] > 1){
                            $Chance = floor((($_Vars_CombatData[$ID]['sd'][$DefenderTarget] - 1) / $_Vars_CombatData[$ID]['sd'][$DefenderTarget]) * 100);
                            for($SDCounter = 0; $SDCounter < $Count; $SDCounter += 1){
                                while(mt_rand(1,100) <= $Chance){
                                    $CalcForce = $_Vars_CombatData[$ID]['attack'] * $DefenderTech[$DefenderTargetUser]['tech_weapons'];
                                    $DefenderForce += $CalcForce;
                                    $Rounds[$i]['def']['count'] += 1;
                                    $Rounds[$i]['def']['force'] += $CalcForce;
                                    $RapidFireMaxShips += 1;
                                }
                            }
                        }
                    }
                }

                if($DefenderForce > 0){
                    $Destroyed = 0;
                    $DefenderDestroyedTarget = false;

                    $AttackerShipsLeft = $AttackerShips[$DefenderTargetUser][$DefenderTarget] - $AttackerLost[$DefenderTargetUser][$DefenderTarget];

                    if($AttackerShield < 0){
                        $AttackerShield = $AttackerShipsLeft * $_Vars_CombatData[$DefenderTarget]['shield'] * $AttackerTech[$DefenderTargetUser]['tech_shielding'];
                        if($AttackerShield > $DefenderForce){
                            $Rounds[$i]['atk']['shield'] += $DefenderForce;
                        } else {
                            $Rounds[$i]['atk']['shield'] += $AttackerShield;
                        }
                        $DefenderForce -= $AttackerShield;
                    } elseif($AttackerShield > 0){
                        if($AttackerShield > $DefenderForce){
                            $Rounds[$i]['atk']['shield'] += $DefenderForce;
                        } else {
                            $Rounds[$i]['atk']['shield'] += $AttackerShield;
                        }
                        $DefenderForce -= $AttackerShield;
                    }

                    $HowManyCouldDestroy = $DefenderForce / ($ShipsHullValues[$DefenderTarget] * $AttackerTech[$DefenderTargetUser]['tech_armour']);
                    if($HowManyCouldDestroy > $RapidFireMaxShips){
                        $HowManyCouldDestroy = $RapidFireMaxShips;
                    }

                    if($HowManyCouldDestroy >= $AttackerShipsLeft){
                        $Destroyed = $AttackerShipsLeft;
                        $SaveHullDmg['def'][$DefenderTargetUser][$DefenderTarget] = 0;
                        $DefenderDestroyedTarget = true;
                    } else {
                        $HowManyCouldDestroyReal = floor($HowManyCouldDestroy);
                        $ChanceToBlow = $HowManyCouldDestroy - $HowManyCouldDestroyReal;
                        if($HowManyCouldDestroy < 1 AND $SaveHullDmg['atk'][$DefenderTargetUser][$DefenderTarget] > 0){
                            $ChanceToBlow += $SaveHullDmg['atk'][$DefenderTargetUser][$DefenderTarget];
                        }
                        if($ChanceToBlow > 0.3){
                            if($ChanceToBlow >= 1 OR mt_rand(1,100) <= ($ChanceToBlow * 100)){
                                $HowManyCouldDestroyReal += 1;
                                $SaveHullDmg['atk'][$DefenderTargetUser][$DefenderTarget] = 0;
                            } else {
                                $SaveHullDmg['atk'][$DefenderTargetUser][$DefenderTarget] = $ChanceToBlow;
                            }
                            $DoNotCalcExplosion['atk'][$DefenderTargetUser][$DefenderTarget] = 1;
                        } else {
                            $SaveHullDmg['atk'][$DefenderTargetUser][$DefenderTarget] = $ChanceToBlow;
                        }
                        if($HowManyCouldDestroyReal >= $AttackerShipsLeft){
                            $Destroyed = $AttackerShipsLeft;
                            $DefenderDestroyedTarget = true;
                        } else {
                            $Destroyed = $HowManyCouldDestroyReal;
                        }
                        $ClearForce = true;
                    }
                    $AttackerLost[$DefenderTargetUser][$DefenderTarget] += $Destroyed;
                    $AtkLoseCount[$DefenderTarget] += $Destroyed;
                    if($DefenderDestroyedTarget){
                        unset($AttackerShipList[$DefenderTargetUser][$DefenderTargetIndex]);
                        if(empty($AttackerShipList[$DefenderTargetUser])){
                            unset($AttackerShipList[$DefenderTargetUser]);
                            unset($AttackerUsers[$DefenderTargetUser]);
                        }
                    }
                }
            }
        }

        // Calculate ships Explosion (Atk Ships)
        if(!empty($SaveHullDmg['atk'])){
            foreach($SaveHullDmg['atk'] as $User => $Ships){
                foreach($Ships as $Ship => $ExplosionChance){
                    if($DoNotCalcExplosion['atk'][$User][$Ship] != 1){
                        if($ExplosionChance > 0.3){
                            if($ExplosionChance >= 1 OR mt_rand(1,100) <= ($ExplosionChance * 100)){
                                $SaveHullDmg['atk'][$User][$Ship] = 0;
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

        // End of Calculations
        // --------------------------------------------

        if(!empty($AttackerLost)){
            foreach($AttackerLost as $User => $Ships){
                foreach($Ships as $ID => $Count){
                    if($Count > 0){
                        if($AttackerShips[$User][$ID] <= $Count){
                            unset($AttackerShips[$User][$ID]);
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
