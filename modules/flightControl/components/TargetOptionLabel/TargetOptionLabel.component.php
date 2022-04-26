<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\TargetOptionLabel;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

//  Arguments
//      - $props (Object)
//          - target (Object[])
//              - own_name (String)
//              - name (String)
//              - planet_type (String)
//              - galaxy (String)
//              - system (String)
//              - planet (String)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Lang;

    $target = $props['target'];

    $targetCustomName = (
        !empty($target['own_name']) ?
            "\"{$target['own_name']}\"" :
            null
    );
    $targetOriginalName = $target['name'];
    $targetTypeLabel = [
        '1' => $_Lang['planet_sign'],
        '2' => $_Lang['debris_sign'],
        '3' => $_Lang['moon_sign'],
    ][$target['planet_type']];
    $targetTypeMarker = "({$targetTypeLabel})";
    $targetPos = "[{$target['galaxy']}:{$target['system']}:{$target['planet']}]";

    $elementLabelParts = Collections\compact([
        $targetCustomName,
        (
            !empty($targetCustomName) && !empty($targetOriginalName) ?
                '-' :
                null
        ),
        $targetOriginalName,
        $targetTypeMarker,
        $targetPos
    ]);

    return [
        'componentHTML' => implode(' ', $elementLabelParts),
    ];
}

?>
