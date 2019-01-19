<?php

function DeleteSelectedPlanetorMoon()
{
    global $_Planet, $_GalaxyRow, $_User;

    // Do necessary checks
    if($_Planet['id'] <= 0)
    {
        return array('result' => false, 'reason' => 'planetID');
    }
    if($_User['techQueue_EndTime'] > 0 AND $_User['techQueue_Planet'] == $_Planet['id'])
    {
        return array('result' => false, 'reason' => 'tech');
    }
    $Query = "SELECT COUNT(`fleet_id`) as `Count` FROM {{table}} WHERE `fleet_start_id` = {$_Planet['id']} OR `fleet_end_id` = {$_Planet['id']};";
    $Fleets = doquery($Query, 'fleets', true);
    if($Fleets['Count'] > 0)
    {
        return array('result' => false, 'reason' => 'fleet_current');
    }
    if($_Planet['planet_type'] == 1 AND $_GalaxyRow['id_moon'] > 0)
    {
        $Query = "SELECT COUNT(`fleet_id`) as `Count` FROM {{table}} WHERE `fleet_start_id` = {$_GalaxyRow['id_moon']} OR `fleet_end_id` = {$_GalaxyRow['id_moon']};";
        $Fleets = doquery($Query, 'fleets', true);
        if($Fleets['Count'] > 0)
        {
            return array('result' => false, 'reason' => 'fleet_moon');
        }
    }

    // Abandon Planet here
    if($_User['settings_mainPlanetID'] == $_Planet['id'])
    {
        $Query_UpdateUser_Set[] = "`settings_mainPlanetID` = `id_planet`";
    }

    $Query_UpdateUser_Set[] = "`current_planet` = `id_planet`";
    $Query_UpdateUser = '';
    $Query_UpdateUser .= "UPDATE {{table}} SET ";
    $Query_UpdateUser .= implode(', ', $Query_UpdateUser_Set);
    $Query_UpdateUser .= " WHERE `id` = {$_User['id']} LIMIT 1;";
    doquery($Query_UpdateUser, 'users');

    $PlanetIDs = [];
    $PlanetIDs[] = $_Planet['id'];
    if($_Planet['planet_type'] == 1 AND $_GalaxyRow['id_moon'] > 0)
    {
        $PlanetIDs[] = $_GalaxyRow['id_moon'];
    }

    $PlanetCount = count($PlanetIDs);
    $PlanetImplode = implode(',', $PlanetIDs);
    if(PLANET_ABANDONTIME > 0 AND $_Planet['planet_type'] == 1)
    {
        $Query_UpdatePlanets = '';
        $Query_UpdatePlanets .= "UPDATE {{table}} ";
        $Query_UpdatePlanets .= "SET `id_owner` = 0, `abandon_time` = UNIX_TIMESTAMP() ";
        $Query_UpdatePlanets .= "WHERE `id` IN ({$PlanetImplode}) LIMIT {$PlanetCount};";
        doquery($Query_UpdatePlanets, 'planets');
    }
    else
    {
        if($_Planet['planet_type'] == 1)
        {
            doquery("DELETE FROM {{table}} WHERE `galaxy_id` = {$_GalaxyRow['galaxy_id']} LIMIT 1;", 'galaxy');
        }
        else
        {
            doquery("UPDATE {{table}} SET `id_moon` = 0 WHERE `galaxy_id` = {$_GalaxyRow['galaxy_id']} LIMIT 1;", 'galaxy');
        }
        doquery("DELETE FROM {{table}} WHERE `id` IN ({$PlanetImplode}) LIMIT {$PlanetCount};", 'planets');
    }

    return array('result' => true, 'ids' => $PlanetIDs);
}

?>
