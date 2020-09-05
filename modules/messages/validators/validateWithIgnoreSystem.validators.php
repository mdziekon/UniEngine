<?php

namespace UniEngine\Engine\Modules\Messages\Validators;

/**
 * @param array $params
 * @param &array $params['senderUser']
 * @param &array $params['recipientUser']
 */
function validateWithIgnoreSystem($params) {
    $isValid = function () {
        return [
            'isValid' => true,
        ];
    };
    $isInvalid = function ($errors) {
        return [
            'isValid' => false,
            'errors' => $errors,
        ];
    };

    $senderUser = &$params['senderUser'];
    $recipientUser = &$params['recipientUser'];

    if (
        CheckAuth('user', AUTHCHECK_HIGHER, $senderUser) ||
        CheckAuth('user', AUTHCHECK_HIGHER, $recipientUser)
    ) {
        return $isValid();
    }

    $Query_IgnoreSystem = '';
    $Query_IgnoreSystem .= "SELECT `OwnerID` FROM {{table}} WHERE ";
    $Query_IgnoreSystem .= "(`OwnerID` = {$senderUser['id']} AND `IgnoredID` = {$recipientUser['id']}) OR ";
    $Query_IgnoreSystem .= "(`OwnerID` = {$recipientUser['id']} AND `IgnoredID` = {$senderUser['id']}) ";
    $Query_IgnoreSystem .= "LIMIT 2; -- messages.php|IgnoreSystem";

    $Result_IgnoreSystem = doquery($Query_IgnoreSystem, 'ignoresystem');

    if ($Result_IgnoreSystem->num_rows == 0) {
        return $isValid();
    }

    $validationErrors = [
        'isRecipientIgnored' => false,
        'isSenderIgnored' => false,
    ];

    while ($IgnoreData = $Result_IgnoreSystem->fetch_assoc()) {
        if ($IgnoreData['OwnerID'] == $senderUser['id']) {
            $validationErrors['isRecipientIgnored'] = true;
        }
        if ($IgnoreData['OwnerID'] == $recipientUser['id']) {
            $validationErrors['isSenderIgnored'] = true;
        }
    }

    return $isInvalid($validationErrors);
}

?>
