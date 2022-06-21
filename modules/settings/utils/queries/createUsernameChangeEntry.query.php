<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param string $params['newUsername']
 * @param arrayRef $params['currentUser']
 */
function createUsernameChangeEntry($params) {
    $newUsername = $params['newUsername'];
    $currentUser = &$params['currentUser'];

    $query = (
        "INSERT INTO {{table}} " .
        "VALUES " .
        "( " .
        "NULL, " .
        "{$currentUser['id']}, " .
        "UNIX_TIMESTAMP(), " .
        "'{$newUsername}', " .
        "'{$currentUser['username']}' " .
        ")" .
        "; -- UniEngine\Engine\Modules\Settings\Utils\Queries\createUsernameChangeEntry"
    );

    doquery($query, 'nick_changelog');
}

?>
