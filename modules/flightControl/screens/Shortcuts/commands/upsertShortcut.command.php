<?php

namespace UniEngine\Engine\Modules\FlightControl\Screens\Shortcuts\Commands;

use UniEngine\Engine\Modules\Flights;

/**
 * @param Object $props
 * @param Object $props['input']
 * @param String $props['userId']
 * @param String $props['shortcutId']
 *
 * @return Object $result
 * @return Boolean $result['isSuccess']
 */
function upsertShortcut($props) {
    $input = $props['input'];
    $userId = $props['userId'];
    $shortcutId = (
        isset($props['shortcutId']) ?
            $props['shortcutId'] :
            null
    );

    $normalizedInput['customName'] = trim($input['name']);
    $normalizedInput['galaxy'] = intval($input['galaxy']);
    $normalizedInput['system'] = intval($input['system']);
    $normalizedInput['planet'] = intval($input['planet']);
    $normalizedInput['planetType'] = intval($input['type']);
    $normalizedInput['targetId'] = '0';

    if ($shortcutId !== null) {
        $shortcutId = intval($shortcutId, 10);

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
    }

    $isValidCoordinate = Flights\Utils\Checks\isValidCoordinate([
        'coordinate' => [
            'galaxy' => $normalizedInput['galaxy'],
            'system' => $normalizedInput['system'],
            'planet' => $normalizedInput['planet'],
            'type' => $normalizedInput['planetType'],
        ]
    ]);

    if (!$isValidCoordinate['isValid']) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'INVALID_COORDINATES',
            ],
        ];
    }

    if (
        !empty($normalizedInput['customName']) &&
        !preg_match('/^[a-zA-Z0-9\_\-\ ]{1,}$/D', $normalizedInput['customName'])
    ) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'INVALID_NAME',
            ],
        ];
    }

    if ($normalizedInput['planetType'] == 1 || $normalizedInput['planetType'] == 3) {
        $fetchPossibleTargetQuery = (
            "SELECT " .
            "`id` " .
            "FROM {{table}} " .
            "WHERE " .
            "`galaxy` = {$normalizedInput['galaxy']} AND " .
            "`system` = {$normalizedInput['system']} AND " .
            "`planet` = {$normalizedInput['planet']} AND " .
            "`planet_type` = {$normalizedInput['planetType']} " .
            "LIMIT 1 " .
            ";"
        );
        $possibleTargetResult = doquery($fetchPossibleTargetQuery, 'planets', true);

        if ($possibleTargetResult) {
            $normalizedInput['targetId'] = $possibleTargetResult['id'];
        }
    }

    $fetchShortcutByCoordsQuery = (
        "SELECT " .
        "`id` " .
        "FROM {{table}} " .
        "WHERE " .
        "`galaxy` = {$normalizedInput['galaxy']} AND " .
        "`system` = {$normalizedInput['system']} AND " .
        "`planet` = {$normalizedInput['planet']} AND " .
        "`type` = {$normalizedInput['planetType']} AND " .
        "`id_owner` = {$userId} " .
        "LIMIT 1 " .
        ";"
    );
    $shortcutByCoords = doquery($fetchShortcutByCoordsQuery, 'fleet_shortcuts', true);

    if (
        $shortcutByCoords &&
        (
            $shortcutId === null ||
            $shortcutId != $shortcutByCoords['id']
        )
    ) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'ALREADY_EXISTS',
            ],
        ];
    }

    $upsertShortcutQuery = (
        "INSERT INTO {{table}}  " .
        "VALUES " .
        "( " .
        (
            $shortcutId ?
                "{$shortcutId}, " :
                "NULL, "
        ) .
        "{$userId}, " .
        "{$normalizedInput['targetId']}, " .
        "{$normalizedInput['galaxy']}, " .
        "{$normalizedInput['system']}, " .
        "{$normalizedInput['planet']}, " .
        "{$normalizedInput['planetType']}, " .
        "'{$normalizedInput['customName']}' " .
        ") " .
        "ON DUPLICATE KEY UPDATE " .
        "`id_planet` = VALUES(`id_planet`), " .
        "`galaxy` = VALUES(`galaxy`), " .
        "`system` = VALUES(`system`), " .
        "`planet` = VALUES(`planet`), " .
        "`type` = VALUES(`type`), " .
        "`own_name` = VALUES(`own_name`) " .
        "; "
    );
    doquery($upsertShortcutQuery, 'fleet_shortcuts');

    return [
        'isSuccess' => true,
        'payload' => [],
    ];
}

?>
