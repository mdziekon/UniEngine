<?php

namespace UniEngine\Engine\Modules\Registration\Validators;

function _validateUsername($normalizedInput) {
    $validator = function ($input, $resultHelpers) {
        $value = $input['username'];

        $minLength = 4;
        $maxLenght = 64;

        if (strlen($value) < $minLength) {
            return $resultHelpers['createFailure']([
                'code' => 'USERNAME_TOO_SHORT',
                'minLength' => $minLength,
            ]);
        }
        if (strlen($value) > $maxLenght) {
            return $resultHelpers['createFailure']([
                'code' => 'USERNAME_TOO_LONG',
                'maxLength' => $maxLenght,
            ]);
        }
        if (!preg_match(REGEXP_USERNAME_ABSOLUTE, $value)) {
            return $resultHelpers['createFailure']([
                'code' => 'USERNAME_INVALID',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($normalizedInput);
}

function _validatePassword($normalizedInput) {
    $validator = function ($input, $resultHelpers) {
        $value = $input['password'];

        $minLength = 4;

        if (strlen($value) < $minLength) {
            return $resultHelpers['createFailure']([
                'code' => 'PASSWORD_TOO_SHORT',
                'minLength' => $minLength,
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($normalizedInput);
}

function _validateEmail($normalizedInput) {
    $validator = function ($input, $resultHelpers) {
        global $_GameConfig;

        $value = $input['email'];

        $bannedDomains = str_replace('.', '\.', $_GameConfig['BannedMailDomains']);

        if (empty($value['escaped'])) {
            return $resultHelpers['createFailure']([
                'code' => 'EMAIL_EMPTY',
            ]);
        }
        if ($value['escaped'] != $value['original']) {
            return $resultHelpers['createFailure']([
                'code' => 'EMAIL_HAS_ILLEGAL_CHARACTERS',
            ]);
        }
        if (!is_email($value['escaped'])) {
            return $resultHelpers['createFailure']([
                'code' => 'EMAIL_INVALID',
            ]);
        }
        if (!empty($bannedDomains) && preg_match('#('.$bannedDomains.')+#si', $value['escaped'])) {
            return $resultHelpers['createFailure']([
                'code' => 'EMAIL_ON_BANNED_DOMAIN',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($normalizedInput);
}

function _validateGalaxyNo($normalizedInput) {
    $validator = function ($input, $resultHelpers) {
        $value = $input['galaxyNo'];

        $minGalaxyNo = 1;
        $maxGalaxyNo = MAX_GALAXY_IN_WORLD;

        if ($value < $minGalaxyNo) {
            return $resultHelpers['createFailure']([
                'code' => 'GALAXY_NO_TOO_LOW',
                'minLength' => $minGalaxyNo,
            ]);
        }
        if ($value > $maxGalaxyNo) {
            return $resultHelpers['createFailure']([
                'code' => 'GALAXY_NO_TOO_HIGH',
                'maxLength' => $maxGalaxyNo,
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($normalizedInput);
}

function _validateLangCode($normalizedInput) {
    $validator = function ($input, $resultHelpers) {
        $value = $input['langCode'];

        if (empty($value)) {
            return $resultHelpers['createFailure']([
                'code' => 'LANG_CODE_EMPTY',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($normalizedInput);
}

function _validateHasAcceptedRules($normalizedInput) {
    $validator = function ($input, $resultHelpers) {
        $value = $input['hasAcceptedRules'];

        if ($value !== true) {
            return $resultHelpers['createFailure']([
                'code' => 'RULES_NOT_ACCEPTED',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($normalizedInput);
}

function _validateAntiBot($normalizedInput, $params) {
    $validator = function ($input, $resultHelpers) use ($params) {
        if (!REGISTER_RECAPTCHA_ENABLE) {
            return $resultHelpers['createSuccess']([]);
        }

        $value = $input['captchaResponse'];

        $reCaptchaValidationResult = validateReCaptcha([
            'responseValue' => $value,
            'currentSessionIp' => $params['userSessionIp']
        ]);

        if (!($reCaptchaValidationResult['isValid'])) {
            return $resultHelpers['createFailure']([
                'code' => 'RECAPTCHA_VALIDATION_FAILED',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($validator)($normalizedInput);
}

//  Arguments
//      - $normalizedInput (Object)
//
function validateInputs($normalizedInput, $params) {
    return [
        'username' => _validateUsername($normalizedInput),
        'password' => _validatePassword($normalizedInput),
        'email' => _validateEmail($normalizedInput),
        'galaxyNo' => _validateGalaxyNo($normalizedInput),
        'langCode' => _validateLangCode($normalizedInput),
        'hasAcceptedRules' => _validateHasAcceptedRules($normalizedInput),
        'antiBot' => _validateAntiBot($normalizedInput, $params),
    ];
}

?>
