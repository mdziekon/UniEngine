<?php

use UniEngine\Engine\Modules\Development\Components\ModernQueue;
use UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernQueuePlanetInfo;
use UniEngine\Engine\Modules\Development\Screens\ResearchListPage\ModernQueueLabUpgradeInfo;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\Users;

function LaboratoryPage(&$CurrentPlanet, $CurrentUser, $InResearch, $ThePlanet)
{
    global    $_EnginePath, $_Lang,
            $_Vars_GameElements, $_Vars_ElementCategories, $_Vars_MaxElementLevel,
            $_SkinPath, $_GameConfig, $_GET;

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
    $TPL['infobox_body']                = gettemplate('buildings_compact_infobox_body_lab');
    $TPL['infobox_levelmodif']            = gettemplate('buildings_compact_infobox_levelmodif');
    $TPL['infobox_req_res']                = gettemplate('buildings_compact_infobox_req_res');
    $TPL['infobox_additionalnfo']        = gettemplate('buildings_compact_infobox_additionalnfo');
    $TPL['infobox_req_selector_single'] = gettemplate('buildings_compact_infobox_req_selector_single');
    $TPL['infobox_req_selector_dual']    = gettemplate('buildings_compact_infobox_req_selector_dual');

    if($CurrentPlanet[$_Vars_GameElements[31]] > 0)
    {
        $HasLab = true;
    }
    else
    {
        $HasLab = false;
    }

    // Get OtherPlanets with Lab
    $OtherLabs_ConnectedLabs = 0;
    $OtherLabs_ConnectedLabsLevel = 0;
    $OtherLabs_TotalLabsLevel = 0;
    $OtherLabs_LabsCount = 0;

    $LabInQueue_CheckID = 0;

    $Query_GetOtherLabs = '';
    $Query_GetOtherLabs .= "SELECT `id`, `buildQueue`, `{$_Vars_GameElements[31]}` FROM {{table}} ";
    $Query_GetOtherLabs .= "WHERE `id_owner` = {$CurrentUser['id']} AND `planet_type` = 1;";

    $SQLResult_GetOtherLabs = doquery($Query_GetOtherLabs, 'planets');

    if($SQLResult_GetOtherLabs->num_rows > 0)
    {
        $OtherLabs_Levels = [];
        while($FetchData = $SQLResult_GetOtherLabs->fetch_assoc())
        {
            if(!empty($FetchData['buildQueue']))
            {
                if(substr($FetchData['buildQueue'], 0, 3) == '31,' OR strstr($FetchData['buildQueue'], ';31,') !== false)
                {
                    $LabInQueue_CheckID = $FetchData['id'];
                }
            }
            if($FetchData[$_Vars_GameElements[31]] > 0)
            {
                $OtherLabs_Levels[] = $FetchData[$_Vars_GameElements[31]];
            }
        }
        if(!empty($OtherLabs_Levels))
        {
            rsort($OtherLabs_Levels);
            $OtherLabs_ConnectedLabsCount = 1 + $CurrentUser[$_Vars_GameElements[123]];
            foreach($OtherLabs_Levels as $ThisLabLevel)
            {
                if($OtherLabs_ConnectedLabs < $OtherLabs_ConnectedLabsCount)
                {
                    $OtherLabs_ConnectedLabsLevel += $ThisLabLevel;
                    $OtherLabs_ConnectedLabs += 1;
                }
                $OtherLabs_TotalLabsLevel += $ThisLabLevel;
            }
            $OtherLabs_LabsCount = count($OtherLabs_Levels);
        }
    }

    // Check if Lab is in BuildQueue
    $LabInQueue = false;
    $planetsWithUnfinishedLabUpgrades = [];
    if($_GameConfig['BuildLabWhileRun'] != 1 AND $LabInQueue_CheckID > 0)
    {
        include($_EnginePath.'/includes/functions/CheckLabInQueue.php');

        $LabInQueue_CheckPlanet = doquery("SELECT * FROM {{table}} WHERE `id` = {$LabInQueue_CheckID} LIMIT 1;", 'planets', true);

        $Results['planets'] = array();
        // Update Planet - Building Queue
        $CheckLab = CheckLabInQueue($LabInQueue_CheckPlanet);
        if($CheckLab !== false)
        {
            if($CheckLab <= $Now)
            {
                if(HandlePlanetQueue($LabInQueue_CheckPlanet, $CurrentUser, $Now, true) === true)
                {
                    $Results['planets'][] = $LabInQueue_CheckPlanet;
                }
            }
            else
            {
                $planetsWithUnfinishedLabUpgrades[] = $LabInQueue_CheckPlanet;

                $LabInQueue = true;
            }
        }
        HandlePlanetUpdate_MultiUpdate($Results, $CurrentUser);
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
    $cmdResult = UniEngine\Engine\Modules\Development\Input\UserCommands\handleResearchCommand(
        $CurrentUser,
        $ResearchPlanet,
        $_GET,
        [
            "timestamp" => $Now,
            "currentPlanet" => $CurrentPlanet,
            "hasPlanetsWithUnfinishedLabUpgrades" => $LabInQueue
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

    $planetInfoComponent = ModernQueuePlanetInfo\render([
        'currentPlanet'     => &$CurrentPlanet,
        'researchPlanet'    => &$ResearchPlanet,
        'queue'             => Planets\Queues\Research\parseQueueString(
            $ResearchPlanet['techQueue']
        ),
        'timestamp'         => $Now,
    ]);
    $labsUpgradeInfoComponent = ModernQueueLabUpgradeInfo\render([
        'planetsWithUnfinishedLabUpgrades' => $planetsWithUnfinishedLabUpgrades
    ]);

    $queueComponent = ModernQueue\render([
        'user'              => &$CurrentUser,
        'planet'            => &$ResearchPlanet,
        'queue'             => Planets\Queues\Research\parseQueueString(
            $ResearchPlanet['techQueue']
        ),
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

    // Parse Queue
    $CurrentQueue = (isset($ResearchPlanet['techQueue']) ? $ResearchPlanet['techQueue'] : false);
    if(!empty($CurrentQueue))
    {
        $LockResources['metal'] = 0;
        $LockResources['crystal'] = 0;
        $LockResources['deuterium'] = 0;

        $CurrentQueue = explode(';', $CurrentQueue);
        $QueueIndex = 0;
        foreach($CurrentQueue as $QueueID => $QueueData)
        {
            $QueueData = explode(',', $QueueData);
            $BuildEndTime = $QueueData[3];

            if ($BuildEndTime < $Now) {
                continue;
            }

            $ElementID = $QueueData[0];
            if($QueueIndex == 0)
            {
                // Do nothing
            }
            else
            {
                $GetResourcesToLock = GetBuildingPrice($CurrentUser, $CurrentPlanet, $ElementID, true, false);
                $LockResources['metal'] += $GetResourcesToLock['metal'];
                $LockResources['crystal'] += $GetResourcesToLock['crystal'];
                $LockResources['deuterium'] += $GetResourcesToLock['deuterium'];
            }

            if(!isset($LevelModifiers[$ElementID]))
            {
                $LevelModifiers[$ElementID] = 0;
            }
            $LevelModifiers[$ElementID] -= 1;
            $CurrentUser[$_Vars_GameElements[$ElementID]] += 1;

            $QueueIndex += 1;
        }
        $CurrentPlanet['metal'] -= (isset($LockResources['metal']) ? $LockResources['metal'] : 0);
        $CurrentPlanet['crystal'] -= (isset($LockResources['crystal']) ? $LockResources['crystal'] : 0);
        $CurrentPlanet['deuterium'] -= (isset($LockResources['deuterium']) ? $LockResources['deuterium'] : 0);

        $Queue['lenght'] = $QueueIndex;
    }
    else
    {
        $Queue['lenght'] = 0;
    }
    if($LabInQueue === false)
    {
        if($Queue['lenght'] < ((isPro($CurrentUser)) ? MAX_TECH_QUEUE_LENGTH_PRO : MAX_TECH_QUEUE_LENGTH))
        {
            $CanAddToQueue = true;
        }
        else
        {
            $CanAddToQueue = false;
        }
    }
    else
    {
        $CanAddToQueue = false;
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
        'InfoBox_Level'                => $_Lang['InfoBox_Level'],
        'InfoBox_Build'                => $_Lang['InfoBox_DoResearch'],
        'InfoBox_RequirementsFor'    => $_Lang['InfoBox_RequirementsFor'],
        'InfoBox_ResRequirements'    => $_Lang['InfoBox_ResRequirements'],
        'InfoBox_Requirements_Res'    => $_Lang['InfoBox_Requirements_Res'],
        'InfoBox_Requirements_Tech' => $_Lang['InfoBox_Requirements_Tech'],
        'InfoBox_BuildTime'            => $_Lang['InfoBox_ResearchTime'],
        'ElementPriceDiv'            => ''
    );

    foreach($_Vars_ElementCategories['tech'] as $ElementID)
    {
        $ElementParser = $ElementParserDefault;

        $CurrentLevel = $CurrentUser[$_Vars_GameElements[$ElementID]];
        $NextLevel = $CurrentUser[$_Vars_GameElements[$ElementID]] + 1;
        $MaxLevelReached = false;
        $TechLevelOK = false;
        $HasResources = true;

        $HideButton_Build = false;
        $HideButton_QuickBuild = false;

        $ElementParser['HideBuildWarn'] = 'hide';
        $ElementParser['ElementName'] = $_Lang['tech'][$ElementID];
        $ElementParser['ElementID'] = $ElementID;
        $ElementParser['ElementLevel'] = prettyNumber($CurrentUser[$_Vars_GameElements[$ElementID]]);
        $ElementParser['ElementRealLevel'] = prettyNumber($CurrentUser[$_Vars_GameElements[$ElementID]] + (isset($LevelModifiers[$ElementID]) ? $LevelModifiers[$ElementID] : 0));
        $ElementParser['BuildLevel'] = prettyNumber($CurrentUser[$_Vars_GameElements[$ElementID]] + 1);
        $ElementParser['Desc'] = $_Lang['WorldElements_Detailed'][$ElementID]['description_short'];
        $ElementParser['BuildButtonColor'] = 'buildDo_Green';

        if(isset($LevelModifiers[$ElementID]))
        {
            $ElementParser['levelmodif']['modColor'] = 'lime';
            $ElementParser['levelmodif']['modText'] = '+'.prettyNumber($LevelModifiers[$ElementID] * (-1));
            $ElementParser['LevelModifier'] = parsetemplate($TPL['infobox_levelmodif'], $ElementParser['levelmodif']);
            $ElementParser['ElementLevelModif'] = parsetemplate($TPL['list_levelmodif'], $ElementParser['levelmodif']);
            unset($ElementParser['levelmodif']);
        }

        if(!(isset($_Vars_MaxElementLevel[$ElementID]) && $_Vars_MaxElementLevel[$ElementID] > 0 && $NextLevel > $_Vars_MaxElementLevel[$ElementID]))
        {
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
                        if($Queue['lenght'] > 0)
                        {
                            $ResColor = 'orange';
                        }
                        else
                        {
                            $ResColor = 'red';
                        }
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
                }
            }
            $ElementParser['BuildTime'] = pretty_time(GetBuildingTime($CurrentUser, $CurrentPlanet, $ElementID));
        }
        else
        {
            $MaxLevelReached = true;
            $ElementParser['HideBuildInfo'] = 'hide';
            $ElementParser['HideBuildWarn'] = '';
            $HideButton_Build = true;
            $ElementParser['BuildWarn_Color'] = 'red';
            $ElementParser['BuildWarn_Text'] = $_Lang['ListBox_Disallow_MaxLevelReached'];
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
        if(IsElementBuyable($CurrentUser, $CurrentPlanet, $ElementID, false) === false)
        {
            $HasResources = false;
            if($Queue['lenght'] == 0)
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

        if($MaxLevelReached)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_MaxLevelReached'];
        }
        else if(!$HasResources)
        {
            $BlockReason[] = $_Lang['ListBox_Disallow_NoResources'];
        }
        if(!$TechLevelOK)
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
        if($LabInQueue === true)
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

        if($HideButton_Build)
        {
            $ElementParser['HideBuildButton'] = 'hide';
        }
        if($HideButton_Build OR $HideButton_QuickBuild)
        {
            $ElementParser['HideQuickBuildButton'] = 'hide';
        }

        if(!empty($ElementParser['AdditionalNfo']))
        {
            $ElementParser['AdditionalNfo'] = implode('', $ElementParser['AdditionalNfo']);
        }
        $ElementParser['ElementRequirementsHeadline'] = parsetemplate($ElementParser['ElementRequirementsHeadline'], $ElementParser);
        $StructuresList[] = parsetemplate($TPL['list_element'], $ElementParser);
        $InfoBoxes[] = parsetemplate($TPL['infobox_body'], $ElementParser);
    }

    if(!empty($LevelModifiers))
    {
        foreach($LevelModifiers as $ElementID => $Modifier)
        {
            $CurrentUser[$_Vars_GameElements[$ElementID]] += $Modifier;
        }
    }
    $CurrentPlanet['metal'] += (isset($LockResources['metal']) ? $LockResources['metal'] : 0);
    $CurrentPlanet['crystal'] += (isset($LockResources['crystal']) ? $LockResources['crystal'] : 0);
    $CurrentPlanet['deuterium'] += (isset($LockResources['deuterium']) ? $LockResources['deuterium'] : 0);

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
    $Parse['Insert_Overview_LabsConnected'] = prettyNumber($OtherLabs_ConnectedLabs);
    $Parse['Insert_Overview_TotalLabsCount'] = prettyNumber($OtherLabs_LabsCount);
    $Parse['Insert_Overview_LabPower'] = prettyNumber($OtherLabs_ConnectedLabsLevel);
    $Parse['Insert_Overview_LabPowerTotal'] = prettyNumber($OtherLabs_TotalLabsLevel);

    $Page = parsetemplate(gettemplate('buildings_compact_body_lab'), $Parse);

    display($Page, $_Lang['Research']);
}

?>
