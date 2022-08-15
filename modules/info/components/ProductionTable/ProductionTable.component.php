<?php

namespace UniEngine\Engine\Modules\Info\Components\ProductionTable;

use UniEngine\Engine\Includes\Helpers\World;
use UniEngine\Engine\Modules\Info;

/**
 * @param array $props
 * @param number $props['elementId']
 * @param arrayRef $props['user']
 * @param arrayRef $props['planet']
 * @param number $props['currentTimestamp']
 */
function render($props) {
    global $_Lang, $_Vars_CombatData, $_Vars_CombatUpgrades;

    $elementId = $props['elementId'];
    $user = &$props['user'];
    $planet = &$props['planet'];
    $currentTimestamp = $props['currentTimestamp'];

    $IMPULSE_DRIVE_ELEMENTID = 117;
    $PHALANX_ELEMENTID = 42;

    if (World\Elements\isProductionRelated($elementId)) {
        return Info\Components\ResourceProductionTable\render([
            'elementId' => $elementId,
            'planet' => &$planet,
            'user' => &$user,
            'currentTimestamp' => $currentTimestamp,
        ]);
    }
    if (World\Elements\isStorageStructure($elementId)) {
        return Info\Components\ResourceStorageTable\render([
            'elementId' => $elementId,
            'planet' => &$planet,
        ]);
    }
    if ($elementId == $IMPULSE_DRIVE_ELEMENTID) {
        return Info\Components\MissileRangeTable\render([
            'elementId' => $elementId,
            'planet' => &$planet,
            'user' => &$user,
        ]);
    }
    if ($elementId == $PHALANX_ELEMENTID) {
        return Info\Components\PhalanxRangeTable\render([
            'elementId' => $elementId,
            'planet' => &$planet,
            'user' => &$user,
        ]);
    }

    return [
        'componentHTML' => '',
    ];
}

?>
