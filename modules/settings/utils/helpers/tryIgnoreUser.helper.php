<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Helpers;

/**
 * @param array $params
 * @param array $params['userToIgnore']
 * @param stringEnum $params['userToIgnore']['selectorType'] ('username' | 'id')
 * @param string $params['userToIgnore']['selectorValue']
 * @param arrayRef $params['currentUser']
 * @param array $params['ignoredUsers']
 */
function tryIgnoreUser($params) {
    $executor = function ($input, $resultHelpers) {
        $currentUser = &$input['currentUser'];
        $userToIgnore = $input['userToIgnore'];
        $ignoredUsers = $input['ignoredUsers'];

        if ($userToIgnore['selectorType'] === 'username') {
            if (strtolower($userToIgnore['selectorValue']) == strtolower($currentUser['username'])) {
                return $resultHelpers['createFailure']([
                    'code' => 'CANT_IGNORE_YOURSELF',
                ]);
            }
            if (!preg_match(REGEXP_USERNAME_ABSOLUTE, $userToIgnore['selectorValue'])) {
                return $resultHelpers['createFailure']([
                    'code' => 'INVALID_USER_SELECTOR',
                ]);
            }
        }
        if ($userToIgnore['selectorType'] === 'id') {
            if ($userToIgnore['selectorValue'] == $currentUser['id']) {
                return $resultHelpers['createFailure']([
                    'code' => 'CANT_IGNORE_YOURSELF',
                ]);
            }
            if ($userToIgnore['selectorValue'] <= 0) {
                return $resultHelpers['createFailure']([
                    'code' => 'INVALID_USER_SELECTOR',
                ]);
            }
        }

        $ignoreUserSelector = (
            $userToIgnore['selectorType'] === 'username' ?
                [
                    'column' => 'username',
                    'value' => "'{$userToIgnore['selectorValue']}'",
                ] :
                [
                    'column' => 'id',
                    'value' => "{$userToIgnore['selectorValue']}",
                ]
        );

        $fetchUserQuery = (
            "SELECT " .
            "`id`, `username`, `authlevel` " .
            "FROM {{table}} " .
            "WHERE " .
            "`{$ignoreUserSelector['column']}` = {$ignoreUserSelector['value']} " .
            "LIMIT 1 " .
            "; -- UniEngine\Engine\Modules\Settings\Utils\Helpers\tryIgnoreUser::fetchUserQuery"
        );

        $fetchUserResult = doquery($fetchUserQuery, 'users', true);

        if (!$fetchUserResult) {
            return $resultHelpers['createFailure']([
                'code' => 'USER_NOT_FOUND',
            ]);
        }
        if (CheckAuth('user', AUTHCHECK_HIGHER, $fetchUserResult)) {
            return $resultHelpers['createFailure']([
                'code' => 'CANT_IGNORE_GAMETEAM_MEMBER',
            ]);
        }

        $ignoreUserId = $fetchUserResult['id'];

        if (!empty($ignoredUsers[$ignoreUserId])) {
            return $resultHelpers['createFailure']([
                'code' => 'USER_ALREADY_IGNORED',
            ]);
        }

        return $resultHelpers['createSuccess']([
            'ignoreUser' => $fetchUserResult,
        ]);
    };

    return createFuncWithResultHelpers($executor)($params);
}

?>
