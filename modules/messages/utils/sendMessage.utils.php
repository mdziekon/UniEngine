<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

/**
 * Send a message by inserting it into the Messages Cache system's queue.
 *
 * @param array $params
 * @param &array $params['senderUser']
 * @param &array $params['recipientUser']
 * @param array $params['messageData']
 * @param string $params['messageData']['timestamp']
 * @param string $params['messageData']['type']
 * @param string $params['messageData']['subject']
 * @param string $params['messageData']['content']
 * @param string $params['messageData']['threadId']
 * @param string $params['messageData']['threadHasStarted']
 */
function sendMessage($params) {
    $senderUser = &$params['senderUser'];
    $recipientUser = &$params['recipientUser'];

    $messageData = $params['messageData'];
    $isInThread = ($messageData['threadId'] > 0);
    $isThisMessageNewLastMessageInThread = $isInThread;

    if ($isInThread) {
        if ($messageData['threadHasStarted']) {
            $query_UpdateExistingThread = (
                "UPDATE {{table}} " .
                "SET " .
                "`Thread_IsLast` = 0 " .
                "WHERE " .
                "`Thread_ID` = {$messageData['threadId']} AND " .
                "`id_owner` = {$recipientUser['id']} " .
                ";"
            );

            doquery($query_UpdateExistingThread, 'messages');
        } else {
            $query_UpdateNewThreadFirstMessage = (
                "UPDATE {{table}} " .
                "SET " .
                "`Thread_ID` = `id`, " .
                "`Thread_IsLast` = 1 " .
                "WHERE " .
                "`id` = {$messageData['threadId']} " .
                "LIMIT 1 " .
                ";"
            );

            doquery($query_UpdateNewThreadFirstMessage, 'messages');
        }
    }

    Cache_Message(
        $recipientUser['id'],
        $senderUser['id'],
        $messageData['timestamp'],
        $messageData['type'],
        '',
        $messageData['subject'],
        $messageData['content'],
        $messageData['threadId'],
        $isThisMessageNewLastMessageInThread
    );

    return [
        'isSuccess' => true,
    ];
}

?>
