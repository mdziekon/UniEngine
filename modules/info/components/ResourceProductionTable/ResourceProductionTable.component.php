<?php

namespace UniEngine\Engine\Modules\Info\Components\ResourceProductionTable;

/**
 * @param array $props
 * @param string $props['elementId']
 * @param arrayRef $props['planet']
 * @param arrayRef $props['user']
 * @param number $props['currentTimestamp']
 */
function render($props) {
    $elementId = $props['elementId'];
    $planet = &$props['planet'];
    $user = &$props['user'];
    $timestamp = $props['currentTimestamp'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $rowTpl = '';

    if (in_array($elementId, [ 1, 2, 3, 12 ])) {
        $rowTpl = $localTemplateLoader('productionFueledSourceRow');
    }
    if (in_array($elementId, [ 4 ])) {
        $rowTpl = $localTemplateLoader('productionRenewableSourceRow');
    }

    $elementPlanetKey = _getElementPlanetKey($elementId);

    $currentLevel = $planet[$elementPlanetKey];

    $currentLevelProduction = getElementProduction(
        $elementId,
        $planet,
        $user,
        [
            'useCurrentBoosters' => true,
            'currentTimestamp' => $timestamp,
            'customLevel' => $currentLevel,
            'customProductionFactor' => 10
        ]
    );

    $tableRangeStartLevel = $currentLevel - 3;
    $tableRangeEndLevel = $currentLevel + 6;

    if ($tableRangeStartLevel < 0) {
        $offset = $tableRangeStartLevel * (-1);

        $tableRangeStartLevel += $offset;
        $tableRangeEndLevel += $offset;
    }

    // Supports only one resource type produced / consumed
    $producedResourceKey = getElementProducedResourceKeys($elementId)[0];
    $consumedResourceKey = getElementConsumedResourceKeys($elementId)[0];

    $productionRows = [];

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

        $iterLevelProduction = getElementProduction(
            $elementId,
            $planet,
            $user,
            [
                'useCurrentBoosters' => true,
                'currentTimestamp' => $timestamp,
                'customLevel' => $iterLevel,
                'customProductionFactor' => 10
            ]
        );

        $resourceProduction = $iterLevelProduction[$producedResourceKey];
        $resourceConsumption = $iterLevelProduction[$consumedResourceKey];

        $productionDifference = ($resourceProduction - $currentLevelProduction[$producedResourceKey]);
        $consumptionDifference = ($resourceConsumption - $currentLevelProduction[$consumedResourceKey]);

        $rowData['build_prod'] = prettyNumber($resourceProduction);
        $rowData['build_prod_diff'] = prettyColorNumber(floor($productionDifference));
        $rowData['build_need'] = prettyColorNumber($resourceConsumption);
        $rowData['build_need_diff'] = prettyColorNumber(floor($consumptionDifference));

        $productionRows[] = parsetemplate($rowTpl, $rowData);
    }

    return [
        'componentHTML' => implode('', $productionRows),
    ];
}

?>
