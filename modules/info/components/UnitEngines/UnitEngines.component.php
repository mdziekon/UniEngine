<?php

namespace UniEngine\Engine\Modules\Info\Components\UnitEngines;

use UniEngine\Engine\Includes\Helpers\Users;

/**
 * @param array $props
 * @param number $props['elementId']
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_Lang;

    $elementId = $props['elementId'];
    $user = &$props['user'];

    $thisShipsUsedEngine = getShipsUsedEngineData($elementId, $user);
    $thisShipsCurrentSpeed = getShipsCurrentSpeed($elementId, $user);
    $thisShipsCurrentSpeedModifier = (
        $thisShipsUsedEngine['engineIdx'] === -1 ?
            0 :
            Users\getUsersEngineSpeedTechModifier($thisShipsUsedEngine['data']['tech'], $user)
    );
    $thisShipsEngines = getShipsEngines($elementId);

    if (empty($thisShipsEngines)) {
        $thisShipsEngines = [
            [
                'speed' => 0,
                'consumption' => 0
            ],
        ];
    }

    $hasUpgradableEngine = (count($thisShipsEngines) > 1);

    // Sort engines in reverse order, so the first one is the "slowest"
    // according to the ordering assumption.
    krsort($thisShipsEngines);

    $speedBases = [];
    $fuelConsumptionBases = [];

    foreach ($thisShipsEngines as $engineIdx => $engineData) {
        $engineDisplayProps = [
            'speed' => prettyNumber($engineData['speed']),
            'consumption' => prettyNumber($engineData['consumption']),
        ];

        if (
            $hasUpgradableEngine &&
            $engineIdx === $thisShipsUsedEngine['engineIdx']
        ) {
            $engineDisplayProps['speed'] = ('<b class="skyblue">' . $engineDisplayProps['speed'] . '</b>');
            $engineDisplayProps['consumption'] = ('<b class="skyblue">' . $engineDisplayProps['consumption'] . '</b>');
        }

        $speedBases[] = $engineDisplayProps['speed'];
        $fuelConsumptionBases[] = $engineDisplayProps['consumption'];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        [
            'lang_BaseSpeed' => $_Lang['nfo_base_speed'],
            'lang_Consumption' => $_Lang['nfo_consumption'],

            'data_SpeedBase' => implode(' / ', $speedBases),
            'data_SpeedModifier' => prettyNumber($thisShipsCurrentSpeedModifier * 100),
            'data_SpeedFinal' => prettyNumber($thisShipsCurrentSpeed),
            'data_FuelBase' => implode(' / ', $fuelConsumptionBases),
        ]
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
