<?php

namespace UniEngine\Engine\Modules\Info\Components\RapidFireAgainstList;

use UniEngine\Engine\Modules\Info;

/**
 * @param array $props
 * @param string $props['elementId']
 */
function render($props) {
    global $_Lang, $_Vars_CombatData;

    $elementId = $props['elementId'];

    $rapidFireTargets = array_filter($_Vars_CombatData[$elementId]['sd'], function ($rapidFireShots) {
        return $rapidFireShots > 1;
    });
    $rapidFireTargetRows = array_map_withkeys(
        $rapidFireTargets,
        function ($rapidFireShots, $targetElementId) use (&$_Lang) {
            return Info\Components\RapidFireCommonRow\render([
                'elementId' => $targetElementId,
                'title' => $_Lang['nfo_rf_again'],
                'color' => 'lime',
                'value' => $rapidFireShots,
            ])['componentHTML'];
        }
    );

    return [
        'componentHTML' => implode('', $rapidFireTargetRows),
    ];
}

?>
