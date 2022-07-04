<?php

namespace UniEngine\Engine\Modules\Overview\Screens\PlanetNameChange;

use UniEngine\Engine\Common;
use UniEngine\Engine\Modules\Overview\Screens\PlanetNameChange;

/**
 * @param array $params
 * @param arrayRef $params['input']
 * @param arrayRef $params['user']
 * @param arrayRef $params['planet']
 */
function render($props) {
    global $_Lang;

    $input = &$props['input'];
    $user = &$props['user'];
    $planet = &$props['planet'];

    $effectsResult = PlanetNameChange\runEffects([
        'input' => &$input,
        'user' => &$user,
        'planet' => &$planet,
    ]);

    $screenTitle = $_Lang['Rename_TitleMain'];
    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $isOnPlanet = $planet['planet_type'] == 1;
    $galaxyPlanetLink = Common\Components\GalaxyPlanetLink\render([
        'coords' => $planet,
    ]);

    $tplBodyParams = [
        'Rename_CurrentName' => sprintf(
            $_Lang['Rename_CurrentName'],
            (
                $isOnPlanet ?
                    $_Lang['Rename_Planet'] :
                    $_Lang['Rename_Moon']
            )
        ),
        'Rename_Ins_CurrentName' => "{$planet['name']} {$galaxyPlanetLink}",

        'Rename_Ins_MsgHide' => (
            $effectsResult['isSuccess'] === null ?
                'style="display: none;"' :
                ''
        ),
        'Rename_Ins_MsgColor' => (
            $effectsResult['isSuccess'] !== null ?
                $effectsResult['payload']['color'] :
                ''
        ),
        'Rename_Ins_MsgTxt' => (
            $effectsResult['isSuccess'] !== null ?
                $effectsResult['payload']['message'] :
                ''
        ),
    ];
    $tplBodyParams = array_merge($_Lang, $tplBodyParams);

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        $tplBodyParams
    );

    display($componentHTML, $screenTitle);
}

?>
