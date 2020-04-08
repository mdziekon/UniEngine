<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Factories;

use UniEngine\Engine\Includes\Helpers\Common\Navigation;

/**
 * @param array $params
 * @param string $params['report']
 * @param string $params['combatResult']
 * @param string $params['fleetRow']
 */
function createCombatResultForAlliedDefendersMessage($params) {
    global $_Lang;

    $report = $params['report'];
    $combatResult = $params['combatResult'];
    $fleetRow = $params['fleetRow'];

    $reportHashlinkRelative = Navigation\getPageURL(
        'battleReportByHash',
        [ 'hash' => $report['Hash'] ]
    );
    $reportHashlinkAbsolute = GAMEURL . $reportHashlinkRelative;

    $message = [
        'msg_id' => '075',
        'args' => [
            $report['ID'],
            classNames([
                'red' => ($combatResult === COMBAT_ATK),
                'orange' => ($combatResult === COMBAT_DRAW),
                'green' => ($combatResult === COMBAT_DEF),
            ]),
            $fleetRow['fleet_end_galaxy'],
            $fleetRow['fleet_end_system'],
            $fleetRow['fleet_end_planet'],
            $_Lang['BR_Target_'.$fleetRow['fleet_end_type']],
            $reportHashlinkRelative,
            $reportHashlinkAbsolute
        ],
    ];

    return json_encode($message);
}

?>
