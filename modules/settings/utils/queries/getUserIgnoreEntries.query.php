<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param string $params['userId']
 */
function getUserIgnoreEntries($params) {
    $userId = $params['userId'];

    $query = (
        "SELECT " .
        "`ignore`.`IgnoredID`, `user`.`username` " .
        "FROM {{table}} AS `ignore` " .
        "JOIN `{{prefix}}users` AS `user` " .
        "ON `ignore`.`IgnoredID` = `user`.`id` " .
        "WHERE " .
        "`ignore`.`OwnerID` = {$userId} " .
        ";"
    );

    $results = doquery($query, 'ignoresystem');

    $ignoreEntries = mapQueryResults($results, function ($entryRow) {
        return $entryRow;
    });
    $ignoreEntries = object_map($ignoreEntries, function ($entryRow) {
        return [
            $entryRow['username'],
            $entryRow['IgnoredID'],
        ];
    });

    return $ignoreEntries;
}

?>
