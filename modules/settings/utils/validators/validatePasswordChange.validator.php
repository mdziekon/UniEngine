<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Validators;

use UniEngine\Engine\Modules\Session;

/**
 * @param array $params
 * @param array $params['input']
 * @param string $params['input']['oldPassword']
 * @param string $params['input']['newPassword']
 * @param string $params['input']['newPasswordConfirm']
 * @param arrayRef $params['currentUser']
 */
function validatePasswordChange($params) {
    $currentUser = &$params['currentUser'];

    $executor = function ($input, $resultHelpers) use (&$currentUser) {
        $oldPassword = $input['oldPassword'];
        $newPassword = $input['newPassword'];
        $newPasswordConfirm = $input['newPasswordConfirm'];

        $currentUserPasswordHash = $currentUser['password'];

        $inputOldPasswordHash = Session\Utils\LocalIdentityV1\hashPassword([
            'password' => $oldPassword,
        ]);
        $inputNewPasswordHash = Session\Utils\LocalIdentityV1\hashPassword([
            'password' => $newPassword,
        ]);

        if ($inputOldPasswordHash != $currentUserPasswordHash) {
            return $resultHelpers['createFailure']([
                'code' => 'OLD_PASSWORD_INCORRECT',
            ]);
        }
        if (strlen($newPassword) < 4) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_PASSWORD_TOO_SHORT',
            ]);
        }
        if ($inputNewPasswordHash == $currentUserPasswordHash) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_PASSWORD_SAME_AS_OLD',
            ]);
        }
        if ($newPassword != $newPasswordConfirm) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_PASSWORD_CONFIRMATION_INVALID',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($executor)($params['input']);
}

?>
