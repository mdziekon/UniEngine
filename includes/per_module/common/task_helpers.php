<?php

//  Arguments:
//      - $user (&Object)
//
//  Return: Object
//      - completedTasks (Number)
//      - completedTasksLinks (Number)
//      - postTaskDataUpdates (Object)
//
function parseCompletedTasks (&$user) {
    global $_Vars_TasksData;

    $result = [
        'completedTasks' => 0,
        'completedTasksLinks' => [],
        'postTaskDataUpdates' => []
    ];

    if (empty($user['tasks_done_parsed']['locked'])) {
        return $result;
    }

    $completedTasks = 0;
    $completedTasksLinks = [];
    $postTaskDataUpdates = [];

    foreach ($user['tasks_done_parsed']['locked'] as $taskCategoryID => $categoryTasks) {
        $hasSkippedCategory = false;

        if (strstr($taskCategoryID, 's')) {
            $taskCategoryID = str_replace('s', '', $taskCategoryID);
            $hasSkippedCategory = true;
        }

        foreach ($categoryTasks as $taskID) {
            unset($user['tasks_done_parsed']['jobs'][$taskCategoryID][$taskID]);

            if (
                !$hasSkippedCategory ||
                (
                    $hasSkippedCategory &&
                    $_Vars_TasksData[$taskCategoryID]['skip']['tasksrew'] === true
                )
            ) {
                $completedTasksLinks[$taskCategoryID] = 'cat='.$taskCategoryID.'&amp;showtask='.$taskID;
                foreach($_Vars_TasksData[$taskCategoryID]['tasks'][$taskID]['reward'] as $RewardData) {
                    Tasks_ParseRewards($RewardData, $postTaskDataUpdates);
                }
            }
            $completedTasks += 1;
        }

        if (Tasks_IsCatDone($taskCategoryID, $user)) {
            unset($user['tasks_done_parsed']['jobs'][$taskCategoryID]);

            if (
                $hasSkippedCategory ||
                (
                    $hasSkippedCategory &&
                    $_Vars_TasksData[$taskCategoryID]['skip']['catrew'] === true
                )
            ) {
                $completedTasksLinks[$taskCategoryID] = 'mode=log&amp;cat='.$taskCategoryID;
                foreach ($_Vars_TasksData[$taskCategoryID]['reward'] as $RewardData) {
                    Tasks_ParseRewards($RewardData, $postTaskDataUpdates);
                }
            }
        } else {
            if (empty($user['tasks_done_parsed']['jobs'])) {
                unset($user['tasks_done_parsed']['jobs']);
            }
        }
    }

    if (empty($user['tasks_done_parsed']['jobs'])) {
        unset($user['tasks_done_parsed']['jobs']);
    }

    unset($user['tasks_done_parsed']['locked']);
    // TODO: investigate if this should be moved to "postTaskDataUpdates"
    $user['tasks_done'] = json_encode($user['tasks_done_parsed']);

    $result['completedTasks'] = $completedTasks;
    $result['completedTasksLinks'] = $completedTasksLinks;
    $result['postTaskDataUpdates'] = $postTaskDataUpdates;

    return $result;
}

function prepareTasksInfoboxHTML (&$handleTasksResult) {
    $tplData = includeLang('tasks_infobox', true);
    $tpl = gettemplate('tasks_infobox');

    $tplData['Task'] = (
        ($handleTasksResult['completedTasks'] > 1) ?
        $tplData['MoreTasks'] :
        $tplData['OneTask']
    );
    $tplData['CatLinks'] = [];

    foreach ($handleTasksResult['completedTasksLinks'] as $CatID => $LinkData) {
        $tplData['CatLinks'][] = sprintf(
            $tplData['CatLink'],
            $LinkData,
            $tplData['Names'][$CatID]
        );
    }

    $tplData['CatLinks'] = implode(', ', $tplData['CatLinks']);

    return parsetemplate($tpl, $tplData);
}

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
