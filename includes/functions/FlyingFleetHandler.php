<?php

function FlyingFleetHandler(&$planet, $PassedID = array())
{
	global $UserStatsPattern, $UserStatsData, $ChangeCoordinatesForFleets, $_Vars_ElementCategories, $_BenchTool, $_Cache;

	if(!empty($_BenchTool)){ $_BenchTool->simpleCountStart(false, 'telemetry__f0'); }
	
	$UserStatsPattern = array
	(
		'raids_won'					=> '0',
		'raids_draw'				=> '0',
		'raids_lost'				=> '0',
		'raids_acs_won'				=> '0',
		'raids_inAlly'				=> '0',
		'raids_missileAttack'		=> '0',
		'moons_destroyed'			=> '0',
		'moons_created'				=> '0',
		'other_expeditions_count'	=> '0',
	);

	$FleetArchive_Fields = array
	(
		'Owner', 'Mission', 'Mission_Changed', 'Array', 'Array_Changes', 'Time_Send', 'Time_Start', 'Time_Stay',
		'Time_End', 'Time_ACSAdd', 'Start_ID', 'Start_Galaxy', 'Start_System', 'Start_Planet', 'Start_Type',
		'Start_Type_Changed', 'Start_ID_Changed', 'Start_Res_Metal', 'Start_Res_Crystal', 'Start_Res_Deuterium',
		'End_ID', 'End_ID_Galaxy', 'End_Galaxy', 'End_System', 'End_Planet', 'End_Type', 'End_Type_Changed',
		'End_ID_Changed', 'End_Res_Metal', 'End_Res_Crystal', 'End_Res_Deuterium', 'End_Owner', 'End_Owner_IdleHours',
		'Calculated_Mission', 'Calculated_Mission_Time', 'Calculated_ComeBack', 'Calculated_ComeBack_Time', 'Destroyed',
		'Destroyed_Reason', 'TurnedBack', 'TurnedBack_Time', 'TurnedBack_EndTime', 'ACSID', 'ReportID', 'DefenderReportIDs',
		'Info_HadSameIP_Ever', 'Info_HadSameIP_Ever_Filtred', 'Info_HadSameIP_OnSend', 'Info_HasLostShips', 'Info_UsedTeleport'
	);
	foreach($FleetArchive_Fields as $Key => $Value)
	{
		$FleetArchive_Fields[$Key] = 'Fleet_'.$Value;
		$FleetArchive_Pattern[$FleetArchive_Fields[$Key]] = null;
	}

	foreach($_Vars_ElementCategories['fleet'] as $ElementID)
	{
		$ThisKey1 = 'destroyed_'.$ElementID;
		$CreateAchievementKeys[] = "`{$ThisKey1}`";
		$UserStatsPattern[$ThisKey1] = '0';
		$UserStatsUpQuery[] = "`{$ThisKey1}` = `{$ThisKey1}` + VALUES(`{$ThisKey1}`)";
		
		$ThisKey2 = 'lost_'.$ElementID;
		$CreateAchievementKeys[] = "`{$ThisKey2}`";
		$UserStatsPattern[$ThisKey2] = '0';
		$UserStatsUpQuery[] = "`{$ThisKey2}` = `{$ThisKey2}` + VALUES(`{$ThisKey2}`)";
	}
	foreach($_Vars_ElementCategories['defense'] as $ElementID)
	{
		if($ElementID >= 500)
		{
			break;
		}
		$ThisKey1 = 'destroyed_'.$ElementID;
		$CreateAchievementKeys[] = "`{$ThisKey1}`";
		$UserStatsPattern[$ThisKey1] = '0';
		$UserStatsUpQuery[] = "`{$ThisKey1}` = `{$ThisKey1}` + VALUES(`{$ThisKey1}`)";
		
		$ThisKey2 = 'lost_'.$ElementID;
		$CreateAchievementKeys[] = "`{$ThisKey2}`";
		$UserStatsPattern[$ThisKey2] = '0';
		$UserStatsUpQuery[] = "`{$ThisKey2}` = `{$ThisKey2}` + VALUES(`{$ThisKey2}`)";
	}
	$CreateAchievementKeys = implode(', ', $CreateAchievementKeys);
	$UserStatsData = false;
	
	// Get all Non-mine ACS MainFleets
	$QryGetACS = "SELECT `main_fleet_id`, `end_target_id` FROM {{table}} WHERE `owner_id` != {$planet['id_owner']} AND `user_joined` LIKE '%|{$planet['id_owner']}|%';";
	$GetACS = doquery($QryGetACS, 'acs');
	if(mysql_num_rows($GetACS) > 0)
	{
		while($ACSData = mysql_fetch_assoc($GetACS))
		{
			if($ACSData['end_target_id'] == $planet['id'])
			{
				continue;
			}
			else
			{
				$AdditionalIDs[] = $ACSData['main_fleet_id'];
			}
		}
	}

	// Select all my Recycling & Colonizing Fleets
	$Query_MyGalaxyFleets .= "SELECT `fleet_mission`, `fleet_end_id_galaxy`, `fleet_end_galaxy`, `fleet_end_system`, `fleet_end_planet` FROM {{table}} WHERE ";
	$Query_MyGalaxyFleets .= "`fleet_mission` IN (7, 8) AND `fleet_owner` = {$planet['id_owner']};";
	$Result_MyGalaxyFleets = doquery($Query_MyGalaxyFleets, 'fleets');
	if(mysql_num_rows($Result_MyGalaxyFleets) > 0)
	{
		$InsertGalaxyIDs = array();
		$InsertGalaxyPos = array();
		while($FetchData = mysql_fetch_assoc($Result_MyGalaxyFleets))
		{
			if($FetchData['fleet_mission'] == 7)
			{
				$ThisArray = array('g' => $FetchData['fleet_end_galaxy'], 's' => $FetchData['fleet_end_system'], 'p' => $FetchData['fleet_end_planet']);
				if(!in_array($ThisArray, $InsertGalaxyPos))
				{
					$InsertGalaxyPos[] = $ThisArray;
				}
			}
			elseif($FetchData['fleet_mission'] == 8)
			{
				if(!in_array($FetchData['fleet_end_id_galaxy'], $InsertGalaxyIDs))
				{
					$InsertGalaxyIDs[] = $FetchData['fleet_end_id_galaxy'];
				}
			}
		}
	}
	
	// Select all my Recycling Fleets (Get GalaxyID & FleetID Only!)
	$QryPreCounter = "SELECT `fleet_end_id_galaxy` FROM {{table}} WHERE `fleet_end_id_galaxy` > 0 AND `fleet_owner` = {$planet['id_owner']} AND `fleet_mission` IN (8);";
	$PreCountedRecycles = doquery($QryPreCounter, 'fleets');
	if(mysql_num_rows($PreCountedRecycles) > 0)
	{
		$InsertGalaxyIDs = array();
		while($MyRecycles = mysql_fetch_assoc($PreCountedRecycles))
		{
			if(!in_array($MyRecycles['fleet_end_id_galaxy'], $InsertGalaxyIDs))
			{
				$InsertGalaxyIDs[] = $MyRecycles['fleet_end_id_galaxy'];
			}
		}
	}

	// --- Get all Non-mine Fleets, which can interact with my Target
	// 1. Get My Fleets with EndIDs
	$QryPreCounter = "SELECT `fleet_end_id` FROM {{table}} WHERE ( ";
	$QryPreCounter .= "`fleet_owner` = {$planet['id_owner']} ";
	if(!empty($AdditionalIDs))
	{
		$ImplodeAddIDS = implode(',', $AdditionalIDs);
		$QryPreCounter .= "OR `fleet_id` IN ({$ImplodeAddIDS}) ";
	}
	$QryPreCounter .= ") AND `fleet_end_id` > 0 AND `fleet_mission` IN (1, 6, 7, 9, 10) ";
	$QryPreCounter .= "AND (`fleet_start_time` <= UNIX_TIMESTAMP() OR `fleet_end_time` <= UNIX_TIMESTAMP());";
	$PreCountedCoords = doquery($QryPreCounter, 'fleets');

	$AllAdditionalEndIDs = array();

	if(!empty($PassedID))
	{
		foreach($PassedID as $GivenID)
		{
			if(!in_array($GivenID, $AllAdditionalEndIDs))
			{
				$AllAdditionalEndIDs[] = $GivenID;
			}
		}
	}
	if(mysql_num_rows($PreCountedCoords) > 0)
	{
		while($PreCountedData = mysql_fetch_assoc($PreCountedCoords))
		{
			if(!in_array($PreCountedData['fleet_end_id'], $AllAdditionalEndIDs))
			{
				$AllAdditionalEndIDs[] = $PreCountedData['fleet_end_id'];
			}
		}
	}
	if(!empty($AllAdditionalEndIDs) OR !empty($InsertGalaxyIDs) OR !empty($InsertGalaxyPos))
	{
		// 2. Get All non-mine Fleets (FleetIDs)
		if(!empty($AllAdditionalEndIDs))
		{
			$AllAdditionalEndIDs = implode(',', $AllAdditionalEndIDs);
			$QryPreCounterWhere[] = "(`fleet_start_id` IN ({$AllAdditionalEndIDs}) OR `fleet_end_id` IN ({$AllAdditionalEndIDs}))";
		}
		if(!empty($InsertGalaxyIDs))
		{
			$InsertGalaxyIDs = implode(',', $InsertGalaxyIDs);
			$QryPreCounterWhere[] = "`fleet_end_id_galaxy` IN ({$InsertGalaxyIDs})";
		}
		if(!empty($InsertGalaxyPos))
		{
			foreach($InsertGalaxyPos as $ThisPos)
			{
				$TempPos[] = "(`fleet_end_galaxy` = {$ThisPos['g']} AND `fleet_end_system` = {$ThisPos['s']} AND `fleet_end_planet` = {$ThisPos['p']})";
			}
			$TempPos = implode(' OR ', $TempPos);
			$QryPreCounterWhere[] = "(`fleet_mission` = 7 AND ({$TempPos}))";
		}
		$QryPreCounter = "SELECT `fleet_id` FROM {{table}} ";
		$QryPreCounter .= "WHERE ( ".implode(' OR ', $QryPreCounterWhere)." ) ";
		$QryPreCounter .= "AND `fleet_owner` != {$planet['id_owner']} ";
		if(!empty($AdditionalIDs))
		{
			$ImplodeAddIDS = implode(',', $AdditionalIDs);
			$QryPreCounter .= "AND `fleet_id` NOT IN ({$ImplodeAddIDS}) ";
		}
		$QryPreCounter .= ";";
		$PreCountedFleets = doquery($QryPreCounter, 'fleets');
		if(mysql_num_rows($PreCountedFleets) > 0)
		{
			if(empty($AdditionalIDs))
			{
				$AdditionalIDs = array();
			}
			while($PreCountedData = mysql_fetch_assoc($PreCountedFleets))
			{
				if(!in_array($PreCountedData['fleet_id'], $AdditionalIDs))
				{
					$AdditionalIDs[] = $PreCountedData['fleet_id'];
				}
			}
		}
	}
	// ---

	// -------------------------------------------------------------------------------------
	// --- Main Fleet Getter Query ---------------------------------------------------------
	// -------------------------------------------------------------------------------------
	$Fields[] = "{{table}}.*";
	$Fields[] = "`usr`.`username`, `usr`.`ally_id`, `usr`.`tech_espionage`";
	$Fields[] = "`usr`.`morale_level`, `usr`.`morale_points`, `usr`.`morale_droptime`, `usr`.`morale_lastupdate`";
	$Fields[] = "`usr`.`tech_weapons`, `usr`.`tech_armour`, `usr`.`tech_shielding`, `usr`.`tech_laser`, `usr`.`tech_ion`, `usr`.`tech_plasma`";
	$Fields[] = "`usr`.`tech_antimatter`, `usr`.`tech_disintegration`, `usr`.`tech_graviton`";
	$Fields[] = "`ally`.`ally_tag`";
	$Fields[] = "`fleets_count`";
	$Fields[] = "{{prefix}}acs.`id` AS `acs_id`, {{prefix}}acs.`fleets_id`";
	$Fields[] = "`planet1`.`name` AS `attacking_planet_name`, `planet1`.`id_owner` AS `attacking_planet_owner`";
	$Fields[] = "`planet2`.`name` AS `endtarget_planet_name`, `planet2`.`id_owner` AS `endtarget_planet_owner`";
	$Fields = implode(', ', $Fields);

	$QryFleet = "SELECT {$Fields} FROM {{table}} ";
	$QryFleet.= "LEFT JOIN {{prefix}}users AS `usr` ON `usr`.`id` = {{table}}.fleet_owner ";
	$QryFleet.= "LEFT JOIN {{prefix}}alliance AS `ally` ON `usr`.`ally_id` = `ally`.`id` ";
	$QryFleet.= "LEFT JOIN {{prefix}}acs ON {{prefix}}acs.main_fleet_id = {{table}}.fleet_id ";
	$QryFleet.= "LEFT JOIN {{prefix}}planets AS `planet1` ON `planet1`.`id` = `fleet_start_id` ";
	$QryFleet.= "LEFT JOIN {{prefix}}planets AS `planet2` ON `planet2`.`id` = `fleet_end_id` ";
	$QryFleet.= "WHERE ";
	$QryFleet.= "( `fleet_start_time` <= UNIX_TIMESTAMP() OR `fleet_end_time` <= UNIX_TIMESTAMP() ) AND ";
	$QryFleet.= "( `fleet_start_id` = {$planet['id']} OR `fleet_end_id` = {$planet['id']} ";
	if(!empty($AdditionalIDs))
	{
		$QryFleet.= "OR `fleet_id` IN (".implode(',', $AdditionalIDs).") ";
	}
	$QryFleet.= "OR `fleet_owner` = {$planet['id_owner']} OR `fleet_target_owner` = {$planet['id_owner']} );";
	$FleetResult = doquery($QryFleet, 'fleets');

	if(!empty($_BenchTool)){ $_BenchTool->simpleCountStop(); }
	if(mysql_num_rows($FleetResult) > 0)
	{
		if(!empty($_BenchTool)){ $_BenchTool->simpleCountStart(false, 'telemetry__f1'); }
		
		include('MissionCheckCalculation.php');
		$Now = time();
		$RowNo = 0;
		while($ThisFleet = mysql_fetch_assoc($FleetResult))
		{
			$PrepareReturn = MissionCheckCalculation($ThisFleet, $Now);
			foreach($PrepareReturn as $Key => $Value)
			{
				if($Key == 'timeSort')
				{
					foreach($Value as $Key2 => $Value2)
					{
						$FleetTimes[$Key2] = array('rowNo' => $RowNo, 'type' => $Value2);
						$_FleetCache['fleetRowStatus'][$ThisFleet['fleet_id']]['calcCount'] += 1;
					}
				}
				elseif($Key == 'acsFleets')
				{
					foreach($Value as $Key2 => $Value2)
					{
						$PrepareData[$Key][$Key2] = $Value2;
					}
				}
				elseif($Key == 'taskData')
				{
					foreach($Value as $Value2)
					{
						if(empty($PrepareData[$Key]) OR !in_array($Value2, $PrepareData[$Key]))
						{
							$PrepareData[$Key][] = $Value2;
						}
					}
				}
				else
				{
					if(empty($PrepareData[$Key]) OR !in_array($Value, $PrepareData[$Key]))
					{
						$PrepareData[$Key][] = $Value;
					}
				}
			}
			$RowNo += 1;
		}
		if(!empty($FleetTimes))
		{
			ksort($FleetTimes, SORT_STRING);
			include('RestoreFleetToPlanet.php');
			doquery("LOCK TABLE {{table}}fleet_archive WRITE, {{table}}fleets AS `get_ids` WRITE, {{table}}acs WRITE, {{table}}battle_reports WRITE, {{table}}errors WRITE, {{table}}fleets WRITE, {{table}}planets WRITE, {{table}}galaxy WRITE ,{{table}}users WRITE, {{table}}users AS `users` WRITE, {{table}}alliance AS `ally` WRITE, {{table}}achievements_stats WRITE, {{table}}user_developmentlog WRITE", '');

			// --- Prepare $_FleetCache ---
			if(!empty($PrepareData['users']))
			{
				global $_User;
				if(in_array($_User['id'], $PrepareData['users']))
				{
					$Temp1 = array_keys($PrepareData['users'], $_User['id']);
					unset($PrepareData['users'][$Temp1[0]]);
					$_FleetCache['users'][$_User['id']] = $_User;
					if($_User['techQueue_firstEndTime'] > 0)
					{
						$PrepareData['planets'][] = $_User['techQueue_Planet'];
					}
					if($_User['ally_id'] > 0)
					{
						$Query_GetMyAllyTag = "SELECT `ally_tag` FROM {{table}} AS `ally` WHERE `id` = {$_User['ally_id']} LIMIT 1; -- FlyingFleetHandler [Q01]";
						$Result_GetMyAllyTag = doquery($Query_GetMyAllyTag, 'alliance', true);
						$_FleetCache['users'][$_User['id']]['ally_tag'] = $Result_GetMyAllyTag['ally_tag'];
					}
				}
				if(!empty($PrepareData['users']))
				{
					$Temp1 = implode(',', $PrepareData['users']);
					$Temp2 = count($PrepareData['users']);
					$Query_PrepUsers .= "SELECT `users`.*, `ally`.`ally_tag` FROM {{table}} AS `users` ";
					$Query_PrepUsers .= "LEFT JOIN `{{prefix}}alliance` AS `ally` ON `users`.`ally_id` = `ally`.`id` ";
					$Query_PrepUsers .= "WHERE `users`.`id` IN ({$Temp1}) LIMIT {$Temp2}; -- FlyingFleetHandler [Q02]";
					$Result_PrepUsers = doquery($Query_PrepUsers, 'users');
					while($FetchData = mysql_fetch_assoc($Result_PrepUsers))
					{
						$_FleetCache['users'][$FetchData['id']] = $FetchData;
						if($FetchData['techQueue_firstEndTime'] > 0)
						{
							$PrepareData['planets'][] = $FetchData['techQueue_Planet'];
						}
					}
				}
			}
			if(!empty($PrepareData['addPlanets']))
			{
				$Temp1 = implode(',', $PrepareData['addPlanets']);
				$Temp2 = count($PrepareData['addPlanets']);
				$Query_PrepAddPlanets = "SELECT `id_planet`, `id_moon` FROM {{table}} WHERE `id_moon` IN ({$Temp1}) LIMIT {$Temp2}; -- FlyingFleetHandler [Q03]";
				$Result_PrepAddPlanets = doquery($Query_PrepAddPlanets, 'galaxy');
				while($FetchData = mysql_fetch_assoc($Result_PrepAddPlanets))
				{
					$_FleetCache['moonPlanets'][$FetchData['id_moon']] = $FetchData['id_planet'];
					$PrepareData['planets'][] = $FetchData['id_planet'];
					$PrepareData['defFleets'][] = $FetchData['id_planet'];
				}
			}		
			if(!empty($PrepareData['planets']))
			{
				if(in_array($planet['id'], $PrepareData['planets']))
				{
					$Temp1 = array_keys($PrepareData['planets'], $planet['id']);
					unset($PrepareData['planets'][$Temp1[0]]);
					$_FleetCache['planets'][$planet['id']] = $planet;
				}
				if(!empty($PrepareData['planets']))
				{
					$Temp1 = implode(',', $PrepareData['planets']);
					$Temp2 = count($PrepareData['planets']);
					$Query_PrepPlanets = "SELECT * FROM {{table}} WHERE `id` IN ({$Temp1}) LIMIT {$Temp2}; -- FlyingFleetHandler [Q04]";
					$Result_PrepPlanets = doquery($Query_PrepPlanets, 'planets');
					while($FetchData = mysql_fetch_assoc($Result_PrepPlanets))
					{
						$_FleetCache['planets'][$FetchData['id']] = $FetchData;
					}
				}
			}
			if(!empty($PrepareData['defFleets']))
			{
				$Temp1 = implode(',', $PrepareData['defFleets']);
				$Query_PrepDefFleets .= "SELECT `fleet_id`, `fleet_owner`, `fleet_array`, `fleet_end_id`, `username`, `ally_tag`, ";
				$Query_PrepDefFleets .= "`morale_level`, `morale_droptime`, `morale_lastupdate`, ";
				$Query_PrepDefFleets .= "`tech_weapons`, `tech_armour`, `tech_shielding`, `tech_laser`, `tech_ion`, `tech_plasma`, `tech_antimatter`, ";
				$Query_PrepDefFleets .= "`tech_disintegration`, `tech_graviton`, `fleet_start_galaxy`, `fleet_start_system`, `fleet_start_planet` ";
				$Query_PrepDefFleets .= "FROM {{table}} ";
				$Query_PrepDefFleets .= "LEFT JOIN {{prefix}}users AS `users` ON `users`.`id` = `fleet_owner` ";
				$Query_PrepDefFleets .= "LEFT JOIN {{prefix}}alliance AS `ally` ON `users`.`ally_id` = `ally`.`id` ";
				$Query_PrepDefFleets .= "WHERE ";
				$Query_PrepDefFleets .= "`fleet_end_id` IN ({$Temp1}) AND ";
				$Query_PrepDefFleets .= "`fleet_mission` = 5 AND ";
				$Query_PrepDefFleets .= "`fleet_start_time` <= UNIX_TIMESTAMP() AND ";
				$Query_PrepDefFleets .= "`fleet_end_stay` > UNIX_TIMESTAMP(); -- FlyingFleetHandler [Q05]";
				$Result_PrepDefFleets = doquery($Query_PrepDefFleets, 'fleets');
				while($FetchData = mysql_fetch_assoc($Result_PrepDefFleets))
				{
					$_FleetCache['defFleets'][$FetchData['fleet_end_id']][$FetchData['fleet_id']] = $FetchData;
				}
			}
			if(!empty($PrepareData['acsFleets']))
			{
				$Temp1 = array();
				$Temp3 = array();
				foreach($PrepareData['acsFleets'] as $ACSFleetID => $Fleets)
				{
					foreach($Fleets as $FleetID)
					{
						$Temp1[] = $FleetID;
						$Temp3[$FleetID] = $ACSFleetID;
					}
				}
				$Temp2 = count($Temp1);
				$Temp1 = implode(',', $Temp1);			
				$Query_PrepACSFleets .= "SELECT `fleet_id`, `fleet_owner`, `fleet_array`, `username`, `ally_id`, `ally_tag`, ";
				$Query_PrepACSFleets .= "`morale_level`, `morale_points`, `morale_droptime`, `morale_lastupdate`, ";
				$Query_PrepACSFleets .= "`fleet_start_galaxy`, `fleet_start_system`, `fleet_start_planet`, `fleet_resource_metal`, `fleet_resource_crystal`, `fleet_resource_deuterium`, ";
				$Query_PrepACSFleets .= "`tech_weapons`, `tech_armour`, `tech_shielding`, `tech_laser`, `tech_ion`, `tech_plasma`, `tech_antimatter`, `tech_disintegration`, `tech_graviton` ";
				$Query_PrepACSFleets .= "FROM {{table}} ";
				$Query_PrepACSFleets .= "LEFT JOIN {{prefix}}users as `users` ON `users`.`id` = `fleet_owner` ";
				$Query_PrepACSFleets .= "LEFT JOIN {{prefix}}alliance AS `ally` ON `users`.`ally_id` = `ally`.`id` ";
				$Query_PrepACSFleets .= "WHERE `fleet_id` IN ({$Temp1}) LIMIT {$Temp2}; -- FlyingFleetHandler [Q06]";
				$Result_PrepACSFleets = doquery($Query_PrepACSFleets, 'fleets');
				while($FetchData = mysql_fetch_assoc($Result_PrepACSFleets))
				{
					$_FleetCache['acsFleets'][$Temp3[$FetchData['fleet_id']]][] = $FetchData;
					$PrepareData['taskData'][] = $FetchData['fleet_owner'];
				}
			}
			if(!empty($PrepareData['taskData']))
			{
				$Temp1 = array();
				foreach($PrepareData['taskData'] as $UserID)
				{
					if($UserID == $_User['id'])
					{
						$_FleetCache['userTasks'][$UserID] = $_User['tasks_done'];
					}
					elseif(!empty($PrepareData['users']) AND in_array($UserID, $PrepareData['users']))
					{
						$_FleetCache['userTasks'][$UserID] = $_FleetCache['users'][$UserID]['tasks_done'];
					}
					else
					{
						$Temp1[] = $UserID;
					}
				}
				if(!empty($Temp1))
				{
					$Temp2 = count($Temp1);
					$Temp1 = implode(',', $Temp1);
					$Query_PrepUserTasks .= "SELECT `id`, `tasks_done` FROM {{table}} ";
					$Query_PrepUserTasks .= "WHERE `id` IN ({$Temp1}) LIMIT {$Temp2}; -- FlyingFleetHandler [Q07]";
					$Result_PrepUserTasks = doquery($Query_PrepUserTasks, 'users');
					while($FetchData = mysql_fetch_assoc($Result_PrepUserTasks))
					{
						$_FleetCache['userTasks'][$FetchData['id']] = $FetchData['tasks_done'];
					}
				}
			}
			if(!empty($PrepareData['galaxy']))
			{
				global $_GalaxyRow;
				if(in_array($_GalaxyRow['galaxy_id'], $PrepareData['galaxy']))
				{
					$Temp1 = array_keys($PrepareData['galaxy'], $_GalaxyRow['galaxy_id']);
					unset($PrepareData['galaxy'][$Temp1[0]]);
					$_FleetCache['galaxy'][$_GalaxyRow['galaxy_id']] = $_GalaxyRow;
					$_FleetCache['galaxyMap']['byPlanet'][$_GalaxyRow['id_planet']] = $_GalaxyRow['galaxy_id'];
					if($_GalaxyRow['id_moon'] > 0)
					{
						$_FleetCache['galaxyMap']['byMoon'][$_GalaxyRow['id_moon']] = $_GalaxyRow['galaxy_id'];
					}
				}
				if(!empty($PrepareData['galaxy']))
				{
					$Temp1 = implode(',', $PrepareData['galaxy']);
					$Temp2 = count($PrepareData['galaxy']);
					$Query_PrepGalaxy = "SELECT `galaxy_id`, `id_planet`, `id_moon`, `metal`, `crystal` FROM {{table}} WHERE `galaxy_id` IN ({$Temp1}) LIMIT {$Temp2}; -- FlyingFleetHandler [Q08]";
					$Result_PrepGalaxy = doquery($Query_PrepGalaxy, 'galaxy');
					while($FetchData = mysql_fetch_assoc($Result_PrepGalaxy))
					{
						$_FleetCache['galaxy'][$FetchData['galaxy_id']] = $FetchData;
						$_FleetCache['galaxyMap']['byPlanet'][$FetchData['id_planet']] = $FetchData['galaxy_id'];
						if($FetchData['id_moon'] > 0)
						{
							$_FleetCache['galaxyMap']['byMoon'][$FetchData['id_moon']] = $FetchData['galaxy_id'];
						}
					}
				}
			}
			// ---
			$Fleets2Delete = array();
			foreach($FleetTimes as $CalculationData)
			{
				mysql_data_seek($FleetResult, $CalculationData['rowNo']);
				$CurrentFleet = mysql_fetch_assoc($FleetResult);
				$CurrentFleet['calcType'] = $CalculationData['type'];

				if(!empty($ChangeCoordinatesForFleets))
				{
					$FoundKey = array_search($CurrentFleet['fleet_start_id'], $ChangeCoordinatesForFleets);
					if($FoundKey > 0)
					{
						$ExplodeFoundKey = explode('<|>', $FoundKey);
						$CurrentFleet['fleet_start_id'] = $ExplodeFoundKey[0];
						$CurrentFleet['attacking_planet_name'] = $ExplodeFoundKey[1];
						$CurrentFleet['fleet_start_type'] = 1;
					}
					else
					{
						$FoundKey = array_search($CurrentFleet['fleet_end_id'], $ChangeCoordinatesForFleets);
						if($FoundKey > 0)
						{
							$ExplodeFoundKey = explode('<|>', $FoundKey);
							$CurrentFleet['fleet_end_id'] = $ExplodeFoundKey[0];
							$CurrentFleet['endtarget_planet_name'] = $ExplodeFoundKey[1];
							$CurrentFleet['fleet_end_type'] = 1;
							if($CurrentFleet['fleet_mission'] == 9)
							{
								$CurrentFleet['fleet_mission']= 1;
							}
						}
					}
				}

				$return = false;

				switch($CurrentFleet['fleet_mission'])
				{
					case 1:
						if($CurrentFleet['fleets_count'] > 0)
						{
							// Mission : Group Attack
							if($Inc_MissionGroupAttack !== true)
							{
								$Inc_MissionGroupAttack = true;
								include('MissionCaseGroupAttack.php');
							}
							if($Inc_Function_CreateOneMoonRecord !== true)
							{
								$Inc_Function_CreateOneMoonRecord = true;
								include('CreateOneMoonRecord.php');
							}
							$return = MissionCaseGroupAttack($CurrentFleet, $_FleetCache);
							if(!empty($return['DeleteACS']))
							{
								$ACS2Delete[] = $return['DeleteACS'];
							}
						}
						else
						{
							// Mission : Attack
							if($Inc_MissionAttack !== true)
							{
								$Inc_MissionAttack = true;
								include('MissionCaseAttack.php');
							}
							if($Inc_Function_CreateOneMoonRecord !== true)
							{
								$Inc_Function_CreateOneMoonRecord = true;
								include('CreateOneMoonRecord.php');
							}
							$return = MissionCaseAttack($CurrentFleet, $_FleetCache);
							if($CurrentFleet['acs_id'] > 0)
							{
								$ACS2Delete[] = $CurrentFleet['acs_id'];
							}
						}
						break;

					case 2:
						// Mission : Attack in Group (Joined fleet)
						// Make ReturnUpdate only if main fleet (GroupAttack Leader) was calculated
						if($CurrentFleet['calcType'] == 3)
						{
							if($Inc_MissionAttack !== true)
							{
								$Inc_MissionAttack = true;
								include('MissionCaseAttack.php');
							}
							$return = MissionCaseAttack($CurrentFleet, $_FleetCache);
						}
						break;

					case 3:
						// Mission: Transport
						if($Inc_MissionTransport !== true)
						{
							$Inc_MissionTransport = true;
							include('MissionCaseTransport.php');
						}
						if($CurrentFleet['calcType'] == 1 AND $Inc_Function_StoreGoodsToPlanet !== true)
						{
							$Inc_Function_StoreGoodsToPlanet = true;
							include('StoreGoodsToPlanet.php');
						}
						$return = MissionCaseTransport($CurrentFleet, $_FleetCache);
						break;

					case 4:
						// Mission: Stay
						if($Inc_MissionStay !== true)
						{
							$Inc_MissionStay = true;
							include('MissionCaseStay.php');
						}
						$return = MissionCaseStay($CurrentFleet, $_FleetCache);
						break;

					case 5:
						// Mission: Stay and Defend (for allys)
						if($Inc_MissionStayAlly !== true)
						{
							$Inc_MissionStayAlly = true;
							include('MissionCaseStayAlly.php');
						}
						$return = MissionCaseStayAlly($CurrentFleet, $_FleetCache);
						break;

					case 6:
						// Mission: Spy
						if($Inc_MissionSpy !== true)
						{
							$Inc_MissionSpy = true;
							include('MissionCaseSpy.php');
						}
						$return = MissionCaseSpy($CurrentFleet, $_FleetCache);
						break;

					case 7:
						// Mission: Colonisation
						if($Inc_MissionColonisation !== true)
						{
							$Inc_MissionColonisation = true;
							include('MissionCaseColonisation.php');
							include('CreateOnePlanetRecord.php');
						}
						$return = MissionCaseColonisation($CurrentFleet, $_FleetCache);
						break;

					case 8:
						// Mission: Recycling
						if($Inc_MissionRecycling !== true)
						{
							$Inc_MissionRecycling = true;
							include('MissionCaseRecycling.php');
						}
						$return = MissionCaseRecycling($CurrentFleet, $_FleetCache);
						break;

					case 9:
						// Mission: Moon destruction
						if($Inc_MissionDestruction !== true)
						{
							$Inc_MissionDestruction = true;
							include('MissionCaseDestruction.php');
						}
						if($Inc_Function_CreateOneMoonRecord !== true)
						{
							$Inc_Function_CreateOneMoonRecord = true;
							include('CreateOneMoonRecord.php');
						}
						$return = MissionCaseDestruction($CurrentFleet, $_FleetCache);
						if($return['MoonDestroyed'] === true)
						{
							if($CurrentFleet['fleet_end_id'] == $planet['id'])
							{
								$FleetHandlerReturn['ThisMoonDestroyed'] = true;
							}
						}
						break;

					case 10:
						// Mission: Interplanetar Attack
						if($Inc_MissionMIP !== true)
						{
							$Inc_MissionMIP = true;
							include('MissionCaseMIP.php');
						}
						$return = MissionCaseMIP($CurrentFleet, $_FleetCache);				
						break;

					case 15:
						// Mission: Expedition
						if($Inc_MissionExpedition === false)
						{
							$Inc_MissionExpedition = true;
							include('MissionCaseExpedition.php');
						}
						$return = MissionCaseExpedition($CurrentFleet);
						break;

					default: 
						// Bad mission number!!!
						$Fleets2Delete[] = $CurrentFleet['fleet_id'];
						break;
				}
				
				if(!empty($return['FleetsToDelete']))
				{
					foreach($return['FleetsToDelete'] as $ThisID)
					{
						if(!in_array($ThisID, $Fleets2Delete))
						{
							$Fleets2Delete[] = $ThisID;
						}
					}
				}
				
				if(!empty($return['FleetArchive']))
				{
					foreach($return['FleetArchive'] as $FleetID => $ArchiveData)
					{
						$ThisResult = array_merge($FleetArchive_Pattern, $ArchiveData);
						if(empty($Updater_FleetArchive[$FleetID]))
						{
							$Updater_FleetArchive[$FleetID] = $ThisResult;
						}
						else
						{
							$Pointer = &$Updater_FleetArchive[$FleetID];
							foreach($ThisResult as $Key => $Value)
							{
								if($Value !== null)
								{
									if($Pointer[$Key] !== null)
									{
										if(substr($Pointer[$Key], 0, 1) != '!')
										{
											if(substr($Value, 0, 2) == '"+')
											{
												$Value = rtrim($Pointer[$Key], '"').str_replace('"+', '', $Value);
											}
											$Pointer[$Key] = $Value;
										}
									}
									else
									{
										$Pointer[$Key] = $Value;
									}
								}
							}
						}
					}
				}
			}

			// $_FleetCache Handler
			if(!empty($_FleetCache['updatePlanets']))
			{
				foreach($_FleetCache['updatePlanets'] as $ThisID => $ThisData)
				{
					if($ThisData === true)
					{
						$Results['planets'][] = &$_FleetCache['planets'][$ThisID];
					}
				}
				$Results['users'] = &$_FleetCache['users'];
				HandlePlanetUpdate_MultiUpdate($Results, array(), true, true);
				$Results = null;
			}
			if(!empty($_FleetCache['addToPlanets']['data']))
			{
				$TempArray = array();
				$TempArray3 = array();
				$TempArray4 = array();
				foreach($_FleetCache['addToPlanets']['data'] as $ThisID => $ThisData)
				{
					$TempArray2 = array();
					foreach($_FleetCache['addToPlanets']['fields'] as $ThisField)
					{
						$TempArray2[] = (empty($ThisData[$ThisField]) ? '0' : $ThisData[$ThisField]);
					}
					$TempArray2 = implode(', ', $TempArray2);
					$TempArray[] = "({$ThisID}, {$TempArray2})";
				}
				foreach($_FleetCache['addToPlanets']['fields'] as $ThisField)
				{
					$TempArray3[] = "`{$ThisField}`";
					$TempArray4[] = "`{$ThisField}` = `{$ThisField}` + VALUES(`{$ThisField}`)";
				}
				$TempArray = implode(', ', $TempArray);
				$TempArray3 = implode(', ', $TempArray3);
				$TempArray4 = implode(', ', $TempArray4);
				$Query_AddToPlanets .= "INSERT INTO {{table}} (`id`, {$TempArray3}) VALUES {$TempArray}";
				$Query_AddToPlanets .= " ON DUPLICATE KEY UPDATE {$TempArray4}; -- FlyingFleetHandler [Q09]";
				doquery($Query_AddToPlanets, 'planets');
			}			
			if(!empty($_FleetCache['galaxy']) AND $_FleetCache['updated']['galaxy'] === true)
			{
				$TempArray = array();		
				foreach($_FleetCache['galaxy'] as $ThisID => $ThisData)
				{
					if($ThisData['updated'] === true)
					{
						$TempArray[] = "({$ThisID}, {$ThisData['metal']}, {$ThisData['crystal']})";
					}
				}
				if(!empty($TempArray))
				{
					$Query_UpdCachedGalaxy .= "INSERT INTO {{table}} (`galaxy_id`, `metal`, `crystal`) VALUES ";
					$Query_UpdCachedGalaxy .= implode(', ', $TempArray);
					$Query_UpdCachedGalaxy .= " ON DUPLICATE KEY UPDATE ";
					$Query_UpdCachedGalaxy .= "`metal` = VALUES(`metal`), ";
					$Query_UpdCachedGalaxy .= "`crystal` = VALUES(`crystal`); -- FlyingFleetHandler [Q10]";
					doquery($Query_UpdCachedGalaxy, 'galaxy');
				}
			}
			if(!empty($_FleetCache['deleteMoons']))
			{
				$Temp2 = count($_FleetCache['deleteMoons']);
				$Temp1 = implode(',', $_FleetCache['deleteMoons']);
				$Query_DeleteMoons = "DELETE FROM {{table}} WHERE `id` IN ({$Temp1}) LIMIT {$Temp2}; -- FlyingFleetHandler [Q11]";
				doquery($Query_DeleteMoons, 'planets');
			}
			if(!empty($_FleetCache['moonGalaxyUpdate']))
			{
				$Temp2 = count($_FleetCache['moonGalaxyUpdate']);
				$Temp1 = implode(',', $_FleetCache['moonGalaxyUpdate']);
				$Query_UpdMoonGalaxy = "UPDATE {{table}} SET `id_moon` = 0 WHERE `id_planet` IN ({$Temp1}) LIMIT {$Temp2}; -- FlyingFleetHandler [Q12]";
				doquery($Query_UpdMoonGalaxy, 'galaxy');
			}
			if(!empty($_FleetCache['moonUserUpdate']))
			{
				$TempArray = array();
				foreach($_FleetCache['moonUserUpdate'] as $ThisUser => $ThisID)
				{
					$TempArray[] = "({$ThisUser}, {$ThisID})";
				}
				$TempArray = implode(',', $TempArray);
				$Query_UpdMoonUser .= "INSERT INTO {{table}} (`id`, `current_planet`) VALUES {$TempArray} ";
				$Query_UpdMoonUser .= "ON DUPLICATE KEY UPDATE `current_planet` = VALUES(`current_planet`); -- FlyingFleetHandler [Q13]";
				doquery($Query_UpdMoonUser, 'users');
			}
			if(!empty($_FleetCache['MoraleCache']))
			{
				$TempArray = array();
				foreach($_FleetCache['MoraleCache'] as $ThisUserID => $ThisData)
				{
					$TempArray[] = "({$ThisUserID}, {$ThisData['level']}, {$ThisData['droptime']}, {$ThisData['lastupdate']})";
				}
				$TempArray = implode(',', $TempArray);
				$Query_UpdUserMorale .= "INSERT INTO {{table}} (`id`, `morale_level`, `morale_droptime`, `morale_lastupdate`) VALUES {$TempArray} ";
				$Query_UpdUserMorale .= "ON DUPLICATE KEY UPDATE ";
				$Query_UpdUserMorale .= "`morale_level` = VALUES(`morale_level`), ";
				$Query_UpdUserMorale .= "`morale_droptime` = VALUES(`morale_droptime`), ";
				$Query_UpdUserMorale .= "`morale_lastupdate` = VALUES(`morale_lastupdate`); -- FlyingFleetHandler [Q14]";
				doquery($Query_UpdUserMorale, 'users');
			}
			
			if(!empty($_FleetCache['updateFleets']))
			{
				$TempArray = array();
				foreach($_FleetCache['updateFleets'] as $ThisID => $ThisData)
				{
					if(empty($ThisData['fleet_array']))
					{
						$ThisData['fleet_array'] = "''";
						$ThisData['fleet_amount'] = '0';
					}
					else
					{
						$ThisData['fleet_array'] = "'{$ThisData['fleet_array']}'";
					}
					if(empty($ThisData['fleet_mess']))
					{
						$ThisData['fleet_mess'] = '0';
					}
					if(empty($ThisData['fleet_resource_metal']))
					{
						$ThisData['fleet_resource_metal'] = '0';
					}
					if(empty($ThisData['fleet_resource_crystal']))
					{
						$ThisData['fleet_resource_crystal'] = '0';
					}
					if(empty($ThisData['fleet_resource_deuterium']))
					{
						$ThisData['fleet_resource_deuterium'] = '0';
					}
					$TempArray[] = "({$ThisID}, {$ThisData['fleet_amount']}, {$ThisData['fleet_array']}, {$ThisData['fleet_mess']}, {$ThisData['fleet_resource_metal']}, {$ThisData['fleet_resource_crystal']}, {$ThisData['fleet_resource_deuterium']})";
				}
				$TempArray = implode(',', $TempArray);
				$Query_UpdateFleets .= "INSERT INTO {{table}} (`fleet_id`, `fleet_amount`, `fleet_array`, `fleet_mess`, `fleet_resource_metal`, `fleet_resource_crystal`, `fleet_resource_deuterium`) ";
				$Query_UpdateFleets .= "VALUES {$TempArray} ";
				$Query_UpdateFleets .= "ON DUPLICATE KEY UPDATE ";
				$Query_UpdateFleets .= "`fleet_amount` = IF(VALUES(`fleet_amount`) = 0, `fleet_amount`, VALUES(`fleet_amount`)), ";
				$Query_UpdateFleets .= "`fleet_array` = IF(VALUES(`fleet_array`) = '', `fleet_array`, VALUES(`fleet_array`)), ";
				$Query_UpdateFleets .= "`fleet_mess` = IF(VALUES(`fleet_mess`) = 0, `fleet_mess`, VALUES(`fleet_mess`)), ";
				$Query_UpdateFleets .= "`fleet_resource_metal` = `fleet_resource_metal` + VALUES(`fleet_resource_metal`), ";
				$Query_UpdateFleets .= "`fleet_resource_crystal` = `fleet_resource_crystal` + VALUES(`fleet_resource_crystal`), ";
				$Query_UpdateFleets .= "`fleet_resource_deuterium` = `fleet_resource_deuterium` + VALUES(`fleet_resource_deuterium`); -- FlyingFleetHandler [Q14]";
				doquery($Query_UpdateFleets, 'fleets');
			}
			// ---
			
			if($FleetHandlerReturn['ThisMoonDestroyed'] !== true)
			{
				if(!empty($_FleetCache['planets'][$planet['id']]))
				{
					$planet = $_FleetCache['planets'][$planet['id']];
				}
				else
				{
					$planet = doquery("SELECT * FROM {{table}} WHERE `id` = {$planet['id']};", 'planets', true);
				}
			}
			if($_GalaxyRow['galaxy_id'] > 0 AND !empty($_FleetCache['galaxy'][$_GalaxyRow['galaxy_id']]))
			{
				$_GalaxyRow['metal'] = $_FleetCache['galaxy'][$_GalaxyRow['galaxy_id']]['metal'];
				$_GalaxyRow['crystal'] = $_FleetCache['galaxy'][$_GalaxyRow['galaxy_id']]['crystal'];
			}

			if(!empty($Updater_FleetArchive))
			{
				$Qry_Updater_FleetArchive = "INSERT INTO {{table}} VALUES ";
				foreach($Updater_FleetArchive as $FleetID => $Data)
				{
					$Qry_Updater_FleetArchive_Row = "({$FleetID}, ";
					foreach($Data as &$DataVal)
					{
						if($DataVal === null)
						{
							$DataVal = '"!noupd!"';
						}
						elseif($DataVal === 0)
						{
							$DataVal = '0';
						}
						elseif($DataVal === '')
						{
							$DataVal = '""';
						}
						elseif($DataVal === true)
						{
							$DataVal = 'true';
						}
						elseif($DataVal === false)
						{
							$DataVal = 'false';
						}
						elseif(substr($DataVal, 0, 1) == '!')
						{
							$DataVal = preg_replace('/^\!/', '', $DataVal);
						}
					}
					$Qry_Updater_FleetArchive_Row .= implode(', ', $Data);
					$Qry_Updater_FleetArchive_Row .= ')';
					$Qry_Updater_FleetArchive_Array[] = $Qry_Updater_FleetArchive_Row;
				}
				$Qry_Updater_FleetArchive .= implode(', ', $Qry_Updater_FleetArchive_Array);
				$Qry_Updater_FleetArchive .= " ON DUPLICATE KEY UPDATE ";
				foreach($FleetArchive_Fields as $FieldName)
				{
					$Qry_Updater_FleetArchive_Array2[] = "`{$FieldName}` = IF((VALUES(`{$FieldName}`) = \"!noupd!\"), `{$FieldName}`, IF(SUBSTRING(VALUES(`{$FieldName}`), 1, 1) = '+', CONCAT(`{$FieldName}`, SUBSTRING(VALUES(`{$FieldName}`), 2)), VALUES(`{$FieldName}`)))";
				}
				$Qry_Updater_FleetArchive .= implode(', ', $Qry_Updater_FleetArchive_Array2);
				doquery($Qry_Updater_FleetArchive, 'fleet_archive');
			}

			if(!empty($UserStatsData))
			{
				$QryUsersStats = "INSERT INTO {{table}} (`A_UserID`, `ustat_raids_won`, `ustat_raids_draw`, `ustat_raids_lost`, `ustat_raids_acs_won`, `ustat_raids_inAlly`, `ustat_raids_missileAttack`, `ustat_moons_destroyed`, `ustat_moons_created`, `ustat_other_expeditions_count`, {$CreateAchievementKeys}) VALUES ";
				foreach($UserStatsData as $UserID => $Data)
				{
					if($UserID <= 0)
					{
						continue;
					}
					foreach($Data as &$Value)
					{
						if($Value == '')
						{
							$Value = '""';
						}
						elseif($Value === 0)
						{
							$Value = '0';
						}
					}
					$QryUsersStatsA[] = "({$UserID}, ".implode(', ', $Data).")";
				}
				$QryUsersStats .= implode(', ', $QryUsersStatsA);
				$QryUsersStats .= " ON DUPLICATE KEY UPDATE ";
				$QryUsersStats .= "`ustat_raids_won` = `ustat_raids_won` + VALUES(`ustat_raids_won`), ";
				$QryUsersStats .= "`ustat_raids_draw` = `ustat_raids_draw` + VALUES(`ustat_raids_draw`), ";
				$QryUsersStats .= "`ustat_raids_lost` = `ustat_raids_lost` + VALUES(`ustat_raids_lost`), ";
				$QryUsersStats .= "`ustat_raids_acs_won` = `ustat_raids_acs_won` + VALUES(`ustat_raids_acs_won`), ";
				$QryUsersStats .= "`ustat_raids_inAlly` = `ustat_raids_inAlly` + VALUES(`ustat_raids_inAlly`), ";
				$QryUsersStats .= "`ustat_raids_missileAttack` = `ustat_raids_missileAttack` + VALUES(`ustat_raids_missileAttack`), ";
				$QryUsersStats .= "`ustat_moons_destroyed` = `ustat_moons_destroyed` + VALUES(`ustat_moons_destroyed`), ";
				$QryUsersStats .= "`ustat_moons_created` = `ustat_moons_created` + VALUES(`ustat_moons_created`), ";
				$QryUsersStats .= "`ustat_other_expeditions_count` = `ustat_other_expeditions_count` + VALUES(`ustat_other_expeditions_count`)";
				if(!empty($UserStatsUpQuery))
				{
				$QryUsersStats .= ", ".implode(', ', $UserStatsUpQuery);
				}
				$QryUsersStats .= ';';
				doquery($QryUsersStats, 'achievements_stats');
			}

			if(!empty($ACS2Delete))
			{
				doquery("DELETE FROM {{table}} WHERE `id` IN (".implode(', ', $ACS2Delete).");", 'acs');
			}
			if(!empty($Fleets2Delete))
			{
				doquery("DELETE FROM {{table}} WHERE `fleet_id` IN (".implode(', ', $Fleets2Delete).");", 'fleets');
			}
			doquery('UNLOCK TABLES', '');

			if(!empty($_Cache['Messages']))
			{
				SendSimpleMultipleMessages($_Cache['Messages']);
				$_Cache['Messages'] = array();
			}
		}
		
		if(!empty($_BenchTool)){ $_BenchTool->simpleCountStop(); }

		return $FleetHandlerReturn;
	}
}

?>