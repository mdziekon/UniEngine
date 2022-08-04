<?php

namespace UniEngine\Engine\Modules\Info\Components\PhalanxRangeTable;

use UniEngine\Engine\Includes\Helpers\World;
use UniEngine\Engine\Modules\Info;

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

    $tableRange = Info\Utils\getLevelRange([
        'currentLevel' => $currentLevel,
        'rangeLengthLeft' => 3,
        'rangeLengthRight' => 6,
    ]);

    $tableRows = [];

    for (
        $iterLevel = $tableRange['startLevel'];
        $iterLevel <= $tableRange['endLevel'];
        $iterLevel++
    ) {
        $rowData = [];

        if ($iterLevel == $currentLevel) {
            $rowData['build_lvl'] = "<span class=\"red\">{$iterLevel}</span>";
            $rowData['IsCurrent'] = ' class="thisLevel"';
        } else {
            $rowData['build_lvl'] = $iterLevel;
        }

        $iterLevelRange = prettyNumber(GetPhalanxRange($iterLevel));

        $rowData['build_range'] = $iterLevelRange;

        $tableRows[] = parsetemplate($rowTpl, $rowData);
    }

    return [
        'componentHTML' => implode('', $tableRows),
    ];
}

?>
