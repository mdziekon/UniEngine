<?php

namespace UniEngine\Engine\Modules\Overview\Screens\AbandonPlanet;

use UniEngine\Engine\Common;
use UniEngine\Engine\Modules\Overview\Screens\AbandonPlanet;

/**
 * @param array $params
 * @param arrayRef $params['input']
 * @param arrayRef $params['user']
 * @param arrayRef $params['planet']
 * @param number $params['currentTimestamp']
 */
function render($props) {
    global $_Lang;

    $input = &$props['input'];
    $user = &$props['user'];
    $planet = &$props['planet'];
    $currentTimestamp = $props['currentTimestamp'];

    if (isOnVacation()) {
        return message($_Lang['Vacation_WarnMsg'], $_Lang['Vacation']);
    }

    $effectsResult = AbandonPlanet\runEffects([
        'input' => &$input,
        'user' => &$user,
        'planet' => &$planet,
        'currentTimestamp' => $currentTimestamp,
    ]);

    if ($effectsResult['isSuccess']) {
        header("Location: {$effectsResult['payload']['redirectUrl']}");

        return safeDie();
    }

    $screenTitle = $_Lang['Abandon_TitleMain'];
    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $tplBodyParams = [
        'Abandon_Desc' => sprintf(
            $_Lang['Abandon_Desc'],
            (
                $planet['planet_type'] == 1 ?
                    $_Lang['Abandon_Planet'] :
                    $_Lang['Abandon_Moon']
            ),
            $planet['name'],
            Common\Components\GalaxyPlanetLink\render([
                'coords' => $planet,
                'linkAttrs' => [
                    'class' => 'orange',
                ],
            ])
        ),
        'Abandon_Ins_Pass' => $user['password'],

        'Abandon_Ins_MsgHide' => (
            $effectsResult['isSuccess'] === null ?
                'style="display: none;"' :
                ''
        ),
        'Abandon_Ins_MsgColor' => 'red',
        'Abandon_Ins_MsgTxt' => (
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
