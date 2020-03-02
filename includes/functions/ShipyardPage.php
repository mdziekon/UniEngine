<?php

use UniEngine\Engine\Modules\Development;

use UniEngine\Engine\Includes\Helpers\World\Elements;

function ShipyardPage(&$CurrentPlanet, $CurrentUser, $PageType = 'fleet')
{
    global $_Lang, $_Vars_GameElements, $_Vars_ElementCategories, $_SkinPath, $_GameConfig, $_POST, $UserDev_Log, $_EnginePath;

    include($_EnginePath.'includes/functions/GetMaxConstructibleElements.php');
    include($_EnginePath.'includes/functions/GetElementTechReq.php');
    includeLang('worldElements.detailed');

    $isUserOnVacation = isOnVacation($CurrentUser);

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
    $TPL['list_hidden']                     = gettemplate('buildings_compact_list_hidden');
    $TPL['list_row']                        = gettemplate('buildings_compact_list_row');
    $TPL['list_breakrow']                   = gettemplate('buildings_compact_list_breakrow');
    $TPL['queue_topinfo']                   = gettemplate('buildings_compact_queue_topinfo');
    $TPL['infobox_additionalnfo']           = gettemplate('buildings_compact_infobox_additionalnfo');
    $TPL['infobox_additionalnfo_single']    = gettemplate('buildings_compact_infobox_additionalnfo_single');

    $hasShipyard = ($CurrentPlanet[$_Vars_GameElements[21]] > 0);

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
    if(!$isUserOnVacation)
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

    $TabIndex = 1;

    foreach ($_Vars_ElementCategories[$PageType] as $ElementID) {
        $elementCurrentState = Elements\getElementState($ElementID, $CurrentPlanet, $CurrentUser)['count'];

        $hasReachedShieldCountLimit = false;
        $hasReachedMissileSiloCapacity = false;
        $maxElementsCount = 0;

        $hasTechnologyRequirementMet = IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $ElementID);
        $hasConstructionResourcesForOneUnit = IsElementBuyable($CurrentUser, $CurrentPlanet, $ElementID, false);

        $maxElementsCount = GetMaxConstructibleElements($ElementID, $CurrentPlanet);
        if($IsInDefense)
        {
            if($ElementID == 407 OR $ElementID == 408)
            {
                if($Shields[$ElementID] >= 1)
                {
                    $maxElementsCount = 0;
                    $hasReachedShieldCountLimit = true;
                }
                else
                {
                    $maxElementsCount = 1;
                }
            }
            else if($ElementID == 502 OR $ElementID == 503)
            {
                $MaxMissilesSpace = floor($SiloFreeSpace / $MissileSizes[$ElementID]);
                if($MaxMissilesSpace > 0)
                {
                    if($MaxMissilesSpace < $maxElementsCount)
                    {
                        $maxElementsCount = $MaxMissilesSpace;
                    }
                }
                else
                {
                    $hasReachedMissileSiloCapacity = true;
                    $maxElementsCount = 0;
                }
            }
        }

        $elementPrice = GetBuildingPrice($CurrentUser, $CurrentPlanet, $ElementID, true, false, true);
        foreach ($elementPrice as $Key => $Value) {
            if ($Value <= 0) {
                continue;
            }

            $ElementPriceArray[$ElementID][$Key] = $Value;
        }
        $ElementTimeArray[$ElementID] = GetBuildingTime($CurrentUser, $CurrentPlanet, $ElementID);


        $BlockReason = [];

        if (!$hasConstructionResourcesForOneUnit) {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoResources'];
        }
        if (!$hasTechnologyRequirementMet) {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoTech'];
        }
        if ($hasReachedShieldCountLimit) {
            $BlockReason[] = $_Lang['ListBox_Disallow_ShieldBlock'];
        }
        if ($hasReachedMissileSiloCapacity) {
            $BlockReason[] = $_Lang['ListBox_Disallow_MissileBlock'];
        }
        if (!$hasShipyard) {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoShipyard'];
        }
        if ($isUserOnVacation) {
            $BlockReason[] = $_Lang['ListBox_Disallow_VacationMode'];
        }

        $isUpgradeAvailableNow = (
            !$isUserOnVacation &&
            $hasShipyard &&
            $hasTechnologyRequirementMet &&
            !$hasReachedShieldCountLimit &&
            !$hasReachedMissileSiloCapacity
        );
        $isUpgradeQueueableNow = (
            $isUpgradeAvailableNow &&
            $hasConstructionResourcesForOneUnit
        );

        if ($isUpgradeAvailableNow) {
            $TabIndex += 1;
        }

        $iconComponent = Development\Components\GridViewElementIcon\render([
            'elementID' => $ElementID,
            'elementDetails' => [
                'currentState' => $elementCurrentState,
                'queueLevelModifier' => 0,
                'isInQueue' => false,
                'isUpgradeAvailableNow' => $isUpgradeAvailableNow,
                'isUpgradeQueueableNow' => $isUpgradeQueueableNow,
                'whyUpgradeImpossible' => [ end($BlockReason) ],
            ],
            'getUpgradeElementActionLinkHref' => function () {
                return "";
            },
            'tabIdx' => (
                $isUpgradeAvailableNow ?
                $TabIndex :
                null
            ),
        ]);

        $cardInfoComponent = Development\Components\GridViewElementCard\render([
            'elementID' => $ElementID,
            'user' => $CurrentUser,
            'planet' => $CurrentPlanet,
            'isQueueActive' => false,
            'elementDetails' => [
                'currentState' => $elementCurrentState,
                'isInQueue' => false,
                'queueLevelModifier' => 0,
                'isUpgradePossible' => true,
                'isUpgradeAvailable' => false,
                'isUpgradeQueueable' => false,
                'whyUpgradeImpossible' => [],
                'isDowngradePossible' => false,
                'isDowngradeAvailable' => false,
                'isDowngradeQueueable' => false,
                'hasTechnologyRequirementMet' => $hasTechnologyRequirementMet,
                'additionalUpgradeDetailsRows' => [
                    parsetemplate(
                        $TPL['infobox_additionalnfo'],
                        [
                            'LabelClasses' => '',
                            'Label' => $_Lang['InfoBox_MaxConstructible'],
                            'ValueClasses' => '',
                            'ValueOtherAttributes' => "id=\"maxConst_{$ElementID}\"",
                            'Value' => prettyNumber($maxElementsCount),
                        ]
                    ),
                    (
                        in_array($ElementID, $_Vars_ElementCategories['prod']) ?
                        Development\Components\GridViewElementCard\UpgradeProductionChange\render([
                            'elementID' => $ElementID,
                            'user' => $CurrentUser,
                            'planet' => $CurrentPlanet,
                            'timestamp' => $Now,
                            'elementDetails' => [
                                'currentState' => 1,
                                'queueLevelModifier' => 0,
                            ],
                        ])['componentHTML'] :
                        ''
                    ),
                    (
                        $hasReachedShieldCountLimit ?
                        parsetemplate(
                            $TPL['infobox_additionalnfo_single'],
                            [
                                'ValueClasses' => 'red',
                                'Value' => $_Lang['ListBox_Disallow_ShieldBlock'],
                            ]
                        ) :
                        ''
                    ),
                    (
                        $hasReachedMissileSiloCapacity ?
                        parsetemplate(
                            $TPL['infobox_additionalnfo_single'],
                            [
                                'ValueClasses' => 'red',
                                'Value' => $_Lang['ListBox_Disallow_MissileBlock'],
                            ]
                        ) :
                        ''
                    ),
                ],
            ],
            'getUpgradeElementActionLinkHref' => function () {
                return '';
            },
            'getDowngradeElementActionLinkHref' => function () {
                return '';
            },
            'hideActionBtnsContainerWhenUnavailable' => true,
        ]);

        $StructuresList[] = $iconComponent['componentHTML'];
        $InfoBoxes[] = $cardInfoComponent['componentHTML'];
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
