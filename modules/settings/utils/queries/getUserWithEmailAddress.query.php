<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param string $params['emailAddress']
 */
function getUserWithEmailAddress($params) {
    $emailAddress = $params['emailAddress'];

    $query = (
        "SELECT `id` " .
        "FROM {{table}} " .
        "WHERE `email` = '{$emailAddress}' " .
        "LIMIT 1 " .
        ";"
    );

    $result = doquery($query, 'users', true);

    return $result;
}

?>
