<?php

function GetBuildingTime($_User, $planet, $Element)
{
	global $_Vars_Prices, $_Vars_GameElements, $_Vars_ElementCategories, $_GameConfig, $_Vars_BuildingsFixedBuildTime;

	$level = ($planet[$_Vars_GameElements[$Element]]) ? $planet[$_Vars_GameElements[$Element]] : $_User[$_Vars_GameElements[$Element]];
	if(!empty($_Vars_BuildingsFixedBuildTime[$Element]))
	{
		$timeBase = $_Vars_BuildingsFixedBuildTime[$Element];
	}
	if(in_array($Element, $_Vars_ElementCategories['build']))
	{
		$cost_metal = floor($_Vars_Prices[$Element]['metal'] * pow($_Vars_Prices[$Element]['factor'], $level));
		$cost_crystal = floor($_Vars_Prices[$Element]['crystal'] * pow($_Vars_Prices[$Element]['factor'], $level));
		$timeBase = ($timeBase) ? $timeBase : (($cost_crystal) + ($cost_metal));
		$time = ($timeBase / $_GameConfig['game_speed']) * (1 / ($planet[$_Vars_GameElements['14']] + 1)) * pow(0.5, $planet[$_Vars_GameElements['15']]);
		$time = floor($time * 60 * 60);
	}
	else if(in_array($Element, $_Vars_ElementCategories['tech']))
	{
		$cost_metal = floor($_Vars_Prices[$Element]['metal'] * pow($_Vars_Prices[$Element]['factor'], $level));
		$cost_crystal = floor($_Vars_Prices[$Element]['crystal'] * pow($_Vars_Prices[$Element]['factor'], $level));
		$intergal_lab = $_User[$_Vars_GameElements[123]];
		if($intergal_lab < 1)
		{
			$lablevel = $planet[$_Vars_GameElements[31]];
		}
		else if($intergal_lab >= 1)
		{
			$lablevel = 0;
			$empire = doquery("SELECT `{$_Vars_GameElements[31]}` FROM {{table}} WHERE `id_owner` = {$_User['id']} AND `{$_Vars_GameElements[31]}` > 0 ORDER BY `{$_Vars_GameElements[31]}` DESC LIMIT ".($intergal_lab + 1).";", 'planets');
			if(mysql_num_rows($empire) > 1)
			{
				while($colony = mysql_fetch_assoc($empire))
				{
					$lablevel += $colony[$_Vars_GameElements[31]];
				}
			}
			else
			{
				$lablevel = $planet[$_Vars_GameElements[31]];
			}
		}
		$timeBase = ($timeBase) ? $timeBase : (($cost_crystal) + ($cost_metal));
		$time = ($timeBase / $_GameConfig['game_speed']) / (($lablevel + 1) * 2);
		$time = floor($time * 60 * 60 * (($_User['technocrat_time'] > time()) ? 0.8 : 1));
	}
	else if(in_array($Element, $_Vars_ElementCategories['defense']))
	{
		$timeBase = ($timeBase) ? $timeBase : ($_Vars_Prices[$Element]['metal'] + $_Vars_Prices[$Element]['crystal']);
		$time = ($timeBase / $_GameConfig['game_speed']) * (1 / ($planet[$_Vars_GameElements['21']] + 1)) * pow(1 / 2, $planet[$_Vars_GameElements['15']]);
		$time = floor($time * 60 * 60);
	}
	else if(in_array($Element, $_Vars_ElementCategories['fleet']))
	{
		$timeBase = ($timeBase) ? $timeBase : ($_Vars_Prices[$Element]['metal'] + $_Vars_Prices[$Element]['crystal']);
		$time = ($timeBase / $_GameConfig['game_speed']) * (1 / ($planet[$_Vars_GameElements['21']] + 1)) * pow(1 / 2, $planet[$_Vars_GameElements['15']]);
		$time = floor($time * 60 * 60);
	}

	if($time < 0)
	{
		$time = 0;
	}

	return $time;
}

?>