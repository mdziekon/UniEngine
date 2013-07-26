<?php

// Important functions
function ReadFromFile($filename)
{
	return @file_get_contents($filename);
}

function parsetemplate($template, $array)
{
	return preg_replace('#\{([a-z0-9\-_]*?)\}#Ssie', '( ( isset($array[\'\1\']) ) ? $array[\'\1\'] : \'\' );', $template);
}

function gettemplate($templatename)
{
	global $_EnginePath;

	return ReadFromFile($_EnginePath.TEMPLATE_DIR.TEMPLATE_NAME.'/'.$templatename.'.tpl');
}

function includeLang($filename, $Return = false)
{
	global $_EnginePath, $_User;

	if(!$Return)
	{
		global $_Lang;
	}
	
	$SelLanguage = DEFAULT_LANG;
	if(isset($_User['lang']) && $_User['lang'] != '')
	{
		$SelLanguage = $_User['lang'];
	}
	else
	{
		$SelLanguage = DEFAULT_LANG;
	}
	include("{$_EnginePath}language/{$SelLanguage}/{$filename}.lang");

	if($Return)
	{
		return $_Lang;
	}
}

// Fleet-related functions
function GetTargetDistance($OrigGalaxy, $DestGalaxy, $OrigSystem, $DestSystem, $OrigPlanet, $DestPlanet)
{
	$distance = 0;

	if(($OrigGalaxy - $DestGalaxy) != 0)
	{
		$distance = abs($OrigGalaxy - $DestGalaxy) * 20000;
	}
	else if(($OrigSystem - $DestSystem) != 0)
	{
		$distance = abs($OrigSystem - $DestSystem) * 5 * 19 + 2700;
	}
	else if(($OrigPlanet - $DestPlanet) != 0)
	{
		$distance = abs($OrigPlanet - $DestPlanet) * 5 + 1000;
	}
	else
	{
		$distance = 5;
	}

	return $distance;
}

function GetMissionDuration($GameSpeed, $MaxFleetSpeed, $Distance, $SpeedFactor)
{
	$Duration = round(((35000 / $GameSpeed * sqrt($Distance * 10 / $MaxFleetSpeed) + 10) / $SpeedFactor));

	return $Duration;
}

function GetGameSpeedFactor()
{
	global $_GameConfig;

	return $_GameConfig['fleet_speed'] / 2500;
}

function GetFleetMaxSpeed($FleetArray, $Fleet, $Player, $ReturnInfo = false)
{
	global $_Vars_Prices, $_Vars_GameElements, $_Vars_TechSpeedModifiers;

	if($Fleet != 0)
	{
		$FleetArray = array($Fleet => 1);
	}
	
	foreach($FleetArray as $Ship => $Count)
	{
		if(!empty($_Vars_Prices[$Ship]['engine']))
		{
			foreach($_Vars_Prices[$Ship]['engine'] as $EngineID => $EngineData)
			{
				if($Player[$_Vars_GameElements[$EngineData['tech']]] >= $EngineData['minlevel'])
				{
					$speedalls[$Ship] = $EngineData['speed'] * (1 + ($_Vars_TechSpeedModifiers[$EngineData['tech']] * $Player[$_Vars_GameElements[$EngineData['tech']]]));
					if($ReturnInfo === true)
					{
						$EngineData['engineID'] = $EngineID;
						$Return[$Ship]['engine'] = $EngineData;
					}
					break;
				}
			}
		}
		else
		{		
			$speedalls[$Ship] = 0;
		}
	}
	if($Fleet != 0)
	{
		$speedalls = $speedalls[$Ship];
	}
	
	if($ReturnInfo === true)
	{
		return array('speed' => $speedalls, 'info' => $Return);
	}	
	return $speedalls;
}

function GetShipConsumption($Ship, $Player)
{
	global $_Vars_Prices, $_Vars_GameElements;
	
	if(!empty($_Vars_Prices[$Ship]['engine']))
	{
		foreach($_Vars_Prices[$Ship]['engine'] as $EngineData)
		{
			if($Player[$_Vars_GameElements[$EngineData['tech']]] >= $EngineData['minlevel'])
			{
				$Consumption = $EngineData['consumption'];
				break;
			}
		}
	}
	else
	{		
		$Consumption = 0;
	}

	return $Consumption;
}

function GetFleetConsumption($FleetArray, $SpeedFactor, $MissionDuration, $MissionDistance, $Player)
{
	foreach($FleetArray as $Ship => $Count)
	{
		if($Ship > 0)
		{
			$ShipSpeed = GetFleetMaxSpeed('', $Ship, $Player);
			$ShipConsumption = GetShipConsumption($Ship, $Player);
			$spd = 35000 / ($MissionDuration * $SpeedFactor - 10) * sqrt($MissionDistance * 10 / $ShipSpeed);
			$basicConsumption = $ShipConsumption * $Count;
			$consumption += $basicConsumption * $MissionDistance / 35000 * (($spd / 10) + 1) * (($spd / 10) + 1);
		}
	}

	return (round($consumption) + 1);
}

function GetStartAdressLink($FleetRow, $FleetType, $FromWindow = false)
{
	$Link .= "<a ".(($FromWindow === true) ? "onclick=\"opener.location = this.href; opener.focus(); return false;\"" : '')." href=\"galaxy.php?mode=3&galaxy={$FleetRow['fleet_start_galaxy']}&system={$FleetRow['fleet_start_system']}&planet={$FleetRow['fleet_start_planet']}\" class=\"{$FleetType}\" >";
	$Link .= "[{$FleetRow['fleet_start_galaxy']}:{$FleetRow['fleet_start_system']}:{$FleetRow['fleet_start_planet']}]</a>";
	return $Link;
}

function GetTargetAdressLink($FleetRow, $FleetType, $FromWindow = false)
{
	$Link .= "<a ".(($FromWindow === true) ? "onclick=\"opener.location = this.href; opener.focus(); return false;\"" : '')." href=\"galaxy.php?mode=3&galaxy={$FleetRow['fleet_end_galaxy']}&system={$FleetRow['fleet_end_system']}&planet={$FleetRow['fleet_end_planet']}\" class=\"{$FleetType}\" >";
	$Link .= "[{$FleetRow['fleet_end_galaxy']}:{$FleetRow['fleet_end_system']}:{$FleetRow['fleet_end_planet']}]</a>";
	return $Link;
}

function BuildHostileFleetPlayerLink($FleetRow, $FromWindow = false)
{
	global $_Lang, $_SkinPath;

	$Link .= $FleetRow['owner_name']." ";
	$Link .= "<a ".(($FromWindow === true) ? "onclick=\"opener.location = this.href; opener.focus(); return false;\"" : '')." href=\"messages.php?mode=write&uid={$FleetRow['fleet_owner']}\">";
	$Link .= "<img src=\"{$_SkinPath}/img/m.gif\" alt=\"{$_Lang['ov_message']}\" title=\"{$_Lang['ov_message']}\" border=\"0\"></a>";
	return $Link;
}

function CreatePlanetLink($Galaxy, $System, $Planet)
{
	$Link .= "<a href=\"galaxy.php?mode=3&galaxy={$Galaxy}&system={$System}&planet={$Planet}\">";
	$Link .= "[{$Galaxy}:{$System}:{$Planet}]</a>";
	return $Link;
}

function GetNextJumpWaitTime($CurMoon)
{
	global $_Vars_GameElements;

	$JumpGateLevel = $CurMoon[$_Vars_GameElements[43]];
	$LastJumpTime = $CurMoon['last_jump_time'];
	if($JumpGateLevel > 0)
	{
		$WaitBetweenJmp = 3600 * (1 / $JumpGateLevel);
		$NextJumpTime = $LastJumpTime + $WaitBetweenJmp;

		$Now = time();
		if($NextJumpTime >= $Now)
		{
			$RestWait = $NextJumpTime - $Now;
			$RestString = ' '.pretty_time($RestWait);
		}
		else
		{
			$RestWait = 0;
			$RestString = '';
		}
	}
	else
	{
		$RestWait = 0;
		$RestString = '';
	}
	$RetValue['string'] = $RestString;
	$RetValue['value'] = $RestWait;

	return $RetValue;
}

function CreateFleetPopupedFleetLink($FleetRow, $Texte)
{
	global $_Lang;
	
	$FleetArray = String2Array($FleetRow['fleet_array']);
	if(!empty($FleetArray))
	{
		foreach($FleetArray as $ShipID => $ShipCount)
		{
			$CreateTitle[] = "<tr><th class='flLabel sh'>{$_Lang['tech'][$ShipID]}:</th><th class='flVal'>".prettyNumber($ShipCount)."</th></tr>";
		}
	}
	if($FleetRow['fleet_resource_metal'] > 0 OR $FleetRow['fleet_resource_crystal'] > 0 OR $FleetRow['fleet_resource_deuterium'] > 0)
	{
		$CreateTitle[] = '<tr><th class=\'flRes\' colspan=\'2\'>&nbsp;</th></tr>';
		if($FleetRow['fleet_resource_metal'] > 0)
		{
			$CreateTitle[] = "<tr><th class='flLabel rs'>{$_Lang['Metal']}:</th><th class='flVal'>".prettyNumber($FleetRow['fleet_resource_metal'])."</th></tr>";
		}
		if($FleetRow['fleet_resource_crystal'] > 0)
		{
			$CreateTitle[] = "<tr><th class='flLabel rs'>{$_Lang['Crystal']}:</th><th class='flVal'>".prettyNumber($FleetRow['fleet_resource_crystal'])."</th></tr>";
		}
		if($FleetRow['fleet_resource_deuterium'] > 0)
		{
			$CreateTitle[] = "<tr><th class='flLabel rs'>{$_Lang['Deuterium']}:</th><th class='flVal'>".prettyNumber($FleetRow['fleet_resource_deuterium'])."</th></tr>";
		}
	}
	
	return '<a class="white flShips" title="<table style=\'width: 100%;\'>'.implode('', $CreateTitle).'</table>">'.$Texte.'</a>';
}

// String-related functions
function pretty_time($Seconds, $ChronoType = false, $Format = false)
{
	$Time = '';
	
	$Seconds = floor($Seconds);
	$Days = floor($Seconds / TIME_DAY);
	$Seconds -= $Days * TIME_DAY;
	$Hours = floor($Seconds / 3600);
	$Seconds -= $Hours * 3600;
	$Minutes = floor($Seconds / 60);
	$Seconds -= $Minutes * 60;

	if($Hours < 10)
	{
		$Hours = '0'.(string)$Hours;
	} 
	if($Minutes < 10)
	{
		$Minutes = '0'.(string)$Minutes;
	} 
	if($Seconds < 10)
	{
		$Seconds = '0'.(string)$Seconds;
	} 

	if($ChronoType === false)
	{
		$DAllowed = array
		(
			'd' => false,
			'h' => false,
			'm' => false,
			's' => false
		);
		
		if($Format)
		{
			if(strstr($Format, 'd') === false)
			{
				$DAllowed['d'] = true;
			}
			if(strstr($Format, 'h') === false)
			{
				$DAllowed['h'] = true;
			}
			if(strstr($Format, 'm') === false)
			{
				$DAllowed['m'] = true;
			}
			if(strstr($Format, 's') === false)
			{
				$DAllowed['s'] = true;
			}
		}
		
		if($Days > 0 AND ($DAllowed['d'] !== true))
		{
			$Time .= "{$Days}d ";
		}
		if($DAllowed['h'] !== true)
		{
			$Time .= "{$Hours}g ";
		}
		if($DAllowed['m'] !== true)
		{
			$Time .= "{$Minutes}m ";
		}
		if($DAllowed['s'] !== true)
		{
			$Time .= "{$Seconds}s";
		}
	}
	else
	{
		if($Days > 0)
		{
			if(strstr($Format, 'D') !== FALSE)
			{
				global $_Lang;
				$UseLang = ($Days > 1 ? $_Lang['Chrono_DayM'] : $_Lang['Chrono_Day1']);
				$Time = "{$Days} {$UseLang} ";
			}
			else if(strstr($Format, 'd') !== FALSE)
			{
				$Time = "{$Days}d ";
			}
			else
			{
				$Hours += $Days * 24;
			}
		}
		$Time .= "{$Hours}:{$Minutes}:{$Seconds}";
	}

	return $Time;
}

function pretty_time_hour($seconds, $NoSpace = false)
{
	$min = floor($seconds / 60 % 60);

	if($min != 0)
	{ 
		if($NoSpace)
		{
			$time = $min.'min';
		}
		else
		{
			$time = $min.'min ';
		}
	}
	else
	{
		$time = '';
	}

	return $time;
}

function prettyMonth($month, $variant = '0')
{
	global $_Lang;
	if(!isset($_Lang['pretty_months_loaded']))
	{
		includeLang('months');
	}
	return $_Lang['months_variant'.$variant][($month-1)];
}

function prettyDate($format, $timestamp = false, $variant = '0')
{
	if(strstr($format, 'm') !== false)
	{
		$HasMonth = true;
		$format = str_replace('m', '{|_|}', $format);
	}
	$Date = date($format, $timestamp);
	if($HasMonth === true)
	{
		$Month = prettyMonth(date('m', $timestamp), $variant);
		$Date = str_replace('{|_|}', $Month, $Date);
	}
	return $Date;
}

function ShowBuildTime($time)
{
	global $_Lang;

	return "<br/>{$_Lang['ConstructionTime']}: ".pretty_time($time);
}

function Array2String($Array)
{
	foreach($Array as $Key => $Value)
	{
		$String[] = "{$Key},{$Value}";
	}
	return implode(';', $String);
}

function String2Array($String)
{
	$String = explode(';', $String);
	foreach($String as $Data)
	{
		$Data = explode(',', $Data);
		$Array[$Data[0]] = $Data[1];
	}
	return $Array;
}

?>