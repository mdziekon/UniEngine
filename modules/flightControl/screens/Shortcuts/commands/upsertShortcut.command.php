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
function upsertShortcut($props) {
    $input = $props['input'];
    $userId = $props['userId'];

    $normalizedInput['customName'] = trim($input['name']);
    $normalizedInput['galaxy'] = intval($input['galaxy']);
    $normalizedInput['system'] = intval($input['system']);
    $normalizedInput['planet'] = intval($input['planet']);
    $normalizedInput['planetType'] = intval($input['type']);
    $normalizedInput['targetId'] = '0';

    if (
        !in_array(
            $normalizedInput['planetType'],
            [ 1, 2, 3 ]
        ) ||
        $normalizedInput['galaxy'] <= 0 ||
        $normalizedInput['galaxy'] > MAX_GALAXY_IN_WORLD ||
        $normalizedInput['system'] <= 0 ||
        $normalizedInput['system'] > MAX_SYSTEM_IN_GALAXY ||
        $normalizedInput['planet'] <= 0 ||
        $normalizedInput['planet'] > (MAX_PLANET_IN_SYSTEM + 1)
    ) {
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

    if ($shortcutByCoords) {
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
        "NULL, " .
        "{$userId}, " .
        "{$normalizedInput['targetId']}, " .
        "{$normalizedInput['galaxy']}, " .
        "{$normalizedInput['system']}, " .
        "{$normalizedInput['planet']}, " .
        "{$normalizedInput['planetType']}, " .
        "'{$normalizedInput['customName']}' " .
        ") " .
        "; "
    );
    doquery($upsertShortcutQuery, 'fleet_shortcuts');

    return [
        'isSuccess' => true,
        'payload' => [],
    ];
}

?>
