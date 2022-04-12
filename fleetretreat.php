<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');
include($_EnginePath.'modules/flightControl/_includes.php');
include($_EnginePath.'includes/functions/FleetControl_Retreat.php');

use UniEngine\Engine\Modules\FlightControl\Enums\RetreatResultType;

loggedCheck();

includeLang('fleet');

function retreatFleet($fleetId, &$user) {
    if ($fleetId <= 0) {
        return RetreatResultType::ErrorCantRetreatAnymore;
    }

    $retreatResult = FleetControl_Retreat("`fleet_id` = {$fleetId} AND `fleet_owner` = {$user['id']}");

    if ($retreatResult['Rows'] == 0) {
        return RetreatResultType::ErrorCantRetreatAnymore;
    }

    if (isset($retreatResult['Errors'][$fleetId])) {
        if ($retreatResult['Errors'][$fleetId] == 1) {
            return RetreatResultType::ErrorMissileStrikeRetreat;
        }
        if ($retreatResult['Errors'][$fleetId] == 2) {
            return RetreatResultType::ErrorCantRetreatAnymore;
        }
    }

    if (isset($retreatResult['Types'][$fleetId])) {
        if ($retreatResult['Types'][$fleetId] == 1) {
            return RetreatResultType::SuccessTurnedBack;
        }
        if ($retreatResult['Types'][$fleetId] == 2) {
            return RetreatResultType::SuccessRetreated;
        }
    }

    return RetreatResultType::ErrorCantRetreatAnymore;
}

function handleRetreatRequest() {
    global $_User;

    $fleetId = (
        isset($_POST['fleetid']) ?
            floor(floatval($_POST['fleetid'])) :
            0
    );
    $retreatResultCode = retreatFleet($fleetId, $_User);

    header("Location: fleet.php?ret=1&m={$retreatResultCode}");
    safeDie();
}

handleRetreatRequest();

?>
