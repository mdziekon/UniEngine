<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath . 'common.php');
include_once($_EnginePath . 'modules/info/_includes.php');
include_once($_EnginePath . 'includes/functions/GetMissileRange.php');
include_once($_EnginePath . 'includes/functions/GetPhalanxRange.php');

use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Includes\Helpers\World;
use UniEngine\Engine\Modules\Info;

loggedCheck();

// Inner Functions
function ShowProductionTable ($CurrentUser, $CurrentPlanet, $elementId) {
    $IMPULSE_DRIVE_ELEMENTID = 117;
    $PHALANX_ELEMENTID = 42;

    if (World\Elements\isProductionRelated($elementId)) {
        return Info\Components\ResourceProductionTable\render([
            'elementId' => $elementId,
            'planet' => &$CurrentPlanet,
            'user' => &$CurrentUser,
            'currentTimestamp' => time(),
        ])['componentHTML'];
    }
    if (World\Elements\isStorageStructure($elementId)) {
        return Info\Components\ResourceStorageTable\render([
            'elementId' => $elementId,
            'planet' => &$CurrentPlanet,
        ])['componentHTML'];
    }
    if ($elementId == $IMPULSE_DRIVE_ELEMENTID) {
        return Info\Components\MissileRangeTable\render([
            'elementId' => $elementId,
            'planet' => &$CurrentPlanet,
            'user' => &$CurrentUser,
        ])['componentHTML'];
    }
    if ($elementId == $PHALANX_ELEMENTID) {
        return Info\Components\PhalanxRangeTable\render([
            'elementId' => $elementId,
            'planet' => &$CurrentPlanet,
            'user' => &$CurrentUser,
        ])['componentHTML'];
    }
}
// End of Internal functions

$BuildID = $_GET['gid'];

includeLang('infos');
includeLang('worldElements.detailed');

$GateTPL = '';
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
    $PageTPL = gettemplate('info_buildings_table');
    $TPL_Production_Header = gettemplate('infos_production_header_storages');
}
else if($BuildID >= 14 AND $BuildID <= 32)
{
    // Other Buildings
    $PageTPL = gettemplate('info_buildings_general');
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
}
else if($BuildID == 44)
{
    // Rocket Silo
    $PageTPL = gettemplate('info_buildings_general');
    $Show_DestroyMissiles = true;
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
    }
    else
    {
        $PageTPL = gettemplate('info_buildings_general');
    }
}
else if($BuildID == 43)
{
    // Teleport
    $PageTPL = gettemplate('info_buildings_general');
    $GateTPL = gettemplate('gate_fleet_table');
}
else if($BuildID == 50)
{
    // Quantum Gate
    $PageTPL = gettemplate('info_buildings_general');
    if($_Planet['quantumgate'] > 0)
    {
        include_once("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");

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
    $parse['table_data'] = ShowProductionTable($_User, $_Planet, $BuildID);
}

$page = parsetemplate($PageTPL, $parse);

if(!isOnVacation($_User))
{
    // Missile Destroy Function
    if (isset($Show_DestroyMissiles)) {
        $page .= Info\Components\MissileDestructionSection\render([
            'elementId' => $BuildID,
            'planet' => &$_Planet,
            'user' => &$_User,
        ])['componentHTML'];
    }

    // Teleport Functions
    if ($GateTPL != '') {
        $page .= Info\Components\TeleportSection\render([
            'elementId' => $BuildID,
            'planet' => &$_Planet,
            'user' => &$_User,
        ])['componentHTML'];
    }

    // Building Destroy Function
    $page .= Info\Components\BuildingDestructionSection\render([
        'elementId' => $BuildID,
        'planet' => &$_Planet,
        'user' => &$_User,
    ])['componentHTML'];
}

display($page, $_Lang['nfo_page_title'], false);

?>
