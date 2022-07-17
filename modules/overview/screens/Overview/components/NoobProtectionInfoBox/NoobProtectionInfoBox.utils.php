<?php

namespace UniEngine\Engine\Modules\Overview\Screens\Overview\Components\NoobProtectionInfoBox;

use UniEngine\Engine\Modules\Overview\Screens\Overview\Components\NoobProtectionInfoBox;

/**
 * @param array $params
 * @param arrayRef $params['input']
 * @param arrayRef $params['user']
 * @param number $params['currentTimestamp']
 */
function runEffects($props) {
    $input = &$props['input'];
    $user = &$props['user'];
    $currentTimestamp = $props['currentTimestamp'];

    $isProtectedByNoobProtection = NoobProtectionInfoBox\Utils\isProtectedByNoobProtection([
        'user' => &$user,
        'currentTimestamp' => $currentTimestamp,
    ]);

    if (!$isProtectedByNoobProtection) {
        return [
            'isSuccess' => null,
        ];
    }
    if (
        !isset($input['cancelprotection']) ||
        $input['cancelprotection'] != '1'
    ) {
        return [
            'isSuccess' => null,
        ];
    }

    NoobProtectionInfoBox\Utils\Effects\turnOffProtection([
        'user' => &$user,
        'currentTimestamp' => $currentTimestamp,
    ]);

    return [
        'isSuccess' => true,
        'payload' => [
            'protectionTurnedOff' => true,
        ],
    ];
}

?>
