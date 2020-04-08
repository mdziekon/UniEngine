<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Factories;

/**
 * @param array $params
 * @param string $params['reportID']
 * @param string $params['combatResult']
 * @param string $params['fleetRow']
 */
function createCombatResultForAlliedDefendersMessage($params) {
    global $_Lang;

    $combatResult = $params['combatResult'];
    $fleetRow = $params['fleetRow'];

    $message = [
        'msg_id' => '075',
        'args' => [
            $params['reportID'],
            (
                $combatResult === COMBAT_ATK ?
                    'red' :
                    (
                        $combatResult === COMBAT_DRAW ?
                        'orange' :
                        'green'
                    )
            ),
            $fleetRow['fleet_end_galaxy'],
            $fleetRow['fleet_end_system'],
            $fleetRow['fleet_end_planet'],
            $_Lang['BR_Target_'.$fleetRow['fleet_end_type']],

        ],
    ];

    return $message;
}

?>
