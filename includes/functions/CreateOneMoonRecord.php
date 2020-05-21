<?php

/**
 * @param array $params
 * @param array $params['coordinates']
 * @param string $params['ownerID']
 * @param string | null $params['moonName']
 * @param number | null $params['moonCreationChance']
 * @param number | null $params['fixedDiameter']
 */
function CreateOneMoonRecord($params) {
    global $_Lang;

    $coordinates = $params['coordinates'];
    $fixedDiameter = (
        isset($params['fixedDiameter']) ?
        $params['fixedDiameter'] :
        null
    );

    $query_GetMoonGalaxyRow = (
        "SELECT `galaxy_id`, `id_moon` " .
        "FROM {{table}} " .
        "WHERE " .
        "`galaxy` = '{$coordinates['galaxy']}' AND " .
        "`system` = '{$coordinates['system']}' AND " .
        "`planet` = '{$coordinates['planet']}' " .
        ";"
    );
    $result_GetMoonGalaxyRow = doquery($query_GetMoonGalaxyRow, 'galaxy', true);

    if (
        empty($result_GetMoonGalaxyRow) ||
        $result_GetMoonGalaxyRow['id_moon'] != 0
    ) {
        return false;
    }

    $galaxyRowID = $result_GetMoonGalaxyRow['galaxy_id'];

    $query_GetCoordinatesPlanetRow = (
        "SELECT `id`, `temp_min`, `temp_max` " .
        "FROM {{table}} " .
        "WHERE " .
        "`galaxy` = '{$coordinates['galaxy']}' AND " .
        "`system` = '{$coordinates['system']}' AND " .
        "`planet` = '{$coordinates['planet']}' " .
        ";"
    );
    $result_GetCoordinatesPlanetRow = doquery($query_GetCoordinatesPlanetRow, 'planets', true);

    if (empty($result_GetCoordinatesPlanetRow)) {
        return false;
    }

    $coordinatesPlanetRow = $result_GetCoordinatesPlanetRow;

    if (
        $fixedDiameter === null ||
        !(
            $fixedDiameter >= 2000 &&
            $fixedDiameter <= 10000
        )
    ) {
        $Diameter_Min = 2000 + ($params['moonCreationChance'] * 100);
        $Diameter_Max = 6000 + ($params['moonCreationChance'] * 200);
        $Diameter = rand($Diameter_Min, $Diameter_Max);
    } else {
        $Diameter = $params['fixedDiameter'];
    }

    $RandTemp = rand(10, 45);
    $mintemp = $coordinatesPlanetRow['temp_min'] - $RandTemp;
    $maxtemp = $coordinatesPlanetRow['temp_max'] - $RandTemp;

    $newMoonName = (
        !empty($params['moonName']) ?
            $params['moonName'] :
            $_Lang['sys_moon']
    );

    $QryInsertMoonInPlanet = "INSERT INTO {{table}} SET ";
    $QryInsertMoonInPlanet .= "`name` = '{$newMoonName}', ";
    $QryInsertMoonInPlanet .= "`id_owner` = '{$params['ownerID']}', ";
    $QryInsertMoonInPlanet .= "`galaxy` = '{$coordinates['galaxy']}', ";
    $QryInsertMoonInPlanet .= "`system` = '{$coordinates['system']}', ";
    $QryInsertMoonInPlanet .= "`planet` = '{$coordinates['planet']}', ";
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
    $QrySelectPlanet = "SELECT `id` FROM {{table}} WHERE `galaxy` = '{$coordinates['galaxy']}' AND `system` = '{$coordinates['system']}' AND `planet` = '{$coordinates['planet']}' AND `planet_type` = 3;";
    $GetPlanetID = doquery($QrySelectPlanet, 'planets', true);

    $QryUpdateMoonInGalaxy = "UPDATE {{table}} SET ";
    $QryUpdateMoonInGalaxy .= "`id_moon` = '{$GetPlanetID['id']}' ";
    $QryUpdateMoonInGalaxy .= "WHERE `galaxy_id` = {$galaxyRowID};";
    doquery($QryUpdateMoonInGalaxy, 'galaxy');

    return $GetPlanetID['id'];
}

?>
