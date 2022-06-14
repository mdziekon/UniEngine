<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Helpers;

/**
 * @param array $params
 * @param array $params['entriesIds']
 * @param arrayRef $params['currentUser']
 */
function tryDeleteUserIgnoreEntries($params) {
    $executor = function ($input, $resultHelpers) {
        $currentUser = &$input['currentUser'];
        $entriesIds = $input['entriesIds'];

        if (empty($entriesIds)) {
            return $resultHelpers['createFailure']([
                'code' => 'NO_VALID_ENTRY_ID_PROVIDED',
            ]);
        }

        $existingEntriesIds = array_filter($entriesIds, function ($entryId) use (&$currentUser) {
            return !empty($currentUser['IgnoredUsers'][$entryId]);
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
