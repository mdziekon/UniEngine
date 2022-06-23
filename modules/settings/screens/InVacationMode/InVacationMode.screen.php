<?php

namespace UniEngine\Engine\Modules\Settings\Screens\InVacationMode;

use UniEngine\Engine\Modules\Settings\Screens\InVacationMode;

/**
 * @param array $params
 * @param arrayRef $params['input']
 * @param arrayRef $params['user']
 * @param number $params['currentTimestamp']
 */
function render($props) {
    global $_Lang;

    $input = &$props['input'];
    $user = &$props['user'];
    $currentTimestamp = $props['currentTimestamp'];

    includeLang('common_vacationmode');

    $screenTitle = $_Lang['VacationMode_Title'];

    $inputHandlingResult = InVacationMode\Utils\handleScreenInput([
        'input' => &$input,
        'userId' => $user['id'],
        'currentTimestamp' => $currentTimestamp,
    ]);

    if ($inputHandlingResult) {
        if (!$inputHandlingResult['isSuccess']) {
            $errorMessage = [
                'CANNOT_LEAVE_YET' => $_Lang['Vacation_CantGoOut'],
            ];
            $errorCode = $inputHandlingResult['error']['code'];

            return message($errorMessage[$errorCode], $screenTitle, 'settings.php', 3);
        }

        $successMessage = [
            'LEFT_VACATION_MODE' => $_Lang['Vacation_GoOut'],
        ];
        $successCode = $inputHandlingResult['payload']['code'];

        return message($successMessage[$successCode], $screenTitle, 'overview.php', 3);
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    if (canTakeVacationOffAnytime()) {
        $_Lang['Parse_Vacation_EndTime'] = $_Lang['VacationMode_EndTime_Anytime'];
    } else {
        $MinimalVacationTime = getUserMinimalVacationTime($user);
        $MinimalVacationTimeColor = (
            $MinimalVacationTime <= $currentTimestamp ?
            'lime' :
            'orange'
        );

        $_Lang['Parse_Vacation_EndTime'] = sprintf(
            $_Lang['VacationMode_EndTime_DefinedAs'],
            $MinimalVacationTimeColor,
            prettyDate('d m Y, H:i:s', $MinimalVacationTime, 1)
        );
    }

    $screenHTML = parsetemplate($tplBodyCache['body'], $_Lang);

    display($screenHTML, $screenTitle, false);
}

?>
