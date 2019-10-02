<?php

namespace UniEngine\Engine\Modules\Structures\Input\UserCommands;

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

//  Arguments:
//      - $user
//      - $planet
//      - $input
//      - $params (Object)
//          - timestamp (Number)
//
function handleStructureCommand(&$user, &$planet, &$input, $params) {
    $knownCommands = [
        'cancel',
        'remove',
        'insert',
        'destroy'
    ];

    $timestamp = $params['timestamp'];

    $cmd = (
        isset($input['cmd']) ?
        $input['cmd'] :
        null
    );

    if (!in_array($cmd, $knownCommands)) {
        return [
            'isSuccess' => false,
            'error' => [
                'invalidCommand' => true
            ]
        ];
    }

    if (isOnVacation($user)) {
        return [
            'isSuccess' => false,
            'error' => [
                'onVacation' => true
            ]
        ];
    }

    switch ($cmd) {
        case "cancel":
            $cmdResult = _handleStructureCommandCancel($user, $planet);

            break;
        case 'remove':
            $cmdResult = _handleStructureCommandRemove($user, $planet, $input);

            break;
        case "insert":
        case "destroy":
            $cmdResult = _handleStructureCommandInsert($user, $planet, $input, [
                'cmd' => $cmd
            ]);

            break;
    }

    if (!$cmdResult['isSuccess']) {
        return [
            'isSuccess' => false,
            'error' => $cmdResult['error']
        ];
    }

    $isPlanetRecordSyncNotNeeded = HandlePlanetQueue_StructuresSetNext(
        $planet,
        $user,
        $timestamp,
        true
    );

    if (!$isPlanetRecordSyncNotNeeded) {
        include($_EnginePath . 'includes/functions/BuildingSavePlanetRecord.php');

        BuildingSavePlanetRecord($planet);
    }

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $cmdResult['payload']['elementID']
        ]
    ];
}

function _handleStructureCommandCancel(&$user, &$planet) {
    global $_EnginePath;

    include($_EnginePath . 'includes/functions/CancelBuildingFromQueue.php');

    $highlightElementID = CancelBuildingFromQueue($planet, $user);

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $highlightElementID
        ]
    ];
}

function _handleStructureCommandRemove(&$user, &$planet, &$input) {
    global $_EnginePath;

    $listElementIdx = (
        isset($input['listid']) ?
        intval($input['listid']) :
        -1
    );

    if ($listElementIdx <= 1) {
        return [
            'isSuccess' => false,
            'error' => [
                'invalidListIndex' => true
            ]
        ];
    }

    include($_EnginePath . 'includes/functions/RemoveBuildingFromQueue.php');

    $highlightElementID = RemoveBuildingFromQueue($planet, $user, $listElementIdx);

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $highlightElementID
        ]
    ];
}

//  Arguments:
//      - $user
//      - $planet
//      - $input
//      - $params (Object)
//          - cmd (EnumString: 'insert' | 'destroy')
//
function _handleStructureCommandInsert(&$user, &$planet, &$input, $params) {
    global $_EnginePath;

    $cmd = $params['cmd'];

    $elementID = (
        isset($input['building']) ?
        intval($input['building']) :
        -1
    );

    if (!Elements\isStructure($elementID)) {
        return [
            'isSuccess' => false,
            'error' => [
                'invalidElementID' => true
            ]
        ];
    }

    if (!Elements\isStructureAvailableOnPlanetType($elementID, $planet['planet_type'])) {
        return [
            'isSuccess' => false,
            'error' => [
                'structureUnavailable' => true
            ]
        ];
    }

    if (
        $elementID == 31 &&
        $planet['techQueue_Planet'] > 0 &&
        $planet['techQueue_EndTime'] > 0 &&
        !isLabUpgradableWhileInUse()
    ) {
        return [
            'isSuccess' => false,
            'error' => [
                'researchInProgress' => true
            ]
        ];
    }

    $queueLength = Planets\Queues\getQueueLength($planet);
    $userMaxQueueLength = Users\getMaxStructuresQueueLength($user);

    if ($queueLength >= $userMaxQueueLength) {
        return [
            'isSuccess' => false,
            'error' => [
                'queueFull' => true
            ]
        ];
    }

    $purchaseMode = ($cmd === 'insert');

    include($_EnginePath.'includes/functions/AddBuildingToQueue.php');

    AddBuildingToQueue($planet, $user, $elementID, $purchaseMode);

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $elementID
        ]
    ];
}

?>
