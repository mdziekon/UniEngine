<?php

namespace UniEngine\Engine\Modules\Info\Components\ResourceStorageTable;

/**
 * @param array $props
 * @param string $props['elementId']
 * @param arrayRef $props['planet']
 */
function render($props) {
    $elementId = $props['elementId'];
    $planet = &$props['planet'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $rowTpl = $localTemplateLoader('storageRow');

    $elementPlanetKey = _getElementPlanetKey($elementId);

    $currentLevel = $planet[$elementPlanetKey];

    $currentLevelCapacity = getElementStorageCapacities($elementId, $planet, []);

    $tableRangeStartLevel = $currentLevel - 3;
    $tableRangeEndLevel = $currentLevel + 6;

    if ($tableRangeStartLevel < 0) {
        $offset = $tableRangeStartLevel * (-1);

        $tableRangeStartLevel += $offset;
        $tableRangeEndLevel += $offset;
    }

    // Supports only one resource type
    $capacityResourceKey = getElementStoredResourceKeys($elementId)[0];

    $storageRows = [];

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

        $iterLevelCapacity = getElementStorageCapacities(
            $elementId,
            $planet,
            [
                'customLevel' => $iterLevel
            ]
        );

        $resourceCapacity = $iterLevelCapacity[$capacityResourceKey];
        $capacityDifference = ($resourceCapacity - $currentLevelCapacity[$capacityResourceKey]);

        $rowData['build_capacity'] = prettyNumber($resourceCapacity);
        $rowData['build_capacity_diff'] = prettyColorNumber(floor($capacityDifference));

        $storageRows[] = parsetemplate($rowTpl, $rowData);
    }

    return [
        'componentHTML' => implode('', $storageRows),
    ];
}

?>
