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

    // Check, if this IP is Proxy
    FirstLogin\Utils\Effects\handleProxyDetection([
        'user' => &$user,
        'currentTimestamp' => $currentTimestamp,
    ]);

    // TODO: move this to utils
    // Give Free ProAccount for 7 days
    // doquery("INSERT INTO {{table}} VALUES (NULL, {$user['id']}, UNIX_TIMESTAMP(), 0, 0, 11, 0);", 'premium_free');

    FirstLogin\Utils\Effects\createUserDevLogDump([
        'userId' => $user['id'],
    ]);
}

?>
