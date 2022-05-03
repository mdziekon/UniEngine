<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

use UniEngine\Engine\Modules\Flights;

function isNoobProtectionEnabled() {
    global $_GameConfig;

    return ($_GameConfig['noobprotection'] == 1);
}

function isMissionNoobProtectionChecked($missionType) {
    if (isMissionAntiBashProtectionChecked($missionType)) {
        return true;
    }

    $noobProtectionCheckedMissionTypes = [
        Flights\Enums\FleetMission::Spy,
        Flights\Enums\FleetMission::MissileAttack,
    ];

    return in_array($missionType, $noobProtectionCheckedMissionTypes);
}

function isMissionAntiBashProtectionChecked($missionType) {
    $antiBashCheckedMissionTypes = [
        Flights\Enums\FleetMission::Attack,
        Flights\Enums\FleetMission::UnitedAttack,
        Flights\Enums\FleetMission::DestroyMoon,
    ];

    return in_array($missionType, $antiBashCheckedMissionTypes);
}

?>
