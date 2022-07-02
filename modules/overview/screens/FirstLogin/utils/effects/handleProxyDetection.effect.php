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
    if($Result_CheckProxy['ID'] > 0 AND $Result_CheckProxy['isProxy'] == 1)
    {
        if(!isset($_Included_AlertSystemUtilities))
        {
            include($_EnginePath.'includes/functions/AlertSystemUtilities.php');
            $_Included_AlertSystemUtilities = true;
        }
        $FiltersData = array();
        $FiltersData['place'] = 4;
        $FiltersData['alertsender'] = 5;
        $FiltersData['users'] = array($user['id']);
        $FiltersData['ips'] = array($Result_CheckProxy['ID']);

        $FilterResult = AlertUtils_CheckFilters($FiltersData, array('DontLoad' => true, 'DontLoad_OnlyIfCacheEmpty' => true));
        if($FilterResult['SendAlert'])
        {
            $_Alert['Data']['IPID'] = $Result_CheckProxy['ID'];
            if($usersIP == $user['ip_at_reg'])
            {
                $_Alert['Data']['RegIP'] = true;
            }

            Alerts_Add(5, $currentTimestamp, 1, 3, 8, $user['id'], $_Alert['Data']);
        }
    }
}

?>
