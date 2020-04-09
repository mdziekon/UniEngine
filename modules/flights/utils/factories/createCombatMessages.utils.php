<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Factories;

use UniEngine\Engine\Includes\Helpers\Common\Navigation;

/**
 * @param array $params
 * @param string $params['report']
 * @param string $params['combatResult']
 * @param string $params['fleetRow']
 * @param string $params['hasMoonBeenDestroyed'] (default: null)
 */
function createCombatResultForAlliedDefendersMessage($params) {
    global $_Lang;

    $report = $params['report'];
    $combatResult = $params['combatResult'];
    $fleetRow = $params['fleetRow'];
    $hasMoonBeenDestroyed = (
        isset($params['hasMoonBeenDestroyed']) ?
            $params['hasMoonBeenDestroyed'] :
            null
    );

    $hasMoonDestructionAttempt = (
        $combatResult === COMBAT_ATK &&
        $hasMoonBeenDestroyed !== null
    );

    $targetTypeLabelContent = $_Lang['BR_Target_'.$fleetRow['fleet_end_type']];

    $targetTypeLabel = (
        $hasMoonDestructionAttempt ?
        buildDOMElementHTML([
            'tagName' => 'span',
            'attrs' => [
                'style' => (
                    "color: " .
                    classNames([
                        'green' => ($hasMoonBeenDestroyed === 1),
                        'orange' => ($hasMoonBeenDestroyed !== 1),
                    ])
                )
            ],
            'contentHTML' => $targetTypeLabelContent,
        ]) :
        $targetTypeLabelContent
    );

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
            $targetTypeLabel,
            $reportHashlinkRelative,
            $reportHashlinkAbsolute
        ],
    ];

    return json_encode($message);
}

?>
