<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

include($_EnginePath . 'modules/flightControl/_includes.php');

use UniEngine\Engine\Modules\Flights;
use UniEngine\Engine\Modules\FlightControl;

loggedCheck();

if((!isset($_POST['sending_fleet']) || $_POST['sending_fleet'] != '1') && (!isset($_POST['fromEnd']) || $_POST['fromEnd'] != '1'))
{
    header('Location: fleet.php');
    safeDie();
}

$_Lang['SelectResources'] = 'false';
$_Lang['SelectQuantumGate'] = 'false';

$setFormValues = [
    'holdingtime' => null,
    'expeditionTime' => null,
];

if(!empty($_POST['gobackVars']))
{
    $_POST['gobackVars'] = json_decode(base64_decode($_POST['gobackVars']), true);
    if(isset($_POST['fromEnd']))
    {
        $_POST['quickres'] = $_POST['gobackVars']['useQuickRes'];
        $_POST['target_mission'] = $_POST['gobackVars']['mission'];
        $_POST['getacsdata'] = (isset($_POST['gobackVars']['acs_id']) ? $_POST['gobackVars']['acs_id'] : null);
        $_POST['FleetArray'] = $_POST['gobackVars']['FleetArray'];
        $_POST['galaxy'] = $_POST['gobackVars']['galaxy'];
        $_POST['system'] = $_POST['gobackVars']['system'];
        $_POST['planet'] = $_POST['gobackVars']['planet'];
        $_POST['planettype'] = $_POST['gobackVars']['planettype'];
        $_POST['speed'] = $_POST['gobackVars']['speed'];
    }
    if(isset($_POST['gobackVars']['holdingtime']))
    {
        $setFormValues['holdingtime'] = $_POST['gobackVars']['holdingtime'];
    }
    if(isset($_POST['gobackVars']['expeditiontime']))
    {
        $setFormValues['expeditionTime'] = $_POST['gobackVars']['expeditiontime'];
    }
    $_Lang['SelectResources'] = json_encode(array
    (
        'resource1' => (isset($_POST['gobackVars']['resource1']) ? $_POST['gobackVars']['resource1'] : null),
        'resource2' => (isset($_POST['gobackVars']['resource2']) ? $_POST['gobackVars']['resource2'] : null),
        'resource3' => (isset($_POST['gobackVars']['resource3']) ? $_POST['gobackVars']['resource3'] : null)
    ));
    if(isset($_POST['gobackVars']['usequantumgate']) && $_POST['gobackVars']['usequantumgate'] == 'on')
    {
        $_Lang['SelectQuantumGate'] = 'true';
    }
}

includeLang('fleet');

$QuantumGateInterval = QUANTUMGATE_INTERVAL_HOURS;
$Now = time();
$ErrorTitle = &$_Lang['fl_error'];
$Hide = ' class="hide"';

$_Lang['MissionSelectors'] = '';

if(MORALE_ENABLED)
{
    Morale_ReCalculate($_User, $Now);
}

// Check, if Target Data are correct
$Target['galaxy'] = (isset($_POST['galaxy']) ? intval($_POST['galaxy']) : null);
$Target['system'] = (isset($_POST['system']) ? intval($_POST['system']) : null);
$Target['planet'] = (isset($_POST['planet']) ? intval($_POST['planet']) : null);
$Target['type'] = (isset($_POST['planettype']) ? intval($_POST['planettype']) : null);

$inputJoinUnionId = intval($_POST['getacsdata']);

if ($inputJoinUnionId > 0) {
    $joinUnionResult = FlightControl\Utils\Helpers\tryJoinUnion([
        'unionId' => $inputJoinUnionId,
        'currentTimestamp' => $Now,
    ]);

    if (!$joinUnionResult['isSuccess']) {
        $errorMessage = FlightControl\Utils\Errors\mapTryJoinUnionErrorToReadableMessage($joinUnionResult['error']);

        message($errorMessage, $ErrorTitle, 'fleet.php', 3);
    }

    $unionData = $joinUnionResult['payload']['unionData'];

    $Target['galaxy'] = $unionData['end_galaxy'];
    $Target['system'] = $unionData['end_system'];
    $Target['planet'] = $unionData['end_planet'];
    $Target['type'] = $unionData['end_type'];
}

if($Target['galaxy'] == $_Planet['galaxy'] AND $Target['system'] == $_Planet['system'] AND $Target['planet'] == $_Planet['planet'] AND $Target['type'] == $_Planet['planet_type'])
{
    message($_Lang['fl2_cantsendsamecoords'], $ErrorTitle, 'fleet.php', 3);
}

$isValidCoordinate = Flights\Utils\Checks\isValidCoordinate([ 'coordinate' => $Target ]);

if (!$isValidCoordinate['isValid']) {
    message($_Lang['fl2_targeterror'], $ErrorTitle, 'fleet.php', 3);
}

foreach ($Target as $Type => $Value) {
    // Set Positions for Inputs
    $_Lang['Target_'.$Type] = $Value;
}

$availableSpeeds = FlightControl\Utils\Helpers\getAvailableSpeeds([
    'user' => &$_User,
    'timestamp' => $Now,
]);

if (!in_array($_POST['speed'], $availableSpeeds)) {
    message($_Lang['fl_bad_fleet_speed'], $ErrorTitle, 'fleet.php', 3);
}

$targetInfo = FlightControl\Utils\Helpers\getTargetInfo([
    'targetCoords' => $Target,
    'fleetEntry' => [
        // TODO: There should be a function to fetch just the target owner details
        'Mission' => null,
    ],
    'fleetOwnerUser' => &$_User,
    'isExtendedTargetOwnerDetailsEnabled' => false,
]);

$targetPlanetDetails = $targetInfo['targetPlanetDetails'];
$targetOwnerDetails = $targetInfo['targetOwnerDetails'];

if (
    $targetInfo['isPlanetOccupied'] &&
    !$targetInfo['isPlanetAbandoned'] &&
    !$targetInfo['isPlanetOwnedByFleetOwner'] &&
    !empty($_GameConfig['TestUsersIDs'])
) {
    $TestUsersArray = explode(',', $_GameConfig['TestUsersIDs']);

    if (in_array($targetOwnerDetails['id'], $TestUsersArray)) {
        $EnableTestAccWarning = true;
    }
}

// Parse Fleet Array
$Fleet = [
    'array' => [],
    'count' => 0,
    'storage' => 0,
    'FuelStorage' => 0,
];

$inputFleetArray = String2Array($_POST['FleetArray']);

if (
    empty($inputFleetArray) ||
    !is_array($inputFleetArray)
) {
    message($_Lang['fl1_NoShipsGiven'], $ErrorTitle, 'fleet.php', 3);
}

$fleetArrayParsingResult = FlightControl\Utils\Validators\parseFleetArray([
    'fleet' => $inputFleetArray,
    'planet' => &$_Planet,
    'isFromDirectUserInput' => false,
]);

if (!$fleetArrayParsingResult['isValid']) {
    $firstValidationError = $fleetArrayParsingResult['errors'][0];
    $errorMessage = FlightControl\Utils\Errors\mapFleetArrayValidationErrorToReadableMessage($firstValidationError);

    message($errorMessage, $ErrorTitle, 'fleet.php', 3);
}

$Fleet['array'] = $fleetArrayParsingResult['payload']['parsedFleet'];

$shipsTotalStorage = FlightControl\Utils\Helpers\FleetArray\getShipsTotalStorage($Fleet['array']);

$Fleet['count'] = FlightControl\Utils\Helpers\FleetArray\getAllShipsCount($Fleet['array']);
$Fleet['storage'] = $shipsTotalStorage['allPurpose'];
$Fleet['FuelStorage'] = $shipsTotalStorage['fuelOnly'];

$AvailableMissions = FlightControl\Utils\Helpers\getValidMissionTypes([
    'targetCoordinates' => $Target,
    'fleetShips' => $Fleet['array'],
    'fleetShipsCount' => $Fleet['count'],
    'isPlanetOccupied' => $targetInfo['isPlanetOccupied'],
    'isPlanetOwnedByUser' => $targetInfo['isPlanetOwnedByFleetOwner'],
    'isPlanetOwnedByUsersFriend' => $targetInfo['isPlanetOwnerFriendly'],
    'isUnionMissionAllowed' => false,
]);

if (
    in_array(Flights\Enums\FleetMission::Attack, $AvailableMissions) &&
    $targetPlanetDetails['id'] > 0
) {
    $SQLResult_CheckACS = doquery(
        "SELECT * FROM {{table}} WHERE (`users` LIKE '%|{$_User['id']}|%' OR `owner_id` = {$_User['id']}) AND `end_target_id` = {$targetPlanetDetails['id']} AND `start_time` > UNIX_TIMESTAMP();",
        'acs'
    );

    if($SQLResult_CheckACS->num_rows > 0)
    {
        while($ACSData = $SQLResult_CheckACS->fetch_assoc())
        {
            $ACSData['fleets_count'] += 1;
            $ACSList[$ACSData['id']] = "{$ACSData['name']} ({$_Lang['fl_acs_fleets']}: {$ACSData['fleets_count']})";
        }
        $AvailableMissions[] = Flights\Enums\FleetMission::UnitedAttack;
    }
}

$quantumGateStateDetails = FlightControl\Utils\Helpers\getQuantumGateStateDetails([
    'availableMissions' => $AvailableMissions,
    'originPlanet' => $_Planet,
    'targetCoords' => $Target,
    'targetInfo' => $targetInfo,
    'targetPlanetDetails' => $targetPlanetDetails,
    'currentTimestamp' => $Now,
]);

$allowUseQuantumGate = $quantumGateStateDetails['canUseQuantumGate'];
$allowGateJump = $quantumGateStateDetails['canUseQuantumGateJump'];

$PreSelectedMission = intval($_POST['target_mission']);
$SpeedFactor = getUniFleetsSpeedFactor();
$GenFleetSpeed = $_POST['speed'];

$slowestShipSpeed = FlightControl\Utils\Helpers\getSlowestShipSpeed([
    'shipsDetails' => getFleetShipsSpeeds($Fleet['array'], $_User),
    'user' => &$_User,
]);

$distance = getFlightDistanceBetween($_Planet, $Target);
$duration = getFlightDuration([
    'speedFactor' => $GenFleetSpeed,
    'distance' => $distance,
    'maxShipsSpeed' => $slowestShipSpeed
]);

$consumption = getFlightTotalConsumption(
    [
        'ships' => $Fleet['array'],
        'distance' => $distance,
        'duration' => $duration,
    ],
    $_User
);

if($_Planet['deuterium'] < $consumption)
{
    if($allowUseQuantumGate)
    {
        if($allowGateJump OR $_Planet['deuterium'] > ($consumption / 2))
        {
            $AllowNoEnoughDeuterium = true;
            $_Lang['P_UserHave2UseQuantumGate'] = '1';
        }
    }
    if(!isset($AllowNoEnoughDeuterium))
    {
        message($_Lang['fl2_NoEnoughFuel'], $ErrorTitle, 'fleet.php', 3);
    }
}
if(($Fleet['storage'] + $Fleet['FuelStorage']) < $consumption)
{
    if($allowUseQuantumGate)
    {
        if($allowGateJump OR ($Fleet['storage'] + $Fleet['FuelStorage']) > ($consumption / 2))
        {
            $AllowNoEnoughFreeStorage = true;
            $_Lang['P_UserHave2UseQuantumGate'] = '1';
        }
    }
    if($AllowNoEnoughFreeStorage !== true)
    {
        message($_Lang['fl2_NoEnoughStorage'], $ErrorTitle, 'fleet.php', 3);
    }
}

// Show info boxes
$_Lang['P_SFBInfobox'] = FlightControl\Components\SmartFleetBlockadeInfoBox\render()['componentHTML'];

$_Lang['TitlePos'] = ($_Planet['planet_type'] == 1 ? $_Lang['fl2_sendfromplanet'] : $_Lang['fl2_sendfrommoon'])." {$_Planet['name']} [{$_Planet['galaxy']}:{$_Planet['system']}:{$_Planet['planet']}]";

$_Lang['FleetArray'] = $_POST['FleetArray'];
if($_POST['quickres'] == '1')
{
    $_Lang['P_SetQuickRes']= '1';
}
else
{
    $_Lang['P_SetQuickRes']= '0';
}
$_Lang['Now'] = $Now;
$_Lang['This_metal'] = explode('.', sprintf('%f', floor($_Planet['metal'])));
$_Lang['This_metal'] = (string)$_Lang['This_metal'][0];
$_Lang['This_crystal'] = explode('.', sprintf('%f', floor($_Planet['crystal'])));
$_Lang['This_crystal'] = (string)$_Lang['This_crystal'][0];
$_Lang['This_deuterium'] = explode('.', sprintf('%f', floor($_Planet['deuterium'])));
$_Lang['This_deuterium'] = (string)$_Lang['This_deuterium'][0];
$_Lang['P_FlightDuration'] = (string)($duration + 0);
$_Lang['FlightTimeShow'] = pretty_time($duration, true);
$_Lang['consumption'] = (string)($consumption + 0);
$_Lang['ShowConsumption'] = prettyNumber($consumption);
$_Lang['totalstorage'] = (string)($Fleet['storage'] + 0);
if($Fleet['FuelStorage'] >= $consumption)
{
    $_Lang['FuelStorageReduce'] = $consumption;
}
else
{
    $_Lang['FuelStorageReduce'] = $Fleet['FuelStorage'];
}
$TempCeil = ceil($consumption / 2);
if($Fleet['FuelStorage'] >= $TempCeil)
{
    $_Lang['FuelStorageReduceH'] = $TempCeil;
}
else
{
    $_Lang['FuelStorageReduceH'] = $Fleet['FuelStorage'];
}
$_Lang['freeStorage'] = (string)($Fleet['storage'] - $consumption + $_Lang['FuelStorageReduce'] + 0);
$_Lang['FuelStorageReduce'] = (string)($_Lang['FuelStorageReduce'] + 0);
$_Lang['FuelStorageReduceH'] = (string)($_Lang['FuelStorageReduceH'] + 0);
if((float)$_Lang['freeStorage'] > 0)
{
    $_Lang['SetDefaultFreeStorageColor'] = 'lime';
}
elseif((float)$_Lang['freeStorage'] < 0)
{
    $_Lang['SetDefaultFreeStorageColor'] = 'red';
}
else
{
    $_Lang['SetDefaultFreeStorageColor'] = 'orange';
}
$_Lang['SetDefaultFreeStorage'] = prettyNumber($_Lang['freeStorage']);
$_Lang['ShowTargetPos'] = "<a href=\"galaxy.php?mode=3&galaxy={$Target['galaxy']}&system={$Target['system']}&planet={$Target['planet']}\" target=\"_blank\">[{$Target['galaxy']}:{$Target['system']}:{$Target['planet']}]</a><b class=\"".($Target['type'] == 1 ? 'planet' : ($Target['type'] == 3 ? 'moon' : 'debris'))."\"></b><br/>";
if(!empty($targetPlanetDetails['name']))
{
    if($targetOwnerDetails['id'] > 0)
    {
        $_Lang['ShowTargetPos'] .= '<b class="orange">'.$targetPlanetDetails['name'].'</b>';
    }
    else
    {
        $_Lang['ShowTargetPos'] .= '<b class="red">'.$_Lang['fl2_target_abandoned_'.$Target['type']].'</b>';
    }
}
else
{
    if($Target['type'] == 2)
    {
        $_Lang['ShowTargetPos'] .= $_Lang['fl2_debrisfield'];
    }
    else
    {
        $_Lang['ShowTargetPos'] .= $_Lang['fl2_emptyplanet'];
    }
}
if($targetOwnerDetails['id'] > 0)
{
    $_Lang['ShowTargetOwner'] = "<a ".($targetInfo['isPlanetOwnerNonAggressiveAllianceMember'] ? 'class="skyblue"' : '')." href=\"profile.php?uid={$targetOwnerDetails['id']}\" target=\"_blank\">{$targetOwnerDetails['username']}</a>";
}
else
{
    $_Lang['ShowTargetOwner'] = '-';
}
$_Lang['SetSpeed'] = $_POST['speed'];

if($_User['settings_useprettyinputbox'] == 1)
{
    $_Lang['P_AllowPrettyInputBox'] = 'true';
}
else
{
    $_Lang['P_AllowPrettyInputBox'] = 'false';
}
$_User['settings_resSortArray'] = explode(',', $_User['settings_resSortArray']);
foreach($_User['settings_resSortArray'] as $ResSortData)
{
    switch($ResSortData)
    {
        case 'met':
            $Temp[] = "'1'";
            break;
        case 'cry':
            $Temp[] = "'2'";
            break;
        case 'deu':
            $Temp[] = "'3'";
            break;
    }

    $Temp[] = "'{$ResSortData}'";
    if($ResSortData != 'deu')
    {
        $Temp2[] = "'{$ResSortData}'";
    }
}
$_Lang['ResSortArrayAll'] = '['.implode(', ', $Temp).']';
$_Lang['ResSortArrayNoDeu'] = '['.implode(', ', $Temp2).']';

if($allowUseQuantumGate)
{
    $NextUseTimestamp = ($_Planet['quantumgate_lastuse'] + ($QuantumGateInterval * 3600)) - $Now;
    if($NextUseTimestamp < 0)
    {
        $NextUseTimestamp = 0;
    }
    if($NextUseTimestamp == 0)
    {
        $_Lang['P_HideQuantumGateReady2UseIn'] = 'hide';
    }
    else
    {
        include("{$_EnginePath}/includes/functions/InsertJavaScriptChronoApplet.php");
        $_Lang['InsertQuantumGateChronoApplet'] = InsertJavaScriptChronoApplet('quantum', '0', $NextUseTimestamp);
        $_Lang['P_QuantumGateNextUse'] = pretty_time($NextUseTimestamp, true);
        $_Lang['P_HideQuantumGateReady2Use'] = 'hide';
    }
}
else
{
    $_Lang['P_HideQuantumGate'] = $Hide;
}

if (!empty($AvailableMissions)) {
    $_Lang['MissionSelectors'] = FlightControl\Components\AvailableMissionsList\render([
        'availableMissions' => $AvailableMissions,
        'selectedMission' => $PreSelectedMission,
    ])['componentHTML'];

    $_Lang['P_HideNoMissionInfo'] = $Hide;

    if (!in_array(Flights\Enums\FleetMission::Hold, $AvailableMissions)) {
        $_Lang['P_HideHoldingTimers'] = $Hide;
    }
    if (in_array(Flights\Enums\FleetMission::UnitedAttack, $AvailableMissions)) {
        $_Lang['CreateACSList'] = '';

        foreach ($ACSList as $ID => $Name) {
            $_Lang['CreateACSList'] .= '<option value="'.$ID.'" '.($inputJoinUnionId == $ID ? 'selected' : '').'>'.$Name.'</option>';
        }
    } else {
        $_Lang['P_HideACSJoinList'] = $Hide;
    }
}

$quantumGateFuelJSObject = FlightControl\Utils\Factories\createQuantumGateFuelJSObject([
    'availableMissions' => $AvailableMissions,
    'canUseQuantumGate' => $quantumGateStateDetails['canUseQuantumGate'],
    'canUseQuantumGateJump' => $quantumGateStateDetails['canUseQuantumGateJump'],
]);
$_Lang['QuantumGateJSArray'] = json_encode($quantumGateFuelJSObject);

if(isset($EnableTestAccWarning))
{
    $_Lang['CreateTestACCAlert'] = 'alert("'.$_Lang['fl2_testacctarget'].'");';
}

if($Target['planet'] != (MAX_PLANET_IN_SYSTEM + 1))
{
    $_Lang['P_HideExpeditionTimers'] = $Hide;
}
if ($targetInfo['isPlanetOwnerNonAggressiveAllianceMember'])
{
    $_Lang['Insert_AllyPact_AttackWarn'] = 'true';
}
else
{
    $_Lang['Insert_AllyPact_AttackWarn'] = 'false';
}

$availableHoldTimes = FlightControl\Utils\Helpers\getAvailableHoldTimes([]);

$missionHoldTimeOptions = array_map(
    function ($holdTimeValue) use ($setFormValues) {
        return buildDOMElementHTML([
            'tagName' => 'option',
            'contentHTML' => $holdTimeValue,
            'attrs' => [
                'value' => $holdTimeValue,
                'selected' => (
                    $holdTimeValue == $setFormValues['holdingtime'] ?
                        '' :
                        null
                ),
            ],
        ]);
    },
    $availableHoldTimes
);

$availableExpeditionTimes = FlightControl\Utils\Helpers\getAvailableExpeditionTimes();

$missionExpeditionTimeOptions = array_map(
    function ($optionTimeValue) use ($setFormValues) {
        return buildDOMElementHTML([
            'tagName' => 'option',
            'contentHTML' => $optionTimeValue,
            'attrs' => [
                'value' => $optionTimeValue,
                'selected' => (
                    $optionTimeValue == $setFormValues['expeditionTime'] ?
                        '' :
                        null
                ),
            ],
        ]);
    },
    $availableExpeditionTimes
);

$_Lang['P_HTMLBuilder_MissionHold_AvailableTimes'] = implode('', $missionHoldTimeOptions);
$_Lang['P_HTMLBuilder_MissionExpedition_AvailableTimes'] = implode('', $missionExpeditionTimeOptions);

display(parsetemplate(gettemplate('fleet2_body'), $_Lang), $_Lang['fl_title']);

?>
