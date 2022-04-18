<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;

use UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;
use UniEngine\Engine\Modules\Flights;

// TODO: this should most likely be a component
//  Arguments
//      - $params (Object)
//          - elementNo (Number)
//          - acsUnion (String)
//          - acsMainFleets (Record<mainFleetId: string, object>)
//          - currentTimestamp (Number)
//          - acsUnionsExtraSquads (Record<mainFleetId: string, object>)
//          - isJoiningThisUnion (Boolean)
//
function buildFriendlyAcsListElement($params) {
    global $_Lang;

    $elementNo = $params['elementNo'];
    $acsUnion = $params['acsUnion'];
    $acsMainFleets = $params['acsMainFleets'];
    $currentTimestamp = $params['currentTimestamp'];
    $acsUnionsExtraSquads = $params['acsUnionsExtraSquads'];
    $isJoiningThisUnion = $params['isJoiningThisUnion'];

    $acsId = $acsUnion['id'];
    $mainFleetId = $acsUnion['main_fleet_id'];

    $behaviorDetails = Utils\getFleetBehaviorDetails([
        'fleetEntry' => $acsUnion,
        'currentTimestamp' => $currentTimestamp,
    ]);
    $unionShips = [];
    $unionShipsCount = 0;

    if (!empty($acsUnionsExtraSquads[$mainFleetId])) {
        foreach ($acsUnionsExtraSquads[$mainFleetId] as $extraSquad) {
            if (empty($extraSquad['array'])) {
                continue;
            }

            foreach ($extraSquad['array'] as $shipId => $shipCount) {
                if (empty($unionShips[$shipId])) {
                    $unionShips[$shipId] = 0;
                }

                $unionShips[$shipId] += $shipCount;
                $unionShipsCount += $shipCount;
            }
        }
    }

    $mainFleetShips = String2Array($acsUnion['fleet_array']);

    foreach ($mainFleetShips as $shipId => $shipCount) {
        if (empty($unionShips[$shipId])) {
            $unionShips[$shipId] = 0;
        }

        $unionShips[$shipId] += $shipCount;
        $unionShipsCount += $shipCount;
    }

    $flightTimeRemaining = ($acsUnion['fleet_start_time'] - $currentTimestamp);

    $thisUnionListNo = (array_search($mainFleetId, array_keys($acsMainFleets)) + 1);

    $listElement = [
        'FleetNo'               => $elementNo,
        'FleetMissionColor'     => 'orange',
        'FleetMission'          => (
            $_Lang['type_mission'][Flights\Enums\FleetMission::UnitedAttack] .
            " #{$thisUnionListNo}"
        ),
        'ACSOwner'              => '<br/>('.$acsUnion['username'].')',
        'FleetBehaviour'        => $behaviorDetails['behavior'],
        'FleetBehaviourTxt'     => $behaviorDetails['behaviorTxt'],
        'FleetCount'            => prettyNumber($unionShipsCount),
        // Origin details
        'FleetOriGalaxy'        => $acsUnion['fleet_start_galaxy'],
        'FleetOriSystem'        => $acsUnion['fleet_start_system'],
        'FleetOriPlanet'        => $acsUnion['fleet_start_planet'],
        'FleetOriType'          => (
            $acsUnion['fleet_start_type'] == 1 ?
            'planet' :
            (
                $acsUnion['fleet_start_type'] == 3 ?
                'moon' :
                'debris'
            )
        ),
        'FleetOriStart'         => date('d.m.Y<\b\r/>H:i:s', $acsUnion['fleet_send_time']),
        // Destination details
        'FleetDesGalaxy'        => $acsUnion['end_galaxy'],
        'FleetDesSystem'        => $acsUnion['end_system'],
        'FleetDesPlanet'        => $acsUnion['end_planet'],
        'FleetDesType'          => (
            $acsUnion['end_type'] == 1 ?
            'planet' :
            (
                $acsUnion['end_type'] == 3 ?
                'moon' :
                'debris'
            )
        ),
        'FleetDesArrive'        => date('d.m.Y<\b\r/>H:i:s', $acsUnion['fleet_start_time']),
        // Times
        'FleetEndTime'          => '-',
        'FleetFlyTargetTime'    => (
            '<b class="lime flRi" id="bxxft_' .
            $mainFleetId .
            '">' .
            pretty_time($flightTimeRemaining, true, 'D') .
            '</b>'
        ),
        'FleetHideComeBackTime'     => ' class="hide"',
        'FleetHideTargetorBackTime' => ' class="hide"',
        'FleetHideStayTime'         => ' class="hide"',
        'FleetHideRetreatTime'      => ' class="hide"',

        'FleetOrders'           => buildDOMElementHTML([
            'tagName'           => 'input',
            'contentHTML'       => (
                '<br/>' .
                $_Lang['fl_acs_joinnow']
            ),
            'attrs'             => [
                'type'          => 'radio',
                'value'         => $acsId,
                'class'         => 'setACS_ID pad5',
                'name'          => 'acs_select',
                'checked'       => (
                    $isJoiningThisUnion ?
                        'checked' :
                        null
                ),
            ],
        ]),

        'data'                  => [
            'ships' => $unionShips,
        ],

        'addons'                => [
            'chronoApplets'     => [
                InsertJavaScriptChronoApplet('ft_', $mainFleetId, $flightTimeRemaining),
            ],
        ],
    ];

    return $listElement;
}

?>
