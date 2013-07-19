<?php

function ShowBuildingQueue($CurrentPlanet, $CurrentUser)
{
	global $_Lang, $_Vars_PremiumBuildings;

	$CurrentQueue = $CurrentPlanet['buildQueue'];
	$QueueID = 0;
	if($CurrentQueue != 0)
	{
		// Building Queue is not empty
		$QueueArray = explode(';', $CurrentQueue);
		$ActualCount = count($QueueArray);
	}
	else
	{
		// Building Queue is empty
		$QueueArray = '0';
		$ActualCount = 0;
	}

	$ListIDRow = '';
	if($ActualCount != 0)
	{
		$ListIDRow = '<style>.lime { color: lime; }</style>';
		$PlanetID = $CurrentPlanet['id'];
		$CurrentTime = time();
		for($QueueID = 0; $QueueID < $ActualCount; $QueueID += 1)
		{
			// Each BuildingRecord is created in array:
			// [0] -> Building ID
			// [1] -> Level of Building
			// [2] -> Time of Construction
			// [3] -> End Time of Construction
			// [4] -> Action (build/destroy)
			$BuildArray = explode (',', $QueueArray[$QueueID]);
			$BuildEndTime = floor($BuildArray[3]);
			if($BuildEndTime >= $CurrentTime)
			{
				$ListID = $QueueID + 1;
				$Element = $BuildArray[0];
				$BuildLevel = $BuildArray[1];
				$BuildMode = $BuildArray[4];
				$BuildTime = $BuildEndTime - $CurrentTime;
				$ElementTitle = $_Lang['tech'][$Element];

				$BuildLevelShow = $BuildLevel;
				if($BuildMode != 'build')
				{
					$BuildLevelShow += 1;
					$ShowDestroy = " ({$_Lang['destroy']})";
				}
				else
				{
					$ShowDestroy = '';
				}

				$CreateRow = "<tr><td class=\"l\" colspan=\"2\"><b>{$ListID}. {$ElementTitle} ({$_Lang['level']} {$BuildLevelShow}){$ShowDestroy}</b></td>";
				$CreateRow .= '<td class="k">';
				if($ListID == 1)
				{
					if($_Vars_PremiumBuildings[$Element] == 1)
					{
						$LinkHref = '#';
						$LinkOnClick = "alert('{$_Lang['CannotDeletePremiumBuilding_Warning']}'); return false;";
						$LinkClass = ' style="cursor: pointer; color: red;"';
					}
					else
					{
						$LinkHref = "buildings.php?listid={$ListID}&amp;cmd=cancel&amp;planet={$PlanetID}";
						$LinkOnClick = "return confirm('{$_Lang['AreYouSure']}');";
						$LinkClass = '';
					}
					$CreateRow .= "<div id=\"blc\" class=\"z\">{$BuildTime}</div><div id=\"dlink\"><a href=\"{$LinkHref}\" onclick=\"{$LinkOnClick}\"{$LinkClass}>{$_Lang['DelFirstQueue']}</a></div>";
					$CreateRow .= '<b class="lime"><br/>'.date('d/m H:i:s', $BuildEndTime).'</b>';
					$CreateRow .= "<script>pp = \"{$BuildTime}\"; pk = \"{$ListID}\"; pm = \"cancel\"; pl = \"{$PlanetID}\"; t();</script>";
				}
				else
				{
					$CreateRow .= "<a href=\"buildings.php?listid={$ListID}&amp;cmd=remove&amp;planet={$PlanetID}\">{$_Lang['DelFromQueue']}</a>";
					$CreateRow .= '<b class="lime"><br/><br/>'.date('d/m H:i:s', $BuildEndTime).'</b>';
				}
				$CreateRow .= '</td></tr>';

				$ListIDRow .= $CreateRow;
			}
		}
	}

	$RetValue['lenght'] = $ActualCount;
	$RetValue['buildlist'] = $ListIDRow;

	return $RetValue;
}

?>