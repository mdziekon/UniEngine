<?php

function getReferralID() {
    global $_GET;

    $referralIDKey = 'r';

    if (empty($_GET[$referralIDKey])) {
        return null;
    }

    return intval($_GET[$referralIDKey]);
}

function hasValidReferralData() {
    $referralID = getReferralID();

    return ($referralID > 0);
}

function hasReferralCookie() {
    global $_COOKIE;

    $referralCookieKey = REFERING_COOKIENAME;

    return (!empty($_COOKIE[$referralCookieKey]));
}

function onValidReferralDataProvided($referralID) {
    global $_COOKIE;

    define('INSIDE', true);

    $_EnginePath = './';
    include($_EnginePath . 'includes/constants.php');

    if (hasReferralCookie()) {
        return;
    }

    $nowTimestamp = time();
    $referralCookieTTLDays = 14;
    $referralCookieTTL = ($referralCookieTTLDays * TIME_DAY);

    setcookie(
        REFERING_COOKIENAME,
        $referralID,
        $nowTimestamp + $referralCookieTTL,
        '',
        GAMEURL_DOMAIN
    );
}

function renderPage() {
    if (!hasValidReferralData()) {
        header('Location: login.php');

        return;
    }

    $referralID = getReferralID();

    onValidReferralDataProvided($referralID);

    header('Location: reg_mainpage.php');
}

renderPage();

?>
