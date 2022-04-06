<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Fetchers;

/**
 * @param array $props
 * @param array $props['targetCoords']
 */
function fetchTargetGalaxyDetails ($props) {
    $fetchGalaxyDetailsQuery = (
        "SELECT " .
        "`galaxy_id`, `metal`, `crystal` " .
        "FROM {{table}} " .
        "WHERE " .
        "`galaxy` = {$props['targetCoords']['galaxy']} AND " .
        "`system` = {$props['targetCoords']['system']} AND " .
        "`planet` = {$props['targetCoords']['planet']} " .
        "LIMIT 1 " .
        ";"
    );
    $galaxyDetails = doquery($fetchGalaxyDetailsQuery, 'galaxy', true);

    return $galaxyDetails;
}

?>
