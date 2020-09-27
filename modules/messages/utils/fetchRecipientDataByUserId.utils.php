<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

/**
 * Fetches necessary data of a message recipient.
 *
 * @param array $params
 * @param string $params['userId']
 */
function fetchRecipientDataByUserId($params) {
    $userId = intval($params['userId']);

    if (!($userId > 0)) {
        return [
            'isSuccess' => false,
            'errors' => [
                'isUserIdInvalid' => true,
            ],
        ];
    }

    $query_fetchRecipientData = (
        "SELECT `id`, `username`, `authlevel` " .
        "FROM {{table}} " .
        "WHERE `id` = '{$userId}' " .
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
    }

    $recipientData = [
        'id' => $result_fetchRecipientData['id'],
        'username' => $result_fetchRecipientData['username'],
        'authlevel' => $result_fetchRecipientData['authlevel'],
    ];

    return [
        'isSuccess' => true,
        'payload' => $recipientData,
    ];
}

?>
