<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\UnionManagement\Utils;

/**
 * @param array $props
 * @param array $props['input']
 * @param array $props['unionData']
 */
function updateUnionName($props) {
    $input = $props['input'];
    $unionData = $props['unionData'];

    $NAME_MIN_LENGTH = 4;

    if (empty($input['acs_name'])) {
        return null;
    }

    $newName = trim($input['acs_name']);
    $newName = preg_replace('#[^a-zA-Z'.REGEXP_POLISHSIGNS.'0-9\_\-\.\ \:]#si', '', $newName);

    if ($newName == $unionData['name']) {
        return null;
    }

    if (strlen($newName) < $NAME_MIN_LENGTH) {
        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'NAME_TOO_SHORT',
            ],
        ];
    }

    doquery("UPDATE {{table}} SET `name` = '{$newName}' WHERE `id` = {$unionData['id']};", 'acs');

    return [
        'isSuccess' => true,
        'payload' => [
            'unionName' => $newName,
        ],
    ];
}

?>
