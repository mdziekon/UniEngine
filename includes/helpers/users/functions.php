<?php

function getUsersCurrentIP() {
    return $_SERVER['REMOTE_ADDR'];
}

function getUsersTechLevel($techID, $user) {
    global $_Vars_GameElements;

    $userTechKey = $_Vars_GameElements[$techID];

    return $user[$userTechKey];
}

function getUsersEngineSpeedTechModifier($engineTechID, $user) {
    global $_Vars_TechSpeedModifiers;

    $engineTechSpeedModifier = $_Vars_TechSpeedModifiers[$engineTechID];
    $userTechLevel = getUsersTechLevel($engineTechID, $user);

    return (1 + ($engineTechSpeedModifier * $userTechLevel));
}

?>
