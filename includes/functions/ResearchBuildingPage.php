<?php

function ResearchBuildingPage(&$CurrentPlanet, $CurrentUser, $InResearch, $ThePlanet)
{
    global $_EnginePath, $_Lang, $_Vars_GameElements, $_Vars_ElementCategories, $_SkinPath, $_GameConfig, $_GET;

    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    include($_EnginePath.'includes/functions/GetElementPrice.php');
    include($_EnginePath.'includes/functions/GetRestPrice.php');
    includeLang('worldElements.detailed');

    $Now = time();

    // Break on "no lab"
    if($CurrentPlanet[$_Vars_GameElements[31]] <= 0)
    {
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
    // TODO: this should be changed on the queue rendering level
    if (
        isset($_GET) &&
        isset($_GET['cmd']) &&
        $_GET['cmd'] === 'cancel' &&
        !isset($_GET['el'])
    ) {
        $_GET['el'] = '0';
    }

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

    $TechRowTPL = gettemplate('buildings_research_row');
    $TechScrTPL = gettemplate('buildings_research_script');

    if(!empty($ResearchPlanet['techQueue']))
    {
        $ExplodeQueue = explode(';', $ResearchPlanet['techQueue']);
        $FirstElement = explode(',', $ExplodeQueue[0]);
        $FirstElementID = $FirstElement[0];
        $InResearch = true;
    }
    else
    {
        $InResearch = false;
    }

    $TechnoList = '';
    foreach($_Vars_ElementCategories['tech'] as $Tech)
    {
        $RowParse = $_Lang;
        $RowParse['skinpath'] = $_SkinPath;
        $RowParse['tech_id'] = $Tech;
        $building_level = $CurrentUser[$_Vars_GameElements[$Tech]];
        $RowParse['tech_level'] = ($building_level == 0) ? '' : "({$_Lang['level']} {$building_level})";
        $RowParse['tech_name'] = $_Lang['tech'][$Tech];
        $RowParse['tech_descr'] = $_Lang['WorldElements_Detailed'][$Tech]['description_short'];

        if(IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Tech))
        {
            $RowParse['tech_price'] = GetElementPrice($CurrentUser, $CurrentPlanet, $Tech);
            $SearchTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $Tech);
            $RowParse['search_time'] = ShowBuildTime($SearchTime);
            $RowParse['tech_restp'] = GetRestPrice ($CurrentUser, $CurrentPlanet, $Tech, true);
            $CanBeDone = IsElementBuyable($CurrentUser, $CurrentPlanet, $Tech, false);

            // Check if user can do the research (TechQueue is empty)
            if(!isOnVacation($CurrentUser))
            {
                if(!$InResearch)
                {
                    // Yes! We can do the science!
                    $LevelToDo = 1 + $CurrentUser[$_Vars_GameElements[$Tech]];
                    // Check if is "buyable" & Lab is not in BuildQueue
                    if($CanBeDone AND !$LabInQueue)
                    {
                        $TechnoLink = "<a href=\"buildings.php?mode=research&cmd=search&tech={$Tech}\">";
                        if($LevelToDo == 1)
                        {
                            $TechnoLink .= "<span class=\"lime\">{$_Lang['Rechercher']}</span>";
                        }
                        else
                        {
                            $TechnoLink .= "<span class=\"lime\">{$_Lang['Rechercher']}<br/>{$_Lang['level']} {$LevelToDo}</span>";
                        }
                        $TechnoLink .= '</a>';
                    }
                    else
                    {
                        if($LevelToDo == 1)
                        {
                            $TechnoLink = "<span class=\"red\">{$_Lang['Rechercher']}</span>";
                        }
                        else
                        {
                            $TechnoLink = "<span class=\"red\">{$_Lang['Rechercher']}<br/>{$_Lang['level']} {$LevelToDo}</span>";
                        }
                    }
                }
                else
                {
                    // Research - underway
                    if($FirstElementID == $Tech)
                    {
                        // Include ChronoApplet
                        include($_EnginePath.'includes/functions/InsertJavaScriptChronoApplet.php');
                        $bloc = $_Lang;
                        $bloc['Script'] = InsertJavaScriptChronoApplet(
                            'res',
                            '',
                            $ResearchPlanet['techQueue_firstEndTime'],
                            true,
                            false,
                            'function() { onQueuesFirstElementFinished(); }'
                        );
                        $bloc['SetStartTime'] = pretty_time($ResearchPlanet['techQueue_firstEndTime'] - $Now, true);
                        $bloc['tech_home'] = $ResearchPlanet['id'];
                        $bloc['tech_id']= $FirstElementID;
                        if($ResearchPlanet['id'] != $CurrentPlanet['id'])
                        {
                            // Research is not on this planet
                            $bloc['tech_name']= " {$_Lang['on']}<br/>{$ResearchPlanet['name']}<br/>[{$ResearchPlanet['galaxy']}:{$ResearchPlanet['system']}:{$ResearchPlanet['planet']}]";
                        }
                        else
                        {
                            // Research is on this planet
                            $bloc['tech_name']= '';
                        }
                        $TechnoLink = parsetemplate($TechScrTPL, $bloc);
                    }
                    else
                    {
                        $TechnoLink= '-';
                    }
                }
            }
            else
            {
                $TechnoLink = "<span class=\"red\">{$_Lang['ListBox_Disallow_VacationMode']}</span>";
            }
            $RowParse['tech_link']= $TechnoLink;
        }
        else
        {
            if($CurrentUser['settings_ExpandedBuildView'] == 0)
            {
                continue;
            }
            $RowParse['tech_link'] = '&nbsp;';
            $RowParse['TechRequirementsPlace'] = GetElementTechReq($CurrentUser, $CurrentPlanet, $Tech);
        }

        $TechnoList .= parsetemplate($TechRowTPL, $RowParse);
    }

    $PageParse = $_Lang;
    $PageParse['technolist'] = $TechnoList;

    if($InResearch)
    {
        $PageParse['Insert_QueueInfo'] = parsetemplate(gettemplate('_singleRow'), array('Classes' => 'pad5 red', 'Colspan' => 3, 'Text' => $_Lang['Queue_ListView_Info']));
    }

    display(parsetemplate(gettemplate('buildings_research'), $PageParse), $_Lang['Research']);
}

?>
