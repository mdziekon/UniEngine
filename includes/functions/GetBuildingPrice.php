<?php

function GetBuildingPrice($CurrentUser, $CurrentPlanet, $Element, $Incremental = true, $ForDestroy = false, $GetPremiumData = false)
{
	global $_Vars_Prices, $_Vars_GameElements;

	if($Incremental)
	{
		$level = ($CurrentPlanet[$_Vars_GameElements[$Element]]) ? $CurrentPlanet[$_Vars_GameElements[$Element]] : $CurrentUser[$_Vars_GameElements[$Element]];
	}
	if($ForDestroy == true)
	{
		$level -= 1;
	}

	$array = array('metal', 'crystal', 'deuterium', 'energy_max');
	foreach($array as $ResType)
	{
		if($Incremental)
		{
			$cost[$ResType] = floor($_Vars_Prices[$Element][$ResType] * pow($_Vars_Prices[$Element]['factor'], $level));
		}
		else
		{
			$cost[$ResType] = floor($_Vars_Prices[$Element][$ResType]);
		}

		if($ForDestroy == true)
		{
			$cost[$ResType] = floor($cost[$ResType] / 2);
		}
	}
	if($GetPremiumData)
	{
		global $_Vars_PremiumBuildingPrices;
		if($_Vars_PremiumBuildingPrices[$Element] > 0)
		{
			$cost['darkEnergy'] = $_Vars_PremiumBuildingPrices[$Element];
		}
	}

	return $cost;
}

?>