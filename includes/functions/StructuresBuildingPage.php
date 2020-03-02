<?php

use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Modules\Development\Components\ModernQueue;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

function StructuresBuildingPage(&$CurrentPlanet, $CurrentUser)
{
    global $_Lang, $_SkinPath, $_GET, $_EnginePath, $_Vars_ElementCategories;

    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    includeLang('worldElements.detailed');

    $Now = time();
    $Parse = &$_Lang;
    $ShowElementID = 0;

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $Now);

    // Constants
    $ElementsPerRow = 7;

    // Get Templates
    $TPL['list_hidden']         = gettemplate('buildings_compact_list_hidden');
    $TPL['list_row']            = gettemplate('buildings_compact_list_row');
    $TPL['list_breakrow']       = gettemplate('buildings_compact_list_breakrow');

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
    $hasAvailableFieldsOnPlanet = (
        ($CurrentPlanet['field_current'] + $planetFieldsUsageCounter) <
        CalculateMaxPlanetFields($CurrentPlanet)
    );
    $isQueueFull = (
        $elementsInQueue >=
        Users\getMaxStructuresQueueLength($CurrentUser)
    );
    $hasElementsInQueue = ($elementsInQueue > 0);
    $isUserOnVacation = isOnVacation($CurrentUser);

    $resourceLabels = [
        'metal'         => $_Lang['Metal'],
        'crystal'       => $_Lang['Crystal'],
        'deuterium'     => $_Lang['Deuterium'],
        'energy'        => $_Lang['Energy'],
        'energy_max'    => $_Lang['Energy'],
        'darkEnergy'    => $_Lang['DarkEnergy']
    ];

    $elementsDestructionDetails = [];

    foreach($_Vars_ElementCategories['build'] as $ElementID) {
        if (!Elements\isStructureAvailableOnPlanetType($ElementID, $CurrentPlanet['planet_type'])) {
            continue;
        }

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
        $hasReachedMaxLevel = (
            $elementQueuedLevel >=
            $elementMaxLevel
        );

        $hasUpgradeResources = IsElementBuyable($CurrentUser, $CurrentPlanet, $ElementID, false);
        $hasDowngradeResources = IsElementBuyable($CurrentUser, $CurrentPlanet, $ElementID, true);

        $hasTechnologyRequirementMet = IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $ElementID);
        $isBlockedByTechResearchProgress = (
            $ElementID == 31 &&
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

        $isDowngradePossible = (
            ($elementQueuedLevel > 0) &&
            !Elements\isIndestructibleStructure($ElementID)
        );
        $isDowngradeQueueable = (
            $isDowngradePossible &&
            !$isUserOnVacation &&
            !$isQueueFull &&
            !$isBlockedByTechResearchProgress
        );
        $isDowngradeAvailableNow = (
            $isDowngradeQueueable &&
            $hasDowngradeResources
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
        if ($isBlockedByTechResearchProgress) {
            $BlockReason[] = $_Lang['ListBox_Disallow_LabResearch'];
        }
        if (!$hasAvailableFieldsOnPlanet) {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoFreeFields'];
        }
        if ($isQueueFull) {
            $BlockReason[] = $_Lang['ListBox_Disallow_QueueIsFull'];
        }
        if ($isUserOnVacation) {
            $BlockReason[] = $_Lang['ListBox_Disallow_VacationMode'];
        }

        if ($isDowngradePossible) {
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

                $resourceCostColor = classNames([
                    'red' => ($hasResourceDeficit && !$hasElementsInQueue),
                    'orange' => ($hasResourceDeficit && $hasElementsInQueue),
                ]);

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
                return "?cmd=insert&amp;building={$ElementID}";
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
                'isDowngradePossible' => $isDowngradePossible,
                'isDowngradeAvailable' => $isDowngradeAvailableNow,
                'isDowngradeQueueable' => $isDowngradeQueueable,
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
                                'currentState' => $elementCurrentLevel,
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

        $StructuresList[] = $iconComponent['componentHTML'];
        $InfoBoxes[] = $cardInfoComponent['componentHTML'];
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
