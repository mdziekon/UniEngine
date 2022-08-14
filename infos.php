<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath . 'common.php');
include_once($_EnginePath . 'modules/info/_includes.php');
include_once($_EnginePath . 'includes/functions/GetMissileRange.php');
include_once($_EnginePath . 'includes/functions/GetPhalanxRange.php');

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
}
else if($BuildID == 50)
{
    // Quantum Gate
    $PageTPL = gettemplate('info_buildings_general');

    $QUANTUMGATE_ELEMENTID = 50;

    $elementLevel = World\Elements\getElementCurrentLevel($QUANTUMGATE_ELEMENTID, $_Planet, $_User);

    if ($elementLevel > 0) {
        $parse['AdditionalInfo'] = Info\Components\QuantumGateState\render([
            'planet' => &$_Planet,
            'currentTimestamp' => time(),
        ])['componentHTML'];
    }
} else if (World\Elements\isTechnology($BuildID)) {
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
} else if (World\Elements\isConstructibleInHangar($BuildID)) {
    // Ships & Defense
    $PageTPL = gettemplate('info_buildings_unit');

    $isShip = World\Elements\isShip($BuildID);
    $isDefenseSystem = World\Elements\isDefenseSystem($BuildID);

    $parse['element_typ'] = (
        $isShip ?
            $_Lang['tech'][200] :
            $_Lang['tech'][400]
    );
    $parse['component_UnitDetails'] = Info\Components\UnitDetailsTable\render([
        'elementId' => $BuildID,
        'user' => &$_User,
    ])['componentHTML'];

    if (
        $isShip ||
        $isDefenseSystem
    ) {
        $parse['rf_info_to'] = Info\Components\RapidFireAgainstList\render([
            'elementId' => $BuildID,
        ])['componentHTML'];
        $parse['rf_info_fr'] = Info\Components\RapidFireFromList\render([
            'elementId' => $BuildID,
        ])['componentHTML'];
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

if (!isOnVacation($_User)) {
    if ($BuildID == 44) {
        $page .= Info\Components\MissileDestructionSection\render([
            'elementId' => $BuildID,
            'planet' => &$_Planet,
            'user' => &$_User,
        ])['componentHTML'];
    }

    if ($BuildID == 43) {
        $page .= Info\Components\TeleportSection\render([
            'elementId' => $BuildID,
            'planet' => &$_Planet,
            'user' => &$_User,
        ])['componentHTML'];
    }

    $page .= Info\Components\BuildingDestructionSection\render([
        'elementId' => $BuildID,
        'planet' => &$_Planet,
        'user' => &$_User,
    ])['componentHTML'];
}

display($page, $_Lang['nfo_page_title'], false);

?>
