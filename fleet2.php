<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

include($_EnginePath . 'modules/flightControl/_includes.php');

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
        $_Lang['SelectExpedition_'.$_POST['gobackVars']['expeditiontime']] = 'selected';
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

$GetACSData = intval($_POST['getacsdata']);
if($GetACSData > 0)
{
    $ACSData = doquery("SELECT `id`, `name`, `end_galaxy`, `end_system`, `end_planet`, `end_type`, `start_time` FROM {{table}} WHERE `id` = {$GetACSData};", 'acs', true);
    if($ACSData['id'] == $GetACSData)
    {
        if($ACSData['start_time'] > $Now)
        {
            $Target['galaxy'] = $ACSData['end_galaxy'];
            $Target['system'] = $ACSData['end_system'];
            $Target['planet'] = $ACSData['end_planet'];
            $Target['type'] = $ACSData['end_type'];
        }
        else
        {
            message($_Lang['fl1_ACSTimeUp'], $ErrorTitle, 'fleet.php', 3);
        }
    }
    else
    {
        message($_Lang['fl1_ACSNoExist'], $ErrorTitle, 'fleet.php', 3);
    }
}

if($Target['galaxy'] == $_Planet['galaxy'] AND $Target['system'] == $_Planet['system'] AND $Target['planet'] == $_Planet['planet'] AND $Target['type'] == $_Planet['planet_type'])
{
    message($_Lang['fl2_cantsendsamecoords'], $ErrorTitle, 'fleet.php', 3);
}

foreach($Target as $Type => $Value)
{
    if($Value < 1)
    {
        $TargetError = true;
        break;
    }
    switch($Type)
    {
        case 'galaxy':
            $CheckValue = MAX_GALAXY_IN_WORLD;
            break;
        case 'system':
            $CheckValue = MAX_SYSTEM_IN_GALAXY;
            break;
        case 'planet':
            $CheckValue = MAX_PLANET_IN_SYSTEM + 1;
            break;
        case 'type':
            $CheckValue = 3;
            break;
    }
    if($Value > $CheckValue)
    {
        $TargetError = true;
        break;
    }
    // Set Positions for Inputs
    $_Lang['Target_'.$Type] = $Value;
}
if(isset($TargetError))
{
    message($_Lang['fl2_targeterror'], $ErrorTitle, 'fleet.php', 3);
}

$availableSpeeds = FlightControl\Utils\Helpers\getAvailableSpeeds([
    'user' => &$_User,
    'timestamp' => $Now,
]);

if (!in_array($_POST['speed'], $availableSpeeds)) {
    message($_Lang['fl_bad_fleet_speed'], $ErrorTitle, 'fleet.php', 3);
}

// Check PlanetOwner
$planetOwnerDetails = FlightControl\Utils\Fetchers\fetchPlanetOwnerDetails([
    'targetCoordinates' => $Target,
    'user' => &$_User,
    'isExtendedUserDetailsEnabled' => false,
]);

$isPlanetOccupied = !empty($planetOwnerDetails);

$YourPlanet = false;
$OwnerFriend = false;
$OwnerHasMarcantilePact = false;
$AllyPactWarning = false;

if ($planetOwnerDetails) {
    $CheckPlanetOwner = $planetOwnerDetails;

    if ($CheckPlanetOwner['owner'] == $_User['id']) {
        $YourPlanet = true;
    } else {
        if(!empty($_GameConfig['TestUsersIDs']))
        {
            $TestUsersArray = explode(',', $_GameConfig['TestUsersIDs']);
            if(in_array($CheckPlanetOwner['owner'], $TestUsersArray))
            {
                $EnableTestAccWarning = true;
            }
        }
        if((isset($CheckPlanetOwner['AllyPact1']) && $CheckPlanetOwner['AllyPact1'] >= ALLYPACT_NONAGGRESSION) || (isset($CheckPlanetOwner['AllyPact2']) && $CheckPlanetOwner['AllyPact2'] >= ALLYPACT_NONAGGRESSION))
        {
            $AllyPactWarning = true;
        }
        if((isset($CheckPlanetOwner['AllyPact1']) && $CheckPlanetOwner['AllyPact1'] >= ALLYPACT_MERCANTILE) || (isset($CheckPlanetOwner['AllyPact2']) && $CheckPlanetOwner['AllyPact2'] >= ALLYPACT_MERCANTILE))
        {
            $OwnerHasMarcantilePact = true;
        }
        if(($CheckPlanetOwner['active1'] == 1 OR $CheckPlanetOwner['active2'] == 1) OR ($CheckPlanetOwner['ally_id'] == $_User['ally_id'] AND $_User['ally_id'] > 0) OR ((isset($CheckPlanetOwner['AllyPact1']) && $CheckPlanetOwner['AllyPact1'] >= ALLYPACT_DEFENSIVE) || (isset($CheckPlanetOwner['AllyPact2']) && $CheckPlanetOwner['AllyPact2'] >= ALLYPACT_DEFENSIVE)))
        {
            $OwnerFriend = true;
        }
    }
} else {
    $CheckPlanetOwner = [];
}

// Parse Fleet Array
$Fleet['count'] = 0;
$Fleet['storage'] = 0;
$Fleet['FuelStorage'] = 0;

$Fleet['array'] = String2Array($_POST['FleetArray']);
$FleetArray = array();

if (
    !empty($Fleet['array']) &&
    is_array($Fleet['array'])
) {
    $fleetArrayValidationResult = FlightControl\Utils\Validators\validateFleetArray([
        'fleet' => $Fleet['array'],
        'planet' => &$_Planet,
        'isFromDirectUserInput' => false,
    ]);

    if (!$fleetArrayValidationResult['isValid']) {
        $firstValidationError = $fleetArrayValidationResult['errors'][0];

        $errorMessage = null;
        switch ($firstValidationError['errorCode']) {
            case 'INVALID_SHIP_ID':
                $errorMessage = $_Lang['fl1_BadShipGiven'];
                break;
            case 'SHIP_WITH_NO_ENGINE':
                $errorMessage = $_Lang['fl1_CantSendUnflyable'];
                break;
            case 'INVALID_SHIP_COUNT':
                $errorMessage = $_Lang['fleet_generic_errors_invalidshipcount'];
                break;
            case 'SHIP_COUNT_EXCEEDS_AVAILABLE':
                $errorMessage = $_Lang['fl1_NoEnoughShips'];
                break;
            default:
                $errorMessage = $_Lang['fleet_generic_errors_unknown'];
                break;
        }

        message($errorMessage, $ErrorTitle, 'fleet.php', 3);
    }

    foreach ($Fleet['array'] as $ShipID => $ShipCount) {
        $ShipID = intval($ShipID);
        $ShipCount = floor($ShipCount);
        $FleetArray[$ShipID] = $ShipCount;
        $Fleet['count'] += $ShipCount;

        $ThisStorage = getShipsStorageCapacity($ShipID) * $ShipCount;

        if ($ShipID != 210) {
            $Fleet['storage'] += $ThisStorage;
        } else {
            $Fleet['FuelStorage'] += $ThisStorage;
        }
    }
} else {
    message($_Lang['fl2_FleetArrayPostEmpty'], $ErrorTitle, 'fleet.php', 3);
}

if($Fleet['count'] <= 0)
{
    message($_Lang['fl2_ZeroShips'], $ErrorTitle, 'fleet.php', 3);
}
$Fleet['array'] = $FleetArray;
unset($FleetArray);

$AvailableMissions = FlightControl\Utils\Helpers\getValidMissionTypes([
    'targetCoordinates' => $Target,
    'fleetShips' => $Fleet['array'],
    'fleetShipsCount' => $Fleet['count'],
    'isPlanetOccupied' => $isPlanetOccupied,
    'isPlanetOwnedByUser' => $YourPlanet,
    'isPlanetOwnedByUsersFriend' => $OwnerFriend,
    'isUnionMissionAllowed' => false,
]);

if(in_array(1, $AvailableMissions) && $CheckPlanetOwner['id'] > 0)
{
    $SQLResult_CheckACS = doquery(
        "SELECT * FROM {{table}} WHERE (`users` LIKE '%|{$_User['id']}|%' OR `owner_id` = {$_User['id']}) AND `end_target_id` = {$CheckPlanetOwner['id']} AND `start_time` > UNIX_TIMESTAMP();",
        'acs'
    );

    if($SQLResult_CheckACS->num_rows > 0)
    {
        while($ACSData = $SQLResult_CheckACS->fetch_assoc())
        {
            $ACSData['fleets_count'] += 1;
            $ACSList[$ACSData['id']] = "{$ACSData['name']} ({$_Lang['fl_acs_fleets']}: {$ACSData['fleets_count']})";
        }
        $AvailableMissions[] = 2;
    }
}

$allowUseQuantumGate = false;
$allowGateJump = false;
if(!empty($AvailableMissions))
{
    if($_Planet['quantumgate'] == 1)
    {
        if(($YourPlanet OR $OwnerFriend OR $OwnerHasMarcantilePact) AND $CheckPlanetOwner['quantumgate'] == 1 AND (in_array(3, $AvailableMissions) OR in_array(4, $AvailableMissions) OR in_array(5, $AvailableMissions)))
        {
            $allowUseQuantumGate = true;
            $allowGateJump = true;
        }
        else
        {
            if($_Planet['galaxy'] == $Target['galaxy'])
            {
                if(($_Planet['quantumgate_lastuse'] + ($QuantumGateInterval * 3600)) <= $Now)
                {
                    $allowUseQuantumGate = true;
                }
            }
        }
    }
}

$PreSelectedMission = intval($_POST['target_mission']);
$SpeedFactor = getUniFleetsSpeedFactor();
$AllFleetSpeed = getFleetShipsSpeeds($Fleet['array'], $_User);
$GenFleetSpeed = $_POST['speed'];
$MaxFleetSpeed = min($AllFleetSpeed);
if(MORALE_ENABLED)
{
    if($_User['morale_level'] <= MORALE_PENALTY_FLEETSLOWDOWN)
    {
        $MaxFleetSpeed *= MORALE_PENALTY_FLEETSLOWDOWN_VALUE;
    }
}

$distance = getFlightDistanceBetween($_Planet, $Target);
$duration = getFlightDuration([
    'speedFactor' => $GenFleetSpeed,
    'distance' => $distance,
    'maxShipsSpeed' => $MaxFleetSpeed
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

// Fleet Blockade Info (here, only for Global Block)
$GetSFBData = doquery("SELECT `ID`, `EndTime`, `BlockMissions`, `DontBlockIfIdle`, `Reason` FROM {{table}} WHERE `Type` = 1 AND `StartTime` <= UNIX_TIMESTAMP() AND (`EndTime` > UNIX_TIMESTAMP() OR `PostEndTime` > UNIX_TIMESTAMP()) ORDER BY `EndTime` DESC LIMIT 1;", 'smart_fleet_blockade', true);
if($GetSFBData['ID'] > 0)
{
    // Fleet Blockade is Active
    include($_EnginePath.'includes/functions/CreateSFBInfobox.php');
    $_Lang['P_SFBInfobox'] = CreateSFBInfobox($GetSFBData, array('standAlone' => true, 'Width' => 750, 'MarginBottom' => 10));
}

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
if(!empty($CheckPlanetOwner['name']))
{
    if($CheckPlanetOwner['owner'] > 0)
    {
        $_Lang['ShowTargetPos'] .= '<b class="orange">'.$CheckPlanetOwner['name'].'</b>';
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
if($CheckPlanetOwner['owner'] > 0)
{
    $_Lang['ShowTargetOwner'] = "<a ".($AllyPactWarning === true ? 'class="skyblue"' : '')." href=\"profile.php?uid={$CheckPlanetOwner['owner']}\" target=\"_blank\">{$CheckPlanetOwner['username']}</a>";
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

if(!empty($AvailableMissions))
{
    $MissionRowTPL = gettemplate('fleet2_missionrow');
    foreach($AvailableMissions as $MID)
    {
        $ThisMission = array();
        $ThisMission['MID'] = $MID;
        if($PreSelectedMission == $MID)
        {
            $ThisMission['CheckThisMission'] = ' checked';
        }
        $ThisMission['ThisMissionName'] = $_Lang['type_mission'][$MID];

        $_Lang['MissionSelectors'] .= parsetemplate($MissionRowTPL, $ThisMission);
        if($allowUseQuantumGate)
        {
            if($MID == 1 OR $MID == 2 OR $MID == 6 OR $MID == 9)
            {
                $SetValue = '0';
            }
            else
            {
                if($allowGateJump AND ($MID == 3 OR $MID == 4 OR $MID == 5))
                {
                    $SetValue = '2';
                }
                else
                {
                    $SetValue = '1';
                }
            }
        }
        else
        {
            $SetValue = '0';
        }
        $_Lang['QuantumGateJSArray'][] = $MID.': '.$SetValue;
    }
    if(!empty($_Lang['QuantumGateJSArray']))
    {
        $_Lang['QuantumGateJSArray'] = 'var QuantumGateDeuteriumUse = {'.implode(', ', $_Lang['QuantumGateJSArray']).'}';
    }
    $_Lang['P_HideNoMissionInfo'] = $Hide;
}

if(isset($EnableTestAccWarning))
{
    $_Lang['CreateTestACCAlert'] = 'alert("'.$_Lang['fl2_testacctarget'].'");';
}

if($Target['planet'] != (MAX_PLANET_IN_SYSTEM + 1))
{
    $_Lang['P_HideExpeditionTimers'] = $Hide;
}
if(!in_array(5, $AvailableMissions))
{
    $_Lang['P_HideHoldingTimers'] = $Hide;
}
if(in_array(2, $AvailableMissions))
{
    $_Lang['CreateACSList'] = '';
    foreach($ACSList as $ID => $Name)
    {
        $_Lang['CreateACSList'] .= '<option value="'.$ID.'" '.($GetACSData == $ID ? 'selected' : '').'>'.$Name.'</option>';
    }
}
else
{
    $_Lang['P_HideACSJoinList'] = $Hide;
}
if($AllyPactWarning === true)
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

$_Lang['P_HTMLBuilder_MissionHold_AvailableTimes'] = implode('', $missionHoldTimeOptions);

display(parsetemplate(gettemplate('fleet2_body'), $_Lang), $_Lang['fl_title']);

?>
