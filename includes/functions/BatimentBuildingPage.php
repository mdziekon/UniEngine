<?php

use UniEngine\Engine\Modules\Development;
use UniEngine\Engine\Modules\Development\Components\LegacyQueue;
use UniEngine\Engine\Includes\Helpers\Planets;

function BatimentBuildingPage(&$CurrentPlanet, $CurrentUser)
{
    global $_EnginePath, $_Lang, $_Vars_GameElements, $_Vars_ElementCategories,
           $_SkinPath, $_GameConfig, $_GET, $_Vars_PremiumBuildingPrices, $_Vars_MaxElementLevel, $_Vars_PremiumBuildings;

    $BuildingPage = '';

    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    include($_EnginePath.'includes/functions/GetElementPrice.php');
    include($_EnginePath.'includes/functions/GetRestPrice.php');
    includeLang('worldElements.detailed');

    CheckPlanetUsedFields ($CurrentPlanet);

    $Now = time();
    $SubTemplate = gettemplate('buildings_builds_row');

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $Now);

    // Handle Commands
    Development\Input\UserCommands\handleStructureCommand(
        $CurrentUser,
        $CurrentPlanet,
        $_GET,
        [
            "timestamp" => $Now
        ]
    );
    // End of - Handle Commands

    $buildingsQueue = Planets\Queues\Structures\parseQueueString($CurrentPlanet['buildQueue']);
    $buildingsQueueLength = count($buildingsQueue);

    if($buildingsQueueLength < ((isPro($CurrentUser)) ? MAX_BUILDING_QUEUE_SIZE_PRO : MAX_BUILDING_QUEUE_SIZE ))
    {
        $CanBuildElement = true;
    }
    else
    {
        $CanBuildElement = false;
    }

    $queueComponent = LegacyQueue\render([
        'queue' => $buildingsQueue,
        'currentTimestamp' => $Now,

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

    if(!empty($CurrentPlanet['buildQueue']))
    {
        $LockResources = array
        (
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0
        );

        $CurrentQueue = explode(';', $CurrentPlanet['buildQueue']);
        foreach($CurrentQueue as $QueueIndex => $ThisBuilding)
        {
            $ThisBuilding = explode(',', $ThisBuilding);
            $ElementID = $ThisBuilding[0]; //ElementID
            $BuildMode = $ThisBuilding[4]; //BuildMode

            if($QueueIndex > 0)
            {
                if($BuildMode == 'destroy')
                {
                    $ForDestroy = true;
                }
                else
                {
                    $ForDestroy = false;
                }
                $GetResourcesToLock = GetBuildingPrice($CurrentUser, $CurrentPlanet, $ElementID, true, $ForDestroy);
                $LockResources['metal'] += $GetResourcesToLock['metal'];
                $LockResources['crystal'] += $GetResourcesToLock['crystal'];
                $LockResources['deuterium'] += $GetResourcesToLock['deuterium'];
            }

            if(!isset($LevelModifiers[$ElementID]))
            {
                $LevelModifiers[$ElementID] = 0;
            }
            if($BuildMode == 'destroy')
            {
                $LevelModifiers[$ElementID] += 1;
                $CurrentPlanet[$_Vars_GameElements[$ElementID]] -= 1;
            }
            else
            {
                $LevelModifiers[$ElementID] -= 1;
                $CurrentPlanet[$_Vars_GameElements[$ElementID]] += 1;
            }
        }
    }

    foreach($_Vars_ElementCategories['build'] as $Element)
    {
        if(in_array($Element, $_Vars_ElementCategories['buildOn'][$CurrentPlanet['planet_type']]))
        {
            $ElementName = $_Lang['tech'][$Element];
            $CurrentMaxFields = CalculateMaxPlanetFields($CurrentPlanet);
            if($CurrentPlanet['field_current'] < ($CurrentMaxFields - $buildingsQueueLength))
            {
                $RoomIsOk = true;
            }
            else
            {
                $RoomIsOk = false;
            }

            $parse = array();
            $parse['skinpath'] = $_SkinPath;
            $parse['i'] = $Element;
            $BuildingLevel = $CurrentPlanet[$_Vars_GameElements[$Element]];
            if(isset($LevelModifiers[$Element]))
            {
                $PlanetLevel = $BuildingLevel + $LevelModifiers[$Element];
            }
            else
            {
                $PlanetLevel = $BuildingLevel;
            }
            $parse['nivel'] = ($BuildingLevel == 0) ? '' : " ({$_Lang['level']} {$PlanetLevel})";

            if (in_array($Element, array(1, 2, 3, 4, 12))) {
                // Show energy on BuildingPage
                $thisLevelProduction = getElementProduction(
                    $Element,
                    $CurrentPlanet,
                    $CurrentUser,
                    [
                        'useCurrentBoosters' => true,
                        'currentTimestamp' => $Now,
                        'customLevel' => $BuildingLevel,
                        'customProductionFactor' => 10
                    ]
                );
                $nextLevelProduction = getElementProduction(
                    $Element,
                    $CurrentPlanet,
                    $CurrentUser,
                    [
                        'useCurrentBoosters' => true,
                        'currentTimestamp' => $Now,
                        'customLevel' => ($BuildingLevel + 1),
                        'customProductionFactor' => 10
                    ]
                );

                $energyDifference = ($nextLevelProduction['energy'] - $thisLevelProduction['energy']);
                $deuteriumDifference = ($nextLevelProduction['deuterium'] - $thisLevelProduction['deuterium']);

                $energyDifferenceFormatted = prettyColorNumber(floor($energyDifference));

                if ($Element >= 1 && $Element <= 3) {
                    $parse['build_need_diff'] = "(<span class=\"red\">{$_Lang['Energy']}: {$energyDifferenceFormatted}</span>)";
                } else if ($Element == 4) {
                    $parse['build_need_diff'] = "(<span class=\"lime\">{$_Lang['Energy']}: +{$energyDifferenceFormatted}</span>)";
                } else if ($Element == 12) {
                    $deuteriumDifferenceFormatted = prettyColorNumber(floor($deuteriumDifference));

                    $parse['build_need_diff'] = "(<span class=\"lime\">{$_Lang['Energy']}: +{$energyDifferenceFormatted}</span> | <span class=\"red\">{$_Lang['Deuterium']}: {$deuteriumDifferenceFormatted}</span>)";
                }
            }

            $parse['n'] = $ElementName;
            $parse['Description'] = $_Lang['WorldElements_Detailed'][$Element]['description_short'];
            $parse['click'] = '';
            $NextBuildLevel = $CurrentPlanet[$_Vars_GameElements[$Element]] + 1;
            $skip = false;

            if(IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element))
            {
                if(!empty($LockResources))
                {
                    foreach($LockResources as $Key => $Value)
                    {
                        $CurrentPlanet[$Key] -= $Value;
                    }
                }
                $HaveRessources = IsElementBuyable($CurrentUser, $CurrentPlanet, $Element, false);
                $ElementBuildTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $Element);
                $parse['time'] = ShowBuildTime($ElementBuildTime);
                $parse['price'] = GetElementPrice($CurrentUser, $CurrentPlanet, $Element);
                $parse['rest_price'] = GetRestPrice($CurrentUser, $CurrentPlanet, $Element);
                if(!empty($LockResources))
                {
                    foreach($LockResources as $Key => $Value)
                    {
                        $CurrentPlanet[$Key] += $Value;
                    }
                }

                if(isset($LevelModifiers[$Element]) && $LevelModifiers[$Element] != 0)
                {
                    $parse['AddLevelPrice'] = "<b>[{$_Lang['level']}: {$NextBuildLevel}]</b><br/>";
                }

                if($Element == 31)
                {
                    // Block Lab Upgrade is Research running (and Config dont allow that)
                    if($CurrentUser['techQueue_Planet'] > 0 AND $CurrentUser['techQueue_EndTime'] > 0 AND $_GameConfig['BuildLabWhileRun'] != 1)
                    {
                        $parse['click'] = "<span class=red>{$_Lang['in_working']}</span>";
                    }
                }
                if(!empty($_Vars_MaxElementLevel[$Element]))
                {
                    if($NextBuildLevel > $_Vars_MaxElementLevel[$Element])
                    {
                        $parse['click'] = "<span class=red>{$_Lang['onlyOneLevel']}</span>";
                        $skip = true;
                    }
                }

                if(isset($_Vars_PremiumBuildings[$Element]) && $_Vars_PremiumBuildings[$Element] == 1)
                {
                    $parse['rest_price'] = "<br/><font color=\"#7f7f7f\">{$_Lang['ResourcesLeft']}: {$_Lang['DarkEnergy']}";
                    $parse['price'] = "{$_Lang['Requires']}: {$_Lang['DarkEnergy']} <span class=\"noresources\">";
                    if($CurrentUser['darkEnergy'] < $_Vars_PremiumBuildingPrices[$Element])
                    {
                        if($skip == false)
                        {
                            $parse['click'] = "<span class=\"red\">{$_Lang['BuildFirstLevel']}</span>";
                        }
                        $parse['price'] .= " <b class=\"red\"> ".prettyNumber($_Vars_PremiumBuildingPrices[$Element])."</b></span> ";
                        $parse['rest_price'] .= "<b style=\"color: rgb(127, 95, 96);\"> ".prettyNumber($CurrentUser['darkEnergy'] - $_Vars_PremiumBuildingPrices[$Element])."</b>";
                    }
                    else
                    {
                        $parse['price'] .= " <b class=\"lime\"> ".prettyNumber($_Vars_PremiumBuildingPrices[$Element])."</b></span> ";
                        $parse['rest_price'] .= "<b style=\"color: rgb(95, 127, 108);\"> ".prettyNumber($CurrentUser['darkEnergy'] - $_Vars_PremiumBuildingPrices[$Element])."</b>";
                    }
                    $parse['rest_price'] .= '</font>';
                }

                if(isOnVacation($CurrentUser))
                {
                    $parse['click'] = "<span class=\"red\">{$_Lang['ListBox_Disallow_VacationMode']}</span>";
                }

                if($parse['click'] != '')
                {
                    // Don't do anything here
                }
                else if($RoomIsOk AND $CanBuildElement)
                {
                    if($buildingsQueueLength == 0)
                    {
                        if($NextBuildLevel == 1)
                        {
                            if($HaveRessources == true)
                            {
                                $parse['click'] = "<a href=\"?cmd=insert&building={$Element}\" class=\"lime\">{$_Lang['BuildFirstLevel']}</a>";
                            }
                            else
                            {
                                $parse['click'] = "<span class=\"red\">{$_Lang['BuildFirstLevel']}</span>";
                            }
                        }
                        else
                        {
                            if($HaveRessources == true)
                            {
                                $parse['click'] = "<a href=\"?cmd=insert&building={$Element}\" class=\"lime\">{$_Lang['BuildNextLevel']} {$NextBuildLevel}</a>";
                            }
                            else
                            {
                                $parse['click'] = "<span class=\"red\">{$_Lang['BuildNextLevel']} {$NextBuildLevel}</span>";
                            }
                        }
                    }
                    else
                    {
                        if($HaveRessources == true)
                        {
                            $ThisColor = 'lime';
                        }
                        else
                        {
                            $ThisColor = 'orange';
                        }

                        $parse['click'] = "<a href=\"?cmd=insert&building={$Element}\" class=\"{$ThisColor}\">{$_Lang['InBuildQueue']}<br/>({$_Lang['level']} {$NextBuildLevel})</a>";
                    }
                }
                else if($RoomIsOk AND !$CanBuildElement)
                {
                    $parse['click'] = "<span class=\"red\">{$_Lang['QueueIsFull']}</span>";
                }
                else
                {
                    if($CurrentPlanet['planet_type'] == 3)
                    {
                        $parse['click'] = "<span class=\"red\">{$_Lang['NoMoreSpace_Moon']}</span>";
                    }
                    else
                    {
                        $parse['click'] = "<span class=\"red\">{$_Lang['NoMoreSpace']}</span>";
                    }
                }
            }
            else
            {
                if($CurrentUser['settings_ExpandedBuildView'] == 0)
                {
                    continue;
                }
                $parse['click'] = '&nbsp;';
                $parse['TechRequirementsPlace'] = GetElementTechReq($CurrentUser, $CurrentPlanet, $Element);
            }

            $BuildingPage .= parsetemplate($SubTemplate, $parse);
        }
    }

    if(!empty($LevelModifiers))
    {
        foreach($LevelModifiers as $ElementID => $Modifier)
        {
            $CurrentPlanet[$_Vars_GameElements[$ElementID]] += $Modifier;
        }
    }

    $parse = $_Lang;

    $parse['planet_field_current'] = $CurrentPlanet['field_current'];
    $parse['planet_field_max'] = CalculateMaxPlanetFields($CurrentPlanet);
    $parse['field_libre'] = $parse['planet_field_max'] - $CurrentPlanet['field_current'];

    $parse['BuildList'] = $queueComponent['componentHTML'];
    $parse['BuildingsList'] = $BuildingPage;

    display(parsetemplate(gettemplate('buildings_builds'), $parse), $_Lang['Builds']);
}

?>
