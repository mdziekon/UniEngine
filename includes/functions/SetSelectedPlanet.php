<?php

function SetSelectedPlanet(&$user, $planetID) {
    $userID = $user['id'];

    $query_IsPlanetOwner = (
        "SELECT `id` " .
        "FROM {{table}} " .
        "WHERE " .
        "  `id` = {$planetID} AND" .
        "  `id_owner` = {$userID} " .
        "LIMIT 1;"
    );
    $result_IsPlanetOwner = doquery($query_IsPlanetOwner, 'planets', true);

    if (!$result_IsPlanetOwner) {
        return false;
    }

    $user['current_planet'] = $planetID;

    $query_UpdateUser = (
        "UPDATE {{table}} " .
        "SET " .
        "  `current_planet` = {$planetID} " .
        "WHERE " .
        "  `id` = {$userID} " .
        "LIMIT 1;"
    );
    doquery($query_UpdateUser, 'users');

    return true;
}

?>
