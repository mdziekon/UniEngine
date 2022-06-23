<?php

namespace UniEngine\Engine\Modules\Settings\Screens\UsernameChange;

use UniEngine\Engine\Modules\Settings;
use UniEngine\Engine\Modules\Settings\Screens\UsernameChange;

/**
 * @param array $params
 * @param arrayRef $params['input']
 * @param arrayRef $params['user']
 */
function render($props) {
    global $_Lang, $_SkinPath;

    $input = &$props['input'];
    $user = &$props['user'];

    $screenTitle = $_Lang['NickChange_Title'];

    $inputHandlingResult = UsernameChange\Utils\handleScreenInput([
        'input' => &$input,
        'user' => &$user,
    ]);

    if ($inputHandlingResult) {
        if (!$inputHandlingResult['isSuccess']) {
            $errorMessage = Settings\Utils\ErrorMappers\mapValidateUsernameChangeErrorToReadableMessage(
                $inputHandlingResult['error']
            );

            return message($errorMessage, $screenTitle, 'settings.php?mode=nickchange');
        }

        return message($_Lang['NewNick_saved'], $screenTitle, 'login.php');
    }

    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $userDarkEnergy = $user['darkEnergy'];

    $_Lang['skinpath'] = $_SkinPath;
    $_Lang['DarkEnergy_Counter'] = $userDarkEnergy;
    if ($userDarkEnergy >= 15) {
        $_Lang['DarkEnergy_Color'] = 'lime';
    } else if ($userDarkEnergy > 0) {
        $_Lang['DarkEnergy_Color'] = 'orange';
    } else {
        $_Lang['DarkEnergy_Color'] = 'red';
    }

    $_Lang['NickChange_Info'] = parsetemplate(
        $_Lang['NickChange_Info'],
        [
            'data_changeCost' => Settings\Utils\Helpers\getUsernameChangeCost(),
        ]
    );
    $_Lang['AreYouSure'] = parsetemplate(
        $_Lang['AreYouSure'],
        [
            'data_changeCost' => Settings\Utils\Helpers\getUsernameChangeCost(),
        ]
    );

    $screenHTML = parsetemplate($tplBodyCache['body'], $_Lang);

    display($screenHTML, $screenTitle, false);
}

?>
