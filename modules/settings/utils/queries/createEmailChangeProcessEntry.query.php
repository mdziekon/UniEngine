<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param arrayRef $params['user']
 * @param string $params['newEmailAddress']
 * @param string $params['changeTokenOldAddress']
 * @param string $params['changeTokenNewAddress']
 * @param number $params['currentTimestamp']
 */
function createEmailChangeProcessEntry($params) {
    $user = &$params['user'];
    $newEmailAddress = $params['newEmailAddress'];
    $changeTokenOldAddress = $params['changeTokenOldAddress'];
    $changeTokenNewAddress = $params['changeTokenNewAddress'];
    $currentTimestamp = $params['currentTimestamp'];

    $query = (
        "INSERT INTO {{table}} " .
        "VALUES " .
        "(" .
        "NULL, " .
        "{$currentTimestamp}, " .
        "{$user['id']}, " .
        "'{$user['email']}', " .
        "'{$newEmailAddress}', " .
        "0, " .
        "0, " .
        "'{$changeTokenOldAddress}', " .
        "'{$changeTokenNewAddress}' " .
        ") " .
        ";"
    );

    doquery($query, 'mailchange');
}

?>
