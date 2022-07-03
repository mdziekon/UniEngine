<?php

namespace UniEngine\Engine\Modules\Overview\Screens\FirstLogin\Utils\Helpers;

/**
 * @param array $params
 * @param number $params['referredById']
 */
function getReferrerTasksData($props) {
    global $GlobalParsedTasks;

    $referredById = $props['referredById'];

    if (!empty($GlobalParsedTasks[$referredById]['tasks_done_parsed'])) {
        $referringUserWithTasksData = $GlobalParsedTasks[$referredById];
        $referringUserWithTasksData['id'] = $referredById;

        return $referringUserWithTasksData;
    }

    $fetchUserTasksDoneQuery = (
        "SELECT " .
        "`tasks_done` " .
        "FROM {{table}} " .
        "WHERE " .
        "`id` = {$referredById} " .
        "LIMIT 1 " .
        ";"
    );
    $fetchUserTasksDoneResult = doquery($fetchUserTasksDoneQuery, 'users', true);

    if (!$fetchUserTasksDoneQuery) {
        return null;
    }

    // Prepare data & populate global cache
    Tasks_CheckUservar($fetchUserTasksDoneResult);
    $GlobalParsedTasks[$referredById] = $fetchUserTasksDoneResult;

    $referringUserWithTasksData = $GlobalParsedTasks[$referredById];
    $referringUserWithTasksData['id'] = $referredById;

    return $referringUserWithTasksData;
}

?>
