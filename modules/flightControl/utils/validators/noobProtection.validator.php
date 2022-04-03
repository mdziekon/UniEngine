<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param object $props['attackerUser']
 * @param object $props['attackerStats']
 * @param object $props['targetUser']
 * @param object $props['targetStats']
 * @param number $props['currentTimestamp']
 */
function validateNoobProtection($validationParams) {
    global $_GameConfig;

    $protectionConfig = [
        'fixedBasicProtectionLimit' => $_GameConfig['noobprotectiontime'] * 1000,
        'noIdleProtectionTime' => $_GameConfig['no_idle_protect'] * TIME_DAY,
        'weakProtectionFinalLimit' => $_GameConfig['no_noob_protect'] * 1000,
        'weakProtectionMultiplier' => $_GameConfig['noobprotectionmulti'],
    ];

    $validator = function ($input, $resultHelpers) use ($protectionConfig) {
        $attackerUser = &$input['attackerUser'];
        $attackerStats = $input['attackerStats'];
        $targetUser = &$input['targetUser'];
        $targetStats = $input['targetStats'];
        $currentTimestamp = $input['currentTimestamp'];

        if ($attackerUser['total_rank'] < 1) {
            return $resultHelpers['createFailure']([
                'code' => 'ATTACKER_STATISTICS_UNAVAILABLE',
            ]);
        }
        if ($targetUser['total_rank'] < 1) {
            return $resultHelpers['createFailure']([
                'code' => 'TARGET_STATISTICS_UNAVAILABLE',
            ]);
        }
        if ($attackerUser['NoobProtection_EndTime'] > $currentTimestamp) {
            return $resultHelpers['createFailure']([
                'code' => 'ATTACKER_NOOBPROTECTION_ENDTIME_NOT_REACHED',
                'params' => [
                    'endTime' => $attackerUser['NoobProtection_EndTime'],
                    'timeLeft' => ($attackerUser['NoobProtection_EndTime'] - $currentTimestamp),
                ],
            ]);
        }
        if ($targetUser['first_login'] == 0) {
            return $resultHelpers['createFailure']([
                'code' => 'TARGET_NEVER_LOGGED_IN',
            ]);
        }
        if ($targetUser['NoobProtection_EndTime'] > $currentTimestamp) {
            return $resultHelpers['createFailure']([
                'code' => 'TARGET_NOOBPROTECTION_ENDTIME_NOT_REACHED',
                'params' => [
                    'endTime' => $targetUser['NoobProtection_EndTime'],
                    'timeLeft' => ($targetUser['NoobProtection_EndTime'] - $currentTimestamp),
                ],
            ]);
        }
        if ($attackerStats < $protectionConfig['fixedBasicProtectionLimit']) {
            return $resultHelpers['createFailure']([
                'code' => 'ATTACKER_NOOBPROTECTION_BASIC_LIMIT_NOT_REACHED',
                'params' => [
                    'basicLimit' => $protectionConfig['fixedBasicProtectionLimit'],
                ],
            ]);
        }

        if ($targetUser['onlinetime'] < ($currentTimestamp - $protectionConfig['noIdleProtectionTime'])) {
            return $resultHelpers['createSuccess']([
                'isTargetIdle' => true,
            ]);
        }

        if ($targetStats < $protectionConfig['fixedBasicProtectionLimit']) {
            return $resultHelpers['createFailure']([
                'code' => 'TARGET_NOOBPROTECTION_BASIC_LIMIT_NOT_REACHED',
                'params' => [
                    'basicLimit' => $protectionConfig['fixedBasicProtectionLimit'],
                ],
            ]);
        }

        if (
            $targetStats >= $protectionConfig['weakProtectionFinalLimit'] &&
            $attackerStats >= $protectionConfig['weakProtectionFinalLimit']
        ) {
            return $resultHelpers['createSuccess']([
                'isTargetIdle' => false,
            ]);
        }

        if ($attackerStats > ($targetStats * $protectionConfig['weakProtectionMultiplier'])) {
            return $resultHelpers['createFailure']([
                'code' => 'TARGET_NOOBPROTECTION_TOO_WEAK_BY_MULTIPLIER',
                'params' => [
                    'weakMultiplier' => $protectionConfig['weakProtectionMultiplier'],
                ],
            ]);
        }
        if ($targetStats > ($attackerStats * $protectionConfig['weakProtectionMultiplier'])) {
            return $resultHelpers['createFailure']([
                'code' => 'ATTACKER_NOOBPROTECTION_TOO_WEAK_BY_MULTIPLIER',
                'params' => [
                    'weakMultiplier' => $protectionConfig['weakProtectionMultiplier'],
                ],
            ]);
        }

        return $resultHelpers['createSuccess']([
            'isTargetIdle' => false,
        ]);
    };

    return createFuncWithResultHelpers($validator)($validationParams);
}

?>
