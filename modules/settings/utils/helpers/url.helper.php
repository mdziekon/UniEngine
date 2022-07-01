<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Helpers;

/**
 * @param string $url
 */
function hasHttpProtocol($url) {
    return (
        strpos($url, 'http://') === 0 ||
        strpos($url, 'https://') === 0
    );
}

/**
 * @param string $url
 */
function hasWWWPart($url) {
    return (strpos($url, 'www.') === 0);
}

/**
 * @param string $url
 */
function hasProtoSeparator($url) {
    return (strpos($url, '://') !== false);
}

/**
 * @param string $url
 */
function isExternalUrl($url) {
    return hasProtoSeparator($url);
}

/**
 * @param string $url
 */
function isValidExternalUrl($url) {
    return (
        hasProtoSeparator($url) &&
        (
            hasHttpProtocol($url) ||
            hasWWWPart($url)
        )
    );
}

function completeWWWUrl($url) {
    $defaultProto = 'https';

    return str_replace('www.', "{$defaultProto}://www.", $url);
}

?>
