<?php

namespace UniEngine\Engine\Modules\Session\Utils\RateLimiter;

function updateLoginRateLimiterEntry($params) {
    $ipHash = $params['ipHash'];

    $lockTime = LOGINPROTECTION_LOCKTIME;
    $maxAttempts = LOGINPROTECTION_MAXATTEMPTS;

    $query = (
        "INSERT INTO {{table}} " .
        "(`IP`, `Date`, `FailCount`) " .
        "VALUES " .
        "('{$ipHash}', UNIX_TIMESTAMP(), 1) " .
        "ON DUPLICATE KEY UPDATE " .
        "`FailCount` = IF( " .
        "   `Date` < (UNIX_TIMESTAMP() - {$lockTime}), " .
        "   1, " .
        "   IF( " .
        "       `FailCount` < {$maxAttempts}, " .
        "       `FailCount` + 1, " .
        "       `FailCount` " .
        "   ) " .
        "), " .
        "`Date` = IF( " .
        "   `FailCount` < {$maxAttempts} OR `Date` < (UNIX_TIMESTAMP() - {$lockTime}), " .
        "   UNIX_TIMESTAMP(), " .
        "   `Date` " .
        ") " .
        ";"
    );

    doquery($query, 'login_protection');
}

function _fetchLoginRateLimitEntry($params) {
    $ipHash = $params['ipHash'];

    $lockTime = LOGINPROTECTION_LOCKTIME;

    $query = (
        "SELECT " .
        "`FailCount` " .
        "FROM {{table}} " .
        "WHERE " .
        "`IP` = '{$ipHash}' AND " .
        "`Date` >= (UNIX_TIMESTAMP() - {$lockTime}) " .
        "LIMIT 1 " .
        ";"
    );

    return doquery($query, 'login_protection', true);
}

function verifyLoginRateLimit($params) {
    $ipHash = $params['ipHash'];

    $maxAttempts = LOGINPROTECTION_MAXATTEMPTS;

    $rateLimitEntry = _fetchLoginRateLimitEntry([
        'ipHash' => $ipHash,
    ]);

    $isIpRateLimited = ($rateLimitEntry['FailCount'] >= $maxAttempts);

    return [
        'isIpRateLimited' => $isIpRateLimited,
    ];
}

?>
