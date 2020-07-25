<?php

function MissionCaseExpeditionNew ($FleetRow, &$_FleetCache) {
    $Return = [];
    $Now = time();

    $thisFleetID = $FleetRow['fleet_id'];

    if ($FleetRow['calcType'] == 1) {
        $Return['FleetArchive'][$thisFleetID]['Fleet_Calculated_Mission'] = true;
        $Return['FleetArchive'][$thisFleetID]['Fleet_Calculated_Mission_Time'] = $Now;

        $_FleetCache['fleetRowUpdate'][$thisFleetID]['fleet_mess'] = 1;
        $_FleetCache['fleetRowStatus'][$thisFleetID]['calcCounter'] += 1;
        $_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate'] = true;

        // TODO: Probably nothing else to do here, we just started exploring
    }

    if ($FleetRow['calcType'] == 2) {
        $_FleetCache['fleetRowUpdate'][$thisFleetID]['fleet_mess'] = 2;
        $_FleetCache['fleetRowStatus'][$thisFleetID]['calcCounter'] += 1;
        $_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate'] = true;

        // TODO: Exploration has ended, calculate the outcome
        // TODO: Check if the fleet has been destroyed, damaged, expanded, or found resources
    }

    if ($FleetRow['calcType'] == 3) {
        if (
            isset($_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate']) &&
            $_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate'] === true &&
            !empty($_FleetCache['fleetRowUpdate'][$thisFleetID])
        ) {
            foreach ($_FleetCache['fleetRowUpdate'][$thisFleetID] as $Key => $Value) {
                $FleetRow[$Key] = $Value;
            }
        }

        $Return['FleetsToDelete'][] = $thisFleetID;
        $Return['FleetArchive'][$thisFleetID]['Fleet_Calculated_ComeBack'] = true;
        $Return['FleetArchive'][$thisFleetID]['Fleet_Calculated_ComeBack_Time'] = $Now;

        RestoreFleetToPlanet($FleetRow, true, $_FleetCache);

        $_FleetCache['fleetRowStatus'][$thisFleetID]['calcCounter'] += 1;
        $_FleetCache['fleetRowStatus'][$thisFleetID]['needUpdate'] = false;
    }

    if ($_FleetCache['fleetRowStatus'][$thisFleetID]['calcCounter'] == $_FleetCache['fleetRowStatus'][$thisFleetID]['calcCount']) {
        if ($FleetRow['calcType'] != 3) {
            $_FleetCache['updateFleets'][$thisFleetID]['fleet_mess'] = $FleetRow['fleet_mess'];
        }
    }

    return $Return;
}

?>
