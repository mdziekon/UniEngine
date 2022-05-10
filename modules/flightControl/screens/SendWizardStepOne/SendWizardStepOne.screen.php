<?php

namespace UniEngine\Engine\Modules\FlightControl\Screens\SendWizardStepOne;

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Modules\Flights;
use UniEngine\Engine\Modules\FlightControl;
use UniEngine\Engine\Modules\FlightControl\Screens\SendWizardStepOne;

/**
 * @param Object $props
 * @param Object $props['inputs']
 * @param Object $props['inputs']['queryParams']
 * @param Object $props['inputs']['formData']
 * @param ObjectRef $props['user']
 * @param ObjectRef $props['planet']
 * @param Number $props['currentTimestamp']
 *
 * @return Object $result
 * @return String $result['componentHTML']
 */
function render($props) {
    global $_Lang;

    $inputQueryParams = $props['inputs']['queryParams'];
    $inputFormData = $props['inputs']['formData'];
    $user = &$props['user'];
    $planet = &$props['planet'];
    $currentTimestamp = $props['currentTimestamp'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];


    $formInputsTplData = [];
    $unionIdToJoin = null;

    $unionManagementComponent = null;

    if (
        isset($inputFormData['acsmanage']) &&
        $inputFormData['acsmanage'] == 'open'
    ) {
        $unionManagementComponent = SendWizardStepOne\Components\UnionManagement\render([
            'unionOwner' => $user,
            'currentTimestamp' => $currentTimestamp,
            'input' => $inputFormData,
        ]);
    }

    $isQuickTransportOptionUsed = (
        isset($inputQueryParams['quickres']) &&
        $inputQueryParams['quickres'] == 1 &&
        isPro($user)
    );

    $gobackFleet = [];

    if (
        isset($inputFormData['gobackUsed']) &&
        !empty($inputFormData['FleetArray'])
    ) {
        $gobackFleet = String2Array($inputFormData['FleetArray']);
        $gobackFleet = object_map($gobackFleet, function ($shipCount, $shipId) {
            if (!Elements\isShip($shipId)) {
                return [ null, $shipId ];
            }

            return [ $shipCount, $shipId ];
        });
        $gobackFleet = Collections\compact($gobackFleet);
    }

    $formInputsTplData['P_SetQuickRes'] = (
        $isQuickTransportOptionUsed ? '1' : '0'
    );

    if (isset($inputFormData['gobackUsed'])) {
        $preserveFormData = [
            'speed' => $inputFormData['speed'],
        ];

        if (!empty($inputFormData['gobackVars'])) {
            $decodedPreservedFormData = json_decode(base64_decode($inputFormData['gobackVars']), true);
            if (is_array($decodedPreservedFormData)) {
                $preserveFormData = array_merge($decodedPreservedFormData, $preserveFormData);
            }
        }

        $formInputsTplData['SetJoiningACSID'] = (isset($inputFormData['getacsdata']) ? $inputFormData['getacsdata'] : null);
        $formInputsTplData['P_Galaxy'] = (isset($inputFormData['galaxy']) ? $inputFormData['galaxy'] : null);
        $formInputsTplData['P_System'] = (isset($inputFormData['system']) ? $inputFormData['system'] : null);
        $formInputsTplData['P_Planet'] = (isset($inputFormData['planet']) ? $inputFormData['planet'] : null);
        $formInputsTplData['P_PlType'] = (isset($inputFormData['planettype']) ? $inputFormData['planettype'] : null);
        $formInputsTplData['P_Mission'] = (isset($inputFormData['target_mission']) ? $inputFormData['target_mission'] : null);
        $formInputsTplData['P_SetQuickRes'] = (isset($inputFormData['quickres']) ? $inputFormData['quickres'] : null);
        $formInputsTplData['P_GoBackVars'] = base64_encode(json_encode($preserveFormData));
    } else {
        $joinUnionIdRaw = (
            isset($inputQueryParams['joinacs']) ?
                $inputQueryParams['joinacs'] :
                (
                    isset($inputFormData['getacsdata']) ?
                        $inputFormData['getacsdata'] :
                        0
                )
        );
        $joinUnionId = intval($joinUnionIdRaw);

        $formInputsTplData['SetJoiningACSID'] = $joinUnionId;
        $formInputsTplData['P_Galaxy'] = (isset($inputQueryParams['galaxy']) ? intval($inputQueryParams['galaxy']) : null);
        $formInputsTplData['P_System'] = (isset($inputQueryParams['system']) ? intval($inputQueryParams['system']) : null);
        $formInputsTplData['P_Planet'] = (isset($inputQueryParams['planet']) ? intval($inputQueryParams['planet']) : null);
        $formInputsTplData['P_PlType'] = (isset($inputQueryParams['planettype']) ? intval($inputQueryParams['planettype']) : null);
        $formInputsTplData['P_Mission'] = (isset($inputQueryParams['target_mission']) ? intval($inputQueryParams['target_mission']) : null);

        if (
            $isQuickTransportOptionUsed &&
            (
                !isset($inputQueryParams['target_mission']) ||
                $inputQueryParams['target_mission'] != Flights\Enums\FleetMission::Transport
            )
        ) {
            if ($user['settings_mainPlanetID'] != $planet['id']) {
                $quickTransportPlanetPosition = doquery("SELECT `galaxy`, `system`, `planet` FROM {{table}} WHERE `id` = {$user['settings_mainPlanetID']};", 'planets', true);
            } else {
                $quickTransportPlanetPosition = [
                    'galaxy' => '',
                    'system' => '',
                    'planet' => '',
                ];
            }

            $formInputsTplData['P_Galaxy'] = $quickTransportPlanetPosition['galaxy'];
            $formInputsTplData['P_System'] = $quickTransportPlanetPosition['system'];
            $formInputsTplData['P_Planet'] = $quickTransportPlanetPosition['planet'];
            $formInputsTplData['P_PlType'] = 1;
            $formInputsTplData['P_Mission'] = Flights\Enums\FleetMission::Transport;
        }
    }

    $unionIdToJoin = $formInputsTplData['SetJoiningACSID'];

    $resourcesToLoad = Resources\sumAllPlanetTransportableResources($planet);
    $preselectedCargoShips = (
        $isQuickTransportOptionUsed ?
            FlightControl\Utils\Helpers\calculateCargoFleetArray([
                'planet' => $planet,
                'user' => $user,
            ]) :
            []
    );

    /**
     * Flights list is purposefully rendered after UnionManagement
     * to allow any new Union entries to be inserted before rendering the list
     */
    $flightsList = SendWizardStepOne\Components\FlightsList\render([
        'userId' => $user['id'],
        'currentTimestamp' => $currentTimestamp,
        'unionIdToJoin' => $unionIdToJoin,
    ])['componentHTML'];

    $smartFleetBlockadeComponent = FlightControl\Components\SmartFleetBlockadeInfoBox\render();
    $retreatInfoBoxComponent = null;
    if (
        isset($inputQueryParams['ret']) &&
        isset($inputQueryParams['m'])
    ) {
        $retreatInfoBoxComponent = SendWizardStepOne\Components\RetreatInfoBox\render([
            'eventCode' => $inputQueryParams['m'],
        ]);
    }
    $availableShipsListComponent = SendWizardStepOne\Components\AvailableShipsList\render([
        'planet' => $planet,
        'user' => $user,
        'preselectedShips' => (
            !empty($gobackFleet) ?
                $gobackFleet :
                $preselectedCargoShips
        ),
    ]);
    $shipsJSData = FlightControl\Utils\Factories\createPlanetShipsJSObject([
        'planet' => $planet,
        'user' => $user,
    ]);

    $userMaxFleetSlotsCount = FlightControl\Utils\Helpers\getUserFleetSlotsCount([
        'user' => $user,
        'timestamp' => $currentTimestamp,
    ]);
    $fleetsInFlightCounters = FlightControl\Utils\Helpers\getFleetsInFlightCounters([
        'userId' => $user['id'],
    ]);
    $allFleetsInFlightCount = $fleetsInFlightCounters['allFleetsInFlight'];
    $expeditionsInFlightCount = $fleetsInFlightCounters['expeditionsInFlight'];

    $hasAvailableShips = !empty($availableShipsListComponent['componentHTML']);
    $hideHTMLClass = ' class="hide"';

    $componentTplData = [
        'Insert_ACSForm' => (
            $unionManagementComponent ?
                $unionManagementComponent['componentHTML'] :
                ''
        ),
        'FlyingFleetsRows' => (
            empty($flightsList['elementsList']) ?
                '<tr><th colspan="8">-</th></tr>' :
                $flightsList['elementsList']
        ),
        'ChronoAppletsScripts' => $flightsList['chronoApplets'],

        'ShipsRow' => $availableShipsListComponent['componentHTML'],
        'Insert_ShipsData' => json_encode($shipsJSData),

        'P_TotalPlanetResources' => (string) $resourcesToLoad,
        'P_StorageColor' => (
            $resourcesToLoad == 0 ?
                'lime' :
                'orange'
        ),
        'P_HideQuickRes' => (
            !isPro() ?
                'hide' :
                ''
        ),
        'P_AllowPrettyInputBox' => (
            ($user['settings_useprettyinputbox'] == 1) ?
                'true' :
                'false'
        ),
        'P_Expeditions_isHidden_style' => (
            isFeatureEnabled(\FeatureType::Expeditions) ?
                '' :
                'display: none;'
        ),

        'P_MaxFleetSlots' => $userMaxFleetSlotsCount,
        'P_MaxExpedSlots' => FlightControl\Utils\Helpers\getUserExpeditionSlotsCount([
            'user' => $user,
        ]),
        'P_FlyingFleetsCount' => (string) $allFleetsInFlightCount,
        'P_FlyingExpeditions' => (string) $expeditionsInFlightCount,
        'P_HideNoFreeSlots' => (
            (
                $allFleetsInFlightCount > 0 &&
                $allFleetsInFlightCount >= $userMaxFleetSlotsCount
            ) ?
                '' :
                $hideHTMLClass
        ),
        'P_HideNoSlotsInfo' => (
            (
                $hasAvailableShips &&
                $allFleetsInFlightCount >= $userMaxFleetSlotsCount
            ) ?
                '' :
                $hideHTMLClass
        ),
        'P_HideSendShips' => (
            (
                $hasAvailableShips &&
                $allFleetsInFlightCount < $userMaxFleetSlotsCount
            ) ?
                '' :
                $hideHTMLClass
        ),
        'P_HideNoShipsInfo' => (
            !$hasAvailableShips ?
                '' :
                $hideHTMLClass
        ),

        'P_SFBInfobox' => $smartFleetBlockadeComponent['componentHTML'],
        'ComponentHTML_RetreatInfoBox' => (
            $retreatInfoBoxComponent ?
                $retreatInfoBoxComponent['componentHTML'] :
                ''
        ),

        'InsertACSUsersMax' => MAX_ACS_JOINED_PLAYERS,
    ];

    $componentHTML = parsetemplate($tplBodyCache['body'], array_merge($_Lang, $componentTplData, $formInputsTplData));

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
