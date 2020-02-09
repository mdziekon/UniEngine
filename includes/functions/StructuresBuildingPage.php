<?php

use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Modules\Development\Components\ModernQueue;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

function StructuresBuildingPage(&$CurrentPlanet, $CurrentUser)
{
    global    $_Lang, $_SkinPath, $_GameConfig, $_GET, $_EnginePath,
            $_Vars_GameElements, $_Vars_ElementCategories, $_Vars_MaxElementLevel, $_Vars_IndestructibleBuildings;

    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    includeLang('worldElements.detailed');

    $Now = time();
    $Parse = &$_Lang;
    $ShowElementID = 0;
    $FieldsModifier = 0;

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $Now);

    // Constants
    $ElementsPerRow = 7;

    // Get Templates
    $TPL['list_element']                = gettemplate('buildings_compact_list_element_structures');
    $TPL['list_levelmodif']                = gettemplate('buildings_compact_list_levelmodif');
    $TPL['list_hidden']                    = gettemplate('buildings_compact_list_hidden');
    $TPL['list_row']                    = gettemplate('buildings_compact_list_row');
    $TPL['list_breakrow']                = gettemplate('buildings_compact_list_breakrow');
    $TPL['list_disabled']                = gettemplate('buildings_compact_list_disabled');
    $TPL['list_partdisabled']            = parsetemplate($TPL['list_disabled'], array('AddOpacity' => 'dPart'));
    $TPL['list_disabled']                = parsetemplate($TPL['list_disabled'], array('AddOpacity' => ''));
    $TPL['infobox_body']                = gettemplate('buildings_compact_infobox_body_structures');
    $TPL['infobox_levelmodif']            = gettemplate('buildings_compact_infobox_levelmodif');
    $TPL['infobox_req_res']                = gettemplate('buildings_compact_infobox_req_res');
    $TPL['infobox_additionalnfo']        = gettemplate('buildings_compact_infobox_additionalnfo');
    $TPL['infobox_req_selector_single'] = gettemplate('buildings_compact_infobox_req_selector_single');
    $TPL['infobox_req_selector_dual']    = gettemplate('buildings_compact_infobox_req_selector_dual');

    // Handle Commands
    $cmdResult = Development\Input\UserCommands\handleStructureCommand(
        $CurrentUser,
        $CurrentPlanet,
        $_GET,
        [
            "timestamp" => $Now
        ]
    );

    if ($cmdResult['isSuccess']) {
        $ShowElementID = $cmdResult['payload']['elementID'];
    }
    // End of - Handle Commands

    $queueComponent = ModernQueue\render([
        'planet' => &$CurrentPlanet,
        'queue' => Planets\Queues\Structures\parseQueueString($CurrentPlanet['buildQueue']),
        'queueMaxLength' => Users\getMaxStructuresQueueLength($CurrentUser),
        'timestamp' => $Now,
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

    // Parse Queue
    $CurrentQueue = $CurrentPlanet['buildQueue'];
    if (!empty($CurrentQueue)) {
        $LockResources['metal'] = 0;
        $LockResources['crystal'] = 0;
        $LockResources['deuterium'] = 0;

        $CurrentQueue = explode(';', $CurrentQueue);
        $QueueIndex = 0;

        foreach ($CurrentQueue as $QueueID => $QueueData) {
            $QueueData = explode(',', $QueueData);
            $BuildEndTime = $QueueData[3];

            if ($BuildEndTime < $Now) {
                continue;
            }

            $ElementID = $QueueData[0];
            $ElementMode = $QueueData[4];
            $ThisForDestroy = ($ElementMode != 'build');

            if ($QueueIndex != 0) {
                $GetResourcesToLock = GetBuildingPrice($CurrentUser, $CurrentPlanet, $ElementID, true, $ThisForDestroy);
                $LockResources['metal'] += $GetResourcesToLock['metal'];
                $LockResources['crystal'] += $GetResourcesToLock['crystal'];
                $LockResources['deuterium'] += $GetResourcesToLock['deuterium'];
            }

            if (!isset($LevelModifiers[$ElementID])) {
                $LevelModifiers[$ElementID] = 0;
            }
            if ($ThisForDestroy) {
                $LevelModifiers[$ElementID] += 1;
                $CurrentPlanet[$_Vars_GameElements[$ElementID]] -= 1;
                $FieldsModifier += 2;
            } else {
                $LevelModifiers[$ElementID] -= 1;
                $CurrentPlanet[$_Vars_GameElements[$ElementID]] += 1;
            }

            $QueueIndex += 1;
        }

        $CurrentPlanet['metal'] -= (isset($LockResources['metal']) ? $LockResources['metal'] : 0);
        $CurrentPlanet['crystal'] -= (isset($LockResources['crystal']) ? $LockResources['crystal'] : 0);
        $CurrentPlanet['deuterium'] -= (isset($LockResources['deuterium']) ? $LockResources['deuterium'] : 0);

        $Queue['lenght'] = $QueueIndex;
    } else {
        $Queue['lenght'] = 0;
    }
    // End of - Parse Queue

    // Parse all available buildings
    if(($CurrentPlanet['field_current'] + $Queue['lenght'] - $FieldsModifier) < CalculateMaxPlanetFields($CurrentPlanet))
    {
        $HasLeftFields = true;
    }
    else
    {
        $HasLeftFields = false;
    }
    if($Queue['lenght'] < ((isPro($CurrentUser)) ? MAX_BUILDING_QUEUE_SIZE_PRO : MAX_BUILDING_QUEUE_SIZE))
    {
        $CanAddToQueue = true;
    }
    else
    {
        $CanAddToQueue = false;
    }

    $ResImages = array
    (
        'metal' => 'metall',
        'crystal' => 'kristall',
        'deuterium' => 'deuterium',
        'energy_max' => 'energie',
        'darkEnergy' => 'darkenergy'
    );
    $ResLangs = array
    (
        'metal' => $_Lang['Metal'],
        'crystal' => $_Lang['Crystal'],
        'deuterium' => $_Lang['Deuterium'],
        'energy_max' => $_Lang['Energy'],
        'darkEnergy' => $_Lang['DarkEnergy']
    );

    $ElementParserDefault = array
    (
        'SkinPath'                        => $_SkinPath,
        'InfoBox_Level'                    => $_Lang['InfoBox_Level'],
        'InfoBox_Build'                    => $_Lang['InfoBox_Build'],
        'InfoBox_Destroy'                => $_Lang['InfoBox_Destroy'],
        'InfoBox_RequirementsFor'        => $_Lang['InfoBox_RequirementsFor'],
        'InfoBox_ResRequirements'        => $_Lang['InfoBox_ResRequirements'],
        'InfoBox_Requirements_Res'        => $_Lang['InfoBox_Requirements_Res'],
        'InfoBox_Requirements_Tech'        => $_Lang['InfoBox_Requirements_Tech'],
        'InfoBox_BuildTime'                => $_Lang['InfoBox_BuildTime'],
    );

    $hasElementsInQueue = ($Queue['lenght'] > 0);
    $resourceLabels = [
        'metal'         => $_Lang['Metal'],
        'crystal'       => $_Lang['Crystal'],
        'deuterium'     => $_Lang['Deuterium'],
        'energy'        => $_Lang['Energy'],
        'energy_max'    => $_Lang['Energy'],
        'darkEnergy'    => $_Lang['DarkEnergy']
    ];

    $elementsDestructionDetails = [];

    foreach($_Vars_ElementCategories['build'] as $ElementID)
    {
        if(in_array($ElementID, $_Vars_ElementCategories['buildOn'][$CurrentPlanet['planet_type']]))
        {
            $ElementParser = $ElementParserDefault;

            $CurrentLevel = $CurrentPlanet[$_Vars_GameElements[$ElementID]];
            $NextLevel = $CurrentPlanet[$_Vars_GameElements[$ElementID]] + 1;
            $MaxLevelReached = false;
            $TechLevelOK = false;
            $HasResources = true;

            $HideButton_Build = false;
            $HideButton_Destroy = false;
            $HideButton_QuickBuild = false;

            $ElementParser['HideBuildWarn'] = 'hide';
            $ElementParser['ElementName'] = $_Lang['tech'][$ElementID];
            $ElementParser['ElementID'] = $ElementID;
            $ElementParser['ElementLevel'] = prettyNumber($CurrentPlanet[$_Vars_GameElements[$ElementID]]);
            $ElementParser['ElementRealLevel'] = prettyNumber($CurrentPlanet[$_Vars_GameElements[$ElementID]] + (isset($LevelModifiers[$ElementID]) ? $LevelModifiers[$ElementID] : 0));
            $ElementParser['BuildLevel'] = prettyNumber($CurrentPlanet[$_Vars_GameElements[$ElementID]] + 1);
            $ElementParser['DestroyLevel'] = prettyNumber($CurrentPlanet[$_Vars_GameElements[$ElementID]] - 1);
            $ElementParser['Desc'] = $_Lang['WorldElements_Detailed'][$ElementID]['description_short'];
            $ElementParser['BuildButtonColor'] = 'buildDo_Green';
            $ElementParser['DestroyButtonColor'] = 'buildDo_Red';

            if(isset($LevelModifiers[$ElementID]))
            {
                if($LevelModifiers[$ElementID] > 0)
                {
                    $ElementParser['levelmodif']['modColor'] = 'red';
                    $ElementParser['levelmodif']['modText'] = prettyNumber($LevelModifiers[$ElementID] * (-1));
                }
                else if($LevelModifiers[$ElementID] == 0)
                {
                    $ElementParser['levelmodif']['modColor'] = 'orange';
                    $ElementParser['levelmodif']['modText'] = '0';
                }
                else
                {
                    $ElementParser['levelmodif']['modColor'] = 'lime';
                    $ElementParser['levelmodif']['modText'] = '+'.prettyNumber($LevelModifiers[$ElementID] * (-1));
                }
                $ElementParser['LevelModifier'] = parsetemplate($TPL['infobox_levelmodif'], $ElementParser['levelmodif']);
                $ElementParser['ElementLevelModif'] = parsetemplate($TPL['list_levelmodif'], $ElementParser['levelmodif']);
                unset($ElementParser['levelmodif']);
            }

            if(!(isset($_Vars_MaxElementLevel[$ElementID]) && $_Vars_MaxElementLevel[$ElementID] > 0 && $NextLevel > $_Vars_MaxElementLevel[$ElementID]))
            {
                $ElementParser['ElementPrice'] = GetBuildingPrice($CurrentUser, $CurrentPlanet, $ElementID, true, false, true);
                foreach($ElementParser['ElementPrice'] as $Key => $Value)
                {
                    if($Value > 0)
                    {
                        $ResColor = '';
                        $ResMinusColor = '';
                        $MinusValue = '&nbsp;';

                        if($Key != 'darkEnergy')
                        {
                            $UseVar = &$CurrentPlanet;
                        }
                        else
                        {
                            $UseVar = &$CurrentUser;
                        }
                        if($UseVar[$Key] < $Value)
                        {
                            $ResMinusColor = 'red';
                            $MinusValue = '('.prettyNumber($UseVar[$Key] - $Value).')';
                            if($Queue['lenght'] > 0)
                            {
                                $ResColor = 'orange';
                            }
                            else
                            {
                                $ResColor = 'red';
                            }
                        }

                        $ElementParser['ElementPrices'] = array
                        (
                            'SkinPath' => $_SkinPath,
                            'ResName' => $Key,
                            'ResImg' => $ResImages[$Key],
                            'ResColor' => $ResColor,
                            'Value' => prettyNumber($Value),
                            'ResMinusColor' => $ResMinusColor,
                            'MinusValue' => $MinusValue,
                        );
                        if(!isset($ElementParser['ElementPriceDiv']))
                        {
                            $ElementParser['ElementPriceDiv'] = '';
                        }
                        $ElementParser['ElementPriceDiv'] .= parsetemplate($TPL['infobox_req_res'], $ElementParser['ElementPrices']);
                    }
                }
                $ElementParser['BuildTime'] = pretty_time(GetBuildingTime($CurrentUser, $CurrentPlanet, $ElementID));
            }
            else
            {
                $MaxLevelReached = true;
                $ElementParser['HideBuildInfo'] = 'hide';
                $ElementParser['HideBuildWarn'] = '';
                $HideButton_Build = true;
                $ElementParser['BuildWarn_Color'] = 'red';
                $ElementParser['BuildWarn_Text'] = $_Lang['ListBox_Disallow_MaxLevelReached'];
            }
            if($CurrentLevel == 0 || (isset($_Vars_IndestructibleBuildings[$ElementID]) && $_Vars_IndestructibleBuildings[$ElementID]))
            {
                $HideButton_Destroy = true;
            }
            if(IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $ElementID))
            {
                $TechLevelOK = true;
                $ElementParser['ElementRequirementsHeadline'] = $TPL['infobox_req_selector_single'];
            }
            else
            {
                $ElementParser['ElementRequirementsHeadline'] = $TPL['infobox_req_selector_dual'];
                $ElementParser['ElementTechDiv'] = GetElementTechReq($CurrentUser, $CurrentPlanet, $ElementID, true);
                $ElementParser['HideResReqDiv'] = 'hide';
            }
            if(IsElementBuyable($CurrentUser, $CurrentPlanet, $ElementID, false) === false)
            {
                $HasResources = false;
                if($Queue['lenght'] == 0)
                {
                    $ElementParser['BuildButtonColor'] = 'buildDo_Gray';
                    $HideButton_QuickBuild = true;
                }
                else
                {
                    $ElementParser['BuildButtonColor'] = 'buildDo_Orange';
                }
            }
            if(IsElementBuyable($CurrentUser, $CurrentPlanet, $ElementID, true) === false)
            {
                if($Queue['lenght'] == 0)
                {
                    $ElementParser['DestroyButtonColor'] = 'destroyDo_Gray';
                }
            }

            $BlockReason = array();

            if($MaxLevelReached)
            {
                $BlockReason[] = $_Lang['ListBox_Disallow_MaxLevelReached'];
            }
            else if(!$HasResources)
            {
                $BlockReason[] = $_Lang['ListBox_Disallow_NoResources'];
            }
            if(!$TechLevelOK)
            {
                $BlockReason[] = $_Lang['ListBox_Disallow_NoTech'];
                $ElementParser['BuildButtonColor'] = 'buildDo_Gray';
                $HideButton_QuickBuild = true;
                $HideButton_Destroy = true;
            }
            if($ElementID == 31 AND $CurrentUser['techQueue_Planet'] > 0 AND $CurrentUser['techQueue_EndTime'] > 0 AND $_GameConfig['BuildLabWhileRun'] != 1)
            {
                $BlockReason[] = $_Lang['ListBox_Disallow_LabResearch'];
                $ElementParser['BuildButtonColor'] = 'buildDo_Gray';
                $ElementParser['DestroyButtonColor'] = 'destroyDo_Gray';
                $HideButton_QuickBuild = true;
            }
            if($HasLeftFields === false)
            {
                $BlockReason[] = $_Lang['ListBox_Disallow_NoFreeFields'];
                $ElementParser['BuildButtonColor'] = 'buildDo_Gray';
                $HideButton_QuickBuild = true;
            }
            if($CanAddToQueue === false)
            {
                $BlockReason[] = $_Lang['ListBox_Disallow_QueueIsFull'];
                $ElementParser['BuildButtonColor'] = 'buildDo_Gray';
                $HideButton_QuickBuild = true;
            }
            if(isOnVacation($CurrentUser))
            {
                $BlockReason[] = $_Lang['ListBox_Disallow_VacationMode'];
                $ElementParser['BuildButtonColor'] = 'buildDo_Gray';
                $ElementParser['DestroyButtonColor'] = 'destroyDo_Gray';
                $HideButton_QuickBuild = true;
            }

            if(!empty($BlockReason))
            {
                if($ElementParser['BuildButtonColor'] == 'buildDo_Orange')
                {
                    $ElementParser['ElementDisabled'] = $TPL['list_partdisabled'];
                }
                else
                {
                    $ElementParser['ElementDisabled'] = $TPL['list_disabled'];
                }
                $ElementParser['ElementDisableReason'] = end($BlockReason);
            }

            if($HideButton_Build)
            {
                $ElementParser['HideBuildButton'] = 'hide';
            }
            if($HideButton_Build OR $HideButton_QuickBuild)
            {
                $ElementParser['HideQuickBuildButton'] = 'hide';
            }
            if($HideButton_Destroy)
            {
                $ElementParser['HideDestroyButton'] = 'hide';
            }
            else
            {
                $downgradeCost = Elements\calculatePurchaseCost(
                    $ElementID,
                    Elements\getElementState($ElementID, $CurrentPlanet, $CurrentUser),
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

                $destructionTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $ElementID) / 2;

                $elementsDestructionDetails[$ElementID] = [
                    'resources' => $elementDowngradeResources,
                    'destructionTime' => pretty_time($destructionTime)
                ];
            }

            if(in_array($ElementID, $_Vars_ElementCategories['prod']))
            {
                // Calculate theoretical production increase
                $thisLevelProduction = getElementProduction(
                    $ElementID,
                    $CurrentPlanet,
                    $CurrentUser,
                    [
                        'useCurrentBoosters' => true,
                        'currentTimestamp' => $Now,
                        'customLevel' => $CurrentLevel,
                        'customProductionFactor' => 10
                    ]
                );
                $nextLevelProduction = getElementProduction(
                    $ElementID,
                    $CurrentPlanet,
                    $CurrentUser,
                    [
                        'useCurrentBoosters' => true,
                        'currentTimestamp' => $Now,
                        'customLevel' => ($CurrentLevel + 1),
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

                    $ElementParser['AdditionalNfo'][] = parsetemplate(
                        $TPL['infobox_additionalnfo'],
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
            }

            if(!empty($ElementParser['AdditionalNfo'])) {
                $ElementParser['AdditionalNfo'] = implode('', $ElementParser['AdditionalNfo']);
            }
            $ElementParser['ElementRequirementsHeadline'] = parsetemplate($ElementParser['ElementRequirementsHeadline'], $ElementParser);
            $StructuresList[] = parsetemplate($TPL['list_element'], $ElementParser);
            $InfoBoxes[] = parsetemplate($TPL['infobox_body'], $ElementParser);
        }
    }

    if(!empty($LevelModifiers))
    {
        foreach($LevelModifiers as $ElementID => $Modifier)
        {
            $CurrentPlanet[$_Vars_GameElements[$ElementID]] += $Modifier;
        }
    }
    $CurrentPlanet['metal'] += (isset($LockResources['metal']) ? $LockResources['metal'] : 0);
    $CurrentPlanet['crystal'] += (isset($LockResources['crystal']) ? $LockResources['crystal'] : 0);
    $CurrentPlanet['deuterium'] += (isset($LockResources['deuterium']) ? $LockResources['deuterium'] : 0);

    // Create Structures List
    $ThisRowIndex = 0;
    $InRowCount = 0;
    foreach($StructuresList as $ParsedData)
    {
        if($InRowCount == $ElementsPerRow)
        {
            $ParsedRows[($ThisRowIndex + 1)] = $TPL['list_breakrow'];
            $ThisRowIndex += 2;
            $InRowCount = 0;
        }

        if(!isset($StructureRows[$ThisRowIndex]['Elements']))
        {
            $StructureRows[$ThisRowIndex]['Elements'] = '';
        }
        $StructureRows[$ThisRowIndex]['Elements'] .= $ParsedData;
        $InRowCount += 1;
    }
    if($InRowCount < $ElementsPerRow)
    {
        if(!isset($StructureRows[$ThisRowIndex]['Elements']))
        {
            $StructureRows[$ThisRowIndex]['Elements'] = '';
        }
        $StructureRows[$ThisRowIndex]['Elements'] .= str_repeat($TPL['list_hidden'], ($ElementsPerRow - $InRowCount));
    }
    foreach($StructureRows as $Index => $Data)
    {
        $ParsedRows[$Index] = parsetemplate($TPL['list_row'], $Data);
    }
    ksort($ParsedRows, SORT_ASC);
    $Parse['Create_StructuresList'] = implode('', $ParsedRows);
    $Parse['Create_ElementsInfoBoxes'] = implode('', $InfoBoxes);
    if($ShowElementID > 0)
    {
        $Parse['Create_ShowElementOnStartup'] = $ShowElementID;
    }
    $MaxFields = CalculateMaxPlanetFields($CurrentPlanet);
    if($CurrentPlanet['field_current'] == $MaxFields)
    {
        $Parse['Insert_Overview_Fields_Used_Color'] = 'red';
    }
    else if($CurrentPlanet['field_current'] >= ($MaxFields * 0.9))
    {
        $Parse['Insert_Overview_Fields_Used_Color'] = 'orange';
    }
    else
    {
        $Parse['Insert_Overview_Fields_Used_Color'] = 'lime';
    }
    // End of - Parse all available buildings

    $Parse['Insert_SkinPath'] = $_SkinPath;
    $Parse['Insert_PlanetImg'] = $CurrentPlanet['image'];
    $Parse['Insert_PlanetType'] = $_Lang['PlanetType_'.$CurrentPlanet['planet_type']];
    $Parse['Insert_PlanetName'] = $CurrentPlanet['name'];
    $Parse['Insert_PlanetPos_Galaxy'] = $CurrentPlanet['galaxy'];
    $Parse['Insert_PlanetPos_System'] = $CurrentPlanet['system'];
    $Parse['Insert_PlanetPos_Planet'] = $CurrentPlanet['planet'];
    $Parse['Insert_Overview_Diameter'] = prettyNumber($CurrentPlanet['diameter']);
    $Parse['Insert_Overview_Fields_Used'] = prettyNumber($CurrentPlanet['field_current']);
    $Parse['Insert_Overview_Fields_Max'] = prettyNumber($MaxFields);
    $Parse['Insert_Overview_Fields_Percent'] = sprintf('%0.2f', ($CurrentPlanet['field_current'] / $MaxFields) * 100);
    $Parse['Insert_Overview_Temperature'] = sprintf($_Lang['Overview_Form_Temperature'], $CurrentPlanet['temp_min'], $CurrentPlanet['temp_max']);
    $Parse['PHPData_ElementsDestructionDetailsJSON'] = json_encode($elementsDestructionDetails);

    $Page = parsetemplate(gettemplate('buildings_compact_body_structures'), $Parse);

    display($Page, $_Lang['Builds']);
}

?>
