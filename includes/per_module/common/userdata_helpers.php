<?php

class UniEngineException extends \Exception {};
class UniEngineDataFetchException extends UniEngineException {};
class UniEnginePlanetDataFetchException extends UniEngineDataFetchException {};

function _fetchPlanetData($planetID) {
    $query_GetPlanet = (
        "SELECT * " .
        "FROM {{table}} " .
        "WHERE " .
        "  `id` = {$planetID} " .
        "LIMIT 1;"
    );
    $result_GetPlanet = doquery($query_GetPlanet, 'planets', true);

    return $result_GetPlanet;
}

function _isPlanetOwner($userID, &$planet) {
    return $planet['id_owner'] == $userID;
}

function fetchCurrentPlanetData (&$user) {
    $userID = $user['id'];
    $currentPlanetID = $user['current_planet'];
    $motherPlanetID = $user['id_planet'];

    $planet = _fetchPlanetData($currentPlanetID);

    if (
        (!$planet || !_isPlanetOwner($userID, $planet)) &&
        $currentPlanetID != $motherPlanetID
    ) {
        // TODO: determine is this is needed
        //       by checking how many places allow you to change 'current_planet'
        //
        // If this planet doesn't exist, try to go back to MotherPlanet
        SetSelectedPlanet($user, $motherPlanetID);

        $planet = _fetchPlanetData($motherPlanetID);
    }

    if (!$planet) {
        throw new UniEnginePlanetDataFetchException('Could not select the current, nor mother, planets');
    }

    CheckPlanetUsedFields($planet);

    return $planet;
}

function fetchGalaxyData(&$planet) {
    $planetID = $planet['id'];

    $selectorKey = (
        $planet['type'] == 1 ?
        'id_planet' :
        'id_moon'
    );

    $query_GetGalaxyRow = (
        "SELECT * " .
        "FROM {{table}} " .
        "WHERE " .
        "  `{$selectorKey}` = {$planetID} " .
        "LIMIT 1;"
    );

    $result_GetGalaxyRow = doquery($query_GetGalaxyRow, 'galaxy', true);

    return $result_GetGalaxyRow;
}

?>
