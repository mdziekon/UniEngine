<?php

namespace UniEngine\Engine\Modules\Registration\Utils\Errors;

/**
 * @param object $error As returned by Registration\Validators\validateInputs
 */
function mapErrorToAjaxResponse($error) {
    $knownErrorsByCode = [
        'USERNAME_TOO_SHORT' => [
            'jsonCode' => 1,
            'fieldName' => 'username',
        ],
        'USERNAME_TOO_LONG' => [
            'jsonCode' => 2,
            'fieldName' => 'username',
        ],
        'USERNAME_INVALID' => [
            'jsonCode' => 3,
            'fieldName' => 'username',
        ],
        'PASSWORD_TOO_SHORT' => [
            'jsonCode' => 4,
            'fieldName' => 'password',
        ],
        'EMAIL_EMPTY' => [
            'jsonCode' => 5,
            'fieldName' => 'email',
        ],
        'EMAIL_HAS_ILLEGAL_CHARACTERS' => [
            'jsonCode' => 6,
            'fieldName' => 'email',
        ],
        'EMAIL_INVALID' => [
            'jsonCode' => 7,
            'fieldName' => 'email',
        ],
        'EMAIL_ON_BANNED_DOMAIN' => [
            'jsonCode' => 8,
            'fieldName' => 'email',
        ],
        'GALAXY_NO_TOO_LOW' => [
            'jsonCode' => 13,
            'fieldName' => 'galaxy',
        ],
        'GALAXY_NO_TOO_HIGH' => [
            'jsonCode' => 14,
            'fieldName' => 'galaxy',
        ],
        'LANG_CODE_EMPTY' => [
            'jsonCode' => 16,
        ],
        'RULES_NOT_ACCEPTED' => [
            'jsonCode' => 9,
        ],
        'RECAPTCHA_VALIDATION_FAILED' => [
            'jsonCode' => 10,
        ],
        'USERNAME_TAKEN' => [
            'jsonCode' => 11,
            'fieldName' => 'username',
        ],
        'EMAIL_TAKEN' => [
            'jsonCode' => 12,
            'fieldName' => 'email',
        ],
        'GALAXY_TOO_CROWDED' => [
            'jsonCode' => 15,
            'fieldName' => 'galaxy',
        ],
    ];

    $errorCode = $error['code'];

    return $knownErrorsByCode[$errorCode];
}

?>
