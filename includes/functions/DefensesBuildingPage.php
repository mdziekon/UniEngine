<?php

function DefensesBuildingPage(&$CurrentPlanet, $CurrentUser)
{
     global $_EnginePath, $_Lang, $_Vars_GameElements, $_SkinPath, $_POST, $_Vars_ElementCategories, $UserDev_Log;

    include($_EnginePath.'includes/functions/GetMaxConstructibleElements.php');
    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    include($_EnginePath.'includes/functions/GetElementPrice.php');
    includeLang('worldElements.detailed');

    $Now = time();

    if($CurrentPlanet[$_Vars_GameElements[21]] <= 0)
    {
        message($_Lang['need_hangar'], $_Lang['tech'][21]);
    }

    $AddedMissilesFromQueue = false;
    $AddedShieldsFromQueue = false;
    $DoNotShowSelectors = false;

    $Missiles[502] = $CurrentPlanet[$_Vars_GameElements[502]];
    $Missiles[503] = $CurrentPlanet[$_Vars_GameElements[503]];
    $SiloSize = $CurrentPlanet[$_Vars_GameElements[44]];
    $MaxMissiles = $SiloSize * 25;

    $QueueSize = ((isPro($CurrentUser)) ? MAX_FLEET_OR_DEFS_PER_ROW_PRO : MAX_FLEET_OR_DEFS_PER_ROW);

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $Now);

    if(!isOnVacation($CurrentUser))
    {
        if(isset($_POST['fmenge']))
        {
            $Shields = array(408 => 0, 407 => 0);
            $Missiles = array(502 => 0, 503 => 0);

            $AddedSomething = false;

            $CurrentPlanet = doquery("SELECT * FROM {{table}} WHERE `id` = {$CurrentPlanet['id']} LIMIT 1;", 'planets', true);
            $BuildQueueList = $CurrentPlanet['shipyardQueue'];
            $BuildArray = explode (';', $BuildQueueList);
            $BuildArrayCount = count($BuildArray);
            for($QElement = 0; $QElement < $BuildArrayCount; $QElement += 1)
            {
                $ElmentArray = explode(',', $BuildArray[$QElement]);
                if($ElmentArray[0] == 502 AND $ElmentArray[1] > 0)
                {
                    $Missiles[502] += $ElmentArray[1];
                    $AddedMissilesFromQueue = true;
                }
                else if($ElmentArray[0] == 503 AND $ElmentArray[1] > 0)
                {
                    $Missiles[503] += $ElmentArray[1];
                    $AddedMissilesFromQueue = true;
                }
                else if($ElmentArray[0] == 408 AND $ElmentArray[1] > 0)
                {
                    $Shields[408] += $ElmentArray[1];
                    $AddedShieldsFromQueue = true;
                }
                else if($ElmentArray[0] == 407 AND $ElmentArray[1] > 0)
                {
                    $Shields[407] += $ElmentArray[1];
                    $AddedShieldsFromQueue = true;
                }
            }

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
                    if(in_array($Element, $_Vars_ElementCategories['defense']))
                    {
                        if($Count > $QueueSize)
                        {
                            $Count = $QueueSize;
                        }
                        if($Count >= 1)
                        {
                            if($Element == 407)
                            {
                                if($CurrentPlanet[$_Vars_GameElements[407]] >= 1 OR $Shields[407] > 0)
                                {
                                    $Count = 0;
                                }
                                else
                                {
                                    $Count = 1;
                                }
                            }
                            if($Element == 408)
                            {
                                if($CurrentPlanet[$_Vars_GameElements[408]] >= 1 OR $Shields[408] > 0)
                                {
                                    $Count = 0;
                                }
                                else
                                {
                                    $Count = 1;
                                }
                            }
                            if(IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element))
                            {
                                $MaxElements = GetMaxConstructibleElements($Element, $CurrentPlanet);
                                if($Element == 502 OR $Element == 503)
                                {
                                    $ActuMissiles = $Missiles[502] + (2 * $Missiles[503]);
                                    $MissilesSpace = $MaxMissiles - $ActuMissiles;
                                    if($Element == 502)
                                    {
                                        if($Count > $MissilesSpace)
                                        {
                                            $Count = $MissilesSpace;
                                        }
                                    }
                                    else
                                    {
                                        if($Count > floor($MissilesSpace / 2 ))
                                        {
                                            $Count = floor($MissilesSpace / 2);
                                        }
                                    }
                                    if($Count > $MaxElements)
                                    {
                                        $Count = $MaxElements;
                                    }
                                    $Missiles[$Element] += $Count;
                                }
                                else
                                {
                                    if($Count > $MaxElements)
                                    {
                                        $Count = $MaxElements;
                                    }
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
                                    $CurrentPlanet['metal'] -= $Ressource['metal'];
                                    $CurrentPlanet['crystal'] -= $Ressource['crystal'];
                                    $CurrentPlanet['deuterium'] -= $Ressource['deuterium'];
                                    $CurrentPlanet['shipyardQueue'] .= "{$Element},{$Count};";

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
                    $UserDev_Log[] = array('PlanetID' => $CurrentPlanet['id'], 'Date' => $Now, 'Place' => 7, 'Code' => '0', 'ElementID' => '0', 'AdditionalData' => implode(';', $DevLog_Array));
                }
            }
        }
    }

    $TabIndex = 0;
    $PageTable = '';

    $ElementRowTPL = gettemplate('buildings_fleet_row');
    $ElementRowInputTPL = gettemplate('buildings_fleet_row_input');

    foreach($_Vars_ElementCategories['defense'] as $Element)
    {
        $Row = array();

        $Row['Element'] = $Element;
        $Row['skinpath'] = $_SkinPath;
        $Row['ElementName'] = $_Lang['tech'][$Element];
        $ElementCount = $CurrentPlanet[$_Vars_GameElements[$Element]];
        $Row['ElementNbre'] = ($ElementCount == 0) ? '' : " ({$_Lang['dispo']}: " . prettyNumber($ElementCount) . ")";
        $Row['Description'] = $_Lang['WorldElements_Detailed'][$Element]['description_short'];

        if(IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element))
        {
            $CanBuildOne = IsElementBuyable($CurrentUser, $CurrentPlanet, $Element, false);
            $BuildOneElementTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $Element);

            $Row['GetElementPrice'] = GetElementPrice($CurrentUser, $CurrentPlanet, $Element, false);
            $Row['ShowBuildTime'] = ShowBuildTime($BuildOneElementTime);

            if($Element == 502 OR $Element == 503)
            {
                if($AddedMissilesFromQueue == false)
                {
                    $BuildQueueList = $CurrentPlanet['shipyardQueue'];
                    $BuildArray = explode (';', $BuildQueueList);
                    $BuildArrayCount= count($BuildArray);
                    for($QElement = 0; $QElement < $BuildArrayCount; $QElement += 1)
                    {
                        $ElmentArray = explode (',', $BuildArray[$QElement] );
                        if($ElmentArray[0] == 502 AND $ElmentArray[1] > 0)
                        {
                            $Missiles[502] += $ElmentArray[1];
                        }
                        else if($ElmentArray[0] == 503 AND $ElmentArray[1] > 0)
                        {
                            $Missiles[503] += $ElmentArray[1];
                        }
                    }
                }
                $CurrentMissiles = $Missiles[502] + ($Missiles[503] * 2);

                if($Element == 502)
                {
                    $MissileSize = 1;
                }
                else
                {
                    $MissileSize = 2;
                }

                $CanBuildXMissiles = floor(($MaxMissiles - $CurrentMissiles)/$MissileSize);
                if($CanBuildXMissiles > 0)
                {
                    $Row['MissilesInfo'] = "<br/><br/><span class=\"orange\">{$_Lang['CanBuildXMissiles']}:</span> {$CanBuildXMissiles}";
                }
                else
                {
                    $Row['MissilesInfo'] = "<br/><br/><span class=\"red\">{$_Lang['NoMoreSpaceForThisMissiles']}</span>";
                }
            }

            if(isOnVacation($CurrentUser))
            {
                $CanBuildOne = false;
            }

            if($CanBuildOne)
            {
                $InQueue = strpos($CurrentPlanet['shipyardQueue'], $Element.',');
                $IsBuildp = ($CurrentPlanet[$_Vars_GameElements[407]] >= 1) ? TRUE : FALSE;
                $IsBuildg = ($CurrentPlanet[$_Vars_GameElements[408]] >= 1) ? TRUE : FALSE;
                $BuildIt = TRUE;
                if($Element == 407 OR $Element == 408)
                {
                    $BuildIt = false;
                    if($Element == 407 AND !$IsBuildp AND $InQueue === FALSE)
                    {
                        $BuildIt = TRUE;
                    }
                    if($Element == 408 AND !$IsBuildg AND $InQueue === FALSE)
                    {
                        $BuildIt = TRUE;
                    }
                }
                $IsBuild = ($CurrentPlanet[$_Vars_GameElements[408]] >= 1) ? true : false;
                if($Element == 408)
                {
                    $BuildIt = false;
                    if($InQueue === false AND !$IsBuild)
                    {
                        $BuildIt = true;
                    }
                }
                if(!$BuildIt)
                {
                    $Row['Input'] = "<b class=\"red\">{$_Lang['only_one']}</b>";
                }
                else
                {
                    $Row['InputData'] = array();
                    $TabIndex += 1;
                    $MaxElements = GetMaxConstructibleElements($Element, $CurrentPlanet);

                    if($Element == 502 OR $Element == 503)
                    {
                        if($CanBuildXMissiles > 0)
                        {
                            if($MaxElements < $CanBuildXMissiles)
                            {
                                $CanBuildXMissiles = $MaxElements;
                            }
                            $Row['InputData']['MaxElements'] = $CanBuildXMissiles;

                            $MaxElements = $CanBuildXMissiles;
                        }
                        else
                        {
                            $Row['Input'] = "<b class=\"red\">{$_Lang['noMoreSpaceInSilo']}</b>";
                            $DoNotShowSelectors = true;
                        }
                    }
                    else
                    {
                        if($Element == 407 OR $Element == 408)
                        {
                            if($MaxElements > 1)
                            {
                                $MaxElements = 1;
                            }
                        }
                    }
                    if($DoNotShowSelectors == false)
                    {
                        $Row['InputData']['youCanBuild'] = $_Lang['youCanBuild'];
                        $Row['InputData']['MaxElements'] = prettyNumber($MaxElements);
                        $Row['InputData']['Element'] = $Element;
                        $Row['InputData']['ElementName'] = $Row['ElementName'];
                        $Row['InputData']['TabIndex'] = $TabIndex;
                        $Row['InputData']['Max'] = $_Lang['max'];

                        $Row['Input'] = parsetemplate($ElementRowInputTPL, $Row['InputData']);
                    }
                }
            }
            else
            {
                $Row['Input'] = '&nbsp;';
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

    display(parsetemplate(gettemplate('buildings_defense'), $parse), $_Lang['Defense']);
}
?>
