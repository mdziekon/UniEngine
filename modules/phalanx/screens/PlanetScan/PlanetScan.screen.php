<?php

namespace UniEngine\Engine\Modules\Phalanx\Screens\PlanetScan;

use UniEngine\Engine\Modules\Phalanx;
use UniEngine\Engine\Modules\Flights;

/**
 * @param Object $props
 * @param arrayRef $props['input']
 * @param arrayRef $props['currentUser']
 * @param arrayRef $props['currentPlanet']
 * @param number $props['currentTimestamp']
 *
 * @return Object $result
 * @return String $result['componentHTML']
 */
function render($props) {
    global $_Lang;

    $input = &$props['input'];
    $currentPlanet = &$props['currentPlanet'];
    $currentUser = &$props['currentUser'];
    $currentTimestamp = $props['currentTimestamp'];

    includeLang('overview');
    includeLang('phalanx');

    $scanCost = PHALANX_DEUTERIUMCOST;

    if (Phalanx\Utils\Helpers\canUserBypassChecks([ 'user' => &$currentUser ])) {
        $scanCost = 0;
    }

    $targetCoords = [
        'galaxy' => (isset($input['galaxy']) ? intval($input['galaxy']) : 0),
        'system' => (isset($input['system']) ? intval($input['system']) : 0),
        'planet' => (isset($input['planet']) ? intval($input['planet']) : 0),
    ];

    $tryScanPlanet = Phalanx\Utils\Helpers\tryScanPlanet([
        'targetCoords' => $targetCoords,
        'phalanxMoon' => &$currentPlanet,
        'currentUser' => &$currentUser,
        'scanCost' => $scanCost,
    ]);

    if (!$tryScanPlanet['isSuccess']) {
        $errorMessage = Phalanx\Utils\Errors\mapTryScanPlanetErrorToReadableMessage($tryScanPlanet['error']);

        message($errorMessage, $_Lang['Page_Title']);
    }

    if ($scanCost > 0) {
        Phalanx\Utils\Effects\updateMoonFuelOnUsage([
            'scanCost' => $scanCost,
            'phalanxMoon' => &$currentPlanet,
            'currentTimestamp' => $currentTimestamp,
        ]);
    }

    $targetDetails = $tryScanPlanet['payload']['targetDetails'];
    $currentFlights = Flights\Fetchers\fetchCurrentFlights([
        'targetId' => $targetDetails['id'],
    ]);

    $planetScanResult = Phalanx\Screens\PlanetScan\Components\PlanetScanResult\render([
        'targetDetails' => $targetDetails,
        'phalanxMoon' => &$currentPlanet,
        'viewingUserId' => $currentUser['id'],
        'currentTimestamp' => $currentTimestamp,
        'scanCost' => $scanCost,
        'foundFlights' => $currentFlights,
    ]);

    display($planetScanResult['componentHTML'], $_Lang['Page_Title'], false);
}

?>
