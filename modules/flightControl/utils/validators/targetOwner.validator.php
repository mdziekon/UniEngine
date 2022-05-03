<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

use UniEngine\Engine\Modules\FlightControl;

/**
 * @param array $props
 * @param array $props['fleetEntry']
 * @param array $props['fleetOwner']
 * @param array $props['targetOwner']
 */
function validateTargetOwner($validationParams) {
    global $_GameConfig;

    $protectionConfig = [
        'isAllyProtectionEnabled' => ($_GameConfig['allyprotection'] == 1),
    ];

    $validator = function ($input, $resultHelpers) use ($protectionConfig) {
        $fleetEntry = $input['fleetEntry'];
        $fleetOwner = $input['fleetOwner'];
        $targetOwner = $input['targetOwner'];

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


        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($validationParams);
}

?>
