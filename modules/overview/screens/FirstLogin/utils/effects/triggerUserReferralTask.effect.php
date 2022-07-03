<?php

namespace UniEngine\Engine\Modules\Overview\Screens\FirstLogin\Utils\Effects;

/**
 * @param array $params
 * @param arrayRef $params['referringUserWithTasksData']
 */
function triggerUserReferralTask($props) {
    $referringUserWithTasksData = &$props['referringUserWithTasksData'];

    if (empty($referringUserWithTasksData)) {
        return;
    }

    Tasks_TriggerTask(
        $referringUserWithTasksData,
        'NEWUSER_REGISTER',
        [
            'mainCheck' => function ($JobArray, $ThisCat, $TaskID, $JobID) use (&$referringUserWithTasksData) {
                $taskStatusCheckResult = Tasks_TriggerTask_MainCheck_Progressive($JobArray, $ThisCat, $TaskID, $JobID, $referringUserWithTasksData, 1);

                $referringUserWithTasksData['TaskData'][] = [
                    'TaskID' => $TaskID,
                    'TaskStatus' => $referringUserWithTasksData['tasks_done_parsed']['status'][$ThisCat][$TaskID][$JobID],
                    'TaskLimit' => $JobArray[$JobArray['statusField']]
                ];

                return $taskStatusCheckResult;
            }
        ]
    );
}

?>
