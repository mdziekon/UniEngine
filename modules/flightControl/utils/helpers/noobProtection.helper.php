<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

function isNoobProtectionEnabled() {
    global $_GameConfig;

    return ($_GameConfig['noobprotection'] == 1);
}

function isMissionNoobProtectionChecked($missionType) {
    $noobProtectionCheckedMissionTypes = [
        1,
        2,
        6,
        9,
    ];

    return in_array($missionType, $noobProtectionCheckedMissionTypes);
}

?>
