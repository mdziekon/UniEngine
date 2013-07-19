<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename	= 'admin/';
$_SetAccessLogPath			= '../';
$_EnginePath			= '../';

include($_EnginePath.'common.php');
	
	if(!CheckAuth('go'))
	{
		message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
	}

	includeLang('admin/bashDetector');
	$TPL_Body = gettemplate('admin/bashDetector_body');
	$Now = time();
	$_BashLimit_PerPlanet = BASH_PERPLANET;
	$_BashLimit_PerUser = BASH_PERUSER;
	$_Colspan = 5;
	
	if(!empty($_GET['sender']))
	{
		$_Lang['Insert_srch_sender'] = $_GET['sender'];
	}
	if(!empty($_GET['owner']))
	{
		$_Lang['Insert_srch_owner'] = $_GET['owner'];
	}
	if(!empty($_GET['date']))
	{
		$_Lang['Insert_srch_date'] = $_GET['date'];
	}
	
	$_Lang['Insert_HideResults'] = 'display: none;';
	if($_POST['send'] == 1)
	{
		$_Lang['Insert_HideResults'] = '';
		
		$Filter['sender'] = trim($_POST['srch_sender']);
		$Filter['owner'] = trim($_POST['srch_owner']);
		$Filter['date'] = trim($_POST['srch_date']);
		if(!empty($Filter['sender']))
		{
			if(strstr($Filter['sender'], '[') !== false)
			{
				$Filter['sender'] = intval(str_replace(array('[', ']'), '', $Filter['sender']));
				if($Filter['sender'] > 0)
				{
					$Query_Where['Fleet_Owner'] = "`Fleet_Owner` = {$Filter['sender']}";
					$_Lang['Insert_srch_sender'] = "[{$Filter['sender']}]";
					$GetUsernames[] = $Filter['sender'];
					$Set['SenderID'] = $Filter['sender'];
				}
			}
			elseif(preg_match(REGEXP_USERNAME_ABSOLUTE, $Filter['sender']))
			{
				$Query_GetUser = doquery("SELECT `id`, `username` FROM {{table}} WHERE `username` = '{$Filter['sender']}' LIMIT 1;", 'users', true);
				if($Query_GetUser['id'] > 0)
				{
					$Query_Where['Fleet_Owner'] = "`Fleet_Owner` = {$Query_GetUser['id']}";
					$_Lang['Insert_srch_sender'] = $Query_GetUser['username'];
					$Usernames[$Query_GetUser['id']] = $Query_GetUser['username'];
					$Set['SenderID'] = $Query_GetUser['id'];
				}
				else
				{
					$_Lang['Insert_BashOverallResult'][] = $_Lang['Analysis_BadSenderName'];
				}
			}
		}
		if(!empty($Filter['owner']))
		{
			if(strstr($Filter['owner'], '[') !== false)
			{
				$Filter['owner'] = intval(str_replace(array('[', ']'), '', $Filter['owner']));
				if($Filter['owner'] > 0)
				{
					$Query_Where['Fleet_End_Owner'] = "`Fleet_End_Owner` = {$Filter['owner']}";
					$_Lang['Insert_srch_owner'] = "[{$Filter['owner']}]";
					$GetUsernames[] = $Filter['owner'];
					$Set['OwnerID'] = $Filter['owner'];
				}
			}
			elseif(preg_match(REGEXP_USERNAME_ABSOLUTE, $Filter['owner']))
			{
				$Query_GetUser = doquery("SELECT `id`, `username` FROM {{table}} WHERE `username` = '{$Filter['owner']}' LIMIT 1;", 'users', true);
				if($Query_GetUser['id'] > 0)
				{
					$Query_Where['Fleet_End_Owner'] = "`Fleet_End_Owner` = {$Query_GetUser['id']}";
					$_Lang['Insert_srch_owner'] = $Query_GetUser['username'];
					$Usernames[$Query_GetUser['id']] = $Query_GetUser['username'];
					$Set['OwnerID'] = $Query_GetUser['id'];
				}
				else
				{
					$_Lang['Insert_BashOverallResult'][] = $_Lang['Analysis_BadOwnerName'];
				}
			}
		}
		if(!empty($Filter['date']))
		{
			$Filter['timestamp'] = strtotime($Filter['date']);
			if($Filter['timestamp'] != false AND $Filter['timestamp'] < $Now)
			{
				$Filter['timestampEnd'] = $Filter['timestamp'] + TIME_DAY;
				$Query_Where['Fleet_Time_Start'] = "(`Fleet_Time_Start` + `Fleet_Time_ACSAdd`) BETWEEN {$Filter['timestamp']} AND {$Filter['timestampEnd']}";
				$_Lang['Insert_srch_date'] = $Filter['date'];
				$Set['Date'] = $Filter['timestamp'];
			}
		}
		
		if(!empty($Query_Where['Fleet_Owner']) AND !empty($Query_Where['Fleet_End_Owner']) AND !empty($Query_Where['Fleet_Time_Start']))
		{
			if(!empty($GetUsernames))
			{
				$GetUsernamesCount = count($GetUsernames);
				$GetUsernames = implode(',', $GetUsernames);
				$Query_GetUsernames = "SELECT `id`, `username` FROM {{table}} WHERE `id` IN ({$GetUsernames}) LIMIT {$GetUsernamesCount};";
				$Result_GetUsernames = doquery($Query_GetUsernames, 'users');
				if(mysql_num_rows($Result_GetUsernames) > 0)
				{
					while($FetchData = mysql_fetch_assoc($Result_GetUsernames))
					{
						$Usernames[$FetchData['id']] = $FetchData['username'];
					}
				}
			}
			if(empty($Usernames[$Set['SenderID']]))
			{
				$Usernames[$Set['SenderID']] = $_Lang['UserRow_Deleted'];
			}
			if(empty($Usernames[$Set['OwnerID']]))
			{
				$Usernames[$Set['OwnerID']] = $_Lang['UserRow_Deleted'];
			}
			
			$ThisArray = array($Usernames[$Set['SenderID']], $Set['SenderID'], $Set['SenderID'], $Usernames[$Set['OwnerID']], $Set['OwnerID'], $Set['OwnerID'], prettyDate('d m Y', $Set['Date'], 1));
			$_Lang['Insert_BashOverallResult'][] = vsprintf($_Lang['Analysis_Info'], $ThisArray);
			
			$Query_Where[] = "`Fleet_Mission` IN (1, 2, 9)";
			$Query_Where[] = "`Fleet_End_Owner_IdleHours` < 168";
			$Query_Where[] = "`Fleet_ReportID` > 0";
			$Query_Where[] = "`Fleet_Destroyed_Reason` NOT IN (1, 4, 11)";
			
			$Query_GetFleets .= "SELECT `Fleet_ID`, `Fleet_Mission`, `Fleet_Time_Start`, `Fleet_End_ID`, `Fleet_ReportID` ";
			$Query_GetFleets .= "FROM {{table}} WHERE ";
			$Query_GetFleets .= implode(' AND ', $Query_Where);
			
			$Result_GetFleets = doquery($Query_GetFleets, 'fleet_archive');
			if(mysql_num_rows($Result_GetFleets) > 0)
			{
				$TPL_FleetRow = gettemplate('admin/bashDetector_fleetrow');
				$GetTargets = array();
				$BashCountersUser = 0;
				$BashCountersPlanet = array();
				$FoundBash = false;
				
				while($FetchRow = mysql_fetch_assoc($Result_GetFleets))
				{
					$BashCountersUser += 1;
					$BashCountersPlanet[$FetchRow['Fleet_End_ID']] += 1;
					
					if($BashCountersUser > $_BashLimit_PerUser)
					{
						$FoundBash = true;
					}
					elseif($BashCountersPlanet[$FetchRow['Fleet_End_ID']] > $_BashLimit_PerPlanet)
					{
						$FoundBash = true;
					}
					
					$FetchRow['Fleet_Date'] = prettyDate('d m Y, H:i:s', $FetchRow['Fleet_Time_Start'], 1);
					$FetchRow['Fleet_Mission'] = $_Lang['type_mission'][$FetchRow['Fleet_Mission']];
					
					if(!in_array($FetchRow['Fleet_End_ID'], $GetTargets))
					{
						$GetTargets[] = $FetchRow['Fleet_End_ID'];
					}
					
					$FleetRow[] = $FetchRow;
				}
				if(!empty($GetTargets))
				{
					$GetTargetsCount = count($GetTargets);
					$GetTargets = implode(',', $GetTargets);
					$Query_GetTargets .= "SELECT `id`, `name`, `galaxy`, `system`, `planet`, `planet_type` FROM {{table}} ";
					$Query_GetTargets .= "WHERE `id` IN ({$GetTargets}) LIMIT {$GetTargetsCount};";
					
					$Result_GetTargets = doquery($Query_GetTargets, 'planets');
					if(mysql_num_rows($Result_GetTargets) > 0)
					{
						while($FetchData = mysql_fetch_assoc($Result_GetTargets))
						{
							$Targets[$FetchData['id']] = $FetchData;
						}
					}
				}
				
				if($FoundBash === true)
				{
					$_Lang['Insert_BashOverallResult'][] = sprintf($_Lang['Analysis_BashFound'], $Set['SenderID']);
					
					if($BashCountersUser > $_BashLimit_PerUser)
					{
						$BashedUser = true;
						$_Lang['Insert_BashList'][] = sprintf($_Lang['Analysis_List_UserBash'], $BashCountersUser, $_BashLimit_PerUser);
					}
					foreach($BashCountersPlanet as $PlanetID => $Count)
					{
						if($Count > $_BashLimit_PerPlanet)
						{
							$BashedPlanets[$PlanetID] = true;
							
							if(!empty($Targets[$PlanetID]['name']))
							{
								$ThisArray = array($Targets[$PlanetID]['name'], $Targets[$PlanetID]['galaxy'], $Targets[$PlanetID]['system'], $Targets[$PlanetID]['planet'], $_Lang['FleetRow_PlanetTypes'][$Targets[$PlanetID]['planet_type']], $PlanetID);
							}
							else
							{
								$ThisArray = array($_Lang['FleetRow_PlanetDeleted'], '0', '0', '0', '-', $PlanetID);
							}
							$ThisArray[] = $Count;
							$ThisArray[] = $_BashLimit_PerPlanet;
							$_Lang['Insert_BashList'][] = vsprintf($_Lang['Analysis_List_PlanetBash'], $ThisArray);
						}
					}
					if(!empty($_Lang['Insert_BashList']))
					{
						$_Lang['Insert_BashList'] = '<br/>'.implode('<br/>', $_Lang['Insert_BashList']);
					}
				}
				else
				{
					$_Lang['Insert_BashOverallResult'][] = $_Lang['Analysis_BashNotFound'];
				}
				
				foreach($FleetRow as $FleetData)
				{
					if(!empty($Targets[$FleetData['Fleet_End_ID']]['name']))
					{
						$FleetData['Fleet_TargetName'] = $Targets[$FleetData['Fleet_End_ID']]['name'];
						$FleetData['Fleet_TargetGalaxy'] = $Targets[$FleetData['Fleet_End_ID']]['galaxy'];
						$FleetData['Fleet_TargetSystem'] = $Targets[$FleetData['Fleet_End_ID']]['system'];
						$FleetData['Fleet_TargetPlanet'] = $Targets[$FleetData['Fleet_End_ID']]['planet'];
						$FleetData['Fleet_TargetType'] = $_Lang['FleetRow_PlanetTypes'][$Targets[$FleetData['Fleet_End_ID']]['planet_type']];
					}
					else
					{
						$FleetData['Fleet_TargetName'] = $_Lang['FleetRow_PlanetDeleted'];
						$FleetData['Fleet_TargetGalaxy'] = '0';
						$FleetData['Fleet_TargetSystem'] = '0';
						$FleetData['Fleet_TargetPlanet'] = '0';
						$FleetData['Fleet_TargetType'] = '-';
					}
					if($BashedUser !== true AND $BashedPlanets[$FleetData['Fleet_End_ID']] === true)
					{
						$FleetData['BashClass'] = 'red';
					}
					
					$_Lang['Insert_FleetRows'][] = parsetemplate($TPL_FleetRow, $FleetData);
				}
			}
			else
			{
				$_Lang['Insert_BashOverallResult'][] = $_Lang['Analysis_BashNotFound'];
				$_Lang['Insert_FleetRows'][] = parsetemplate(gettemplate('_singleRow'), array('Classes' => 'orange pad5', 'Colspan' => $_Colspan, 'Text' => $_Lang['Info_NoFleetRows']));
			}
		}
		else
		{
			$_Lang['Insert_BashOverallResult'][] = $_Lang['Analysis_BadInput'];
			$_Lang['Insert_HideFleetRows'] = 'display: none;';
		}
	}
	if(!empty($_Lang['Insert_BashOverallResult']))
	{
		$_Lang['Insert_BashOverallResult'] = implode('<br/>', $_Lang['Insert_BashOverallResult']);
	}
	if(!empty($_Lang['Insert_FleetRows']))
	{
		$_Lang['Insert_FleetRows'] = implode('', $_Lang['Insert_FleetRows']);
	}
	
	if(empty($_Lang['Insert_srch_date']))
	{
		$_Lang['Insert_srch_date'] = date('Y-m-d');
	}
	
	display(parsetemplate($TPL_Body, $_Lang), $_Lang['Page_Title'], false, true);

?>