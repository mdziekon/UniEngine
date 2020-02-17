<?php

namespace UniEngine\Engine\Modules\Structures\Screens\StructuresListPage;

use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Modules\Development\Components\ModernQueue;
use UniEngine\Engine\Modules\Development\Components\LegacyQueue;
use UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\ModernElementListIcon;
use UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\ModernElementInfoCard;
use UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\LegacyElementListItem;

function render (&$CurrentPlanet, $CurrentUser) {
    global $_Lang, $_SkinPath, $_GET, $_EnginePath, $_Vars_ElementCategories;

    include($_EnginePath . 'includes/functions/GetElementTechReq.php');
    includeLang('worldElements.detailed');

    $currentTimestamp = time();
    $Parse = &$_Lang;
    $highlightElementID = 0;

    $isModernLayoutEnabled = (
        $CurrentUser['settings_DevelopmentOld'] != 1 ?
        true :
        false
    );

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $currentTimestamp);

    // Constants
    $const_ElementsPerRow = 7;

    // Get Templates
    $tplBodyCache = [
        'list_hidden'   => gettemplate('buildings_compact_list_hidden'),
        'list_row'      => gettemplate('buildings_compact_list_row'),
        'list_breakrow' => gettemplate('buildings_compact_list_breakrow'),
    ];

    // Handle Commands
    $cmdResult = Development\Input\UserCommands\handleStructureCommand(
        $CurrentUser,
        $CurrentPlanet,
        $_GET,
        [
            "timestamp" => $currentTimestamp
        ]
    );

    if ($cmdResult['isSuccess']) {
        $highlightElementID = $cmdResult['payload']['elementID'];
    }
    // End of - Handle Commands

    // Display queue
    $queueStateDetails = Development\Utils\getQueueStateDetails([
        'queue' => [
            'type' => Development\Utils\QueueType::Research,
            'content' => Planets\Queues\Structures\parseQueueString(
                $CurrentPlanet['buildQueue']
            ),
        ],
        'user' => $CurrentUser,
        'planet' => $CurrentPlanet,
    ]);
    $elementsInQueue = $queueStateDetails['queuedElementsCount'];
    $planetFieldsUsageCounter = 0;

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
        $CurrentPlanet[$elementKey] += $elementLevelModifier;
        $planetFieldsUsageCounter += $elementLevelModifier;
    }

    // Parse all available buildings
    $hasAvailableFieldsOnPlanet = (
        ($CurrentPlanet['field_current'] + $planetFieldsUsageCounter) <
        CalculateMaxPlanetFields($CurrentPlanet)
    );
    $hasElementsInQueue = ($elementsInQueue > 0);
    $isOnVacation = isOnVacation($CurrentUser);
    $isQueueFull = (
        $elementsInQueue >=
        Users\getMaxStructuresQueueLength($CurrentUser)
    );
    $isBlockingTechResearchInProgress = (
        $CurrentUser['techQueue_Planet'] > 0 &&
        $CurrentUser['techQueue_EndTime'] > 0 &&
        !isLabUpgradableWhileInUse()
    );

    $resourceLabels = [
        'metal'         => $_Lang['Metal'],
        'crystal'       => $_Lang['Crystal'],
        'deuterium'     => $_Lang['Deuterium'],
        'energy'        => $_Lang['Energy'],
        'energy_max'    => $_Lang['Energy'],
        'darkEnergy'    => $_Lang['DarkEnergy']
    ];

    $elementsListIcons = [];
    $elementsListDetailedInfoboxes = [];
    $elementsDestructionDetails = [];
    $elementsListItems = [];

    foreach ($_Vars_ElementCategories['build'] as $elementID) {
        $isAvailableOnThisPlanetType = Elements\isStructureAvailableOnPlanetType(
            $elementID,
            $CurrentPlanet['planet_type']
        );

        if (!$isAvailableOnThisPlanetType) {
            continue;
        }

        $elementCurrentQueuedLevel = Elements\getElementCurrentLevel($elementID, $CurrentPlanet, $CurrentUser);
        $elementPreviousLevel = $elementCurrentQueuedLevel - 1;
        $elementMaxLevel = Elements\getElementMaxUpgradeLevel($elementID);
        $isInQueue = isset($queueStateDetails['queuedElementLevelModifiers'][$elementID]);
        $elementQueueLevelModifier = (
            $isInQueue ?
            $queueStateDetails['queuedElementLevelModifiers'][$elementID] :
            0
        );
        $elementCurrentPlanetLevel = (
            $elementCurrentQueuedLevel -
            $elementQueueLevelModifier
        );
        $isLevelDowngradeable = ($elementPreviousLevel >= 0);
        $isBlockedByTechResearchProgress = (
            $elementID == 31 &&
            $isBlockingTechResearchInProgress
        );
        $hasReachedMaxLevel = ($elementCurrentQueuedLevel >= $elementMaxLevel);
        $hasTechnologyRequirementsMet = IsTechnologieAccessible(
            $CurrentUser,
            $CurrentPlanet,
            $elementID
        );

        $isUpgradeable = (!$hasReachedMaxLevel);
        $isDowngradeable = (
            $isLevelDowngradeable &&
            !Elements\isIndestructibleStructure($elementID)
        );

        $hasUpgradeResources = (
            $isUpgradeable ?
            IsElementBuyable($CurrentUser, $CurrentPlanet, $elementID, false) :
            null
        );
        $hasDowngradeResources = (
            $isDowngradeable ?
            IsElementBuyable($CurrentUser, $CurrentPlanet, $elementID, true) :
            null
        );

        // Generate all additional informations
        if ($isDowngradeable) {
            $downgradeCost = Elements\calculatePurchaseCost(
                $elementID,
                Elements\getElementState($elementID, $CurrentPlanet, $CurrentUser),
                [
                    'purchaseMode' => Elements\PurchaseMode::Downgrade
                ]
            );

            $elementDowngradeResources = [];

            foreach ($downgradeCost as $costResourceKey => $costValue) {
                $currentResourceState = Resources\getResourceState(
                    $costResourceKey,
                    $CurrentUser,
                    $CurrentPlanet
                );

                $resourceLeft = ($currentResourceState - $costValue);
                $hasResourceDeficit = ($resourceLeft < 0);

                $resourceCostColor = (
                    !$hasResourceDeficit ?
                    '' :
                    (
                        $hasElementsInQueue ?
                        'orange' :
                        'red'
                    )
                );

                $elementDowngradeResources[] = [
                    'name' => $resourceLabels[$costResourceKey],
                    'color' => $resourceCostColor,
                    'value' => prettyNumber($costValue)
                ];
            }

            $destructionTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $elementID) / 2;

            $elementsDestructionDetails[$elementID] = [
                'resources' => $elementDowngradeResources,
                'destructionTime' => pretty_time($destructionTime)
            ];
        }

        // Perform all necessary tests to determine if actions are possible
        $isUpgradeHardBlocked = (
            !$isUpgradeable
        );
        $isDowngradeHardBlocked = (
            !$isDowngradeable ||
            !$hasTechnologyRequirementsMet
        );
        $canStartUpgrade = (
            !$isUpgradeHardBlocked &&
            $hasUpgradeResources &&
            $hasTechnologyRequirementsMet &&
            !$isBlockedByTechResearchProgress &&
            $hasAvailableFieldsOnPlanet &&
            !$isQueueFull &&
            !$isOnVacation
        );
        $canQueueUpgrade = (
            !$isUpgradeHardBlocked &&
            ($hasUpgradeResources || $hasElementsInQueue) &&
            $hasTechnologyRequirementsMet &&
            !$isBlockedByTechResearchProgress &&
            $hasAvailableFieldsOnPlanet &&
            !$isQueueFull &&
            !$isOnVacation
        );
        $canQueueDowngrade = (
            !$isDowngradeHardBlocked &&
            ($hasDowngradeResources || $hasElementsInQueue) &&
            !$isBlockedByTechResearchProgress &&
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
        if ($isBlockedByTechResearchProgress) {
            $upgradeBlockers[] = $_Lang['ListBox_Disallow_LabResearch'];
        }
        if (!$hasAvailableFieldsOnPlanet) {
            $upgradeBlockers[] = $_Lang['ListBox_Disallow_NoFreeFields'];
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
                'elementCurrentLevel' => $elementCurrentPlanetLevel,
                'elementQueueLevelModifier' => $elementQueueLevelModifier,
                'isInQueue' => $isInQueue,
                'canStartUpgrade' => $canStartUpgrade,
                'canQueueUpgrade' => $canQueueUpgrade,
                'upgradeBlockersList' => $upgradeBlockers
            ]);
            $elementInfoCard = ModernElementInfoCard\render([
                'user' => $CurrentUser,
                'planet' => $CurrentPlanet,
                'currentTimestamp' => $currentTimestamp,
                'elementID' => $elementID,
                'elementCurrentLevel' => $elementCurrentPlanetLevel,
                'elementQueueLevelModifier' => $elementQueueLevelModifier,
                'isQueueActive' => $hasElementsInQueue,
                'isInQueue' => $isInQueue,
                'isUpgradeable' => $isUpgradeable,
                'isUpgradeHardBlocked' => $isUpgradeHardBlocked,
                'isDowngradeHardBlocked' => $isDowngradeHardBlocked,
                'hasReachedMaxLevel' => $hasReachedMaxLevel,
                'hasTechnologyRequirementsMet' => $hasTechnologyRequirementsMet,
                'canStartUpgrade' => $canStartUpgrade,
                'canQueueUpgrade' => $canQueueUpgrade,
                'canQueueDowngrade' => $canQueueDowngrade
            ]);

            $elementsListIcons[] = $elementListIcon['componentHTML'];
            $elementsListDetailedInfoboxes[] = $elementInfoCard['componentHTML'];
        } else {
            $elementListItem = LegacyElementListItem\render([
                'user' => $CurrentUser,
                'planet' => $CurrentPlanet,
                'currentTimestamp' => $currentTimestamp,
                'elementID' => $elementID,
                'elementCurrentLevel' => $elementCurrentPlanetLevel,
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
        $CurrentPlanet[$elementKey] -= $elementLevelModifier;
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
        $Parse['PHPData_ElementsDestructionDetailsJSON'] = json_encode($elementsDestructionDetails);

        if ($highlightElementID > 0) {
            $Parse['Create_ShowElementOnStartup'] = $highlightElementID;
        }
        // End of - Parse all available buildings

        $planetsMaxFields = CalculateMaxPlanetFields($CurrentPlanet);

        $Parse['Insert_SkinPath'] = $_SkinPath;
        $Parse['Insert_PlanetImg'] = $CurrentPlanet['image'];
        $Parse['Insert_PlanetType'] = $_Lang['PlanetType_'.$CurrentPlanet['planet_type']];
        $Parse['Insert_PlanetName'] = $CurrentPlanet['name'];
        $Parse['Insert_PlanetPos_Galaxy'] = $CurrentPlanet['galaxy'];
        $Parse['Insert_PlanetPos_System'] = $CurrentPlanet['system'];
        $Parse['Insert_PlanetPos_Planet'] = $CurrentPlanet['planet'];
        $Parse['Insert_Overview_Diameter'] = prettyNumber($CurrentPlanet['diameter']);
        $Parse['Insert_Overview_Fields_Used'] = prettyNumber($CurrentPlanet['field_current']);
        $Parse['Insert_Overview_Fields_Max'] = prettyNumber($planetsMaxFields);
        $Parse['Insert_Overview_Fields_Percent'] = sprintf(
            '%0.2f',
            ($CurrentPlanet['field_current'] / $planetsMaxFields) * 100
        );
        $Parse['Insert_Overview_Temperature'] = sprintf(
            $_Lang['Overview_Form_Temperature'],
            $CurrentPlanet['temp_min'],
            $CurrentPlanet['temp_max']
        );

        if ($CurrentPlanet['field_current'] == $planetsMaxFields) {
            $Parse['Insert_Overview_Fields_Used_Color'] = 'red';
        } else if($CurrentPlanet['field_current'] >= ($planetsMaxFields * 0.9)) {
            $Parse['Insert_Overview_Fields_Used_Color'] = 'orange';
        } else {
            $Parse['Insert_Overview_Fields_Used_Color'] = 'lime';
        }

        $queueComponent = ModernQueue\render([
            'planet' => &$CurrentPlanet,
            'queue' => Planets\Queues\Structures\parseQueueString($CurrentPlanet['buildQueue']),
            'queueMaxLength' => Users\getMaxStructuresQueueLength($CurrentUser),
            'timestamp' => $currentTimestamp,
            'infoComponents' => [],

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

        $Parse['Create_Queue'] = $queueComponent['componentHTML'];

        $pageTPLBody = gettemplate('buildings_compact_body_structures');
        $pageHTML = parsetemplate($pageTPLBody, $Parse);
    } else {
        $planetsMaxFields = CalculateMaxPlanetFields($CurrentPlanet);

        $Parse['PHPInject_ElementsListHTML'] = implode(
            '',
            $elementsListItems
        );

        if ($hasElementsInQueue) {
            $queueComponent = LegacyQueue\render([
                'planet' => $CurrentPlanet,
                'queue' => Planets\Queues\Structures\parseQueueString($CurrentPlanet['buildQueue']),
                'currentTimestamp' => $currentTimestamp,

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

            $Parse['PHPInject_Queue'] = $queueComponent['componentHTML'];
        }

        $Parse['Insert_Overview_Fields_Used'] = prettyNumber($CurrentPlanet['field_current']);
        $Parse['Insert_Overview_Fields_Max'] = prettyNumber($planetsMaxFields);
        $Parse['Insert_Overview_Fields_Available'] = prettyNumber(
            $planetsMaxFields - $CurrentPlanet['field_current']
        );

        $pageTPLBody = gettemplate('buildings_legacy_body_structures');
        $pageHTML = parsetemplate($pageTPLBody, $Parse);
    }

    display($pageHTML, $_Lang['Builds']);
}

?>
