<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

	if(!CheckAuth('supportadmin'))
	{
		AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
	}
		
	includeLang('admin/addMoon');

	$PageTpl = gettemplate('admin/addMoon');

	if(isset($_POST['sent']) && $_POST['sent'] == '1')
	{
		$_Lang['PHP_Ins_PlanetID'] = $_POST['planetID'];
		$_Lang['PHP_Ins_Name'] = $_POST['name'];
		$_Lang['PHP_Ins_Diameter'] = $_POST['diameter'];
		
		$Set_PlanetID = round(floatval($_POST['planetID']));
		$Set_MoonName = $_POST['name'];
		$Set_Diameter = round(floatval($_POST['diameter']));
		
		if($Set_Diameter <= 0)
		{
			$Set_Diameter = false;
		}
		
		if($Set_PlanetID > 0)
		{
			if(empty($Set_MoonName) || preg_match(REGEXP_PLANETNAME_ABSOLUTE, $Set_MoonName))
			{
				$Query_GetPlanetData = "SELECT `galaxy`, `system`, `planet`, `id_owner` FROM {{table}} WHERE `id` = {$Set_PlanetID} LIMIT 1; -- admin/addMoon.php - Query #1";
				$QResult_GetPlanetData = doquery($Query_GetPlanetData, 'planets', true);
				$PlData = &$QResult_GetPlanetData;
				
				if($PlData['id_owner'] > 0)
				{
					include($_EnginePath.'includes/functions/CreateOneMoonRecord.php');
					if(CreateOneMoonRecord($PlData['galaxy'], $PlData['system'], $PlData['planet'], $PlData['id_owner'], $Set_MoonName, 20, $Set_Diameter) != false)
					{
						$_Lang['PHP_InfoBox_Text'] = $_Lang['AddMoon_Success'];
						$_Lang['PHP_InfoBox_Color'] = 'lime';
					}
					else
					{
						$_Lang['PHP_InfoBox_Text'] = $_Lang['AddMoon_Fail_MoonExists'];
						$_Lang['PHP_InfoBox_Color'] = 'red';
					}
				}
				else
				{
					$_Lang['PHP_InfoBox_Text'] = $_Lang['AddMoon_Fail_NoPlanet'];
					$_Lang['PHP_InfoBox_Color'] = 'red';
				}
			}
			else
			{
				$_Lang['PHP_InfoBox_Text'] = $_Lang['AddMoon_Fail_NameBadSigns'];
				$_Lang['PHP_InfoBox_Color'] = 'red';
			}
		}
		else
		{
			$_Lang['PHP_InfoBox_Text'] = $_Lang['AddMoon_Fail_BadID'];
			$_Lang['PHP_InfoBox_Color'] = 'red';
		}
	}
	
	if(empty($_Lang['PHP_InfoBox_Text']))
	{
		$_Lang['PHP_InfoBox_Hide'] = 'display: none;';
	}
	
	$Page = parsetemplate($PageTpl, $_Lang);

	display($Page, $_Lang['AddMoon_Title'], false, true);
	
?>