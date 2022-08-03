<?php

namespace UniEngine\Engine\Modules\Info\Components\RapidFireFromList;

use UniEngine\Engine\Modules\Info;

/**
 * @param array $props
 * @param string $props['elementId']
 */
function render($props) {
    global $_Lang, $_Vars_CombatData;

    $elementId = $props['elementId'];

    $rapidFireFromUnits = array_filter($_Vars_CombatData, function ($unitData) use ($elementId) {
        return (
            isset($unitData['sd'][$elementId]) &&
            $unitData['sd'][$elementId] > 1
        );
    });
    $rapidFireFromUnitsRows = array_map_withkeys(
        $rapidFireFromUnits,
        function ($unitData, $unitElementId) use ($elementId, &$_Lang) {
            return Info\Components\RapidFireCommonRow\render([
                'elementId' => $unitElementId,
                'title' => $_Lang['nfo_rf_from'],
                'color' => 'red',
                'value' => $unitData['sd'][$elementId],
            ])['componentHTML'];
        }
    );

    return [
        'componentHTML' => implode('', $rapidFireFromUnitsRows),
    ];
}

?>
