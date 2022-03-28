<?php

namespace UniEngine\Engine\Modules\Flights\Components\FlightListElement;

use UniEngine\Engine\Modules\Flights\Enums;

//  Arguments
//      - $props (Object)
//          - flight (FleetRow)
//          - fleetStatus (Number)
//          - isDisplayedAsOwn (Boolean)
//          - isPhalanxViewMode (Boolean) [default: false]
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_EnginePath, $_Lang;
    static $tplBodyCache = null, $currentDatePrefix = null;

    include_once("{$_EnginePath}includes/functions/BuildFleetEventTable.php");
    include_once("{$_EnginePath}includes/functions/InsertJavaScriptChronoApplet.php");

    if (!$tplBodyCache) {
        global $_User;

        $localTemplateLoader = createLocalTemplateLoader(__DIR__);
        $tplBodyCache = [
            'body' => $localTemplateLoader('body'),
        ];

        GlobalTemplate_AppendToAfterBody($localTemplateLoader('globalFiles'));

        $userCustomFleetColorsStylesHTML = _getUserCustomFleetColorsStylesHTML($_User);

        if ($userCustomFleetColorsStylesHTML) {
            GlobalTemplate_AppendToAfterBody($userCustomFleetColorsStylesHTML);
        }
    }
    if (!$currentDatePrefix) {
        $currentDatePrefix = date('d/m | ');
    }

    $flight = $props['flight'];
    $fleetStatus = $props['fleetStatus'];
    $isDisplayedAsOwn = $props['isDisplayedAsOwn'];
    $isPhalanxViewMode = (
        isset($props['isPhalanxViewMode']) ?
            $props['isPhalanxViewMode'] :
            false
    );

    $fleetMissionType = $flight['fleet_mission'];
    $originType = $flight['fleet_start_type'];
    $targetType = $flight['fleet_end_type'];
    $fleetComposition = _getFleetPopupedFleetLinkHTML(
        $flight,
        ($isDisplayedAsOwn ? $_Lang['ov_fleet'] : $_Lang['ov_fleet2'])
    );

    if ($fleetStatus != 2) {
        if ($originType == 1) {
            $originLabel = $_Lang['ov_planet_to'];
        } else if ($originType == 3) {
            $originLabel = $_Lang['ov_moon_to'];
        }
        $originLabel .= $flight['start_name'].' ';
        $originLabel .= _getStartAdressLinkHTML($flight, $isPhalanxViewMode);

        if ($fleetMissionType != Enums\FleetMission::Expedition) {
            if ($targetType == 1) {
                $targetLabel = $_Lang['ov_planet_to_target'];
            } else if ($targetType == 2) {
                $targetLabel = $_Lang['ov_debris_to_target'];
            } else if($targetType == 3) {
                $targetLabel = $_Lang['ov_moon_to_target'];
            }
        } else {
            $targetLabel = $_Lang['ov_explo_to_target'];
        }

        $targetLabel .= $flight['end_name'].' ';
        $targetLabel .= _getTargetAdressLinkHTML($flight, $isPhalanxViewMode);
    } else {
        if ($originType == 1) {
            $originLabel = $_Lang['ov_back_planet'];
        } else if ($originType == 3) {
            $originLabel = $_Lang['ov_back_moon'];
        }

        $originLabel .= $flight['start_name'].' ';
        $originLabel .= _getStartAdressLinkHTML($flight, $isPhalanxViewMode);

        if ($fleetMissionType != Enums\FleetMission::Expedition) {
            if ($targetType == 1) {
                $targetLabel = $_Lang['ov_planet_from'];
            } else if($targetType == 2) {
                $targetLabel = $_Lang['ov_debris_from'];
            } else if($targetType == 3) {
                $targetLabel = $_Lang['ov_moon_from'];
            }
        } else {
            $targetLabel = $_Lang['ov_explo_from'];
        }

        $targetLabel .= $flight['end_name'].' ';
        $targetLabel .= _getTargetAdressLinkHTML($flight, $isPhalanxViewMode);
    }

    if ($isDisplayedAsOwn) {
        $eventDescription = (
            $isPhalanxViewMode ?
                $_Lang['ov_une_phalanx'] :
                $_Lang['ov_une']
        );
        $eventDescription .= $fleetComposition;
    } else {
        $eventDescription = $_Lang['ov_une_hostile'];
        $eventDescription .= $fleetComposition;
        $eventDescription .= $_Lang['ov_hostile'];
        $eventDescription .= _getHostileFleetPlayerLinkHTML($flight, $isPhalanxViewMode);
    }

    if ($fleetStatus == 0) {
        $eventTimestamp = $flight['fleet_start_time'];
        $eventTimeRemaining = $eventTimestamp - time();

        $eventDescription .= $_Lang['ov_vennant'];
        $eventDescription .= $originLabel;
        $eventDescription .= $_Lang['ov_atteint'];
        $eventDescription .= $targetLabel;
        $eventDescription .= $_Lang['ov_mission'];
    } else if ($fleetStatus == 1) {
        $eventTimestamp = $flight['fleet_end_stay'];
        $eventTimeRemaining = $eventTimestamp - time();

        $eventDescription .= $_Lang['ov_vennant'];
        $eventDescription .= $originLabel;

        if ($flight['fleet_mission'] == Enums\FleetMission::Hold) {
            $eventDescription .= $_Lang['ov_onorbit_stay'];
        } else {
            $eventDescription .= $_Lang['ov_explo_stay'];
        }

        $eventDescription .= $targetLabel;
        $eventDescription .= $_Lang['ov_explo_mission'];
    } else if ($fleetStatus == 2) {
        $eventTimestamp = $flight['fleet_end_time'];
        $eventTimeRemaining = $eventTimestamp - time();

        $eventDescription .= $_Lang['ov_rentrant'];
        $eventDescription .= $targetLabel;
        $eventDescription .= $originLabel;
        $eventDescription .= $_Lang['ov_mission'];
    }
    $eventDescription .= $_Lang['type_mission'][$fleetMissionType];

    $fleetStatusName = _getFleetStatus($fleetStatus);
    $entryCounterId = "_fleet_{$flight['fleet_id']}_{$fleetStatusName}";


    $componentTPLData = [
        'fleet_status'      => $fleetStatusName,
        'fleet_prefix'      => (
            $isDisplayedAsOwn ?
                'own' :
                ''
        ),
        'fleet_style'       => _getMissionStyle($fleetMissionType),
        'fleet_order'       => $entryCounterId,
        'fleet_countdown'   => pretty_time($eventTimeRemaining, true),
        'fleet_time'        => str_replace($currentDatePrefix, '', date('d/m | H:i:s', $eventTimestamp)),
        'fleet_descr'       => $eventDescription,
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], $componentTPLData);

    GlobalTemplate_AppendToAfterBody(InsertJavaScriptChronoApplet("", $entryCounterId, $eventTimeRemaining));

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
