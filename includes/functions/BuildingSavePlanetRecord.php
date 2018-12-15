<?php

function BuildingSavePlanetRecord($CurrentPlanet)
{
    // Update table on changes in Structures Queue
    $QryUpdatePlanet = '';
    $QryUpdatePlanet .= "UPDATE {{table}} SET ";
    $QryUpdatePlanet .= "`buildQueue` = '{$CurrentPlanet['buildQueue']}', `buildQueue_firstEndTime` = '{$CurrentPlanet['buildQueue_firstEndTime']}' ";
    $QryUpdatePlanet .= "WHERE `id` = {$CurrentPlanet['id']};";
    doquery($QryUpdatePlanet, 'planets');
}

?>
