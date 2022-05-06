<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');
include($_EnginePath . 'modules/flightControl/_includes.php');

use UniEngine\Engine\Includes\Helpers\Common\Collections;
use UniEngine\Engine\Includes\Helpers\World\Elements;
use UniEngine\Engine\Modules\FlightControl;

loggedCheck();

if(!$_Planet)
{
    message($_Lang['fl_noplanetrow'], $_Lang['fl_error']);
}

include($_EnginePath.'/includes/functions/InsertJavaScriptChronoApplet.php');

$Now = time();
includeLang('fleet');
$BodyTPL                = gettemplate('fleet_body');

$_Lang['ShipsRow'] = '';
$_Lang['FlyingFleetsRows'] = '';

$Hide = ' class="hide"';

if($_User['settings_useprettyinputbox'] == 1)
{
    $_Lang['P_AllowPrettyInputBox'] = 'true';
}
else
{
    $_Lang['P_AllowPrettyInputBox'] = 'false';
}
$_Lang['InsertACSUsers'] = 'new Object()';
$_Lang['InsertACSUsersMax'] = MAX_ACS_JOINED_PLAYERS;

// Show info boxes
$_Lang['P_SFBInfobox'] = FlightControl\Components\SmartFleetBlockadeInfoBox\render()['componentHTML'];
$_Lang['ComponentHTML_RetreatInfoBox'] = FlightControl\Components\RetreatInfoBox\render([
    'isVisible' => isset($_GET['ret']),
    'eventCode' => $_GET['m'],
])['componentHTML'];

$fleetsInFlightCounters = FlightControl\Utils\Helpers\getFleetsInFlightCounters([
    'userId' => $_User['id'],
]);

$FlyingFleetsCount = $fleetsInFlightCounters['allFleetsInFlight'];
$FlyingExpeditions = $fleetsInFlightCounters['expeditionsInFlight'];

$_Lang['P_MaxFleetSlots'] = FlightControl\Utils\Helpers\getUserFleetSlotsCount([
    'user' => $_User,
    'timestamp' => $Now,
]);
$_Lang['P_MaxExpedSlots'] = FlightControl\Utils\Helpers\getUserExpeditionSlotsCount([
    'user' => $_User,
]);
$_Lang['P_FlyingFleetsCount']    = (string)($FlyingFleetsCount);
$_Lang['P_FlyingExpeditions']    = (string)($FlyingExpeditions);
$_Lang['P_Expeditions_isHidden_style'] = (
    isFeatureEnabled(FeatureType::Expeditions) ?
    '' :
    'display: none;'
);

// TODO: refactor and add validation (?)
if (
    (
        isset($_GET['joinacs'])
    ) ||
    (
        isset($_POST['getacsdata']) &&
        $_POST['getacsdata'] > 0
    )
)
{
    $_Lang['SetJoiningACSID'] = (
        isset($_GET['joinacs']) ?
            $_GET['joinacs'] :
            $_POST['getacsdata']
    );
}

$flightsList = FlightControl\Components\FlightsList\render([
    'userId' => $_User['id'],
    'currentTimestamp' => $Now,
])['componentHTML'];

$_Lang['FlyingFleetsRows'] .= $flightsList['elementsList'];
$_Lang['ChronoAppletsScripts'] = $flightsList['chronoApplets'];

$_Lang['P_HideNoFreeSlots'] = $Hide;
if(empty($_Lang['FlyingFleetsRows']))
{
    $_Lang['FlyingFleetsRows'] = '<tr><th colspan="8">-</th></tr>';
}
else
{
    if($FlyingFleetsCount >= $_Lang['P_MaxFleetSlots'])
    {
        $_Lang['P_HideNoFreeSlots'] = '';
    }
}

$newUnionEntry = null;

if (
    isset($_POST['acsmanage']) &&
    $_POST['acsmanage'] == 'open'
) {
    $unionManagement = FlightControl\Components\UnionManagement\render([
        'unionOwner' => $_User,
        'currentTimestamp' => $Now,
        'input' => $_POST,
    ]);

    $_Lang['Insert_ACSForm'] = $unionManagement['componentHTML'];
    $newUnionEntry = $unionManagement['extraPayload']['newUnionEntry'];
}

if (
    $newUnionEntry !== null &&
    strstr($_Lang['FlyingFleetsRows'], 'AddACSJoin_') !== false
) {
    $joinInputHTML = "<input type=\"radio\" value=\"{$newUnionEntry['id']}\" class=\"setACS_ID pad5\" name=\"acs_select\"><br/>{$_Lang['fl_acs_joinnow']}";

    $_Lang['FlyingFleetsRows'] = str_replace(
        '{AddACSJoin_'.$newUnionEntry['main_fleet_id'].'}',
        $joinInputHTML,
        $_Lang['FlyingFleetsRows']
    );
}

$_Lang['FlyingFleetsRows'] = preg_replace('#\{AddACSJoin\_[0-9]+\}#si', '', $_Lang['FlyingFleetsRows']);

if(!isPro())
{
    // Don't Allow to use this function to NonPro Players
    $_GET['quickres'] = 0;
}

$preselectedCargoShips = [];

if (
    isset($_GET['quickres']) &&
    $_GET['quickres'] == 1
) {
    $_Lang['P_SetQuickRes'] = '1';

    $resourcesToLoad = (
        floor($_Planet['metal']) +
        floor($_Planet['crystal']) +
        floor($_Planet['deuterium'])
    );

    $transportShipIds = $_Vars_ElementCategories['units']['transport'];

    usort($transportShipIds, function ($leftShipId, $rightShipId) {
        return (getShipsStorageCapacity($leftShipId) < getShipsStorageCapacity($rightShipId));
    });

    foreach ($transportShipIds as $shipId) {
        $shipCapacity = getShipsStorageCapacity($shipId);

        $shipsNeeded = ceil($resourcesToLoad / $shipCapacity);
        $shipsAvailable = Elements\getElementCurrentCount($shipId, $_Planet, $_User);
        $shipsToUse = keepInRange($shipsNeeded, 0, $shipsAvailable);

        $preselectedCargoShips[$shipId] = $shipsToUse;

        $resourcesToLoad -= ($shipsToUse * $shipCapacity);

        if ($resourcesToLoad <= 0) {
            break;
        }
    }
} else {
    $_Lang['P_SetQuickRes'] = '0';
}

$gobackFleet = [];

if (
    isset($_POST['gobackUsed']) &&
    !empty($_POST['FleetArray'])
) {
    $gobackFleet = String2Array($_POST['FleetArray']);
    $gobackFleet = object_map($gobackFleet, function ($shipCount, $shipId) {
        if (!Elements\isShip($shipId)) {
            return [ null, $shipId ];
        }

        return [ $shipCount, $shipId ];
    });
    $gobackFleet = Collections\compact($gobackFleet);
}

$shipsJSData = FlightControl\Utils\Factories\createPlanetShipsJSObject([
    'planet' => $_Planet,
    'user' => $_User,
]);
$_Lang['Insert_ShipsData'] = json_encode($shipsJSData);
$_Lang['ShipsRow'] = FlightControl\Components\AvailableShipsList\render([
    'planet' => $_Planet,
    'user' => $_User,
    'preselectedShips' => (
        !empty($gobackFleet) ?
            $gobackFleet :
            $preselectedCargoShips
    ),
])['componentHTML'];

$_Lang['P_HideNoSlotsInfo'] = $Hide;
$_Lang['P_HideSendShips'] = $Hide;
$_Lang['P_HideNoShipsInfo'] = $Hide;
if(!empty($_Lang['ShipsRow']))
{
    if($FlyingFleetsCount >= $_Lang['P_MaxFleetSlots'])
    {
        $_Lang['P_HideNoSlotsInfo'] = '';
    }
    else
    {
        $_Lang['P_HideSendShips'] = '';
    }
}
else
{
    $_Lang['P_HideNoShipsInfo'] = '';
}

if(isset($_POST['gobackUsed']))
{
    $GoBackVars = array
    (
        'speed' => $_POST['speed'],
    );
    if(!empty($_POST['gobackVars']))
    {
        $_Lang['P_GoBackVars'] = json_decode(base64_decode($_POST['gobackVars']), true);
        if((array)$_Lang['P_GoBackVars'] === $_Lang['P_GoBackVars'])
        {
            $GoBackVars = array_merge($_Lang['P_GoBackVars'], $GoBackVars);
        }
    }

    $_Lang['SetJoiningACSID'] = (isset($_POST['getacsdata']) ? $_POST['getacsdata'] : null);
    $_Lang['P_Galaxy'] = (isset($_POST['galaxy']) ? $_POST['galaxy'] : null);
    $_Lang['P_System'] = (isset($_POST['system']) ? $_POST['system'] : null);
    $_Lang['P_Planet'] = (isset($_POST['planet']) ? $_POST['planet'] : null);
    $_Lang['P_PlType'] = (isset($_POST['planettype']) ? $_POST['planettype'] : null);
    $_Lang['P_Mission'] = (isset($_POST['target_mission']) ? $_POST['target_mission'] : null);
    $_Lang['P_SetQuickRes'] = (isset($_POST['quickres']) ? $_POST['quickres'] : null);
    $_Lang['P_GoBackVars'] = base64_encode(json_encode($GoBackVars));
}
else
{
    $_Lang['P_Galaxy'] = (isset($_GET['galaxy']) ? intval($_GET['galaxy']) : null);
    $_Lang['P_System'] = (isset($_GET['system']) ? intval($_GET['system']) : null);
    $_Lang['P_Planet'] = (isset($_GET['planet']) ? intval($_GET['planet']) : null);
    $_Lang['P_PlType'] = (isset($_GET['planettype']) ? intval($_GET['planettype']) : null);
    $_Lang['P_Mission'] = (isset($_GET['target_mission']) ? intval($_GET['target_mission']) : null);
    if(isset($_GET['quickres']) && $_GET['quickres'] == 1)
    {
        if(!isset($_GET['target_mission']) || $_GET['target_mission'] != 3)
        {
            if($_User['settings_mainPlanetID'] != $_Planet['id'])
            {
                $GetQuickResPlanet = doquery("SELECT `galaxy`, `system`, `planet` FROM {{table}} WHERE `id` = {$_User['settings_mainPlanetID']};", 'planets', true);
            }
            $_Lang['P_Galaxy'] = $GetQuickResPlanet['galaxy'];
            $_Lang['P_System'] = $GetQuickResPlanet['system'];
            $_Lang['P_Planet'] = $GetQuickResPlanet['planet'];
            $_Lang['P_PlType'] = 1;
            $_Lang['P_Mission'] = 3;
        }
    }
}

if(!isPro())
{
    $_Lang['P_HideQuickRes'] = 'hide';
}

$_Lang['P_TotalPlanetResources'] = (string)(floor($_Planet['metal']) + floor($_Planet['crystal']) + floor($_Planet['deuterium']) + 0);
if($_Lang['P_TotalPlanetResources'] == '0')
{
    $_Lang['P_StorageColor'] = 'lime';
}
else
{
    $_Lang['P_StorageColor'] = 'orange';
}

$Page = parsetemplate($BodyTPL, $_Lang);
display($Page, $_Lang['fl_title']);

?>
