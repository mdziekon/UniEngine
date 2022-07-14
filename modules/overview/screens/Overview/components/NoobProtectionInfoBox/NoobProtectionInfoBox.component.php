<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\NoobProtectionInfoBox;

use UniEngine\Engine\Modules\Overview\Screens\Overview\Components\NoobProtectionInfoBox;

/**
 * @param array $params
 * @param arrayRef $params['input']
 * @param arrayRef $params['user']
 * @param number $params['currentTimestamp']
 */
function render($props) {
    global $_Lang;

    $input = &$props['input'];
    $user = &$props['user'];
    $currentTimestamp = $props['currentTimestamp'];

    $isProtectedByNoobProtection = NoobProtectionInfoBox\Utils\isProtectedByNoobProtection([
        'user' => &$user,
        'currentTimestamp' => $currentTimestamp,
    ]);

    if (!$isProtectedByNoobProtection) {
        return [
            'componentHTML' => '',
            'globalJS' => '',
        ];
    }

    $effectsResult = NoobProtectionInfoBox\runEffects([
        'input' => &$input,
        'user' => &$user,
        'currentTimestamp' => $currentTimestamp,
    ]);

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    if (
        $effectsResult['isSuccess'] &&
        $effectsResult['payload']['protectionTurnedOff']
    ) {
        $componentHTML = parsetemplate(
            $localTemplateLoader('turnOffMessage'),
            $_Lang
        );

        return [
            'componentHTML' => $componentHTML,
            'globalJS' => '',
        ];
    }

    $protectionTimeLeft = $user['NoobProtection_EndTime'] - $currentTimestamp;

    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        [
            'data_ProtectionCounterMessage' => sprintf(
                $_Lang['NewUserProtection_Text'],
                pretty_time($protectionTimeLeft, true, 'dhms')
            )
        ]
    );

    $protectionCountdownJS = InsertJavaScriptChronoApplet('newprotect', '', $protectionTimeLeft);

    return [
        'componentHTML' => $componentHTML,
        'globalJS' => $protectionCountdownJS,
    ];
}

?>
