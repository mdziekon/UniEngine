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

$QUANTUMGATE_ELEMENTID = 50;

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

$isValidElement = (
    World\Elements\isStructure($BuildID) ||
    World\Elements\isTechnology($BuildID) ||
    World\Elements\isConstructibleInHangar($BuildID)
);

if (!$isValidElement) {
    message($_Lang['Infos_BadElementID'], $_Lang['nfo_page_title']);
}

$isCurrentlyOnPlanet = ($_Planet['planet_type'] == 1);

if (World\Elements\isStructure($BuildID)) {
    $parse['element_typ'] = $_Lang['tech'][0];
} else if (World\Elements\isTechnology($BuildID)) {
    $parse['element_typ'] = $_Lang['tech'][100];
} else if (World\Elements\isShip($BuildID)) {
    $parse['element_typ'] = $_Lang['tech'][200];
} else if (
    World\Elements\isDefenseSystem($BuildID) ||
    World\Elements\isMissile($BuildID)
) {
    $parse['element_typ'] = $_Lang['tech'][400];
}

if (
    World\Elements\isPlanetaryMine($BuildID) &&
    $isCurrentlyOnPlanet
) {
    $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
        'elementId' => $BuildID,
        'user' => &$_User,
        'planet' => &$_Planet,
        'currentTimestamp' => time(),
    ])['componentHTML'];
}

if (
    $BuildID == 4 &&
    $isCurrentlyOnPlanet
) {
    $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
        'elementId' => $BuildID,
        'user' => &$_User,
        'planet' => &$_Planet,
        'currentTimestamp' => time(),
    ])['componentHTML'];
}

if (
    $BuildID == 12 &&
    $isCurrentlyOnPlanet
) {
    $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
        'elementId' => $BuildID,
        'user' => &$_User,
        'planet' => &$_Planet,
        'currentTimestamp' => time(),
    ])['componentHTML'];
}

if (World\Elements\isStorageStructure($BuildID)) {
    $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
        'elementId' => $BuildID,
        'user' => &$_User,
        'planet' => &$_Planet,
        'currentTimestamp' => time(),
    ])['componentHTML'];
}

if (
    $BuildID == 42 &&
    !$isCurrentlyOnPlanet
) {
    $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
        'elementId' => $BuildID,
        'user' => &$_User,
        'planet' => &$_Planet,
        'currentTimestamp' => time(),
    ])['componentHTML'];
}

if ($BuildID == 117) {
    $parse['component_ProductionTable'] = Info\Components\ProductionTable\render([
        'elementId' => $BuildID,
        'user' => &$_User,
        'planet' => &$_Planet,
        'currentTimestamp' => time(),
    ])['componentHTML'];
}

if (World\Elements\isConstructibleInHangar($BuildID)) {
    $isShip = World\Elements\isShip($BuildID);
    $isDefenseSystem = World\Elements\isDefenseSystem($BuildID);

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

$additionalControls = [];

if (!isOnVacation($_User)) {
    if ($BuildID == 44) {
        $additionalControls[] = Info\Components\MissileDestructionSection\render([
            'elementId' => $BuildID,
            'planet' => &$_Planet,
            'user' => &$_User,
        ])['componentHTML'];
    }

    if ($BuildID == 43) {
        $additionalControls[] = Info\Components\TeleportSection\render([
            'elementId' => $BuildID,
            'planet' => &$_Planet,
            'user' => &$_User,
        ])['componentHTML'];
    }

    if ($BuildID == $QUANTUMGATE_ELEMENTID) {
        $elementLevel = World\Elements\getElementCurrentLevel($QUANTUMGATE_ELEMENTID, $_Planet, $_User);

        if ($elementLevel > 0) {
            $parse['AdditionalInfo'] = Info\Components\QuantumGateState\render([
                'planet' => &$_Planet,
                'currentTimestamp' => time(),
            ])['componentHTML'];
        }
    }

    $additionalControls[] = Info\Components\BuildingDestructionSection\render([
        'elementId' => $BuildID,
        'planet' => &$_Planet,
        'user' => &$_User,
    ])['componentHTML'];
}

$parse['component_AdditionalControls'] = implode('', $additionalControls);

$PageTPL = gettemplate('info_element');
$page = parsetemplate($PageTPL, $parse);

display($page, $_Lang['nfo_page_title'], false);

?>
