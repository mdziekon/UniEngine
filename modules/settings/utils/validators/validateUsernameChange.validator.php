<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Validators;

/**
 * @param array $params
 * @param array $params['input']
 * @param string $params['input']['newUsername']
 * @param arrayRef $params['currentUser']
 */
function validateUsernameChange($params) {
    $currentUser = &$params['currentUser'];

    $executor = function ($input, $resultHelpers) use (&$currentUser) {
        $newUsername = $input['newUsername'];
        $currentUsername = $currentUser['username'];

        $CHANGE_COST = 10;
        $USERNAME_MIN_LENGTH = 4;

        if ($currentUser['darkEnergy'] < $CHANGE_COST) {
            return $resultHelpers['createFailure']([
                'code' => 'NOT_ENOUGH_DARK_ENERGY',
                'params' => [
                    'cost' => $CHANGE_COST,
                ],
            ]);
        }
        if ($newUsername === $currentUsername) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_USERNAME_SAME_AS_OLD',
            ]);
        }
        if (strlen($newUsername) < $USERNAME_MIN_LENGTH) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_USERNAME_TOO_SHORT',
                'params' => [
                    'minLength' => $USERNAME_MIN_LENGTH,
                ],
            ]);
        }
        if (
            strstr($newUsername, 'http') ||
            strstr($newUsername, 'www.')
        ) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_USERNAME_LINK_FORBIDDEN',
            ]);
        }
        if (!preg_match(REGEXP_USERNAME_ABSOLUTE, $newUsername)) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_USERNAME_INVALID_CHARACTERS',
            ]);
        }

        $fetchExistingUsernameFromDB = doquery(
            "SELECT " .
            "`id` " .
            "FROM {{table}} " .
            "WHERE " .
            "`username` = '{$newUsername}' " .
            "LIMIT 1 ".
            ";",
            'users',
            true
        );

        if ($fetchExistingUsernameFromDB) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_USERNAME_ALREADY_IN_USE',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($executor)($params['input']);
}

?>
