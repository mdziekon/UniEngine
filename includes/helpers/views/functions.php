<?php

//  $params (Object)
//      - text (String)
//      - href (String)
//      - query (Object | undefined)
//      - attrs (Object | undefined)
//
function buildLinkHTML($params) {
    $attrs = [];
    $queryParams = [];

    if (empty($params['query'])) {
        $params['query'] = [];
    }
    if (empty($params['attrs'])) {
        $params['attrs'] = [];
    }

    foreach ($params['query'] as $paramKey => $paramValue) {
        $queryParams[] = "{$paramKey}={$paramValue}";
    }

    $href = $params['href'];
    $queryParams = implode('&', $queryParams);

    if ($queryParams) {
        $href .= "?{$queryParams}";
    }

    $params['attrs']['href'] = $href;

    foreach ($params['attrs'] as $attrKey => $attrValue) {
        if ($attrValue === null) {
            continue;
        }

        $attrs[] = "{$attrKey}=\"{$attrValue}\"";
    }

    $attrs = implode(' ', $attrs);

    return ("<a {$attrs}>{$params['text']}</a>");
}

?>
