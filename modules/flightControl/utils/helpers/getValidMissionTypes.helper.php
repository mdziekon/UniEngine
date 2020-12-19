<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

/**
 * @param array $props
 * @param array $props['targetCoordinates']
 * @param array $props['fleetShips']
 * @param number $props['fleetShipsCount']
 * @param boolean $props['isPlanetOccupied']
 * @param boolean $props['isPlanetOwnedByUser']
 * @param boolean $props['isPlanetOwnedByUsersFriend']
 * @param boolean $props['isUnionMissionAllowed']
 */
function getValidMissionTypes ($props) {
    $targetCoordinates = $props['targetCoordinates'];
    $fleetShips = $props['fleetShips'];
    $fleetShipsCount = $props['fleetShipsCount'];
    $isPlanetOccupied = $props['isPlanetOccupied'];
    $isPlanetOwnedByUser = $props['isPlanetOwnedByUser'];
    $isPlanetOwnedByUsersFriend = $props['isPlanetOwnedByUsersFriend'];
    $isUnionMissionAllowed = $props['isUnionMissionAllowed'];

    $validMissionTypes = [];

    if ($targetCoordinates['type'] == 2) {
        if ($fleetShips[209] > 0) {
            $validMissionTypes[] = 8;
        }

        // No other valid missions possible for debris field
        return $validMissionTypes;
    }

    if ($isPlanetOccupied) {
        // Transport mission should not be available
        // when the only ship type available is espionage probe
        if (
            !isset($fleetShips[210]) ||
            $fleetShips[210] < $fleetShipsCount
        ) {
            $validMissionTypes[] = 3;
        }

        if ($isPlanetOwnedByUser) {
            $validMissionTypes[] = 4;
        }

        if (!$isPlanetOwnedByUser) {
            $validMissionTypes[] = 1;

            if ($isUnionMissionAllowed) {
                $validMissionTypes[] = 2;
            }

            if (
                $targetCoordinates['type'] == 3 &&
                isset($fleetShips[214]) &&
                $fleetShips[214] > 0
            ) {
                $validMissionTypes[] = 9;
            }

            if ($isPlanetOwnedByUsersFriend) {
                $validMissionTypes[] = 5;
            }

            if (
                isset($fleetShips[210]) &&
                $fleetShips[210] == $fleetShipsCount
            ) {
                $validMissionTypes[] = 6;
            }
        }
    }

    if (!$isPlanetOccupied) {
        $expeditionPlanetCoordinate = (MAX_PLANET_IN_SYSTEM + 1);

        if (
            isFeatureEnabled(\FeatureType::Expeditions) &&
            $targetCoordinates['planet'] == $expeditionPlanetCoordinate
        ) {
            $validMissionTypes[] = 15;
        }

        if ($targetCoordinates['planet'] <= MAX_PLANET_IN_SYSTEM) {
            if (
                $targetCoordinates['type'] == 1 &&
                isset($fleetShips[208]) &&
                $fleetShips[208] > 0
            ) {
                $validMissionTypes[] = 7;
            }
        }
    }

    return $validMissionTypes;
}

?>
