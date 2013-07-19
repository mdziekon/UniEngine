<?php

// Check if Lab is in BuildQueue and get Time of last Building completion
function CheckLabInQueue($CurrentPlanet)
{
	global $_GameConfig;

	$EndTime = false;

	if($CurrentPlanet['buildQueue'] != '0' AND $CurrentPlanet['buildQueue'] != 0)
	{
		$Queue = explode(';', $CurrentPlanet['buildQueue']);
		foreach($Queue as $BuildingData)
		{
			$BuildingData = explode(',', $BuildingData);
			$ElementID = $BuildingData[0];
			$BuildEndTime = $BuildingData[3];
			if($ElementID == 31)
			{
				$EndTime = $BuildEndTime;
			}
		}
	}

	return $EndTime;
}

?>