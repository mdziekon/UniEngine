<?php

namespace UniEngine\Engine\Modules\Development\Screens\StructuresView;

use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

abstract class StructuresViewType {
    const Grid = 'StructuresViewType::Grid';
    const List = 'StructuresViewType::List';
}

//  Arguments
//      - $props (Object)
//          - pageType (Enum: StructuresViewType)
//          - input (Object)
//          - planet (&Object)
//              Reference needed because the internal PlanetResourceUpdate()
//              needs to persist the changes for the finishing display() call,
//              which uses global $_Planet.
//          - user (Object)
//          - timestamp (Number)
//
//  Returns: Object
//      - componentHTML (String)
//
function render ($props) {
    global $_Vars_ElementCategories;

    includeLang('worldElements.detailed');

    $pageType = $props['pageType'];
    $input = $props['input'];
    $planet = &$props['planet'];
    $user = $props['user'];
    $currentTimestamp = $props['timestamp'];

    $planetType = $planet['planet_type'];

    $elementsList = array_filter(
        $_Vars_ElementCategories['build'],
        function ($elementID) use ($planetType) {
            return Elements\isStructureAvailableOnPlanetType($elementID, $planetType);
        }
    );

    $highlightElementID = null;
    $planetFieldsUsageCounter = 0;

    // Preparations
    CheckPlanetUsedFields($planet);
    PlanetResourceUpdate($user, $planet, $currentTimestamp);

    $isUserOnVacation = isOnVacation($user);
    $planetsMaxFieldsCount = CalculateMaxPlanetFields($planet);

    // Handle user input
    $cmdResult = Development\Input\UserCommands\handleStructureCommand(
        $user,
        $planet,
        $input,
        [
            "timestamp" => $currentTimestamp
        ]
    );
    if ($cmdResult['isSuccess']) {
        $highlightElementID = $cmdResult['payload']['elementID'];
    }

    // Handle queue display and data gathering
    $queueContent = Planets\Queues\Structures\parseQueueString($planet['buildQueue']);

    $queueStateDetails = Development\Utils\getQueueStateDetails([
        'queue' => [
            'type' => Development\Utils\QueueType::Planetary,
            'content' => $queueContent,
        ],
        'user' => $user,
        'planet' => $planet,
    ]);

    // Apply queue modifiers to planet & user objects
    foreach ($queueStateDetails['queuedResourcesToUse'] as $resourceKey => $resourceValue) {
        if (Resources\isPlanetaryResource($resourceKey)) {
            $planet[$resourceKey] -= $resourceValue;
        } else if (Resources\isUserResource($resourceKey)) {
            $user[$resourceKey] -= $resourceValue;
        }
    }
    foreach ($queueStateDetails['queuedElementLevelModifiers'] as $elementID => $elementLevelModifier) {
        $elementKey = Elements\getElementKey($elementID);
        $planet[$elementKey] += $elementLevelModifier;
        $planetFieldsUsageCounter += $elementLevelModifier;
    }

    $hasAvailableFieldsOnPlanet = (
        ($planet['field_current'] + $planetFieldsUsageCounter) <
        $planetsMaxFieldsCount
    );
    $elementsInQueue = $queueStateDetails['queuedElementsCount'];
    $hasElementsInQueue = ($elementsInQueue > 0);
    $isQueueFull = (
        $elementsInQueue >=
        Users\getMaxStructuresQueueLength($user)
    );

    // Iterate through all available elements
    $elementsDetails = [];

    foreach ($elementsList as $elementID) {
        $elementQueuedLevel = Elements\getElementState($elementID, $planet, $user)['level'];
        $isElementInQueue = isset(
            $queueStateDetails['queuedElementLevelModifiers'][$elementID]
        );
        $elementQueueLevelModifier = (
            $isElementInQueue ?
            $queueStateDetails['queuedElementLevelModifiers'][$elementID] :
            0
        );
        $elementCurrentLevel = (
            $elementQueuedLevel +
            ($elementQueueLevelModifier * -1)
        );

        $elementMaxLevel = Elements\getElementMaxUpgradeLevel($elementID);
        $hasReachedMaxLevel = (
            $elementQueuedLevel >=
            $elementMaxLevel
        );

        $hasUpgradeResources = IsElementBuyable($user, $planet, $elementID, false);
        $hasDowngradeResources = IsElementBuyable($user, $planet, $elementID, true);

        $hasTechnologyRequirementMet = IsTechnologieAccessible($user, $planet, $elementID);
        $isBlockedByTechResearchProgress = (
            $elementID == 31 &&
            $user['techQueue_Planet'] > 0 &&
            $user['techQueue_EndTime'] > 0 &&
            !isLabUpgradableWhileInUse()
        );

        $isUpgradePossible = (!$hasReachedMaxLevel);
        $isUpgradeQueueable = (
            $isUpgradePossible &&
            !$isUserOnVacation &&
            !$isQueueFull &&
            $hasAvailableFieldsOnPlanet &&
            $hasTechnologyRequirementMet &&
            !$isBlockedByTechResearchProgress
        );
        $isUpgradeAvailableNow = (
            $isUpgradeQueueable &&
            $hasUpgradeResources
        );
        $isUpgradeQueueableNow = (
            $isUpgradeQueueable &&
            $hasElementsInQueue
        );

        $isDowngradePossible = (
            ($elementQueuedLevel > 0) &&
            !Elements\isIndestructibleStructure($elementID)
        );
        $isDowngradeQueueable = (
            $isDowngradePossible &&
            !$isUserOnVacation &&
            !$isQueueFull &&
            !$isBlockedByTechResearchProgress
        );
        $isDowngradeAvailableNow = (
            $isDowngradeQueueable &&
            $hasDowngradeResources
        );

        $upgradeBlockReasons = [
            'isUserOnVacation' => $isUserOnVacation,
            'isQueueFull' => $isQueueFull,
            'isBlockedByResearchInProgress' => $isBlockedByTechResearchProgress,
            'hasInsufficientUpgradeResources' => !$hasUpgradeResources,
            'hasReachedMaxLevel' => $hasReachedMaxLevel,
            'hasUnmetTechnologyRequirements' => !$hasTechnologyRequirementMet,
            'hasInsufficientPlanetFieldsLeft' => !$hasAvailableFieldsOnPlanet,
        ];

        $elementDetails = [
            'currentState' => $elementCurrentLevel,
            'isInQueue' => $isElementInQueue,
            'queueLevelModifier' => $elementQueueLevelModifier,
            'isUpgradePossible' => $isUpgradePossible,
            'isUpgradeQueueable' => $isUpgradeQueueable,
            'isUpgradeAvailableNow' => $isUpgradeAvailableNow,
            'isUpgradeQueueableNow' => $isUpgradeQueueableNow,
            'upgradeBlockReasons' => $upgradeBlockReasons,
            'isDowngradePossible' => $isDowngradePossible,
            'isDowngradeAvailable' => $isDowngradeAvailableNow,
            'isDowngradeQueueable' => $isDowngradeQueueable,
            'hasTechnologyRequirementMet' => $hasTechnologyRequirementMet,
        ];

        $elementsDetails[$elementID] = $elementDetails;
    }

    $viewComponent = null;
    $viewProps = [
        'planet' => $planet,
        'user' => $user,
        'timestamp' => $currentTimestamp,
        'elementsDetails' => $elementsDetails,
        'highlightElementID' => $highlightElementID,
        'queueContent' => $queueContent,
        'isQueueActive' => $hasElementsInQueue,
    ];

    if ($pageType === StructuresViewType::Grid) {
        $viewComponent = Components\GridView\render($viewProps);
    } else if ($pageType === StructuresViewType::List) {
        $viewComponent = Components\ListView\render($viewProps);
    }

    // Restore previous state of planet & user objects
    foreach ($queueStateDetails['queuedResourcesToUse'] as $resourceKey => $resourceValue) {
        if (Resources\isPlanetaryResource($resourceKey)) {
            $planet[$resourceKey] += $resourceValue;
        } else if (Resources\isUserResource($resourceKey)) {
            $user[$resourceKey] += $resourceValue;
        }
    }
    foreach ($queueStateDetails['queuedElementLevelModifiers'] as $elementID => $elementLevelModifier) {
        $elementKey = Elements\getElementKey($elementID);
        $planet[$elementKey] -= $elementLevelModifier;
    }

    return [
        'componentHTML' => $viewComponent['componentHTML'],
    ];
}

?>
