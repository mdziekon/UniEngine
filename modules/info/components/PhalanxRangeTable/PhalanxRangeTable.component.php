<?php

namespace UniEngine\Engine\Modules\Info\Components\PhalanxRangeTable;

use UniEngine\Engine\Includes\Helpers\World;

/**
 * @param array $props
 * @param string $props['elementId']
 * @param arrayRef $props['planet']
 * @param arrayRef $props['user']
 */
function render($props) {
    $elementId = $props['elementId'];
    $planet = &$props['planet'];
    $user = &$props['user'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $rowTpl = $localTemplateLoader('rangeRow');

    $currentLevel = World\Elements\getElementCurrentLevel($elementId, $planet, $user);

    $tableRangeStartLevel = $currentLevel - 3;
    $tableRangeEndLevel = $currentLevel + 6;

    if ($tableRangeStartLevel < 0) {
        $offset = $tableRangeStartLevel * (-1);

        $tableRangeStartLevel += $offset;
        $tableRangeEndLevel += $offset;
    }

    $tableRows = [];

    for (
        $iterLevel = $tableRangeStartLevel;
        $iterLevel <= $tableRangeEndLevel;
        $iterLevel++
    ) {
        $rowData = [];

        if ($iterLevel == $currentLevel) {
            $rowData['build_lvl'] = "<span class=\"red\">{$iterLevel}</span>";
            $rowData['IsCurrent'] = ' class="thisLevel"';
        } else {
            $rowData['build_lvl'] = $iterLevel;
        }

        $iterLevelRange = prettyNumber(($iterLevel * $iterLevel) - 1);

        $rowData['build_range'] = $iterLevelRange;

        $tableRows[] = parsetemplate($rowTpl, $rowData);
    }

    return [
        'componentHTML' => implode('', $tableRows),
    ];
}

?>
