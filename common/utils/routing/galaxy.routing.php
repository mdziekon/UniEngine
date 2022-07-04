<?php

namespace UniEngine\Engine\Common\Utils\Routing;

/**
 * @param array $params
 * @param number $params['galaxy']
 * @param number $params['system']
 * @param number $params['planet']
 */
function getGalaxyTargetUrl($params) {
    return buildHref([
        'path' => 'galaxy.php',
        'query' => [
            'mode' => '3',
            'galaxy' => $params['galaxy'],
            'system' => $params['system'],
            'planet' => $params['planet'],
        ],
    ]);
}

?>
