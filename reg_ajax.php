<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');
include_once($_EnginePath . 'modules/session/_includes.php');
include_once($_EnginePath . 'modules/registration/_includes.php');

use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Modules\Session;
use UniEngine\Engine\Modules\Registration;

function handleRegistration(&$input) {
    global $_EnginePath, $_Lang, $_GameConfig;

    if (!isset($input['register'])) {
        return [
            'params' => [
                'headers' => [
                    'Location: index.php'
                ]
            ],
            'payload' => [],
        ];
    }

    includeLang('reg_ajax');
    $Now = time();

    $normalizedInput = Registration\Input\normalizeUserInput($input);
    $userSessionIP = Users\Session\getCurrentIP();

    $validationResults = Registration\Validators\validateInputs(
        $normalizedInput,
        [
            'userSessionIp' => $userSessionIP
        ]
    );

    $errorsJSONPayload = [
        'Errors' => [],
        'BadFields' => [],
    ];

    foreach ($validationResults as $fieldName => $fieldValidationResult) {
        if ($fieldValidationResult['isSuccess']) {
            continue;
        }

        switch ($fieldValidationResult['error']['code']) {
            case 'USERNAME_TOO_SHORT':
                $errorsJSONPayload['Errors'][] = 1;
                $errorsJSONPayload['BadFields'][] = 'username';
                break;
            case 'USERNAME_TOO_LONG':
                $errorsJSONPayload['Errors'][] = 2;
                $errorsJSONPayload['BadFields'][] = 'username';
                break;
            case 'USERNAME_INVALID':
                $errorsJSONPayload['Errors'][] = 3;
                $errorsJSONPayload['BadFields'][] = 'username';
                break;
            case 'PASSWORD_TOO_SHORT':
                $errorsJSONPayload['Errors'][] = 4;
                $errorsJSONPayload['BadFields'][] = 'password';
                break;
            case 'EMAIL_EMPTY':
                $errorsJSONPayload['Errors'][] = 5;
                $errorsJSONPayload['BadFields'][] = 'email';
                break;
            case 'EMAIL_HAS_ILLEGAL_CHARACTERS':
                $errorsJSONPayload['Errors'][] = 6;
                $errorsJSONPayload['BadFields'][] = 'email';
                break;
            case 'EMAIL_INVALID':
                $errorsJSONPayload['Errors'][] = 7;
                $errorsJSONPayload['BadFields'][] = 'email';
                break;
            case 'EMAIL_ON_BANNED_DOMAIN':
                $errorsJSONPayload['Errors'][] = 8;
                $errorsJSONPayload['BadFields'][] = 'email';
                break;
            case 'GALAXY_NO_TOO_LOW':
                $errorsJSONPayload['Errors'][] = 13;
                $errorsJSONPayload['BadFields'][] = 'galaxy';
                break;
            case 'GALAXY_NO_TOO_HIGH':
                $errorsJSONPayload['Errors'][] = 14;
                $errorsJSONPayload['BadFields'][] = 'galaxy';
                break;
            case 'LANG_CODE_EMPTY':
                $errorsJSONPayload['Errors'][] = 16;
                break;
            case 'RULES_NOT_ACCEPTED':
                $errorsJSONPayload['Errors'][] = 9;
                break;
            case 'RECAPTCHA_VALIDATION_FAILED':
                $errorsJSONPayload['Errors'][] = 10;
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
            $errorsJSONPayload['Errors'][] = 11;
            $errorsJSONPayload['BadFields'][] = 'username';
        }
        if ($takenParamsValidationResult['isEmailTaken']) {
            $errorsJSONPayload['Errors'][] = 12;
            $errorsJSONPayload['BadFields'][] = 'email';
        }
    }

    if (!empty($errorsJSONPayload['Errors'])) {
        return [
            'params' => null,
            'payload' => $errorsJSONPayload,
        ];
    }

    $newPlanetCoordinates = Registration\Utils\Galaxy\findNewPlanetPosition([
        'preferredGalaxy' => $normalizedInput['galaxyNo']
    ]);

    if ($newPlanetCoordinates === null) {
        $errorsJSONPayload['Errors'][] = 15;
        $errorsJSONPayload['BadFields'][] = 'email';

        return [
            'params' => null,
            'payload' => $errorsJSONPayload,
        ];
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

    if (!isGameStartTimeReached($Now)) {
        return [
            'params' => null,
            'payload' => [
                'Code' => 2
            ],
        ];
    }

    $sessionTokenValue = Session\Utils\Cookie\packSessionCookie([
        'userId' => $UserID,
        'username' => $normalizedInput['username'],
        'obscuredPasswordHash' => Session\Utils\Cookie\createCookiePasswordHash([
            'passwordHash' => $passwordHash,
        ]),
        'isRememberMeActive' => 0,
    ]);

    return [
        'params' => null,
        'payload' => [
            'Code' => 1,
            'Cookie' => [
                [
                    'Name' => getSessionCookieKey(),
                    'Value' => $sessionTokenValue
                ]
            ],
            'Redirect' => GAMEURL_UNISTRICT.'/overview.php'
        ],
    ];
}

function sendResponse($payload, $params) {
    header('access-control-allow-origin: *');

    if (!empty($params)) {
        if (!empty($params['headers'])) {
            foreach ($params['headers'] as $header) {
                header($header);
            }
        }
    }

    $jsonContent = json_encode((object) $payload);

    die("regCallback({$jsonContent});");
}

$registrationResult = handleRegistration($_GET);

sendResponse(
    $registrationResult['payload'],
    $registrationResult['params']
);

?>
