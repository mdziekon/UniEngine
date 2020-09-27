<?php

namespace UniEngine\Engine\Modules\Messages\Input\UserCommands;

use UniEngine\Engine\Modules\Messages;

/**
 * @param array $input
 *  Same as Messages\Utils\normalizeFormData
 *
 * @param array $params
 * @param objectRef $params['senderUser']
 * @param number $params['subjectMaxLength']
 * @param number $params['contentMaxLength']
 * @param number $params['timestamp']
 */
function handleSendMessage(&$input, $params) {
    global $_EnginePath;

    $createSuccess = function ($messages, $payload) {
        return [
            'isSuccess' => true,
            'messages' => $messages,
            'payload' => $payload,
        ];
    };
    $createFailure = function ($errors, $payload) {
        return [
            'isSuccess' => false,
            'errors' => $errors,
            'payload' => $payload,
        ];
    };

    $senderUser = &$params['senderUser'];
    $subjectMaxLength = $params['subjectMaxLength'];
    $contentMaxLength = $params['contentMaxLength'];
    $timestamp = $params['timestamp'];

    $formDataNormalizationResult = Messages\Utils\normalizeFormData(
        $input,
        [
            'senderUser' => &$senderUser,
            'subjectMaxLength' => $subjectMaxLength,
            'contentMaxLength' => $contentMaxLength,
        ]
    );

    $formData = $formDataNormalizationResult['formData'];

    if (!$formDataNormalizationResult['isSuccess']) {
        return $createFailure(
            $formDataNormalizationResult['errors'],
            [ 'formData' => $formData, ]
        );
    }

    // Validation
    if ($formData['recipient']['id'] == $senderUser['id']) {
        return $createFailure(
            [
                'isWritingToYourself' => true,
            ],
            [ 'formData' => $formData, ]
        );
    }

    if (empty($formData['message']['subject'])) {
        return $createFailure(
            [
                'isMessageSubjectEmpty' => true,
            ],
            [ 'formData' => $formData, ]
        );
    }
    if (empty($formData['message']['content'])) {
        return $createFailure(
            [
                'isMessageContentEmpty' => true,
            ],
            [ 'formData' => $formData, ]
        );
    }

    include($_EnginePath . 'includes/functions/FilterMessages.php');

    if (FilterMessages($formData['message']['content'], 1)) {
        return $createFailure(
            [
                'isMessageContentSpam' => true,
            ],
            [ 'formData' => $formData, ]
        );
    }

    $ignoreSystemValidationResult = Messages\Validators\validateWithIgnoreSystem([
        'senderUser' => &$senderUser,
        'recipientUser' => $formData['recipient'],
    ]);

    if (!$ignoreSystemValidationResult['isValid']) {
        return $createFailure(
            $ignoreSystemValidationResult['errors'],
            [ 'formData' => $formData, ]
        );
    }

    Messages\Utils\sendMessage([
        'senderUser' => &$senderUser,
        'recipientUser' => $formData['recipient'],
        'messageData' => [
            'timestamp' => $timestamp,
            'type' => $formData['message']['type'],
            'subject' => $formData['message']['subject'],
            'content' => $formData['message']['content'],
            'threadId' => $formData['replyToId'],
            'threadHasStarted' => $formData['meta']['isReplyingToOngoingThread'],
        ],
    ]);

    return $createSuccess(
        [],
        [ 'formData' => $formData, ]
    );
}

?>
