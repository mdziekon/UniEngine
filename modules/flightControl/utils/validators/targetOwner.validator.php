<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param array $props['fleetEntry']
 * @param array $props['fleetOwner']
 * @param array $props['targetOwner']
 * @param array $props['targetInfo']
 * @param array $props['usersStats']
 * @param array $props['fleetsInFlightCounters']
 * @param number $props['currentTimestamp']
 */
function validateTargetOwner($validationParams) {
    global $_GameConfig;

    $protectionConfig = [
        'isAllyProtectionEnabled' => ($_GameConfig['allyprotection'] == 1),
        'isAdminProtectionEnabled' => ($_GameConfig['adminprotection'] == 1),
        'isAntiBashProtectionEnabled' => ($_GameConfig['Protection_BashLimitEnabled'] == 1),
        'isAntiFarmProtectionEnabled' => ($_GameConfig['Protection_AntiFarmEnabled'] == 1),

        'antiFarmProtectionRate' => $_GameConfig['Protection_AntiFarmRate'],
    ];

    $validator = function ($input, $resultHelpers) use ($protectionConfig) {
        $fleetEntry = $input['fleetEntry'];
        $fleetOwner = $input['fleetOwner'];
        $targetOwner = $input['targetOwner'];
        $targetInfo = $input['targetInfo'];
        $usersStats = $input['usersStats'];
        $fleetsInFlightCounters = $input['fleetsInFlightCounters'];
        $currentTimestamp = $input['currentTimestamp'];

        if (isOnVacation($targetOwner)) {
            return $resultHelpers['createFailure']([
                'code' => (
                    isUserBanned($targetOwner) ?
                        'TARGET_USER_BANNED' :
                        'TARGET_USER_ON_VACATION'
                )
            ]);
        }

        if (
            $protectionConfig['isAllyProtectionEnabled'] &&
            FlightControl\Utils\Helpers\isMissionNoobProtectionChecked($fleetEntry['Mission']) &&
            $fleetOwner['ally_id'] > 0 &&
            $fleetOwner['ally_id'] == $targetOwner['ally_id']
        ) {
            return $resultHelpers['createFailure']([
                'code' => 'TARGET_ALLY_PROTECTION'
            ]);
        }

        if (
            !FlightControl\Utils\Helpers\isNoobProtectionEnabled() ||
            !FlightControl\Utils\Helpers\isMissionNoobProtectionChecked($fleetEntry['Mission'])
        ) {
            return $resultHelpers['createSuccess']([]);
        }

        $noobProtectionValidationResult = FlightControl\Utils\Validators\validateNoobProtection([
            'attackerUser' => $fleetOwner,
            'attackerStats' => $usersStats['fleetOwner'],
            'targetUser' => $targetOwner,
            'targetStats' => $usersStats['targetOwner'],
            'currentTimestamp' => $currentTimestamp,
        ]);

        if (!$noobProtectionValidationResult['isSuccess']) {
            return $resultHelpers['createFailure']([
                'code' => 'NOOB_PROTECTION_VALIDATION_ERROR',
                'params' => $noobProtectionValidationResult['error'],
            ]);
        }

        $isFleetOwnerSupportAdmin = CheckAuth('supportadmin', AUTHCHECK_NORMAL, $fleetOwner);
        $isTargetOwnerSupportAdmin = CheckAuth('supportadmin', AUTHCHECK_NORMAL, $targetOwner);

        if (
            $protectionConfig['isAdminProtectionEnabled'] &&
            (
                $isFleetOwnerSupportAdmin ||
                $isTargetOwnerSupportAdmin
            )
        ) {
            return $resultHelpers['createFailure']([
                'code' => 'ADMIN_PROTECTION_ERROR',
                'params' => [
                    'isFleetOwnerProtected' => $isFleetOwnerSupportAdmin,
                    'isTargetOwnerProtected' => $isTargetOwnerSupportAdmin,
                ],
            ]);
        }

        $usersPointsRatio = ($usersStats['fleetOwner']['points'] / $usersStats['targetOwner']['points']);

        $isBashCheckRequired = $protectionConfig['isAntiBashProtectionEnabled'];
        $isFarmCheckRequired = (
            $protectionConfig['isAntiFarmProtectionEnabled'] &&
            !($noobProtectionValidationResult['payload']['isTargetIdle']) &&
            $usersPointsRatio >= $protectionConfig['antifarm_rate']
        );

        if (
            !$isBashCheckRequired &&
            !$isFarmCheckRequired
        ) {
            return $resultHelpers['createSuccess']([]);
        }

        $targetId = $targetInfo['targetPlanetDetails']['id'];
        $targetUserId = $targetOwner['id'];

        $bashLimitValidationResult = FlightControl\Utils\Validators\validateBashLimit([
            'isFarmCheckRequired' => $isFarmCheckRequired,
            'isBashCheckRequired' => $isBashCheckRequired,
            'attackerUserId' => $fleetOwner['id'],
            'targetId' => $targetId,
            'targetUserId' => $targetUserId,
            'fleetsInFlightToTargetCount' => $fleetsInFlightCounters['aggressiveFleetsInFlight']['byTargetOwnerId'][$targetUserId],
            'fleetsInFlightToTargetOwnerCount' => $fleetsInFlightCounters['aggressiveFleetsInFlight']['byTargetId'][$targetId],
            'currentTimestamp' => $currentTimestamp,
        ]);

        if (!$bashLimitValidationResult['isSuccess']) {
            return $resultHelpers['createFailure']([
                'code' => 'BASH_PROTECTION_VALIDATION_ERROR',
                'params' => $bashLimitValidationResult['error'],
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($validationParams);
}

?>
