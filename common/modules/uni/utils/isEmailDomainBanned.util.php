<?php

namespace UniEngine\Engine\Common\Modules\Uni\Utils;

function isEmailDomainBanned($emailAddress) {
    global $_GameConfig;

    $bannedDomains = $_GameConfig['BannedMailDomains'];
    $bannedDomains = str_replace('.', '\.', $bannedDomains);

    if (empty($bannedDomains)) {
        return false;
    }

    return preg_match('#('.$bannedDomains.')+#si', $emailAddress) === 1;
}

?>
