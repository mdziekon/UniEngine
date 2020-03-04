<?php

use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Modules\Development\Components\LegacyQueue;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\World\Elements;

function ResearchBuildingPage(&$CurrentPlanet, $CurrentUser, $ThePlanet) {
    global $_EnginePath, $_Lang, $_Vars_ElementCategories, $_GET;

    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    include($_EnginePath.'includes/functions/GetElementPrice.php');
    include($_EnginePath.'includes/functions/GetRestPrice.php');
    includeLang('worldElements.detailed');

    $Now = time();
    $isUserOnVacation = isOnVacation($CurrentUser);
    $hasResearchLab = Planets\Elements\hasResearchLab($CurrentPlanet);

    // Break on "no lab"
    if (!$hasResearchLab) {
        message($_Lang['no_laboratory'], $_Lang['Research']);
    }

    $researchNetworkStatus = Development\Utils\Research\fetchResearchNetworkStatus($CurrentUser);
    $planetsWithUnfinishedLabUpgrades = [];

    if (
        !isLabUpgradableWhileInUse() &&
        !empty($researchNetworkStatus['planetsWithLabInStructuresQueue'])
    ) {
        $planetsUpdateResult = Development\Utils\Research\updatePlanetsWithLabsInQueue(
            $CurrentUser,
            [
                'planetsWithLabInStructuresQueueIDs' => $researchNetworkStatus['planetsWithLabInStructuresQueue'],
                'currentTimestamp' => $Now
            ]
        );

        $planetsWithUnfinishedLabUpgrades = $planetsUpdateResult['planetsWithUnfinishedLabUpgrades'];
    }

    $hasPlanetsWithUnfinishedLabUpgrades = !empty($planetsWithUnfinishedLabUpgrades);

    if(!$hasPlanetsWithUnfinishedLabUpgrades) {
        $_Lang['Input_HideNoResearch'] = 'display: none;';
    } else {
        $LabInQueueAt = array_map(
            function ($planet) {
                return "{$planet['name']} [{$planet['galaxy']}:{$planet['system']}:{$planet['planet']}]";
            },
            $planetsWithUnfinishedLabUpgrades
        );

        $_Lang['labo_on_update'] = sprintf($_Lang['labo_on_update'], implode(', ', $LabInQueueAt));
    }

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $Now);

    if(is_array($ThePlanet))
    {
        $ResearchPlanet = &$ThePlanet;
    }
    else
    {
        $ResearchPlanet = &$CurrentPlanet;
    }

    // Handle Commands
    UniEngine\Engine\Modules\Development\Input\UserCommands\handleResearchCommand(
        $CurrentUser,
        $ResearchPlanet,
        $_GET,
        [
            "timestamp" => $Now,
            "currentPlanet" => $CurrentPlanet,
            "hasPlanetsWithUnfinishedLabUpgrades" => $hasPlanetsWithUnfinishedLabUpgrades
        ]
    );
    // End of - Handle Commands

    $researchQueue = Planets\Queues\Research\parseQueueString($ResearchPlanet['techQueue']);
    $researchQueueLength = count($researchQueue);

    $queueComponent = LegacyQueue\render([
        'queue' => $researchQueue,
        'currentTimestamp' => $Now,

        'getQueueElementCancellationLinkHref' => function ($queueElement) {
            $listID = $queueElement['listID'];

            return buildHref([
                'path' => 'buildings.php',
                'query' => [
                    'mode' => 'research',
                    'cmd' => 'cancel',
                    'el' => ($listID - 1)
                ]
            ]);
        }
    ]);

    $queueStateDetails = Development\Utils\getQueueStateDetails([
        'queue' => [
            'type' => Development\Utils\QueueType::Research,
            'content' => $researchQueue,
        ],
        'user' => $CurrentUser,
        'planet' => $CurrentPlanet,
    ]);

    $elementsInQueue = $queueStateDetails['queuedElementsCount'];
    $isQueueFull = ($researchQueueLength >= Users\getMaxResearchQueueLength($CurrentUser));
    $hasElementsInQueue = ($elementsInQueue > 0);
    $isResearchInProgress = ($researchQueueLength > 0);
    $canQueueResearchOnThisPlanet = (
        !$isResearchInProgress ||
        $ResearchPlanet['id'] == $CurrentPlanet['id']
    );
    $isUpgradeBlockedByLabUpgradeInProgress = $hasPlanetsWithUnfinishedLabUpgrades;

    foreach ($queueStateDetails['queuedResourcesToUse'] as $resourceKey => $resourceValue) {
        if (Resources\isPlanetaryResource($resourceKey)) {
            $CurrentPlanet[$resourceKey] -= $resourceValue;
        } else if (Resources\isUserResource($resourceKey)) {
            $CurrentUser[$resourceKey] -= $resourceValue;
        }
    }
    foreach ($queueStateDetails['queuedElementLevelModifiers'] as $elementID => $elementLevelModifier) {
        $elementKey = Elements\getElementKey($elementID);
        $CurrentUser[$elementKey] += $elementLevelModifier;
    }

    $TechnoList = '';
    foreach($_Vars_ElementCategories['tech'] as $elementID) {
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

        $blockReason = [];
        $showInactiveUpgradeActionLink = false;

        if ($hasReachedMaxLevel) {
            $blockReason[] = $_Lang['ListBox_Disallow_MaxLevelReached'];
        }
        if (!$hasResearchLab) {
            $blockReason[] = $_Lang['ListBox_Disallow_NoLab'];
        }
        if ($isQueueFull) {
            $blockReason[] = $_Lang['ListBox_Disallow_QueueIsFull'];
        }
        if ($isUserOnVacation) {
            $blockReason[] = $_Lang['ListBox_Disallow_VacationMode'];
        }
        if ($isUpgradeQueueable && !$hasUpgradeResources) {
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
                'isUpgradeAvailableNow' => $isUpgradeAvailableNow,
                'isUpgradeQueueableNow' => $isUpgradeQueueableNow,
                'whyUpgradeImpossible' => (
                    !empty($blockReason) ?
                    [ end($blockReason) ] :
                    []
                ),
            ],
            'getUpgradeElementActionLinkHref' => function () use ($elementID) {
                return "buildings.php?mode=research&amp;cmd=search&amp;tech={$elementID}";
            },
            'showInactiveUpgradeActionLink' => $showInactiveUpgradeActionLink,
        ]);

        $TechnoList .= $listElement['componentHTML'];
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
        $CurrentUser[$elementKey] -= $elementLevelModifier;
    }

    $PageParse = $_Lang;
    $PageParse['technolist'] = $TechnoList;
    $PageParse['Data_QueueComponentHTML'] = $queueComponent['componentHTML'];

    if (!$canQueueResearchOnThisPlanet) {
        $PageParse['Insert_QueueInfo'] = parsetemplate(
            gettemplate('_singleRow'),
            [
                'Classes' => 'pad5 red',
                'Colspan' => 3,
                'Text' => (
                    $_Lang['Queue_ResearchOn'] .
                    ' ' .
                    "{$ResearchPlanet['name']} [{$ResearchPlanet['galaxy']}:{$ResearchPlanet['system']}:{$ResearchPlanet['planet']}]"
                )
            ]
        );
    }

    display(parsetemplate(gettemplate('buildings_research'), $PageParse), $_Lang['Research']);
}

?>
