<?php

namespace UniEngine\Engine\Modules\Flights\Components\FlightListElement;

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
    global $_EnginePath, $_Lang, $InsertJSChronoApplet_GlobalIncluded;
    static $InsertJSChronoApplet_Included = false, $tplBodyCache = null, $ThisDate = null;

    include_once("{$_EnginePath}includes/functions/BuildFleetEventTable.php");

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
    if (!$ThisDate) {
        $ThisDate = date('d/m | ');
    }

    if (
        $InsertJSChronoApplet_GlobalIncluded !== true &&
        $InsertJSChronoApplet_Included === false
    ) {
        include('InsertJavaScriptChronoApplet.php');
        $InsertJSChronoApplet_Included = true;
    }

    $FleetRow = $props['flight'];
    $Status = $props['fleetStatus'];
    $Owner = $props['isDisplayedAsOwn'];
    $Phalanx = (
        isset($props['isPhalanxViewMode']) ?
            $props['isPhalanxViewMode'] :
            false
    );

    $FleetPrefix = (
        $Owner ?
            'own' :
            ''
    );

    $MissionType = $FleetRow['fleet_mission'];
    $FleetContent = _getFleetPopupedFleetLinkHTML(
        $FleetRow,
        ($Owner ? $_Lang['ov_fleet'] : $_Lang['ov_fleet2'])
    );
    $FleetMission = $_Lang['type_mission'][$MissionType];
    $StartType = $FleetRow['fleet_start_type'];
    $TargetType = $FleetRow['fleet_end_type'];

    if ($Status != 2) {
        if ($StartType == 1) {
            $StartID = $_Lang['ov_planet_to'];
        } else if ($StartType == 3) {
            $StartID = $_Lang['ov_moon_to'];
        }
        $StartID .= $FleetRow['start_name'].' ';
        $StartID .= _getStartAdressLinkHTML($FleetRow, $Phalanx);

        // TODO: use enum
        if ($MissionType != 15) {
            if ($TargetType == 1) {
                $TargetID = $_Lang['ov_planet_to_target'];
            } else if ($TargetType == 2) {
                $TargetID = $_Lang['ov_debris_to_target'];
            } else if($TargetType == 3) {
                $TargetID = $_Lang['ov_moon_to_target'];
            }
        } else {
            $TargetID = $_Lang['ov_explo_to_target'];
        }

        $TargetID .= $FleetRow['end_name'].' ';
        $TargetID .= _getTargetAdressLinkHTML($FleetRow, $Phalanx);
    } else {
        if ($StartType == 1) {
            $StartID = $_Lang['ov_back_planet'];
        } else if ($StartType == 3) {
            $StartID = $_Lang['ov_back_moon'];
        }

        $StartID .= $FleetRow['start_name'].' ';
        $StartID .= _getStartAdressLinkHTML($FleetRow, $Phalanx);

        // TODO: use enum
        if ($MissionType != 15) {
            if ($TargetType == 1) {
                $TargetID = $_Lang['ov_planet_from'];
            } else if($TargetType == 2) {
                $TargetID = $_Lang['ov_debris_from'];
            } else if($TargetType == 3) {
                $TargetID = $_Lang['ov_moon_from'];
            }
        } else {
            $TargetID = $_Lang['ov_explo_from'];
        }

        $TargetID .= $FleetRow['end_name'].' ';
        $TargetID .= _getTargetAdressLinkHTML($FleetRow, $Phalanx);
    }

    if ($Owner) {
        $EventString = (
            $Phalanx ?
                $_Lang['ov_une_phalanx'] :
                $_Lang['ov_une']
        );
        $EventString .= $FleetContent;
    } else {
        $EventString = $_Lang['ov_une_hostile'];
        $EventString .= $FleetContent;
        $EventString .= $_Lang['ov_hostile'];
        $EventString .= _getHostileFleetPlayerLinkHTML($FleetRow, $Phalanx);
    }

    if ($Status == 0) {
        $Time = $FleetRow['fleet_start_time'];
        $Rest = $Time - time();

        $EventString .= $_Lang['ov_vennant'];
        $EventString .= $StartID;
        $EventString .= $_Lang['ov_atteint'];
        $EventString .= $TargetID;
        $EventString .= $_Lang['ov_mission'];
    } else if ($Status == 1) {
        $Time = $FleetRow['fleet_end_stay'];
        $Rest = $Time - time();

        $EventString .= $_Lang['ov_vennant'];
        $EventString .= $StartID;

        // TODO: use enum
        if ($FleetRow['fleet_mission'] == 5) {
            $EventString .= $_Lang['ov_onorbit_stay'];
        } else {
            $EventString .= $_Lang['ov_explo_stay'];
        }

        $EventString .= $TargetID;
        $EventString .= $_Lang['ov_explo_mission'];
    } else if ($Status == 2) {
        $Time = $FleetRow['fleet_end_time'];
        $Rest = $Time - time();

        $EventString .= $_Lang['ov_rentrant'];
        $EventString .= $TargetID;
        $EventString .= $StartID;
        $EventString .= $_Lang['ov_mission'];
    }
    $EventString .= $FleetMission;

    $fleetStatusName = _getFleetStatus($Status);
    $entryCounterId = "_fleet_{$FleetRow['fleet_id']}_{$fleetStatusName}";


    $componentTPLData = [
        'fleet_status'      => $fleetStatusName,
        'fleet_prefix'      => $FleetPrefix,
        'fleet_style'       => _getMissionStyle($MissionType),
        'fleet_order'       => $entryCounterId,
        'fleet_countdown'   => pretty_time($Rest, true),
        'fleet_time'        => str_replace($ThisDate, '', date('d/m | H:i:s', $Time)),
        'fleet_descr'       => $EventString,
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], $componentTPLData);

    GlobalTemplate_AppendToAfterBody(InsertJavaScriptChronoApplet("", $entryCounterId, $Rest));

    return [
        'componentHTML' => $componentHTML
    ];
}

?>
