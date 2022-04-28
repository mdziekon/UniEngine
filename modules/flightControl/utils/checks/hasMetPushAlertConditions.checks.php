<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Checks;

use UniEngine\Engine\Modules\Flights\Enums\FleetMission;
use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param object $props['fleetData']
 * @param object $props['fleetOwner']
 * @param object $props['targetOwner']
 * @param object $props['targetInfo']
 * @param object $props['statsData']
 */
function hasMetPushAlertConditions($props) {
    $fleetData = $props['fleetData'];
    $fleetOwner = $props['fleetOwner'];
    $targetOwner = $props['targetOwner'];
    $targetInfo = $props['targetInfo'];
    $statsData = $props['statsData'];

    if (
        !$targetInfo['isPlanetOccupied'] ||
        $targetInfo['isPlanetAbandoned'] ||
        $targetInfo['isPlanetOwnedByFleetOwner'] ||
        $fleetData['Mission'] != FleetMission::Transport ||
        $statsData['mine'] >= $statsData['his'] ||
        empty($fleetData['resources'])
    ) {
        return false;
    }

    $hasAnyResources = array_any($fleetData['resources'], function ($resourceValue) {
        return $resourceValue > 0;
    });

    if (!$hasAnyResources) {
        return false;
    }

    $alertFiltersSearchParams = FlightControl\Utils\Factories\createAlertFiltersSearchParams([
        'fleetOwner' => &$fleetOwner,
        'targetOwner' => [
            'id' => $targetOwner['id'],
        ],
        'ipsIntersectionsCheckResult' => null,
    ]);
    $checkFiltersResult = AlertUtils_CheckFilters(
        $alertFiltersSearchParams,
        [
            'DontLoad' => true,
            'DontLoad_OnlyIfCacheEmpty' => true,
        ]
    );

    return $checkFiltersResult['SendAlert'];
}

?>
