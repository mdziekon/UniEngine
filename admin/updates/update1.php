<?php

if(!defined('IN_UPDATER'))
{
	die();
}

doquery("ALTER TABLE `{{prefix}}planets` CHANGE `metal_mine_porcent` `metal_mine_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';", '');
doquery("ALTER TABLE `{{prefix}}planets` CHANGE `crystal_mine_porcent` `crystal_mine_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';", '');
doquery("ALTER TABLE `{{prefix}}planets` CHANGE `deuterium_synthesizer_porcent` `deuterium_synthesizer_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';", '');
doquery("ALTER TABLE `{{prefix}}planets` CHANGE `solar_plant_porcent` `solar_plant_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';", '');
doquery("ALTER TABLE `{{prefix}}planets` CHANGE `fusion_reactor_porcent` `fusion_reactor_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';", '');
doquery("ALTER TABLE `{{prefix}}planets` CHANGE `solar_satellite_porcent` `solar_satellite_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10';", '');

?>