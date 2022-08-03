<?php

namespace UniEngine\Engine\Modules\Info\Components\TeleportFleetUnitSelectorsList;

use UniEngine\Engine\Includes\Helpers\World;

/**
 * @param array $props
 * @param arrayRef $props['planet']
 */
function render($props) {
    global $_Lang, $_Vars_ElementCategories;

    $planet = &$props['planet'];
    $user = [];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $unitSelectorTpl = $localTemplateLoader('unitSelector');

    $availableUnits = array_filter($_Vars_ElementCategories['fleet'], function ($elementId) use (&$planet, &$user) {
        return World\Elements\getElementCurrentCount($elementId, $planet, $user);
    });

    if (empty($availableUnits)) {
        return [
            'componentHTML' => '',
        ];
    }

    $unitSelectors = array_map_withkeys(
        $availableUnits,
        function ($elementId) use (&$unitSelectorTpl, &$planet, &$user, &$_Lang) {
            $elementCount = World\Elements\getElementCurrentCount($elementId, $planet, $user);

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
