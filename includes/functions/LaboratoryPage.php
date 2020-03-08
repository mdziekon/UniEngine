<?php

use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Modules\Development\Components\ModernQueue;
use UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernQueuePlanetInfo;
use UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernQueueLabUpgradeInfo;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\World\Elements;

function LaboratoryPage(&$CurrentPlanet, $CurrentUser, $InResearch, $ThePlanet)
{
    global $_EnginePath, $_Lang, $_Vars_ElementCategories, $_SkinPath, $_GET;

    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    includeLang('worldElements.detailed');

    $Now = time();
    $Parse = &$_Lang;
    $ShowElementID = 0;

    // Constants
    $ElementsPerRow = 7;

    // Get Templates
    $tplBodyCache = [
        'pageBody' => gettemplate('buildings_compact_body_lab'),
        'list_hidden' => gettemplate('buildings_compact_list_hidden'),
        'list_row' => gettemplate('buildings_compact_list_row'),
        'list_breakrow' => gettemplate('buildings_compact_list_breakrow'),
    ];

    $isUserOnVacation = isOnVacation($CurrentUser);
    $hasResearchLab = Planets\Elements\hasResearchLab($CurrentPlanet);

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

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $Now);

    if (is_array($ThePlanet)) {
        $ResearchPlanet = &$ThePlanet;
    } else {
        $ResearchPlanet = &$CurrentPlanet;
    }

    // Handle Commands
    $cmdResult = UniEngine\Engine\Modules\Development\Input\UserCommands\handleResearchCommand(
        $CurrentUser,
        $ResearchPlanet,
        $_GET,
        [
            "timestamp" => $Now,
            "currentPlanet" => $CurrentPlanet,
            "hasPlanetsWithUnfinishedLabUpgrades" => $hasPlanetsWithUnfinishedLabUpgrades
        ]
    );

    if ($cmdResult['isSuccess']) {
        $ShowElementID = $cmdResult['payload']['elementID'];
    }
    // End of - Handle Commands

    $techQueueContent = Planets\Queues\Research\parseQueueString(
        $ResearchPlanet['techQueue']
    );

    $planetInfoComponent = ModernQueuePlanetInfo\render([
        'currentPlanet'     => &$CurrentPlanet,
        'researchPlanet'    => &$ResearchPlanet,
        'queue'             => $techQueueContent,
        'timestamp'         => $Now,
    ]);
    $labsUpgradeInfoComponent = ModernQueueLabUpgradeInfo\render([
        'planetsWithUnfinishedLabUpgrades' => $planetsWithUnfinishedLabUpgrades
    ]);

    $queueComponent = ModernQueue\render([
        'user'              => &$CurrentUser,
        'planet'            => &$ResearchPlanet,
        'queue'             => $techQueueContent,
        'queueMaxLength'    => Users\getMaxResearchQueueLength($CurrentUser),
        'timestamp'         => $Now,
        'infoComponents'    => [
            $planetInfoComponent['componentHTML'],
            $labsUpgradeInfoComponent['componentHTML']
        ],
        'isQueueEmptyInfoHidden' => (
            !empty($labsUpgradeInfoComponent['componentHTML'])
        ),

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
            'content' => $techQueueContent,
        ],
        'user' => $CurrentUser,
        'planet' => $CurrentPlanet,
    ]);
    $elementsInQueue = $queueStateDetails['queuedElementsCount'];
    $isQueueFull = (
        $elementsInQueue >=
        Users\getMaxResearchQueueLength($CurrentUser)
    );
    $hasElementsInQueue = ($elementsInQueue > 0);
    $canQueueResearchOnThisPlanet = (
        !$InResearch ||
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
    // End of - Parse Queue

    $elementsIconComponents = [];

    foreach ($_Vars_ElementCategories['tech'] as $ElementID) {
        $elementQueuedLevel = Elements\getElementState($ElementID, $CurrentPlanet, $CurrentUser)['level'];
        $isElementInQueue = isset(
            $queueStateDetails['queuedElementLevelModifiers'][$ElementID]
        );
        $elementQueueLevelModifier = (
            $isElementInQueue ?
            $queueStateDetails['queuedElementLevelModifiers'][$ElementID] :
            0
        );
        $elementCurrentLevel = (
            $elementQueuedLevel +
            ($elementQueueLevelModifier * -1)
        );

        $elementMaxLevel = Elements\getElementMaxUpgradeLevel($ElementID);
        $hasReachedMaxLevel = ($elementQueuedLevel >= $elementMaxLevel);

        $hasUpgradeResources = IsElementBuyable($CurrentUser, $CurrentPlanet, $ElementID, false);

        $hasTechnologyRequirementMet = IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $ElementID);

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

        $BlockReason = [];

        if (!$hasUpgradeResources) {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoResources'];
        }
        if ($hasReachedMaxLevel) {
            $BlockReason[] = $_Lang['ListBox_Disallow_MaxLevelReached'];
        }
        if (!$hasTechnologyRequirementMet) {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoTech'];
        }
        if ($isQueueFull) {
            $BlockReason[] = $_Lang['ListBox_Disallow_QueueIsFull'];
        }
        if (!$hasResearchLab) {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoLab'];
        }
        if (!$canQueueResearchOnThisPlanet) {
            $BlockReason[] = $_Lang['ListBox_Disallow_NotThisLab'];
        }
        if ($isUpgradeBlockedByLabUpgradeInProgress) {
            $BlockReason[] = $_Lang['ListBox_Disallow_LabInQueue'];
        }
        if ($isUserOnVacation) {
            $BlockReason[] = $_Lang['ListBox_Disallow_VacationMode'];
        }

        $iconComponent = Development\Components\GridViewElementIcon\render([
            'elementID' => $ElementID,
            'elementDetails' => [
                'currentState' => $elementCurrentLevel,
                'queueLevelModifier' => $elementQueueLevelModifier,
                'isInQueue' => $isElementInQueue,
                'isUpgradeAvailableNow' => $isUpgradeAvailableNow,
                'isUpgradeQueueableNow' => $isUpgradeQueueableNow,
                'whyUpgradeImpossible' => [ end($BlockReason) ],
            ],
            'getUpgradeElementActionLinkHref' => function () use ($ElementID) {
                return "?mode=research&amp;cmd=search&amp;tech={$ElementID}";
            },
        ]);

        $cardInfoComponent = Development\Components\GridViewElementCard\render([
            'elementID' => $ElementID,
            'user' => $CurrentUser,
            'planet' => $CurrentPlanet,
            'isQueueActive' => $hasElementsInQueue,
            'elementDetails' => [
                'currentState' => $elementCurrentLevel,
                'isInQueue' => $isElementInQueue,
                'queueLevelModifier' => $elementQueueLevelModifier,
                'isUpgradePossible' => $isUpgradePossible,
                'isUpgradeAvailable' => $isUpgradeAvailableNow,
                'isUpgradeQueueable' => $isUpgradeQueueable,
                'whyUpgradeImpossible' => [
                    (
                        $hasReachedMaxLevel ?
                        $_Lang['ListBox_Disallow_MaxLevelReached'] :
                        ''
                    ),
                ],
                'isDowngradePossible' => false,
                'isDowngradeAvailable' => false,
                'isDowngradeQueueable' => false,
                'hasTechnologyRequirementMet' => $hasTechnologyRequirementMet,
                'additionalUpgradeDetailsRows' => [],
            ],
            'getUpgradeElementActionLinkHref' => function () use ($ElementID) {
                return "?mode=research&amp;cmd=search&amp;tech={$ElementID}";
            },
            'getDowngradeElementActionLinkHref' => function () {
                return '';
            },
        ]);

        $elementsIconComponents[] = $iconComponent['componentHTML'];
        $InfoBoxes[] = $cardInfoComponent['componentHTML'];
    }

    // Restore resources & element levels to previous values
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

    // Create List
    $groupedIcons = Common\Collections\groupInRows($elementsIconComponents, $ElementsPerRow);
    $groupedIconRows = array_map(
        function ($elementsInRow) use (&$tplBodyCache, $ElementsPerRow) {
            $mergedElementsInRow = implode('', $elementsInRow);
            $emptySpaceFiller = '';

            $elementsInRowCount = count($elementsInRow);

            if ($elementsInRowCount < $ElementsPerRow) {
                $emptySpaceFiller = str_repeat(
                    $tplBodyCache['list_hidden'],
                    ($ElementsPerRow - $elementsInRowCount)
                );
            }

            return parsetemplate(
                $tplBodyCache['list_row'],
                [
                    'Elements' => ($mergedElementsInRow . $emptySpaceFiller)
                ]
            );
        },
        $groupedIcons
    );

    $Parse['Create_Queue'] = $queueComponent['componentHTML'];
    $Parse['Create_StructuresList'] = implode(
        $tplBodyCache['list_breakrow'],
        $groupedIconRows
    );
    $Parse['Create_ElementsInfoBoxes'] = implode('', $InfoBoxes);
    $Parse['Create_ShowElementOnStartup'] = (
        $ShowElementID > 0 ?
        $ShowElementID :
        ''
    );
    $Parse['Insert_SkinPath'] = $_SkinPath;
    $Parse['Insert_PlanetImg'] = $CurrentPlanet['image'];
    $Parse['Insert_PlanetType'] = $_Lang['PlanetType_'.$CurrentPlanet['planet_type']];
    $Parse['Insert_PlanetName'] = $CurrentPlanet['name'];
    $Parse['Insert_PlanetPos_Galaxy'] = $CurrentPlanet['galaxy'];
    $Parse['Insert_PlanetPos_System'] = $CurrentPlanet['system'];
    $Parse['Insert_PlanetPos_Planet'] = $CurrentPlanet['planet'];
    $Parse['Insert_Overview_LabLevel'] = Elements\getElementState(31, $CurrentPlanet, $CurrentUser)['level'];
    $Parse['Insert_Overview_LabsConnected'] = prettyNumber($researchNetworkStatus['connectedLabsCount']);
    $Parse['Insert_Overview_TotalLabsCount'] = prettyNumber($researchNetworkStatus['allLabsCount']);
    $Parse['Insert_Overview_LabPower'] = prettyNumber($researchNetworkStatus['connectedLabsLevel']);
    $Parse['Insert_Overview_LabPowerTotal'] = prettyNumber($researchNetworkStatus['allLabsLevel']);

    $Page = parsetemplate($tplBodyCache['pageBody'], $Parse);

    display($Page, $_Lang['Research']);
}

?>
