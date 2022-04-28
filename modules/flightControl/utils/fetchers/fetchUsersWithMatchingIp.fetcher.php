<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Fetchers;

/**
 * @param array $props
 * @param array $props['requiredIpIds']
 * @param array $props['excludedUserIds']
 */
function fetchUsersWithMatchingIp($props) {
    $requiredIpIds = $props['requiredIpIds'];
    $excludedUserIds = $props['excludedUserIds'];

    $excludedUserIdsString = implode(', ', $excludedUserIds);
    $requiredIpIdsString = implode(', ', $requiredIpIds);

    $query = (
        "SELECT " .
        "DISTINCT `User_ID` " .
        "FROM {{table}} " .
        "WHERE " .
        "`User_ID` NOT IN ({$excludedUserIdsString}) AND " .
        "`IP_ID` IN ({$requiredIpIdsString}) AND " .
        "`Count` > `FailCount` " .
        "; -- fetchUsersWithMatchingIp()"
    );

    $results = doquery($query, 'user_enterlog');

    return mapQueryResults($results, function ($entry) {
        return $entry;
    });
}

?>
