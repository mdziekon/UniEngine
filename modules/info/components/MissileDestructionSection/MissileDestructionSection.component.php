<?php

namespace UniEngine\Engine\Modules\Info\Components\MissileDestructionSection;

use UniEngine\Engine\Includes\Helpers\World;

/**
 * @param array $props
 * @param string $props['elementId']
 * @param arrayRef $props['planet']
 * @param arrayRef $props['user']
 */
function render($props) {
    global $_Lang, $_Vars_ElementCategories;

    $elementId = $props['elementId'];
    $planet = &$props['planet'];
    $user = &$props['user'];

    $elementLevel = World\Elements\getElementCurrentLevel($elementId, $planet, $user);

    if ($elementLevel <= 0) {
        return [
            'componentHTML' => '',
        ];
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $missileRowTpl = $localTemplateLoader('missileRow');

    $missileRows = array_map_withkeys(
        $_Vars_ElementCategories['rockets'],
        function ($missileId) use (&$planet, &$missileRowTpl, &$_Lang) {
            $missileCount = World\Elements\getElementCurrentCount($missileId, $planet, $user);

            $rowTplProps = [
                'DestroyRockets_ID'             => $missileId,
                'DestroyRockets_Name'           => $_Lang['tech'][$missileId],
                'DestroyRockets_Count'          => $missileCount,
                'DestroyRockets_PrettyCount'    => prettyNumber($missileCount),

                'Lang_rocket_everything'        => $_Lang['rocket_everything'],
                'Lang_rocket_nothing'           => $_Lang['rocket_nothing'],
            ];

            return parsetemplate($missileRowTpl, $rowTplProps);
        }
    );

    $tplProps = [
        'DestroyRockets_Insert_Rows' => implode('', $missileRows),
    ];

    return [
        'componentHTML' => parsetemplate(
            $localTemplateLoader('body'),
            array_merge($_Lang, $tplProps)
        ),
    ];
}

?>
