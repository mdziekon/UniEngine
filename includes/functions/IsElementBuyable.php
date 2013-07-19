<?php

function IsElementBuyable($CurrentUser, $CurrentPlanet, $Element, $Incremental = true, $ForDestroy = false, $GetPremiumData = false)
{
	global $_Vars_Prices, $_Vars_GameElements;

	if(isOnVacation($CurrentUser))
	{
		return false;
	}

	if($Incremental)
	{
		$level = ($CurrentPlanet[$_Vars_GameElements[$Element]]) ? $CurrentPlanet[$_Vars_GameElements[$Element]] : $CurrentUser[$_Vars_GameElements[$Element]];
	}
	if($ForDestroy === true)
	{
		$level -= 1;
	}

	$RetValue = true;
	$array = array('metal', 'crystal', 'deuterium', 'energy_max');

	foreach($array as $ResType)
	{
		if($_Vars_Prices[$Element][$ResType] != 0)
		{
			if($Incremental)
			{
				$cost[$ResType] = floor($_Vars_Prices[$Element][$ResType] * pow($_Vars_Prices[$Element]['factor'], $level));
			}
			else
			{
				$cost[$ResType] = floor($_Vars_Prices[$Element][$ResType]);
			}

			if($ForDestroy)
			{
				$cost[$ResType] = floor($cost[$ResType] / 2);
			}

			if($cost[$ResType] > $CurrentPlanet[$ResType])
			{
				$RetValue = false;
			}
		}
	}

	if($GetPremiumData)
	{
		global $_Vars_PremiumBuildingPrices;
		if($_Vars_PremiumBuildingPrices[$Element] > $CurrentUser['darkEnergy'])
		{
			$RetValue = false;
		}
	}

	return $RetValue;
}

?>