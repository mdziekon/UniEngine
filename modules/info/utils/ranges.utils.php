<?php

namespace UniEngine\Engine\Modules\Info\Utils;

/**
 * @param array $props
 * @param number $props['currentLevel']
 * @param number $props['rangeLengthLeft']
 * @param number $props['rangeLengthRight']
 */
function getLevelRange($props) {
    $tableRangeStartLevel = $props['currentLevel'] - $props['rangeLengthLeft'];
    $tableRangeEndLevel = $props['currentLevel'] + $props['rangeLengthRight'];

    if ($tableRangeStartLevel < 0) {
        $offset = $tableRangeStartLevel * (-1);

        $tableRangeStartLevel += $offset;
        $tableRangeEndLevel += $offset;
    }

    return [
        'startLevel' => $tableRangeStartLevel,
        'endLevel' => $tableRangeEndLevel,
    ];
}

?>
