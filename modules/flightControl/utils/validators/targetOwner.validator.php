<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Validators;

/**
 * @param array $props
 * @param array $props['targetUser']
 */
function validateTargetOwner($validationParams) {
    $validator = function ($input, $resultHelpers) {
        $targetUser = $input['targetUser'];

        if (isOnVacation($targetUser)) {
            return $resultHelpers['createFailure']([
                'code' => (
                    isUserBanned($targetUser) ?
                        'TARGET_USER_BANNED' :
                        'TARGET_USER_ON_VACATION'
                )
            ]);
        }


        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($validationParams);
}

?>
