<?php

namespace UniEngine\Engine\Modules\Registration\Utils\General;

use UniEngine\Engine\Modules\Registration\Utils;

/**
 * @return float | null Sanitized user Id or `null`
 */
function getRegistrationReferrerId() {
    $cookieReferrerId = Utils\Cookies\getStoredReferrerId();

    if ($cookieReferrerId === null) {
        return null;
    }
    if (!(Utils\Queries\checkIfUserExists([ 'userId' => $cookieReferrerId ]))) {
        return null;
    }

    return $cookieReferrerId;
}

?>
