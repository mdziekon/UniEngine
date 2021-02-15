<?php

namespace UniEngine\Engine\Modules\Session\Utils\LocalIdentityV1;

function hashPassword($params) {
    $password = $params['password'];

    return md5($password);
}

?>
