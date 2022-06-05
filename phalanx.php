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

$PageTitle = $_Lang['Page_Title'];

$ThisMoon = &$_Planet;
$Now = time();
$ScanCost = PHALANX_DEUTERIUMCOST;

if (Phalanx\Utils\Helpers\canUserBypassChecks([ 'user' => &$_User ])) {
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

$Result_GetFleets = Flights\Fetchers\fetchCurrentFlights([
    'targetId' => $targetDetails['id'],
]);

$viewScreen = Phalanx\Screens\PlanetScan\render([
    'targetDetails' => $targetDetails,
    'phalanxMoon' => &$ThisMoon,
    'viewingUserId' => $_User['id'],
    'currentTimestamp' => $Now,
    'scanCost' => $ScanCost,
    'foundFlights' => $Result_GetFleets,
]);

display($viewScreen['componentHTML'], $PageTitle, false);

?>
