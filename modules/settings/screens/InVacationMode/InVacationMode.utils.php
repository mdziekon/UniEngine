<?php

namespace UniEngine\Engine\Modules\Settings\Screens\InVacationMode\Utils;

use UniEngine\Engine\Modules\Settings;

/**
 * @param array $params
 * @param arrayRef $params['input']
 * @param number $params['userId']
 * @param number $params['currentTimestamp']
 */
function handleScreenInput($params) {
    global $_Planet, $UserDev_Log;

    $input = &$params['input'];
    $userId = $params['userId'];
    $currentTimestamp = $params['currentTimestamp'];

    if (
        !isset($input['exit_modus']) ||
        !$input['exit_modus'] == 'on'
    ) {
        return;
    }

    if (!canTakeVacationOff($currentTimestamp)) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'CANNOT_LEAVE_YET',
            ],
        ];
    }

    Settings\Utils\Queries\updateUserOnVacationFinish([ 'userId' => $userId ]);
    Settings\Utils\Queries\updateUserPlanetsOnVacationFinish([ 'userId' => $userId ]);

    $_Planet['last_update'] = $currentTimestamp;

    $UserDev_Log[] = Settings\Utils\Factories\createVacationFinishDevLogEntry([
        'currentTimestamp' => $currentTimestamp,
    ]);

    return [
        'isSuccess' => true,
        'payload' => [
            'code' => 'LEFT_VACATION_MODE',
        ],
    ];
}

?>
