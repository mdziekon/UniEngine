<?php

namespace UniEngine\Engine\Modules\Registration\Validators;

//  Arguments
//      - $params (Object)
//          - responseValue (String | null)
//          - currentSessionIp (String)
//
function validateReCaptcha($params) {
    global $_EnginePath;

    require("{$_EnginePath}vendor/google/recaptcha/src/autoload.php");

    $serverIdentificator = $_SERVER['SERVER_NAME'];

    if (
        defined("REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME") &&
        REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME
    ) {
        $serverIdentificator = $_SERVER['SERVER_ADDR'];
    }

    $recaptcha = new \ReCaptcha\ReCaptcha(REGISTER_RECAPTCHA_PRIVATEKEY);

    $recaptchaResponse = $recaptcha
        ->setExpectedHostname($serverIdentificator)
        ->verify(
            $params['responseValue'],
            $params['currentSessionIp']
        );

    $verificationResult = $recaptchaResponse->isSuccess();

    return [
        'isValid' => $verificationResult,
    ];
}

?>
