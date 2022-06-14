<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Queries;

/**
 * @param array $params
 * @param string $params['entryOwnerId']
 * @param string $params['ignoredUserId']
 */
function createUserIgnoreEntry($params) {
    $entryOwnerId = $params['entryOwnerId'];
    $ignoredUserId = $params['ignoredUserId'];

    $query = (
        "INSERT INTO {{table}} " .
        "(`OwnerID`, `IgnoredID`)" .
        "VALUES " .
        "({$entryOwnerId}, {$ignoredUserId})" .
        "; -- UniEngine\Engine\Modules\Settings\Utils\Queries\createUserIgnoreEntry"
    );

    doquery($query, 'ignoresystem');
}

?>
