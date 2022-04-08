<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Updaters;

/**
 * @param object $props
 * @param string $props['fleetEntryId']
 * @param object $props['ownerUser']
 * @param object $props['ownerPlanet']
 * @param object $props['fleetEntry']
 * @param object $props['targetPlanet']
 * @param object $props['targetCoords']
 * @param object $props['flags']
 * @param number $props['currentTime']
 */
function insertFleetArchiveEntry ($props) {
    $fleetArray = Array2String($props['fleetEntry']['array']);
    $targetId = (
        !empty($props['targetPlanet']['id']) ?
            $props['targetPlanet']['id'] :
            '0'
    );
    $targetOwnerId = (
        !empty($props['targetPlanet']['ownerId']) ?
            $props['targetPlanet']['ownerId'] :
            '0'
    );
    $targetGalaxyId = (
        !empty($props['targetPlanet']['galaxy_id']) ?
            $props['targetPlanet']['galaxy_id'] :
            '0'
    );

    $hasIpIntersectionQueryVal = $props['flags']['hasIpIntersection'] ?
        '1' :
        '0';
    $hasIpIntersectionFilteredQueryVal = $props['flags']['hasIpIntersectionFiltered'] ?
        '1' :
        '0';
    $hasIpIntersectionOnSendQueryVal = $props['flags']['hasIpIntersectionOnSend'] ?
        '1' :
        '0';
    $hasUsedTeleportationQueryVal = $props['flags']['hasUsedTeleportation'] ?
        '1' :
        '0';

    $query = (
        "INSERT INTO {{table}} " .
        "SET " .
        "`Fleet_ID` = {$props['fleetEntryId']}, " .
        "`Fleet_Owner` = {$props['ownerUser']['id']}, " .
        "`Fleet_Mission` = {$props['fleetEntry']['Mission']}, " .
        "`Fleet_Array` = '{$fleetArray}', " .
        "`Fleet_Time_Send` = {$props['currentTime']}, " .
        "`Fleet_Time_Start` = {$props['fleetEntry']['SetCalcTime']}, " .
        "`Fleet_Time_Stay` = {$props['fleetEntry']['SetStayTime']}, " .
        "`Fleet_Time_End` = {$props['fleetEntry']['SetBackTime']}, " .
        "`Fleet_Start_ID` = {$props['ownerPlanet']['id']}, " .
        "`Fleet_Start_Galaxy` = {$props['ownerPlanet']['galaxy']}, " .
        "`Fleet_Start_System` = {$props['ownerPlanet']['system']}, " .
        "`Fleet_Start_Planet` = {$props['ownerPlanet']['planet']}, " .
        "`Fleet_Start_Type` = {$props['ownerPlanet']['planet_type']}, " .
        "`Fleet_Start_Res_Metal` = {$props['fleetEntry']['resources']['metal']}, " .
        "`Fleet_Start_Res_Crystal` = {$props['fleetEntry']['resources']['crystal']}, " .
        "`Fleet_Start_Res_Deuterium` = {$props['fleetEntry']['resources']['deuterium']}, " .
        "`Fleet_End_ID` = {$targetId}, " .
        "`Fleet_End_ID_Galaxy` = {$targetGalaxyId}, " .
        "`Fleet_End_Galaxy` = {$props['targetCoords']['galaxy']}, " .
        "`Fleet_End_System` = {$props['targetCoords']['system']}, " .
        "`Fleet_End_Planet` = {$props['targetCoords']['planet']}, " .
        "`Fleet_End_Type` = {$props['targetCoords']['type']}, " .
        "`Fleet_End_Owner` = {$targetOwnerId}, " .
        "`Fleet_ACSID` = '{$props['fleetEntry']['ACS_ID']}', " .
        "`Fleet_Info_HadSameIP_Ever` = {$hasIpIntersectionQueryVal}, " .
        "`Fleet_Info_HadSameIP_Ever_Filtred` = {$hasIpIntersectionFilteredQueryVal}, " .
        "`Fleet_Info_HadSameIP_OnSend` = {$hasIpIntersectionOnSendQueryVal}, " .
        "`Fleet_Info_UsedTeleport` = {$hasUsedTeleportationQueryVal} " .
        ";"
    );

    doquery($query, 'fleet_archive');
}

?>
