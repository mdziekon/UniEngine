<?php

namespace UniEngine\Engine\Modules\Overview\Screens\FirstLogin\Utils\Effects;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param number $params['referredById']
 * @param arrayRef $params['referringUserWithTasksData']
 * @param number $params['currentTimestamp']
 */
function handleReferralMultiAccountDetection($props) {
    global $_EnginePath, $_Included_AlertSystemUtilities;

    $user = &$props['user'];
    $userId = $user['id'];
    $referredById = $props['referredById'];
    $referringUserWithTasksData = &$props['referringUserWithTasksData'];
    $currentTimestamp = $props['currentTimestamp'];

    $_Included_AlertSystemUtilities = true;
    include($_EnginePath.'includes/functions/AlertSystemUtilities.php');

    $CheckIntersection = AlertUtils_IPIntersect($userId, $referredById, array
    (
        'LastTimeDiff' => (TIME_DAY * 60),
        'ThisTimeDiff' => (TIME_DAY * 60),
        'ThisTimeStamp' => ($currentTimestamp - SERVER_MAINOPEN_TSTAMP)
    ));

    if($CheckIntersection !== false)
    {
        $FiltersData = array();
        $FiltersData['place'] = 4;
        $FiltersData['alertsender'] = 4;
        $FiltersData['users'] = array($userId, $referredById);
        $FiltersData['ips'] = $CheckIntersection['Intersect'];
        $FiltersData['newuser'] = $userId;
        $FiltersData['referrer'] = $referredById;
        foreach($CheckIntersection['Intersect'] as $IP)
        {
            $FiltersData['logcount'][$IP][$userId] = $CheckIntersection['IPLogData'][$userId][$IP]['Count'];
            $FiltersData['logcount'][$IP][$referredById] = $CheckIntersection['IPLogData'][$referredById][$IP]['Count'];
        }

        $FilterResult = AlertUtils_CheckFilters($FiltersData, array('Save' => true));
        if($FilterResult['SendAlert'])
        {
            $_Alert['Data']['ReferrerID'] = $referredById;
            foreach($CheckIntersection['Intersect'] as $ThisIPID)
            {
                $_Alert['Data']['Intersect'][] = array
                (
                    'IPID' => $ThisIPID,
                    'NewUser' => $CheckIntersection['IPLogData'][$userId][$ThisIPID],
                    'OldUser' => $CheckIntersection['IPLogData'][$referredById][$ThisIPID]
                );
            }
            if(!empty($referringUserWithTasksData['TaskData']))
            {
                $_Alert['Data']['Tasks'] = $referringUserWithTasksData['TaskData'];
            }

            $Query_AlertOtherUsers .= "SELECT DISTINCT `User_ID` FROM {{table}} WHERE ";
            $Query_AlertOtherUsers .= "`User_ID` NOT IN ({$userId}, {$referredById}) AND ";
            $Query_AlertOtherUsers .= "`IP_ID` IN (".implode(', ', $CheckIntersection['Intersect']).") AND ";
            $Query_AlertOtherUsers .= "`Count` > `FailCount`;";
            $Result_AlertOtherUsers = doquery($Query_AlertOtherUsers, 'user_enterlog');
            if($Result_AlertOtherUsers->num_rows > 0)
            {
                while($FetchData = $Result_AlertOtherUsers->fetch_assoc())
                {
                    $_Alert['Data']['OtherUsers'][] = $FetchData['User_ID'];
                }
            }

            Alerts_Add(4, $currentTimestamp, 1, 2, 8, $userId, $_Alert['Data']);
        }
    }
}

?>
