<?php

namespace UniEngine\Engine\Modules\Development\Screens\ResearchView;

use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

abstract class ResearchViewType {
    const Grid = 'ResearchViewType::Grid';
    const List = 'ResearchViewType::List';
}

//  Arguments
//      - $props (Object)
//          - pageType (Enum: ResearchViewType)
//          - input (Object)
//          - planet (&Object)
//              Reference needed because the internal PlanetResourceUpdate()
//              needs to persist the changes for the finishing display() call,
//              which uses global $_Planet.
//          - researchPlanet (&Object)
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
    $researchPlanet = &$props['researchPlanet'];
    $user = $props['user'];
    $currentTimestamp = $props['timestamp'];

    if (!is_array($researchPlanet)) {
        $researchPlanet = &$planet;
    }

    $elementsList = $_Vars_ElementCategories['tech'];

    $highlightElementID = null;

    $isUserOnVacation = isOnVacation($user);
    $hasResearchLab = Planets\Elements\hasResearchLab($planet);

    $researchNetworkStatus = Development\Utils\Research\fetchResearchNetworkStatus($user);
    $planetsWithUnfinishedLabUpgrades = [];

    // Preparations
    if (
        !isLabUpgradableWhileInUse() &&
        !empty($researchNetworkStatus['planetsWithLabInStructuresQueue'])
    ) {
        $planetsUpdateResult = Development\Utils\Research\updatePlanetsWithLabsInQueue(
            $user,
            [
                'planetsWithLabInStructuresQueueIDs' => $researchNetworkStatus['planetsWithLabInStructuresQueue'],
                'currentTimestamp' => $currentTimestamp,
            ]
        );

        $planetsWithUnfinishedLabUpgrades = $planetsUpdateResult['planetsWithUnfinishedLabUpgrades'];
    }

    $hasPlanetsWithUnfinishedLabUpgrades = !empty($planetsWithUnfinishedLabUpgrades);

    PlanetResourceUpdate($user, $planet, $currentTimestamp);

    // Handle user input
    $cmdResult = Development\Input\UserCommands\handleResearchCommand(
        $user,
        $researchPlanet,
        $input,
        [
            "timestamp" => $currentTimestamp,
            "currentPlanet" => $planet,
            "hasPlanetsWithUnfinishedLabUpgrades" => $hasPlanetsWithUnfinishedLabUpgrades,
        ]
    );
    if ($cmdResult['isSuccess']) {
        $highlightElementID = $cmdResult['payload']['elementID'];
    }

    // Handle queue display and data gathering
    $queueContent = Planets\Queues\Research\parseQueueString($researchPlanet['techQueue']);

    $queueStateDetails = Development\Utils\getQueueStateDetails([
        'queue' => [
            'type' => Development\Utils\QueueType::Research,
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
        $user[$elementKey] += $elementLevelModifier;
    }

    $elementsInQueue = $queueStateDetails['queuedElementsCount'];
    $hasElementsInQueue = ($elementsInQueue > 0);
    $isQueueFull = (
        $elementsInQueue >=
        Users\getMaxResearchQueueLength($user)
    );
    $isResearchInProgress = $hasElementsInQueue;
    $canQueueResearchOnThisPlanet = (
        !$isResearchInProgress ||
        $researchPlanet['id'] == $planet['id']
    );
    $isUpgradeBlockedByLabUpgradeInProgress = $hasPlanetsWithUnfinishedLabUpgrades;

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

        $hasTechnologyRequirementMet = IsTechnologieAccessible($user, $planet, $elementID);

        $isUpgradePossible = (!$hasReachedMaxLevel);
        $isUpgradeQueueable = (
            $isUpgradePossible &&
            !$isUserOnVacation &&
            !$isQueueFull &&
            $hasResearchLab &&
            $canQueueResearchOnThisPlanet &&
            $hasTechnologyRequirementMet &&
            !$isUpgradeBlockedByLabUpgradeInProgress
        );
        $isUpgradeAvailableNow = (
            $isUpgradeQueueable &&
            $hasUpgradeResources
        );
        $isUpgradeQueueableNow = (
            $isUpgradeQueueable &&
            $hasElementsInQueue
        );

        $upgradeBlockReasons = [
            'isUserOnVacation' => $isUserOnVacation,
            'isQueueFull' => $isQueueFull,
            'hasNoLab' => !$hasResearchLab,
            'isBlockedByLabUpgradeInProgress' => $isUpgradeBlockedByLabUpgradeInProgress,
            'hasInsufficientUpgradeResources' => !$hasUpgradeResources,
            'hasReachedMaxLevel' => $hasReachedMaxLevel,
            'hasUnmetTechnologyRequirements' => !$hasTechnologyRequirementMet,
            'hasOngoingResearchElsewhere' => !$canQueueResearchOnThisPlanet,
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
            'hasTechnologyRequirementMet' => $hasTechnologyRequirementMet,
        ];

        $elementsDetails[$elementID] = $elementDetails;
    }

    $viewComponent = null;
    $viewProps = [
        'planet' => $planet,
        'researchPlanet' => $researchPlanet,
        'user' => $user,
        'timestamp' => $currentTimestamp,
        'elementsDetails' => $elementsDetails,
        'highlightElementID' => $highlightElementID,
        'queueContent' => $queueContent,
        'isQueueActive' => $hasElementsInQueue,
        'canQueueResearchOnThisPlanet' => $canQueueResearchOnThisPlanet,
        'planetsWithUnfinishedLabUpgrades' => $planetsWithUnfinishedLabUpgrades,
        'researchNetworkStatus' => $researchNetworkStatus,
    ];

    if ($pageType === ResearchViewType::Grid) {
        $viewComponent = Components\GridView\render($viewProps);
    } else if ($pageType === ResearchViewType::List) {
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
        $user[$elementKey] -= $elementLevelModifier;
    }

    return [
        'componentHTML' => $viewComponent['componentHTML'],
    ];
}

?>
