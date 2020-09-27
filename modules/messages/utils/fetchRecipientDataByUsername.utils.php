<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

/**
 * Fetches necessary data of a message recipient.
 *
 * @param array $params
 * @param string $params['username']
 */
function fetchRecipientDataByUsername($params) {
    $username = trim($params['username']);

    if (!preg_match(REGEXP_USERNAME_ABSOLUTE, $username)) {
        return [
            'isSuccess' => false,
            'errors' => [
                'isUsernameInvalid' => true,
            ],
        ];
    }

    $query_fetchRecipientData = (
        "SELECT `id`, `authlevel` " .
        "FROM {{table}} " .
        "WHERE `username` = '{$username}' " .
        "LIMIT 1 " .
        ";"
    );
    $result_fetchRecipientData = doquery($query_fetchRecipientData, 'users', true);

    if (!$result_fetchRecipientData) {
        return [
            'isSuccess' => false,
            'errors' => [
                'notFound' => true,
            ],
        ];

        return;
    }

    $recipientData = [
        'id' => $result_fetchRecipientData['id'],
        'username' => $username,
        'authlevel' => $result_fetchRecipientData['authlevel'],
    ];

    return [
        'isSuccess' => true,
        'payload' => $recipientData,
    ];
}

?>
