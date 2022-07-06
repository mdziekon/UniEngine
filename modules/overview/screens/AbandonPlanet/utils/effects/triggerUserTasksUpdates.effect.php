<?php

namespace UniEngine\Engine\Modules\Overview\Screens\AbandonPlanet\Utils\Effects;

/**
 * @param array $params
 * @param arrayRef $params['user']
 */
function triggerUserTasksUpdates($props) {
    $user = &$props['user'];

    // Prevent abandoning Planet to make mission faster
    Tasks_TriggerTask(
        $user,
        'COLONIZE_PLANET',
        [
            'mainCheck' => function ($JobArray, $ThisCat, $TaskID, $JobID) use ($user) {
                global $UserTasksUpdate;

                $userId = $user['id'];

                if (!empty($UserTasksUpdate[$userId]['status'][$ThisCat][$TaskID][$JobID])) {
                    $user['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] = $UserTasksUpdate[$userId]['status'][$ThisCat][$TaskID][$JobID];
                }
                if ($user['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] <= 0) {
                    return true;
                }

                $user['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID] -= 1;
                $UserTasksUpdate[$userId]['status'][$ThisCat][$TaskID][$JobID] = $user['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID];

                return true;
            }
        ]
    );
}

?>
