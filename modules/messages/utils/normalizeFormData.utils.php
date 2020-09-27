<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

use UniEngine\Engine\Modules\Messages;

/**
 * Send a message by inserting it into the Messages Cache system's queue.
 *
 * @param &array $input
 * @param number | null $input['replyto']
 * @param string $input['uname']
 * @param 'on' | null $input['send_as_admin_msg']
 * @param string $input['subject']
 * @param string $input['text']
 * @param array $params
 * @param &array $params['senderUser']
 * @param number $params['subjectMaxLength']
 * @param number $params['contentMaxLength']
 */
function normalizeFormData($input, $params) {
    $senderUser = &$params['senderUser'];

    $formData = [
        'replyToId' => null,
        'recipient' => [
            'id' => null,
            'username' => null,
            'authlevel' => null,
        ],
        'message' => [
            'type' => null,
            'subject' => null,
            'content' => null,
        ],
        'meta' => [
            'isSendingAsAdmin' => false,
            'isReplyingToOngoingThread' => false,
            'nextSubject' => null,
        ],
    ];

    $normalizationErrors = [
        'isReplyToInvalid' => false,
        'isRecipientUsernameInvalid' => false,
        'recipientNotFound' => false,
        'hasNoPermissionToChangeType' => false,
    ];

    $isReplying = !empty($input['replyto']);

    if ($isReplying) {
        $handleReplyTo = function () use ($input, $senderUser, &$formData, &$normalizationErrors) {
            $replyId = round($input['replyto']);

            if (!($replyId > 0)) {
                $normalizationErrors['isReplyToInvalid'] = true;

                return;
            }

            $replyFormData = Messages\Utils\fetchFormDataForReply([
                'replyToMessageId' => $replyId,
                'senderUser' => &$senderUser,
            ]);

            if (!$replyFormData['isSuccess']) {
                $normalizationErrors['isReplyToInvalid'] = true;

                return;
            }

            $formData['replyToId'] = $replyId;
            $formData['recipient'] = [
                'id' => $replyFormData['payload']['uid'],
                'username' => $replyFormData['payload']['username'],
                'authlevel' => $replyFormData['payload']['authlevel'],
            ];
            $formData['message']['subject'] = $replyFormData['payload']['subject'];
            $formData['meta']['isReplyingToOngoingThread'] = $replyFormData['payload']['Thread_Started'];
            $formData['meta']['nextSubject'] = $replyFormData['payload']['nextSubject'];
        };

        $handleReplyTo();
    }

    if (!$isReplying) {
        $fetchResult = (
            !empty($input['uid']) ?
                Messages\Utils\fetchRecipientDataByUserId([
                    'userId' => $input['uid'],
                ]) :
                Messages\Utils\fetchRecipientDataByUsername([
                    'username' => $input['uname'],
                ])
        );

        if (!$fetchResult['isSuccess']) {
            if ($fetchResult['errors']['isUsernameInvalid']) {
                $normalizationErrors['isRecipientUsernameInvalid'] = true;
            }
            if ($fetchResult['errors']['notFound']) {
                $normalizationErrors['recipientNotFound'] = true;
            }
        }

        if ($fetchResult['isSuccess']) {
            $recipientData = $fetchResult['payload'];

            $formData['recipient'] = [
                'id' => $recipientData['id'],
                'username' => $recipientData['username'],
                'authlevel' => $recipientData['authlevel'],
            ];
        }
    }

    $handleMessageType = function () use ($input, $senderUser, &$formData, &$normalizationErrors) {
        if (
            empty($input['send_as_admin_msg']) ||
            $input['send_as_admin_msg'] !== 'on'
        ) {
            $formData['message']['type'] = 1;

            return;
        }

        if (!CheckAuth('supportadmin', AUTHCHECK_NORMAL, $senderUser)) {
            $normalizationErrors['hasNoPermissionToChangeType'] = true;

            return;
        }

        $formData['message']['type'] = 80;
        $formData['meta']['isSendingAsAdmin'] = true;
    };

    $normalizeUserContent = function ($content, $params) {
        $normalizedContent = (
            isset($content) ?
                stripslashes(trim($content)) :
                ''
        );

        if (get_magic_quotes_gpc()) {
            $normalizedContent = stripslashes($normalizedContent);
        }

        $normalizedContent = strip_tags($normalizedContent);
        $normalizedContent = substr($normalizedContent, 0, $params['maxLength']);

        return $normalizedContent;
    };

    $handleMessageType();

    if (empty($formData['message']['subject'])) {
        $formData['message']['subject'] = $normalizeUserContent(
            $input['subject'],
            [ 'maxLength' => $params['subjectMaxLength'], ]
        );
    }

    $formData['message']['content'] = $normalizeUserContent(
        $input['text'],
        [ 'maxLength' => $params['contentMaxLength'], ]
    );

    $hasNormalizationErrors = (
        count(
            array_filter(
                $normalizationErrors,
                function ($hasErrorOccured) {
                    return $hasErrorOccured;
                }
            )
        ) > 0
    );

    return [
        'isSuccess' => !$hasNormalizationErrors,
        'formData' => $formData,
        'errors' => $normalizationErrors,
    ];
}

?>
