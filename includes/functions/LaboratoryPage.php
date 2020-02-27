<?php

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
    global    $_EnginePath, $_Lang,
            $_Vars_GameElements, $_Vars_ElementCategories, $_Vars_MaxElementLevel,
            $_SkinPath, $_GET;

    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    includeLang('worldElements.detailed');

    $Now = time();
    $Parse = &$_Lang;
    $Parse['Create_Queue'] = '';
    $ShowElementID = 0;

    // Constants
    $ElementsPerRow = 7;

    // Get Templates
    $TPL['list_element']                = gettemplate('buildings_compact_list_element_lab');
    $TPL['list_levelmodif']                = gettemplate('buildings_compact_list_levelmodif');
    $TPL['list_hidden']                    = gettemplate('buildings_compact_list_hidden');
    $TPL['list_row']                    = gettemplate('buildings_compact_list_row');
    $TPL['list_breakrow']                = gettemplate('buildings_compact_list_breakrow');
    $TPL['list_disabled']                = gettemplate('buildings_compact_list_disabled');
    $TPL['list_partdisabled']            = parsetemplate($TPL['list_disabled'], array('AddOpacity' => 'dPart'));
    $TPL['list_disabled']                = parsetemplate($TPL['list_disabled'], array('AddOpacity' => ''));

    if($CurrentPlanet[$_Vars_GameElements[31]] > 0)
    {
        $HasLab = true;
    }
    else
    {
        $HasLab = false;
    }

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

    if(is_array($ThePlanet))
    {
        $ResearchPlanet = &$ThePlanet;
    }
    else
    {
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

    if($InResearch === true && $ResearchPlanet['id'] != $CurrentPlanet['id'])
    {
        $ResearchInThisLab = false;
    }
    else
    {
        $ResearchInThisLab = true;
    }
    // End of - Execute Commands
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

    $Parse['Create_Queue'] = $queueComponent['componentHTML'];

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

    if (!$hasPlanetsWithUnfinishedLabUpgrades) {
        $CanAddToQueue = !$isQueueFull;
    } else {
        $CanAddToQueue = false;
    }
    // End of - Parse Queue

    foreach($_Vars_ElementCategories['tech'] as $ElementID)
    {
        $ElementParser = [
            'SkinPath' => $_SkinPath,
        ];

        $CurrentLevel = $CurrentUser[$_Vars_GameElements[$ElementID]];
        $NextLevel = $CurrentUser[$_Vars_GameElements[$ElementID]] + 1;
        $isElementInQueue = isset(
            $queueStateDetails['queuedElementLevelModifiers'][$ElementID]
        );
        $elementQueueLevelModifier = (
            $isElementInQueue ?
            $queueStateDetails['queuedElementLevelModifiers'][$ElementID] :
            0
        );
        $HasResources = true;

        $elementMaxLevel = Elements\getElementMaxUpgradeLevel($ElementID);
        $hasReachedMaxLevel = ($CurrentUser[$_Vars_GameElements[$ElementID]] >= $elementMaxLevel);

        $hasTechnologyRequirementMet = IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $ElementID);

        $HideButton_Build = false;
        $HideButton_QuickBuild = false;

        $ElementParser['ElementName'] = $_Lang['tech'][$ElementID];
        $ElementParser['ElementID'] = $ElementID;
        $ElementParser['ElementRealLevel'] = prettyNumber(
            $CurrentUser[$_Vars_GameElements[$ElementID]] +
            ($elementQueueLevelModifier * -1)
        );
        $ElementParser['BuildButtonColor'] = 'buildDo_Green';

        if ($isElementInQueue)
        {
            $levelmodif = [];
            $levelmodif['modColor'] = 'lime';
            $levelmodif['modText'] = '+'.prettyNumber($elementQueueLevelModifier);
            $ElementParser['ElementLevelModif'] = parsetemplate($TPL['list_levelmodif'], $levelmodif);
        }

        if ($hasReachedMaxLevel) {
            $HideButton_Build = true;
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

        if($hasReachedMaxLevel)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_MaxLevelReached'];
        }
        else if(!$HasResources)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoResources'];
        }
        if(!$hasTechnologyRequirementMet)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoTech'];
            $ElementParser['BuildButtonColor'] = 'buildDo_Gray';
            $HideButton_QuickBuild = true;
        }
        if($CanAddToQueue === false)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_QueueIsFull'];
            $ElementParser['BuildButtonColor'] = 'buildDo_Gray';
            $HideButton_QuickBuild = true;
        }
        if($HasLab === false)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoLab'];
            $ElementParser['BuildButtonColor'] = 'buildDo_Gray';
            $HideButton_QuickBuild = true;
        }
        if($ResearchInThisLab === false)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_NotThisLab'];
            $ElementParser['BuildButtonColor'] = 'buildDo_Gray';
            $HideButton_QuickBuild = true;
        }
        if($hasPlanetsWithUnfinishedLabUpgrades)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_LabInQueue'];
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

        $StructuresList[] = parsetemplate($TPL['list_element'], $ElementParser);

        $hasUpgradeResources = $HasResources;
        $hasElementsInQueue = ($elementsInQueue > 0);
        $isBlockedByLabUpgradeProgress = $hasPlanetsWithUnfinishedLabUpgrades;
        $isOnVacation = isOnVacation($CurrentUser);

        $cardInfoComponent = Development\Components\GridViewElementCard\render([
            'elementID' => $ElementID,
            'user' => $CurrentUser,
            'planet' => $CurrentPlanet,
            'isQueueActive' => $hasElementsInQueue,
            'elementDetails' => [
                'currentState' => (
                    $CurrentUser[$_Vars_GameElements[$ElementID]] +
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
                    $HasLab &&
                    $ResearchInThisLab &&
                    $hasTechnologyRequirementMet &&
                    !$isBlockedByLabUpgradeProgress &&
                    !$isQueueFull &&
                    !$isOnVacation
                ),
                'isUpgradeQueueable' => (
                    !$hasReachedMaxLevel &&
                    $HasLab &&
                    $ResearchInThisLab &&
                    $hasTechnologyRequirementMet &&
                    !$isBlockedByLabUpgradeProgress &&
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
    // End of - Parse all available technologies

    $Parse['Insert_SkinPath'] = $_SkinPath;
    $Parse['Insert_PlanetImg'] = $CurrentPlanet['image'];
    $Parse['Insert_PlanetType'] = $_Lang['PlanetType_'.$CurrentPlanet['planet_type']];
    $Parse['Insert_PlanetName'] = $CurrentPlanet['name'];
    $Parse['Insert_PlanetPos_Galaxy'] = $CurrentPlanet['galaxy'];
    $Parse['Insert_PlanetPos_System'] = $CurrentPlanet['system'];
    $Parse['Insert_PlanetPos_Planet'] = $CurrentPlanet['planet'];
    $Parse['Insert_Overview_LabLevel'] = $CurrentPlanet[$_Vars_GameElements[31]];
    $Parse['Insert_Overview_LabsConnected'] = prettyNumber($researchNetworkStatus['connectedLabsCount']);
    $Parse['Insert_Overview_TotalLabsCount'] = prettyNumber($researchNetworkStatus['allLabsCount']);
    $Parse['Insert_Overview_LabPower'] = prettyNumber($researchNetworkStatus['connectedLabsLevel']);
    $Parse['Insert_Overview_LabPowerTotal'] = prettyNumber($researchNetworkStatus['allLabsLevel']);

    $Page = parsetemplate(gettemplate('buildings_compact_body_lab'), $Parse);

    display($Page, $_Lang['Research']);
}

?>
