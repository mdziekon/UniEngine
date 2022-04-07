<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

use UniEngine\Engine\Modules\Flights\Enums\FleetMission;
use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param array $props['targetCoords']
 * @param array $props['fleetEntry']
 * @param array $props['fleetOwnerUser']
 */
function getTargetInfo($props) {
    $targetCoords = $props['targetCoords'];
    $fleetEntry = $props['fleetEntry'];
    $fleetOwnerUser = $props['fleetOwnerUser'];

    $result = [
        'galaxyEntry' => null,
        'targetOwnerDetails' => null,
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
        'isExtendedUserDetailsEnabled' => true,
    ]);

    if (!$planetOwnerDetails) {
        $result['targetOwnerDetails'] = [];

        return $result;
    }

    $galaxyRow = FlightControl\Utils\Fetchers\fetchTargetGalaxyDetails([ 'targetCoords' => $targetCoords ]);

    $result['isPlanetOccupied'] = true;
    $result['galaxyId'] = $galaxyRow['galaxy_id'];
    $result['targetOwnerDetails'] = $planetOwnerDetails;

    if (!($planetOwnerDetails['__mig']['targetPlanet']['ownerId'] > 0)) {
        $result['isPlanetAbandoned'] = true;

        return $result;
    }

    if ($planetOwnerDetails['__mig']['targetPlanet']['ownerId'] == $fleetOwnerUser['id']) {
        $result['isPlanetOwnedByFleetOwner'] = true;

        return $result;
    }

    $result['isPlanetOwnerNonAggressiveAllianceMember'] = (
        (
            isset($planetOwnerDetails['AllyPact1']) &&
            $planetOwnerDetails['AllyPact1'] >= ALLYPACT_NONAGGRESSION
        ) ||
        (
            isset($planetOwnerDetails['AllyPact2']) &&
            $planetOwnerDetails['AllyPact2'] >= ALLYPACT_NONAGGRESSION
        )
    );
    $result['isPlanetOwnerFriendlyMerchant'] = (
        (
            isset($planetOwnerDetails['AllyPact1']) &&
            $planetOwnerDetails['AllyPact1'] >= ALLYPACT_MERCANTILE
        ) ||
        (
            isset($planetOwnerDetails['AllyPact2']) &&
            $planetOwnerDetails['AllyPact2'] >= ALLYPACT_MERCANTILE
        )
    );

    $isPlanetOwnerBuddy = (
        $planetOwnerDetails['active1'] == 1 ||
        $planetOwnerDetails['active2'] == 1
    );
    $isPlanetOwnerAlly = (
        $fleetOwnerUser['ally_id'] > 0 &&
        $planetOwnerDetails['ally_id'] == $fleetOwnerUser['ally_id']
    );

    $result['isPlanetOwnerBuddy'] = $isPlanetOwnerBuddy;
    $result['isPlanetOwnerAlly'] = $isPlanetOwnerAlly;
    $result['isPlanetOwnerFriendly'] = (
        $isPlanetOwnerBuddy ||
        $isPlanetOwnerAlly ||
        (
            isset($planetOwnerDetails['AllyPact1']) &&
            $planetOwnerDetails['AllyPact1'] >= ALLYPACT_DEFENSIVE
        ) ||
        (
            isset($planetOwnerDetails['AllyPact2']) &&
            $planetOwnerDetails['AllyPact2'] >= ALLYPACT_DEFENSIVE
        )
    );

    return $result;
}

?>
