<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Helpers;

/**
 * @param string $url
 */
function hasHttpProtocol($url) {
    return (
        strstr($url, 'http://') !== false ||
        strstr($url, 'https://') !== false
    );
}

/**
 * @param string $url
 */
function hasWWWPart($url) {
    return (strstr($url, 'www.') !== false);
}

/**
 * @param string $url
 */
function isExternalUrl($url) {
    return (
        hasHttpProtocol($url) ||
        hasWWWPart($url)
    );
}

function completeWWWUrl($url) {
    $defaultProto = 'https';

    return str_replace('www.', "{$defaultProto}://www.", $url);
}

?>
