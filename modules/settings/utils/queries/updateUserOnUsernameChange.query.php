<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param string $params['newUsername']
 * @param arrayRef $params['currentUser']
 */
function updateUserOnUsernameChange($params) {
    $newUsername = $params['newUsername'];
    $currentUser = &$params['currentUser'];

    $CHANGE_COST = 10;

    $query = (
        "UPDATE {{table}}  " .
        "SET " .
        "`darkEnergy` = `darkEnergy` - {$CHANGE_COST},  " .
        "`username` = '{$newUsername}', " .
        "`old_username` = '{$currentUser['username']}', " .
        "`old_username_expire` = UNIX_TIMESTAMP() + (7 * 24 * 60 * 60) " .
        "WHERE " .
        "`id` = {$currentUser['id']} " .
        "LIMIT 1 " .
        "; -- UniEngine\Engine\Modules\Settings\Utils\Queries\updateUserOnUsernameChange"
    );

    doquery($query, 'users');
}

?>
