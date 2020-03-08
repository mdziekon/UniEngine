<?php

use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Modules\Development\Components\LegacyQueue;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;

function BatimentBuildingPage(&$CurrentPlanet, $CurrentUser) {
    global $_Lang, $_GET, $_Vars_ElementCategories;

    $BuildingPage = '';

    includeLang('worldElements.detailed');

    CheckPlanetUsedFields($CurrentPlanet);

    $Now = time();

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $Now);

    // Handle Commands
    Development\Input\UserCommands\handleStructureCommand(
        $CurrentUser,
        $CurrentPlanet,
        $_GET,
        [
            "timestamp" => $Now
        ]
    );
    // End of - Handle Commands

    $buildingsQueue = Planets\Queues\Structures\parseQueueString($CurrentPlanet['buildQueue']);

    $queueComponent = LegacyQueue\render([
        'queue' => $buildingsQueue,
        'currentTimestamp' => $Now,

        'getQueueElementCancellationLinkHref' => function ($queueElement) {
            $queueElementIdx = $queueElement['queueElementIdx'];
            $listID = $queueElement['listID'];
            $isFirstQueueElement = ($queueElementIdx === 0);
            $cmd = ($isFirstQueueElement ? "cancel" : "remove");

            return buildHref([
                'path' => 'buildings.php',
                'query' => [
                    'cmd' => $cmd,
                    'listid' => $listID
                ]
            ]);
        }
    ]);

    $queueStateDetails = Development\Utils\getQueueStateDetails([
        'queue' => [
            'type' => Development\Utils\QueueType::Planetary,
            'content' => $buildingsQueue,
        ],
        'user' => $CurrentUser,
        'planet' => $CurrentPlanet,
    ]);
    $elementsInQueue = $queueStateDetails['queuedElementsCount'];
    $planetFieldsUsageCounter = 0;
    $hasElementsInQueue = ($elementsInQueue > 0);

    foreach ($queueStateDetails['queuedResourcesToUse'] as $resourceKey => $resourceValue) {
        if (Resources\isPlanetaryResource($resourceKey)) {
            $CurrentPlanet[$resourceKey] -= $resourceValue;
        } else if (Resources\isUserResource($resourceKey)) {
            $CurrentUser[$resourceKey] -= $resourceValue;
        }
    }
    foreach ($queueStateDetails['queuedElementLevelModifiers'] as $elementID => $elementLevelModifier) {
        $elementKey = Elements\getElementKey($elementID);
        $CurrentPlanet[$elementKey] += $elementLevelModifier;
        $planetFieldsUsageCounter += $elementLevelModifier;
    }

    $isUserOnVacation = isOnVacation($CurrentUser);
    $planetsMaxFieldsCount = CalculateMaxPlanetFields($CurrentPlanet);
    $hasAvailableFieldsOnPlanet = (
        ($CurrentPlanet['field_current'] + $planetFieldsUsageCounter) <
        $planetsMaxFieldsCount
    );
    $isQueueFull = (
        $elementsInQueue >=
        Users\getMaxStructuresQueueLength($CurrentUser)
    );

    foreach ($_Vars_ElementCategories['build'] as $elementID) {
        if (!Elements\isStructureAvailableOnPlanetType($elementID, $CurrentPlanet['planet_type'])) {
            continue;
        }

        $elementQueuedLevel = Elements\getElementState($elementID, $CurrentPlanet, $CurrentUser)['level'];
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

        $hasUpgradeResources = IsElementBuyable($CurrentUser, $CurrentPlanet, $elementID, false);

        $hasTechnologyRequirementMet = IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $elementID);
        $isBlockedByTechResearchProgress = (
            $elementID == 31 &&
            $CurrentUser['techQueue_Planet'] > 0 &&
            $CurrentUser['techQueue_EndTime'] > 0 &&
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

        $blockReason = [];
        $showInactiveUpgradeActionLink = false;

        if ($hasReachedMaxLevel) {
            $blockReason[] = $_Lang['ListBox_Disallow_MaxLevelReached'];
        }
        if ($isBlockedByTechResearchProgress) {
            $blockReason[] = $_Lang['ListBox_Disallow_LabResearch'];
        }
        if (!$hasAvailableFieldsOnPlanet) {
            $blockReason[] = $_Lang['ListBox_Disallow_NoFreeFields'];
        }
        if ($isQueueFull) {
            $blockReason[] = $_Lang['ListBox_Disallow_QueueIsFull'];
        }
        if ($isUserOnVacation) {
            $blockReason[] = $_Lang['ListBox_Disallow_VacationMode'];
        }
        if (!$hasUpgradeResources) {
            $showInactiveUpgradeActionLink = true;
        }

        $listElement = Development\Components\ListViewElementRow\render([
            'elementID' => $elementID,
            'user' => $CurrentUser,
            'planet' => $CurrentPlanet,
            'timestamp' => $Now,
            'isQueueActive' => $hasElementsInQueue,
            'elementDetails' => [
                'currentState' => $elementCurrentLevel,
                'isInQueue' => $isElementInQueue,
                'queueLevelModifier' => $elementQueueLevelModifier,
                'hasTechnologyRequirementMet' => $hasTechnologyRequirementMet,
                'isUpgradePossible' => $isUpgradePossible,
                'isUpgradeAvailableNow' => $isUpgradeAvailableNow,
                'isUpgradeQueueableNow' => $isUpgradeQueueableNow,
                'whyUpgradeImpossible' => (
                    !empty($blockReason) ?
                    [ end($blockReason) ] :
                    []
                ),
            ],
            'getUpgradeElementActionLinkHref' => function () use ($elementID) {
                return "?cmd=insert&amp;building={$elementID}";
            },
            'showInactiveUpgradeActionLink' => $showInactiveUpgradeActionLink,
        ]);

        $BuildingPage .= $listElement['componentHTML'];
    }

    foreach ($queueStateDetails['queuedResourcesToUse'] as $resourceKey => $resourceValue) {
        if (Resources\isPlanetaryResource($resourceKey)) {
            $CurrentPlanet[$resourceKey] += $resourceValue;
        } else if (Resources\isUserResource($resourceKey)) {
            $CurrentUser[$resourceKey] += $resourceValue;
        }
    }
    foreach ($queueStateDetails['queuedElementLevelModifiers'] as $elementID => $elementLevelModifier) {
        $elementKey = Elements\getElementKey($elementID);
        $CurrentPlanet[$elementKey] -= $elementLevelModifier;
    }

    $tplBodyCache = [
        'pageBody' => gettemplate('buildings_builds'),
    ];
    $parse = $_Lang;

    $parse['Insert_Overview_Fields_Used'] = $CurrentPlanet['field_current'];
    $parse['Insert_Overview_Fields_Max'] = $planetsMaxFieldsCount;
    $parse['Insert_Overview_Fields_Available'] = $planetsMaxFieldsCount - $CurrentPlanet['field_current'];

    $parse['PHPInject_QueueHTML'] = $queueComponent['componentHTML'];
    $parse['PHPInject_ElementsListHTML'] = $BuildingPage;

    display(
        parsetemplate($tplBodyCache['pageBody'], $parse),
        $_Lang['Builds']
    );
}

?>
