<?php

namespace UniEngine\Engine\Modules\Overview\Screens\FirstLogin\Utils\Effects;

use UniEngine\Engine\Includes\Helpers\Users;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param number $params['currentTimestamp']
 */
function handleProxyDetection($props) {
    global $_EnginePath, $_Included_AlertSystemUtilities;

    $user = &$props['user'];
    $currentTimestamp = $props['currentTimestamp'];

    $usersIP = Users\Session\getCurrentIP();
    $IPHash = md5($usersIP);
    $Query_CheckProxy = "SELECT `ID`, `isProxy` FROM {{table}} WHERE `ValueHash` = '{$IPHash}' LIMIT 1;";
    $Result_CheckProxy = doquery($Query_CheckProxy, 'used_ip_and_ua', true);

    if (
        !$Result_CheckProxy ||
        $Result_CheckProxy['isProxy'] != 1
    ) {
        return;
    }

    if (!isset($_Included_AlertSystemUtilities)) {
        include($_EnginePath.'includes/functions/AlertSystemUtilities.php');
        $_Included_AlertSystemUtilities = true;
    }

    $ALERT_SENDER = 5;

    $alertSystemFilterParams = [];
    $alertSystemFilterParams['place'] = 4;
    $alertSystemFilterParams['alertsender'] = $ALERT_SENDER;
    $alertSystemFilterParams['users'] = [
        $user['id'],
    ];
    $alertSystemFilterParams['ips'] = [
        $Result_CheckProxy['ID'],
    ];

    $alertSystemFilterResult = AlertUtils_CheckFilters(
        $alertSystemFilterParams, [
            'DontLoad' => true,
            'DontLoad_OnlyIfCacheEmpty' => true,
        ]
    );

    if (!$alertSystemFilterResult['SendAlert']) {
        return;
    }

    $alertParams = [
        'Data' => [
            'IPID' => $Result_CheckProxy['ID'],
        ],
    ];

    if ($usersIP == $user['ip_at_reg']) {
        $alertParams['Data']['RegIP'] = true;
    }

    Alerts_Add($ALERT_SENDER, $currentTimestamp, 1, 3, 8, $user['id'], $alertParams['Data']);
}

?>
