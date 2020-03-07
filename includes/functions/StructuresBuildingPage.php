<?php

use UniEngine\Engine\Includes\Helpers\Common;
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
    $pageTemplateData = &$_Lang;
    $ShowElementID = 0;

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $Now);

    // Constants
    $const_ElementsPerRow = 7;

    // Get Templates
    $tplBodyCache = [
        'pageBody' => gettemplate('buildings_compact_body_structures'),
        'list_hidden' => gettemplate('buildings_compact_list_hidden'),
        'list_row' => gettemplate('buildings_compact_list_row'),
        'list_breakrow' => gettemplate('buildings_compact_list_breakrow'),
    ];

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

    // Parse all available buildings
    $planetsMaxFieldsCount = CalculateMaxPlanetFields($CurrentPlanet);
    $hasAvailableFieldsOnPlanet = (
        ($CurrentPlanet['field_current'] + $planetFieldsUsageCounter) <
        $planetsMaxFieldsCount
    );
    $isQueueFull = (
        $elementsInQueue >=
        Users\getMaxStructuresQueueLength($CurrentUser)
    );
    $hasElementsInQueue = ($elementsInQueue > 0);
    $isUserOnVacation = isOnVacation($CurrentUser);

    $elementsIconComponents = [];
    $elementsCardComponents = [];
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
            $elementsDestructionDetails[$ElementID] = Development\Utils\Structures\getDestructionDetails([
                'elementID' => $ElementID,
                'planet' => $CurrentPlanet,
                'user' => $CurrentUser,
                'isQueueActive' => $hasElementsInQueue,
            ]);
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
                        Elements\isProductionRelated($ElementID) ?
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

        $elementsIconComponents[] = $iconComponent['componentHTML'];
        $elementsCardComponents[] = $cardInfoComponent['componentHTML'];
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
    $groupedIcons = Common\Collections\groupInRows($elementsIconComponents, $const_ElementsPerRow);
    $groupedIconRows = array_map(
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
        $groupedIcons
    );

    $pageTemplateData['Create_Queue'] = $queueComponent['componentHTML'];
    $pageTemplateData['Create_StructuresList'] = implode(
        $tplBodyCache['list_breakrow'],
        $groupedIconRows
    );
    $pageTemplateData['Create_ElementsInfoBoxes'] = implode('', $elementsCardComponents);
    $pageTemplateData['Create_ShowElementOnStartup'] = (
        $ShowElementID > 0 ?
        $ShowElementID :
        ''
    );
    $pageTemplateData['Insert_Overview_Fields_Used_Color'] = classNames([
        'red' => ($CurrentPlanet['field_current'] >= $planetsMaxFieldsCount),
        'orange' => (
            ($CurrentPlanet['field_current'] < $planetsMaxFieldsCount) &&
            ($CurrentPlanet['field_current'] >= ($planetsMaxFieldsCount * 0.9))
        ),
        'lime' => ($CurrentPlanet['field_current'] < ($planetsMaxFieldsCount * 0.9)),
    ]);
    $pageTemplateData['Insert_SkinPath'] = $_SkinPath;
    $pageTemplateData['Insert_PlanetImg'] = $CurrentPlanet['image'];
    $pageTemplateData['Insert_PlanetType'] = $_Lang['PlanetType_'.$CurrentPlanet['planet_type']];
    $pageTemplateData['Insert_PlanetName'] = $CurrentPlanet['name'];
    $pageTemplateData['Insert_PlanetPos_Galaxy'] = $CurrentPlanet['galaxy'];
    $pageTemplateData['Insert_PlanetPos_System'] = $CurrentPlanet['system'];
    $pageTemplateData['Insert_PlanetPos_Planet'] = $CurrentPlanet['planet'];
    $pageTemplateData['Insert_Overview_Diameter'] = prettyNumber($CurrentPlanet['diameter']);
    $pageTemplateData['Insert_Overview_Fields_Used'] = prettyNumber($CurrentPlanet['field_current']);
    $pageTemplateData['Insert_Overview_Fields_Max'] = prettyNumber($planetsMaxFieldsCount);
    $pageTemplateData['Insert_Overview_Fields_Percent'] = sprintf(
        '%0.2f',
        (($CurrentPlanet['field_current'] / $planetsMaxFieldsCount) * 100)
    );
    $pageTemplateData['Insert_Overview_Temperature'] = sprintf(
        $_Lang['Overview_Form_Temperature'],
        $CurrentPlanet['temp_min'],
        $CurrentPlanet['temp_max']
    );
    $pageTemplateData['PHPData_ElementsDestructionDetailsJSON'] = json_encode($elementsDestructionDetails);

    $pageHTML = parsetemplate($tplBodyCache['pageBody'], $pageTemplateData);

    display($pageHTML, $_Lang['Builds']);
}

?>
