<?php

namespace UniEngine\Engine\Modules\Phalanx\Screens\PlanetScan;

use UniEngine\Engine\Modules\Phalanx;

/**
 * @param Object $props
 * @param array $props['targetDetails']
 * @param arrayRef $props['phalanxMoon']
 * @param number $props['viewingUserId']
 * @param number $props['currentTimestamp']
 * @param number $props['scanCost']
 * @param array $props['foundFlights']
 *
 * @return Object $result
 * @return String $result['componentHTML']
 */
function render($props) {
    $planetScanResult = Phalanx\Screens\PlanetScan\Components\PlanetScanResult\render($props);

    return [
        'componentHTML' => $planetScanResult['componentHTML'],
    ];
}

?>
