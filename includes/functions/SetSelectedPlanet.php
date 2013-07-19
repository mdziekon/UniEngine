<?php

function SetSelectedPlanet(&$CurrentUser, $Security = false)
{
	global $_GET;

	if($Security !== false)
	{
		$SelectPlanet = $Security;
	}
	else
	{
		$SelectPlanet = round($_GET['cp']);
	}

	if($SelectPlanet > 0)
	{		
		$IsPlanetMine = doquery("SELECT `id` FROM {{table}} WHERE `id` = {$SelectPlanet} AND `id_owner` = {$CurrentUser['id']} LIMIT 1;", 'planets', true);
		if($IsPlanetMine['id'] == $SelectPlanet)
		{
			$CurrentUser['current_planet'] = $SelectPlanet;
			doquery("UPDATE {{table}} SET `current_planet` = {$SelectPlanet} WHERE `id` = {$CurrentUser['id']} LIMIT 1;", 'users');
		}
	}
}

?>