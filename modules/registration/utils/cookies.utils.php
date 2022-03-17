<?php

namespace UniEngine\Engine\Modules\Registration\Utils\Cookies;

/**
 * @return float | null Sanitized user Id or `null`
 */
function getStoredReferrerId() {
    if (empty($_COOKIE[REFERING_COOKIENAME])) {
        return null;
    }

    $referrerId = round($_COOKIE[REFERING_COOKIENAME]);

    if ($referrerId <= 0) {
        return null;
    }

    return $referrerId;
}

?>
