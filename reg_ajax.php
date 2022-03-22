<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');
include_once($_EnginePath . 'modules/session/_includes.php');
include_once($_EnginePath . 'modules/registration/_includes.php');

use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Modules\Session;
use UniEngine\Engine\Modules\Registration;

function handleRegistration() {
    global $_EnginePath, $_Lang, $_GameConfig;

    header('access-control-allow-origin: *');

    if (!isset($_GET['register'])) {
        header('Location: index.php');
        die('regCallback({});');

        return;
    }

    includeLang('reg_ajax');
    $Now = time();

    $JSONResponse = [
        'Errors' => [],
        'BadFields' => [],
    ];

    $normalizedInput = Registration\Input\normalizeUserInput($_GET);
    $userSessionIP = Users\Session\getCurrentIP();

    $validationResults = Registration\Validators\validateInputs(
        $normalizedInput,
        [
            'userSessionIp' => $userSessionIP
        ]
    );

    foreach ($validationResults as $fieldName => $fieldValidationResult) {
        if ($fieldValidationResult['isSuccess']) {
            continue;
        }

        switch ($fieldValidationResult['error']['code']) {
            case 'USERNAME_TOO_SHORT':
                $JSONResponse['Errors'][] = 1;
                $JSONResponse['BadFields'][] = 'username';
                break;
            case 'USERNAME_TOO_LONG':
                $JSONResponse['Errors'][] = 2;
                $JSONResponse['BadFields'][] = 'username';
                break;
            case 'USERNAME_INVALID':
                $JSONResponse['Errors'][] = 3;
                $JSONResponse['BadFields'][] = 'username';
                break;
            case 'PASSWORD_TOO_SHORT':
                $JSONResponse['Errors'][] = 4;
                $JSONResponse['BadFields'][] = 'password';
                break;
            case 'EMAIL_EMPTY':
                $JSONResponse['Errors'][] = 5;
                $JSONResponse['BadFields'][] = 'email';
                break;
            case 'EMAIL_HAS_ILLEGAL_CHARACTERS':
                $JSONResponse['Errors'][] = 6;
                $JSONResponse['BadFields'][] = 'email';
                break;
            case 'EMAIL_INVALID':
                $JSONResponse['Errors'][] = 7;
                $JSONResponse['BadFields'][] = 'email';
                break;
            case 'EMAIL_ON_BANNED_DOMAIN':
                $JSONResponse['Errors'][] = 8;
                $JSONResponse['BadFields'][] = 'email';
                break;
            case 'GALAXY_NO_TOO_LOW':
                $JSONResponse['Errors'][] = 13;
                $JSONResponse['BadFields'][] = 'galaxy';
                break;
            case 'GALAXY_NO_TOO_HIGH':
                $JSONResponse['Errors'][] = 14;
                $JSONResponse['BadFields'][] = 'galaxy';
                break;
            case 'LANG_CODE_EMPTY':
                $JSONResponse['Errors'][] = 16;
                break;
            case 'RULES_NOT_ACCEPTED':
                $JSONResponse['Errors'][] = 9;
                break;
            case 'RECAPTCHA_VALIDATION_FAILED':
                $JSONResponse['Errors'][] = 10;
                break;
        }
    }

    if (
        $validationResults['email']['isSuccess'] === true &&
        $validationResults['username']['isSuccess'] === true
    ) {
        $takenParamsValidationResult = Registration\Validators\validateTakenParams([
            'username' => $normalizedInput['username'],
            'email' => $normalizedInput['email']['escaped'],
        ]);

        if ($takenParamsValidationResult['isUsernameTaken']) {
            $JSONResponse['Errors'][] = 11;
            $JSONResponse['BadFields'][] = 'username';
        }
        if ($takenParamsValidationResult['isEmailTaken']) {
            $JSONResponse['Errors'][] = 12;
            $JSONResponse['BadFields'][] = 'email';
        }
    }

    if (!empty($JSONResponse['Errors'])) {
        die('regCallback('.json_encode($JSONResponse).');');
    }

    unset($JSONResponse['Errors']);

    $newPlanetCoordinates = Registration\Utils\Galaxy\findNewPlanetPosition([
        'preferredGalaxy' => $normalizedInput['galaxyNo']
    ]);

    if ($newPlanetCoordinates === null) {
        $JSONResponse['Errors'] = [];
        $JSONResponse['Errors'][] = 15;
        $JSONResponse['BadFields'][] = 'email';

        die('regCallback('.json_encode($JSONResponse).');');
    }

    $passwordHash = Session\Utils\LocalIdentityV1\hashPassword([
        'password' => $normalizedInput['password'],
    ]);

    $insertNewUserResult = Registration\Utils\Queries\insertNewUser([
        'username' => $normalizedInput['username'],
        'passwordHash' => $passwordHash,
        'langCode' => $normalizedInput['langCode'],
        'email' => $normalizedInput['email']['escaped'],
        'registrationIP' => $userSessionIP,
        'currentTimestamp' => $Now,
    ]);
    $UserID = $insertNewUserResult['userId'];

    // Create a Planet for User
    include($_EnginePath.'includes/functions/CreateOnePlanetRecord.php');

    $PlanetID = CreateOnePlanetRecord(
        $newPlanetCoordinates['galaxy'],
        $newPlanetCoordinates['system'],
        $newPlanetCoordinates['planet'],
        $UserID,
        $_Lang['MotherPlanet'],
        true
    );

    Registration\Utils\Queries\incrementUsersCounterInGameConfig();
    Registration\Utils\Queries\updateAllMailChanges([
        'email' => $normalizedInput['email']['escaped']
    ]);

    $referrerUserId = Registration\Utils\General\getRegistrationReferrerId();

    if ($referrerUserId !== null) {
        $registrationIPs = [
            'r' => trim($userSessionIP),
            'p' => trim(Users\Session\getCurrentOriginatingIP())
        ];

        if (empty($registrationIPs['p'])) {
            unset($registrationIPs['p']);
        }

        $existingMatchingEnterLogIds = Registration\Utils\Queries\findEnterLogIPsWithMatchingIPValue([
            'ips' => $registrationIPs,
        ]);

        Registration\Utils\Queries\insertReferralsTableEntry([
            'referrerUserId' => $referrerUserId,
            'referredUserId' => $UserID,
            'timestamp' => $Now,
            'registrationIPs' => $registrationIPs,
            'existingMatchingEnterLogIds' => $existingMatchingEnterLogIds,
        ]);

        $Message = false;
        $Message['msg_id'] = '038';
        $Message['args'] = array('');
        $Message = json_encode($Message);

        SendSimpleMessage($referrerUserId, 0, $Now, 70, '007', '016', $Message);
    }

    $ActivationCode = md5(mt_rand(0, 99999999999));

    // Update User with new data
    Registration\Utils\Queries\updateUserFinalDetails([
        'userId' => $UserID,
        'motherPlanetId' => $PlanetID,
        'motherPlanetGalaxy' => $newPlanetCoordinates['galaxy'],
        'motherPlanetSystem' => $newPlanetCoordinates['system'],
        'motherPlanetPlanetPos' => $newPlanetCoordinates['planet'],
        'referrerId' => $referrerUserId,
        'activationCode' => (
            REGISTER_REQUIRE_EMAILCONFIRM ?
                $ActivationCode :
                null
        )
    ]);

    // Send a invitation private msg
    $Message = false;
    $Message['msg_id'] = '022';
    $Message['args'] = array('');
    $Message = json_encode($Message);

    SendSimpleMessage($UserID, 0, $Now, 70, '004', '009', $Message);

    if (REGISTER_REQUIRE_EMAILCONFIRM) {
        include($_EnginePath.'includes/functions/SendMail.php');

        $mailContent = Registration\Components\RegistrationConfirmationMail\render([
            'userId' => $UserID,
            'login' => $normalizedInput['username'],
            'password' => $normalizedInput['password'],
            'gameName' => $_GameConfig['game_name'],
            'universe' => $_Lang['RegMail_UniName'],
            'activationCode' => $ActivationCode,
        ])['componentHTML'];

        $mailTitle = parsetemplate(
            $_Lang['mail_title'],
            [
                'gameName' => $_GameConfig['game_name']
            ]
        );

        SendMail($normalizedInput['email']['escaped'], $mailTitle, $mailContent);
    }

    if (isGameStartTimeReached($Now)) {
        $sessionTokenValue = Session\Utils\Cookie\packSessionCookie([
            'userId' => $UserID,
            'username' => $normalizedInput['username'],
            'obscuredPasswordHash' => Session\Utils\Cookie\createCookiePasswordHash([
                'passwordHash' => $passwordHash,
            ]),
            'isRememberMeActive' => 0,
        ]);

        $JSONResponse['Code'] = 1;
        $JSONResponse['Cookie'][] = [
            'Name' => getSessionCookieKey(),
            'Value' => $sessionTokenValue
        ];
        $JSONResponse['Redirect'] = GAMEURL_UNISTRICT.'/overview.php';
    } else {
        $JSONResponse['Code'] = 2;
    }

    die('regCallback('.json_encode($JSONResponse).');');
}

handleRegistration();

?>
