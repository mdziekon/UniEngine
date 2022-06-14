<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param string $params['entryOwnerId']
 * @param string[] $params['entriesIds']
 */
function deleteUserIgnoreEntries($params) {
    $entryOwnerId = $params['entryOwnerId'];
    $entriesIds = $params['entriesIds'];

    $entriesIdsString = implode(', ', $entriesIds);
    $entriesIdsCount = count($entriesIds);

    $query = (
        "DELETE FROM {{table}} " .
        "WHERE " .
        "`OwnerID` = {$entryOwnerId} AND  " .
        "`IgnoredID` IN ({$entriesIdsString}) " .
        "LIMIT {$entriesIdsCount} " .
        "; -- UniEngine\Engine\Modules\Settings\Utils\Queries\deleteUserIgnoreEntries"
    );

    doquery($query, 'ignoresystem');
}

?>
