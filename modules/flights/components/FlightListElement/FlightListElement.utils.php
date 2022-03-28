<?php

namespace UniEngine\Engine\Modules\Flights\Components\FlightListElement\Utils;

function getFleetStatus($statusID) {
    $fleetStatuses = [
        0 => 'flight',
        1 => 'holding',
        2 => 'return'
    ];

    return $fleetStatuses[$statusID];
}

function getMissionStyle($missionID) {
    $missionStyles = [
        1 => 'attack',
        2 => 'federation',
        3 => 'transport',
        4 => 'deploy',
        5 => 'hold',
        6 => 'espionage',
        7 => 'colony',
        8 => 'harvest',
        9 => 'destroy',
        10 => 'missile',
    ];

    return $missionStyles[$missionID];
}

function getUserCustomFleetColorsStylesHTML(&$user) {
    if (empty($user['settings_FleetColors'])) {
        return null;
    }

    $fleetColorsSettings = json_decode($user['settings_FleetColors'], true);

    $stylesData = [];

    foreach ($fleetColorsSettings as $fleetType => $perMissionColors) {
        $isOwnFleet = ($fleetType !== 'nonown');
        $isOwnComeback = ($fleetType === 'owncb');
        $missionType = (
            $isOwnComeback ?
            "flight" :
            "return"
        );

        foreach ($perMissionColors as $missionID => $missionColor) {
            if (empty($missionColor)) {
                continue;
            }

            $stylesData[] = [
                'MissionType' => $missionType,
                'MissionName' => (
                    ($isOwnFleet ? 'own' : '') .
                    getMissionStyle($missionID)
                ),
                'MissionColor' => $missionColor
            ];

            if (
                $missionID == 5 &&
                !$isOwnComeback
            ) {
                $stylesData[] = [
                    'MissionType' => 'holding',
                    'MissionName' => (
                        ($isOwnFleet ? 'own' : '') .
                        getMissionStyle($missionID)
                    ),
                    'MissionColor' => $missionColor
                ];
            }
        }
    }

    if (empty($stylesData)) {
        return null;
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplColorRow = $localTemplateLoader('fleetColorsRow');
    $tplColorsStyles = $localTemplateLoader('fleetColorsStylingBody');

    $missionStyles = array_map(
        function ($styleData) use ($tplColorRow) {
            return parsetemplate($tplColorRow, $styleData);
        },
        $stylesData
    );
    $missionStyles = implode(' ', $missionStyles);

    return parsetemplate($tplColorsStyles, [ 'InsertStyles' => $missionStyles ]);
}

//  Arguments:
//      - $fleetRow (Object)
//      - $isStartLink (Boolean)
//      - $options (Object)
//          - linkClass (String) [default: "white"]
//          - isOpenedInPopup (Boolean) [default: false]
//
function _getFleetsGalaxyPositionHyperlinkHTML($fleetRow, $isStartLink, $options) {
    $isOpenedInPopup = $options['isOpenedInPopup'];
    $linkClass = $options['linkClass'];

    if (!$linkClass) {
        $linkClass = 'white';
    }

    $position = (
        $isStartLink ?
        [
            'galaxy' => $fleetRow['fleet_start_galaxy'],
            'system' => $fleetRow['fleet_start_system'],
            'planet' => $fleetRow['fleet_start_planet'],
        ] :
        [
            'galaxy' => $fleetRow['fleet_end_galaxy'],
            'system' => $fleetRow['fleet_end_system'],
            'planet' => $fleetRow['fleet_end_planet'],
        ]
    );

    $linkParams = [
        'text' => "[{$position['galaxy']}:{$position['system']}:{$position['planet']}]",
        'href' => 'galaxy.php',
        'query' => [
            'mode' => '3',
            'galaxy' => $position['galaxy'],
            'system' => $position['system'],
            'planet' => $position['planet'],
        ],
        'attrs' => [
            'class' => $linkClass,
            'onclick' => (
                $isOpenedInPopup ?
                'opener.location = this.href; opener.focus(); return false;' :
                null
            )
        ]
    ];

    return buildLinkHTML($linkParams);
}

function getStartAdressLinkHTML($fleetRow, $FromWindow = false) {
    return _getFleetsGalaxyPositionHyperlinkHTML($fleetRow, true, [ 'isOpenedInPopup' => $FromWindow ]);
}

function getTargetAdressLinkHTML($fleetRow, $FromWindow = false) {
    return _getFleetsGalaxyPositionHyperlinkHTML($fleetRow, false, [ 'isOpenedInPopup' => $FromWindow ]);
}

function getHostileFleetPlayerLinkHTML($fleetRow, $FromWindow = false) {
    global $_Lang, $_SkinPath;

    $isOpenedInPopup = $FromWindow;

    $linkParams = [
        'text' => (
            "{$fleetRow['owner_name']} " .
            buildDOMElementHTML([
                'tagName' => 'img',
                'attrs' => [
                    'src' => "{$_SkinPath}/img/m.gif",
                    'alt' => $_Lang['ov_message'],
                    'title' => $_Lang['ov_message'],
                    'border' => '0'
                ]
            ])
        ),
        'href' => 'messages.php',
        'query' => [
            'mode' => 'write',
            'uid' => $fleetRow['fleet_owner']
        ],
        'attrs' => [
            'onclick' => (
                $isOpenedInPopup ?
                'opener.location = this.href; opener.focus(); return false;' :
                null
            )
        ]
    ];

    return buildLinkHTML($linkParams);
}

function getFleetPopupedFleetLinkHTML($fleetRow, $popupLabel) {
    global $_Lang;

    $popupElements = [];

    $fleetShips = String2Array($fleetRow['fleet_array']);

    if (!empty($fleetShips)) {
        foreach($fleetShips as $shipID => $shipsCount) {
            $shipLabel = $_Lang['tech'][$shipID];
            $shipsCountDisplay = prettyNumber($shipsCount);

            $popupElements[] = "<tr><th class='flLabel sh'>{$shipLabel}:</th><th class='flVal'>{$shipsCountDisplay}</th></tr>";
        }
    }

    $resourcesDefs = [
        'fleet_resource_metal' => [
            'label' => $_Lang['Metal']
        ],
        'fleet_resource_crystal' => [
            'label' => $_Lang['Crystal']
        ],
        'fleet_resource_deuterium' => [
            'label' => $_Lang['Deuterium']
        ],
    ];

    $resourceRows = [];

    foreach ($resourcesDefs as $resourceKey => $resourceDetails) {
        if (!($fleetRow[$resourceKey] > 0)) {
            continue;
        }

        $resourceLabel = $resourceDetails['label'];
        $resourceAmount = prettyNumber($fleetRow[$resourceKey]);

        $resourceRows[] = "<tr><th class='flLabel rs'>{$resourceLabel}:</th><th class='flVal'>{$resourceAmount}</th></tr>";
    }

    if (!empty($resourceRows)) {
        $popupElements[] = "<tr><th class='flRes' colspan='2'>&nbsp;</th></tr>";
        foreach ($resourceRows as $resourceRow) {
            $popupElements[] = $resourceRow;
        }
    }

    $popupHTML = '<table style=\'width: 100%;\'>'.implode('', $popupElements).'</table>';

    return '<a class="white flShips" title="' . $popupHTML . '">' . $popupLabel . '</a>';
}

?>
