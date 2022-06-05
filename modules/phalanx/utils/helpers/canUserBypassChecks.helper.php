<?php

namespace UniEngine\Engine\Modules\Phalanx\Utils\Helpers;

/**
 * @param array $params
 * @param arrayRef $params['user']
 */
function canUserBypassChecks($params) {
    return CheckAuth('supportadmin', AUTHCHECK_NORMAL, $params['user']);
}

?>
