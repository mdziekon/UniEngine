<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Factories;

use UniEngine\Engine\Includes\Helpers\World\Elements;

/**
 * @param object $props
 * @param object $props['planet']
 * @param object $props['user']
 */
function createPlanetShipsJSObject($props) {
    global $_Vars_ElementCategories;

    $planet = $props['planet'];
    $user = $props['user'];

    $shipsJSData = [];

    foreach ($_Vars_ElementCategories['fleet'] as $shipId) {
        $elementCurrentCount = Elements\getElementCurrentCount($shipId, $planet, $user);

        if (
            $elementCurrentCount <= 0 ||
            !hasAnyEngine($shipId)
        ) {
            continue;
        }

        $shipsJSData[$shipId] = [
            'storage' => getShipsPillageStorageCapacity($shipId),
            'count' => $elementCurrentCount,
        ];
    }

    return $shipsJSData;
}

?>
