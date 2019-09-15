<?php

use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

//  Arguments:
//      - $user
//      - $planet
//      - $input
//      - $params (Object)
//          - timestamp (Number)
//
function _handleStructureCommand(&$user, &$planet, &$input, $params) {
    $knownCommands = [
        'cancel',
        'remove',
        'insert',
        'destroy'
    ];

    $timestamp = $params['timestamp'];

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

    switch ($cmd) {
        case "cancel":
            $cmdResult = _handleStructureCommandCancel($user, $planet);

            break;
        case 'remove':
            $cmdResult = _handleStructureCommandRemove($user, $planet, $input);

            break;
        case "insert":
        case "destroy":
            $cmdResult = _handleStructureCommandInsert($user, $planet, $input, [
                'cmd' => $cmd
            ]);

            break;
    }

    if (!$cmdResult['isSuccess']) {
        return [
            'isSuccess' => false,
            'error' => $cmdResult['error']
        ];
    }

    $isPlanetRecordSyncNotNeeded = HandlePlanetQueue_StructuresSetNext(
        $planet,
        $user,
        $timestamp,
        true
    );

    if (!$isPlanetRecordSyncNotNeeded) {
        include($_EnginePath . 'includes/functions/BuildingSavePlanetRecord.php');

        BuildingSavePlanetRecord($planet);
    }

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $cmdResult['payload']['elementID']
        ]
    ];
}

function _handleStructureCommandCancel(&$user, &$planet) {
    global $_EnginePath;

    include($_EnginePath . 'includes/functions/CancelBuildingFromQueue.php');

    $highlightElementID = CancelBuildingFromQueue($planet, $user);

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $highlightElementID
        ]
    ];
}

function _handleStructureCommandRemove(&$user, &$planet, &$input) {
    global $_EnginePath;

    $listElementIdx = (
        isset($input['listid']) ?
        intval($input['listid']) :
        -1
    );

    if ($listElementIdx <= 0) {
        return [
            'isSuccess' => false,
            'error' => [
                'invalidListIndex' => true
            ]
        ];
    }

    include($_EnginePath . 'includes/functions/RemoveBuildingFromQueue.php');

    $highlightElementID = RemoveBuildingFromQueue($planet, $user, $listElementIdx);

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $highlightElementID
        ]
    ];
}

//  Arguments:
//      - $user
//      - $planet
//      - $input
//      - $params (Object)
//          - cmd (EnumString: 'insert' | 'destroy')
//
function _handleStructureCommandInsert(&$user, &$planet, &$input, $params) {
    global $_EnginePath;

    $cmd = $params['cmd'];

    $elementID = (
        isset($input['building']) ?
        intval($input['building']) :
        -1
    );

    if (!Elements\isStructure($elementID)) {
        return [
            'isSuccess' => false,
            'error' => [
                'invalidElementID' => true
            ]
        ];
    }

    if (!Elements\isStructureAvailableOnPlanetType($elementID, $planet['planet_type'])) {
        return [
            'isSuccess' => false,
            'error' => [
                'structureUnavailable' => true
            ]
        ];
    }

    if (
        $elementID == 31 &&
        $planet['techQueue_Planet'] > 0 &&
        $planet['techQueue_EndTime'] > 0 &&
        !isLabUpgradableWhileInUse()
    ) {
        return [
            'isSuccess' => false,
            'error' => [
                'researchInProgress' => true
            ]
        ];
    }

    $purchaseMode = (
        $cmd === 'insert' ?
        true :
        false
    );

    include($_EnginePath.'includes/functions/AddBuildingToQueue.php');

    AddBuildingToQueue($planet, $user, $elementID, $purchaseMode);

    return [
        'isSuccess' => true,
        'payload' => [
            'elementID' => $elementID
        ]
    ];
}

function StructuresBuildingPage (&$CurrentPlanet, $CurrentUser) {
    global $_Lang, $_SkinPath, $_GET, $_EnginePath, $_Vars_ElementCategories;

    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    includeLang('worldElements.detailed');

    $currentTimestamp = time();
    $Parse = &$_Lang;
    $highlightElementID = 0;
    $fieldsModifierByQueuedDowngrades = 0;

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $currentTimestamp);

    // Constants
    $const_ElementsPerRow = 7;

    // Get Templates
    $tplBody['list_element']                = gettemplate('buildings_compact_list_element_structures');
    $tplBody['list_levelmodif']             = gettemplate('buildings_compact_list_levelmodif');
    $tplBody['list_hidden']                 = gettemplate('buildings_compact_list_hidden');
    $tplBody['list_row']                    = gettemplate('buildings_compact_list_row');
    $tplBody['list_breakrow']               = gettemplate('buildings_compact_list_breakrow');
    $tplBody['list_disabled']               = gettemplate('buildings_compact_list_disabled');
    $tplBody['list_partdisabled']           = parsetemplate($tplBody['list_disabled'], [ 'AddOpacity' => 'dPart' ]);
    $tplBody['list_disabled']               = parsetemplate($tplBody['list_disabled'], [ 'AddOpacity' => '' ]);
    $tplBody['queue_topinfo']               = gettemplate('buildings_compact_queue_topinfo');
    $tplBody['infobox_body']                = gettemplate('buildings_compact_infobox_body_structures');
    $tplBody['infobox_levelmodif']          = gettemplate('buildings_compact_infobox_levelmodif');
    $tplBody['infobox_req_res']             = gettemplate('buildings_compact_infobox_req_res');
    $tplBody['infobox_req_desttable']       = gettemplate('buildings_compact_infobox_req_desttable');
    $tplBody['infobox_req_destres']         = gettemplate('buildings_compact_infobox_req_destres');
    $tplBody['infobox_additionalnfo']       = gettemplate('buildings_compact_infobox_additionalnfo');
    $tplBody['infobox_req_selector_single'] = gettemplate('buildings_compact_infobox_req_selector_single');
    $tplBody['infobox_req_selector_dual']   = gettemplate('buildings_compact_infobox_req_selector_dual');

    // Handle Commands
    $cmdResult = _handleStructureCommand(
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

    $queueTempResourcesLock = [
        'metal' => 0,
        'crystal' => 0,
        'deuterium' => 0
    ];
    $queueElementLevelModifiers = [];

    // Display queue
    $buildingsQueue = Planets\Queues\parseStructuresQueueString($CurrentPlanet['buildQueue']);
    $queueUnfinishedLenght = 0;

    if (!empty($buildingsQueue)) {
        $queueElementsTplData = [];
        $queueDisplayIdx = 0;

        foreach ($buildingsQueue as $queueIdx => $queueElement) {
            if ($queueElement['endTimestamp'] < $currentTimestamp) {
                continue;
            }

            $listID = $queueDisplayIdx + 1;
            $elementID = $queueElement['elementID'];
            $elementLevel = $queueElement['level'];
            $progressDuration = $queueElement['duration'];
            $progressEndTime = $queueElement['endTimestamp'];
            $progressTimeLeft = $progressEndTime - $currentTimestamp;
            $isUpgrading = (
                $queueElement['mode'] == 'build' ?
                true :
                false
            );
            $isFirstQueueElement = ($queueIdx === 0);

            if ($queueElement['mode'] != 'build') {
                $elementLevel += 1;
            }

            $queueElementTplData = [
                'ListID'                => $listID,
                'ElementNo'             => $listID,
                'ElementID'             => $elementID,
                'Name'                  => $_Lang['tech'][$elementID],
                'Level'                 => $elementLevel,
                'PlanetID'              => $CurrentPlanet['id'],
                'BuildTime'             => pretty_time($progressDuration),
                'EndTimer'              => pretty_time($progressTimeLeft, true),
                'EndTimeExpand'         => date('H:i:s', $progressEndTime),
                'EndDate'               => date('d/m | H:i:s', $progressEndTime),
                'EndDateExpand'         => prettyDate('d m Y', $progressEndTime, 1),

                'ChronoAppletScript'    => '',
                'Data_CancelLock_class' => (
                    Elements\isCancellableOnceInProgress($elementID) ?
                    '' :
                    'premblock'
                ),
                'ModeText'              => (
                    $isUpgrading ?
                    $_Lang['Queue_Mode_Build_1'] :
                    $_Lang['Queue_Mode_Destroy_1']
                ),
                'ModeColor'             => (
                    $isUpgrading ?
                    'lime' :
                    'red'
                ),
                'Lang_CancelBtn_Text'   => (
                    $isFirstQueueElement ?
                    (
                        (!Elements\isCancellableOnceInProgress($elementID)) ?
                        $_Lang['Queue_Cancel_CantCancel'] :
                        (
                            $isUpgrading ?
                            $_Lang['Queue_Cancel_Build'] :
                            $_Lang['Queue_Cancel_Destroy']
                        )
                    ) :
                    $_Lang['Queue_Cancel_Remove']
                ),
                'InfoBox_BuildTime'     => (
                    $isUpgrading ?
                    $_Lang['InfoBox_BuildTime'] :
                    $_Lang['InfoBox_DestroyTime']
                ),

                'SkinPath'              => $_SkinPath,
                'LevelText'             => $_Lang['level'],
                'EndText'               => $_Lang['Queue_EndTime'],
                'EndTitleBeg'           => $_Lang['Queue_EndTitleBeg'],
                'EndTitleHour'          => $_Lang['Queue_EndTitleHour'],
            ];

            if ($isFirstQueueElement) {
                include_once($_EnginePath . '/includes/functions/InsertJavaScriptChronoApplet.php');

                $queueElementTplData['ChronoAppletScript'] = InsertJavaScriptChronoApplet(
                    'QueueFirstTimer',
                    '',
                    $progressEndTime,
                    true,
                    false,
                    'function() { onQueuesFirstElementFinished(); }'
                );
            }

            $queueElementsTplData[] = $queueElementTplData;

            if (!$isFirstQueueElement) {
                $elementCost = GetBuildingPrice($CurrentUser, $CurrentPlanet, $elementID, true, !$isUpgrading);
                $queueTempResourcesLock['metal'] += $elementCost['metal'];
                $queueTempResourcesLock['crystal'] += $elementCost['crystal'];
                $queueTempResourcesLock['deuterium'] += $elementCost['deuterium'];
            }

            if (!isset($queueElementLevelModifiers[$elementID])) {
                $queueElementLevelModifiers[$elementID] = 0;
            }

            $elementPlanetKey = _getElementPlanetKey($elementID);

            if (!$isUpgrading) {
                $queueElementLevelModifiers[$elementID] += 1;
                $CurrentPlanet[$elementPlanetKey] -= 1;
                $fieldsModifierByQueuedDowngrades += 2;
            } else {
                $queueElementLevelModifiers[$elementID] -= 1;
                $CurrentPlanet[$elementPlanetKey] += 1;
            }

            $queueDisplayIdx += 1;
        }

        $queueUnfinishedLenght = $queueDisplayIdx;

        if (!empty($queueElementsTplData)) {
            $Parse['Create_Queue'] = '';

            foreach ($queueElementsTplData as $elementIdx => $queueElementTplData) {
                $tplBody = (
                    $elementIdx === 0 ?
                    gettemplate('buildings_compact_queue_firstel') :
                    gettemplate('buildings_compact_queue_nextel')
                );

                $Parse['Create_Queue'] .= parsetemplate($tplBody, $queueElementTplData);
            }
        }
    } else {
        $Parse['Create_Queue'] = parsetemplate(
            $tplBody['queue_topinfo'],
            [
                'InfoText' => $_Lang['Queue_Empty']
            ]
        );
    }
    // End of - Display queue

    $CurrentPlanet['metal'] -= $queueTempResourcesLock['metal'];
    $CurrentPlanet['crystal'] -= $queueTempResourcesLock['crystal'];
    $CurrentPlanet['deuterium'] -= $queueTempResourcesLock['deuterium'];

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
            $tplBody['queue_topinfo'],
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

        $elementCurrentLevel = Elements\getElementCurrentLevel($elementID, $CurrentPlanet, $CurrentUser);
        $elementNextLevel = $elementCurrentLevel + 1;
        $elementPreviousLevel = $elementCurrentLevel - 1;
        $elementMaxLevel = Elements\getElementMaxUpgradeLevel($elementID);
        $elementQueueLevelModifier = (
            isset($queueElementLevelModifiers[$elementID]) ?
            $queueElementLevelModifiers[$elementID] :
            0
        );
        $isInQueue = isset($queueElementLevelModifiers[$elementID]);
        $isLevelDowngradeable = ($elementPreviousLevel >= 0);
        $isProductionRelatedStructure = in_array($elementID, $_Vars_ElementCategories['prod']);
        $isBlockedByTechResearchProgress = (
            $elementID == 31 &&
            $isBlockingTechResearchInProgress
        );
        $hasReachedMaxLevel = ($elementCurrentLevel >= $elementMaxLevel);
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
        $elementTPLData['ElementLevel'] = prettyNumber($elementCurrentLevel);
        $elementTPLData['ElementRealLevel'] = prettyNumber(
            $elementCurrentLevel +
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

            if ($elementQueueLevelModifier > 0) {
                $elementLevelModifierTPLData['modColor'] = 'red';
                $elementLevelModifierTPLData['modText'] = prettyNumber($elementQueueLevelModifier * (-1));
            } else if ($elementQueueLevelModifier == 0) {
                $elementLevelModifierTPLData['modColor'] = 'orange';
                $elementLevelModifierTPLData['modText'] = '0';
            } else {
                $elementLevelModifierTPLData['modColor'] = 'lime';
                $elementLevelModifierTPLData['modText'] = '+' . prettyNumber($elementQueueLevelModifier * (-1));
            }

            $elementTPLData['LevelModifier'] = parsetemplate($tplBody['infobox_levelmodif'], $elementLevelModifierTPLData);
            $elementTPLData['ElementLevelModif'] = parsetemplate($tplBody['list_levelmodif'], $elementLevelModifierTPLData);
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
                    $tplBody['infobox_req_res'],
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

                $currentResourceState = $resourceStateContainerVariable[$costResourceKey];
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
                        $tplBody['infobox_req_destres'],
                        $elementTPLData['ElementPrices']
                    )
                );
            }

            $Parse['Create_DestroyTips'] .= parsetemplate(
                $tplBody['infobox_req_desttable'],
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
            $elementTPLData['ElementRequirementsHeadline'] = $tplBody['infobox_req_selector_single'];
        } else {
            $elementTPLData['ElementRequirementsHeadline'] = $tplBody['infobox_req_selector_dual'];
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
                    'customLevel' => $elementCurrentLevel,
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
                    $tplBody['infobox_additionalnfo'],
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
                $tplBody['list_partdisabled'] :
                $tplBody['list_disabled']
            );

            $elementTPLData['ElementDisableReason'] = end($upgradeBlockers);
        }

        $elementTPLData['ElementRequirementsHeadline'] = parsetemplate(
            $elementTPLData['ElementRequirementsHeadline'],
            $elementTPLData
        );
        $elementsListIcons[] = parsetemplate($tplBody['list_element'], $elementTPLData);
        $elementsListDetailedInfoboxes[] = parsetemplate($tplBody['infobox_body'], $elementTPLData);
    }

    if (!empty($queueElementLevelModifiers)) {
        foreach ($queueElementLevelModifiers as $elementID => $levelModifier) {
            $elementPlanetKey = _getElementPlanetKey($elementID);

            $CurrentPlanet[$elementPlanetKey] += $levelModifier;
        }
    }
    $CurrentPlanet['metal'] += $queueTempResourcesLock['metal'];
    $CurrentPlanet['crystal'] += $queueTempResourcesLock['crystal'];
    $CurrentPlanet['deuterium'] += $queueTempResourcesLock['deuterium'];

    // Create Structures List
    $groupedStructureRows = Common\Utils\groupInRows($elementsListIcons, $const_ElementsPerRow);
    $parsedStructureRows = array_map(
        function ($elementsInRow) use (&$tplBody, $const_ElementsPerRow) {
            $mergedElementsInRow = implode('', $elementsInRow);
            $emptySpaceFiller = '';

            $elementsInRowCount = count($elementsInRow);

            if ($elementsInRowCount < $const_ElementsPerRow) {
                $emptySpaceFiller = str_repeat(
                    $tplBody['list_hidden'],
                    ($const_ElementsPerRow - $elementsInRowCount)
                );
            }

            return parsetemplate(
                $tplBody['list_row'],
                [
                    'Elements' => ($mergedElementsInRow . $emptySpaceFiller)
                ]
            );
        },
        $groupedStructureRows
    );

    $Parse['Create_StructuresList'] = implode(
        $tplBody['list_breakrow'],
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
