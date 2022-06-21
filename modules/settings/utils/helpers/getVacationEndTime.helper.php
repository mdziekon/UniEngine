<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Helpers;

/**
 * @param array $params
 * @param number $params['currentTimestamp']
 */
function getVacationEndTime($params) {
    return ($params['currentTimestamp'] + (MAXVACATIONS_REG * TIME_DAY));
}

?>
