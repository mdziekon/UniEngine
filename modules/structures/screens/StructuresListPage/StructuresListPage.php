<?php

namespace UniEngine\Engine\Modules\Structures\Screens\StructuresListPage;

use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Modules\Structures\Input;
use UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\Queue;
use UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\ModernElementListIcon;
use UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\ModernElementInfoCard;

function render (&$CurrentPlanet, $CurrentUser) {
    global $_Lang, $_SkinPath, $_GET, $_EnginePath, $_Vars_ElementCategories;

    include($_EnginePath . 'includes/functions/GetElementTechReq.php');
    includeLang('worldElements.detailed');

    $currentTimestamp = time();
    $Parse = &$_Lang;
    $highlightElementID = 0;
    $fieldsModifierByQueuedDowngrades = 0;

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $currentTimestamp);

    // Constants
    $const_ElementsPerRow = 7;

    // Get Templates
    $tplBodyCache['list_hidden']                 = gettemplate('buildings_compact_list_hidden');
    $tplBodyCache['list_row']                    = gettemplate('buildings_compact_list_row');
    $tplBodyCache['list_breakrow']               = gettemplate('buildings_compact_list_breakrow');
    $tplBodyCache['infobox_req_desttable']       = gettemplate('buildings_compact_infobox_req_desttable');
    $tplBodyCache['infobox_req_destres']         = gettemplate('buildings_compact_infobox_req_destres');

    // Handle Commands
    $cmdResult = Input\UserCommands\handleStructureCommand(
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
    $queueComponent = Queue\render([
        'planet' => &$CurrentPlanet,
        'user' => &$CurrentUser,
        'timestamp' => $currentTimestamp
    ]);

    $queueTempResourcesLock = $queueComponent['parsedDetails']['queuedResourcesToUse'];
    $queueUnfinishedElementsCount = $queueComponent['parsedDetails']['unfinishedElementsCount'];
    $queuedElementLevelModifiers = $queueComponent['parsedDetails']['queuedElementLevelModifiers'];
    $fieldsModifierByQueuedDowngrades = $queueComponent['parsedDetails']['fieldsModifier'];

    $Parse['Create_Queue'] = $queueComponent['componentHTML'];

    // Apply queue modifiers
    $CurrentPlanet['metal'] -= $queueTempResourcesLock['metal'];
    $CurrentPlanet['crystal'] -= $queueTempResourcesLock['crystal'];
    $CurrentPlanet['deuterium'] -= $queueTempResourcesLock['deuterium'];

    foreach($queuedElementLevelModifiers as $elementID => $levelModifier) {
        $elementPlanetKey = _getElementPlanetKey($elementID);

        $CurrentPlanet[$elementPlanetKey] += $levelModifier;
    }

    // Parse all available buildings
    $hasAvailableFieldsOnPlanet = (
        ($CurrentPlanet['field_current'] + $queueUnfinishedElementsCount - $fieldsModifierByQueuedDowngrades) <
        CalculateMaxPlanetFields($CurrentPlanet)
    );
    $hasElementsInQueue = ($queueUnfinishedElementsCount > 0);
    $isOnVacation = isOnVacation($CurrentUser);
    $isQueueFull = (
        $queueUnfinishedElementsCount >=
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

    $elementTPLDataDefaults = [
        'SkinPath'                  => $_SkinPath,
        // 'InfoBox_TechRequirements'  => $_Lang['InfoBox_TechRequirements'],
    ];

    $Parse['Create_DestroyTips'] = '';

    $elementsListIcons = [];
    $elementsListDetailedInfoboxes = [];

    foreach ($_Vars_ElementCategories['build'] as $elementID) {
        $isAvailableOnThisPlanetType = Elements\isStructureAvailableOnPlanetType(
            $elementID,
            $CurrentPlanet['planet_type']
        );

        if (!$isAvailableOnThisPlanetType) {
            continue;
        }

        $elementTPLData = $elementTPLDataDefaults;

        $elementCurrentQueuedLevel = Elements\getElementCurrentLevel($elementID, $CurrentPlanet, $CurrentUser);
        $elementPreviousLevel = $elementCurrentQueuedLevel - 1;
        $elementMaxLevel = Elements\getElementMaxUpgradeLevel($elementID);
        $elementQueueLevelModifier = (
            isset($queuedElementLevelModifiers[$elementID]) ?
            $queuedElementLevelModifiers[$elementID] :
            0
        );
        $isInQueue = isset($queuedElementLevelModifiers[$elementID]);
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

            $elementTPLData['Create_DestroyTips_Res'] = '';

            foreach ($downgradeCost as $costResourceKey => $costValue) {
                $currentResourceState = Resources\getResourceState(
                    $costResourceKey,
                    $CurrentUser,
                    $CurrentPlanet
                );

                $resourceCostColor = '';

                $resourceLeft = ($currentResourceState - $costValue);
                $hasResourceDeficit = ($resourceLeft < 0);

                if ($hasResourceDeficit) {
                    $resourceCostColor = (
                        $hasElementsInQueue ?
                        'orange' :
                        'red'
                    );
                }

                $elementTPLData['ElementPrices'] = [
                    'Name' => $resourceLabels[$costResourceKey],
                    'Color' => $resourceCostColor,
                    'Value' => prettyNumber($costValue)
                ];
                $elementTPLData['Create_DestroyTips_Res'] .= trim(
                    parsetemplate(
                        $tplBodyCache['infobox_req_destres'],
                        $elementTPLData['ElementPrices']
                    )
                );
            }

            $Parse['Create_DestroyTips'] .= parsetemplate(
                $tplBodyCache['infobox_req_desttable'],
                [
                    'ElementID' => $elementID,
                    'InfoBox_DestroyCost' => $_Lang['InfoBox_DestroyCost'],
                    'InfoBox_DestroyTime' => $_Lang['InfoBox_DestroyTime'],
                    'Resources' => $elementTPLData['Create_DestroyTips_Res'],
                    'DestroyTime' => pretty_time(GetBuildingTime($CurrentUser, $CurrentPlanet, $elementID) / 2)
                ]
            );
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

        $elementListIcon = ModernElementListIcon\render([
            'elementID' => $elementID,
            'elementCurrentLevel' => (
                $elementCurrentQueuedLevel -
                $elementQueueLevelModifier
            ),
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
            'elementCurrentLevel' => (
                $elementCurrentQueuedLevel -
                $elementQueueLevelModifier
            ),
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
    }

    // Restore original state by unapplying queue modifiers
    $CurrentPlanet['metal'] += $queueTempResourcesLock['metal'];
    $CurrentPlanet['crystal'] += $queueTempResourcesLock['crystal'];
    $CurrentPlanet['deuterium'] += $queueTempResourcesLock['deuterium'];

    foreach ($queuedElementLevelModifiers as $elementID => $levelModifier) {
        $elementPlanetKey = _getElementPlanetKey($elementID);

        $CurrentPlanet[$elementPlanetKey] -= $levelModifier;
    }

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

    $pageTPLBody = gettemplate('buildings_compact_body_structures');
    $pageHTML = parsetemplate($pageTPLBody, $Parse);

    display($pageHTML, $_Lang['Builds']);
}

?>
