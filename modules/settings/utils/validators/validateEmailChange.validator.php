<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Validators;

// TODO: Deduplicate, registration does the same thing
function _isOnDomainBanlist($emailAddress) {
    global $_GameConfig;

    $bannedDomains = $_GameConfig['BannedMailDomains'];
    $bannedDomains = str_replace('.', '\.', $bannedDomains);

    if (empty($bannedDomains)) {
        return false;
    }

    return preg_match('#('.$bannedDomains.')+#si', $emailAddress) === 1;
}

/**
 * @param array $params
 * @param array $params['input']
 * @param string $params['input']['newEmailAddress']
 * @param string $params['input']['newEmailAddressConfirm']
 * @param arrayRef $params['currentUser']
 * @param boolean $params['isAlreadyChangingEmail']
 */
function validateEmailChange($params) {
    $currentUser = &$params['currentUser'];
    $isAlreadyChangingEmail = $params['isAlreadyChangingEmail'];

    $executor = function ($input, $resultHelpers) use (&$currentUser, $isAlreadyChangingEmail) {
        $newEmailAddress = $input['newEmailAddress'];
        $newEmailAddressConfirm = $input['newEmailAddressConfirm'];

        $currentUserEmail = $currentUser['email'];

        if ($isAlreadyChangingEmail) {
            return $resultHelpers['createFailure']([
                'code' => 'EMAIL_CHANGE_IN_PROGRESS',
            ]);
        }

        if (!is_email($newEmailAddress)) {
            return $resultHelpers['createFailure']([
                'code' => 'INVALID_EMAIL',
            ]);
        }
        if ($newEmailAddress === $currentUserEmail) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_EMAIL_SAME_AS_OLD',
            ]);
        }
        if ($newEmailAddress !== $newEmailAddressConfirm) {
            return $resultHelpers['createFailure']([
                'code' => 'NEW_EMAIL_CONFIRMATION_INVALID',
            ]);
        }
        if (_isOnDomainBanlist($newEmailAddress)) {
            return $resultHelpers['createFailure']([
                'code' => 'BANNED_DOMAIN_USED',
            ]);
        }

        $fetchExistingEmailFromDB = doquery(
            "SELECT `id` FROM {{table}} WHERE `email` = '{$newEmailAddress}' LIMIT 1;",
            'users',
            true
        );

        if ($fetchExistingEmailFromDB) {
            // TODO: Verify whether we should fetch email change processes as well
            return $resultHelpers['createFailure']([
                'code' => 'NEW_EMAIL_ALREADY_IN_USE',
            ]);
        }

        return $resultHelpers['createSuccess']([]);
    };

    return createFuncWithResultHelpers($executor)($params['input']);
}

?>
