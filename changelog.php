<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

includeLang('changelog');

$template = gettemplate('changelog_table');
$infos = gettemplate('changelog_serverinfo');

$parse = $_Lang;

$parse['info_resourcemultiplier'] = prettyNumber($_GameConfig['resource_multiplier']);
$parse['info_gamespeed'] = prettyNumber($_GameConfig['game_speed'] / 2500);
$parse['info_fleetspeed'] = prettyNumber($_GameConfig['fleet_speed'] / 2500);
$parse['info_shipyard_elementsperrow'] = prettyNumber(MAX_FLEET_OR_DEFS_PER_ROW);
$parse['info_building_queuesize'] = prettyNumber(MAX_BUILDING_QUEUE_SIZE);
$parse['info_noobprotection_time'] = prettyNumber($_GameConfig['noobprotectiontime'] * 1000);
$parse['info_nonoobprotection_time'] = prettyNumber($_GameConfig['no_noob_protect'] * 1000);
$parse['infotip_noobprotection'] = sprintf($_Lang['infotip_noobprotection'], $_GameConfig['noobprotectionmulti']);

$SubFrame = parsetemplate($infos, $parse);

$parse = array();

$ChangesList = '';
foreach($_Lang['changelog'] as $Version => $Desc)
{
    $parse['version_number'] = $Version;
    $parse['description'] = nl2br($Desc);

    $ChangesList .= parsetemplate($template, $parse);
}

$parse = $_Lang;
$parse['InfoTable'] = $SubFrame;
$parse['ChangesList'] = $ChangesList;

display(parsetemplate(gettemplate('changelog_body'), $parse), 'ChangeLog', false);

?>
