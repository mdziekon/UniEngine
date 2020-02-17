<?php

namespace UniEngine\Engine\Modules\Development\Screens\ResearchListPage;

use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Modules\Development\Input;
use UniEngine\Engine\Modules\Development\Components\ModernQueue;
use UniEngine\Engine\Modules\Development\Components\LegacyQueue;
use UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernQueuePlanetInfo;
use UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernElementListIcon;
use UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernElementInfoCard;
use UniEngine\Engine\Modules\Development\Screens\ResearchListPage\LegacyElementListItem;

function render (&$CurrentPlanet, $CurrentUser, $ResearchPlanet) {
    global $_Lang, $_SkinPath, $_GET, $_EnginePath, $_Vars_ElementCategories;

    // Initialisation
    include($_EnginePath . 'includes/functions/GetElementTechReq.php');
    includeLang('worldElements.detailed');

    // - Constants
    $const_ElementsPerRow = 7;

    // - Templates
    $tplBodyCache = [
        'list_hidden'   => gettemplate('buildings_compact_list_hidden'),
        'list_row'      => gettemplate('buildings_compact_list_row'),
        'list_breakrow' => gettemplate('buildings_compact_list_breakrow'),
    ];

    // - Variables
    $currentTimestamp = time();
    $Parse = &$_Lang;
    $highlightElementID = 0;

    $isModernLayoutEnabled = (
        $CurrentUser['settings_DevelopmentOld'] != 1 ?
        true :
        false
    );

    $researchNetworkStatus = Helpers\getResearchNetworkStatus($CurrentUser);
    $planetsWithUnfinishedLabUpgrades = [];

    if (
        !isLabUpgradableWhileInUse() &&
        !empty($researchNetworkStatus['planetsWithLabInStructuresQueue'])
    ) {
        $planetsUpdateResult = Helpers\updatePlanetsWithLabsInQueue(
            $CurrentUser,
            [
                'planetsWithLabInStructuresQueueIDs' => $researchNetworkStatus['planetsWithLabInStructuresQueue'],
                'currentTimestamp' => $currentTimestamp
            ]
        );

        $planetsWithUnfinishedLabUpgrades = $planetsUpdateResult['planetsWithUnfinishedLabUpgrades'];
    }

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $currentTimestamp);

    $planetConductingResearch = &$CurrentPlanet;

    if (is_array($ResearchPlanet)) {
        $planetConductingResearch = &$ResearchPlanet;
    }

    $isConductingResearch = Users\isConductingResearch($CurrentUser);
    $hasResearchLabOnCurrentPlanet = Planets\Elements\hasResearchLab($CurrentPlanet);
    $isConductingResearchOnCurrentPlanet = (
        $isConductingResearch &&
        $planetConductingResearch['id'] === $CurrentPlanet['id']
    );
    $hasPlanetsWithUnfinishedLabUpgrades = !empty($planetsWithUnfinishedLabUpgrades);

    // Handle Commands
    $cmdResult = Input\UserCommands\handleResearchCommand(
        $CurrentUser,
        $planetConductingResearch,
        $_GET,
        [
            "timestamp" => $currentTimestamp,
            "currentPlanet" => $CurrentPlanet,
            "hasPlanetsWithUnfinishedLabUpgrades" => $hasPlanetsWithUnfinishedLabUpgrades
        ]
    );

    if ($cmdResult['isSuccess']) {
        $highlightElementID = $cmdResult['payload']['elementID'];
    }
    // End of - Handle Commands

    // Gather queue state details
    $queueStateDetails = Development\Utils\getQueueStateDetails([
        'queue' => [
            'type' => Development\Utils\QueueType::Research,
            'content' => Planets\Queues\Research\parseQueueString(
                $planetConductingResearch['techQueue']
            ),
        ],
        'user' => $CurrentUser,
        'planet' => $CurrentPlanet,
    ]);
    $elementsInQueue = $queueStateDetails['queuedElementsCount'];

    // Apply queue modifiers
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

    // Parse all available structures
    $hasElementsInQueue = ($elementsInQueue > 0);
    $isOnVacation = isOnVacation($CurrentUser);
    $isQueueFull = (
        $elementsInQueue >=
        Users\getMaxResearchQueueLength($CurrentUser)
    );
    $isResearchBlockedByLabUpgradeInProgress = (
        !isLabUpgradableWhileInUse() &&
        $hasPlanetsWithUnfinishedLabUpgrades
    );

    $elementsListIcons = [];
    $elementsListDetailedInfoboxes = [];
    $elementsListItems = [];

    foreach ($_Vars_ElementCategories['tech'] as $elementID) {
        $elementCurrentQueuedLevel = Elements\getElementCurrentLevel($elementID, $CurrentPlanet, $CurrentUser);
        $elementMaxLevel = Elements\getElementMaxUpgradeLevel($elementID);
        $isInQueue = isset($queueStateDetails['queuedElementLevelModifiers'][$elementID]);
        $elementQueueLevelModifier = (
            $isInQueue ?
            $queueStateDetails['queuedElementLevelModifiers'][$elementID] :
            0
        );
        $elementCurrentLevel = (
            $elementCurrentQueuedLevel -
            $elementQueueLevelModifier
        );
        $hasReachedMaxLevel = ($elementCurrentQueuedLevel >= $elementMaxLevel);
        $hasTechnologyRequirementsMet = IsTechnologieAccessible(
            $CurrentUser,
            $CurrentPlanet,
            $elementID
        );

        $isUpgradeable = (!$hasReachedMaxLevel);

        $hasUpgradeResources = (
            $isUpgradeable ?
            IsElementBuyable($CurrentUser, $CurrentPlanet, $elementID, false) :
            null
        );

        // Perform all necessary tests to determine if actions are possible
        $isUpgradeHardBlocked = (
            !$isUpgradeable ||
            !$hasResearchLabOnCurrentPlanet ||
            $isResearchBlockedByLabUpgradeInProgress ||
            (
                // TODO: This should also be properly checked in input handling
                $isConductingResearch &&
                !$isConductingResearchOnCurrentPlanet
            )
        );
        $canStartUpgrade = (
            !$isUpgradeHardBlocked &&
            $hasUpgradeResources &&
            $hasTechnologyRequirementsMet &&
            !$isQueueFull &&
            !$isOnVacation
        );
        $canQueueUpgrade = (
            !$isUpgradeHardBlocked &&
            ($hasUpgradeResources || $hasElementsInQueue) &&
            $hasTechnologyRequirementsMet &&
            !$isQueueFull &&
            !$isOnVacation
        );

        $upgradeBlockers = [];

        if ($hasReachedMaxLevel) {
            $upgradeBlockers[] = $_Lang['ListBox_Disallow_MaxLevelReached'];
        }
        if ($isUpgradeable && !$hasUpgradeResources) {
            $upgradeBlockers[] = $_Lang['ListBox_Disallow_NoResources'];
        }
        if (!$hasTechnologyRequirementsMet) {
            $upgradeBlockers[] = $_Lang['ListBox_Disallow_NoTech'];
        }
        if (!$hasResearchLabOnCurrentPlanet) {
            $upgradeBlockers[] = $_Lang['ListBox_Disallow_NoLab'];
        }
        if (
            $isConductingResearch &&
            !$isConductingResearchOnCurrentPlanet
        ) {
            $upgradeBlockers[] = $_Lang['ListBox_Disallow_NotThisLab'];
        }
        if ($isResearchBlockedByLabUpgradeInProgress) {
            $upgradeBlockers[] = $_Lang['ListBox_Disallow_LabInQueue'];
        }
        if ($isQueueFull) {
            $upgradeBlockers[] = $_Lang['ListBox_Disallow_QueueIsFull'];
        }
        if ($isOnVacation) {
            $upgradeBlockers[] = $_Lang['ListBox_Disallow_VacationMode'];
        }

        if ($isModernLayoutEnabled) {
            $elementListIcon = ModernElementListIcon\render([
                'elementID' => $elementID,
                'elementCurrentLevel' => $elementCurrentLevel,
                'elementQueueLevelModifier' => $elementQueueLevelModifier,
                'isInQueue' => $isInQueue,
                'canStartUpgrade' => $canStartUpgrade,
                'canQueueUpgrade' => $canQueueUpgrade,
                'upgradeBlockersList' => $upgradeBlockers
            ]);
            $elementInfoCard = ModernElementInfoCard\render([
                'user' => $CurrentUser,
                'planet' => $CurrentPlanet,
                'elementID' => $elementID,
                'elementCurrentLevel' => $elementCurrentLevel,
                'elementQueueLevelModifier' => $elementQueueLevelModifier,
                'isQueueActive' => $hasElementsInQueue,
                'isInQueue' => $isInQueue,
                'isUpgradeable' => $isUpgradeable,
                'isUpgradeHardBlocked' => $isUpgradeHardBlocked,
                'hasReachedMaxLevel' => $hasReachedMaxLevel,
                'hasTechnologyRequirementsMet' => $hasTechnologyRequirementsMet,
                'canStartUpgrade' => $canStartUpgrade,
                'canQueueUpgrade' => $canQueueUpgrade,
            ]);

            $elementsListIcons[] = $elementListIcon['componentHTML'];
            $elementsListDetailedInfoboxes[] = $elementInfoCard['componentHTML'];
        } else {
            $elementListItem = LegacyElementListItem\render([
                'user' => $CurrentUser,
                'planet' => $CurrentPlanet,
                'currentTimestamp' => $currentTimestamp,
                'elementID' => $elementID,
                'elementCurrentLevel' => $elementCurrentLevel,
                'elementQueueLevelModifier' => $elementQueueLevelModifier,
                'isQueueActive' => $hasElementsInQueue,
                'isInQueue' => $isInQueue,
                'isUpgradeable' => $isUpgradeable,
                'hasTechnologyRequirementsMet' => $hasTechnologyRequirementsMet,
                'canStartUpgrade' => $canStartUpgrade,
                'canQueueUpgrade' => $canQueueUpgrade,
                'upgradeBlockersList' => $upgradeBlockers
            ]);

            $elementsListItems[] = $elementListItem['componentHTML'];
        }
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

    if ($isModernLayoutEnabled) {
        // Create Structures List
        $groupedStructureRows = Common\Utils\groupInRows($elementsListIcons, $const_ElementsPerRow);
        $parsedStructureRows = array_map(
            function ($elementsInRow) use (&$tplBodyCache, $const_ElementsPerRow) {
                $mergedElementsInRow = implode('', $elementsInRow);
                $emptySpaceFiller = '';

                $elementsInRowCount = count($elementsInRow);

                if ($elementsInRowCount < $const_ElementsPerRow) {
                    $emptySpaceFiller = str_repeat(
                        $tplBodyCache['list_hidden'],
                        ($const_ElementsPerRow - $elementsInRowCount)
                    );
                }

                return parsetemplate(
                    $tplBodyCache['list_row'],
                    [
                        'Elements' => ($mergedElementsInRow . $emptySpaceFiller)
                    ]
                );
            },
            $groupedStructureRows
        );

        $Parse['Create_StructuresList'] = implode(
            $tplBodyCache['list_breakrow'],
            $parsedStructureRows
        );
        $Parse['Create_ElementsInfoBoxes'] = implode(
            '',
            $elementsListDetailedInfoboxes
        );

        if ($highlightElementID > 0) {
            $Parse['Create_ShowElementOnStartup'] = $highlightElementID;
        }
        // End of - Parse all available buildings

        $Parse['Insert_SkinPath'] = $_SkinPath;
        $Parse['Insert_PlanetImg'] = $CurrentPlanet['image'];
        $Parse['Insert_PlanetType'] = $_Lang['PlanetType_'.$CurrentPlanet['planet_type']];
        $Parse['Insert_PlanetName'] = $CurrentPlanet['name'];
        $Parse['Insert_PlanetPos_Galaxy'] = $CurrentPlanet['galaxy'];
        $Parse['Insert_PlanetPos_System'] = $CurrentPlanet['system'];
        $Parse['Insert_PlanetPos_Planet'] = $CurrentPlanet['planet'];
        $Parse['Insert_Overview_LabLevel'] = Elements\getElementState(
            31,
            $CurrentPlanet,
            $CurrentUser
        )['level'];
        $Parse['Insert_Overview_LabsConnected'] = prettyNumber($researchNetworkStatus['connectedLabsCount']);
        $Parse['Insert_Overview_TotalLabsCount'] = prettyNumber($researchNetworkStatus['allLabsCount']);
        $Parse['Insert_Overview_LabPower'] = prettyNumber($researchNetworkStatus['connectedLabsLevel']);
        $Parse['Insert_Overview_LabPowerTotal'] = prettyNumber($researchNetworkStatus['allLabsLevel']);

        $planetInfoComponent = ModernQueuePlanetInfo\render([
            'currentPlanet'     => &$CurrentPlanet,
            'researchPlanet'    => &$planetConductingResearch,
            'queue'             => Planets\Queues\Research\parseQueueString(
                $planetConductingResearch['techQueue']
            ),
            'timestamp'         => $currentTimestamp,
        ]);

        $queueComponent = ModernQueue\render([
            'user'              => &$CurrentUser,
            'planet'            => &$planetConductingResearch,
            'queue'             => Planets\Queues\Research\parseQueueString(
                $planetConductingResearch['techQueue']
            ),
            'queueMaxLength'    => Users\getMaxResearchQueueLength($CurrentUser),
            'timestamp'         => $currentTimestamp,
            'infoComponents'    => [
                $planetInfoComponent['componentHTML']
            ],

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

        $Parse['Create_Queue'] = $queueComponent['componentHTML'];

        $pageTPLBody = gettemplate('buildings_compact_body_lab');
        $pageHTML = parsetemplate($pageTPLBody, $Parse);
    } else {
        $Parse['PHPInject_ElementsListHTML'] = implode(
            '',
            $elementsListItems
        );

        if ($hasElementsInQueue) {
            $queueComponent = LegacyQueue\render([
                'planet' => $CurrentPlanet,
                'queue'             => Planets\Queues\Research\parseQueueString(
                    $planetConductingResearch['techQueue']
                ),
                'currentTimestamp' => $currentTimestamp,
                'infoComponents' => [],

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

            $Parse['PHPInject_Queue'] = $queueComponent['componentHTML'];
        }

        // TODO: add missing "Labs in queue" box
        // TODO: add missing "Research in other lab" box

        $pageTPLBody = gettemplate('buildings_legacy_body_lab');
        $pageHTML = parsetemplate($pageTPLBody, $Parse);
    }

    display($pageHTML, $_Lang['Research']);
}

?>
