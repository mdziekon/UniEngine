<?php

function ShowTopNavigationBar($CurrentUser, $CurrentPlanet)
{
	global $_Lang, $_GET, $_GameConfig, $_User, $_SkinPath, $NewMSGCount;

	if($CurrentUser)
	{
		if(!$CurrentPlanet)
		{
			return false;
		}

		// Update Planet Resources
		$IsOnVacation = isOnVacation($CurrentUser);
		PlanetResourceUpdate($CurrentUser, $CurrentPlanet, time());

		$parse = $_Lang;
		$parse['skinpath'] = $_SkinPath;
		$parse['image'] = $CurrentPlanet['image'];
		
		// Create PlanetList (for Select)
		$parse['planetlist'] = '';
				
		$ThisUsersPlanets = SortUserPlanets($CurrentUser);
		while($CurPlanet = mysql_fetch_assoc($ThisUsersPlanets))
		{
			if($CurPlanet['galaxy'] == $CurrentPlanet['galaxy'] AND $CurPlanet['system'] == $CurrentPlanet['system'] AND $CurPlanet['planet'] == $CurrentPlanet['planet'])
			{
				if($CurPlanet['id'] != $CurrentPlanet['id'])
				{
					$OtherType_ID = $CurPlanet['id'];
				}
			}
			
			if($CurrentUser['planet_sort_moons'] == 1)
			{
				$ThisPos = "{$CurPlanet['galaxy']}:{$CurPlanet['system']}:{$CurPlanet['planet']}";
				if($CurPlanet['planet_type'] == 1)
				{
					$PlanetListArray[$ThisPos] = $CurPlanet;
				}
				else 
				{
					$MoonListArray[$ThisPos] = $CurPlanet;
				}
			}
			else
			{
				if($CurPlanet['id'] == $CurrentUser['current_planet'])
				{
					$ThisPlanetSelected = ' selected';
				}
				else
				{
					$ThisPlanetSelected = '';
				}
				$parse['planetlist'] .= "\n<option {$ThisPlanetSelected} value=\"?cp={$CurPlanet['id']}&amp;mode={$_GET['mode']}&amp;re=0\">{$CurPlanet['name']} [{$CurPlanet['galaxy']}:{$CurPlanet['system']}:{$CurPlanet['planet']}]&nbsp;&nbsp;</option>";
			}
		}
		if($CurrentUser['planet_sort_moons'] == 1)
		{
			if(!empty($PlanetListArray))
			{
				foreach($PlanetListArray as $Pos => $Planet)
				{
					$ParsedPlanetList[] = $Planet;
					if(!empty($MoonListArray[$Pos]))
					{
						$ParsedPlanetList[] = $MoonListArray[$Pos];
					}
				}
				unset($PlanetListArray);
				unset($MoonListArray);
				foreach($ParsedPlanetList as $CurPlanet)
				{
					if($CurPlanet['id'] == $CurrentUser['current_planet'])
					{
						$ThisPlanetSelected = ' selected';
					}
					else
					{
						$ThisPlanetSelected = '';
					}
					if($CurPlanet['planet_type'] == 1)
					{
						$ThisPlanetPos = "{$CurPlanet['galaxy']}:{$CurPlanet['system']}:{$CurPlanet['planet']}";
					}
					else
					{
						if($ThisPlanetSelected != '')
						{
							$ThisPlanetPos = "{$CurPlanet['galaxy']}:{$CurPlanet['system']}:{$CurPlanet['planet']}] [{$_Lang['PlanetList_MoonChar']}";
						}
						else
						{
							$ThisPlanetPos = $_Lang['PlanetList_MoonSign'];
						}
					}
					$parse['planetlist'] .= "\n<option {$ThisPlanetSelected} value=\"?cp={$CurPlanet['id']}&amp;mode={$_GET['mode']}\">{$CurPlanet['name']} [{$ThisPlanetPos}]&nbsp;&nbsp;</option>";
				}
				unset($ParsedPlanetList);
			}
		}
		if($OtherType_ID > 0)
		{
			$parse['Insert_TypeChange_ID'] = $OtherType_ID;
			if($CurrentPlanet['planet_type'] == 1)
			{
				$parse['Insert_TypeChange_Sign'] = $_Lang['PlanetList_TypeChange_Sign_M'];
				$parse['Insert_TypeChange_Title'] = $_Lang['PlanetList_TypeChange_Title_M'];
			}
			else
			{
				$parse['Insert_TypeChange_Sign'] = $_Lang['PlanetList_TypeChange_Sign_P'];
				$parse['Insert_TypeChange_Title'] = $_Lang['PlanetList_TypeChange_Title_P'];
			}
		}
		else
		{
			$parse['Insert_TypeChange_Hide'] = 'hide';
		}

		// Calculate resources for JS RealTime Counters
		
		// > Energy
		$EnergyFree = $CurrentPlanet['energy_max'] + $CurrentPlanet['energy_used'];
		$EnergyPretty = prettyNumber($EnergyFree);
		if($EnergyFree < 0)
		{
			$parse['Energy_free'] = colorRed($EnergyPretty);
		}
		else
		{
			$parse['Energy_free'] = colorGreen($EnergyPretty);
		}
		$parse['Energy_used'] = prettyNumber($CurrentPlanet['energy_max'] - $EnergyFree);
		$parse['Energy_total'] = prettyNumber($CurrentPlanet['energy_max']);
		
		// > Metal
		if($CurrentPlanet['metal'] > $CurrentPlanet['metal_max'])
		{
			$parse['ShowCount_Metal'] = colorRed(prettyNumber($CurrentPlanet['metal']));
			$parse['ShowStore_Metal'] = colorRed(prettyNumber($CurrentPlanet['metal_max']));
		}
		else
		{
			$parse['ShowCount_Metal'] = colorGreen(prettyNumber($CurrentPlanet['metal']));
			$parse['ShowStore_Metal'] = colorGreen(prettyNumber($CurrentPlanet['metal_max']));
		}
		
		// > Crystal
		if($CurrentPlanet['crystal'] > $CurrentPlanet['crystal_max'])
		{
			$parse['ShowCount_Crystal'] = colorRed(prettyNumber($CurrentPlanet['crystal']));
			$parse['ShowStore_Crystal'] = colorRed(prettyNumber($CurrentPlanet['crystal_max']));
		}
		else
		{
			$parse['ShowCount_Crystal'] = colorGreen(prettyNumber($CurrentPlanet['crystal']));
			$parse['ShowStore_Crystal'] = colorGreen(prettyNumber($CurrentPlanet['crystal_max']));
		}
		
		// > Deuterium
		if($CurrentPlanet['deuterium'] > $CurrentPlanet['deuterium_max'])
		{
			$parse['ShowCount_Deuterium'] = colorRed(prettyNumber($CurrentPlanet['deuterium']));
			$parse['ShowStore_Deuterium'] = colorRed(prettyNumber($CurrentPlanet['deuterium_max']));
		}
		else
		{
			$parse['ShowCount_Deuterium'] = colorGreen(prettyNumber($CurrentPlanet['deuterium']));
			$parse['ShowStore_Deuterium'] = colorGreen(prettyNumber($CurrentPlanet['deuterium_max']));
		}

		// > JS Vars
		$parse['JSCount_Metal'] = $CurrentPlanet['metal'];
		$parse['JSCount_Crystal'] = $CurrentPlanet['crystal'];
		$parse['JSCount_Deuterium'] = $CurrentPlanet['deuterium'];
		$parse['JSStore_Metal'] = $CurrentPlanet['metal_max'];
		$parse['JSStore_Crystal'] = $CurrentPlanet['crystal_max'];
		$parse['JSStore_Deuterium'] = $CurrentPlanet['deuterium_max'];
		$parse['JSStoreOverflow_Metal'] = $CurrentPlanet['metal_max'] * MAX_OVERFLOW;
		$parse['JSStoreOverflow_Crystal'] = $CurrentPlanet['crystal_max'] * MAX_OVERFLOW;
		$parse['JSStoreOverflow_Deuterium'] = $CurrentPlanet['deuterium_max'] * MAX_OVERFLOW;

		// > Production Level
		if(!$IsOnVacation)
		{
			if($CurrentPlanet['energy_max'] == 0 AND abs($CurrentPlanet['energy_used']) > 0)
			{
				$production_level = 0;
				$CurrentPlanet['metal_perhour'] = $_GameConfig['metal_basic_income'];
				$CurrentPlanet['crystal_perhour'] = $_GameConfig['crystal_basic_income'];
				$CurrentPlanet['deuterium_perhour'] = $_GameConfig['deuterium_basic_income'];
			}
			else if($CurrentPlanet['energy_max'] > 0 AND abs($CurrentPlanet['energy_used']) > $CurrentPlanet['energy_max'])
			{
				$production_level = floor(($CurrentPlanet['energy_max'] * 100) / abs($CurrentPlanet['energy_used']));
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
			$production_level = 0;
		}
		
		// > Income
		$parse['JSPerHour_Metal']		= ($CurrentPlanet['metal_perhour'] * 0.01 * $production_level) + (($CurrentPlanet['planet_type'] == 1) ? ($_GameConfig['metal_basic_income'] * $_GameConfig['resource_multiplier']) : 0);
		$parse['JSPerHour_Crystal']		= ($CurrentPlanet['crystal_perhour'] * 0.01 * $production_level) + (($CurrentPlanet['planet_type'] == 1) ? ($_GameConfig['crystal_basic_income'] * $_GameConfig['resource_multiplier']) : 0);
		$parse['JSPerHour_Deuterium']	= ($CurrentPlanet['deuterium_perhour'] * 0.01 * $production_level) + (($CurrentPlanet['planet_type'] == 1) ? ($_GameConfig['deuterium_basic_income'] * $_GameConfig['resource_multiplier']) : 0);
		
		// > Create ToolTip Infos
		$parse['TipIncome_Metal'] = '('.(($parse['JSPerHour_Metal'] >= 0) ? '+' : '-').prettyNumber(abs(round($parse['JSPerHour_Metal']))).'/h)';
		$parse['TipIncome_Crystal'] = '('.(($parse['JSPerHour_Crystal'] >= 0) ? '+' : '-').prettyNumber(abs(round($parse['JSPerHour_Crystal']))).'/h)';
		$parse['TipIncome_Deuterium'] = '('.(($parse['JSPerHour_Deuterium'] >= 0) ? '+' : '-').prettyNumber(abs(round($parse['JSPerHour_Deuterium']))).'/h)';

		$IncomePerSecond['met'] = $parse['JSPerHour_Metal'] / 3600;
		$IncomePerSecond['cry'] = $parse['JSPerHour_Crystal'] / 3600;
		$IncomePerSecond['deu'] = $parse['JSPerHour_Deuterium'] / 3600;

		if($IncomePerSecond['met'] > 0)
		{
			$parse['Metal_full_time'] = (round($parse['JSStoreOverflow_Metal']) - round($CurrentPlanet['metal'])) / $IncomePerSecond['met'];
			if($parse['Metal_full_time'] > 0)
			{
				$parse['Metal_full_time'] = $parse['full_in'].' <span id="metal_fullstore_counter">'.pretty_time($parse['Metal_full_time']).'</span>';
			}
			else
			{
				$parse['Metal_full_time'] = '<span class="red">'.$parse['full'].'</span>';
			}
		}
		else
		{
			$parse['Metal_full_time'] = $_Lang['income_no_mine'];
		}
		if($IsOnVacation)
		{
			$parse['Metal_full_time'] = $_Lang['income_vacation'];
		}

		if($IncomePerSecond['cry'] > 0)
		{
			$parse['Crystal_full_time'] = (round($parse['JSStoreOverflow_Crystal']) - round($CurrentPlanet['crystal'])) / $IncomePerSecond['cry'];
			if($parse['Crystal_full_time'] > 0)
			{
				$parse['Crystal_full_time'] = $parse['full_in'].' <span id="crystal_fullstore_counter">'.pretty_time($parse['Crystal_full_time']).'</span>';
			}
			else
			{
				$parse['Crystal_full_time'] = '<span class="red">'.$parse['full'].'</span>';
			}
		}
		else
		{
			$parse['Crystal_full_time'] = $_Lang['income_no_mine'];			
		}
		if($IsOnVacation)
		{
			$parse['Crystal_full_time'] = $_Lang['income_vacation'];
		}

		if($IncomePerSecond['deu'] > 0)
		{
			$parse['Deuterium_full_time'] = (round($parse['JSStoreOverflow_Deuterium']) - round($CurrentPlanet['deuterium'])) / $IncomePerSecond['deu'];
			if($parse['Deuterium_full_time'] > 0)
			{
				$parse['Deuterium_full_time'] = $parse['full_in'].' <span id="deuterium_fullstore_counter">'.pretty_time($parse['Deuterium_full_time']).'</span>';
			}
			else
			{
				$parse['Deuterium_full_time'] = '<span class="red">'.$parse['full'].'</span>';
			}
		}
		elseif($IncomePerSecond['deu'] < 0)
		{
			$parse['Deuterium_full_time'] = $_Lang['income_minus']; 
		}
		else
		{
			$parse['Deuterium_full_time'] = $_Lang['income_no_mine'];
		}
		if($IsOnVacation)
		{
			$parse['Deuterium_full_time'] = $_Lang['income_vacation'];
		}

		// > Create ToolTip Storage Status
		if($CurrentPlanet['metal'] > $CurrentPlanet['metal_max'])
		{
			if($CurrentPlanet['metal'] == $parse['JSStoreOverflow_Metal'])
			{
				$parse['Metal_store_status'] = $parse['Store_status_Full'];
			}
			else
			{
				$parse['Metal_store_status'] = $parse['Store_status_Overload'];
			}
		}
		else
		{
			if($CurrentPlanet['metal'] > 0)
			{
				if($CurrentPlanet['metal'] >= ($CurrentPlanet['metal_max'] * 0.8))
				{
					$parse['Metal_store_status'] = $parse['Store_status_NearFull'];
				}
				else
				{
					$parse['Metal_store_status'] = $parse['Store_status_OK'];
				}
			}
			else
			{
				$parse['Metal_store_status'] = $parse['Store_status_Empty'];
			}
		}

		if($CurrentPlanet['crystal'] > $CurrentPlanet['crystal_max'])
		{
			if($CurrentPlanet['crystal'] == $parse['JSStoreOverflow_Crystal'])
			{
				$parse['Crystal_store_status'] = $parse['Store_status_Full'];
			}
			else
			{
				$parse['Crystal_store_status'] = $parse['Store_status_Overload'];
			}
		}
		else
		{
			if($CurrentPlanet['crystal'] > 0)
			{
				if($CurrentPlanet['crystal'] >= ($CurrentPlanet['crystal_max'] * 0.8))
				{
					$parse['Crystal_store_status'] = $parse['Store_status_NearFull'];
				}
				else
				{
					$parse['Crystal_store_status'] = $parse['Store_status_OK'];
				}
			}
			else
			{
				$parse['Crystal_store_status'] = $parse['Store_status_Empty'];
			}
		}

		if($CurrentPlanet['deuterium'] > $CurrentPlanet['deuterium_max'])
		{
			if($CurrentPlanet['deuterium'] == $parse['JSStoreOverflow_Deuterium'])
			{
				$parse['Deuterium_store_status'] = $parse['Store_status_Full'];
			}
			else
			{
				$parse['Deuterium_store_status'] = $parse['Store_status_Overload'];
			}
		}
		else
		{
			if($CurrentPlanet['metal'] > 0)
			{
				if($CurrentPlanet['deuterium'] >= ($CurrentPlanet['deuterium_max'] * 0.8))
				{
					$parse['Deuterium_store_status'] = $parse['Store_status_NearFull'];
				}
				else
				{
					$parse['Deuterium_store_status'] = $parse['Store_status_OK'];
				}
			}
			else
			{
				$parse['Deuterium_store_status'] = $parse['Store_status_Empty'];
			}
		}

		// Dark Energy
		if($_User['darkEnergy'] > 0)
		{
			$parse['ShowCount_DarkEnergy'] = '<span class="lime">'.prettyNumber($_User['darkEnergy']).'</span>';
		}
		else
		{
			$parse['ShowCount_DarkEnergy'] = '<span class="orange">'.$_User['darkEnergy'].'</span>';
		}

		// Messages Counter
		$Query_MsgCount .= "SELECT COUNT(*) AS `Count` FROM {{table}} WHERE ";
		$Query_MsgCount .= "`id_owner` = {$CurrentUser['id']} AND ";
		$Query_MsgCount .= "`deleted` = false AND ";
		$Query_MsgCount .= "`read` = false ";
		$Query_MsgCount .= "LIMIT 1;";
		$Result_MsgCount = doquery($Query_MsgCount, 'messages', true);
		if($Result_MsgCount['Count'] > 0)
		{
			$parse['ShowCount_Messages'] = '[ <a href="messages.php">'.prettyNumber($Result_MsgCount['Count']).'</a> ]';
			$NewMSGCount = $Result_MsgCount['Count'];
		}
		else
		{
			$parse['ShowCount_Messages'] = '0';
		}

		$TopBar = parsetemplate(gettemplate('topnav'), $parse);
	}

	return $TopBar;
}

?>
