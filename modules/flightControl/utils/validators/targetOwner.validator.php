<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param array $props['fleetEntry']
 * @param array $props['fleetOwner']
 * @param array $props['targetOwner']
 * @param array $props['usersStats']
 * @param number $props['currentTimestamp']
 */
function validateTargetOwner($validationParams) {
    global $_GameConfig;

    $protectionConfig = [
        'isAllyProtectionEnabled' => ($_GameConfig['allyprotection'] == 1),
        'isAdminProtectionEnabled' => ($_GameConfig['adminprotection'] == 1),
    ];

    $validator = function ($input, $resultHelpers) use ($protectionConfig) {
        $fleetEntry = $input['fleetEntry'];
        $fleetOwner = $input['fleetOwner'];
        $targetOwner = $input['targetOwner'];
        $usersStats = $input['usersStats'];
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

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($validationParams);
}

?>
