<?php

namespace UniEngine\Engine\Includes\Helpers\Users\Session;

function getCurrentIP() {
    return $_SERVER['REMOTE_ADDR'];
}

function getCurrentIPHash() {
    $currentIp = getCurrentIP();

    return md5($currentIp);
}

?>
