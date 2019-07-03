<?php

//  $params (Object)
//      - unixTimestamp (Number)
//      - user (Object)
//
function applyTaskUpdates ($updates, $params) {
    $planetUpdateResult = _applyPlanetTaskUpdates($updates, $params);
    $freePremiumUpdateResult = _applyFreePremiumTaskUpdates($updates, $params);
    $userUpdateResult = _applyUserTaskUpdates($updates, $params);

    return [
        'userUpdatedEntries' => array_merge(
            [],
            $planetUpdateResult['userUpdatedEntries'],
            $freePremiumUpdateResult['userUpdatedEntries'],
            $userUpdateResult['userUpdatedEntries']
        ),
        'devlogEntries' => array_merge(
            [],
            $planetUpdateResult['devlogEntries'],
            $freePremiumUpdateResult['devlogEntries'],
            $userUpdateResult['devlogEntries']
        ),
    ];
}

//  $params (Object)
//      - unixTimestamp (Number)
//      - user (Object)
//
function _applyPlanetTaskUpdates ($updates, $params) {
    $userObj = $params['user'];
    $planetID = $userObj['id_planet'];
    $unixTimestamp = $params['unixTimestamp'];

    $result = [
        'userUpdatedEntries' => [],
        'devlogEntries' => []
    ];

    if (empty($updates['planet'])) {
        return $result;
    }

    $queryElements = [];

    foreach ($updates['planet'] as $Key => $Value2Add) {
        $queryElements[] = "`{$Key}` = `{$Key}` + {$Value2Add}";
    }

    doquery(
        (
            "UPDATE {{table}} SET " .
            " " . implode(', ', $queryElements) . " " .
            "WHERE " .
            "`id` = {$planetID} " .
            " LIMIT 1 " .
            ";"
        ),
        'planets'
    );

    if (!empty($updates['devlog'])) {
        $entryElements = [];

        foreach ($updates['devlog'] as $DevLogKey => $DevLogRow) {
            $entryElements[] = "{$DevLogKey},{$DevLogRow}";
        }

        $result['devlogEntries'][] = [
            'PlanetID' => $planetID,
            'Date' => $unixTimestamp,
            'Place' => 30,
            'Code' => '0',
            'ElementID' => '0',
            'AdditionalData' => implode(';', $entryElements)
        ];
    }

    return $result;
}

//  $params (Object)
//      - user (Object)
//
function _applyFreePremiumTaskUpdates ($updates, $params) {
    $userObj = $params['user'];
    $userID = $userObj['id'];

    $result = [
        'userUpdatedEntries' => [],
        'devlogEntries' => []
    ];

    if (empty($updates['free_premium'])) {
        return $result;
    }

    $queryElements = [];

    foreach ($updates['free_premium'] as $ItemID) {
        $queryElements[] = "(NULL, {$userID}, UNIX_TIMESTAMP(), 0, 0, {$ItemID}, 0)";
    }
    doquery(
        (
            "INSERT INTO {{table}} VALUES " .
            " " . implode(', ', $queryElements) . " " .
            ";"
        ),
        'premium_free'
    );

    return $result;
}

//  $params (Object)
//      - user (Object)
//
function _applyUserTaskUpdates ($updates, $params) {
    $userObj = $params['user'];
    $userID = $userObj['id'];
    $tasksDone = $userObj['tasks_done'];

    $result = [
        'userUpdatedEntries' => [],
        'devlogEntries' => []
    ];

    $userQueryElements = [];
    $userQueryElements[] = "`tasks_done` = '{$tasksDone}'";

    if (!empty($updates['user'])) {
        foreach ($updates['user'] as $Key => $Value2Add) {
            $userQueryElements[] = "`{$Key}` = `{$Key}` + {$Value2Add}";

            $result['userUpdatedEntries'][] = [
                'key' => $Key,
                'value' => $Value2Add
            ];
        }
    }

    doquery(
        (
            "UPDATE {{table}} SET " .
            " " . implode(',', $userQueryElements) . " " .
            "WHERE " .
            "`id` = {$userID} " .
            ";"
        ),
        'users'
    );

    return $result;
}

?>
