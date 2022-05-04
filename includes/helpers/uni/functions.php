<?php

function getUniFleetsSpeedFactor() {
    global $_GameConfig;

    return $_GameConfig['fleet_speed'] / 2500;
}

function isLabUpgradableWhileInUse() {
    global $_GameConfig;

    return ($_GameConfig['BuildLabWhileRun'] == 1);
}

abstract class FeatureType {
    const Expeditions = 'FeatureType::Expeditions';
}

function isFeatureEnabled($featureType) {
    switch ($featureType) {
        case FeatureType::Expeditions:
            return (
                defined('FEATURES__EXPEDITIONS__ISENABLED') &&
                boolval(constant('FEATURES__EXPEDITIONS__ISENABLED'))
            );
    }
}

/**
 * @return int Protection time limit (in seconds)
 */
function getIdleProtectionTimeLimit() {
    global $_GameConfig;

    return ($_GameConfig['no_idle_protect'] * TIME_DAY);
}

?>
