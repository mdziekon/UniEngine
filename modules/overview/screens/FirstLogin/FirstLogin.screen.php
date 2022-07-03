<?php

namespace UniEngine\Engine\Modules\Overview\Screens\FirstLogin;

use UniEngine\Engine\Modules\Overview;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param number $params['currentTimestamp']
 */
function render($props) {
    global $_Lang, $_GameConfig, $_DontShowMenus;

    $user = &$props['user'];
    $currentTimestamp = $props['currentTimestamp'];

    includeLang('firstlogin');

    // Run effects
    Overview\Screens\FirstLogin\Utils\Effects\updateUserOnFirstLogin([
        'userId' => $user['id'],
        'currentTimestamp' => $currentTimestamp,
    ]);

    if ($user['referred'] > 0) {
        $referringUserWithTasksData = Overview\Screens\FirstLogin\Utils\Helpers\getReferrerTasksData([
            'referredById' => $user['referred'],
        ]);

        Overview\Screens\FirstLogin\Utils\Effects\triggerUserReferralTask([
            'referringUserWithTasksData' => &$referringUserWithTasksData,
        ]);
        Overview\Screens\FirstLogin\Utils\Effects\handleReferralMultiAccountDetection([
            'user' => &$user,
            'referredById' => $user['referred'],
            'referringUserWithTasksData' => &$referringUserWithTasksData,
            'currentTimestamp' => $currentTimestamp,
        ]);
    }

    // Check, if this IP is Proxy
    Overview\Screens\FirstLogin\Utils\Effects\handleProxyDetection([
        'user' => &$user,
        'currentTimestamp' => $currentTimestamp,
    ]);

    // TODO: move this to utils
    // Give Free ProAccount for 7 days
    // doquery("INSERT INTO {{table}} VALUES (NULL, {$user['id']}, UNIX_TIMESTAMP(), 0, 0, 11, 0);", 'premium_free');

    Overview\Screens\FirstLogin\Utils\Effects\createUserDevLogDump([
        'userId' => $user['id'],
    ]);

    // Render the screen
    $_DontShowMenus = true;

    $screenTitle = $_Lang['FirstLogin_Title'];
    $localTemplateLoader = createLocalTemplateLoader(__DIR__);

    $_Lang['LoginPage_Text'] = parsetemplate(
        $_Lang['LoginPage_Text'],
        [
            'GameName' => $_GameConfig['game_name'],
            'GameSpeed' => prettyNumber($_GameConfig['game_speed'] / 2500),
            'ResSpeed' => prettyNumber($_GameConfig['resource_multiplier']),
            'FleetSpeed' => prettyNumber($_GameConfig['fleet_speed'] / 2500),
            'FleetDebris' => $_GameConfig['Fleet_Cdr'],
            'DefFlDebris' => $_GameConfig['Defs_Cdr'],
            'DefMiDebris' => $_GameConfig['Debris_Def_Rocket'],
            'MotherSize' => $_GameConfig['initial_fields'],
            'OpenTime' => prettyDate('d m Y - H:i:s', SERVER_MAINOPEN_TSTAMP, 1),
            'Protection_NewPlayerTime' => prettyNumber($_GameConfig['Protection_NewPlayerTime'] / 3600),
            'Protection_PointsLimit' => prettyNumber($_GameConfig['no_noob_protect'] * 1000),
        ]
    );

    $componentHTML = parsetemplate($localTemplateLoader('body'), $_Lang);

    display($componentHTML, $screenTitle, false);
}

?>
