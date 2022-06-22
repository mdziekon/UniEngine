<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param number $params['userId']
 */
function updateUserPlanetsOnVacationFinish($params) {
    $userId = $params['userId'];

    $query = (
        "UPDATE {{table}}  " .
        "SET " .
        "`last_update` = UNIX_TIMESTAMP() " .
        "WHERE " .
        "`id_owner` = {$userId} " .
        "LIMIT 1 " .
        "; -- UniEngine\Engine\Modules\Settings\Utils\Queries\updateUserPlanetsOnVacationFinish"
    );

    doquery($query, 'planets');
}

?>
