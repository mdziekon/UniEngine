<?php

function PostResearchSaveChanges($ThePlanet, $IsCurrentPlanet = true, $UpdateUser = false)
{
    // Update tables on changes in Technology Queue
    $QryUpdatePlanet = "UPDATE {{table}} SET ";
    if($IsCurrentPlanet === false)
    {
        $QryUpdatePlanet .= "`metal` = '{$ThePlanet['metal']}', `crystal` = '{$ThePlanet['crystal']}', `deuterium` = '{$ThePlanet['deuterium']}', ";
    }
    $QryUpdatePlanet .= "`techQueue` = '{$ThePlanet['techQueue']}', `techQueue_firstEndTime` = '{$ThePlanet['techQueue_firstEndTime']}' ";
    $QryUpdatePlanet .= "WHERE `id` = {$ThePlanet['id']};";
    doquery($QryUpdatePlanet, 'planets');

    if(!empty($UpdateUser) AND $UpdateUser['id'] > 0)
    {
        $QryUpdateUser = "UPDATE {{table}} SET ";
        $QryUpdateUser .= "`techQueue_Planet` = '0', `techQueue_EndTime` = '0' ";
        $QryUpdateUser .= "WHERE `id` = {$UpdateUser['id']};";
        doquery($QryUpdateUser, 'users');
    }
}

?>
