<?php

namespace UniEngine\Engine\Modules\Settings\Utils\ErrorMappers;

/**
 * @param object $error As returned by Settings\Utils\Helpers\tryDeleteUserIgnoreEntries
 */
function mapTryDeleteUserIgnoreEntriesErrorToReadableMessage($error) {
    global $_Lang;

    $errorCode = $error['code'];

    $knownErrorsByCode = [
        'NO_VALID_ENTRY_ID_PROVIDED' => $_Lang['Ignore_NothingSelected'],
        'NO_VALID_ENTRY_ID_SELECTED' => $_Lang['Ignore_NothingDeleted'],
    ];

    if (!isset($knownErrorsByCode[$errorCode])) {
        return $_Lang['sys_unknownError'];
    }

    return $knownErrorsByCode[$errorCode];
}

?>
