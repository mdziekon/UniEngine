<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param string $params['userId']
 * @param number $params['historyEntriesLimit']
 */
function getAccountLoginHistory($params) {
    $userId = $params['userId'];
    $historyEntriesLimit = $params['historyEntriesLimit'];

    $query = (
        "SELECT " .
        "`Log`.*, `IPTable`.`Value` " .
        "FROM {{table}} AS `Log` " .
        "LEFT JOIN `{{prefix}}used_ip_and_ua` AS `IPTable` " .
        "ON `Log`.`IP_ID` = `IPTable`.`ID` " .
        "WHERE " .
        "`Log`.`User_ID` = {$userId} " .
        "ORDER BY `Log`.`LastTime` DESC " .
        "LIMIT {$historyEntriesLimit} " .
        ";"
    );

    $results = doquery($query, 'user_enterlog');

    $historyEntries = mapQueryResults($results, function ($entryRow) {
        return $entryRow;
    });

    return $historyEntries;
}

?>
