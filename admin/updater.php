<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

	includeLang('admin/updater');
	
	if(!CheckAuth('programmer'))
	{
		AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
	}
	
	$lastUpdate = intval($_GameConfig['UniEngine_Updater_LastUpdateApplied']);
	
	$scanUpdatesDir = scandir('updates');
	$updatesFound = array();
	
	foreach($scanUpdatesDir as $filename)
	{
		if(strstr($filename, 'update') !== false)
		{
			$version = intval(preg_replace('#update([0-9]{1,})\.php#si', '\\1', $filename));
			if($version > $lastUpdate)
			{
				$updatesFound[] = $version;
			}
		}
	}
	
	if(!empty($updatesFound))
	{
		define('IN_UPDATER', true);
		
		foreach($updatesFound as $version)
		{
			include_once('updates/update'.$version.'.php');
		}
		
		$lastUpdate = end($updatesFound);
		doquery("UPDATE {{table}} SET `config_value` = '{$lastUpdate}' WHERE `config_name` = 'UniEngine_Updater_LastUpdateApplied';", 'config');
		$_GameConfig['UniEngine_Updater_LastUpdateApplied'] = $lastUpdate;
		$_MemCache->GameConfig = $_GameConfig;
		
		AdminMessage(sprintf($_Lang['Updater_Done'], $lastUpdate), $_Lang['Updater_Title']);
	}
	else
	{
		AdminMessage($_Lang['Updater_NothingFound'], $_Lang['Updater_Title']);
	}

?>