<?php

namespace UniEngine\Engine\Common\Components\GalaxyPlanetLink;

use UniEngine\Engine\Common\Utils\Routing;

/**
 * @param array $props
 * @param array $props['coords']
 * @param number $props['coords']['galaxy']
 * @param number $props['coords']['system']
 * @param number $props['coords']['planet']
 */
function render($props) {
    $linkText = "[{$props['coords']['galaxy']}:{$props['coords']['system']}:{$props['coords']['planet']}]";

    return buildDOMElementHTML([
        'tagName' => 'a',
        'contentHTML' => $linkText,
        'attrs' => [
            'href' => Routing\getGalaxyTargetUrl($props['coords']),
        ],
    ]);
}

?>
