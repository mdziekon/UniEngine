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

$BuildID = $_GET['gid'];

includeLang('infos');
includeLang('worldElements.detailed');

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
        $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
            'elementId' => $BuildID,
            'user' => &$_User,
            'planet' => &$_Planet,
            'currentTimestamp' => time(),
        ])['componentHTML'];
    }
}
else if($BuildID == 4)
{
    // Solar Power Station
    if($_Planet['planet_type'] == 1)
    {
        $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
            'elementId' => $BuildID,
            'user' => &$_User,
            'planet' => &$_Planet,
            'currentTimestamp' => time(),
        ])['componentHTML'];
    }
}
else if($BuildID == 12)
{
    // Fusion Power Station
    if($_Planet['planet_type'] == 1)
    {
        $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
            'elementId' => $BuildID,
            'user' => &$_User,
            'planet' => &$_Planet,
            'currentTimestamp' => time(),
        ])['componentHTML'];
    }
}
else if(in_array($BuildID, $_Vars_ElementCategories['storages']))
{
    // Storages
    $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
        'elementId' => $BuildID,
        'user' => &$_User,
        'planet' => &$_Planet,
        'currentTimestamp' => time(),
    ])['componentHTML'];
}
else if($BuildID >= 14 AND $BuildID <= 32)
{
    // Other Buildings
}
else if($BuildID == 33)
{
    // Terraformer
}
else if($BuildID == 34)
{
    // Ally Deposit
}
else if($BuildID == 44)
{
    // Rocket Silo
}
else if($BuildID == 41)
{
    // Moon Station
}
else if($BuildID == 42)
{
    // Phalanx
    if($_Planet['planet_type'] == 3)
    {
        $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
            'elementId' => $BuildID,
            'user' => &$_User,
            'planet' => &$_Planet,
            'currentTimestamp' => time(),
        ])['componentHTML'];
    }
}
else if($BuildID == 43)
{
    // Teleport
}
else if($BuildID == 50)
{
    // Quantum Gate
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

        $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
            'elementId' => $BuildID,
            'user' => &$_User,
            'planet' => &$_Planet,
            'currentTimestamp' => time(),
        ])['componentHTML'];
    }
} else if (World\Elements\isConstructibleInHangar($BuildID)) {
    // Ships & Defense
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

$PageTPL = gettemplate('info_element');

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
