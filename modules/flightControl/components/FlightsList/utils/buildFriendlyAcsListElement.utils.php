<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\FlightsList\Utils;

use UniEngine\Engine\Modules\Flights;

function _getFriendlyAcsFleetBehaviorDetails($params) {
    global $_Lang;

    $acsUnion = $params['acsUnion'];
    $currentTimestamp = $params['currentTimestamp'];

    if ($acsUnion['fleet_start_time'] >= $currentTimestamp) {
        return [
            'behavior' => $_Lang['fl_get_to_ttl'],
            'behaviorTxt' => $_Lang['fl_get_to'],
        ];
    }

    // TODO: Verify if it's actually possible, since ACS fleets shouldn't have stay time
    if (
        $acsUnion['fleet_end_stay'] > 0 &&
        $acsUnion['fleet_end_stay'] > $currentTimestamp
    ) {
        $isMissionExpedition = $acsUnion['fleet_mission'] == Flights\Enums\FleetMission::Expedition;

        return [
            'behavior' => (
                $isMissionExpedition ?
                    $_Lang['fl_explore_to_ttl'] :
                    $_Lang['fl_stay_to_ttl']
            ),
            'behaviorTxt' => (
                $isMissionExpedition ?
                    $_Lang['fl_explore_to'] :
                    $_Lang['fl_stay_to']
            ),
        ];
    }

    if ($acsUnion['fleet_end_time'] > $currentTimestamp) {
        return [
            'behavior' => $_Lang['fl_back_to_ttl'],
            'behaviorTxt' => $_Lang['fl_back_to'],
        ];
    }

    return [
        'behavior' => $_Lang['fl_cameback_ttl'],
        'behaviorTxt' => $_Lang['fl_cameback'],
    ];
}

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

    $fleetShipsRowTpl = gettemplate('fleet_fdetail');

    $acsId = $acsUnion['id'];
    $mainFleetId = $acsUnion['main_fleet_id'];

    $behaviorDetails = _getFriendlyAcsFleetBehaviorDetails($params);
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
        'FleetDetails'          => join(
            '',
            array_map_withkeys($unionShips, function ($shipCount, $shipId) use (&$_Lang, &$fleetShipsRowTpl) {
                return parsetemplate(
                    $fleetShipsRowTpl,
                    [
                        'Ship' => $_Lang['tech'][$shipId],
                        'Count' => prettyNumber($shipCount),
                    ]
                );
            })
        ),
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
        'FleetOriStart'         => date('d.m.Y<\b\r/>H:i:s', $acsUnion['start_time']),
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

        'addons'                => [
            'chronoApplet'      => InsertJavaScriptChronoApplet('ft_', $mainFleetId, $flightTimeRemaining),
        ],
    ];

    return $listElement;
}

?>
