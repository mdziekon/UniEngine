<?php

namespace UniEngine\Engine\Includes\Helpers\Users\Session;

function getCurrentIP() {
    return $_SERVER['REMOTE_ADDR'];
}

function getCurrentOriginatingIP() {
    return preg_replace(
        '#[^a-zA-Z0-9\.\,\:\ ]{1,}#si',
        '',
        $_SERVER['HTTP_X_FORWARDED_FOR']
    );
}

function getCurrentIPHash() {
    $currentIp = getCurrentIP();

    return md5($currentIp);
}

?>
