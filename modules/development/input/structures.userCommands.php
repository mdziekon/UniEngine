<?php

namespace UniEngine\Engine\Modules\Development\Input\UserCommands;

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
            $cmdResult = _handleStructureCommandCancel(
                $user,
                $planet,
                [ 'timestamp' => $timestamp ]
            );

            break;
        case 'remove':
            $cmdResult = _handleStructureCommandRemove(
                $user,
                $planet,
                $input,
                [ 'timestamp' => $timestamp ]
            );

            break;
        case "insert":
        case "destroy":
            $cmdResult = _handleStructureCommandInsert(
                $user,
                $planet,
                $input,
                [
                    'cmd' => $cmd,
                    'timestamp' => $timestamp
                ]
            );

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

function _handleStructureCommandCancel(&$user, &$planet, $params) {
    global $_EnginePath;

    $timestamp = $params['timestamp'];

    include($_EnginePath . 'includes/functions/RemoveBuildingFromQueue.php');
    include($_EnginePath . 'includes/functions/CancelBuildingFromQueue.php');

    $queueLength = Planets\Queues\Structures\getQueueLength($planet);

    if ($queueLength === 0) {
        return [
            'isSuccess' => false,
            'error' => [
                'queueEmpty' => true
            ]
        ];
    }

    $queueFirstElement = Planets\Queues\Structures\getFirstQueueElement($planet);

    if (!Elements\isCancellableOnceInProgress($queueFirstElement['elementID'])) {
        return [
            'isSuccess' => false,
            'error' => [
                'notCancellable' => true
            ]
        ];
    }

    $canceledElementID = CancelBuildingFromQueue(
        $planet,
        $user,
        [ 'currentTimestamp' => $timestamp ]
    );

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $canceledElementID
        ]
    ];
}

function _handleStructureCommandRemove(&$user, &$planet, &$input, $params) {
    global $_EnginePath;

    $timestamp = $params['timestamp'];

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

    $queueLength = Planets\Queues\Structures\getQueueLength($planet);

    // Indexing starts from 1
    if ($listElementIdx > $queueLength) {
        return [
            'isSuccess' => false,
            'error' => [
                'listIndexOutOfBound' => true
            ]
        ];
    }

    include($_EnginePath . 'includes/functions/RemoveBuildingFromQueue.php');

    $removedElementID = RemoveBuildingFromQueue(
        $planet,
        $user,
        $listElementIdx,
        [ 'currentTimestamp' => $timestamp ]
    );

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $removedElementID
        ]
    ];
}

//  Arguments:
//      - $user
//      - $planet
//      - $input
//      - $params (Object)
//          - cmd (EnumString: 'insert' | 'destroy')
//          - timestamp (Number)
//
function _handleStructureCommandInsert(&$user, &$planet, &$input, $params) {
    global $_EnginePath;

    $cmd = $params['cmd'];
    $timestamp = $params['timestamp'];

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
        $user['techQueue_Planet'] > 0 &&
        $user['techQueue_EndTime'] > 0 &&
        !isLabUpgradableWhileInUse()
    ) {
        return [
            'isSuccess' => false,
            'error' => [
                'researchInProgress' => true
            ]
        ];
    }

    $queueLength = Planets\Queues\Structures\getQueueLength($planet);
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

    AddBuildingToQueue(
        $planet,
        $user,
        $elementID,
        $purchaseMode,
        [ 'currentTimestamp' => $timestamp ]
    );

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $elementID
        ]
    ];
}

?>
