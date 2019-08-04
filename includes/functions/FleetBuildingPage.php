<?php

function FleetBuildingPage(&$CurrentPlanet, $CurrentUser)
{
    global $_EnginePath, $_Lang, $_Vars_GameElements, $_SkinPath, $_POST, $_Vars_ElementCategories, $UserDev_Log;

    include($_EnginePath.'includes/functions/GetMaxConstructibleElements.php');
    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    include($_EnginePath.'includes/functions/GetElementPrice.php');
    includeLang('worldElements.detailed');

    $Now = time();

    // Show Error when no Hangar
    if($CurrentPlanet[$_Vars_GameElements[21]] <= 0)
    {
        message($_Lang['need_hangar'], $_Lang['tech'][21]);
    }

    // Get QueueSize
    $QueueSize = ((isPro($CurrentUser)) ? MAX_FLEET_OR_DEFS_PER_ROW_PRO : MAX_FLEET_OR_DEFS_PER_ROW);

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $Now);

    if(!isOnVacation($CurrentUser))
    {
        if(isset($_POST['fmenge']))
        {
            // User is trying to build something
            $AddedSomething = false;
            $CurrentPlanet = doquery("SELECT * FROM {{table}} WHERE `id` = {$CurrentPlanet['id']} LIMIT 1;", 'planets', true);

            if($CurrentPlanet['shipyardQueue'] == '0')
            {
                $CurrentPlanet['shipyardQueue'] = '';
            }

            if($CurrentPlanet)
            {
                include($_EnginePath.'includes/functions/GetElementRessources.php');
                foreach($_POST['fmenge'] as $Element => $Count)
                {
                    $Element = intval($Element);
                    $Count = floor(floatval(str_replace('.', '', $Count)));
                    if(in_array($Element, $_Vars_ElementCategories['fleet']))
                    {
                        if($Count > 0)
                        {
                            if($Count > $QueueSize)
                            {
                                $Count = $QueueSize;
                            }
                            // Check if this Ship is Accessible (Tech)
                            if(IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element))
                            {
                                // Check Max Constructible Element Count
                                $MaxElements = GetMaxConstructibleElements($Element, $CurrentPlanet);
                                if($Count > $MaxElements)
                                {
                                    $Count = $MaxElements;
                                }

                                $Ressource = GetElementRessources($Element, $Count);
                                if($Count > 0)
                                {
                                    $AddedSomething = true;

                                    if(!isset($UpdateAchievements[$Element]))
                                    {
                                        $UpdateAchievements[$Element] = 0;
                                    }
                                    $UpdateAchievements[$Element] += $Count;
                                    $addToBHangar = "{$Element},{$Count};";
                                    $CurrentPlanet['metal'] -= $Ressource['metal'];
                                    $CurrentPlanet['crystal'] -= $Ressource['crystal'];
                                    $CurrentPlanet['deuterium'] -= $Ressource['deuterium'];
                                    $CurrentPlanet['shipyardQueue'] .= $addToBHangar;

                                    $DevLog_Array[] = "{$Element},{$Count}";
                                }
                            }
                        }
                    }
                }

                if($AddedSomething)
                {
                    // Update Achievements
                    foreach($UpdateAchievements as $Key => $Value)
                    {
                        $QryAchievementsKey[] = "`build_{$Key}`";
                        $QryAchievementsArr[] = "`build_{$Key}` = `build_{$Key}` + VALUES(`build_{$Key}`)";
                    }
                    $QryAchievements = "INSERT INTO {{table}} (`A_UserID`, ".implode(', ', $QryAchievementsKey).") VALUES ({$CurrentUser['id']}, ".implode(', ', $UpdateAchievements).")";
                    $QryAchievements .= " ON DUPLICATE KEY UPDATE ";
                    $QryAchievements .= implode(', ', $QryAchievementsArr);
                    $QryAchievements .= ';';
                    doquery($QryAchievements, 'achievements_stats');

                    // Update DevLog
                    $UserDev_Log[] = array('PlanetID' => $CurrentPlanet['id'], 'Date' => $Now, 'Place' => 6, 'Code' => '0', 'ElementID' => '0', 'AdditionalData' => implode(';', $DevLog_Array));
                }
            }
        }
    }

    $TabIndex = 0;

    $ElementRowTPL = gettemplate('buildings_fleet_row');
    $ElementRowInputTPL = gettemplate('buildings_fleet_row_input');

    $PageTable = '';
    foreach($_Vars_ElementCategories['fleet'] as $Element)
    {
        $Row = array();

        $Row['Element'] = $Element;
        $Row['skinpath'] = $_SkinPath;
        $Row['ElementName'] = $_Lang['tech'][$Element];
        $ElementCount = $CurrentPlanet[$_Vars_GameElements[$Element]];
        $Row['ElementNbre'] = ($ElementCount == 0) ? '' : " ({$_Lang['dispo']}: " . prettyNumber($ElementCount) . ")";
        $Row['Description'] = $_Lang['WorldElements_Detailed'][$Element]['description_short'];
        if($Element == 212)
        {
            $solarSatelliteEnergyProduction = getElementProduction(
                $Element,
                $CurrentPlanet,
                $CurrentUser,
                [
                    'useCurrentBoosters' => true,
                    'currentTimestamp' => $Now,
                    'customLevel' => 1,
                    'customProductionFactor' => 10
                ]
            );

            $Row['SateliteInfo'] = (
                '<br/>' .
                sprintf(
                    $_Lang['SatelitesEnergy'],
                    prettyNumber($solarSatelliteEnergyProduction['energy'])
                )
            );
        }

        if(IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element))
        {
            $CanBuildOne = IsElementBuyable($CurrentUser, $CurrentPlanet, $Element, false);
            $BuildOneElementTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $Element);

            $Row['GetElementPrice'] = GetElementPrice($CurrentUser, $CurrentPlanet, $Element, false);
            $Row['ShowBuildTime'] = ShowBuildTime($BuildOneElementTime);

            if(isOnVacation($CurrentUser))
            {
                $CanBuildOne = false;
            }

            if($CanBuildOne)
            {
                $Row['InputData'] = array();
                $TabIndex += 1;
                $MaxElements = GetMaxConstructibleElements($Element, $CurrentPlanet);

                $Row['InputData']['youCanBuild'] = $_Lang['youCanBuild'];
                $Row['InputData']['MaxElements'] = prettyNumber($MaxElements);
                $Row['InputData']['Element'] = $Element;
                $Row['InputData']['ElementName'] = $Row['ElementName'];
                $Row['InputData']['TabIndex'] = $TabIndex;
                $Row['InputData']['Max'] = $_Lang['max'];

                $Row['Input'] = parsetemplate($ElementRowInputTPL, $Row['InputData']);
            }
            else
            {
                $Row['Input'] = '&nbsp';
                if(isOnVacation($CurrentUser))
                {
                    $Row['Input'] = '<span class="red">'.$_Lang['ListBox_Disallow_VacationMode'].'</span>';
                }
            }
        }
        else
        {
            if($CurrentUser['settings_ExpandedBuildView'] == 0)
            {
                continue;
            }
            $Row['Input'] = '&nbsp;';
            $Row['TechRequirementsPlace'] = GetElementTechReq($CurrentUser, $CurrentPlanet, $Element);
        }

        $PageTable .= parsetemplate($ElementRowTPL, $Row);
    }

    $BuildQueue = '';
    if($CurrentPlanet['shipyardQueue'] != '')
    {
        include($_EnginePath.'includes/functions/ElementBuildListBox.php');
        $BuildQueue = ElementBuildListBox($CurrentUser, $CurrentPlanet);
    }

    $parse = $_Lang;

    $parse['buildlist'] = $PageTable;
    $parse['buildinglist'] = $BuildQueue;
    $parse['QueueSize'] = $QueueSize;

    display(parsetemplate(gettemplate('buildings_fleet'), $parse), $_Lang['Hangar']);
}

?>
