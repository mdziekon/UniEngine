<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param array $params['changedUserParams']
 * @param array $params['changedUserParamsTypes']
 */
function updateUserSettings($params) {
    $user = &$params['user'];
    $userId = $user['id'];
    $changedUserParams = $params['changedUserParams'];
    $changedUserParamsTypes = $params['changedUserParamsTypes'];

    $updateFields = [];

    foreach ($changedUserParams as $paramName => $newParamValue) {
        $user[$paramName] = $newParamValue;

        if (
            isset($changedUserParamsTypes[$paramName]) &&
            $changedUserParamsTypes[$paramName] == 's'
        ) {
            $newParamValue = "'{$newParamValue}'";
        }

        $updateFields[] = "`{$paramName}` = {$newParamValue}";
    }

    $updateFieldsString = implode(', ', $updateFields);
    $query = (
        "UPDATE {{table}}  " .
        "SET " .
        "{$updateFieldsString} " .
        "WHERE " .
        "`id` = {$userId} " .
        "LIMIT 1 " .
        "; -- UniEngine\Engine\Modules\Settings\Utils\Queries\updateUserSettings"
    );

    doquery($query, 'users');
}

?>
