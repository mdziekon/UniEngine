<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Fetchers;

/**
 * @param array $props
 * @param string $props['userId']
 */
function fetchSavedShortcuts($props) {
    $userId = $props['userId'];

    $query = (
        "SELECT " .
        "{{table}}.*, " .
        "IF(`planets`.`id` > 0, `planets`.`name`, '') AS `name`, " .
        "IF(`planets`.`id` > 0, `planets`.`galaxy`, {{table}}.galaxy) AS `galaxy`, " .
        "IF(`planets`.`id` > 0, `planets`.`system`, {{table}}.system) AS `system`, " .
        "IF(`planets`.`id` > 0, `planets`.`planet`, {{table}}.planet) AS `planet`, " .
        "IF(`planets`.`id` > 0, `planets`.`planet_type`, {{table}}.type) AS `planet_type` " .
        "FROM {{table}} " .
        "LEFT JOIN {{prefix}}planets as `planets` " .
        "ON " .
        "`planets`.`id` = {{table}}.`id_planet` " .
        "WHERE " .
        "{{table}}.`id_owner` = {$userId} " .
        "ORDER BY {{table}}.id ASC " .
        ";"
    );
    $result = doquery($query, 'fleet_shortcuts');

    return $result;
}

?>
