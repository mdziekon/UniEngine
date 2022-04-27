<?php

namespace UniEngine\Engine\Modules\FlightControl\Screens\Shortcuts\Commands;

/**
 * @param Object $props
 * @param Object $props['input']
 * @param String $props['userId']
 *
 * @return Object $result
 * @return Boolean $result['isSuccess']
 */
function deleteShortcut($props) {
    $input = $props['input'];
    $userId = $props['userId'];

    $shortcutId = (
        isset($input['id']) ?
            intval($input['id'], 10) :
            0
    );

    if ($shortcutId <= 0) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'INVALID_ID',
            ],
        ];
    }

    $fetchShortcutQuery = (
        "SELECT " .
        "`id_owner` " .
        "FROM {{table}} " .
        "WHERE " .
        "`id` = {$shortcutId} " .
        "LIMIT 1 " .
        ";"
    );
    $shortcutEntry = doquery($fetchShortcutQuery, 'fleet_shortcuts', true);

    if (!$shortcutEntry) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'INVALID_ID',
            ],
        ];
    }
    if ($shortcutEntry['id_owner'] != $userId) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'USER_NOT_OWNER',
            ],
        ];
    }

    $deleteShortcutQuery = (
        "DELETE " .
        "FROM {{table}} " .
        "WHERE " .
        "`id` = {$shortcutId} " .
        "LIMIT 1 " .
        "; "
    );
    doquery($deleteShortcutQuery, 'fleet_shortcuts');

    return [
        'isSuccess' => true,
        'payload' => [],
    ];
}

?>
