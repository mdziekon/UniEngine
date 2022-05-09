<?php

define('INSIDE', true);

$_EnginePath = './';

include("{$_EnginePath}/common.php");
include("{$_EnginePath}/modules/flightControl/_includes.php");
include("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Modules\Flights;
use UniEngine\Engine\Modules\FlightControl;

loggedCheck();

includeLang('fleet');

if (!$_Planet) {
    message($_Lang['fl_noplanetrow'], $_Lang['fl_error']);
}

$Now = time();

// TODO: refactor and add validation (?)
if (
    (
        isset($_GET['joinacs'])
    ) ||
    (
        isset($_POST['getacsdata']) &&
        $_POST['getacsdata'] > 0
    )
)
{
    $_Lang['SetJoiningACSID'] = (
        isset($_GET['joinacs']) ?
            $_GET['joinacs'] :
            $_POST['getacsdata']
    );
}

$unionManagementComponent = null;

if (
    isset($_POST['acsmanage']) &&
    $_POST['acsmanage'] == 'open'
) {
    $unionManagementComponent = FlightControl\Components\UnionManagement\render([
        'unionOwner' => $_User,
        'currentTimestamp' => $Now,
        'input' => $_POST,
    ]);
}

/**
 * Flights list is purposefully rendered after UnionManagement
 * to allow any new Union entries to be inserted before rendering the list
 */
$flightsList = FlightControl\Components\FlightsList\render([
    'userId' => $_User['id'],
    'currentTimestamp' => $Now,
])['componentHTML'];

$isQuickTransportOptionUsed = (
    isset($_GET['quickres']) &&
    $_GET['quickres'] == 1 &&
    isPro()
);

$preselectedCargoShips = [];

if ($isQuickTransportOptionUsed) {
    $_Lang['P_SetQuickRes'] = '1';

    $preselectedCargoShips = FlightControl\Utils\Helpers\calculateCargoFleetArray([
        'planet' => $_Planet,
        'user' => $_User,
    ]);
} else {
    $_Lang['P_SetQuickRes'] = '0';
}

$gobackFleet = [];

if (
    isset($_POST['gobackUsed']) &&
    !empty($_POST['FleetArray'])
) {
    $gobackFleet = String2Array($_POST['FleetArray']);
    $gobackFleet = object_map($gobackFleet, function ($shipCount, $shipId) {
        if (!Elements\isShip($shipId)) {
            return [ null, $shipId ];
        }

        return [ $shipCount, $shipId ];
    });
    $gobackFleet = Collections\compact($gobackFleet);
}

if(isset($_POST['gobackUsed']))
{
    $GoBackVars = array
    (
        'speed' => $_POST['speed'],
    );
    if(!empty($_POST['gobackVars']))
    {
        $_Lang['P_GoBackVars'] = json_decode(base64_decode($_POST['gobackVars']), true);
        if((array)$_Lang['P_GoBackVars'] === $_Lang['P_GoBackVars'])
        {
            $GoBackVars = array_merge($_Lang['P_GoBackVars'], $GoBackVars);
        }
    }

    $_Lang['SetJoiningACSID'] = (isset($_POST['getacsdata']) ? $_POST['getacsdata'] : null);
    $_Lang['P_Galaxy'] = (isset($_POST['galaxy']) ? $_POST['galaxy'] : null);
    $_Lang['P_System'] = (isset($_POST['system']) ? $_POST['system'] : null);
    $_Lang['P_Planet'] = (isset($_POST['planet']) ? $_POST['planet'] : null);
    $_Lang['P_PlType'] = (isset($_POST['planettype']) ? $_POST['planettype'] : null);
    $_Lang['P_Mission'] = (isset($_POST['target_mission']) ? $_POST['target_mission'] : null);
    $_Lang['P_SetQuickRes'] = (isset($_POST['quickres']) ? $_POST['quickres'] : null);
    $_Lang['P_GoBackVars'] = base64_encode(json_encode($GoBackVars));
} else {
    $_Lang['P_Galaxy'] = (isset($_GET['galaxy']) ? intval($_GET['galaxy']) : null);
    $_Lang['P_System'] = (isset($_GET['system']) ? intval($_GET['system']) : null);
    $_Lang['P_Planet'] = (isset($_GET['planet']) ? intval($_GET['planet']) : null);
    $_Lang['P_PlType'] = (isset($_GET['planettype']) ? intval($_GET['planettype']) : null);
    $_Lang['P_Mission'] = (isset($_GET['target_mission']) ? intval($_GET['target_mission']) : null);

    if (
        $isQuickTransportOptionUsed &&
        (
            !isset($_GET['target_mission']) ||
            $_GET['target_mission'] != Flights\Enums\FleetMission::Transport
        )
    ) {
        if ($_User['settings_mainPlanetID'] != $_Planet['id']) {
            $quickTransportPlanetPosition = doquery("SELECT `galaxy`, `system`, `planet` FROM {{table}} WHERE `id` = {$_User['settings_mainPlanetID']};", 'planets', true);
        } else {
            $quickTransportPlanetPosition = [
                'galaxy' => '',
                'system' => '',
                'planet' => '',
            ];
        }

        $_Lang['P_Galaxy'] = $quickTransportPlanetPosition['galaxy'];
        $_Lang['P_System'] = $quickTransportPlanetPosition['system'];
        $_Lang['P_Planet'] = $quickTransportPlanetPosition['planet'];
        $_Lang['P_PlType'] = 1;
        $_Lang['P_Mission'] = Flights\Enums\FleetMission::Transport;
    }
}

$resourcesToLoad = Resources\sumAllPlanetTransportableResources($_Planet);

$smartFleetBlockadeComponent = FlightControl\Components\SmartFleetBlockadeInfoBox\render();
$retreatInfoBoxComponent = null;
if (
    isset($_GET['ret']) &&
    isset($_GET['m'])
) {
    $retreatInfoBoxComponent = FlightControl\Components\RetreatInfoBox\render([
        'eventCode' => $_GET['m'],
    ]);
}
$availableShipsListComponent = FlightControl\Components\AvailableShipsList\render([
    'planet' => $_Planet,
    'user' => $_User,
    'preselectedShips' => (
        !empty($gobackFleet) ?
            $gobackFleet :
            $preselectedCargoShips
    ),
]);
$shipsJSData = FlightControl\Utils\Factories\createPlanetShipsJSObject([
    'planet' => $_Planet,
    'user' => $_User,
]);

$userMaxFleetSlotsCount = FlightControl\Utils\Helpers\getUserFleetSlotsCount([
    'user' => $_User,
    'timestamp' => $Now,
]);
$fleetsInFlightCounters = FlightControl\Utils\Helpers\getFleetsInFlightCounters([
    'userId' => $_User['id'],
]);
$allFleetsInFlightCount = $fleetsInFlightCounters['allFleetsInFlight'];
$expeditionsInFlightCount = $fleetsInFlightCounters['expeditionsInFlight'];

$hasAvailableShips = !empty($availableShipsListComponent['componentHTML']);
$hideHTMLClass = ' class="hide"';

$tplProps = [
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
        ($_User['settings_useprettyinputbox'] == 1) ?
            'true' :
            'false'
    ),
    'P_Expeditions_isHidden_style' => (
        isFeatureEnabled(FeatureType::Expeditions) ?
            '' :
            'display: none;'
    ),

    'P_MaxFleetSlots' => $userMaxFleetSlotsCount,
    'P_MaxExpedSlots' => FlightControl\Utils\Helpers\getUserExpeditionSlotsCount([
        'user' => $_User,
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

$pageBodyTpl = gettemplate('fleet_body');
$pageHTML = parsetemplate($pageBodyTpl, array_merge($_Lang, $tplProps));

display($pageHTML, $_Lang['fl_title']);

?>
