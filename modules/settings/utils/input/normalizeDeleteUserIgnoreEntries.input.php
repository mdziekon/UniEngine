<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Input;

/**
 * @param mixed $input
 */
function normalizeDeleteUserIgnoreEntries($input) {
    if (
        empty($input) ||
        !is_array($input)
    ) {
        return [];
    }

    $normalizedInput = array_map_withkeys($input, function ($value) {
        return intval($value, 10);
    });
    $normalizedInput = array_filter($normalizedInput, function ($value) {
        return $value > 0;
    });

    return $normalizedInput;
}

?>
