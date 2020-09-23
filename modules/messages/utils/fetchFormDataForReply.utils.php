<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

use UniEngine\Engine\Modules\Messages;

/**
 * Fetches necessary data when trying to reply to an existing message.
 *
 * @param array $params
 * @param number $params['replyToMessageId']
 * @param &array $params['senderUser']
 */
function fetchFormDataForReply($params) {
    $senderUser = &$params['senderUser'];
    $replyToMessageId = $params['replyToMessageId'];

    $senderUserId = $senderUser['id'];

    $query_GetReferencedMessage = (
        "SELECT " .
        "`m`.`id`, `m`.`Thread_ID`, `m`.`subject`, `m`.`text`, `u`.`id` AS `user_id`, `u`.`username`, `u`.`authlevel` " .
        "FROM " .
        "{{table}} AS `m` " .
        "LEFT JOIN " .
        "`{{prefix}}users` AS `u` ON `u`.`id` = IF(`m`.`id_owner` != {$senderUserId}, `m`.`id_owner`, `m`.`id_sender`) " .
        "WHERE " .
        "( " .
        "`m`.`id` = {$replyToMessageId} OR " .
        "`m`.`Thread_ID` = {$replyToMessageId} " .
        ") AND ( " .
        "`m`.`id_owner` = {$senderUserId} OR " .
        "`m`.`id_sender` = {$senderUserId} " .
        ") AND " .
        "`deleted` = false " .
        "LIMIT 1 " .
        ";"
    );

    $referencedMessage = doquery($query_GetReferencedMessage, 'messages', true);

    if (!$referencedMessage || $referencedMessage['id'] <= 0) {
        return [
            'isSuccess' => false,
            'error' => [
                'messageNotFound' => true,
            ],
        ];
    }


    $isInOngoingThread = ($referencedMessage['Thread_ID'] > 0);
    $previousSubject = $referencedMessage['subject'];
    $replyCounter = 1;

    $formData = [
        'username' => $referencedMessage['username'],
        'uid' => $referencedMessage['user_id'],
        'authlevel' => $referencedMessage['authlevel'],
        'subject' => null,
        'nextSubject' => null,
        'replyto' => $replyToMessageId,
        'Thread_Started' => $isInOngoingThread,
        'lock_username' => true,
        'lock_subject' => true,
    ];

    $checkIsMessageCopy = Messages\Utils\getMessageCopyId([
        'messageData' => &$referencedMessage,
    ]);

    if ($checkIsMessageCopy['isSuccess']) {
        $query_GetOriginalMessage = (
            "SELECT `subject` " .
            "FROM {{table}} " .
            "WHERE `id` = {$checkIsMessageCopy['payload']['originalMessageId']} " .
            "LIMIT 1 " .
            ";"
        );

        $originalMessage = doquery($query_GetOriginalMessage, 'messages', true);

        $previousSubject = $originalMessage['subject'];
    }

    if ($isInOngoingThread) {
        $query_GetThreadLength = (
            "SELECT " .
            "COUNT(*) AS `Count` " .
            "FROM {{table}} " .
            "WHERE " .
            "`Thread_ID` = {$replyToMessageId} " .
            ";"
        );

        $result_GetThreadLength = doquery($query_GetThreadLength, 'messages', true);

        $replyCounter = $result_GetThreadLength['Count'];
    }

    $formData['subject'] = Messages\Utils\createReplyMessageSubject([
        'previousSubject' => $previousSubject,
        'replyCounter' => $replyCounter,
    ]);
    $formData['nextSubject'] = Messages\Utils\createReplyMessageSubject([
        'previousSubject' => $previousSubject,
        'replyCounter' => ($replyCounter + 1),
    ]);

    return [
        'isSuccess' => true,
        'payload' => $formData,
    ];
}

?>
