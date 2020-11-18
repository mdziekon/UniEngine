<?php

/**
 * A.R.E.S Combat Engine
 *
 * @author      mdziekon
 * @version     1.2.0.1
 * @build       41
 * @status      Release
 * @copyright   2011 - 2012 by mdziekon [for UniEngine]
 *
 */

include($_EnginePath . 'includes/ares/initializers.php');
include($_EnginePath . 'includes/ares/calculations.php');
include($_EnginePath . 'includes/ares/distributions.php');
include($_EnginePath . 'includes/ares/evaluators.php');

use UniEngine\Engine\Includes\Ares;

function Combat($Attacker, $Defender, $AttackerTech, $DefenderTech, $UseRapidFire = true) {
    $Rounds = array();
    $AtkLoseCount = array();
    $DefLoseCount = array();
    $PlanetDefSysLost = array();

    $ShipsSD = [
        'a' => [],
        'd' => [],
    ];

    if(!empty($Defender))
    {
        foreach ($DefenderTech as $User => &$Techs) {
            Ares\Initializers\initializeUserTechs($Techs);
        }

        foreach($Defender as $User => $Ships)
        {
            $UserKey = "|{$User}";
            foreach($Ships as $ID => $Count)
            {
                $UserShipKey = "{$ID}{$UserKey}";

                if ($UseRapidFire) {
                    if (empty($ShipsSD['d'][$User])) {
                        $ShipsSD['d'][$User] = [];
                    }

                    Ares\Initializers\initializeShipRapidFire([
                        'rapidFireTableRef' => &$ShipsSD['d'][$User],
                        'userTechs' => &$DefenderTech[$User],
                        'shipId' => $ID,
                    ]);
                }

                $DefenderShips[$User][$ID] = $Count;
                if(!isset($DefShipsTypes[$ID]))
                {
                    $DefShipsTypes[$ID] = 0;
                }
                $DefShipsTypes[$ID] += 1;
                $DefShipsTypesOwners[$ID][$User] = 1;
                $DefShipsTypesCount[$ID][$User] = $Count;

                $DefShipsForce[$UserShipKey] = Ares\Calculations\calculateShipForce([
                    'shipId' => $ID,
                    'userTechs' => &$DefenderTech[$User],
                ]);
                $DefShipsShield[$UserShipKey] = Ares\Calculations\calculateShipShield([
                    'shipId' => $ID,
                    'userTechs' => &$DefenderTech[$User],
                ]);
                $DefShipsHull[$UserShipKey] = Ares\Calculations\calculateShipHull([
                    'shipId' => $ID,
                    'userTechs' => &$DefenderTech[$User],
                ]);
            }
        }
        asort($DefShipsForce);
    }
    else
    {
        $DefenderShips = false;
    }

    if(!empty($Attacker))
    {
        foreach ($AttackerTech as $User => &$Techs) {
            Ares\Initializers\initializeUserTechs($Techs);
        }

        foreach($Attacker as $User => $Ships)
        {
            $UserKey = "|{$User}";
            foreach($Ships as $ID => $Count)
            {
                $UserShipKey = "{$ID}{$UserKey}";

                if ($UseRapidFire) {
                    if (empty($ShipsSD['a'][$User])) {
                        $ShipsSD['a'][$User] = [];
                    }

                    Ares\Initializers\initializeShipRapidFire([
                        'rapidFireTableRef' => &$ShipsSD['a'][$User],
                        'userTechs' => &$AttackerTech[$User],
                        'shipId' => $ID,
                    ]);
                }

                $AttackerShips[$User][$ID] = $Count;
                if(!isset($AtkShipsTypes[$ID]))
                {
                    $AtkShipsTypes[$ID] = 0;
                }
                $AtkShipsTypes[$ID] += 1;
                $AtkShipsTypesOwners[$ID][$User] = 1;
                $AtkShipsTypesCount[$ID][$User] = $Count;

                $AtkShipsForce[$UserShipKey] = Ares\Calculations\calculateShipForce([
                    'shipId' => $ID,
                    'userTechs' => &$AttackerTech[$User],
                ]);
                $AtkShipsShield[$UserShipKey] = Ares\Calculations\calculateShipShield([
                    'shipId' => $ID,
                    'userTechs' => &$AttackerTech[$User],
                ]);
                $AtkShipsHull[$UserShipKey] = Ares\Calculations\calculateShipHull([
                    'shipId' => $ID,
                    'userTechs' => &$AttackerTech[$User],
                ]);
            }
        }
        $AtkShipsForce_Copy = $AtkShipsForce;
        asort($AtkShipsForce);
        asort($AtkShipsForce_Copy);
    }
    else
    {
        return array('result' => false, 'error' => 'NO_ATTACKER');
    }

    $RoundsLimit = BATTLE_MAX_ROUNDS + 1;
    for($i = 1; $i <= $RoundsLimit; $i += 1)
    {
        // Clear Lost Ships in last round
        $AtkLost = array();
        $DefLost = array();
        // Clear Targets Shield Supply
        $DefShields = false;
        $AtkShields = false;
        // Clear Already destroyed targets
        $AlreadyDestroyedDef = false;
        $AlreadyDestroyedAtk = false;
        // Clear RapidFire Exclusions
        $AtkDontShootRF = false;
        $DefDontShootRF = false;
        // Clear TotalACS Force
        $TotalDefTypesForce = false;
        $TotalAtkTypesForce = false;
        // END of Clearing

        $Rounds[$i]['atk']['ships'] = (isset($AttackerShips) ? $AttackerShips : null);
        $Rounds[$i]['def']['ships'] = (isset($DefenderShips) ? $DefenderShips : null);
        $Rounds[$i]['atk']['force'] = 0;
        $Rounds[$i]['atk']['count'] = 0;
        $Rounds[$i]['atk']['shield'] = 0;
        $Rounds[$i]['def']['force'] = 0;
        $Rounds[$i]['def']['count'] = 0;
        $Rounds[$i]['def']['shield'] = 0;

        if($i > BATTLE_MAX_ROUNDS)
        {
            break;
        }

        if(empty($AttackerShips) OR empty($DefenderShips))
        {
            break;
        }

        // Prepare target list
        // -------------------
        // Defender Targets
        $DefShipsForce_Copy = $DefShipsForce;
        foreach($DefShipsForce_Copy as $TempKey => &$TempForce)
        {
            $TempForce += $DefShipsShield[$TempKey];
            $TempKey = explode('|', $TempKey);
            $TempForce *= $DefShipsTypesCount[$TempKey[0]][$TempKey[1]];
            if(!isset($TotalDefTypesForce[$TempKey[0]]))
            {
                $TotalDefTypesForce[$TempKey[0]] = 0;
            }
            $TotalDefTypesForce[$TempKey[0]] += $TempForce;
        }
        foreach($DefShipsForce_Copy as $TempKey => &$TempForce)
        {
            $TempKey = explode('|', $TempKey);
            if($TempForce < $TotalDefTypesForce[$TempKey[0]])
            {
                $TempForce = $TotalDefTypesForce[$TempKey[0]];
            }
        }
        asort($DefShipsForce_Copy);
        // Attacker Targets
        $AtkShipsForce_Copy = $AtkShipsForce;
        foreach($AtkShipsForce_Copy as $TempKey => &$TempForce)
        {
            $TempForce += $AtkShipsShield[$TempKey];
            $TempKey = explode('|', $TempKey);
            $TempForce *= $AtkShipsTypesCount[$TempKey[0]][$TempKey[1]];
            if(!isset($TotalAtkTypesForce[$TempKey[0]]))
            {
                $TotalAtkTypesForce[$TempKey[0]] = 0;
            }
            $TotalAtkTypesForce[$TempKey[0]] += $TempForce;
        }
        foreach($AtkShipsForce_Copy as $TempKey => &$TempForce)
        {
            $TempKey = explode('|', $TempKey);
            if($TempForce < $TotalAtkTypesForce[$TempKey[0]])
            {
                $TempForce = $TotalAtkTypesForce[$TempKey[0]];
            }
        }
        asort($AtkShipsForce_Copy);

        // ------------------------------------------------------------------------------------------------------------------------------------
        // Calculate Attacker(s) Part
        // 1. Let's calculate all regular fires!

        foreach($AtkShipsForce as $AKey => $AForce)
        {
            if($AForce == 0)
            {
                continue; // Jump out if this Ship is useless (like Solar Satelite or Spy Probe)
            }
            $Temp = explode('|', $AKey);
            $AUser = $Temp[1];
            $AShip = $Temp[0];
            $ACount_Copy = $AtkShipsTypesCount[$AShip][$AUser];

            $shotsDistribution = [
                'calculatedForTypeId' => [],
                'distributionByTargetFullKey' => [],
            ];

            // -----------------------
            // Calculate Regular Fire!
            foreach($DefShipsForce_Copy as $TKey => $TForce)
            {
                $Temp = explode('|', $TKey);
                $TShip = $Temp[0];
                $TUser = $Temp[1];

                // When there is more than one defender with the same type of ship,
                // try to distribute the shots proportionally.
                if (
                    $DefShipsTypes[$TShip] > 1 &&
                    !isset($shotsDistribution['calculatedForTypeId'][$TShip])
                ) {
                    $thisTargetShotsDistribution = Ares\Distributions\distributeShots([
                        'targetShipId' => $TShip,
                        'targetShipsOwners' => $DefShipsTypesOwners,
                        'targetInitialShipsTable' => $DefShipsTypesCount,
                        'targetAlreadyDestroyedShipsTable' => $AlreadyDestroyedDef,
                        'shotsCount' => $ACount_Copy,
                    ]);

                    $shotsDistribution['distributionByTargetFullKey'] = array_merge(
                        $shotsDistribution['distributionByTargetFullKey'],
                        $thisTargetShotsDistribution
                    );

                    $shotsDistribution['calculatedForTypeId'][$TShip] = true;
                }

                if ($DefShipsTypes[$TShip] > 1) {
                    $ACount = $shotsDistribution['distributionByTargetFullKey'][$TKey];
                } else {
                    $ACount = $ACount_Copy;
                }

                if ($ACount == 0) {
                    continue;
                }

                if (
                    Ares\Evaluators\isShieldImpenetrable([
                        'shotForce' => $AForce,
                        'targetShipShield' => $DefShipsShield[$TKey],
                    ])
                ) {
                    $UsedForce = $AForce * $ACount;
                    $Rounds[$i]['atk']['force'] += $UsedForce;
                    $Rounds[$i]['atk']['count'] += $ACount;
                    $Rounds[$i]['def']['shield'] += $UsedForce;
                    $ACount_Copy -= $ACount;

                    continue;
                }

                $AvailableForce = $AForce * $ACount;
                $Force2TDShield = 0;

                $isShotBypassingShield = Ares\Evaluators\isShotBypassingShield([
                    'shotForce' => $AForce,
                    'targetShipShield' => $DefShipsShield[$TKey],
                ]);

                if (!$isShotBypassingShield) {
                    if (
                        isset($DefShields[$TKey]['left']) &&
                        $DefShields[$TKey]['left'] === true
                    ) {
                        $Force2TDShield = $DefShields[$TKey]['shield'];
                    } else {
                        $Force2TDShield = $DefShipsShield[$TKey] * $DefShipsTypesCount[$TShip][$TUser];
                    }
                }

                if ($AvailableForce <= $Force2TDShield) {
                    $Rounds[$i]['atk']['force'] += $AvailableForce;
                    $Rounds[$i]['atk']['count'] += $ACount;
                    $Rounds[$i]['def']['shield'] += $AvailableForce;
                    $DefShields[$TKey] = array('left' => true, 'shield' => $Force2TDShield - $AvailableForce);
                    $ACount_Copy -= $ACount;

                    continue;
                }

                if (!$isShotBypassingShield) {
                    $DefShields[$TKey] = array('left' => true, 'shield' => 0);
                }

                $LeftForce = $AvailableForce - $Force2TDShield;

                $Able2Destroy = (
                    $DefShipsTypesCount[$TShip][$TUser] -
                    (isset($AlreadyDestroyedDef[$TKey]) ? $AlreadyDestroyedDef[$TKey] : 0)
                );

                if ($ACount < $Able2Destroy) {
                    $Able2Destroy = $ACount;
                }

                $NeedForce = ($DefShipsHull[$TKey] * $Able2Destroy);
                if(isset($DefHullDmg[$TKey]))
                {
                    $NeedForce -= ($DefHullDmg[$TKey] * $DefShipsHull[$TKey]);
                }
                if($NeedForce > $LeftForce)
                {
                    $UsedForce = $LeftForce + $Force2TDShield;
                    $Shoots = $UsedForce / $AForce;
                    $DestroyedOrg = ($LeftForce / $DefShipsHull[$TKey]);
                    if(isset($DefHullDmg[$TKey]))
                    {
                        $DestroyedOrg += $DefHullDmg[$TKey];
                    }
                    $Destroyed = floor($LeftForce / $DefShipsHull[$TKey]);
                    $Difference = $DestroyedOrg - $Destroyed;
                    $DefHullDmg[$TKey] = $Difference;
                    if($DefHullDmg[$TKey] >= 1)
                    {
                        $Destroyed += 1;
                        $DefHullDmg[$TKey] -= 1;
                    }
                }
                else
                {
                    $UsedForce = $NeedForce + $Force2TDShield;
                    $Shoots = ceil($UsedForce / $AForce);
                    if($Shoots < $Able2Destroy)
                    {
                        $Shoots = $Able2Destroy;
                    }
                    $Destroyed = $Able2Destroy;
                }
                $Rounds[$i]['atk']['force'] += $UsedForce;
                $Rounds[$i]['atk']['count'] += $Shoots;
                $Rounds[$i]['def']['shield'] += $Force2TDShield;

                if(!isset($DefLost[$TShip][$TUser]))
                {
                    $DefLost[$TShip][$TUser] = 0;
                }
                if(!isset($ForceContribution['atk'][$AUser]))
                {
                    $ForceContribution['atk'][$AUser] = 0;
                }
                if(!isset($ShotDown['atk']['d'][$AUser][$TShip]))
                {
                    $ShotDown['atk']['d'][$AUser][$TShip] = 0;
                }
                if(!isset($ShotDown['def']['l'][$TUser][$TShip]))
                {
                    $ShotDown['def']['l'][$TUser][$TShip] = 0;
                }

                $DefLost[$TShip][$TUser] += $Destroyed;
                $ForceContribution['atk'][$AUser] += $UsedForce;
                $ShotDown['atk']['d'][$AUser][$TShip] += $Destroyed;
                $ShotDown['def']['l'][$TUser][$TShip] += $Destroyed;
                if($Destroyed == ($DefShipsTypesCount[$TShip][$TUser] - (isset($AlreadyDestroyedDef[$TKey]) ? $AlreadyDestroyedDef[$TKey] : 0)))
                {
                    unset($DefShipsForce_Copy[$TKey]);
                    if(isset($DefHullDmg[$TKey]))
                    {
                        unset($DefHullDmg[$TKey]);
                    }
                    unset($DefShipsTypesOwners[$TShip][$TUser]);
                    $DefShipsTypes[$TShip] -= 1;
                }
                else
                {
                    if($Destroyed > 0)
                    {
                        if(!isset($AlreadyDestroyedDef[$TKey]))
                        {
                            $AlreadyDestroyedDef[$TKey] = 0;
                        }
                        $AlreadyDestroyedDef[$TKey] += $Destroyed;
                    }
                }
                $ACount_Copy -= $Shoots;
            }

            // ---------------------
            // Calculate Rapid Fire!
            if (!$UseRapidFire) {
                continue;
            }
            if (empty($ShipsSD['a'][$AUser][$AShip])) {
                continue;
            }

            $NoMoreRapidFire = false;

            foreach ($ShipsSD['a'][$AUser][$AShip] as $TShip => $TSDVal) {
                if ($NoMoreRapidFire) {
                    break;
                }

                if (
                    !isset($DefShipsTypes[$TShip]) ||
                    $DefShipsTypes[$TShip] <= 0
                ) {
                    continue;
                }

                $TotalForceNeed = 0;
                $TotalShootsNeed = 0;
                $GainedShoots = 0;
                $RapidForce4Shield = false;
                $RapidForce4Hull = false;
                $RapidForceMinShoots = false;

                $shotsDistribution = [
                    'calculatedForTypeId' => [],
                    'distributionByTargetFullKey' => [],
                ];

                foreach($DefShipsTypesOwners[$TShip] as $Owner => $NotImportant)
                {
                    $ThisKey = "{$TShip}|{$Owner}";
                    $CalcCount = ($DefShipsTypesCount[$TShip][$Owner] - (isset($AlreadyDestroyedDef[$ThisKey]) ? $AlreadyDestroyedDef[$ThisKey] : 0));

                    if(isset($DefShields[$ThisKey]['left']) && $DefShields[$ThisKey]['left'] === true)
                    {
                        $Force2TDShield = $DefShields[$ThisKey]['shield'];
                    }
                    else
                    {
                        $Force2TDShield = $DefShipsShield[$ThisKey] * $CalcCount;
                    }
                    $RapidForce4Shield[$Owner] = $Force2TDShield;
                    $RapidForce4Hull[$Owner] = $CalcCount * $DefShipsHull[$ThisKey];
                    if(isset($DefHullDmg[$ThisKey]))
                    {
                        $RapidForce4Hull[$Owner] -= $DefHullDmg[$ThisKey] * $DefShipsHull[$ThisKey];
                    }
                    $RapidForceMinShoots[$Owner] = $CalcCount;

                    $TotalForceNeed += ($RapidForce4Shield[$Owner] + $RapidForce4Hull[$Owner]);
                    $TotalShootsNeed += $RapidForceMinShoots[$Owner];
                }

                $TotalAvailableShoots = floor(($AtkShipsTypesCount[$AShip][$AUser] * $ShipsSD['a'][$AUser][$AShip][$TShip]) * (1 - (isset($AtkDontShootRF[$AKey]) ? $AtkDontShootRF[$AKey] : 0)));
                $TotalAvailableForce = $TotalAvailableShoots * $AForce;

                if($TotalAvailableShoots > $TotalShootsNeed)
                {
                    if($TotalAvailableForce > $TotalForceNeed)
                    {
                        $GainedShoots = ceil($TotalForceNeed / $AForce);
                        if($GainedShoots < $TotalShootsNeed)
                        {
                            $GainedShoots = $TotalShootsNeed;
                        }
                        if($GainedShoots == $TotalAvailableShoots)
                        {
                            $NoMoreRapidFire = true;
                        }
                        else
                        {
                            $TotalEverAvailableShoots = $AtkShipsTypesCount[$AShip][$AUser] * $ShipsSD['a'][$AUser][$AShip][$TShip];
                            if(!isset($AtkDontShootRF[$AKey]))
                            {
                                $AtkDontShootRF[$AKey] = 0;
                            }
                            $AtkDontShootRF[$AKey] += $GainedShoots / $TotalEverAvailableShoots;
                        }
                    }
                    else
                    {
                        $GainedShoots = $TotalAvailableShoots;
                        $NoMoreRapidFire = true;
                    }
                }
                else
                {
                    $GainedShoots = $TotalAvailableShoots;
                    $NoMoreRapidFire = true;
                }

                if ($GainedShoots <= 0) {
                    continue;
                }

                if ($DefShipsTypes[$TShip] > 1) {
                    $thisTargetShotsDistribution = Ares\Distributions\distributeShots([
                        'targetShipId' => $TShip,
                        'targetShipsOwners' => $DefShipsTypesOwners,
                        'targetInitialShipsTable' => $DefShipsTypesCount,
                        'targetAlreadyDestroyedShipsTable' => $AlreadyDestroyedDef,
                        'shotsCount' => $GainedShoots,
                    ]);

                    $shotsDistribution['distributionByTargetFullKey'] = array_merge(
                        $shotsDistribution['distributionByTargetFullKey'],
                        $thisTargetShotsDistribution
                    );
                }

                foreach($DefShipsTypesOwners[$TShip] as $Owner => $NotImportant)
                {
                    $TKey = "{$TShip}|{$Owner}";
                    $TUser = $Owner;

                    if ($DefShipsTypes[$TShip] > 1) {
                        $ACount = $shotsDistribution['distributionByTargetFullKey'][$TKey];
                    } else {
                        $ACount = $GainedShoots;
                    }

                    if ($ACount == 0) {
                        continue;
                    }

                    if (
                        Ares\Evaluators\isShieldImpenetrable([
                            'shotForce' => $AForce,
                            'targetShipShield' => $DefShipsShield[$TKey],
                        ])
                    ) {
                        $UsedForce = $AForce * $ACount;
                        $Rounds[$i]['atk']['force'] += $UsedForce;
                        $Rounds[$i]['atk']['count'] += $ACount;
                        $Rounds[$i]['def']['shield'] += $UsedForce;

                        continue;
                    }

                    $AvailableForce = $AForce * $ACount;
                    $Force2TDShield = 0;

                    $isShotBypassingShield = Ares\Evaluators\isShotBypassingShield([
                        'shotForce' => $AForce,
                        'targetShipShield' => $DefShipsShield[$TKey],
                    ]);

                    if (!$isShotBypassingShield) {
                        if (
                            isset($DefShields[$TKey]['left']) &&
                            $DefShields[$TKey]['left'] === true
                        ) {
                            $Force2TDShield = $DefShields[$TKey]['shield'];
                        } else {
                            $Force2TDShield = $DefShipsShield[$TKey] * $DefShipsTypesCount[$TShip][$TUser];
                        }
                    }

                    if ($AvailableForce <= $Force2TDShield) {
                        $Rounds[$i]['atk']['force'] += $AvailableForce;
                        $Rounds[$i]['atk']['count'] += $ACount;
                        $Rounds[$i]['def']['shield'] += $AvailableForce;
                        $DefShields[$TKey] = array('left' => true, 'shield' => $Force2TDShield - $AvailableForce);

                        continue;
                    }

                    if (!$isShotBypassingShield) {
                        $DefShields[$TKey] = array('left' => true, 'shield' => 0);
                    }

                    $LeftForce = $AvailableForce - $Force2TDShield;

                    $Able2Destroy = (
                        $DefShipsTypesCount[$TShip][$TUser] -
                        (isset($AlreadyDestroyedDef[$TKey]) ? $AlreadyDestroyedDef[$TKey] : 0)
                    );

                    if ($ACount < $Able2Destroy) {
                        $Able2Destroy = $ACount;
                    }

                    $NeedForce = ($DefShipsHull[$TKey] * $Able2Destroy);
                    if(isset($DefHullDmg[$TKey]))
                    {
                        $NeedForce -= ($DefHullDmg[$TKey] * $DefShipsHull[$TKey]);
                    }
                    if($NeedForce > $LeftForce)
                    {
                        $UsedForce = $LeftForce + $Force2TDShield;
                        $Shoots = $UsedForce / $AForce;
                        $DestroyedOrg = ($LeftForce / $DefShipsHull[$TKey]);
                        if(isset($DefHullDmg[$TKey]))
                        {
                            $DestroyedOrg += $DefHullDmg[$TKey];
                        }
                        $Destroyed = floor($LeftForce / $DefShipsHull[$TKey]);
                        $Difference = $DestroyedOrg - $Destroyed;
                        $DefHullDmg[$TKey] = $Difference;
                        if($DefHullDmg[$TKey] >= 1)
                        {
                            $Destroyed += 1;
                            $DefHullDmg[$TKey] -= 1;
                        }
                    }
                    else
                    {
                        $UsedForce = $NeedForce + $Force2TDShield;
                        $Shoots = ceil($UsedForce / $AForce);
                        if($Shoots < $Able2Destroy)
                        {
                            $Shoots = $Able2Destroy;
                        }
                        $Destroyed = $Able2Destroy;
                    }
                    $Rounds[$i]['atk']['force'] += $UsedForce;
                    $Rounds[$i]['atk']['count'] += $Shoots;
                    $Rounds[$i]['def']['shield'] += $Force2TDShield;

                    if(!isset($DefLost[$TShip][$TUser]))
                    {
                        $DefLost[$TShip][$TUser] = 0;
                    }
                    if(!isset($ForceContribution['atk'][$AUser]))
                    {
                        $ForceContribution['atk'][$AUser] = 0;
                    }
                    if(!isset($ShotDown['atk']['d'][$AUser][$TShip]))
                    {
                        $ShotDown['atk']['d'][$AUser][$TShip] = 0;
                    }
                    if(!isset($ShotDown['def']['l'][$TUser][$TShip]))
                    {
                        $ShotDown['def']['l'][$TUser][$TShip] = 0;
                    }

                    $DefLost[$TShip][$TUser] += $Destroyed;
                    $ForceContribution['atk'][$AUser] += $UsedForce;
                    $ShotDown['atk']['d'][$AUser][$TShip] += $Destroyed;
                    $ShotDown['def']['l'][$TUser][$TShip] += $Destroyed;
                    if($Destroyed == ($DefShipsTypesCount[$TShip][$TUser] - (isset($AlreadyDestroyedDef[$TKey]) ? $AlreadyDestroyedDef[$TKey] : 0)))
                    {
                        unset($DefShipsForce_Copy[$TKey]);
                        if(isset($DefHullDmg[$TKey]))
                        {
                            unset($DefHullDmg[$TKey]);
                        }
                        unset($DefShipsTypesOwners[$TShip][$TUser]);
                        $DefShipsTypes[$TShip] -= 1;
                    }
                    else
                    {
                        if($Destroyed > 0)
                        {
                            if(!isset($AlreadyDestroyedDef[$TKey]))
                            {
                                $AlreadyDestroyedDef[$TKey] = 0;
                            }
                            $AlreadyDestroyedDef[$TKey] += $Destroyed;
                        }
                    }
                }
            }
        }

        // ------------------------------------------------------------------------------------------------------------------------------------

        // Calculate Defender(s) Part
        // 1. Let's calculate all regular fires!

        foreach($DefShipsForce as $AKey => $AForce)
        {
            if($AForce == 0)
            {
                continue; // Jump out if this Ship is useless (like Solar Satelite or Spy Probe)
            }
            $Temp = explode('|', $AKey);
            $AUser = $Temp[1];
            $AShip = $Temp[0];
            $ACount_Copy = $DefShipsTypesCount[$AShip][$AUser];

            $shotsDistribution = [
                'calculatedForTypeId' => [],
                'distributionByTargetFullKey' => [],
            ];

            // -----------------------
            // Calculate Regular Fire!
            foreach($AtkShipsForce_Copy as $TKey => $TForce)
            {
                $Temp = explode('|', $TKey);
                $TShip = $Temp[0];
                $TUser = $Temp[1];

                // When there is more than one defender with the same type of ship,
                // try to distribute the shots proportionally.
                if (
                    $AtkShipsTypes[$TShip] > 1 &&
                    !isset($shotsDistribution['calculatedForTypeId'][$TShip])
                ) {
                    $thisTargetShotsDistribution = Ares\Distributions\distributeShots([
                        'targetShipId' => $TShip,
                        'targetShipsOwners' => $AtkShipsTypesOwners,
                        'targetInitialShipsTable' => $AtkShipsTypesCount,
                        'targetAlreadyDestroyedShipsTable' => $AlreadyDestroyedAtk,
                        'shotsCount' => $ACount_Copy,
                    ]);

                    $shotsDistribution['distributionByTargetFullKey'] = array_merge(
                        $shotsDistribution['distributionByTargetFullKey'],
                        $thisTargetShotsDistribution
                    );

                    $shotsDistribution['calculatedForTypeId'][$TShip] = true;
                }

                if ($AtkShipsTypes[$TShip] > 1) {
                    $ACount = $shotsDistribution['distributionByTargetFullKey'][$TKey];
                } else {
                    $ACount = $ACount_Copy;
                }

                if ($ACount == 0) {
                    continue;
                }

                if (
                    Ares\Evaluators\isShieldImpenetrable([
                        'shotForce' => $AForce,
                        'targetShipShield' => $AtkShipsShield[$TKey],
                    ])
                ) {
                    $UsedForce = $AForce * $ACount;
                    $Rounds[$i]['def']['force'] += $UsedForce;
                    $Rounds[$i]['def']['count'] += $ACount;
                    $Rounds[$i]['atk']['shield'] += $UsedForce;
                    $ACount_Copy -= $ACount;

                    continue;
                }

                $AvailableForce = $AForce * $ACount;
                $Force2TDShield = 0;

                $isShotBypassingShield = Ares\Evaluators\isShotBypassingShield([
                    'shotForce' => $AForce,
                    'targetShipShield' => $AtkShipsShield[$TKey],
                ]);

                if (!$isShotBypassingShield) {
                    if (
                        isset($AtkShields[$TKey]['left']) &&
                        $AtkShields[$TKey]['left'] === true
                    ) {
                        $Force2TDShield = $AtkShields[$TKey]['shield'];
                    } else {
                        $Force2TDShield = $AtkShipsShield[$TKey] * $AtkShipsTypesCount[$TShip][$TUser];
                    }
                }

                if ($AvailableForce <= $Force2TDShield) {
                    $Rounds[$i]['def']['force'] += $AvailableForce;
                    $Rounds[$i]['def']['count'] += $ACount;
                    $Rounds[$i]['atk']['shield'] += $AvailableForce;
                    $AtkShields[$TKey] = array('left' => true, 'shield' => $Force2TDShield - $AvailableForce);
                    $ACount_Copy -= $ACount;

                    continue;
                }

                if (!$isShotBypassingShield) {
                    $AtkShields[$TKey] = array('left' => true, 'shield' => 0);
                }

                $LeftForce = $AvailableForce - $Force2TDShield;

                $Able2Destroy = (
                    $AtkShipsTypesCount[$TShip][$TUser] -
                    (isset($AlreadyDestroyedAtk[$TKey]) ? $AlreadyDestroyedAtk[$TKey] : 0)
                );

                if ($ACount < $Able2Destroy) {
                    $Able2Destroy = $ACount;
                }

                $NeedForce = ($AtkShipsHull[$TKey] * $Able2Destroy);
                if(isset($AtkHullDmg[$TKey]))
                {
                    $NeedForce -= ($AtkHullDmg[$TKey] * $AtkShipsHull[$TKey]);
                }
                if($NeedForce > $LeftForce)
                {
                    $UsedForce = $LeftForce + $Force2TDShield;
                    $Shoots = $UsedForce / $AForce;
                    $DestroyedOrg = ($LeftForce / $AtkShipsHull[$TKey]);
                    if(isset($AtkHullDmg[$TKey]))
                    {
                        $DestroyedOrg += $AtkHullDmg[$TKey];
                    }
                    $Destroyed = floor($LeftForce / $AtkShipsHull[$TKey]);
                    $Difference = $DestroyedOrg - $Destroyed;
                    $AtkHullDmg[$TKey] = $Difference;
                    if($AtkHullDmg[$TKey] >= 1)
                    {
                        $Destroyed += 1;
                        $AtkHullDmg[$TKey] -= 1;
                    }
                }
                else
                {
                    $UsedForce = $NeedForce + $Force2TDShield;
                    $Shoots = ceil($UsedForce / $AForce);
                    if($Shoots < $Able2Destroy)
                    {
                        $Shoots = $Able2Destroy;
                    }
                    $Destroyed = $Able2Destroy;
                }
                $Rounds[$i]['def']['force'] += $UsedForce;
                $Rounds[$i]['def']['count'] += $Shoots;
                $Rounds[$i]['atk']['shield'] += $Force2TDShield;

                if(!isset($AtkLost[$TShip][$TUser]))
                {
                    $AtkLost[$TShip][$TUser] = 0;
                }
                if(!isset($ForceContribution['def'][$AUser]))
                {
                    $ForceContribution['def'][$AUser] = 0;
                }
                if(!isset($ShotDown['def']['d'][$AUser][$TShip]))
                {
                    $ShotDown['def']['d'][$AUser][$TShip] = 0;
                }
                if(!isset($ShotDown['atk']['l'][$TUser][$TShip]))
                {
                    $ShotDown['atk']['l'][$TUser][$TShip] = 0;
                }

                $AtkLost[$TShip][$TUser] += $Destroyed;
                $ForceContribution['def'][$AUser] += $UsedForce;
                $ShotDown['def']['d'][$AUser][$TShip] += $Destroyed;
                $ShotDown['atk']['l'][$TUser][$TShip] += $Destroyed;
                if($Destroyed == ($AtkShipsTypesCount[$TShip][$TUser] - (isset($AlreadyDestroyedAtk[$TKey]) ? $AlreadyDestroyedAtk[$TKey] : 0)))
                {
                    unset($AtkShipsForce_Copy[$TKey]);
                    if(isset($AtkHullDmg[$TKey]))
                    {
                        unset($AtkHullDmg[$TKey]);
                    }
                    unset($AtkShipsTypesOwners[$TShip][$TUser]);
                    $AtkShipsTypes[$TShip] -= 1;
                }
                else
                {
                    if($Destroyed > 0)
                    {
                        if(!isset($AlreadyDestroyedAtk[$TKey]))
                        {
                            $AlreadyDestroyedAtk[$TKey] = 0;
                        }
                        $AlreadyDestroyedAtk[$TKey] += $Destroyed;
                    }
                }
                $ACount_Copy -= $Shoots;
            }

            // ---------------------
            // Calculate Rapid Fire!
            if (!$UseRapidFire) {
                continue;
            }
            if (empty($ShipsSD['d'][$AUser][$AShip])) {
                continue;
            }

            $NoMoreRapidFire = false;

            foreach ($ShipsSD['d'][$AUser][$AShip] as $TShip => $TSDVal) {
                if ($NoMoreRapidFire) {
                    break;
                }

                if (
                    !isset($AtkShipsTypes[$TShip]) ||
                    $AtkShipsTypes[$TShip] <= 0
                ) {
                    continue;
                }

                $TotalForceNeed = 0;
                $TotalShootsNeed = 0;
                $GainedShoots = 0;
                $RapidForce4Shield = false;
                $RapidForce4Hull = false;
                $RapidForceMinShoots = false;

                $shotsDistribution = [
                    'calculatedForTypeId' => [],
                    'distributionByTargetFullKey' => [],
                ];

                foreach($AtkShipsTypesOwners[$TShip] as $Owner => $NotImportant)
                {
                    $ThisKey = "{$TShip}|{$Owner}";
                    $CalcCount = ($AtkShipsTypesCount[$TShip][$Owner] - (isset($AlreadyDestroyedAtk[$ThisKey]) ? $AlreadyDestroyedAtk[$ThisKey] : 0));

                    if(isset($AtkShields[$ThisKey]['left']) && $AtkShields[$ThisKey]['left'] === true)
                    {
                        $Force2TDShield = $AtkShields[$ThisKey]['shield'];
                    }
                    else
                    {
                        $Force2TDShield = $AtkShipsShield[$ThisKey] * $CalcCount;
                    }
                    $RapidForce4Shield[$Owner] = $Force2TDShield;
                    $RapidForce4Hull[$Owner] = $CalcCount * $AtkShipsHull[$ThisKey];
                    if(isset($AtkHullDmg[$ThisKey]))
                    {
                        $RapidForce4Hull[$Owner] -= $AtkHullDmg[$ThisKey] * $AtkShipsHull[$ThisKey];
                    }
                    $RapidForceMinShoots[$Owner] = $CalcCount;

                    $TotalForceNeed += ($RapidForce4Shield[$Owner] + $RapidForce4Hull[$Owner]);
                    $TotalShootsNeed += $RapidForceMinShoots[$Owner];
                }

                $TotalAvailableShoots = floor(($DefShipsTypesCount[$AShip][$AUser] * $ShipsSD['d'][$AUser][$AShip][$TShip]) * (1 - (isset($DefDontShootRF[$AKey]) ? $DefDontShootRF[$AKey] : 0)));
                $TotalAvailableForce = $TotalAvailableShoots * $AForce;

                if($TotalAvailableShoots > $TotalShootsNeed)
                {
                    if($TotalAvailableForce > $TotalForceNeed)
                    {
                        $GainedShoots = ceil($TotalForceNeed / $AForce);
                        if($GainedShoots < $TotalShootsNeed)
                        {
                            $GainedShoots = $TotalShootsNeed;
                        }
                        if($GainedShoots == $TotalAvailableShoots)
                        {
                            $NoMoreRapidFire = true;
                        }
                        else
                        {
                            $TotalEverAvailableShoots = $DefShipsTypesCount[$AShip][$AUser] * $ShipsSD['d'][$AUser][$AShip][$TShip];
                            if(!isset($DefDontShootRF[$AKey]))
                            {
                                $DefDontShootRF[$AKey] = 0;
                            }
                            $DefDontShootRF[$AKey] += $GainedShoots / $TotalEverAvailableShoots;
                        }
                    }
                    else
                    {
                        $GainedShoots = $TotalAvailableShoots;
                        $NoMoreRapidFire = true;
                    }
                }
                else
                {
                    $GainedShoots = $TotalAvailableShoots;
                    $NoMoreRapidFire = true;
                }

                if ($GainedShoots <= 0) {
                    continue;
                }

                if ($AtkShipsTypes[$TShip] > 1) {
                    $thisTargetShotsDistribution = Ares\Distributions\distributeShots([
                        'targetShipId' => $TShip,
                        'targetShipsOwners' => $AtkShipsTypesOwners,
                        'targetInitialShipsTable' => $AtkShipsTypesCount,
                        'targetAlreadyDestroyedShipsTable' => $AlreadyDestroyedAtk,
                        'shotsCount' => $GainedShoots,
                    ]);

                    $shotsDistribution['distributionByTargetFullKey'] = array_merge(
                        $shotsDistribution['distributionByTargetFullKey'],
                        $thisTargetShotsDistribution
                    );
                }

                foreach($AtkShipsTypesOwners[$TShip] as $Owner => $NotImportant)
                {
                    $TKey = "{$TShip}|{$Owner}";
                    $TUser = $Owner;

                    if ($AtkShipsTypes[$TShip] > 1) {
                        $ACount = $shotsDistribution['distributionByTargetFullKey'][$TKey];
                    } else {
                        $ACount = $GainedShoots;
                    }

                    if ($ACount == 0) {
                        continue;
                    }

                    if (
                        Ares\Evaluators\isShieldImpenetrable([
                            'shotForce' => $AForce,
                            'targetShipShield' => $AtkShipsShield[$TKey],
                        ])
                    ) {
                        $UsedForce = $AForce * $ACount;
                        $Rounds[$i]['def']['force'] += $UsedForce;
                        $Rounds[$i]['def']['count'] += $ACount;
                        $Rounds[$i]['atk']['shield'] += $UsedForce;

                        continue;
                    }

                    $AvailableForce = $AForce * $ACount;
                    $Force2TDShield = 0;

                    $isShotBypassingShield = Ares\Evaluators\isShotBypassingShield([
                        'shotForce' => $AForce,
                        'targetShipShield' => $AtkShipsShield[$TKey],
                    ]);

                    if (!$isShotBypassingShield) {
                        if (
                            isset($AtkShields[$TKey]['left']) &&
                            $AtkShields[$TKey]['left'] === true
                        ) {
                            $Force2TDShield = $AtkShields[$TKey]['shield'];
                        } else {
                            $Force2TDShield = $AtkShipsShield[$TKey] * $AtkShipsTypesCount[$TShip][$TUser];
                        }
                    }

                    if ($AvailableForce <= $Force2TDShield) {
                        $Rounds[$i]['def']['force'] += $AvailableForce;
                        $Rounds[$i]['def']['count'] += $ACount;
                        $Rounds[$i]['atk']['shield'] += $AvailableForce;
                        $AtkShields[$TKey] = array('left' => true, 'shield' => $Force2TDShield - $AvailableForce);

                        continue;
                    }

                    if (!$isShotBypassingShield) {
                        $AtkShields[$TKey] = array('left' => true, 'shield' => 0);
                    }

                    $LeftForce = $AvailableForce - $Force2TDShield;

                    $Able2Destroy = (
                        $AtkShipsTypesCount[$TShip][$TUser] -
                        (isset($AlreadyDestroyedAtk[$TKey]) ? $AlreadyDestroyedAtk[$TKey] : 0)
                    );

                    if ($ACount < $Able2Destroy) {
                        $Able2Destroy = $ACount;
                    }

                    $NeedForce = ($AtkShipsHull[$TKey] * $Able2Destroy);
                    if(isset($AtkHullDmg[$TKey]))
                    {
                        $NeedForce -= ($AtkHullDmg[$TKey] * $AtkShipsHull[$TKey]);
                    }
                    if($NeedForce > $LeftForce)
                    {
                        $UsedForce = $LeftForce + $Force2TDShield;
                        $Shoots = $UsedForce / $AForce;
                        $DestroyedOrg = ($LeftForce / $AtkShipsHull[$TKey]);
                        if(isset($AtkHullDmg[$TKey]))
                        {
                            $DestroyedOrg += $AtkHullDmg[$TKey];
                        }
                        $Destroyed = floor($LeftForce / $AtkShipsHull[$TKey]);
                        $Difference = $DestroyedOrg - $Destroyed;
                        $AtkHullDmg[$TKey] = $Difference;
                        if($AtkHullDmg[$TKey] >= 1)
                        {
                            $Destroyed += 1;
                            $AtkHullDmg[$TKey] -= 1;
                        }
                    }
                    else
                    {
                        $UsedForce = $NeedForce + $Force2TDShield;
                        $Shoots = ceil($UsedForce / $AForce);
                        if($Shoots < $Able2Destroy)
                        {
                            $Shoots = $Able2Destroy;
                        }
                        $Destroyed = $Able2Destroy;
                    }
                    $Rounds[$i]['def']['force'] += $UsedForce;
                    $Rounds[$i]['def']['count'] += $Shoots;
                    $Rounds[$i]['atk']['shield'] += $Force2TDShield;

                    if(!isset($AtkLost[$TShip][$TUser]))
                    {
                        $AtkLost[$TShip][$TUser] = 0;
                    }
                    if(!isset($ForceContribution['def'][$AUser]))
                    {
                        $ForceContribution['def'][$AUser] = 0;
                    }
                    if(!isset($ShotDown['def']['d'][$AUser][$TShip]))
                    {
                        $ShotDown['def']['d'][$AUser][$TShip] = 0;
                    }
                    if(!isset($ShotDown['atk']['l'][$TUser][$TShip]))
                    {
                        $ShotDown['atk']['l'][$TUser][$TShip] = 0;
                    }

                    $AtkLost[$TShip][$TUser] += $Destroyed;
                    $ForceContribution['def'][$AUser] += $UsedForce;
                    $ShotDown['def']['d'][$AUser][$TShip] += $Destroyed;
                    $ShotDown['atk']['l'][$TUser][$TShip] += $Destroyed;
                    if($Destroyed == ($AtkShipsTypesCount[$TShip][$TUser] - (isset($AlreadyDestroyedAtk[$TKey]) ? $AlreadyDestroyedAtk[$ThisKey] : 0)))
                    {
                        unset($AtkShipsForce_Copy[$TKey]);
                        if(isset($AtkHullDmg[$TKey]))
                        {
                            unset($AtkHullDmg[$TKey]);
                        }
                        unset($AtkShipsTypesOwners[$TShip][$TUser]);
                        $AtkShipsTypes[$TShip] -= 1;
                    }
                    else
                    {
                        if($Destroyed > 0)
                        {
                            if(!isset($AlreadyDestroyedAtk[$TKey]))
                            {
                                $AlreadyDestroyedAtk[$TKey] = 0;
                            }
                            $AlreadyDestroyedAtk[$TKey] += $Destroyed;
                        }
                    }
                }
            }
        }

        // ------------------------------------------------------------------------------------------------------------------------------------

        // Now Calculate all loses and update all Arrays
        // ---------------------------------------------

        // Defenders
        foreach($DefLost as $ShipID => $ShipUsers)
        {
            $ShipKey = "{$ShipID}|";
            foreach($ShipUsers as $User => $Count)
            {
                $UserKey = "{$ShipKey}{$User}";
                if($Count == $DefenderShips[$User][$ShipID])
                {
                    unset($DefenderShips[$User][$ShipID]);
                    unset($DefShipsForce[$UserKey]);
                }
                else
                {
                    $DefenderShips[$User][$ShipID] -= $Count;
                    $DefShipsTypesCount[$ShipID][$User] -= $Count;
                }
                if(!isset($DefLoseCount[$ShipID]))
                {
                    $DefLoseCount[$ShipID] = 0;
                }
                $DefLoseCount[$ShipID] += $Count;
                if($User == 0)
                {
                    if($ShipID > 400 AND $ShipID < 500)
                    {
                        if(!isset($PlanetDefSysLost[$ShipID]))
                        {
                            $PlanetDefSysLost[$ShipID] = 0;
                        }
                        $PlanetDefSysLost[$ShipID] += $Count;
                    }
                }
            }
        }
        foreach($DefenderShips as $User => $Data)
        {
            if(empty($Data))
            {
                unset($DefenderShips[$User]);
            }
        }
        if(empty($DefenderShips))
        {
            unset($DefenderShips);
        }

        // Attackers
        foreach($AtkLost as $ShipID => $ShipUsers)
        {
            $ShipKey = "{$ShipID}|";
            foreach($ShipUsers as $User => $Count)
            {
                $UserKey = "{$ShipKey}{$User}";
                if($Count == $AttackerShips[$User][$ShipID])
                {
                    unset($AttackerShips[$User][$ShipID]);
                    unset($AtkShipsForce[$UserKey]);
                }
                else
                {
                    $AttackerShips[$User][$ShipID] -= $Count;
                    $AtkShipsTypesCount[$ShipID][$User] -= $Count;
                }
                if(!isset($AtkLoseCount[$ShipID]))
                {
                    $AtkLoseCount[$ShipID] = 0;
                }
                $AtkLoseCount[$ShipID] += $Count;
            }
        }
        foreach($AttackerShips as $User => $Data)
        {
            if(empty($Data))
            {
                unset($AttackerShips[$User]);
            }
        }
        if(empty($AttackerShips))
        {
            unset($AttackerShips);
        }
    }

    if((!empty($AttackerShips) AND !empty($DefenderShips)) OR (empty($AttackerShips) AND empty($DefenderShips)))
    {
        $BattleResult = COMBAT_DRAW; // It's a Draw
    }
    else if(empty($AttackerShips))
    {
        $BattleResult = COMBAT_DEF;// Defenders Won!
    }
    else if(empty($DefenderShips))
    {
        $BattleResult = COMBAT_ATK;// Attackers Won!
    }
    else
    {
        return array('result' => false, 'error' => 'BAD_COMBAT_RESULT');
    }

    return array
    (
        'return' => true,
        'AttackerShips' => (isset($AttackerShips) ? $AttackerShips : null),
        'DefenderShips' => (isset($DefenderShips) ? $DefenderShips : null),
        'rounds' => $Rounds, 'result' => $BattleResult,
        'AtkLose' => $AtkLoseCount, 'DefLose' => $DefLoseCount, 'DefSysLost' => $PlanetDefSysLost,
        'ShotDown' => (isset($ShotDown) ? $ShotDown : null), 'ForceContribution' => (isset($ForceContribution) ? $ForceContribution : null)
    );
}

?>
