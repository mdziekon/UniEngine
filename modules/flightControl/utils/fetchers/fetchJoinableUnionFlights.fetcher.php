<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Fetchers;

/**
 * @param array $props
 * @param number $props['userId']
 * @param number $props['targetPlanetId']
 */
function fetchJoinableUnionFlights($props) {
    $userId = $props['userId'];
    $targetPlanetId = $props['targetPlanetId'];

    $query = (
        "SELECT " .
        "* " .
        "FROM {{table}} " .
        "WHERE " .
        "( " .
        "`users` LIKE '%|{$userId}|%' OR " .
        "`owner_id` = {$userId} " .
        ") AND " .
        "`end_target_id` = {$targetPlanetId} AND " .
        "`start_time` > UNIX_TIMESTAMP() " .
        ";"
    );
    $queryResult = doquery($query, 'acs');

    return mapQueryResults($queryResult, function ($entry) {
        return $entry;
    });
}

?>
