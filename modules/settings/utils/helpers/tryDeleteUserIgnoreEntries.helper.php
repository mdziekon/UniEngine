<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Helpers;

/**
 * @param array $params
 * @param array $params['entriesIds']
 * @param array $params['ignoredUsers']
 */
function tryDeleteUserIgnoreEntries($params) {
    $executor = function ($input, $resultHelpers) {
        $ignoredUsers = $input['ignoredUsers'];
        $entriesIds = $input['entriesIds'];

        if (empty($entriesIds)) {
            return $resultHelpers['createFailure']([
                'code' => 'NO_VALID_ENTRY_ID_PROVIDED',
            ]);
        }

        $existingEntriesIds = array_filter($entriesIds, function ($entryId) use ($ignoredUsers) {
            return !empty($ignoredUsers[$entryId]);
        });

        if (empty($existingEntriesIds)) {
            return $resultHelpers['createFailure']([
                'code' => 'NO_VALID_ENTRY_ID_SELECTED',
            ]);
        }

        return $resultHelpers['createSuccess']([
            'entriesToDelete' => $existingEntriesIds,
        ]);
    };

    return createFuncWithResultHelpers($executor)($params);
}

?>
