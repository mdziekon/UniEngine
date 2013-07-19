<?php

function CalcInterplanetaryAttack($TargetDefTech, $AttackerMiliTech, $IPMissiles, $DefSystemsPlanet, $PrimaryTarget)
{
	global $_Vars_Prices, $_Vars_CombatData, $_GameConfig;

	// --- Init Vars ---
	$IPMissileID = 503;
	$DebrisPercent = $_GameConfig['Debris_Def_Rocket'];


	$LeftMissiles = $IPMissiles;

	$HullUpgrade = 1 + (0.1 * $TargetDefTech);
	$ForceUpgrade = 1 + (0.1 * $AttackerMiliTech);
	$TotalDefSystemsCount = 0;
	$ElementsCount = 0;
	$ProportionLevel1 = 1;

	foreach($DefSystemsPlanet as $ID => $Count)
	{
		$ElementsCount += 1;
		$ResourceCost = $_Vars_Prices[$ID]['metal'] + $_Vars_Prices[$ID]['crystal'] + $_Vars_Prices[$ID]['deuterium'];
		$HullVals[$ID] = floor((($_Vars_Prices[$ID]['metal'] + $_Vars_Prices[$ID]['crystal']) / 10) * $HullUpgrade);
		$DefsPerSqMeter[$ID] = round(((5 * pow(10, -12) * pow($Count, 1.6)) + 0.5) * (2000 / $ResourceCost), 2);
		$TotalDefSystemsCount += $Count;
		if($PrimaryTarget > 0)
		{
			if(($PrimaryTarget + 400) == $ID)
			{
				$TotalDefSystemsCount -= $Count;
				$Proportions[$ID] = (mt_rand(7000, 8000) / 10000);
				$ProportionLevel1 = 1 - $Proportions[$ID];
			}
		}
	}

	foreach($DefSystemsPlanet as $ID => $Count)
	{
		if($PrimaryTarget > 0)
		{
			if(($PrimaryTarget + 400) == $ID)
			{
				continue;
			}
		}
		$Proportions[$ID] = ($Count/$TotalDefSystemsCount) * $ProportionLevel1;
	}
	arsort($Proportions);

	$MissileForce = floor($_Vars_CombatData[$IPMissileID]['attack'] * $ForceUpgrade); 
	$MissileRange = floor((log(($AttackerMiliTech + 1), 2) + 1) * 100);

	$Skip = array();
	$SkipCount = 0;

	$SecurityLoop = 500;

	while(true)
	{
		$SecurityLoop -= 1;
		if($SecurityLoop < 0)
		{
			break;
		}
		foreach($Proportions as $ID => &$Proportion)
		{
			if(in_array($ID, $Skip))
			{
				continue;
			}
			$Missiles = floor($Proportion * $LeftMissiles);
			if($Missiles <= 0)
			{
				$Proportion = 1;
				continue;
			}
			if($MissileForce >= $HullVals[$ID])
			{
				$Missiles4EnoughForce = 1;
			}
			else
			{
				$Missiles4EnoughForce = ceil($HullVals[$ID]/$MissileForce);
			}
			if($Missiles4EnoughForce > $Missiles)
			{
				if($Missiles == $LeftMissiles)
				{
					$Skip[] = $ID;
					$SkipCount += 1;
				}
				continue;
			}
			$MissilesNeeden = ceil($DefSystemsPlanet[$ID] / ($DefsPerSqMeter[$ID] * $MissileRange)) * $Missiles4EnoughForce;
			if($MissilesNeeden <= $Missiles)
			{
				$Destroyed[$ID] = $DefSystemsPlanet[$ID];
				$UsedMissiles = $MissilesNeeden;
				$Skip[] = $ID;
				$SkipCount += 1;
			}
			else
			{
				$TempMissiles = floor($Missiles / $Missiles4EnoughForce);
				$Destroyed[$ID] += floor($DefsPerSqMeter[$ID] * $MissileRange * $TempMissiles);
				$UsedMissiles = $Missiles;
			}
			$LeftMissiles -= $UsedMissiles;
			$Proportion = 1;
		}
		if($SkipCount == $ElementsCount OR $LeftMissiles <= 0)
		{
			break;
		}
	} 

	foreach($DefSystemsPlanet as $ID => $Count)
	{
		if($Destroyed[$ID] > $Count)
		{
			$Destroyed[$ID] = $Count;
		}
		$LeftDefs[$ID] = $Count - $Destroyed[$ID];
		$DestroyedTotal += $Destroyed[$ID];
		$Metal += $Destroyed[$ID] * $_Vars_Prices[$ID]['metal'];
		$Crystal += $Destroyed[$ID] * $_Vars_Prices[$ID]['crystal'];
		$Deuterium += $Destroyed[$ID] * $_Vars_Prices[$ID]['deuterium'];
	}

	$DebrisField['metal'] += floor($Metal * ($DebrisPercent / 100));
	$DebrisField['crystal'] += floor($Crystal * ($DebrisPercent / 100));

	$return['LeftDefs'] = $LeftDefs; //How much defence systems left on a Planet
	$return['Destroyed'] = $Destroyed; //How much defence systems has been lost
	$return['DestroyedTotal'] = $DestroyedTotal; //How much defence systems has been lost (in one number)
	$return['Metal_loss'] = $Metal; //How much metal has been lost (for each def sys)
	$return['Crystal_loss'] = $Crystal; //How much crystal has been lost (for each def sys)
	$return['Deuterium_loss'] = $Deuterium; //How much deuterium has been lost (for each def sys)
	$return['IPM_Range'] = $MissileRange; // What is the Range of Missiles
	$return['Debris'] = $DebrisField; // Create DebrisField

	return $return;
}

?>