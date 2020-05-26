<?php

namespace UniEngine\Engine\Modules\Admin\Screens\MoonCreationView\Utils;

//  Arguments:
//      - $input
//
function handleCommands(&$input) {
    global $_EnginePath;

    if (!isset($input['sent']) || $input['sent'] != '1') {
        return [
            'isSuccess' => null,
        ];
    }

    if (empty($input['planetID'])) {
        return [
            'isSuccess' => false,
            'error' => [
                'input' => [
                    'planetID' => [
                        'isEmpty' => true,
                    ],
                ],
            ],
        ];
    }

    $planetID = round(floatval($input['planetID']));

    if ($planetID <= 0) {
        return [
            'isSuccess' => false,
            'error' => [
                'input' => [
                    'planetID' => [
                        'isInvalid' => true,
                    ],
                ],
            ],
        ];
    }

    $moonName = (
        !empty($_POST['name']) ?
            $_POST['name'] :
            null
    );

    if (
        $moonName !== null &&
        preg_match(REGEXP_PLANETNAME_ABSOLUTE, $moonName) !== 1
    ) {
        return [
            'isSuccess' => false,
            'error' => [
                'input' => [
                    'name' => [
                        'isInvalid' => true,
                    ],
                ],
            ],
        ];
    }

    $query_GetPlanetRow = (
        "SELECT `galaxy`, `system`, `planet`, `id_owner` " .
        "FROM {{table}} " .
        "WHERE " .
        "`id` = {$planetID} " .
        "LIMIT 1 " .
        "; -- admin/addMoon.php - Query #1"
    );
    $result_GetPlanetRow = doquery($query_GetPlanetRow, 'planets', true);

    if (
        empty($result_GetPlanetRow) ||
        $result_GetPlanetRow['id_owner'] <= 0
    ) {
        return [
            'isSuccess' => false,
            'error' => [
                'planet' => [
                    'isInvalid' => true,
                ],
            ],
        ];
    }

    $moonDiameter = round(floatval($input['diameter']));

    if ($moonDiameter <= 0) {
        $moonDiameter = null;
    }

    include($_EnginePath . 'includes/functions/CreateOneMoonRecord.php');

    $moonCreationResult = CreateOneMoonRecord([
        'coordinates' => [
            'galaxy' => $result_GetPlanetRow['galaxy'],
            'system' => $result_GetPlanetRow['system'],
            'planet' => $result_GetPlanetRow['planet'],
        ],
        'ownerID' => $result_GetPlanetRow['id_owner'],
        'moonName' => $moonName,
        'moonCreationChance' => 20,
        'fixedDiameter' => $moonDiameter,
    ]);

    if ($moonCreationResult === false) {
        return [
            'isSuccess' => false,
            'error' => [
                'moon' => [
                    'alreadyExists' => true,
                ],
            ],
        ];
    }

    return [
        'isSuccess' => true,
        'payload' => [
            'moonID' => $moonCreationResult,
        ],
    ];
}

?>
