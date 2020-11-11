<?php

namespace UniEngine\Engine\Modules\Messages\Input\UserCommands;

use UniEngine\Engine\Modules\Messages;

/**
 * @param array $input
 * @param prop $input['deletemessages']
 * @param prop $input['category']
 * @param prop $input['time']
 * @param prop $input['del_all']
 * @param prop $input['del{$ID}']
 * @param prop $input['sm_all']
 * @param prop $input['sm{$ID}']
 * @param prop $input['delid']
 *
 * @param array $params
 * @param objectRef $params['user']
 * @param number $params['timestamp']
 */
function handleBatchAction(&$input, $params) {
    global $_Lang;

    $createSuccess = function ($payload) {
        return [
            'isSuccess' => true,
            'messages' => $payload,
        ];
    };
    $createFailure = function ($payload) {
        return [
            'isSuccess' => false,
            'errors' => $payload,
        ];
    };

    $user = &$params['user'];
    $currentTimestamp = $params['timestamp'];
    $knownMessageTypes = Messages\Utils\getMessageTypes();

    $parsedInput = [
        'command' => (
            isset($input['deletemessages']) ?
                $input['deletemessages'] :
                ''
        ),
        'categoryID' => intval($input['category']),
        'timeLimit' => round($input['time']),
    ];

    $knownCommands = [
        'deleteall',
        'deleteallcat',
        'deletemarked',
        'deleteunmarked',
        'setallread',
        'setcatread',
        '',
    ];

    if (!in_array($parsedInput['command'], $knownCommands)) {
        return $createFailure([]);
    }

    $timeBasedCmds = [
        'deleteall',
        'deleteallcat',
        'setallread',
        'setcatread',
    ];

    if (
        in_array($parsedInput['command'], $timeBasedCmds) &&
        (
            $parsedInput['timeLimit'] <= 0 ||
            $parsedInput['timeLimit'] > $currentTimestamp
        )
    ) {
        return $createFailure([ $_Lang['Delete_BadTimestamp'] ]);
    }

    switch ($parsedInput['command']) {
        case 'deleteall':
            $cmdResult = Messages\Commands\batchDeleteMessagesOlderThan([
                'userID' => $user['id'],
                'untilTimestamp' => $parsedInput['timeLimit'],
            ]);

            if ($cmdResult['deletedMessagesCount'] <= 0) {
                return $createFailure([ $_Lang['Delete_NoMsgsToDelete'] ]);
            }

            return $createSuccess([ $_Lang['Delete_AllMsgsDeleted'] ]);
        break;

        case 'deleteallcat':
            if ($parsedInput['categoryID'] == 80) {
                return $createFailure([ $_Lang['Delete_CannotDeleteAdminMsgsAtOnce'] ]);
            }

            if (
                !in_array($parsedInput['categoryID'], $knownMessageTypes) ||
                $parsedInput['categoryID'] == 100
            ) {
                return $createFailure([ $_Lang['Delete_BadCatSelected'] ]);
            }

            $cmdResult = Messages\Commands\batchDeleteMessagesOlderThan([
                'userID' => $user['id'],
                'messageTypeID' => $parsedInput['categoryID'],
                'untilTimestamp' => $parsedInput['timeLimit'],
            ]);

            if ($cmdResult['deletedMessagesCount'] <= 0) {
                return $createFailure([ $_Lang['Delete_NoMsgsToDelete'] ]);
            }

            return $createSuccess([ $_Lang['Delete_AllCatMsgsDeleted'] ]);
        break;

        case 'deletemarked':
            $messagesIDs = [];

            if (!empty($input['del_all'])) {
                $matches = [];

                preg_match_all('#([0-9]{1,})#si', $input['del_all'], $matches);

                $messagesIDs = $matches[0];
            } else {
                foreach ($input as $key => $value) {
                    $matches = [];
                    $matchResult = preg_match("/^del([0-9]{1,})$/D", $key, $matches);

                    if (!$matchResult || $value != 'on') {
                        continue;
                    }

                    $messagesIDs[] = $matches[1];
                }
            }

            if (empty($messagesIDs)) {
                return $createFailure([ $_Lang['Delete_NoMsgsSelected'] ]);
            }

            $cmdResult = Messages\Commands\batchDeleteMessagesByID([
                'messageIDs' => $messagesIDs,
                'userID' => $user['id'],
            ]);

            if ($cmdResult['deletedMessagesCount'] <= 0) {
                return $createFailure([ $_Lang['Delete_BadSelections'] ]);
            }

            return $createSuccess([ $_Lang['Delete_SelectedDeleted'] ]);
        break;

        case 'deleteunmarked':
            $messagesIDs = [];

            if (!empty($input['sm_all'])) {
                $matches = [];

                preg_match_all('#([0-9]{1,})#si', $input['sm_all'], $matches);

                $messagesIDs = $matches[0];
            } else {
                foreach ($input as $key => $value) {
                    $matches = [];
                    $matchResult = preg_match("/^sm([0-9]{1,})$/D", $key, $matches);

                    if (!$matchResult) {
                        continue;
                    }

                    $deleteKey = ('del' . $matches[1]);

                    if ($input[$deleteKey] == 'on') {
                        continue;
                    }

                    $messagesIDs[] = $matches[1];
                }
            }

            if (empty($messagesIDs)) {
                return $createFailure([ $_Lang['Delete_NoMsgsUnselected'] ]);
            }

            $cmdResult = Messages\Commands\batchDeleteMessagesByID([
                'messageIDs' => $messagesIDs,
                'userID' => $user['id'],
            ]);

            if ($cmdResult['deletedMessagesCount'] <= 0) {
                return $createFailure([ $_Lang['Delete_BadSelections'] ]);
            }

            return $createSuccess([ $_Lang['Delete_UnselectedDeleted'] ]);
        break;

        case 'setallread':
            $cmdResult = Messages\Commands\batchMarkMessagesAsRead([
                'userID' => $user['id'],
                'untilTimestamp' => $parsedInput['timeLimit'],
            ]);

            if ($cmdResult['markedMessagesCount'] <= 0) {
                return $createFailure([ $_Lang['Delete_NoMsgsToRead'] ]);
            }

            return $createSuccess([ $_Lang['Delete_AllMsgsRead'] ]);
        break;

        case 'setcatread':
            if ($parsedInput['categoryID'] == 80) {
                return $createFailure([ $_Lang['Delete_CannotReadAdminMsgsAtOnce'] ]);
            }

            if (
                !in_array($parsedInput['categoryID'], $knownMessageTypes) ||
                $parsedInput['categoryID'] == 100
            ) {
                return $createFailure([ $_Lang['Delete_BadCatSelected'] ]);
            }

            $cmdResult = Messages\Commands\batchMarkMessagesAsRead([
                'userID' => $user['id'],
                'messageTypeID' => $parsedInput['categoryID'],
                'untilTimestamp' => $parsedInput['timeLimit'],
            ]);

            if ($cmdResult['markedMessagesCount'] <= 0) {
                return $createFailure([ $_Lang['Delete_NoMsgsToRead'] ]);
            }

            return $createSuccess([ $_Lang['Delete_CatMsgsRead'] ]);
        break;

        default:
            if (empty($input['delid'])) {
                return $createFailure([]);
            }

            $messageID = round(floatval($input['delid']));

            $cmdResult = Messages\Commands\batchDeleteMessagesByID([
                'messageIDs' => [ $messageID ],
                'userID' => $user['id'],
                'ignoreExcludedMessageTypesRestriction' => true,
            ]);

            if ($cmdResult['deletedMessagesCount'] <= 0) {
                return $createFailure([ $_Lang['Delete_MsgNoExist'] ]);
            }

            return $createSuccess([ $_Lang['Delete_MsgDeleted'] ]);
    }
}



?>
