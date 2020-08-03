<?php

namespace UniEngine\Engine\Modules\Messages\Commands;

use UniEngine\Engine\Modules\Messages;

/**
 * Soft deletes all messages of a specified user which were received up until the specified point in time.
 * Admin messages are excluded from being batch deleted.
 *
 * @param array $params
 * @param number $params['userID']
 *               The ID of a user whose messages should be deleted.
 * @param number|null $params['messageTypeID']
 *               (Optional) Narrows the messages to be deleted to the specified type.
 * @param number $params['untilTimestamp']
 *               Determines the cut-off point for deletion, meaning that all messages
 *               prior to this point in time (inclusive) will be deleted.
 */
function batchDeleteMessagesOlderThan($params) {
    return Messages\Utils\batchDeleteMessages([
        'criteria' => [
            'userID' => $params['userID'],
            'untilTimestamp' => $params['untilTimestamp'],
            'includeTypeIDs' => (
                isset($params['messageTypeID']) ?
                [ $params['messageTypeID'] ] :
                []
            ),
            'excludeTypeIDs' => (
                !isset($params['messageTypeID']) ?
                Messages\Utils\getBatchActionsExcludedMessageTypes() :
                []
            ),
        ],
    ]);
}

?>
