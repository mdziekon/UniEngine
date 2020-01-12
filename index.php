<?php

function getReferralID() {
    global $_GET;

    $referralIDKey = 'r';

    if (empty($_GET[$referralIDKey])) {
        return null;
    }

    return intval($_GET[$referralIDKey]);
}

function getNavigationRedirectHeader($pageName) {
    $_EnginePath = './';
    include_once($_EnginePath . 'includes/helpers/common/navigation.functions.php');

    $pageURL = \UniEngine\Engine\Includes\Helpers\Common\Navigation\getPageURL($pageName, []);

    return "Location: {$pageURL}";
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
    include_once($_EnginePath . 'includes/constants.php');

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
        header(getNavigationRedirectHeader("login"));

        return;
    }

    $referralID = getReferralID();

    onValidReferralDataProvided($referralID);

    header(getNavigationRedirectHeader("registration"));
}

renderPage();

?>
