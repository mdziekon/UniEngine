<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Fetchers;

use UniEngine\Engine\Modules\Flights;

/**
 * @param array $props
 * @param number $props['logsRangeStart']
 * @param string $props['attackerUserId']
 * @param string $props['targetUserId']
 */
function fetchBashValidatorFlightLogEntries($props) {
    $logsRangeStart = $props['logsRangeStart'];
    $attackerUserId = $props['attackerUserId'];
    $targetUserId = $props['targetUserId'];

    $excludedDestructionReasons = [
        strval(Flights\Enums\FleetDestructionReason::INBATTLE_FIRSTROUND_NODAMAGE),
        strval(Flights\Enums\FleetDestructionReason::DRAW_NOBASH),
        strval(Flights\Enums\FleetDestructionReason::INBATTLE_OTHERROUND_NODAMAGE),
    ];
    $excludedDestructionReasonsStr = implode(', ', $excludedDestructionReasons);

    $checkFleetMissions = [
        strval(Flights\Enums\FleetMission::Attack),
        strval(Flights\Enums\FleetMission::UnitedAttack),
        strval(Flights\Enums\FleetMission::DestroyMoon),
    ];
    $checkFleetMissionsStr = implode(', ', $checkFleetMissions);

    $query = (
        "SELECT * " .
        "FROM {{table}} " .
        "WHERE " .
        "(`Fleet_Time_Start` + `Fleet_Time_ACSAdd`) >= {$logsRangeStart} AND " .
        "`Fleet_Owner` = {$attackerUserId} AND " .
        "`Fleet_End_Owner` = {$targetUserId} AND " .
        "`Fleet_Mission` IN ({$checkFleetMissionsStr}) AND " .
        "`Fleet_ReportID` > 0 AND " .
        "`Fleet_Destroyed_Reason` NOT IN ({$excludedDestructionReasonsStr}) " .
        ";"
    );

    $result = doquery($query, 'fleet_archive');

    return $result;
}

?>
