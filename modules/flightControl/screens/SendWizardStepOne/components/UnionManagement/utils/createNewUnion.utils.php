<?php

namespace UniEngine\Engine\Modules\FlightControl\Screens\SendWizardStepOne\Components\UnionManagement\Utils;

use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param array $props['mainFleet']
 * @param array $props['unionOwner']
 * @param number $props['currentTimestamp']
 */
function createNewUnion($props) {
    $mainFleet = $props['mainFleet'];
    $unionOwner = $props['unionOwner'];
    $currentTimestamp = $props['currentTimestamp'];

    $defaultUnionName = substr(
        $unionOwner['username'] . ' ' . date('d.m.Y H:i', $currentTimestamp),
        0,
        50
    );

    $newUnionEntry = FlightControl\Utils\Updaters\createUnionEntry([
        'unionName' => $defaultUnionName,
        'mainFleetEntry' => $mainFleet,
    ]);

    FlightControl\Utils\Updaters\updateFleetArchiveAcsId([
        'fleetId' => $mainFleet['fleet_id'],
        'newAcsId' => $newUnionEntry['id'],
    ]);

    return [
        'newUnionEntry' => $newUnionEntry,
    ];
}

?>
