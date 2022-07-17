<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\Morale;

use UniEngine\Engine\Modules\Overview\Screens\Overview\Components\Morale;

/**
 * @param array $props
 * @param arrayRef $props['user']
 * @param number $props['currentTimestamp']
 */
function render($props) {
    global $_Lang;

    if (!MORALE_ENABLED) {
        return [
            'componentHTML' => '',
            'globalJS' => '',
        ];
    }

    $user = &$props['user'];
    $currentTimestamp = $props['currentTimestamp'];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $moraleLevelColor = null;

    if ($user['morale_level'] > 0) {
        $moraleLevelColor = 'lime';
    } else if ($user['morale_level'] == 0) {
        $moraleLevelColor = '';
    } else if ($user['morale_level'] > -50) {
        $moraleLevelColor = 'orange';
    } else {
        $moraleLevelColor = 'red';
    }

    $moraleStatusData = Morale\Utils\getMoraleStatusData([
        'user' => &$user,
        'currentTimestamp' => $currentTimestamp,
    ]);

    $tplBodyParams = [
        'Insert_Morale_Level' => $user['morale_level'],
        'Insert_Morale_Color' => $moraleLevelColor,
        'Insert_Morale_Status' => $moraleStatusData['text'],
        'Insert_Morale_Points' => sprintf(
            $_Lang['Box_Morale_Points'],
            prettyNumber($user['morale_points'])
        ),
    ];
    $tplBodyParams = array_merge($_Lang, $tplBodyParams);

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        $tplBodyParams
    );

    return [
        'componentHTML' => $componentHTML,
        'globalJS' => $moraleStatusData['globalJS'],
    ];
}

?>
