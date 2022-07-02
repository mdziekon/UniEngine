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

    $intersectionCheckResult = AlertUtils_IPIntersect(
        $userId,
        $referredById,
        [
            'LastTimeDiff' => (TIME_DAY * 60),
            'ThisTimeDiff' => (TIME_DAY * 60),
            'ThisTimeStamp' => ($currentTimestamp - SERVER_MAINOPEN_TSTAMP)
        ]
    );

    if ($intersectionCheckResult === false) {
        return;
    }

    $ALERT_SENDER = 4;

    $alertSystemFilterParams = [];
    $alertSystemFilterParams['place'] = 4;
    $alertSystemFilterParams['alertsender'] = $ALERT_SENDER;
    $alertSystemFilterParams['users'] = [
        $userId,
        $referredById,
    ];
    $alertSystemFilterParams['ips'] = $intersectionCheckResult['Intersect'];
    $alertSystemFilterParams['newuser'] = $userId;
    $alertSystemFilterParams['referrer'] = $referredById;
    $alertSystemFilterParams['logcount'] = object_map(
        $intersectionCheckResult['Intersect'],
        function ($intersectedIpId) use ($intersectionCheckResult, $userId, $referredById) {
            return [
                [
                    $userId => $intersectionCheckResult['IPLogData'][$userId][$intersectedIpId]['Count'],
                    $referredById => $intersectionCheckResult['IPLogData'][$referredById][$intersectedIpId]['Count'],
                ],
                $intersectedIpId
            ];
        }
    );

    $alertSystemFilterResult = AlertUtils_CheckFilters(
        $alertSystemFilterParams,
        [
            'Save' => true,
        ]
    );

    if (!$alertSystemFilterResult['SendAlert']) {
        return;
    }

    $alertParams = [
        'Data' => [
            'ReferrerID' => $referredById,
            'Intersect' => array_map_withkeys(
                $intersectionCheckResult['Intersect'],
                function ($intersectedIpId) use ($intersectionCheckResult, $userId, $referredById) {
                    return [
                        'IPID' => $intersectedIpId,
                        'NewUser' => $intersectionCheckResult['IPLogData'][$userId][$intersectedIpId],
                        'OldUser' => $intersectionCheckResult['IPLogData'][$referredById][$intersectedIpId],
                    ];
                }
            ),
        ],
    ];

    if (!empty($referringUserWithTasksData['TaskData'])) {
        $alertParams['Data']['Tasks'] = $referringUserWithTasksData['TaskData'];
    }

    $Query_AlertOtherUsers = '';
    $Query_AlertOtherUsers .= "SELECT DISTINCT `User_ID` FROM {{table}} WHERE ";
    $Query_AlertOtherUsers .= "`User_ID` NOT IN ({$userId}, {$referredById}) AND ";
    $Query_AlertOtherUsers .= "`IP_ID` IN (".implode(', ', $intersectionCheckResult['Intersect']).") AND ";
    $Query_AlertOtherUsers .= "`Count` > `FailCount`;";
    $Result_AlertOtherUsers = doquery($Query_AlertOtherUsers, 'user_enterlog');

    $alertParams['Data']['OtherUsers'] = mapQueryResults($Result_AlertOtherUsers, function ($otherUserEntry) {
        return $otherUserEntry['User_ID'];
    });

    Alerts_Add($ALERT_SENDER, $currentTimestamp, 1, 2, 8, $userId, $alertParams['Data']);
}

?>
