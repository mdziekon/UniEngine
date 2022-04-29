<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Fetchers;

/**
 * @param array $props
 * @param string $props['declarationId']
 */
function fetchMultiDeclaration($props) {
    $declarationId = $props['declarationId'];

    $query = (
        "SELECT " .
        "`id`, `status` " .
        "FROM {{table}} " .
        "WHERE " .
        "`id` = {$declarationId} " .
        "LIMIT 1 " .
        "; -- fetchMultiDeclaration()"
    );

    $result = doquery($query, 'declarations', true);

    return $result;
}

?>
