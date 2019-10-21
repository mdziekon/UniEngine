<?php

namespace UniEngine\Engine\Modules\Development\Input\UserCommands;

use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Includes\Helpers\Planets;

//  Arguments:
//      - $user
//      - $researchPlanet
//          When cancelling research, pass the planet where the research
//          is conducted.
//      - $input
//      - $params (Object)
//          - timestamp (Number)
//          - currentPlanet (Object)
//              The planet the user is currently at. May be different than "$researchPlanet".
//          - hasPlanetsWithUnfinishedLabUpgrades (Boolean)
//
function handleResearchCommand(&$user, &$researchPlanet, &$input, $params) {
    $knownCommands = [
        'search',
        'cancel',
    ];

    $timestamp = $params['timestamp'];
    $currentPlanet = $params['currentPlanet'];
    $hasPlanetsWithUnfinishedLabUpgrades = $params['hasPlanetsWithUnfinishedLabUpgrades'];

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

    $hasResearchLab = Planets\Elements\hasResearchLab($researchPlanet);

    if (!$hasResearchLab) {
        return [
            'isSuccess' => false,
            'error' => [
                'noLab' => true
            ]
        ];
    }

    if (
        !isLabUpgradableWhileInUse() &&
        $hasPlanetsWithUnfinishedLabUpgrades
    ) {
        return [
            'isSuccess' => false,
            'error' => [
                'labUpgrading' => true
            ]
        ];
    }

    switch ($cmd) {
        case "search":
            $cmdResult = _handleResearchCommandInsert(
                $user,
                $researchPlanet,
                $input,
                [
                    'timestamp' => $timestamp,
                    'currentPlanet' => $currentPlanet
                ]
            );

            break;
        case 'cancel':
            $cmdResult = _handleResearchCommandCancel(
                $user,
                $researchPlanet,
                $input,
                [ 'timestamp' => $timestamp ]
            );

            break;
    }

    if (!$cmdResult['isSuccess']) {
        return [
            'isSuccess' => false,
            'error' => $cmdResult['error']
        ];
    }

    $wasUserUpdated = (
        isset($cmdResult['payload']['wasUserUpdated']) ?
        $cmdResult['payload']['wasUserUpdated'] :
        false
    );

    $isDBDataSyncNotNeeded = HandlePlanetQueue_TechnologySetNext(
        $researchPlanet,
        $user,
        $timestamp,
        true
    );

    if (!$isDBDataSyncNotNeeded) {
        include($_EnginePath . 'includes/functions/PostResearchSaveChanges.php');

        $isResearchPlanetCurrentPlanet = ($researchPlanet['id'] == $currentPlanet['id']);

        PostResearchSaveChanges(
            $researchPlanet,
            $isResearchPlanetCurrentPlanet,
            (
                $wasUserUpdated ?
                $user :
                false
            )
        );
    }

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $cmdResult['payload']['elementID']
        ]
    ];
}

//  Arguments:
//      - $user
//      - $researchPlanet
//      - $input
//      - $params (Object)
//          - timestamp (Number)
//          - currentPlanet (Object)
//
function _handleResearchCommandInsert(&$user, &$researchPlanet, &$input, $params) {
    global $_EnginePath;

    $timestamp = $params['timestamp'];
    $currentPlanet = $params['currentPlanet'];

    $elementID = (
        isset($input['tech']) ?
        intval($input['tech']) :
        -1
    );

    if (!Elements\isTechnology($elementID)) {
        return [
            'isSuccess' => false,
            'error' => [
                'invalidElementID' => true
            ]
        ];
    }

    $queueLength = Planets\Queues\Research\getQueueLength($researchPlanet);
    $userMaxQueueLength = Users\getMaxResearchQueueLength($user);

    if ($queueLength >= $userMaxQueueLength) {
        return [
            'isSuccess' => false,
            'error' => [
                'queueFull' => true
            ]
        ];
    }

    if (
        $user['techQueue_EndTime'] > 0 &&
        $user['techQueue_Planet'] != $currentPlanet['id']
    ) {
        return [
            'isSuccess' => false,
            'error' => [
                'differentResearchPlanet' => true
            ]
        ];
    }

    include($_EnginePath.'includes/functions/TechQueue_Add.php');

    TechQueue_Add(
        $researchPlanet,
        $user,
        $elementID,
        [ "currentTimestamp" => $timestamp ]
    );

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $elementID
        ]
    ];
}

function _handleResearchCommandCancel(&$user, &$researchPlanet, &$input, $params) {
    global $_EnginePath;

    $timestamp = $params['timestamp'];

    $queueElementIdx = (
        isset($input['el']) ?
        intval($input['el']) :
        -1
    );

    if ($queueElementIdx < 0) {
        return [
            'isSuccess' => false,
            'error' => [
                'invalidListIndex' => true
            ]
        ];
    }

    $queueLength = Planets\Queues\Research\getQueueLength($researchPlanet);

    if ($queueLength <= $queueElementIdx) {
        return [
            'isSuccess' => false,
            'error' => [
                'listIndexOutOfBound' => true
            ]
        ];
    }

    if ($queueElementIdx === 0) {
        $queueFirstElement = Planets\Queues\Research\getFirstQueueElement($researchPlanet);

        if (!Elements\isCancellableOnceInProgress($queueFirstElement['elementID'])) {
            return [
                'isSuccess' => false,
                'error' => [
                    'notCancellable' => true
                ]
            ];
        }
    }

    $highlightElementID = null;

    if ($queueElementIdx === 0) {
        include($_EnginePath . 'includes/functions/TechQueue_Remove.php');
        include($_EnginePath . 'includes/functions/TechQueue_RemoveQueued.php');

        $highlightElementID = TechQueue_Remove(
            $researchPlanet,
            $user,
            [ "currentTimestamp" => $timestamp ]
        );
    } else {
        include($_EnginePath . 'includes/functions/TechQueue_RemoveQueued.php');

        $highlightElementID = TechQueue_RemoveQueued(
            $researchPlanet,
            $user,
            $queueElementIdx,
            [ "currentTimestamp" => $timestamp ]
        );
    }

    $wasUserUpdated = ($user['techQueue_Planet'] == '0');

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $highlightElementID,
            'wasUserUpdated' => $wasUserUpdated
        ]
    ];
}

?>
