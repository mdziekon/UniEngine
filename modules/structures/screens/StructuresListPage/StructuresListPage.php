<?php

namespace UniEngine\Engine\Modules\Structures\Screens\StructuresListPage;

use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Modules\Structures\Input;
use UniEngine\Engine\Modules\Structures\Screens\StructuresListPage\Queue;

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
    $tplBodyCache['list_element']                = gettemplate('buildings_compact_list_element_structures');
    $tplBodyCache['list_levelmodif']             = gettemplate('buildings_compact_list_levelmodif');
    $tplBodyCache['list_hidden']                 = gettemplate('buildings_compact_list_hidden');
    $tplBodyCache['list_row']                    = gettemplate('buildings_compact_list_row');
    $tplBodyCache['list_breakrow']               = gettemplate('buildings_compact_list_breakrow');
    $tplBodyCache['list_disabled']               = gettemplate('buildings_compact_list_disabled');
    $tplBodyCache['list_partdisabled']           = parsetemplate($tplBodyCache['list_disabled'], [ 'AddOpacity' => 'dPart' ]);
    $tplBodyCache['list_disabled']               = parsetemplate($tplBodyCache['list_disabled'], [ 'AddOpacity' => '' ]);
    $tplBodyCache['infobox_body']                = gettemplate('buildings_compact_infobox_body_structures');
    $tplBodyCache['infobox_levelmodif']          = gettemplate('buildings_compact_infobox_levelmodif');
    $tplBodyCache['infobox_req_res']             = gettemplate('buildings_compact_infobox_req_res');
    $tplBodyCache['infobox_req_desttable']       = gettemplate('buildings_compact_infobox_req_desttable');
    $tplBodyCache['infobox_req_destres']         = gettemplate('buildings_compact_infobox_req_destres');
    $tplBodyCache['infobox_additionalnfo']       = gettemplate('buildings_compact_infobox_additionalnfo');
    $tplBodyCache['infobox_req_selector_single'] = gettemplate('buildings_compact_infobox_req_selector_single');
    $tplBodyCache['infobox_req_selector_dual']   = gettemplate('buildings_compact_infobox_req_selector_dual');

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
        'currentTimestamp' => $currentTimestamp
    ]);

    $queueTempResourcesLock = $queueComponent['planetModifiers']['queuedResourcesToUse'];
    $queueUnfinishedLenght = $queueComponent['planetModifiers']['unfinishedLength'];
    $queuedElementLevelModifiers = $queueComponent['planetModifiers']['queuedElementLevelModifiers'];
    $fieldsModifierByQueuedDowngrades = $queueComponent['planetModifiers']['fields'];

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
        ($CurrentPlanet['field_current'] + $queueUnfinishedLenght - $fieldsModifierByQueuedDowngrades) <
        CalculateMaxPlanetFields($CurrentPlanet)
    );
    $isOnVacation = isOnVacation($CurrentUser);
    $isQueueFull = (
        $queueUnfinishedLenght >=
        Users\getMaxStructuresQueueLength($CurrentUser)
    );
    $isBlockingTechResearchInProgress = (
        $CurrentUser['techQueue_Planet'] > 0 &&
        $CurrentUser['techQueue_EndTime'] > 0 &&
        !isLabUpgradableWhileInUse()
    );

    if ($isQueueFull) {
        $queueFullMsgHTML = parsetemplate(
            $tplBodyCache['queue_topinfo'],
            [
                'InfoColor' => 'red',
                'InfoText' => $_Lang['Queue_Full']
            ]
        );

        $Parse['Create_Queue'] = $queueFullMsgHTML . $Parse['Create_Queue'];
    }

    $resourceIcons = [
        'metal'         => 'metall',
        'crystal'       => 'kristall',
        'deuterium'     => 'deuterium',
        'energy'        => 'energie',
        'energy_max'    => 'energie',
        'darkEnergy'    => 'darkenergy'
    ];
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
        'InfoBox_Level'             => $_Lang['InfoBox_Level'],
        'InfoBox_Build'             => $_Lang['InfoBox_Build'],
        'InfoBox_Destroy'           => $_Lang['InfoBox_Destroy'],
        'InfoBox_RequirementsFor'   => $_Lang['InfoBox_RequirementsFor'],
        'InfoBox_ResRequirements'   => $_Lang['InfoBox_ResRequirements'],
        'InfoBox_TechRequirements'  => $_Lang['InfoBox_TechRequirements'],
        'InfoBox_Requirements_Res'  => $_Lang['InfoBox_Requirements_Res'],
        'InfoBox_Requirements_Tech' => $_Lang['InfoBox_Requirements_Tech'],
        'InfoBox_BuildTime'         => $_Lang['InfoBox_BuildTime'],
        'InfoBox_ShowTechReq'       => $_Lang['InfoBox_ShowTechReq'],
        'InfoBox_ShowResReq'        => $_Lang['InfoBox_ShowResReq'],
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
        $elementNextLevel = $elementCurrentQueuedLevel + 1;
        $elementPreviousLevel = $elementCurrentQueuedLevel - 1;
        $elementMaxLevel = Elements\getElementMaxUpgradeLevel($elementID);
        $elementQueueLevelModifier = (
            isset($queuedElementLevelModifiers[$elementID]) ?
            $queuedElementLevelModifiers[$elementID] :
            0
        );
        $isInQueue = isset($queuedElementLevelModifiers[$elementID]);
        $isLevelDowngradeable = ($elementPreviousLevel >= 0);
        $isProductionRelatedStructure = in_array($elementID, $_Vars_ElementCategories['prod']);
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

        $elementTPLData['ElementName'] = $_Lang['tech'][$elementID];
        $elementTPLData['ElementID'] = $elementID;
        $elementTPLData['ElementLevel'] = prettyNumber($elementCurrentQueuedLevel);
        $elementTPLData['ElementRealLevel'] = prettyNumber(
            $elementCurrentQueuedLevel -
            $elementQueueLevelModifier
        );
        $elementTPLData['BuildLevel'] = prettyNumber($elementNextLevel);
        $elementTPLData['DestroyLevel'] = prettyNumber($elementPreviousLevel);
        $elementTPLData['Desc'] = $_Lang['WorldElements_Detailed'][$elementID]['description_short'];
        $elementTPLData['BuildButtonColor'] = 'buildDo_Green';
        $elementTPLData['DestroyButtonColor'] = 'buildDo_Red';

        // Generate all additional informations
        if ($isInQueue) {
            $elementLevelModifierTPLData = [];

            if ($elementQueueLevelModifier < 0) {
                $elementLevelModifierTPLData['modColor'] = 'red';
                $elementLevelModifierTPLData['modText'] = prettyNumber($elementQueueLevelModifier);
            } else if ($elementQueueLevelModifier == 0) {
                $elementLevelModifierTPLData['modColor'] = 'orange';
                $elementLevelModifierTPLData['modText'] = '0';
            } else {
                $elementLevelModifierTPLData['modColor'] = 'lime';
                $elementLevelModifierTPLData['modText'] = '+' . prettyNumber($elementQueueLevelModifier);
            }

            $elementTPLData['LevelModifier'] = parsetemplate($tplBodyCache['infobox_levelmodif'], $elementLevelModifierTPLData);
            $elementTPLData['ElementLevelModif'] = parsetemplate($tplBodyCache['list_levelmodif'], $elementLevelModifierTPLData);
        }

        if ($isUpgradeable) {
            $upgradeCost = Elements\calculatePurchaseCost(
                $elementID,
                Elements\getElementState($elementID, $CurrentPlanet, $CurrentUser),
                [
                    'purchaseMode' => Elements\PurchaseMode::Upgrade
                ]
            );

            $elementTPLData['ElementPriceDiv'] = '';

            foreach ($upgradeCost as $costResourceKey => $costValue) {
                $currentResourceState = Resources\getResourceState(
                    $costResourceKey,
                    $CurrentUser,
                    $CurrentPlanet
                );

                $resourceCostColor = '';
                $resourceDeficitColor = '';
                $resourceDeficitValue = '&nbsp;';

                $resourceLeft = $currentResourceState - $costValue;
                $hasResourceDeficit = ($resourceLeft < 0);

                if ($hasResourceDeficit) {
                    $resourceDeficitColor = 'red';
                    $resourceDeficitValue = '(' . prettyNumber($resourceLeft) . ')';
                    $resourceCostColor = (
                        $queueUnfinishedLenght > 0 ?
                        'orange' :
                        'red'
                    );
                }

                $elementTPLData['ElementPrices'] = [
                    'SkinPath'      => $_SkinPath,
                    'ResName'       => $costResourceKey,
                    'ResImg'        => $resourceIcons[$costResourceKey],
                    'ResColor'      => $resourceCostColor,
                    'Value'         => prettyNumber($costValue),
                    'ResMinusColor' => $resourceDeficitColor,
                    'MinusValue'    => $resourceDeficitValue,
                ];

                $elementTPLData['ElementPriceDiv'] .= parsetemplate(
                    $tplBodyCache['infobox_req_res'],
                    $elementTPLData['ElementPrices']
                );
            }

            $elementTPLData['BuildTime'] = pretty_time(GetBuildingTime($CurrentUser, $CurrentPlanet, $elementID));
        }

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
                        $queueUnfinishedLenght > 0 ?
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

        if ($hasTechnologyRequirementsMet) {
            $elementTPLData['ElementRequirementsHeadline'] = $tplBodyCache['infobox_req_selector_single'];
        } else {
            $elementTPLData['ElementRequirementsHeadline'] = $tplBodyCache['infobox_req_selector_dual'];
            $elementTPLData['ElementTechDiv'] = GetElementTechReq($CurrentUser, $CurrentPlanet, $elementID, true);
            $elementTPLData['HideResReqDiv'] = 'hide';
        }

        if ($isProductionRelatedStructure) {
            $elementProductionChangeTPLRows = [];

            // Calculate theoretical production increase
            $thisLevelProduction = getElementProduction(
                $elementID,
                $CurrentPlanet,
                $CurrentUser,
                [
                    'useCurrentBoosters' => true,
                    'currentTimestamp' => $currentTimestamp,
                    'customLevel' => $elementCurrentQueuedLevel,
                    'customProductionFactor' => 10
                ]
            );
            $nextLevelProduction = getElementProduction(
                $elementID,
                $CurrentPlanet,
                $CurrentUser,
                [
                    'useCurrentBoosters' => true,
                    'currentTimestamp' => $currentTimestamp,
                    'customLevel' => $elementNextLevel,
                    'customProductionFactor' => 10
                ]
            );

            foreach ($nextLevelProduction as $resourceKey => $nextLevelResourceProduction) {
                $difference = ($nextLevelResourceProduction - $thisLevelProduction[$resourceKey]);

                if ($difference == 0) {
                    continue;
                }

                $differenceFormatted = prettyNumber($difference);
                $label = $resourceLabels[$resourceKey];

                $elementProductionChangeTPLRows[] = parsetemplate(
                    $tplBodyCache['infobox_additionalnfo'],
                    [
                        'Label' => $label,
                        'ValueClasses' => (
                            $difference >= 0 ?
                            'lime' :
                            'red'
                        ),
                        'Value' => (
                            $difference >= 0 ?
                            ('+' . $differenceFormatted) :
                            $differenceFormatted
                        )
                    ]
                );
            }

            $elementTPLData['AdditionalNfo'] = implode('', $elementProductionChangeTPLRows);
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
            ($hasUpgradeResources || $queueUnfinishedLenght > 0) &&
            $hasTechnologyRequirementsMet &&
            !$isBlockedByTechResearchProgress &&
            $hasAvailableFieldsOnPlanet &&
            !$isQueueFull &&
            !$isOnVacation
        );
        $canQueueDowngrade = (
            !$isDowngradeHardBlocked &&
            ($hasDowngradeResources || $queueUnfinishedLenght > 0) &&
            !$isBlockedByTechResearchProgress &&
            !$isQueueFull &&
            !$isOnVacation
        );

        // Set all template details based on the state of the structure
        $elementTPLData['HideBuildInfo'] = ($isUpgradeHardBlocked ? 'hide' : '');
        $elementTPLData['HideBuildWarn'] = (!$isUpgradeHardBlocked ? 'hide' : '');
        $elementTPLData['BuildWarn_Color'] = ($isUpgradeHardBlocked ? 'red' : '');

        $elementTPLData['HideBuildButton'] = ($isUpgradeHardBlocked ? 'hide' : '');
        $elementTPLData['HideDestroyButton'] = ($isDowngradeHardBlocked ? 'hide' : '');
        $elementTPLData['HideQuickBuildButton'] = (!$canQueueUpgrade ? 'hide' : '');

        $elementTPLData['BuildWarn_Text'] = (
            $isUpgradeHardBlocked && $hasReachedMaxLevel ?
            $_Lang['ListBox_Disallow_MaxLevelReached'] :
            ''
        );
        $elementTPLData['BuildButtonColor'] = (
            $canStartUpgrade ?
            'buildDo_Green' :
            (
                $canQueueUpgrade ?
                'buildDo_Orange' :
                'buildDo_Gray'
            )
        );
        $elementTPLData['DestroyButtonColor'] = (
            $canQueueDowngrade ?
            'buildDo_Red' :
            'destroyDo_Gray'
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

        if (!empty($upgradeBlockers)) {
            $elementTPLData['ElementDisabled'] = (
                $canQueueUpgrade ?
                $tplBodyCache['list_partdisabled'] :
                $tplBodyCache['list_disabled']
            );

            $elementTPLData['ElementDisableReason'] = end($upgradeBlockers);
        }

        $elementTPLData['ElementRequirementsHeadline'] = parsetemplate(
            $elementTPLData['ElementRequirementsHeadline'],
            $elementTPLData
        );
        $elementsListIcons[] = parsetemplate($tplBodyCache['list_element'], $elementTPLData);
        $elementsListDetailedInfoboxes[] = parsetemplate($tplBodyCache['infobox_body'], $elementTPLData);
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
