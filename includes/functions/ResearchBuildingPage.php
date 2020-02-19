<?php

use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Modules\Development\Components\LegacyQueue;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Includes\Helpers\World\Resources;
use UniEngine\Engine\Includes\Helpers\World\Elements;

function ResearchBuildingPage(&$CurrentPlanet, $CurrentUser, $ThePlanet) {
    global $_EnginePath, $_Lang, $_Vars_ElementCategories, $_SkinPath, $_GET;

    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    include($_EnginePath.'includes/functions/GetElementPrice.php');
    include($_EnginePath.'includes/functions/GetRestPrice.php');
    includeLang('worldElements.detailed');

    $Now = time();

    // Break on "no lab"
    if (!Planets\Elements\hasResearchLab($CurrentPlanet)) {
        message($_Lang['no_laboratory'], $_Lang['Research']);
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

    if(!$hasPlanetsWithUnfinishedLabUpgrades) {
        $_Lang['Input_HideNoResearch'] = 'display: none;';
    } else {
        $LabInQueueAt = array_map(
            function ($planet) {
                return "{$planet['name']} [{$planet['galaxy']}:{$planet['system']}:{$planet['planet']}]";
            },
            $planetsWithUnfinishedLabUpgrades
        );

        $_Lang['labo_on_update'] = sprintf($_Lang['labo_on_update'], implode(', ', $LabInQueueAt));
    }

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
    UniEngine\Engine\Modules\Development\Input\UserCommands\handleResearchCommand(
        $CurrentUser,
        $ResearchPlanet,
        $_GET,
        [
            "timestamp" => $Now,
            "currentPlanet" => $CurrentPlanet,
            "hasPlanetsWithUnfinishedLabUpgrades" => $hasPlanetsWithUnfinishedLabUpgrades
        ]
    );
    // End of - Handle Commands

    $researchQueue = Planets\Queues\Research\parseQueueString($ResearchPlanet['techQueue']);
    $researchQueueLength = count($researchQueue);
    $isQueueFull = ($researchQueueLength >= Users\getMaxResearchQueueLength($CurrentUser));
    $isResearchInProgress = ($researchQueueLength > 0);
    $isCurrentPlanetResearchPlanet = ($ResearchPlanet['id'] == $CurrentPlanet['id']);

    $queueComponent = LegacyQueue\render([
        'queue' => $researchQueue,
        'currentTimestamp' => $Now,

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

    $queueStateDetails = Development\Utils\getQueueStateDetails([
        'queue' => [
            'type' => Development\Utils\QueueType::Research,
            'content' => $researchQueue,
        ],
        'user' => $CurrentUser,
        'planet' => $CurrentPlanet,
    ]);

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

    $TechRowTPL = gettemplate('buildings_research_row');

    $TechnoList = '';
    foreach($_Vars_ElementCategories['tech'] as $elementID) {
        $elementKey = _getElementUserKey($elementID);

        $queuedLevel = $CurrentUser[$elementKey];
        $isElementInQueue = isset(
            $queueStateDetails['queuedElementLevelModifiers'][$elementID]
        );
        $elementQueueLevelModifier = (
            $isElementInQueue ?
            $queueStateDetails['queuedElementLevelModifiers'][$elementID] :
            0
        );
        $currentElementLevel = ($queuedLevel - $elementQueueLevelModifier);

        $RowParse = $_Lang;
        $RowParse['skinpath'] = $_SkinPath;
        $RowParse['tech_id'] = $elementID;
        $RowParse['tech_level'] = ($currentElementLevel == 0) ? '' : "({$_Lang['level']} {$currentElementLevel})";
        $RowParse['tech_name'] = $_Lang['tech'][$elementID];
        $RowParse['tech_descr'] = $_Lang['WorldElements_Detailed'][$elementID]['description_short'];

        if (!IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $elementID)) {
            if ($CurrentUser['settings_ExpandedBuildView'] == 0) {
                continue;
            }

            $RowParse['tech_link'] = '&nbsp;';
            $RowParse['TechRequirementsPlace'] = GetElementTechReq($CurrentUser, $CurrentPlanet, $elementID);

            $TechnoList .= parsetemplate($TechRowTPL, $RowParse);

            continue;
        }

        $CanBeDone = IsElementBuyable($CurrentUser, $CurrentPlanet, $elementID, false);
        $SearchTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $elementID);
        $RowParse['tech_price'] = GetElementPrice($CurrentUser, $CurrentPlanet, $elementID);
        $RowParse['search_time'] = ShowBuildTime($SearchTime);
        $RowParse['tech_restp'] = GetRestPrice($CurrentUser, $CurrentPlanet, $elementID, true);

        $upgradeNextLevel = 1 + $queuedLevel;

        if ($isElementInQueue) {
            $RowParse['AddLevelPrice'] = "<b>[{$_Lang['level']}: {$upgradeNextLevel}]</b><br/>";
        }

        if (isOnVacation($CurrentUser)) {
            $TechnoLink = "<span class=\"red\">{$_Lang['ListBox_Disallow_VacationMode']}</span>";
            $RowParse['tech_link'] = $TechnoLink;

            $TechnoList .= parsetemplate($TechRowTPL, $RowParse);

            continue;
        }

        if (
            (
                $isResearchInProgress &&
                !$isCurrentPlanetResearchPlanet
            ) ||
            $hasPlanetsWithUnfinishedLabUpgrades
        ) {
            $RowParse['tech_link'] = '&nbsp;';

            $TechnoList .= parsetemplate($TechRowTPL, $RowParse);

            continue;
        }

        if ($isQueueFull) {
            $TechnoLink = "<span class=\"red\">{$_Lang['QueueIsFull']}</span>";
            $RowParse['tech_link'] = $TechnoLink;

            $TechnoList .= parsetemplate($TechRowTPL, $RowParse);

            continue;
        }

        $linkLabel = (
            ($upgradeNextLevel == 1) ?
            $_Lang['ResearchBtnLabel'] :
            "{$_Lang['ResearchBtnLabel']}<br/>{$_Lang['level']} {$upgradeNextLevel}"
        );

        if (!$isResearchInProgress && !$CanBeDone) {
            // Nothing queued and not enough resources to start research

            $TechnoLink = "<span class=\"red\">{$linkLabel}</span>";
            $RowParse['tech_link'] = $TechnoLink;

            $TechnoList .= parsetemplate($TechRowTPL, $RowParse);

            continue;
        }

        $linkLabelColor = ($CanBeDone ? 'lime' : 'orange');

        $linkURL = "buildings.php?mode=research&cmd=search&tech={$elementID}";
        $TechnoLink = "<a href=\"{$linkURL}\"><span class=\"{$linkLabelColor}\">{$linkLabel}</span></a>";

        $RowParse['tech_link'] = $TechnoLink;

        $TechnoList .= parsetemplate($TechRowTPL, $RowParse);

        continue;
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
        $CurrentUser[$elementKey] -= $elementLevelModifier;
    }

    $PageParse = $_Lang;
    $PageParse['technolist'] = $TechnoList;
    $PageParse['Data_QueueComponentHTML'] = $queueComponent['componentHTML'];

    if ($isResearchInProgress && !$isCurrentPlanetResearchPlanet) {
        $PageParse['Insert_QueueInfo'] = parsetemplate(
            gettemplate('_singleRow'),
            [
                'Classes' => 'pad5 red',
                'Colspan' => 3,
                'Text' => (
                    $_Lang['Queue_ResearchOn'] .
                    ' ' .
                    "{$ResearchPlanet['name']} [{$ResearchPlanet['galaxy']}:{$ResearchPlanet['system']}:{$ResearchPlanet['planet']}]"
                )
            ]
        );
    }

    display(parsetemplate(gettemplate('buildings_research'), $PageParse), $_Lang['Research']);
}

?>
