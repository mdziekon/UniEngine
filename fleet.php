<?php

define('INSIDE', true);

$_EnginePath = './';

include("{$_EnginePath}/common.php");
include("{$_EnginePath}/modules/flightControl/_includes.php");
include("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Modules\FlightControl;

loggedCheck();

if (!$_Planet) {
    message($_Lang['fl_noplanetrow'], $_Lang['fl_error']);
}

$Now = time();

includeLang('fleet');

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

if (
    isset($_POST['acsmanage']) &&
    $_POST['acsmanage'] == 'open'
) {
    $unionManagement = FlightControl\Components\UnionManagement\render([
        'unionOwner' => $_User,
        'currentTimestamp' => $Now,
        'input' => $_POST,
    ]);

    $_Lang['Insert_ACSForm'] = $unionManagement['componentHTML'];
}

$flightsList = FlightControl\Components\FlightsList\render([
    'userId' => $_User['id'],
    'currentTimestamp' => $Now,
])['componentHTML'];

$_Lang['FlyingFleetsRows'] = (
    empty($flightsList['elementsList']) ?
        '<tr><th colspan="8">-</th></tr>' :
        $flightsList['elementsList']
);
$_Lang['ChronoAppletsScripts'] = $flightsList['chronoApplets'];

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
}
else
{
    $_Lang['P_Galaxy'] = (isset($_GET['galaxy']) ? intval($_GET['galaxy']) : null);
    $_Lang['P_System'] = (isset($_GET['system']) ? intval($_GET['system']) : null);
    $_Lang['P_Planet'] = (isset($_GET['planet']) ? intval($_GET['planet']) : null);
    $_Lang['P_PlType'] = (isset($_GET['planettype']) ? intval($_GET['planettype']) : null);
    $_Lang['P_Mission'] = (isset($_GET['target_mission']) ? intval($_GET['target_mission']) : null);
    if ($isQuickTransportOptionUsed) {
        if(!isset($_GET['target_mission']) || $_GET['target_mission'] != 3)
        {
            if($_User['settings_mainPlanetID'] != $_Planet['id'])
            {
                $GetQuickResPlanet = doquery("SELECT `galaxy`, `system`, `planet` FROM {{table}} WHERE `id` = {$_User['settings_mainPlanetID']};", 'planets', true);
            }
            $_Lang['P_Galaxy'] = $GetQuickResPlanet['galaxy'];
            $_Lang['P_System'] = $GetQuickResPlanet['system'];
            $_Lang['P_Planet'] = $GetQuickResPlanet['planet'];
            $_Lang['P_PlType'] = 1;
            $_Lang['P_Mission'] = 3;
        }
    }
}

$resourcesToLoad = Resources\sumAllPlanetTransportableResources($_Planet);

$smartFleetBlockadeComponent = FlightControl\Components\SmartFleetBlockadeInfoBox\render();
$retreatInfoBoxComponent = FlightControl\Components\RetreatInfoBox\render([
    'isVisible' => isset($_GET['ret']),
    'eventCode' => $_GET['m'],
]);
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
    'ComponentHTML_RetreatInfoBox' => $retreatInfoBoxComponent['componentHTML'],

    'InsertACSUsersMax' => MAX_ACS_JOINED_PLAYERS,
];

$pageBodyTpl = gettemplate('fleet_body');
$pageHTML = parsetemplate($pageBodyTpl, array_merge($_Lang, $tplProps));

display($pageHTML, $_Lang['fl_title']);

?>
