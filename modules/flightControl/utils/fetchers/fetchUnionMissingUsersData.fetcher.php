<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Fetchers;

/**
 * @param array $props
 * @param array $props['userIds']
 */
function fetchUnionMissingUsersData($props) {
    $userIds = $props['userIds'];

    if (empty($userIds)) {
        return [];
    }

    $userIdsString = implode(', ', $userIds);
    $userIdsCount = count($userIds);

    $query = (
        "SELECT " .
        "`id`, `username` " .
        "FROM {{table}} " .
        "WHERE " .
        "`id` IN ({$userIdsString}) " .
        "LIMIT {$userIdsCount} " .
        "; -- fetchUnionMissingUserdata()"
    );

    $results = doquery($query, 'users');

    return mapQueryResults($results, function ($userEntry) {
        return $userEntry;
    });
}

?>
