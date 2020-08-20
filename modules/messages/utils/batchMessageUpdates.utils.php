<?php

namespace UniEngine\Engine\Modules\Messages\Utils;

function getBatchActionsExcludedMessageTypes() {
    return [ 80 ];
}

function getBatchActionsExcludedMessageTypesQueryString() {
    $messageTypes = array_map(
        function ($value) { return strval($value); },
        getBatchActionsExcludedMessageTypes()
    );

    return implode(', ', $messageTypes);
}

?>
