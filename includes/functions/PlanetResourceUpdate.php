<?php

function PlanetResourceUpdate($CurrentUser, &$CurrentPlanet, $UpdateTime, $Simul = false)
{
	global $_Vars_ResProduction, $_Vars_GameElements, $_Vars_ElementCategories, $_GameConfig, $_DontShowMenus, $UserDev_Log, $SetPercents;
	
	$NeedUpdate = false;
	
	if(!empty($SetPercents[$CurrentPlanet['id']]))
	{
		foreach($SetPercents[$CurrentPlanet['id']] as $Key => $Value)
		{
			$CurrentPlanet[$Key] = $Value['old'];
		}
	}

	$ProductionTime = ($UpdateTime - $CurrentPlanet['last_update']);

 	// Update place for resources
	$CurrentPlanet['metal_max']		= (floor(BASE_STORAGE_SIZE * pow(1.7, $CurrentPlanet[$_Vars_GameElements[22]])));
	$CurrentPlanet['crystal_max']	= (floor(BASE_STORAGE_SIZE * pow(1.7, $CurrentPlanet[$_Vars_GameElements[23]])));
	$CurrentPlanet['deuterium_max'] = (floor(BASE_STORAGE_SIZE * pow(1.7, $CurrentPlanet[$_Vars_GameElements[24]])));

	// Start ResourceUpdating
	if($CurrentPlanet['planet_type'] == 1)
	{
		// Calculate Officers Income Bonus
		// - Geologist: Resource Bonus
		if($ProductionTime > 0)
		{
			$Multiplier_Resources = 1;
			$Add_Multiplier_Resources = 0;
			if($CurrentUser['geologist_time'] > $CurrentPlanet['last_update'])
			{
				if($CurrentUser['geologist_time'] >= $UpdateTime)
				{
					$Add_Multiplier_Resources = $ProductionTime;
				}
				else
				{
					$Add_Multiplier_Resources = ($CurrentUser['geologist_time'] - $CurrentPlanet['last_update']);
				}
				$Multiplier_Resources += (0.15 * ($Add_Multiplier_Resources / $ProductionTime));
			}
			// - Engineed: Energy Bonus
			$Multiplier_Energy = 1;
			$Add_Multiplier_Energy = 0;
			if($CurrentUser['engineer_time'] > $CurrentPlanet['last_update'])
			{
				if($CurrentUser['engineer_time'] >= $UpdateTime)
				{
					$Add_Multiplier_Energy = $ProductionTime;
				}
				else
				{
					$Add_Multiplier_Energy = ($CurrentUser['engineer_time'] - $CurrentPlanet['last_update']);
				}
				$Multiplier_Energy += (0.10 * ($Add_Multiplier_Energy / $ProductionTime));
			}
		}
		else
		{
			if($CurrentUser['geologist_time'] >= $CurrentPlanet['last_update'])
			{
				$Multiplier_Resources = 1.15;
			}
			else
			{
				$Multiplier_Resources = 1;
			}
			if($CurrentUser['engineer_time'] >= $CurrentPlanet['last_update'])
			{
				$Multiplier_Energy = 1.10;
			}
			else
			{
				$Multiplier_Energy = 1;
			}
		}

		// Calculate Storage for Resources (with Overflow)
		$MaxMetalStorage = $CurrentPlanet['metal_max'] * MAX_OVERFLOW;
		$MaxCrystalStorage = $CurrentPlanet['crystal_max'] * MAX_OVERFLOW;
		$MaxDeuteriumStorage = $CurrentPlanet['deuterium_max'] * MAX_OVERFLOW;

		// Calculate Income from Mines, Power Plants, Extractors etc.
		$Caps = array();
		$BuildTemp = $CurrentPlanet['temp_max'];
		$TextIfEmpty = 'return "0";';
		foreach($_Vars_ElementCategories['prod'] as $ElementID)
		{
			$BuildLevelFactor = $CurrentPlanet[$_Vars_GameElements[$ElementID].'_porcent'];
			$BuildLevel = $CurrentPlanet[$_Vars_GameElements[$ElementID]];

			if($BuildLevel <= 0)
			{
				continue;
			}

			if($_Vars_ResProduction[$ElementID]['formule']['metal'] != $TextIfEmpty)
			{
				$Caps['metal_perhour'] += floor(eval($_Vars_ResProduction[$ElementID]['formule']['metal']) * $_GameConfig['resource_multiplier'] * $Multiplier_Resources);
			}
			if($_Vars_ResProduction[$ElementID]['formule']['crystal'] != $TextIfEmpty)
			{
				$Caps['crystal_perhour'] += floor(eval($_Vars_ResProduction[$ElementID]['formule']['crystal']) * $_GameConfig['resource_multiplier'] * $Multiplier_Resources);
			}
			if($ElementID != 12)
			{
				if($_Vars_ResProduction[$ElementID]['formule']['deuterium'] != $TextIfEmpty)
				{
					$Caps['deuterium_perhour'] += floor(eval($_Vars_ResProduction[$ElementID]['formule']['deuterium']) * $_GameConfig['resource_multiplier'] * $Multiplier_Resources);
				}
			}
			else
			{
				$Caps['deuterium_perhour'] += floor(eval($_Vars_ResProduction[$ElementID]['formule']['deuterium']) * $_GameConfig['resource_multiplier']);
			}

			if($ElementID < 4)
			{
				$Caps['energy_used'] += floor(eval($_Vars_ResProduction[$ElementID]['formule']['energy']));
			}
			else
			{ 
				if($ElementID != 12)
				{
					$Caps['energy_max'] += floor(eval($_Vars_ResProduction[$ElementID]['formule']['energy']) * $Multiplier_Energy);
				}
				else
				{
					$MineDeuteriumUse = floor(eval($_Vars_ResProduction[$ElementID]['formule']['deuterium']) * $_GameConfig['resource_multiplier']) * (-1);
					if($MineDeuteriumUse > 0)
					{
						if($CurrentPlanet['deuterium'] <= 0)
						{
							if($Caps['deuterium_perhour'] == ($MineDeuteriumUse * (-1)))
							{
								// If no enough + production of deuterium
							}
							else
							{
								// If there is still some deuterium in + production to use
								$FusionReactorMulti = ($Caps['deuterium_perhour'] + $MineDeuteriumUse) / $MineDeuteriumUse;
								if($FusionReactorMulti > 1)
								{
									$FusionReactorMulti = 1;
								}
								$Caps['energy_max'] += floor(eval($_Vars_ResProduction[$ElementID]['formule']['energy']) * $FusionReactorMulti * $Multiplier_Energy);
							}
						}
						else
						{
							if($Caps['deuterium_perhour'] >= 0)
							{
								$Caps['energy_max'] += floor(eval($_Vars_ResProduction[$ElementID]['formule']['energy']) * $Multiplier_Energy);
							}
							else
							{
								$FusionReactorMulti = $CurrentPlanet['deuterium'] / ($MineDeuteriumUse / 3600);
								if($FusionReactorMulti > 1)
								{
									$FusionReactorMulti = 1;
								}
								$Caps['energy_max'] += floor(eval($_Vars_ResProduction[$ElementID]['formule']['energy']) * $FusionReactorMulti * $Multiplier_Energy);
							}
						}
					}
				} 
			}
		}

		// Set current IncomeLevels
		$CurrentPlanet['metal_perhour'] = $Caps['metal_perhour'];
		$CurrentPlanet['crystal_perhour'] = $Caps['crystal_perhour'];
		$CurrentPlanet['deuterium_perhour'] = $Caps['deuterium_perhour'];
		$CurrentPlanet['energy_used'] = $Caps['energy_used'];
		$CurrentPlanet['energy_max'] = $Caps['energy_max'];

		if($ProductionTime > 0)
		{
			// Calculate ProductionLevel
			if(!isOnVacation($CurrentUser))
			{
				if($Caps['energy_max'] == 0 AND abs($Caps['energy_used']) > 0)
				{
					$production_level = 0;
				}
				elseif($Caps['energy_max'] > 0 AND abs($Caps['energy_used']) > $Caps['energy_max'])
				{
					$production_level = floor(($Caps['energy_max'] * 100) / abs($Caps['energy_used']));
				}
				else
				{
					$production_level = 100;
				}
				if($production_level > 100)
				{
					$production_level = 100;
				}
			}
			else
			{
				// Protect against VacationMode
				$production_level = 0;
			}

			// Calculate TotalIncome
			if($CurrentPlanet['metal'] < $MaxMetalStorage)
			{
				$Metal_BuildIncome= ($ProductionTime * ($Caps['metal_perhour'] / 3600)) * (0.01 * $production_level);
				$Metal_BaseIncome = $ProductionTime * (($_GameConfig['metal_basic_income'] * $_GameConfig['resource_multiplier']) / 3600);
				$Metal_TotalIncome = $Metal_BuildIncome + $Metal_BaseIncome;
				$Metal_Theoretical = $CurrentPlanet['metal'] + $Metal_TotalIncome;

				if($Metal_Theoretical < 0)
				{
					$Metal_Theoretical = 0;
				}
				if($Metal_Theoretical < $MaxMetalStorage)
				{
					$CurrentPlanet['metal'] = $Metal_Theoretical;
				}
				else
				{
					$CurrentPlanet['metal'] = $MaxMetalStorage;
					$Metal_TotalIncome -= ($Metal_Theoretical - $MaxMetalStorage);
				}
			}
			if($CurrentPlanet['crystal'] < $MaxCrystalStorage)
			{
				$Crystal_BuildIncome= ($ProductionTime * ($Caps['crystal_perhour'] / 3600)) * (0.01 * $production_level);
				$Crystal_BaseIncome = $ProductionTime * (($_GameConfig['crystal_basic_income'] * $_GameConfig['resource_multiplier']) / 3600);
				$Crystal_TotalIncome = $Crystal_BuildIncome + $Crystal_BaseIncome;
				$Crystal_Theoretical = $CurrentPlanet['crystal'] + $Crystal_TotalIncome;

				if($Crystal_Theoretical < 0)
				{
					$Crystal_Theoretical = 0;
				}
				if($Crystal_Theoretical < $MaxCrystalStorage)
				{
					$CurrentPlanet['crystal'] = $Crystal_Theoretical;
				}
				else
				{
					$CurrentPlanet['crystal'] = $MaxCrystalStorage;
					$Crystal_TotalIncome -= ($Crystal_Theoretical - $MaxCrystalStorage);
				}
			}
			if($CurrentPlanet['deuterium'] < $MaxDeuteriumStorage)
			{
				$Deuterium_BuildIncome= ($ProductionTime * ($Caps['deuterium_perhour'] / 3600)) * (0.01 * $production_level);
				$Deuterium_BaseIncome = $ProductionTime * (($_GameConfig['deuterium_basic_income'] * $_GameConfig['resource_multiplier']) / 3600);
				$Deuterium_TotalIncome = $Deuterium_BuildIncome + $Deuterium_BaseIncome;
				$Deuterium_Theoretical = $CurrentPlanet['deuterium'] + $Deuterium_TotalIncome;

				if($Deuterium_Theoretical < 0)
				{
					$Deuterium_Theoretical = 0;
				}
				if($Deuterium_Theoretical < $MaxDeuteriumStorage)
				{
					$CurrentPlanet['deuterium'] = $Deuterium_Theoretical;
				}
				else
				{
					$CurrentPlanet['deuterium'] = $MaxDeuteriumStorage;
					$Deuterium_TotalIncome -= ($Deuterium_Theoretical - $MaxDeuteriumStorage);
				}
			}
			$NeedUpdate = true;
		}
	}

	// End of ResourceUpdate
	$CurrentPlanet['last_update'] = $UpdateTime;

	if($Simul === false)
	{
		// Management of eventual shipyard Queue
		$Builded = HandleShipyardQueue($CurrentUser, $CurrentPlanet, $ProductionTime, $UpdateTime);

		// Update planet
		$QryUpdatePlanet .= "UPDATE {{table}} SET ";
		$QryUpdatePlanet .= "`metal` = '{$CurrentPlanet['metal']}', ";
		$QryUpdatePlanet .= "`crystal` = '{$CurrentPlanet['crystal']}', ";
		$QryUpdatePlanet .= "`deuterium` = '{$CurrentPlanet['deuterium']}', ";
		$QryUpdatePlanet .= "`last_update` = '{$CurrentPlanet['last_update']}', ";
		$QryUpdatePlanet .= "`shipyardQueue` = '{$CurrentPlanet['shipyardQueue']}', ";
		$QryUpdatePlanet .= "`metal_perhour` = '{$CurrentPlanet['metal_perhour']}', ";
		$QryUpdatePlanet .= "`crystal_perhour` = '{$CurrentPlanet['crystal_perhour']}', ";
		$QryUpdatePlanet .= "`deuterium_perhour` = '{$CurrentPlanet['deuterium_perhour']}', ";
		$QryUpdatePlanet .= "`energy_used` = '{$CurrentPlanet['energy_used']}', ";
		$QryUpdatePlanet .= "`energy_max` = '{$CurrentPlanet['energy_max']}', ";
		// Check if something has been built in Shipyard
		if(!empty($Builded))
		{
			$NeedUpdate = true;
			foreach($Builded as $Element => $Count)
			{
				if(!empty($_Vars_GameElements[$Element]))
				{
					$QryUpdatePlanet .= "`{$_Vars_GameElements[$Element]}` = `{$_Vars_GameElements[$Element]}` + {$Count}, ";
				}
			}
		}
		$QryUpdatePlanet .= "`shipyardQueue_additionalWorkTime` = '{$CurrentPlanet['shipyardQueue_additionalWorkTime']}' ";
		$QryUpdatePlanet .= "WHERE ";
		$QryUpdatePlanet .= "`id` = {$CurrentPlanet['id']};";

		doquery('LOCK TABLE {{table}} WRITE, {{prefix}}errors WRITE', 'planets');
		$Last_DontShowMenus = $_DontShowMenus;
		$_DontShowMenus= true;
		doquery($QryUpdatePlanet, 'planets');
		doquery('UNLOCK TABLES', '');
		$_DontShowMenus = $Last_DontShowMenus;
	}

	if(!empty($SetPercents[$CurrentPlanet['id']]) AND $CurrentPlanet['planet_type'] == 1)
	{
		foreach($SetPercents[$CurrentPlanet['id']] as $Key => $Value)
		{ 
			foreach($_Vars_ElementCategories['prod'] as $ProdID)
			{
				if($_Vars_GameElements[$ProdID].'_porcent' == $Key)
				{
					$CalcDiff = array();

					$BuildLevelFactor = $CurrentPlanet[$_Vars_GameElements[$ProdID].'_porcent'];
					$BuildLevel = $CurrentPlanet[$_Vars_GameElements[$ProdID]];

					if($BuildLevel <= 0)
					{
						// Don't waste time to calculate 0 production...
						continue;
					}

					if($_Vars_ResProduction[$ProdID]['formule']['metal'] != $TextIfEmpty)
					{
						$BuildLevelFactor = $Value['old'];
						$CalcDiff['metal_perhour']['old'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['metal']) * $_GameConfig['resource_multiplier'] * $Multiplier_Resources);
						$BuildLevelFactor = $Value['new'];
						$CalcDiff['metal_perhour']['new'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['metal']) * $_GameConfig['resource_multiplier'] * $Multiplier_Resources);
					}
					if($_Vars_ResProduction[$ProdID]['formule']['crystal'] != $TextIfEmpty)
					{
						$BuildLevelFactor = $Value['old'];
						$CalcDiff['crystal_perhour']['old'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['crystal']) * $_GameConfig['resource_multiplier'] * $Multiplier_Resources);
						$BuildLevelFactor = $Value['new'];
						$CalcDiff['crystal_perhour']['new'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['crystal']) * $_GameConfig['resource_multiplier'] * $Multiplier_Resources);
					}
					if($ProdID != 12)
					{
						if($_Vars_ResProduction[$ProdID]['formule']['deuterium'] != $TextIfEmpty)
						{
							$BuildLevelFactor = $Value['old'];
							$CalcDiff['deuterium_perhour']['old'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['deuterium']) * $_GameConfig['resource_multiplier'] * $Multiplier_Resources);
							$BuildLevelFactor = $Value['new'];
							$CalcDiff['deuterium_perhour']['new'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['deuterium']) * $_GameConfig['resource_multiplier'] * $Multiplier_Resources);
						}
					}
					else
					{
						$BuildLevelFactor = $Value['old'];
						$CalcDiff['deuterium_perhour']['old'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['deuterium']) * $_GameConfig['resource_multiplier']);
						$BuildLevelFactor = $Value['new'];
						$CalcDiff['deuterium_perhour']['new'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['deuterium']) * $_GameConfig['resource_multiplier']);
					}

					if($ProdID < 4)
					{
						$BuildLevelFactor = $Value['old'];
						$CalcDiff['energy_used']['old'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']));
						$BuildLevelFactor = $Value['new'];
						$CalcDiff['energy_used']['new'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']));
					}
					elseif($ProdID >= 4 )
					{
						if($ProdID != 12)
						{
							$BuildLevelFactor = $Value['old'];
							$CalcDiff['energy_max']['old'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']) * $Multiplier_Energy);
							$BuildLevelFactor = $Value['new'];
							$CalcDiff['energy_max']['new'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']) * $Multiplier_Energy);
						}
						else
						{
							$BuildLevelFactor = $Value['old'];
							$MineDeuteriumUse = floor(eval($_Vars_ResProduction[$ProdID]['formule']['deuterium']) * $_GameConfig['resource_multiplier']) * (-1);
							if($MineDeuteriumUse > 0)
							{
								if($CurrentPlanet['deuterium'] <= 0)
								{
									if($CurrentPlanet['deuterium_perhour'] <= ($MineDeuteriumUse * (-1)))
									{
										// If no enough + production of deuterium
										$CalcDiff['energy_max']['old'] = 0;
									}
									else
									{
										// If there is still some deuterium in + production to use
										$FusionReactorMulti = ($CurrentPlanet['deuterium_perhour'] + $MineDeuteriumUse) / $MineDeuteriumUse;
										if($FusionReactorMulti > 1)
										{
											$FusionReactorMulti = 1;
										}
										$CalcDiff['energy_max']['old'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']) * $FusionReactorMulti * $Multiplier_Energy);
									}
								}
								else
								{
									if($CurrentPlanet['deuterium_perhour'] >= 0)
									{
										$CalcDiff['energy_max']['old'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']) * $Multiplier_Energy);
									}
									else
									{
										$FusionReactorMulti = $CurrentPlanet['deuterium'] / ($MineDeuteriumUse / 3600);
										if($FusionReactorMulti > 1)
										{
											$FusionReactorMulti = 1;
										}
										$CalcDiff['energy_max']['old'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']) * $FusionReactorMulti * $Multiplier_Energy);
									}
								}
							}
							else
							{
								$CalcDiff['energy_max']['old'] = 0;
							}

							$CurrentPlanet['deuterium_perhour'] += $CalcDiff['deuterium_perhour']['new'] - $CalcDiff['deuterium_perhour']['old'];
							unset($CalcDiff['deuterium_perhour']);

							$BuildLevelFactor = $Value['new'];
							$MineDeuteriumUse = floor(eval($_Vars_ResProduction[$ProdID]['formule']['deuterium']) * $_GameConfig['resource_multiplier']) * (-1);
							if($MineDeuteriumUse > 0)
							{
								if($CurrentPlanet['deuterium'] <= 0)
								{
									if($CurrentPlanet['deuterium_perhour'] <= ($MineDeuteriumUse * (-1)))
									{
										// If no enough + production of deuterium
										$CalcDiff['energy_max']['new'] = 0;
									}
									else
									{
										// If there is still some deuterium in + production to use
										$FusionReactorMulti = ($CurrentPlanet['deuterium_perhour'] + $MineDeuteriumUse) / $MineDeuteriumUse;
										if($FusionReactorMulti > 1)
										{
											$FusionReactorMulti = 1;
										}
										$CalcDiff['energy_max']['new'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']) * $FusionReactorMulti * $Multiplier_Energy);
									}
								}
								else
								{
									if($CurrentPlanet['deuterium_perhour'] >= 0)
									{
										$CalcDiff['energy_max']['new'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']) * $Multiplier_Energy);
									}
									else
									{
										$FusionReactorMulti = $CurrentPlanet['deuterium'] / ($MineDeuteriumUse / 3600);
										if($FusionReactorMulti > 1)
										{
											$FusionReactorMulti = 1;
										}
										$CalcDiff['energy_max']['new'] = floor(eval($_Vars_ResProduction[$ProdID]['formule']['energy']) * $FusionReactorMulti * $Multiplier_Energy);
									}
								}
							}
							else
							{
								$CalcDiff['energy_max']['new'] = 0;
							}
						}
					}

					foreach($CalcDiff as $Key => $Values)
					{
						$CalcThatDiff = $Values['new'] - $Values['old'];
						$CurrentPlanet[$Key] += $CalcThatDiff;
					}
				}
			}
		}
	}
	
	return $NeedUpdate;
}

?>