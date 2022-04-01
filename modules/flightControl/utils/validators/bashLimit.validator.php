<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param boolean $props['isFarmCheckRequired']
 * @param boolean $props['isBashCheckRequired']
 * @param string $props['attackerUserId']
 * @param string $props['targetId']
 * @param string $props['targetUserId']
 * @param number $props['fleetsInFlightToTargetCount']
 * @param number $props['fleetsInFlightToTargetOwnerCount']
 * @param number $props['currentTimestamp']
 */
function validateBashLimit($validationParams) {
    global $_GameConfig;

    $protectionLimits = [
        'farm' => [
            'totalCount' => $_GameConfig['Protection_AntiFarmCountTotal'],
            'planetCount' => $_GameConfig['Protection_AntiFarmCountPlanet'],
        ],
        'bash' => [
            'totalCount' => $_GameConfig['Protection_BashLimitCountTotal'],
            'planetCount' => $_GameConfig['Protection_BashLimitCountPlanet'],
        ],
    ];

    $validator = function ($input, $resultHelpers) use (&$_GameConfig, $protectionLimits) {
        $isFarmCheckRequired = $input['isFarmCheckRequired'];
        $isBashCheckRequired = $input['isBashCheckRequired'];
        $attackerUserId = $input['attackerUserId'];
        $targetId = $input['targetId'];
        $targetUserId = $input['targetUserId'];
        $fleetsInFlightToTargetCount = $input['fleetsInFlightToTargetCount'];
        $fleetsInFlightToTargetOwnerCount = $input['fleetsInFlightToTargetOwnerCount'];
        $currentTimestamp = $input['currentTimestamp'];

        $verificationTimestamps = [];

        if ($isFarmCheckRequired) {
            $todayDate = explode('.', date('d.m.Y', $currentTimestamp));
            $todayStartTimestamp = mktime(0, 0, 0, $todayDate[1], $todayDate[0], $todayDate[2]);
            if ($todayStartTimestamp <= 0) {
                $todayStartTimestamp = 0;
            }

            $verificationTimestamps[] = [
                'type' => 'farm',
                // 'key' => 'antifarm',
                'stamp' => $todayStartTimestamp,
            ];
        }
        if ($isBashCheckRequired) {
            $verificationTimestamps[] = [
                'type' => 'bash',
                // 'key' => 'bashLimit',
                'stamp' => ($currentTimestamp - $_GameConfig['Protection_BashLimitInterval']),
            ];
        }

        sort($verificationTimestamps, SORT_ASC);
        $verificationTimestampRangeStart = $verificationTimestamps[0]['stamp'];

        $limitCountersByType = [
            'bash' => [],
            'farm' => [],
        ];

        $logEntriesResult = FlightControl\Utils\Fetchers\fetchBashValidatorFlightLogEntries([
            'logsRangeStart' => $verificationTimestampRangeStart,
            'attackerUserId' => $attackerUserId,
            'targetUserId' => $targetUserId,
        ]);

        if ($logEntriesResult->num_rows > 0) {
            while ($logEntry = $logEntriesResult->fetch_assoc()) {
                foreach ($verificationTimestamps as $verificationTimestamp) {
                    if (($logEntry['Fleet_Time_Start'] + $logEntry['Fleet_Time_ACSAdd']) < $verificationTimestamp['stamp']) {
                        continue;
                    }

                    $flightTargetId = (
                        $logEntry['Fleet_End_ID_Changed'] > 0 ?
                            $logEntry['Fleet_End_ID_Changed'] :
                            $logEntry['Fleet_End_ID']
                    );

                    $limitType = $verificationTimestamp['type'];

                    if (!isset($limitCountersByType[$limitType][$flightTargetId])) {
                        $limitCountersByType[$limitType][$flightTargetId] = 0;
                    }
                    $limitCountersByType[$limitType][$flightTargetId] += 1;
                }
            }
        }

        foreach ($verificationTimestamps as $verificationTimestamp) {
            $limitType = $verificationTimestamp['type'];

            $totalLimitInstances = array_sum($limitCountersByType[$limitType]);

            if ($totalLimitInstances >= $protectionLimits[$limitType]['totalCount']) {
                return $resultHelpers['createFailure']([
                    'code' => null,
                ]);

                // sprintf($_Lang['fl3_Protect_AttackLimitTotal'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
            }
            if (($totalLimitInstances + $fleetsInFlightToTargetOwnerCount) >= $protectionLimits[$limitType]['totalCount']) {
                return $resultHelpers['createFailure']([
                    'code' => null,
                ]);

                // sprintf($_Lang['fl3_Protect_AttackLimitTotalFly'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
            }

            $targetLimitInstances = $limitCountersByType[$limitType][$targetId];

            if ($targetLimitInstances >= $protectionLimits[$limitType]['planetCount']) {
                return $resultHelpers['createFailure']([
                    'code' => null,
                ]);

                // sprintf($_Lang['fl3_Protect_AttackLimitSingle'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
            }
            if (($targetLimitInstances + $fleetsInFlightToTargetCount) >= $protectionLimits[$limitType]['planetCount']) {
                return $resultHelpers['createFailure']([
                    'code' => null,
                ]);

                // sprintf($_Lang['fl3_Protect_AttackLimitSingleFly'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
            }
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($validationParams);
}

?>
