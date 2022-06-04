<?php

define('INSIDE', true);

$_DontShowMenus = true;
$_DontShowRulesBox = true;
$_DontCheckPolls = true;
$_BlockFleetHandler = true;

$_EnginePath = './';
include($_EnginePath.'common.php');
include($_EnginePath.'modules/flights/_includes.php');
include($_EnginePath.'modules/phalanx/_includes.php');
include($_EnginePath.'includes/functions/GetPhalanxRange.php');

use UniEngine\Engine\Modules\Flights;
use UniEngine\Engine\Modules\Phalanx;

loggedCheck();

includeLang('overview');
includeLang('phalanx');
$PageTPL = gettemplate('phalanx_body');
$PageTitle = $_Lang['Page_Title'];

$ThisMoon = &$_Planet;
$Now = time();
$ScanCost = PHALANX_DEUTERIUMCOST;

if (CheckAuth('supportadmin')) {
    $ScanCost = 0;
}

$targetCoords = [
    'galaxy' => (isset($_GET['galaxy']) ? intval($_GET['galaxy']) : 0),
    'system' => (isset($_GET['system']) ? intval($_GET['system']) : 0),
    'planet' => (isset($_GET['planet']) ? intval($_GET['planet']) : 0),
];

$tryScanPlanet = Phalanx\Utils\Helpers\tryScanPlanet([
    'targetCoords' => $targetCoords,
    'phalanxMoon' => &$ThisMoon,
    'currentUser' => &$_User,
    'scanCost' => $ScanCost,
]);

if (!$tryScanPlanet['isSuccess']) {
    $errorMessage = Phalanx\Utils\Errors\mapTryScanPlanetErrorToReadableMessage($tryScanPlanet['error']);

    message($errorMessage, $PageTitle);
}

$targetDetails = $tryScanPlanet['payload']['targetDetails'];

if ($ScanCost > 0) {
    Phalanx\Utils\Effects\updateMoonFuelOnUsage([
        'scanCost' => $ScanCost,
        'phalanxMoon' => &$ThisMoon,
        'currentTimestamp' => $Now,
    ]);
}

$parse = $_Lang;

if ($targetDetails['id_owner'] > 0) {
    $parse['Insert_OwnerName'] = "({$targetDetails['username']})";
    $parse['Insert_TargetName'] = $targetDetails['name'];
} else {
    $parse['Table_Title2'] = '';
    $parse['Insert_TargetName'] = "<b class=\"red\">{$_Lang['Abandoned_planet']}</b>";
}

$parse['skinpath'] = $_SkinPath;
$parse['Insert_Coord_Galaxy'] = $targetDetails['galaxy'];
$parse['Insert_Coord_System'] = $targetDetails['system'];
$parse['Insert_Coord_Planet'] = $targetDetails['planet'];
$parse['Insert_My_Galaxy'] = $ThisMoon['galaxy'];
$parse['Insert_My_System'] = $ThisMoon['system'];
$parse['Insert_My_Planet'] = $ThisMoon['planet'];
$parse['Insert_MyMoonName'] = $ThisMoon['name'];
$parse['Insert_DeuteriumAmount'] = prettyNumber($ThisMoon['deuterium']);
$parse['Insert_DeuteriumColor'] = (
    ($ThisMoon['deuterium'] >= $ScanCost) ?
        'lime' :
        'red'
);

$Result_GetFleets = Flights\Fetchers\fetchCurrentFlights([
    'targetId' => $targetDetails['id'],
]);

$parse['phl_fleets_table'] = Flights\Components\FlightsList\render([
    'viewMode' => Flights\Components\FlightsList\Utils\ViewMode::Phalanx,
    'flights' => $Result_GetFleets,
    'viewingUserId' => $_User['id'],
    'targetOwnerId' => $targetDetails['id_owner'],
    'currentTimestamp' => $Now,
])['componentHTML'];

if (empty($parse['phl_fleets_table'])) {
    $parse['phl_fleets_table'] = $_Lang['PhalanxInfo_NoMovements'];
}

$page = parsetemplate($PageTPL, $parse);

display($page, $PageTitle, false);

?>
