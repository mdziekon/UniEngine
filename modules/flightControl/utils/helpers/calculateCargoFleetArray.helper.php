<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

use UniEngine\Engine\Includes\Helpers\World\Elements;

/**
 * @param object $props
 * @param object $props['planet']
 * @param object $props['user']
 */
function calculateCargoFleetArray($props) {
    global $_Vars_ElementCategories;

    $planet = $props['planet'];
    $user = $props['user'];

    $cargoFleetArray = [];

    $resourcesToLoad = (
        floor($planet['metal']) +
        floor($planet['crystal']) +
        floor($planet['deuterium'])
    );

    $transportShipIds = $_Vars_ElementCategories['units']['transport'];

    usort($transportShipIds, function ($leftShipId, $rightShipId) {
        return (getShipsStorageCapacity($leftShipId) < getShipsStorageCapacity($rightShipId));
    });

    foreach ($transportShipIds as $shipId) {
        $shipCapacity = getShipsStorageCapacity($shipId);

        $shipsNeeded = ceil($resourcesToLoad / $shipCapacity);
        $shipsAvailable = Elements\getElementCurrentCount($shipId, $planet, $user);
        $shipsToUse = keepInRange($shipsNeeded, 0, $shipsAvailable);

        $cargoFleetArray[$shipId] = $shipsToUse;

        $resourcesToLoad -= ($shipsToUse * $shipCapacity);

        if ($resourcesToLoad <= 0) {
            break;
        }
    }

    return $cargoFleetArray;
}

?>
