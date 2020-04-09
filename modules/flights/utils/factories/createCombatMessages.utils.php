<?php

namespace UniEngine\Engine\Modules\Flights\Utils\Factories;

use UniEngine\Engine\Includes\Helpers\Common\Navigation;
use UniEngine\Engine\Includes\Helpers\World\Resources;

/**
 * @param array $params
 * @param string $params['missionType]
 * @param array $params['report']
 * @param array $params['combatResult']
 * @param array $params['totalAttackersResourcesLoss']
 * @param array $params['totalDefendersResourcesLoss']
 * @param array $params['totalResourcesPillage']
 *              (default: `Record<PillagableResourceKey, 0>`)
 * @param array $params['fleetRow']
 * @param string $params['hasMoonBeenDestroyed'] (default: null)
 * @param string $params['hasFleetBeenDestroyedByMoon'] (default: null)
 */
function createCombatResultForAttackersMessage($params) {
    $missionType = $params['missionType'];
    $report = $params['report'];
    $combatResult = $params['combatResult'];
    $totalAttackersResourcesLoss = $params['totalAttackersResourcesLoss'];
    $totalDefendersResourcesLoss = $params['totalDefendersResourcesLoss'];
    $totalResourcesPillage = (
        isset($params['totalResourcesPillage']) ?
        $params['totalResourcesPillage'] :
        null
    );
    $fleetRow = $params['fleetRow'];
    $hasMoonBeenDestroyed = (
        isset($params['hasMoonBeenDestroyed']) ?
            $params['hasMoonBeenDestroyed'] :
            null
    );
    $hasFleetBeenDestroyedByMoon = (
        isset($params['hasFleetBeenDestroyedByMoon']) ?
            $params['hasFleetBeenDestroyedByMoon'] :
            null
    );

    if ($totalResourcesPillage === null) {
        $totalResourcesPillage = [];
        $pillagableResourceKeys = Resources\getKnownPillagableResourceKeys();

        foreach ($pillagableResourceKeys as $resourceKey) {
            $totalResourcesPillage[$resourceKey] = 0;
        }
    }

    $hasMoonDestructionAttempt = (
        $combatResult === COMBAT_ATK &&
        $hasMoonBeenDestroyed !== null
    );

    $reportHashlinkRelative = Navigation\getPageURL(
        'battleReportByHash',
        [ 'hash' => $report['Hash'] ]
    );
    $reportHashlinkAbsolute = GAMEURL . $reportHashlinkRelative;

    $message = [
        'msg_id' => (
            $missionType != 9 ?
            '071' :
            '072'
        ),
        'args' => [
            $report['ID'],
            classNames([
                '#AD5CD6' => ($combatResult === COMBAT_ATK && $hasFleetBeenDestroyedByMoon),
                'red' => ($combatResult === COMBAT_DEF),
                'orange' => ($combatResult === COMBAT_DRAW),
                'green' => ($combatResult === COMBAT_ATK && !$hasFleetBeenDestroyedByMoon),
            ]),
            $fleetRow['fleet_end_galaxy'],
            $fleetRow['fleet_end_system'],
            $fleetRow['fleet_end_planet'],
            _createTargetTypeLabel([
                'hasMoonBeenDestroyed' => $hasMoonBeenDestroyed,
                'hasMoonDestructionAttempt' => $hasMoonDestructionAttempt,
                'fleetRow' => $fleetRow,
            ]),
            prettyNumber(array_sum($totalAttackersResourcesLoss['realLoss'])),
            prettyNumber(array_sum($totalDefendersResourcesLoss['realLoss'])),
            prettyNumber($totalResourcesPillage['metal']),
            prettyNumber($totalResourcesPillage['crystal']),
            prettyNumber($totalResourcesPillage['deuterium']),
            prettyNumber(
                $totalAttackersResourcesLoss['recoverableLoss']['metal'] +
                $totalDefendersResourcesLoss['recoverableLoss']['metal']
            ),
            prettyNumber(
                $totalAttackersResourcesLoss['recoverableLoss']['crystal'] +
                $totalDefendersResourcesLoss['recoverableLoss']['crystal']
            ),
            $reportHashlinkRelative,
            $reportHashlinkAbsolute
        ],
    ];

    return json_encode($message);
}

/**
 * @param array $params
 * @param string $params['report']
 * @param string $params['combatResult']
 * @param string $params['fleetRow']
 * @param string $params['hasMoonBeenDestroyed'] (default: null)
 */
function createCombatResultForAlliedDefendersMessage($params) {
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
            _createTargetTypeLabel([
                'hasMoonBeenDestroyed' => $hasMoonBeenDestroyed,
                'hasMoonDestructionAttempt' => $hasMoonDestructionAttempt,
                'fleetRow' => $fleetRow,
            ]),
            $reportHashlinkRelative,
            $reportHashlinkAbsolute
        ],
    ];

    return json_encode($message);
}

/**
 * @param array $params
 * @param array $params['fleetRow']
 * @param boolean $params['hasMoonBeenDestroyed']
 * @param boolean $params['hasMoonDestructionAttempt']
 */
function _createTargetTypeLabel($params) {
    global $_Lang;

    $fleetEndType = $params['fleetRow']['fleet_end_type'];
    $hasMoonBeenDestroyed = $params['hasMoonBeenDestroyed'];
    $hasMoonDestructionAttempt = $params['hasMoonDestructionAttempt'];

    $targetTypeLabelContent = $_Lang['BR_Target_'.$fleetEndType];

    if (!$hasMoonDestructionAttempt) {
        return $targetTypeLabelContent;
    }

    return buildDOMElementHTML([
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
    ]);
}

?>
