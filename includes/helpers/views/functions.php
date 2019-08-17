<?php

//  $params (Object)
//      - tagName (String)
//      - contentHTML (String | undefined)
//      - attrs (Object | undefined)
//
function buildDOMElementHTML($params) {
    if (!isset($params['contentHTML'])) {
        $params['contentHTML'] = '';
    }
    if (empty($params['attrs'])) {
        $params['attrs'] = [];
    }

    $elementName = $params['tagName'];
    $contentHTML = $params['contentHTML'];
    $attrs = [];

    foreach ($params['attrs'] as $attrKey => $attrValue) {
        if ($attrValue === null) {
            continue;
        }

        $attrs[] = "{$attrKey}=\"{$attrValue}\"";
    }

    $attrs = implode(' ', $attrs);

    if (empty($contentHTML)) {
        return ("<{$elementName} {$attrs}/>");
    }

    return ("<{$elementName} {$attrs}>{$contentHTML}</{$elementName}>");
}

//  $params (Object)
//      - text (String)
//      - href (String)
//      - query (Object | undefined)
//      - attrs (Object | undefined)
//
function buildLinkHTML($params) {
    $queryParams = [];

    if (empty($params['query'])) {
        $params['query'] = [];
    }
    if (empty($params['attrs'])) {
        $params['attrs'] = [];
    }

    $params['attrs']['href'] = buildHref([
        'path' => $params['href'],
        'query' => $params['query']
    ]);

    return buildDOMElementHTML([
        'tagName' => 'a',
        'contentHTML' => $params['text'],
        'attrs' => $params['attrs']
    ]);
}

//  $params (Object)
//      - path (String)
//      - query (Object | undefined)
//
function buildHref($params) {
    $queryParams = [];

    if (empty($params['query'])) {
        $params['query'] = [];
    }

    foreach ($params['query'] as $paramKey => $paramValue) {
        if ($paramValue === null) {
            continue;
        }

        $queryParams[] = "{$paramKey}={$paramValue}";
    }

    $href = $params['path'];
    $queryParams = implode('&', $queryParams);

    if ($queryParams) {
        $href .= "?{$queryParams}";
    }

    return $href;
}

?>
