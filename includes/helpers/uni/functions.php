<?php

function getUniFleetsSpeedFactor() {
    global $_GameConfig;

    return $_GameConfig['fleet_speed'] / 2500;
}

function isLabUpgradableWhileInUse() {
    global $_GameConfig;

    return ($_GameConfig['BuildLabWhileRun'] == 1);
}

?>
