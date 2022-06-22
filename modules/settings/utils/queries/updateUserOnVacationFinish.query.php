<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param number $params['userId']
 */
function updateUserOnVacationFinish($params) {
    $userId = $params['userId'];

    $query = (
        "UPDATE {{table}}  " .
        "SET " .
        "`is_onvacation` = '0', " .
        "`vacation_starttime` = '0', " .
        "`vacation_endtime` = '0', " .
        "`vacation_leavetime` = IF(`vacation_type` = 2, 0, UNIX_TIMESTAMP()) " .
        "WHERE " .
        "`id` = {$userId} " .
        "LIMIT 1 " .
        "; -- UniEngine\Engine\Modules\Settings\Utils\Queries\updateUserOnVacationFinish"
    );

    doquery($query, 'users');
}

?>
