<?php

use UniEngine\Engine\Modules\Development\Components\LegacyQueue;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

function ResearchBuildingPage(&$CurrentPlanet, $CurrentUser, $ThePlanet) {
    global $_EnginePath, $_Lang, $_Vars_ElementCategories, $_SkinPath, $_GameConfig, $_GET;

    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    include($_EnginePath.'includes/functions/GetElementPrice.php');
    include($_EnginePath.'includes/functions/GetRestPrice.php');
    includeLang('worldElements.detailed');

    $Now = time();

    // Break on "no lab"
    if (!Planets\Elements\hasResearchLab($CurrentPlanet)) {
        message($_Lang['no_laboratory'], $_Lang['Research']);
    }

    // Check if Lab is in BuildQueue
    $LabInQueue = false;
    if($_GameConfig['BuildLabWhileRun'] != 1)
    {
        $SQLResult_CheckOtherPlanets = doquery(
            "SELECT * FROM {{table}} WHERE `id_owner` = {$CurrentUser['id']} AND (`buildQueue` LIKE '31,%' OR `buildQueue` LIKE '%;31,%');",
            'planets'
        );

        if($SQLResult_CheckOtherPlanets->num_rows > 0)
        {
            include($_EnginePath.'/includes/functions/CheckLabInQueue.php');

            $Results['planets'] = array();
            while($PlanetsData = $SQLResult_CheckOtherPlanets->fetch_assoc())
            {
                // Update Planet - Building Queue
                $CheckLab = CheckLabInQueue($PlanetsData);
                if($CheckLab !== false)
                {
                    if($CheckLab <= $Now)
                    {
                        if(HandlePlanetQueue($PlanetsData, $CurrentUser, $Now, true) === true)
                        {
                            $Results['planets'][] = $PlanetsData;
                        }
                    }
                    else
                    {
                        $LabInQueueAt[] = "{$PlanetsData['name']} [{$PlanetsData['galaxy']}:{$PlanetsData['system']}:{$PlanetsData['planet']}]";
                        $LabInQueue = true;
                    }
                }
            }
            HandlePlanetUpdate_MultiUpdate($Results, $CurrentUser);
        }
    }
    if(!$LabInQueue)
    {
        $_Lang['Input_HideNoResearch'] = 'display: none;';
    }
    else
    {
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
            "hasPlanetsWithUnfinishedLabUpgrades" => $LabInQueue
        ]
    );
    // End of - Handle Commands

    $researchQueue = Planets\Queues\Research\parseQueueString($ResearchPlanet['techQueue']);
    $researchQueueLength = count($researchQueue);
    $isQueueFull = ($researchQueueLength >= Users\getMaxResearchQueueLength($CurrentUser));
    $isResearchInProgress = ($researchQueueLength > 0);
    $isCurrentPlanetResearchPlanet = ($ResearchPlanet['id'] == $CurrentPlanet['id']);

    $queueComponent = LegacyQueue\render([
        'queue' => Planets\Queues\Research\parseQueueString(
            $ResearchPlanet['techQueue']
        ),
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

    $queuedElementsResourceLock = [
        'metal' => 0,
        'crystal' => 0,
        'deuterium' => 0
    ];
    $queuedElementsLevelModifiers = [];

    $userCopy = $CurrentUser;

    foreach ($researchQueue as $queueElementIdx => $queueElement) {
        $queueElementID = $queueElement['elementID'];
        $queueElementKey = _getElementUserKey($queueElementID);
        $queueElementMode = $queueElement['mode'];

        $isUpgrading = ($queueElementMode == 'build');

        if ($queueElementIdx > 0) {
            $queueElementCost = GetBuildingPrice(
                $userCopy,
                $ResearchPlanet,
                $queueElementID,
                true,
                !$isUpgrading
            );

            $queuedElementsResourceLock['metal'] += $queueElementCost['metal'];
            $queuedElementsResourceLock['crystal'] += $queueElementCost['crystal'];
            $queuedElementsResourceLock['deuterium'] += $queueElementCost['deuterium'];
        }

        if (!isset($queuedElementsLevelModifiers[$queueElementID])) {
            $queuedElementsLevelModifiers[$queueElementID] = 0;
        }

        $levelModifier = ($isUpgrading ? 1 : -1);

        $userCopy[$queueElementKey] += $levelModifier;
        $queuedElementsLevelModifiers[$queueElementID] += $levelModifier;
    }

    $TechRowTPL = gettemplate('buildings_research_row');

    $TechnoList = '';
    foreach($_Vars_ElementCategories['tech'] as $elementID) {
        $elementKey = _getElementUserKey($elementID);

        $currentElementLevel = $CurrentUser[$elementKey];
        $queuedLevel = (
            $currentElementLevel +
            (
                isset($queuedElementsLevelModifiers[$elementID]) ?
                $queuedElementsLevelModifiers[$elementID] :
                0
            )
        );

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

        if (isset($queuedElementsLevelModifiers[$elementID])) {
            $CurrentUser[$elementKey] += $queuedElementsLevelModifiers[$elementID];
        }
        foreach ($queuedElementsResourceLock as $resourceKey => $resourceAmount) {
            $CurrentPlanet[$resourceKey] -= $resourceAmount;
        }

        $CanBeDone = IsElementBuyable($CurrentUser, $CurrentPlanet, $elementID, false);
        $SearchTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $elementID);
        $RowParse['tech_price'] = GetElementPrice($CurrentUser, $CurrentPlanet, $elementID);
        $RowParse['search_time'] = ShowBuildTime($SearchTime);
        $RowParse['tech_restp'] = GetRestPrice($CurrentUser, $CurrentPlanet, $elementID, true);

        $upgradeNextLevel = 1 + $queuedLevel;

        if (isset($queuedElementsLevelModifiers[$elementID])) {
            $RowParse['AddLevelPrice'] = "<b>[{$_Lang['level']}: {$upgradeNextLevel}]</b><br/>";
        }

        if (isset($queuedElementsLevelModifiers[$elementID])) {
            $CurrentUser[$elementKey] -= $queuedElementsLevelModifiers[$elementID];
        }
        foreach ($queuedElementsResourceLock as $resourceKey => $resourceAmount) {
            $CurrentPlanet[$resourceKey] += $resourceAmount;
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
            $LabInQueue
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
