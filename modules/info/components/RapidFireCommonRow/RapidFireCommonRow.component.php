<?php

namespace UniEngine\Engine\Modules\Info\Components\RapidFireCommonRow;

/**
 * @param array $props
 * @param number $props['elementId']
 * @param string $props['title']
 * @param string $props['color']
 * @param number $props['value']
 */
function render($props) {
    global $_Lang;

    $elementId = $props['elementId'];

    $templateBodyProps = [
        'Title' => $props['title'],
        'ElementID' => $elementId,
        'ElementName' => $_Lang['tech'][$elementId],
        'Color' => $props['color'],
        'Count' => prettyNumber($props['value'])
    ];

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $componentHTML = parsetemplate(
        $localTemplateLoader('body'),
        $templateBodyProps
    );

    return [
        'componentHTML' => $componentHTML,
    ];
}

?>
