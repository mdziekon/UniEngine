<?php

function CreateOneMoonRecord($Galaxy, $System, $Planet, $Owner, $MoonName, $Chance, $SetDiameter = false)
{
	global $_Lang;

	$QryGetMoonGalaxyData = "SELECT `galaxy_id`, `id_moon` FROM {{table}} WHERE `galaxy` = '{$Galaxy}' AND `system` = '{$System}' AND `planet` = '{$Planet}';";
	$MoonGalaxy = doquery($QryGetMoonGalaxyData, 'galaxy', true);
	if($MoonGalaxy['id_moon'] == 0)
	{
		$QryGetMoonPlanetData = "SELECT `id`, `temp_min`, `temp_max` FROM {{table}} WHERE `galaxy` = '{$Galaxy}' AND `system` = '{$System}' AND `planet` = '{$Planet}';";
		$MoonPlanet = doquery($QryGetMoonPlanetData, 'planets', true);
		
		if($MoonPlanet['id'] != 0)
		{
			if($SetDiameter === false || !($SetDiameter >= 2000 && $SetDiameter <= 10000))
			{
				$Diameter_Min = 2000 + ($Chance * 100);
				$Diameter_Max = 6000 + ($Chance * 200);
				$Diameter = rand($Diameter_Min, $Diameter_Max);
			}
			else
			{
				$Diameter = $SetDiameter;
			}
			$RandTemp = rand(10, 45);
			$mintemp = $MoonPlanet['temp_min'] - $RandTemp;
			$maxtemp = $MoonPlanet['temp_max'] - $RandTemp;
			
			if(empty($MoonName))
			{
				$MoonName = $_Lang['sys_moon'];
			}
			
			$QryInsertMoonInPlanet = "INSERT INTO {{table}} SET ";
			$QryInsertMoonInPlanet .= "`name` = '$MoonName', ";
			$QryInsertMoonInPlanet .= "`id_owner` = '{$Owner}', ";
			$QryInsertMoonInPlanet .= "`galaxy` = '{$Galaxy}', ";
			$QryInsertMoonInPlanet .= "`system` = '{$System}', ";
			$QryInsertMoonInPlanet .= "`planet` = '{$Planet}', ";
			$QryInsertMoonInPlanet .= "`last_update` = UNIX_TIMESTAMP(), ";
			$QryInsertMoonInPlanet .= "`planet_type` = 3, ";
			$QryInsertMoonInPlanet .= "`image` = 'mond', ";
			$QryInsertMoonInPlanet .= "`diameter` = '{$Diameter}', ";
			$QryInsertMoonInPlanet .= "`field_max` = 1, ";
			$QryInsertMoonInPlanet .= "`temp_min` = '{$maxtemp}', ";
			$QryInsertMoonInPlanet .= "`temp_max` = '{$mintemp}', ";
			$QryInsertMoonInPlanet .= "`metal` = 0, ";
			$QryInsertMoonInPlanet .= "`metal_perhour` = 0, ";
			$QryInsertMoonInPlanet .= "`metal_max` = '".BASE_STORAGE_SIZE."', ";
			$QryInsertMoonInPlanet .= "`crystal` = 0, ";
			$QryInsertMoonInPlanet .= "`crystal_perhour` = 0, ";
			$QryInsertMoonInPlanet .= "`crystal_max` = '".BASE_STORAGE_SIZE."', ";
			$QryInsertMoonInPlanet .= "`deuterium` = 0, ";
			$QryInsertMoonInPlanet .= "`deuterium_perhour` = 0, ";
			$QryInsertMoonInPlanet .= "`deuterium_max` = '".BASE_STORAGE_SIZE."';";
			doquery($QryInsertMoonInPlanet, 'planets');

			// Select CreatedMoon ID
			$QrySelectPlanet = "SELECT `id` FROM {{table}} WHERE `galaxy` = '{$Galaxy}' AND `system` = '{$System}' AND `planet` = '{$Planet}' AND `planet_type` = 3;";
			$GetPlanetID = doquery($QrySelectPlanet, 'planets', true);

			$QryUpdateMoonInGalaxy = "UPDATE {{table}} SET ";
			$QryUpdateMoonInGalaxy .= "`id_moon` = '{$GetPlanetID['id']}' ";
			$QryUpdateMoonInGalaxy .= "WHERE `galaxy_id` = {$MoonGalaxy['galaxy_id']};";
			doquery($QryUpdateMoonInGalaxy, 'galaxy');
			
			return $GetPlanetID['id'];
		}
	}

	return false;
}

?>