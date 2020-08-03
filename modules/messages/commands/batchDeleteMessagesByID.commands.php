<?php

namespace UniEngine\Engine\Modules\Messages\Commands;

use UniEngine\Engine\Modules\Messages;

/**
 * Soft deletes all messages of a specified user by message IDs.
 * Admin messages are excluded from being batch deleted.
 *
 * @param array $params
 * @param array<number> $params['messageIDs']
 *               Messages to be deleted.
 * @param number $params['userID']
 *               The ID of a user whose messages should be deleted.
 * @param boolean|null $params['ignoreExcludedMessageTypesRestriction']
 *               (Optional) Lifts the prevention of excluded message types removal.
 */
function batchDeleteMessagesByID($params) {
    return Messages\Utils\batchDeleteMessages([
        'criteria' => [
            'userID' => $params['userID'],
            'messageIDs' => $params['messageIDs'],
            'excludeTypeIDs' => (
                (
                    !isset($params['ignoreExcludedMessageTypesRestriction']) ||
                    !$params['ignoreExcludedMessageTypesRestriction']
                ) ?
                Messages\Utils\getBatchActionsExcludedMessageTypes() :
                []
            ),
        ],
    ]);
}

?>
