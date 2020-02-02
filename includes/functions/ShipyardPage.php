<?php

function ShipyardPage(&$CurrentPlanet, $CurrentUser, $PageType = 'fleet')
{
    global $_Lang, $_Vars_GameElements, $_Vars_ElementCategories, $_SkinPath, $_GameConfig, $_POST, $UserDev_Log, $_EnginePath;

    include($_EnginePath.'includes/functions/GetMaxConstructibleElements.php');
    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    includeLang('worldElements.detailed');

    $Now = time();
    $IsInFleet = false;
    $IsInDefense = false;
    if($PageType == 'fleet')
    {
        $IsInFleet = true;
    }
    else if($PageType == 'defense')
    {
        $IsInDefense = true;
    }
    if($IsInDefense)
    {
        $_Lang['Cart_Ships'] = $_Lang['Cart_Defense'];
        $_Lang['InfoBox_SelectShip'] = $_Lang['InfoBox_SelectDefense'];
        $_Lang['ListBox_ShipsList'] = $_Lang['ListBox_DefensesList'];
        $_Lang['Title_Shipyard'] = $_Lang['Title_Defense'];
    }
    $Parse = &$_Lang;
    $Parse['SkinPath'] = $_SkinPath;
    $Parse['Create_Queue'] = '';

    // Constants
    $ElementsPerRow = 7;
    $QueueSize = ((isPro($CurrentUser)) ? MAX_FLEET_OR_DEFS_PER_ROW_PRO : MAX_FLEET_OR_DEFS_PER_ROW);

    // Get Templates
    $TPL['list_element']                    = gettemplate('buildings_compact_list_element_shipyard');
    $TPL['list_hidden']                        = gettemplate('buildings_compact_list_hidden');
    $TPL['list_row']                        = gettemplate('buildings_compact_list_row');
    $TPL['list_breakrow']                    = gettemplate('buildings_compact_list_breakrow');
    $TPL['list_disabled']                    = gettemplate('buildings_compact_list_disabled');
    $TPL['list_disabled']                    = parsetemplate($TPL['list_disabled'], array('AddOpacity' => ''));
    $TPL['queue_topinfo']                    = gettemplate('buildings_compact_queue_topinfo');
    $TPL['infobox_body']                    = gettemplate('buildings_compact_infobox_body_shipyard');
    $TPL['infobox_req_res']                    = gettemplate('buildings_compact_infobox_req_res');
    $TPL['infobox_additionalnfo']            = gettemplate('buildings_compact_infobox_additionalnfo');
    $TPL['infobox_additionalnfo_single']    = gettemplate('buildings_compact_infobox_additionalnfo_single');
    $TPL['infobox_req_selector_single']        = gettemplate('buildings_compact_infobox_req_selector_single');
    $TPL['infobox_req_selector_dual']        = gettemplate('buildings_compact_infobox_req_selector_dual');

    if($CurrentPlanet[$_Vars_GameElements[21]] > 0)
    {
        $HasShipyard = true;
    }
    else
    {
        $HasShipyard = false;
    }

    PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $Now);

    if($IsInDefense)
    {
        $MissileSizes = array(502 => 1, 503 => 2);
        $Missiles[502] = $CurrentPlanet[$_Vars_GameElements[502]];
        $Missiles[503] = $CurrentPlanet[$_Vars_GameElements[503]];
        $Shields[407] = $CurrentPlanet[$_Vars_GameElements[407]];
        $Shields[408] = $CurrentPlanet[$_Vars_GameElements[408]];
        $SiloSize = $CurrentPlanet[$_Vars_GameElements[44]];
        $MaxMissiles = $SiloSize * SILO_PERLEVELPLACE;
        $SiloFreeSpace = $MaxMissiles;

        $CurrentQueue = $CurrentPlanet['shipyardQueue'];
        if(!empty($CurrentQueue))
        {
            $CurrentQueue = explode(';', $CurrentQueue);
            foreach($CurrentQueue as $QueueData)
            {
                if(empty($QueueData))
                {
                    continue;
                }
                $QueueData = explode(',', $QueueData);
                $ElementID = $QueueData[0];
                $ElementCount = $QueueData[1];
                if($ElementCount > 0)
                {
                    if($ElementID == 502 OR $ElementID == 503)
                    {
                        $Missiles[$ElementID] += $ElementCount;
                    }
                    else if($ElementID == 407 OR $ElementID == 408)
                    {
                        $Shields[$ElementID] += $ElementCount;
                    }
                }
            }
        }

        foreach($Missiles as $MissileKey => $MissileCount)
        {
            if($MissileCount > 0)
            {
                $SiloFreeSpace -= $MissileCount * $MissileSizes[$MissileKey];
            }
        }
    }

    // Execute Commands
    if(!isOnVacation($CurrentUser))
    {
        if(isset($_POST['cmd']) && $_POST['cmd'] == 'exec')
        {
            $AddedInQueue = false;
            $AddedSomething = false;

            if($CurrentPlanet['shipyardQueue'] == '0')
            {
                $CurrentPlanet['shipyardQueue'] = '';
            }

            if($CurrentPlanet)
            {
                include($_EnginePath.'includes/functions/GetElementRessources.php');
                foreach($_POST['elem'] as $ElementID => $Count)
                {
                    $Element = intval($ElementID);
                    $Count = floor(floatval(str_replace('.', '', $Count)));
                    if(in_array($ElementID, $_Vars_ElementCategories[$PageType]))
                    {
                        if($Count > 0)
                        {
                            if($Count > $QueueSize)
                            {
                                $Count = $QueueSize;
                            }

                            if($IsInDefense)
                            {
                                if($ElementID == 407 OR $ElementID == 408)
                                {
                                    if($Shields[$ElementID] >= 1)
                                    {
                                        continue;
                                    }
                                    else if($Count > 1)
                                    {
                                        $Count = 1;
                                    }
                                }
                                else if($ElementID == 502 OR $ElementID == 503)
                                {
                                    $ThisNeededSpace = $Count * $MissileSizes[$ElementID];
                                    if($ThisNeededSpace > $SiloFreeSpace)
                                    {
                                        $Count = floor($SiloFreeSpace/$MissileSizes[$ElementID]);
                                        if($Count <= 0)
                                        {
                                            continue;
                                        }
                                    }
                                }
                            }

                            if(IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $ElementID))
                            {
                                $MaxElements = GetMaxConstructibleElements($ElementID, $CurrentPlanet);
                                if($Count > $MaxElements)
                                {
                                    $Count = $MaxElements;
                                }

                                if($Count > 0)
                                {
                                    if($ElementID == 407 OR $ElementID == 408)
                                    {
                                        $Shields[$ElementID] += $Count;
                                    }
                                    else if($ElementID == 502 OR $ElementID == 503)
                                    {
                                        $Missiles[$ElementID] += $Count;
                                        $SiloFreeSpace -= $Count * $MissileSizes[$ElementID];
                                    }

                                    $Ressource = GetElementRessources($ElementID, $Count);
                                    $AddedSomething = true;

                                    if(!isset($UpdateAchievements[$ElementID]))
                                    {
                                        $UpdateAchievements[$ElementID] = 0;
                                    }
                                    $UpdateAchievements[$ElementID] += $Count;
                                    $addToBHangar = "{$ElementID},{$Count};";
                                    $CurrentPlanet['metal'] -= $Ressource['metal'];
                                    $CurrentPlanet['crystal'] -= $Ressource['crystal'];
                                    $CurrentPlanet['deuterium'] -= $Ressource['deuterium'];
                                    $CurrentPlanet['shipyardQueue'] .= $addToBHangar;

                                    $DevLog_Array[] = "{$ElementID},{$Count}";
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
                    $QryAchievements = '';
                    $QryAchievements .= "INSERT INTO {{table}} (`A_UserID`, ".implode(', ', $QryAchievementsKey).") VALUES ({$CurrentUser['id']}, ".implode(', ', $UpdateAchievements).")";
                    $QryAchievements .= " ON DUPLICATE KEY UPDATE ";
                    $QryAchievements .= implode(', ', $QryAchievementsArr);
                    $QryAchievements .= ';';
                    doquery($QryAchievements, 'achievements_stats');

                    // Update DevLog
                    $UserDev_Log[] = array('PlanetID' => $CurrentPlanet['id'], 'Date' => $Now, 'Place' => ($IsInFleet ? 6 : 7), 'Code' => '0', 'ElementID' => '0', 'AdditionalData' => implode(';', $DevLog_Array));
                }
            }
        }
    }
    // End of - Execute Commands

    // Parse Queue
    $CurrentQueue = $CurrentPlanet['shipyardQueue'];
    $QueueIndex = 0;
    $TotalTime = 0;
    if(!empty($CurrentQueue))
    {
        $CurrentQueue = explode(';', $CurrentQueue);
        foreach($CurrentQueue as $QueueID => $QueueData)
        {
            if(empty($QueueData))
            {
                continue;
            }
            $QueueData = explode(',', $QueueData);
            $ListID = $QueueIndex + 1;
            $ElementID = $QueueData[0];
            $ElementCount = $QueueData[1];
            $ElementName = $_Lang['tech'][$ElementID];
            $ElementTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $ElementID);
            $ElementTotalTime = $ElementTime * $ElementCount;
            $TotalTime += $ElementTotalTime;
            if($ElementTime > 0)
            {
                $RemoveCount = 0.1 / $ElementTime;
            }
            else
            {
                $RemoveCount = $ElementCount;
            }

            $QueueParser[] = array
            (
                'ElementNo' => $ListID,
                'ElementID' => $ElementID,
                'Name'        => $ElementName,
                'Count'        => prettyNumber($ElementCount),
            );
            $QueueJSArray[$ListID] = array('No' => $ListID, 'Remove' => $RemoveCount, 'Count' => $ElementCount);

            $QueueIndex += 1;
        }

        if(!empty($QueueParser))
        {
            $TotalTime -= $CurrentPlanet['shipyardQueue_additionalWorkTime'];
            $QueueJSArray[1]['Count'] -= ($QueueJSArray[1]['Remove'] * (10 + ($CurrentPlanet['shipyardQueue_additionalWorkTime'] * 10)));

            include($_EnginePath.'/includes/functions/InsertJavaScriptChronoApplet.php');
            $QueueParser[0]['ChronoAppletScript'] = InsertJavaScriptChronoApplet(
                'QueueFirstTimer',
                '',
                $TotalTime,
                false,
                false,
                'function() { onQueuesFirstElementFinished("' . $PageType . '"); }'
            );
            $QueueParser[0]['EndTimer'] = pretty_time($TotalTime, true);
            $Parse['Create_RunQueueJSHandler'] = 'true';

            foreach($QueueParser as $QueueID => $QueueData)
            {
                if($QueueID == 0)
                {
                    $ThisTPL = gettemplate('buildings_compact_queue_firstel_shipyard');
                }
                else if($QueueID == 1)
                {
                    $ThisTPL = gettemplate('buildings_compact_queue_nextel_shipyard');
                }
                $Parse['Create_Queue'] .= parsetemplate($ThisTPL, $QueueData);
            }
        }
    }
    else
    {
        $Parse['Create_Queue'] = parsetemplate($TPL['queue_topinfo'], array('InfoText' => $_Lang['Queue_Empty']));
    }
    // End of - Parse Queue

    $ResImages = array
    (
        'metal' => 'metall',
        'crystal' => 'kristall',
        'deuterium' => 'deuterium',
        'energy_max' => 'energie',
        'darkEnergy' => 'darkenergy'
    );
    $ResLangs = array
    (
        'metal' => $_Lang['Metal'],
        'crystal' => $_Lang['Crystal'],
        'deuterium' => $_Lang['Deuterium'],
        'energy_max' => $_Lang['Energy'],
        'darkEnergy' => $_Lang['DarkEnergy']
    );

    $ElementParserDefault = array
    (
        'SkinPath'                    => $_SkinPath,
        'InfoBox_Count'                => $_Lang['InfoBox_Count'],
        'InfoBox_Build'                => $_Lang['InfoBox_DoResearch'],
        'InfoBox_RequirementsFor'    => $_Lang['InfoBox_RequirementsForShip'],
        'InfoBox_ResRequirements'    => $_Lang['InfoBox_ResRequirementsShip'],
        'InfoBox_Requirements_Res'    => $_Lang['InfoBox_Requirements_Res'],
        'InfoBox_Requirements_Tech'    => $_Lang['InfoBox_Requirements_Tech'],
        'InfoBox_BuildTime'            => $_Lang['InfoBox_ConstructionTime'],
        'InfoBox_MaxConstructible'    => $_Lang['InfoBox_MaxConstructible'],
        'ElementPriceDiv'            => ''
    );

    $TabIndex = 1;

    foreach($_Vars_ElementCategories[$PageType] as $ElementID)
    {
        $ElementParser = $ElementParserDefault;

        $HasResources = true;
        $TechLevelOK = false;
        $BlockShield = false;
        $BlockMissile = false;

        $ElementParser['ElementCount'] = prettyNumber($CurrentPlanet[$_Vars_GameElements[$ElementID]]);
        if(strlen($ElementParser['ElementCount']) > 10)
        {
            $ElementParser['IsBigNum'] = 'bignum';
        }
        $ElementParser['MaxConstructible'] = GetMaxConstructibleElements($ElementID, $CurrentPlanet);
        if($IsInDefense)
        {
            if($ElementID == 407 OR $ElementID == 408)
            {
                if($Shields[$ElementID] >= 1)
                {
                    $ElementParser['MaxConstructible'] = 0;
                    $BlockShield = true;
                }
                else
                {
                    $ElementParser['MaxConstructible'] = 1;
                }
            }
            else if($ElementID == 502 OR $ElementID == 503)
            {
                $MaxMissilesSpace = floor($SiloFreeSpace / $MissileSizes[$ElementID]);
                if($MaxMissilesSpace > 0)
                {
                    if($MaxMissilesSpace < $ElementParser['MaxConstructible'])
                    {
                        $ElementParser['MaxConstructible'] = $MaxMissilesSpace;
                    }
                }
                else
                {
                    $BlockMissile = true;
                    $ElementParser['MaxConstructible'] = 0;
                }
            }
        }
        $ElementParser['MaxConstructible'] = prettyNumber($ElementParser['MaxConstructible']);
        $ElementParser['ElementName'] = $_Lang['tech'][$ElementID];
        $ElementParser['ElementID'] = $ElementID;
        $ElementParser['Desc'] = $_Lang['WorldElements_Detailed'][$ElementID]['description_short'];

        $ElementParser['ElementPrice'] = GetBuildingPrice($CurrentUser, $CurrentPlanet, $ElementID, true, false, true);
        foreach($ElementParser['ElementPrice'] as $Key => $Value)
        {
            if($Value > 0)
            {
                $ResColor = '';
                $ResMinusColor = '';
                $MinusValue = '&nbsp;';

                if($Key != 'darkEnergy')
                {
                    $UseVar = &$CurrentPlanet;
                }
                else
                {
                    $UseVar = &$CurrentUser;
                }
                if($UseVar[$Key] < $Value)
                {
                    $ResMinusColor = 'red';
                    $MinusValue = '('.prettyNumber($UseVar[$Key] - $Value).')';
                    $ResColor = 'red';
                }

                $ElementParser['ElementPrices'] = array
                (
                    'SkinPath' => $_SkinPath,
                    'ResName' => $Key,
                    'ResImg' => $ResImages[$Key],
                    'ResColor' => $ResColor,
                    'Value' => prettyNumber($Value),
                    'ResMinusColor' => $ResMinusColor,
                    'MinusValue' => $MinusValue
                );
                $ElementParser['ElementPriceDiv'] .= parsetemplate($TPL['infobox_req_res'], $ElementParser['ElementPrices']);
                $ElementPriceArray[$ElementID][$Key] = $Value;
            }
        }
        $ElementParser['BuildTime'] = GetBuildingTime($CurrentUser, $CurrentPlanet, $ElementID);
        $ElementTimeArray[$ElementID] = $ElementParser['BuildTime'];
        $ElementParser['BuildTime'] = pretty_time($ElementParser['BuildTime']);

        if (in_array($ElementID, $_Vars_ElementCategories['prod'])) {
            // Calculate theoretical production increase
            $productionIncrease = getElementProduction(
                $ElementID,
                $CurrentPlanet,
                $CurrentUser,
                [
                    'useCurrentBoosters' => true,
                    'currentTimestamp' => $Now,
                    'customLevel' => 1,
                    'customProductionFactor' => 10
                ]
            );

            $resourceLabels = [
                'metal' => $_Lang['Metal'],
                'crystal' => $_Lang['Crystal'],
                'deuterium' => $_Lang['Deuterium'],
                'energy' => $_Lang['Energy'],
            ];

            foreach ($productionIncrease as $resourceKey => $difference) {
                if ($difference == 0) {
                    continue;
                }

                $differenceFormatted = prettyNumber($difference);
                $label = $resourceLabels[$resourceKey];

                $ElementParser['AdditionalNfo'][] = parsetemplate(
                    $TPL['infobox_additionalnfo'],
                    [
                        'Label' => $label,
                        'ValueClasses' => (
                            $difference >= 0 ?
                            'lime' :
                            'red'
                        ),
                        'Value' => (
                            $difference >= 0 ?
                            ('+' . $differenceFormatted) :
                            $differenceFormatted
                        )
                    ]
                );
            }
        }

        if(IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $ElementID))
        {
            $TechLevelOK = true;
            $ElementParser['ElementRequirementsHeadline'] = $TPL['infobox_req_selector_single'];
        }
        else
        {
            $ElementParser['ElementRequirementsHeadline'] = $TPL['infobox_req_selector_dual'];
            $ElementParser['ElementTechDiv'] = GetElementTechReq($CurrentUser, $CurrentPlanet, $ElementID, true);
            $ElementParser['HideResReqDiv'] = 'hide';
        }
        $HasResources = IsElementBuyable($CurrentUser, $CurrentPlanet, $ElementID, false);

        $BlockReason = array();

        if(!$HasResources)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoResources'];
        }
        if(!$TechLevelOK)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoTech'];
        }
        if($BlockShield)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_ShieldBlock'];
            $ElementParser['AdditionalNfo'][] = parsetemplate($TPL['infobox_additionalnfo_single'], array('ValueClasses' => 'red', 'Value' => $_Lang['ListBox_Disallow_ShieldBlock']));
        }
        if($BlockMissile)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_MissileBlock'];
            $ElementParser['AdditionalNfo'][] = parsetemplate($TPL['infobox_additionalnfo_single'], array('ValueClasses' => 'red', 'Value' => $_Lang['ListBox_Disallow_MissileBlock']));
        }
        if(!$HasShipyard)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoShipyard'];
        }
        if(isOnVacation($CurrentUser))
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_VacationMode'];
        }

        if(!empty($BlockReason))
        {
            $ElementParser['ElementDisabled'] = $TPL['list_disabled'];
            $ElementParser['ElementDisableInv'] = 'inv';
            $ElementParser['ElementDisableReason'] = end($BlockReason);
        }
        else
        {
            $ElementParser['TabIndex'] = $TabIndex;
            $TabIndex += 1;
        }

        if(!empty($ElementParser['AdditionalNfo']))
        {
            $ElementParser['AdditionalNfo'] = implode('', $ElementParser['AdditionalNfo']);
        }
        $ElementParser['ElementRequirementsHeadline'] = parsetemplate($ElementParser['ElementRequirementsHeadline'], $ElementParser);
        $StructuresList[] = parsetemplate($TPL['list_element'], $ElementParser);
        $InfoBoxes[] = parsetemplate($TPL['infobox_body'], $ElementParser);
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
    $Parse['Create_PrettyTimeZero'] = pretty_time(0);
    $Parse['Create_InsertPrices'] = json_encode($ElementPriceArray);
    $Parse['Create_InsertTimes'] = json_encode($ElementTimeArray);
    if($CurrentUser['settings_useprettyinputbox'] == 1)
    {
        $Parse['P_AllowPrettyInputBox'] = 'true';
    }
    else
    {
        $Parse['P_AllowPrettyInputBox'] = 'false';
    }
    $Parse['Create_QueueJSArray'] = json_encode(isset($QueueJSArray) ? $QueueJSArray : null);
    $Parse['Create_LastJSQueueID'] = ($QueueIndex > 0 ? $QueueIndex : '0');
    $Parse['Create_MetalMax'] = floor($CurrentPlanet['metal']);
    $Parse['Create_CrystalMax'] = floor($CurrentPlanet['crystal']);
    $Parse['Create_DeuteriumMax'] = floor($CurrentPlanet['deuterium']);
    // End of - Parse all available ships

    $Parse['Insert_SkinPath'] = $_SkinPath;
    $Parse['Insert_PlanetImg'] = $CurrentPlanet['image'];
    $Parse['Insert_PlanetType'] = $_Lang['PlanetType_'.$CurrentPlanet['planet_type']];
    $Parse['Insert_PlanetName'] = $CurrentPlanet['name'];
    $Parse['Insert_PlanetPos_Galaxy'] = $CurrentPlanet['galaxy'];
    $Parse['Insert_PlanetPos_System'] = $CurrentPlanet['system'];
    $Parse['Insert_PlanetPos_Planet'] = $CurrentPlanet['planet'];
    $Parse['Insert_Overview_ShipyardLevel'] = $CurrentPlanet[$_Vars_GameElements[21]];
    $Parse['Insert_Overview_NanoFactoryLevel'] = $CurrentPlanet[$_Vars_GameElements[15]];
    $Parse['Insert_Overview_Temperature'] = sprintf(
        $_Lang['Overview_Form_Temperature'],
        $CurrentPlanet['temp_min'],
        $CurrentPlanet['temp_max']
    );

    $solarSatelliteEnergyProduction = getElementProduction(
        212,
        $CurrentPlanet,
        $CurrentUser,
        [
            'useCurrentBoosters' => true,
            'currentTimestamp' => $Now,
            'customLevel' => 1,
            'customProductionFactor' => 10
        ]
    );

    $Parse['Insert_Overview_SolarSateliteEnergy'] = prettyNumber($solarSatelliteEnergyProduction['energy']);

    $Page = parsetemplate(gettemplate('buildings_compact_body_shipyard'), $Parse);

    display($Page, $_Lang['Title_Shipyard']);
}

?>
