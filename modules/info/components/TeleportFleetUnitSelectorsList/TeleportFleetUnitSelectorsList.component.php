<?php

namespace UniEngine\Engine\Modules\Info\Components\TeleportFleetUnitSelectorsList;

/**
 * @param array $props
 * @param arrayRef $props['planet']
 */
function render($props) {
    global $_Lang, $_Vars_ElementCategories, $_Vars_GameElements;

    $planet = &$props['planet'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $unitSelectorTpl = $localTemplateLoader('unitSelector');

    $availableUnits = array_filter($_Vars_ElementCategories['fleet'], function ($elementId) use (&$planet, &$_Vars_GameElements) {
        return $planet[$_Vars_GameElements[$elementId]] > 0;
    });

    if (empty($availableUnits)) {
        return [
            'componentHTML' => '',
        ];
    }

    $unitSelectors = array_map_withkeys(
        $availableUnits,
        function ($elementId) use (&$unitSelectorTpl, &$planet, &$_Vars_GameElements, &$_Lang) {
            $elementCount = $planet[$_Vars_GameElements[$elementId]];

            $tplBodyProps = [
                'fleet_setmax' => $_Lang['fleet_setmax'],
                'fleet_setmin' => $_Lang['fleet_setmin'],
                'fleet_id' => $elementId,
                'fleet_name' => $_Lang['tech'][$elementId],
                'fleet_max' => prettyNumber($elementCount),
                'fleet_countmax' => $elementCount,
            ];

            return parsetemplate($unitSelectorTpl, $tplBodyProps);
        }
    );

    return [
        'componentHTML' => implode('', $unitSelectors),
    ];
}

?>
