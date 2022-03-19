<?php

namespace UniEngine\Engine\Modules\Registration\Validators;

function _createFuncWithResultHelpers($func) {
    return function ($arguments) use ($func) {
        $createSuccess = function ($payload) {
            return [
                'isSuccess' => true,
                'payload' => $payload,
            ];
        };
        $createFailure = function ($payload) {
            return [
                'isSuccess' => false,
                'error' => $payload,
            ];
        };

        return $func($arguments, [
            'createSuccess' => $createSuccess,
            'createFailure' => $createFailure,
        ]);
    };
}

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

    return _createFuncWithResultHelpers($validator)($normalizedInput);
}

//  Arguments
//      - $normalizedInput (Object)
//
function validateInputs($normalizedInput) {
    return [
        'username' => _validateUsername($normalizedInput),
    ];
}

?>
