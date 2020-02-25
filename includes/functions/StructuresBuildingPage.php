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

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $Now);

    // Constants
    $ElementsPerRow = 7;

    // Get Templates
    $TPL['list_element']        = gettemplate('buildings_compact_list_element_structures');
    $TPL['list_levelmodif']     = gettemplate('buildings_compact_list_levelmodif');
    $TPL['list_hidden']         = gettemplate('buildings_compact_list_hidden');
    $TPL['list_row']            = gettemplate('buildings_compact_list_row');
    $TPL['list_breakrow']       = gettemplate('buildings_compact_list_breakrow');
    $TPL['list_disabled']       = gettemplate('buildings_compact_list_disabled');
    $TPL['list_partdisabled']   = parsetemplate($TPL['list_disabled'], array('AddOpacity' => 'dPart'));
    $TPL['list_disabled']       = parsetemplate($TPL['list_disabled'], array('AddOpacity' => ''));

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
    $structuresQueueContent = Planets\Queues\Structures\parseQueueString(
        $CurrentPlanet['buildQueue']
    );

    $queueComponent = ModernQueue\render([
        'planet' => &$CurrentPlanet,
        'queue' => $structuresQueueContent,
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

    $queueStateDetails = Development\Utils\getQueueStateDetails([
        'queue' => [
            'type' => Development\Utils\QueueType::Planetary,
            'content' => $structuresQueueContent,
        ],
        'user' => $CurrentUser,
        'planet' => $CurrentPlanet,
    ]);
    $elementsInQueue = $queueStateDetails['queuedElementsCount'];
    $planetFieldsUsageCounter = 0;

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

    $Parse['Create_Queue'] = $queueComponent['componentHTML'];

    // Parse all available buildings
    if(($CurrentPlanet['field_current'] + $planetFieldsUsageCounter) < CalculateMaxPlanetFields($CurrentPlanet))
    {
        $HasLeftFields = true;
    }
    else
    {
        $HasLeftFields = false;
    }
    if($elementsInQueue < ((isPro($CurrentUser)) ? MAX_BUILDING_QUEUE_SIZE_PRO : MAX_BUILDING_QUEUE_SIZE))
    {
        $CanAddToQueue = true;
    }
    else
    {
        $CanAddToQueue = false;
    }

    $hasElementsInQueue = ($elementsInQueue > 0);
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
            $ElementParser = [
                'SkinPath' => $_SkinPath,
            ];

            $CurrentLevel = $CurrentPlanet[$_Vars_GameElements[$ElementID]];
            $NextLevel = $CurrentPlanet[$_Vars_GameElements[$ElementID]] + 1;
            $isElementInQueue = isset(
                $queueStateDetails['queuedElementLevelModifiers'][$ElementID]
            );
            $elementQueueLevelModifier = (
                $isElementInQueue ?
                $queueStateDetails['queuedElementLevelModifiers'][$ElementID] :
                0
            );

            $MaxLevelReached = false;
            $TechLevelOK = false;
            $HasResources = true;

            $HideButton_Build = false;
            $HideButton_Destroy = false;
            $HideButton_QuickBuild = false;

            $ElementParser['ElementName'] = $_Lang['tech'][$ElementID];
            $ElementParser['ElementID'] = $ElementID;
            $ElementParser['ElementRealLevel'] = prettyNumber(
                $CurrentPlanet[$_Vars_GameElements[$ElementID]] +
                ($elementQueueLevelModifier * -1)
            );
            $ElementParser['BuildButtonColor'] = 'buildDo_Green';

            if($isElementInQueue)
            {
                $levelmodif = [];
                if($elementQueueLevelModifier < 0)
                {
                    $levelmodif['modColor'] = 'red';
                    $levelmodif['modText'] = prettyNumber($elementQueueLevelModifier);
                }
                else if($elementQueueLevelModifier == 0)
                {
                    $levelmodif['modColor'] = 'orange';
                    $levelmodif['modText'] = '0';
                }
                else
                {
                    $levelmodif['modColor'] = 'lime';
                    $levelmodif['modText'] = '+'.prettyNumber($elementQueueLevelModifier);
                }
                $ElementParser['ElementLevelModif'] = parsetemplate($TPL['list_levelmodif'], $levelmodif);
            }

            if(!(isset($_Vars_MaxElementLevel[$ElementID]) && $_Vars_MaxElementLevel[$ElementID] > 0 && $NextLevel > $_Vars_MaxElementLevel[$ElementID]))
            {}
            else
            {
                $MaxLevelReached = true;
                $HideButton_Build = true;
            }
            if($CurrentLevel == 0 || (isset($_Vars_IndestructibleBuildings[$ElementID]) && $_Vars_IndestructibleBuildings[$ElementID]))
            {
                $HideButton_Destroy = true;
            }
            if(IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $ElementID))
            {
                $TechLevelOK = true;
            }

            if(IsElementBuyable($CurrentUser, $CurrentPlanet, $ElementID, false) === false)
            {
                $HasResources = false;
                if($elementsInQueue == 0)
                {
                    $ElementParser['BuildButtonColor'] = 'buildDo_Gray';
                    $HideButton_QuickBuild = true;
                }
                else
                {
                    $ElementParser['BuildButtonColor'] = 'buildDo_Orange';
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

            if($HideButton_Build OR $HideButton_QuickBuild)
            {
                $ElementParser['HideQuickBuildButton'] = 'hide';
            }
            if(!$HideButton_Destroy) {
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

            $StructuresList[] = parsetemplate($TPL['list_element'], $ElementParser);

            $elementMaxLevel = Elements\getElementMaxUpgradeLevel($ElementID);
            $hasReachedMaxLevel = ($CurrentPlanet[$_Vars_GameElements[$ElementID]] >= $elementMaxLevel);

            $hasUpgradeResources = $HasResources;
            $hasTechnologyRequirementMet = $TechLevelOK;
            $isBlockedByTechResearchProgress = (
                $ElementID == 31 &&
                $CurrentUser['techQueue_Planet'] > 0 &&
                $CurrentUser['techQueue_EndTime'] > 0 &&
                $_GameConfig['BuildLabWhileRun'] != 1
            );
            $hasAvailableFieldsOnPlanet = $HasLeftFields;
            $isQueueFull = !$CanAddToQueue;
            $isOnVacation = isOnVacation($CurrentUser);
            $hasDowngradeResources = IsElementBuyable(
                $CurrentUser,
                $CurrentPlanet,
                $ElementID,
                true
            );

            $cardInfoComponent = Development\Components\GridViewElementCard\render([
                'elementID' => $ElementID,
                'user' => $CurrentUser,
                'planet' => $CurrentPlanet,
                'isQueueActive' => $hasElementsInQueue,
                'elementDetails' => [
                    'currentState' => (
                        $CurrentPlanet[$_Vars_GameElements[$ElementID]] +
                        ($elementQueueLevelModifier * -1)
                    ),
                    'isInQueue' => $isElementInQueue,
                    'queueLevelModifier' => $elementQueueLevelModifier,
                    'isUpgradePossible' => (
                        !$hasReachedMaxLevel
                    ),
                    'isUpgradeAvailable' => (
                        $hasUpgradeResources &&
                        !$hasReachedMaxLevel &&
                        $hasTechnologyRequirementMet &&
                        !$isBlockedByTechResearchProgress &&
                        $hasAvailableFieldsOnPlanet &&
                        !$isQueueFull &&
                        !$isOnVacation
                    ),
                    'isUpgradeQueueable' => (
                        !$hasReachedMaxLevel &&
                        $hasTechnologyRequirementMet &&
                        !$isBlockedByTechResearchProgress &&
                        $hasAvailableFieldsOnPlanet &&
                        !$isQueueFull &&
                        !$isOnVacation
                    ),
                    'whyUpgradeImpossible' => [
                        (
                            $hasReachedMaxLevel ?
                            $_Lang['ListBox_Disallow_MaxLevelReached'] :
                            ''
                        ),
                    ],
                    'isDowngradePossible' => (
                        ($CurrentPlanet[$_Vars_GameElements[$ElementID]] > 0) &&
                        !Elements\isIndestructibleStructure($ElementID)
                    ),
                    'isDowngradeAvailable' => (
                        $hasDowngradeResources &&
                        !$isBlockedByTechResearchProgress &&
                        !$isQueueFull &&
                        !$isOnVacation
                    ),
                    'isDowngradeQueueable' => (
                        !$isBlockedByTechResearchProgress &&
                        !$isQueueFull &&
                        !$isOnVacation
                    ),
                    'hasTechnologyRequirementMet' => $hasTechnologyRequirementMet,
                    'additionalUpgradeDetailsRows' => [
                        (
                            in_array($ElementID, $_Vars_ElementCategories['prod']) ?
                            Development\Components\GridViewElementCard\UpgradeProductionChange\render([
                                'elementID' => $ElementID,
                                'user' => $CurrentUser,
                                'planet' => $CurrentPlanet,
                                'timestamp' => $Now,
                                'elementDetails' => [
                                    'currentState' => (
                                        $CurrentPlanet[$_Vars_GameElements[$ElementID]] +
                                        ($elementQueueLevelModifier * -1)
                                    ),
                                    'queueLevelModifier' => $elementQueueLevelModifier,
                                ],
                            ])['componentHTML'] :
                            ''
                        ),
                    ],
                ],
                'getUpgradeElementActionLinkHref' => function () use ($ElementID) {
                    return "?cmd=insert&amp;building={$ElementID}";
                },
                'getDowngradeElementActionLinkHref' => function () use ($ElementID) {
                    return "?cmd=destroy&amp;building={$ElementID}";
                },
            ]);

            $InfoBoxes[] = $cardInfoComponent['componentHTML'];
        }
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
