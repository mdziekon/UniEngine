<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

use UniEngine\Engine\Modules\Flights\Enums\FleetMission;
use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param array $props['targetCoords']
 * @param array $props['fleetEntry']
 * @param array $props['fleetOwnerUser']
 * @param array $props['isExtendedTargetOwnerDetailsEnabled']
 */
function getTargetInfo($props) {
    $targetCoords = $props['targetCoords'];
    $fleetEntry = $props['fleetEntry'];
    $fleetOwnerUser = $props['fleetOwnerUser'];
    $isExtendedTargetOwnerDetailsEnabled = $props['isExtendedTargetOwnerDetailsEnabled'];

    $result = [
        'galaxyEntry' => null,
        'targetOwnerDetails' => null,
        'targetPlanetDetails' => null,
        'galaxyId' => null,
        'isPlanetOccupied' => false,
        'isPlanetAbandoned' => false,
        'isPlanetOwnedByFleetOwner' => false,
        'isPlanetOwnerNonAggressiveAllianceMember' => false,
        'isPlanetOwnerFriendlyMerchant' => false,
        'isPlanetOwnerFriendly' => false,
        'isPlanetOwnerAlly' => false,
        'isPlanetOwnerBuddy' => false,
    ];

    if ($fleetEntry['Mission'] == FleetMission::Harvest) {
        $galaxyRow = FlightControl\Utils\Fetchers\fetchTargetGalaxyDetails([ 'targetCoords' => $targetCoords ]);

        $result['galaxyEntry'] = $galaxyRow;
        $result['galaxyId'] = $galaxyRow['galaxy_id'];

        return $result;
    }

    $planetOwnerDetails = FlightControl\Utils\Fetchers\fetchPlanetOwnerDetails([
        'targetCoordinates' => $targetCoords,
        'user' => &$fleetOwnerUser,
        'isExtendedUserDetailsEnabled' => $isExtendedTargetOwnerDetailsEnabled,
    ]);

    if (!$planetOwnerDetails) {
        $result['targetOwnerDetails'] = [];

        return $result;
    }

    $galaxyRow = FlightControl\Utils\Fetchers\fetchTargetGalaxyDetails([ 'targetCoords' => $targetCoords ]);

    $targetOwnerDetails = $planetOwnerDetails['targetOwner'];

    $result['isPlanetOccupied'] = true;
    $result['galaxyId'] = $galaxyRow['galaxy_id'];
    $result['targetOwnerDetails'] = $targetOwnerDetails;
    $result['targetPlanetDetails'] = $planetOwnerDetails['targetPlanet'];

    if (!($targetOwnerDetails['id'] > 0)) {
        $result['isPlanetAbandoned'] = true;

        return $result;
    }

    if ($targetOwnerDetails['id'] == $fleetOwnerUser['id']) {
        $result['isPlanetOwnedByFleetOwner'] = true;

        return $result;
    }

    $result['isPlanetOwnerNonAggressiveAllianceMember'] = (
        (
            isset($targetOwnerDetails['AllyPact1']) &&
            $targetOwnerDetails['AllyPact1'] >= ALLYPACT_NONAGGRESSION
        ) ||
        (
            isset($targetOwnerDetails['AllyPact2']) &&
            $targetOwnerDetails['AllyPact2'] >= ALLYPACT_NONAGGRESSION
        )
    );
    $result['isPlanetOwnerFriendlyMerchant'] = (
        (
            isset($targetOwnerDetails['AllyPact1']) &&
            $targetOwnerDetails['AllyPact1'] >= ALLYPACT_MERCANTILE
        ) ||
        (
            isset($targetOwnerDetails['AllyPact2']) &&
            $targetOwnerDetails['AllyPact2'] >= ALLYPACT_MERCANTILE
        )
    );

    $isPlanetOwnerBuddy = (
        $targetOwnerDetails['active1'] == 1 ||
        $targetOwnerDetails['active2'] == 1
    );
    $isPlanetOwnerAlly = (
        $fleetOwnerUser['ally_id'] > 0 &&
        $targetOwnerDetails['ally_id'] == $fleetOwnerUser['ally_id']
    );

    $result['isPlanetOwnerBuddy'] = $isPlanetOwnerBuddy;
    $result['isPlanetOwnerAlly'] = $isPlanetOwnerAlly;
    $result['isPlanetOwnerFriendly'] = (
        $isPlanetOwnerBuddy ||
        $isPlanetOwnerAlly ||
        (
            isset($targetOwnerDetails['AllyPact1']) &&
            $targetOwnerDetails['AllyPact1'] >= ALLYPACT_DEFENSIVE
        ) ||
        (
            isset($targetOwnerDetails['AllyPact2']) &&
            $targetOwnerDetails['AllyPact2'] >= ALLYPACT_DEFENSIVE
        )
    );

    return $result;
}

?>
