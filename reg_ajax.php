<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');
include_once($_EnginePath . 'modules/session/_includes.php');
include_once($_EnginePath . 'modules/registration/_includes.php');

use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Modules\Session;
use UniEngine\Engine\Modules\Registration;

includeLang('reg_ajax');
$Now = time();

if(REGISTER_RECAPTCHA_ENABLE)
{
    require($_EnginePath.'vendor/google/recaptcha/src/autoload.php');
}

header('access-control-allow-origin: *');

if(isset($_GET['register']))
{
    $JSONResponse = null;
    $JSONResponse['Errors'] = array();

    // User is trying to register
    $Username = (isset($_GET['username']) ? trim($_GET['username']) : null);
    $Password = (isset($_GET['password']) ? trim($_GET['password']) : null);
    $Email = (isset($_GET['email']) ? trim($_GET['email']) : null);
    $CheckEmail = $Email;
    $Email = getDBLink()->escape_string($Email);
    $Rules = (isset($_GET['rules']) ? $_GET['rules'] : null);
    $GalaxyNo = (isset($_GET['galaxy']) ? intval($_GET['galaxy']) : null);
    $LangCode = (
        (
            isset($_GET['lang']) &&
            in_array($_GET['lang'], UNIENGINE_LANGS_AVAILABLE)
        ) ?
        $_GET['lang'] :
        null
    );
    $userSessionIP = Users\Session\getCurrentIP();

    // Check if Username is correct
    $UsernameGood = false;
    if(strlen($Username) < 4)
    {
        // Username is too short
        $JSONResponse['Errors'][] = 1;
        $JSONResponse['BadFields'][] = 'username';
    }
    else if(strlen($Username) > 64)
    {
        // Username is too long
        $JSONResponse['Errors'][] = 2;
        $JSONResponse['BadFields'][] = 'username';
    }
    else if(!preg_match(REGEXP_USERNAME_ABSOLUTE, $Username))
    {
        // Username has illegal signs
        $JSONResponse['Errors'][] = 3;
        $JSONResponse['BadFields'][] = 'username';
    }
    else
    {
        $UsernameGood = true;
    }

    // Check if Password is correct
    if(strlen($Password) < 4)
    {
        // Password is too short
        $JSONResponse['Errors'][] = 4;
        $JSONResponse['BadFields'][] = 'password';
    }

    // Check if EMail is correct
    $EmailGood = false;
    $BannedDomains = str_replace('.', '\.', $_GameConfig['BannedMailDomains']);
    if(empty($Email))
    {
        // EMail is empty
        $JSONResponse['Errors'][] = 5;
        $JSONResponse['BadFields'][] = 'email';
    }
    else if($Email != $CheckEmail)
    {
        // EMail has illegal signs
        $JSONResponse['Errors'][] = 6;
        $JSONResponse['BadFields'][] = 'email';
    }
    else if(!is_email($Email))
    {
        // EMail is incorrect
        $JSONResponse['Errors'][] = 7;
        $JSONResponse['BadFields'][] = 'email';
    }
    else if(!empty($BannedDomains) && preg_match('#('.$BannedDomains.')+#si', $Email))
    {
        // EMail is on banned domains list
        $JSONResponse['Errors'][] = 8;
        $JSONResponse['BadFields'][] = 'email';
    }
    else
    {
        $EmailGood = true;
    }

    // PreCheck Galaxy
    if($GalaxyNo < 1)
    {
        // Galaxy not given
        $JSONResponse['Errors'][] = 13;
        $JSONResponse['BadFields'][] = 'galaxy';
    }
    else if($GalaxyNo > MAX_GALAXY_IN_WORLD)
    {
        // GalaxyNo is too high
        $JSONResponse['Errors'][] = 14;
        $JSONResponse['BadFields'][] = 'galaxy';
    }

    // Check if valid language has been selected
    if(empty($LangCode))
    {
        // Invalid language selected
        $JSONResponse['Errors'][] = 16;
    }

    // Check if Rules has been accepted
    if($Rules != 'on')
    {
        // Rules are not accepted
        $JSONResponse['Errors'][] = 9;
    }

    if(REGISTER_RECAPTCHA_ENABLE)
    {
        $CaptchaResponse = null;
        $RecaptchaServerIdentification = $_SERVER['SERVER_NAME'];

        if (
            defined("REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME") &&
            REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME
        ) {
            $RecaptchaServerIdentification = $_SERVER['SERVER_ADDR'];
        }

        if (isset($_GET['captcha_response'])) {
            $CaptchaResponse = $_GET['captcha_response'];
        }

        $recaptcha = new \ReCaptcha\ReCaptcha(REGISTER_RECAPTCHA_PRIVATEKEY);

        $recaptchaResponse = $recaptcha
            ->setExpectedHostname($RecaptchaServerIdentification)
            ->verify($CaptchaResponse, $userSessionIP);

        if (!($recaptchaResponse->isSuccess())) {
            // ReCaptcha validation failed
            $JSONResponse['Errors'][] = 10;
        }
    }

    if($EmailGood === true AND $UsernameGood === true)
    {
        $Query_CheckExistence = '';
        $Query_CheckExistence .= "SELECT `username`, `email` FROM {{table}} ";
        $Query_CheckExistence .= "WHERE `username` = '{$Username}' OR `email` = '{$Email}' LIMIT 2;";

        $Result_CheckExistence = doquery($Query_CheckExistence, 'users');

        if($Result_CheckExistence->num_rows > 0)
        {
            while($FetchData = $Result_CheckExistence->fetch_assoc())
            {
                if(strtolower($FetchData['username']) == strtolower($Username))
                {
                    // Username is used
                    $JSONResponse['Errors'][] = 11;
                    $JSONResponse['BadFields'][] = 'username';
                }
                else
                {
                    // EMail is used
                    $JSONResponse['Errors'][] = 12;
                    $JSONResponse['BadFields'][] = 'email';
                }
            }
        }
    }

    if(empty($JSONResponse['Errors']))
    {
        unset($JSONResponse['Errors']);

        // Check Galaxy
        $SystemsRange = 25;
        $SystemRandom = mt_rand(1, MAX_SYSTEM_IN_GALAXY);
        if(($SystemRandom + $SystemsRange) >= MAX_SYSTEM_IN_GALAXY)
        {
            $System_Lower = $SystemRandom - $SystemsRange;
        }
        else
        {
            $System_Lower = $SystemRandom;
        }
        $System_Higher = $System_Lower + $SystemsRange;
        $Planet_Lower = 4;
        $Planet_Higher = 12;

        // - Step 1: check random range of solar systems
        $PosFound = false;
        $Position_NonFree = [];
        $Position_NonFreeCount = 0;
        $Position_TotalCount = (($System_Higher - $System_Lower) + 1) * (($Planet_Higher - $Planet_Lower) + 1);

        $Query_CheckGalaxy1 = '';
        $Query_CheckGalaxy1 .= "SELECT `system`, `planet` FROM {{table}} ";
        $Query_CheckGalaxy1 .= "WHERE `galaxy` = {$GalaxyNo} AND ";
        $Query_CheckGalaxy1 .= "`system` BETWEEN {$System_Lower} AND {$System_Higher} AND ";
        $Query_CheckGalaxy1 .= "`planet` BETWEEN {$Planet_Lower} AND {$Planet_Higher};";
        $Result_CheckGalaxy1 = doquery($Query_CheckGalaxy1, 'galaxy');
        if($Result_CheckGalaxy1->num_rows > 0)
        {
            while($FetchData = $Result_CheckGalaxy1->fetch_assoc())
            {
                $Position_NonFree["{$FetchData['system']}:{$FetchData['planet']}"] = true;
            }
            $Position_NonFreeCount = count($Position_NonFree);
        }
        if($Position_NonFreeCount < $Position_TotalCount)
        {
            while(!$PosFound)
            {
                $System = mt_rand($System_Lower, $System_Higher);
                $Planet = mt_rand($Planet_Lower, $Planet_Higher);
                if(!isset($Position_NonFree["{$System}:{$Planet}"]))
                {
                    $PosFound = true;
                }
            }
        }
        else
        {
            // - Step 2: check whole galaxy, if space not found earlier
            $Position_NonFree = [];
            $Position_NonFreeCount = 0;
            $Position_TotalCount = MAX_SYSTEM_IN_GALAXY * (($Planet_Higher - $Planet_Lower) + 1);

            $Query_CheckGalaxy2 = '';
            $Query_CheckGalaxy2 .= "SELECT `system`, `planet` FROM {{table}} ";
            $Query_CheckGalaxy2 .= "WHERE `galaxy` = {$GalaxyNo} AND ";
            $Query_CheckGalaxy2 .= "`planet` BETWEEN {$Planet_Lower} AND {$Planet_Higher};";
            $Result_CheckGalaxy2 = doquery($Query_CheckGalaxy2, 'galaxy');
            if($Result_CheckGalaxy2->num_rows > 0)
            {
                while($FetchData = $Result_CheckGalaxy2->fetch_assoc())
                {
                    $Position_NonFree["{$FetchData['system']}:{$FetchData['planet']}"] = true;
                }
                $Position_NonFreeCount = count($Position_NonFree);
            }
            if($Position_NonFreeCount < $Position_TotalCount)
            {
                while(!$PosFound)
                {
                    $System = mt_rand(1, MAX_SYSTEM_IN_GALAXY);
                    $Planet = mt_rand($Planet_Lower, $Planet_Higher);
                    if(!isset($Position_NonFree["{$System}:{$Planet}"]))
                    {
                        $PosFound = true;
                    }
                }
            }
            else
            {
                // - Step 3: check whole galaxy and all slots which has not been checked
                $Position_NonFree = [];
                $Position_NonFreeCount = 0;
                $Planet_PosArray = [];
                for($i = 1; $i < $Planet_Lower; $i += 1)
                {
                    $Planet_PosArray[] = $i;
                }
                for($i = $Planet_Higher; $i < MAX_PLANET_IN_SYSTEM; $i += 1)
                {
                    $Planet_PosArray[] = $i;
                }
                $Position_TotalCount = MAX_SYSTEM_IN_GALAXY * count($Planet_PosArray);

                $Query_CheckGalaxy3 = '';
                $Query_CheckGalaxy3 .= "SELECT `system`, `planet` FROM {{table}} ";
                $Query_CheckGalaxy3 .= "WHERE `galaxy` = {$GalaxyNo} AND ";
                $Query_CheckGalaxy3 .= "`planet` NOT BETWEEN {$Planet_Lower} AND {$Planet_Higher};";
                $Result_CheckGalaxy3 = doquery($Query_CheckGalaxy3, 'galaxy');
                if($Result_CheckGalaxy3->num_rows > 0)
                {
                    while($FetchData = $Result_CheckGalaxy3->fetch_assoc())
                    {
                        $Position_NonFree["{$FetchData['system']}:{$FetchData['planet']}"] = true;
                    }
                    $Position_NonFreeCount = count($Position_NonFree);
                }
                if($Position_NonFreeCount < $Position_TotalCount)
                {
                    while(!$PosFound)
                    {
                        $System = mt_rand(1, MAX_SYSTEM_IN_GALAXY);
                        $Planet = $Planet_PosArray[array_rand($Planet_PosArray)];
                        if(!isset($Position_NonFree["{$System}:{$Planet}"]))
                        {
                            $PosFound = true;
                        }
                    }
                }
            }
        }

        if ($PosFound) {
            $passwordHash = Session\Utils\LocalIdentityV1\hashPassword([
                'password' => $Password,
            ]);

            $insertNewUserResult = Registration\Utils\Queries\insertNewUser([
                'username' => $Username,
                'passwordHash' => $passwordHash,
                'langCode' => $LangCode,
                'email' => $Email,
                'registrationIP' => $userSessionIP,
                'currentTimestamp' => $Now,
            ]);
            $UserID = $insertNewUserResult['userId'];

            // Update all MailChanges
            doquery("UPDATE {{table}} SET `ConfirmType` = 4 WHERE `NewMail` = '{$Email}' AND `ConfirmType` = 0;", 'mailchange');

            // Create a Planet for User
            include($_EnginePath.'includes/functions/CreateOnePlanetRecord.php');
            $Galaxy = $GalaxyNo;
            $PlanetID = CreateOnePlanetRecord($Galaxy, $System, $Planet, $UserID, $_Lang['MotherPlanet'], true);

            Registration\Utils\Queries\incrementUsersCounterInGameConfig();

            $setReferrerId = null;
            $referrerUserId = Registration\Utils\Cookies\getStoredReferrerId();

            if ($referrerUserId !== null) {
                $Query_SelectReferrer = "SELECT `id` FROM {{table}} WHERE `id` = {$referrerUserId} LIMIT 1;";
                $Result_SelectReferrer = doquery($Query_SelectReferrer, 'users', true);
                if ($Result_SelectReferrer['id'] > 0) {
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

                    $setReferrerId = $referrerUserId;
                }
            }

            $ActivationCode = md5(mt_rand(0, 99999999999));

            // Update User with new data
            Registration\Utils\Queries\updateUserFinalDetails([
                'userId' => $UserID,
                'motherPlanetId' => $PlanetID,
                'motherPlanetGalaxy' => $Galaxy,
                'motherPlanetSystem' => $System,
                'motherPlanetPlanetPos' => $Planet,
                'referrerId' => $setReferrerId,
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
                    'login' => $Username,
                    'password' => $Password,
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

                SendMail($Email, $mailTitle, $mailContent);
            }

            if(SERVER_MAINOPEN_TSTAMP <= $Now)
            {
                $sessionTokenValue = Session\Utils\Cookie\packSessionCookie([
                    'userId' => $UserID,
                    'username' => $Username,
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
            }
            else
            {
                $JSONResponse['Code'] = 2;
            }
        }
        else
        {
            $JSONResponse['Errors'][] = 15;
            $JSONResponse['BadFields'][] = 'email';
        }
    }
    die('regCallback('.json_encode($JSONResponse).');');
}
else
{
    header('Location: index.php');
    die('regCallback({});');
}

?>
