<?php

namespace UniEngine\Engine\Modules\Overview\Screens\FirstLogin;

use UniEngine\Engine\Modules\Overview\Screens\FirstLogin;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param number $params['currentTimestamp']
 */
function runEffects($props) {
    $user = &$props['user'];
    $currentTimestamp = $props['currentTimestamp'];

    FirstLogin\Utils\Effects\updateUserOnFirstLogin([
        'userId' => $user['id'],
        'currentTimestamp' => $currentTimestamp,
    ]);

    if ($user['referred'] > 0) {
        $referringUserWithTasksData = FirstLogin\Utils\Helpers\getReferrerTasksData([
            'referredById' => $user['referred'],
        ]);

        FirstLogin\Utils\Effects\triggerUserReferralTask([
            'referringUserWithTasksData' => &$referringUserWithTasksData,
        ]);
        FirstLogin\Utils\Effects\handleReferralMultiAccountDetection([
            'user' => &$user,
            'referredById' => $user['referred'],
            'referringUserWithTasksData' => &$referringUserWithTasksData,
            'currentTimestamp' => $currentTimestamp,
        ]);
    }

    FirstLogin\Utils\Effects\handleProxyDetection([
        'user' => &$user,
        'currentTimestamp' => $currentTimestamp,
    ]);

    // Note: disabled by default
    // FirstLogin\Utils\Effects\giveUserPremium([
    //     'userId' => $user['id'],
    //     'currentTimestamp' => $currentTimestamp,
    // ]);

    FirstLogin\Utils\Effects\createUserDevLogDump([
        'userId' => $user['id'],
    ]);
}

?>
