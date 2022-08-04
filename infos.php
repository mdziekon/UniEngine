<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');
include_once($_EnginePath . 'modules/info/_includes.php');

use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Modules\Info;

loggedCheck();

$ChronoAppletIncluded = false;

// Inner Functions
function ShowProductionTable($CurrentUser, $CurrentPlanet, $BuildID, $Template)
{
    global $_Vars_GameElements, $_Vars_ElementCategories, $_GameConfig, $_EnginePath;

    include($_EnginePath.'includes/functions/GetMissileRange.php');

    if (in_array($BuildID, $_Vars_ElementCategories['prod'])) {
        return Info\Components\ResourceProductionTable\render([
            'elementId' => $BuildID,
            'planet' => &$CurrentPlanet,
            'user' => &$CurrentUser,
            'currentTimestamp' => time(),
        ])['componentHTML'];
    }
    if (in_array($BuildID, $_Vars_ElementCategories['storages'])) {
        return Info\Components\ResourceStorageTable\render([
            'elementId' => $BuildID,
            'planet' => &$CurrentPlanet,
        ])['componentHTML'];
    }
    if ($BuildID == 117) {
        return Info\Components\MissileRangeTable\render([
            'elementId' => $BuildID,
            'planet' => &$CurrentPlanet,
            'user' => &$CurrentUser,
        ])['componentHTML'];
    }

    if(!in_array($BuildID, $_Vars_ElementCategories['tech']))
    {
        $CurrentLevel = $CurrentPlanet[$_Vars_GameElements[$BuildID]];
    }
    else
    {
        $CurrentLevel = $CurrentUser[$_Vars_GameElements[$BuildID]];
    }

    $BuildStartLvl = $CurrentLevel - 3;
    if($BuildStartLvl < 0)
    {
        $BuildStartLvl = 0;
    }
    $Table = '';
    $BuildEndLevel = $BuildStartLvl + 10;
    for($BuildLevel = $BuildStartLvl; $BuildLevel < $BuildEndLevel; $BuildLevel += 1)
    {
        $bloc = array();
        if($CurrentLevel == $BuildLevel)
        {
            $bloc['build_lvl'] = "<span class=\"red\">{$BuildLevel}</span>";
            $bloc['IsCurrent'] = ' class="thisLevel"';
        }
        else
        {
            $bloc['build_lvl'] = $BuildLevel;
        }

        if($BuildID == 42)
        {
            if($BuildLevel == 0)
            {
                $bloc['build_range'] = '0';
            }
            else
            {
                $bloc['build_range'] = prettyNumber(($BuildLevel * $BuildLevel) - 1);
            }
        }

        $Table .= parsetemplate($Template, $bloc);
    }

    return $Table;
}
// End of Internal functions

$BuildID = $_GET['gid'];

includeLang('infos');
includeLang('worldElements.detailed');

$GateTPL = '';
$DestroyTPL = '';
$TPL_Production_Header = '';

$parse = $_Lang;
$parse['Insert_AllowPrettyInputBox'] = ($_User['settings_useprettyinputbox'] == 1 ? 'true' : 'false');
$parse['skinpath'] = $_SkinPath;
$parse['name'] = $_Lang['tech'][$BuildID];
$parse['image'] = $BuildID;
$parse['description'] = (
    !empty($_Lang['WorldElements_Detailed'][$BuildID]['description_alt']) ?
    $_Lang['WorldElements_Detailed'][$BuildID]['description_alt'] :
    (
        $_Lang['WorldElements_Detailed'][$BuildID]['description_short'] .
        (
            !empty($_Lang['WorldElements_Detailed'][$BuildID]['description_extra']) ?
            ('<br/><br/>' . $_Lang['WorldElements_Detailed'][$BuildID]['description_extra']) :
            ''
        )
    )
);
$parse['element_typ'] = $_Lang['tech'][0];

if($BuildID >= 1 AND $BuildID <= 3)
{
    // Mines
    $DestroyTPL = gettemplate('info_buildings_destroy');
    if($_Planet['planet_type'] == 1)
    {
        $PageTPL = gettemplate('info_buildings_table');
        $TPL_Production_Header = gettemplate('infos_production_header_mines');
    }
    else
    {
        $PageTPL = gettemplate('info_buildings_general');
    }
}
else if($BuildID == 4)
{
    // Solar Power Station
    $DestroyTPL = gettemplate('info_buildings_destroy');
    if($_Planet['planet_type'] == 1)
    {
        $PageTPL = gettemplate('info_buildings_table');
        $TPL_Production_Header = gettemplate('infos_production_header_solarplant');
    }
    else
    {
        $PageTPL = gettemplate('info_buildings_general');
    }
}
else if($BuildID == 12)
{
    // Fusion Power Station
    $DestroyTPL = gettemplate('info_buildings_destroy');
    if($_Planet['planet_type'] == 1)
    {
        $PageTPL = gettemplate('info_buildings_table');
        $TPL_Production_Header = gettemplate('infos_production_header_fusionplant');
    }
    else
    {
        $PageTPL = gettemplate('info_buildings_general');
    }
}
else if(in_array($BuildID, $_Vars_ElementCategories['storages']))
{
    // Storages
    $DestroyTPL = gettemplate('info_buildings_destroy');

    $PageTPL = gettemplate('info_buildings_table');
    $TPL_Production_Header = gettemplate('infos_production_header_storages');
}
else if($BuildID >= 14 AND $BuildID <= 32)
{
    // Other Buildings
    $PageTPL = gettemplate('info_buildings_general');
    $DestroyTPL = gettemplate('info_buildings_destroy');
}
else if($BuildID == 33)
{
    // Terraformer
    $PageTPL = gettemplate('info_buildings_general');
}
else if($BuildID == 34)
{
    // Ally Deposit
    $PageTPL = gettemplate('info_buildings_general');
    $DestroyTPL = gettemplate('info_buildings_destroy');
}
else if($BuildID == 44)
{
    // Rocket Silo
    $PageTPL = gettemplate('info_buildings_general');
    $Show_DestroyMissiles = true;
    $DestroyTPL = gettemplate('info_buildings_destroy');
}
else if($BuildID == 41)
{
    // Moon Station
    $PageTPL = gettemplate('info_buildings_general');
}
else if($BuildID == 42)
{
    // Phalanx
    if($_Planet['planet_type'] == 3)
    {
        $PageTPL = gettemplate('info_buildings_table');
        $TPL_Production_Header = gettemplate('infos_production_header_phalanx');
        $TPL_Production_Rows = gettemplate('infos_production_rows_phalanx');
    }
    else
    {
        $PageTPL = gettemplate('info_buildings_general');
    }
    $DestroyTPL = gettemplate('info_buildings_destroy');
}
else if($BuildID == 43)
{
    // Teleport
    $PageTPL = gettemplate('info_buildings_general');
    $GateTPL = gettemplate('gate_fleet_table');
    $DestroyTPL = gettemplate('info_buildings_destroy');
}
else if($BuildID == 50)
{
    // Quantum Gate
    $PageTPL = gettemplate('info_buildings_general');
    if($_Planet['quantumgate'] > 0)
    {
        if(!$ChronoAppletIncluded)
        {
            include("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");
            $ChronoAppletIncluded = true;
        }
        $NextUseTimestamp = ($_Planet['quantumgate_lastuse'] + (QUANTUMGATE_INTERVAL_HOURS * TIME_HOUR)) - time();
        if($NextUseTimestamp < 0)
        {
            $NextUseTimestamp = 0;
        }
        if($NextUseTimestamp == 0)
        {
            $QuantumGate .= '<span class="lime">'.$_Lang['GateReadyToUse'].'</span>';
        }
        else
        {
            $QuantumGate .= InsertJavaScriptChronoApplet('quantum', '0', $NextUseTimestamp);
            $QuantumGate .= '<span class="orange">'.$_Lang['GateReadyToUseIn'].':</span><br/><span id="bxxquantum0">'.(pretty_time($NextUseTimestamp, true)).'</span>';
        }
        $parse['AdditionalInfo'] = '<tr><th class="c"><br/>'.$QuantumGate.'<br/>&nbsp;</th></tr>';
    }
}
else if(in_array($BuildID, $_Vars_ElementCategories['tech']))
{
    // Technologies
    $parse['element_typ'] = $_Lang['tech'][100];
    if($BuildID == 117)
    {
        $PageTPL = gettemplate('info_buildings_table');
        $TPL_Production_Header = gettemplate('infos_production_header_missiles');
    }
    else
    {
        $PageTPL = gettemplate('info_buildings_general');
    }
}
else if(in_array($BuildID, $_Vars_ElementCategories['fleet']) OR in_array($BuildID, $_Vars_ElementCategories['defense']))
{
    // Ships & Defense
    $InShips = (in_array($BuildID, $_Vars_ElementCategories['fleet']) ? true : false);

    if($InShips)
    {
        $PageTPL = gettemplate('info_buildings_fleet');
        $parse['element_typ'] = $_Lang['tech'][200];
    }
    else
    {
        $PageTPL = gettemplate('info_buildings_defense');
        $parse['element_typ'] = $_Lang['tech'][400];
    }

    if($InShips OR !in_array($BuildID, $_Vars_ElementCategories['rockets']))
    {
        $parse['rf_info_to'] = Info\Components\RapidFireAgainstList\render([
            'elementId' => $BuildID,
        ])['componentHTML'];
        $parse['rf_info_fr'] = Info\Components\RapidFireFromList\render([
            'elementId' => $BuildID,
        ])['componentHTML'];
    }

    $ThisElement_Hull = ($_Vars_Prices[$BuildID]['metal'] + $_Vars_Prices[$BuildID]['crystal']);
    $ThisElement_Sheld = $_Vars_CombatData[$BuildID]['shield'];
    $ThisElement_Force = $_Vars_CombatData[$BuildID]['attack'];

    $ThisElement_Modifiers_Hull = (0.1 * $_User[$_Vars_GameElements[111]]);
    $ThisElement_Modifiers_Shield = (0.1 * $_User[$_Vars_GameElements[110]]);
    $ThisElement_Modifiers_Force = (0.1 * $_User[$_Vars_GameElements[109]]);
    if(!empty($_Vars_CombatUpgrades[$BuildID]))
    {
        foreach($_Vars_CombatUpgrades[$BuildID] as $UpTech => $ReqLevel)
        {
            $TechAvailable = $_User[$_Vars_GameElements[$UpTech]];
            if($TechAvailable > $ReqLevel)
            {
                $ThisElement_Modifiers_Force += ($TechAvailable - $ReqLevel) * 0.05;
            }
        }
    }

    $parse['Insert_Hull_Modifier'] = $ThisElement_Modifiers_Hull * 100;
    $parse['Insert_Shield_Modifier'] = $ThisElement_Modifiers_Shield * 100;
    $parse['Insert_Force_Modifier'] = $ThisElement_Modifiers_Force * 100;

    $parse['Insert_Hull_Base'] = prettyNumber($ThisElement_Hull);
    $parse['Insert_Hull_Modified'] = prettyNumber($ThisElement_Hull * (1 + $ThisElement_Modifiers_Hull));
    $parse['Insert_Shield_Base'] = prettyNumber($ThisElement_Sheld);
    $parse['Insert_Shield_Modified'] = prettyNumber($ThisElement_Sheld * (1 + $ThisElement_Modifiers_Shield));
    $parse['Insert_Force_Base'] = prettyNumber($ThisElement_Force);
    $parse['Insert_Force_Modified'] = prettyNumber($ThisElement_Force * (1 + $ThisElement_Modifiers_Force));

    if(!empty($_Vars_Prices[$BuildID]['weapons']))
    {
        foreach($_Vars_Prices[$BuildID]['weapons'] as $ThisWeaponType)
        {
            $ThisWeaponString = $_Lang['weaponTypes'][$ThisWeaponType];
            if(!empty($_Vars_CombatUpgrades[$BuildID][$ThisWeaponType]))
            {
                $ThisWeaponString = '<a href="?gid='.$ThisWeaponType.'">'.$ThisWeaponString.' ('.$_Vars_CombatUpgrades[$BuildID][$ThisWeaponType].')</a>';
            }

            $parse['Insert_WeaponType'][] = $ThisWeaponString;
        }
        $parse['Insert_WeaponType'] = implode(', ', $parse['Insert_WeaponType']);
    }
    else
    {
        $parse['Insert_WeaponType'] = $_Lang['weaponTypes'][0];
    }

    if ($InShips) {
        $thisShipsStorageCapacity = getShipsStorageCapacity($BuildID);
        $thisShipsUsedEngine = getShipsUsedEngineData($BuildID, $_User);
        $thisShipsCurrentSpeed = getShipsCurrentSpeed($BuildID, $_User);
        $thisShipsCurrentSpeedModifier = (
            $thisShipsUsedEngine['engineIdx'] === -1 ?
            0 :
            Users\getUsersEngineSpeedTechModifier($thisShipsUsedEngine['tech'], $user)
        );
        $thisShipsEngines = getShipsEngines($BuildID);

        if (empty($thisShipsEngines)) {
            $thisShipsEngines[] = [
                'speed' => 0,
                'consumption' => 0
            ];
        }

        $hasUpgradableEngine = (count($thisShipsEngines) > 1);

        // Sort engines in reverse order, so the first one is the "slowest"
        // according to the ordering assumption.
        krsort($thisShipsEngines);

        foreach ($thisShipsEngines as $engineIdx => $engineData) {
            $engineData['speed'] = prettyNumber($engineData['speed']);
            $engineData['consumption'] = prettyNumber($engineData['consumption']);

            if (
                $hasUpgradableEngine &&
                $engineIdx === $thisShipsUsedEngine['engineIdx']
            ) {
                $engineData['speed'] = ('<b class="skyblue">' . $engineData['speed'] . '</b>');
                $engineData['consumption'] = ('<b class="skyblue">' . $engineData['consumption'] . '</b>');
            }

            $parse['Insert_Speed_Base'][] = $engineData['speed'];
            $parse['Insert_Fuel_Base'][] = $engineData['consumption'];
        }

        $parse['Insert_Speed_Base'] = implode(' / ', $parse['Insert_Speed_Base']);
        $parse['Insert_Fuel_Base'] = implode(' / ', $parse['Insert_Fuel_Base']);
        $parse['Insert_Speed_Modified'] = prettyNumber($thisShipsCurrentSpeed);
        $parse['Insert_Speed_Modifier'] = prettyNumber($thisShipsCurrentSpeedModifier * 100);
        $parse['Insert_Storage_Base'] = prettyNumber($thisShipsStorageCapacity);
    }
}
else
{
    message($_Lang['Infos_BadElementID'], $_Lang['nfo_page_title']);
}

if($TPL_Production_Header != '')
{
    $parse['table_head'] = parsetemplate($TPL_Production_Header, $_Lang);
    $parse['table_data'] = ShowProductionTable($_User, $_Planet, $BuildID, $TPL_Production_Rows);
}

$page = parsetemplate($PageTPL, $parse);

if(!isOnVacation($_User))
{
    // Missile Destroy Function
    if(isset($Show_DestroyMissiles))
    {
        if($_Planet[$_Vars_GameElements[$BuildID]] > 0)
        {
            $TPL_DestroyRockets_Body = gettemplate('destroy_rockets_table');
            $TPL_DestroyRockets_Row = gettemplate('destroy_rockets_row');
            $parse['DestroyRockets_Insert_Rows'] = '';
            foreach($_Vars_ElementCategories['rockets'] as $ThisID)
            {
                $parse['DestroyRockets_ID'] = $ThisID;
                $parse['DestroyRockets_Name'] = $_Lang['tech'][$ThisID];
                $parse['DestroyRockets_Count'] = $_Planet[$_Vars_GameElements[$ThisID]];
                $parse['DestroyRockets_PrettyCount'] = prettyNumber($_Planet[$_Vars_GameElements[$ThisID]]);

                $parse['DestroyRockets_Insert_Rows'] .= parsetemplate($TPL_DestroyRockets_Row, $parse);
            }

            $page .= parsetemplate($TPL_DestroyRockets_Body, $parse);
        }
    }

    // Teleport Functions
    if($GateTPL != '')
    {
        if($_Planet[$_Vars_GameElements[$BuildID]] > 0)
        {
            $RestString = GetNextJumpWaitTime($_Planet);
            $parse['gate_start_link'] = "<a href=\"galaxy.php?mode=3&galaxy={$_Planet['galaxy']}&system={$_Planet['system']}&planet={$_Planet['planet']}\">[{$_Planet['galaxy']}:{$_Planet['system']}:{$_Planet['planet']}] {$_Planet['name']}</a>";
            if($RestString['value'] != 0)
            {
                if(!$ChronoAppletIncluded)
                {
                    include("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");
                    $ChronoAppletIncluded = true;
                }
                $parse['gate_time_script'] = InsertJavaScriptChronoApplet('Gate', '1', $RestString['value']);
                $parse['gate_wait_time'] = $_Lang['gate_nextjump_timer'].' <div id="bxxGate1">'.pretty_time($RestString['value'], true).'</div>';
                $parse['PHP_JumpGate_SubmitColor'] = 'orange';
            }
            else
            {
                $parse['PHP_JumpGate_SubmitColor'] = 'lime';
                $parse['gate_time_script'] = '';
                $parse['gate_wait_time'] = '';
                $parse['Gate_HideNextJumpTimer'] = 'style="display: none;"';
            }
            $parse['Gate_HideInfoBox'] = 'style="display: none;"';

            $parse['gate_dest_moons'] = Info\Components\TeleportTargetMoonsList\render([
                'planet' => &$_Planet,
                'user' => &$_User,
            ])['componentHTML'];

            if (empty($parse['gate_dest_moons'])) {
                $parse['Gate_HideInfoBox'] = '';
                $parse['Gate_HideSelector'] = 'style="display: none;"';
                $parse['Gate_HideShips'] = 'style="display: none;"';
                $parse['gate_infobox'][] = $_Lang['gate_nomoonswithtp'];
            }

            $parse['gate_fleet_rows'] = Info\Components\TeleportFleetUnitSelectorsList\render([
                'planet' => &$_Planet,
            ])['componentHTML'];

            if (empty($parse['gate_fleet_rows'])) {
                $parse['Gate_HideInfoBox'] = '';
                $parse['Gate_HideShips'] = 'style="display: none;"';
                $parse['gate_infobox'][] = $_Lang['gate_noshipstotp'];
            }

            if(!empty($parse['gate_infobox']))
            {
                $parse['gate_infobox'] = implode('<br/>', $parse['gate_infobox']);
                $parse['Gate_HideInfoBox'] = '';
            }

            $page .= parsetemplate($GateTPL, $parse);
        }
    }

    // Building Destroy Function
    if($DestroyTPL != '')
    {
        if($_Planet[$_Vars_GameElements[$BuildID]] > 0 && (!isset($_Vars_IndestructibleBuildings[$BuildID]) || $_Vars_IndestructibleBuildings[$BuildID] != 1))
        {
            $NeededRessources = GetBuildingPrice($_User, $_Planet, $BuildID, true, true);
            $DestroyTime = GetBuildingTime($_User, $_Planet, $BuildID) / 2;
            $parse['destroyurl'] = 'buildings.php?cmd=destroy&building='.$BuildID;
            $parse['levelvalue'] = $_Planet[$_Vars_GameElements[$BuildID]];
            $parse['nfo_metal'] = $_Lang['Metal'];
            $parse['nfo_crysta'] = $_Lang['Crystal'];
            $parse['nfo_deuter'] = $_Lang['Deuterium'];
            $parse['metal'] = prettyNumber($NeededRessources['metal']);
            $parse['crystal'] = prettyNumber($NeededRessources['crystal']);
            $parse['deuterium'] = prettyNumber($NeededRessources['deuterium']);
            if($NeededRessources['metal'] > $_Planet['metal'])
            {
                $parse['Met_Color'] = 'red';
            }
            else
            {
                $parse['Met_Color'] = 'lime';
            }
            if($NeededRessources['crystal'] > $_Planet['crystal'])
            {
                $parse['Cry_Color'] = 'red';
            }
            else
            {
                $parse['Cry_Color'] = 'lime';
            }
            if($NeededRessources['deuterium'] > $_Planet['deuterium'])
            {
                $parse['Deu_Color'] = 'red';
            }
            else
            {
                $parse['Deu_Color'] = 'lime';
            }
            $parse['destroytime'] = pretty_time($DestroyTime);
            $page .= parsetemplate($DestroyTPL, $parse);
        }
    }
}

display($page, $_Lang['nfo_page_title'], false);

?>
