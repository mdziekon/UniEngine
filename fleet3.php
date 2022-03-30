<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');

include($_EnginePath . 'modules/flightControl/_includes.php');

use UniEngine\Engine\Modules\FlightControl;
use UniEngine\Engine\Modules\Flights;

loggedCheck();

if($_POST['sending_fleet'] != '1')
{
    header('Location: fleet.php'.($_GET['quickres'] == 1 ? '?quickres=1' : ''));
    safeDie();
}

function messageRed($Text, $Title)
{
    global $_POST, $_Lang;
    $_POST = base64_encode(json_encode($_POST));
    $GoBackForm = '';
    $GoBackForm .= '<form action="fleet2.php" method="post"><input type="hidden" name="fromEnd" value="1"/>';
    $GoBackForm .= '<input type="hidden" name="gobackVars" value="'.$_POST.'"/>';
    $GoBackForm .= '<input class="orange pad5" style="font-weight: bold;" type="submit" value="&laquo; '.$_Lang['fl_goback'].'"/>';
    $GoBackForm .= '</form>';
    message("<br/><b class=\"red\">{$Text}</b><br/>{$GoBackForm}", $Title);
}

includeLang('fleet');

$Now = time();
$ErrorTitle = &$_Lang['fl_error'];

if(MORALE_ENABLED)
{
    Morale_ReCalculate($_User, $Now);
}

// --- Initialize Vars
$Target['galaxy'] = intval($_POST['galaxy']);
$Target['system'] = intval($_POST['system']);
$Target['planet'] = intval($_POST['planet']);
$Target['type'] = intval($_POST['planettype']);
$Fleet['Speed'] = floatval($_POST['speed']);
$Fleet['UseQuantum'] = (isset($_POST['usequantumgate']) && $_POST['usequantumgate'] == 'on' ? true : false);
$Fleet['resources'] = array('metal' => $_POST['resource1'], 'crystal' => $_POST['resource2'], 'deuterium' => $_POST['resource3']);
$Fleet['ExpeTime'] = intval($_POST['expeditiontime']);
$Fleet['HoldTime'] = intval($_POST['holdingtime']);
$Fleet['ACS_ID'] = isset($_POST['acs_id']) ? floor(floatval($_POST['acs_id'])) : 0;
$Fleet['Mission'] = isset($_POST['mission']) ? intval($_POST['mission']) : 0;

$Protections['enable'] = (bool) $_GameConfig['noobprotection'];
$Protections['basicLimit'] = $_GameConfig['noobprotectiontime'] * 1000;
$Protections['weakMulti'] = $_GameConfig['noobprotectionmulti'];
$Protections['adminEnable'] = (bool) $_GameConfig['adminprotection'];
$Protections['ally'] = $_GameConfig['allyprotection'];
$Protections['weakLimit'] = $_GameConfig['no_noob_protect'] * 1000;
$Protections['idleTime'] = $_GameConfig['no_idle_protect'] * TIME_DAY;
$Protections['mtypes'] = array(1, 2, 6, 9);
$Protections['newTime'] = $_GameConfig['Protection_NewPlayerTime'];
$Protections['antifarm_enabled'] = (bool) $_GameConfig['Protection_AntiFarmEnabled'];
$Protections['antifarm_rate'] = $_GameConfig['Protection_AntiFarmRate'];
$Protections['antifarm_counttotal'] = $_GameConfig['Protection_AntiFarmCountTotal'];
$Protections['antifarm_countplanet'] = $_GameConfig['Protection_AntiFarmCountPlanet'];
$Protections['bashLimit_enabled'] = (bool) $_GameConfig['Protection_BashLimitEnabled'];
$Protections['bashLimit_interval'] = $_GameConfig['Protection_BashLimitInterval'];
$Protections['bashLimit_counttotal'] = $_GameConfig['Protection_BashLimitCountTotal'];
$Protections['bashLimit_countplanet'] = $_GameConfig['Protection_BashLimitCountPlanet'];

if (!isUserAccountActivated($_User)) {
    messageRed($_Lang['fl3_BlockAccNotActivated'], $ErrorTitle);
}

// --- Check if Mission is selected
if($Fleet['Mission'] <= 0)
{
    messageRed($_Lang['fl3_NoMissionSelected'], $ErrorTitle);
}

$fleetsInFlightCounters = FlightControl\Utils\Helpers\getFleetsInFlightCounters([
    'userId' => $_User['id'],
]);

// Get Available Slots for Expeditions (1 + floor(ExpeditionTech / 3))
$Slots['MaxFleetSlots'] = FlightControl\Utils\Helpers\getUserFleetSlotsCount([
    'user' => $_User,
    'timestamp' => $Now,
]);
$Slots['MaxExpedSlots'] = FlightControl\Utils\Helpers\getUserExpeditionSlotsCount([
    'user' => $_User,
]);
$Slots['FlyingFleetsCount'] = $fleetsInFlightCounters['allFleetsInFlight'];
$Slots['FlyingExpeditions'] = $fleetsInFlightCounters['expeditionsInFlight'];
if($Slots['FlyingFleetsCount'] >= $Slots['MaxFleetSlots'])
{
    messageRed($_Lang['fl3_NoMoreFreeSlots'], $ErrorTitle);
}
if($Slots['FlyingExpeditions'] >= $Slots['MaxExpedSlots'] AND $Fleet['Mission'] == 15)
{
    messageRed($_Lang['fl3_NoMoreFreeExpedSlots'], $ErrorTitle);
}

// --- Check if all resources are correct (no negative numbers and enough on planet)
foreach($Fleet['resources'] as $Key => $Data)
{
    $Fleet['resources'][$Key] = floor(floatval(str_replace('.', '', $Data)));
    if($Fleet['resources'][$Key] < 0)
    {
        messageRed($_Lang['fl3_BadResourcesGiven'], $ErrorTitle);
    }
    elseif($Fleet['resources'][$Key] > $_Planet[$Key])
    {
        messageRed($_Lang['fl3_PlanetNoEnough'.$Key], $ErrorTitle);
    }

    if($Fleet['resources'][$Key] == 0)
    {
        $Fleet['resources'][$Key] = '0';
    }
}

// --- Check, if Target Data are correct
if($Target['galaxy'] == $_Planet['galaxy'] AND $Target['system'] == $_Planet['system'] AND $Target['planet'] == $_Planet['planet'] AND $Target['type'] == $_Planet['planet_type'])
{
    messageRed($_Lang['fl2_cantsendsamecoords'], $ErrorTitle);
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
}
if(isset($TargetError))
{
    messageRed($_Lang['fl2_targeterror'], $ErrorTitle);
}

$availableSpeeds = FlightControl\Utils\Helpers\getAvailableSpeeds([
    'user' => &$_User,
    'timestamp' => $Now,
]);

if (!in_array($Fleet['Speed'], $availableSpeeds)) {
    messageRed($_Lang['fl_bad_fleet_speed'], $ErrorTitle);
}

// --- Check PlanetOwner
$YourPlanet = false;
$UsedPlanet = false;
$OwnerFriend = false;
$OwnerIsBuddyFriend = false;
$OwnerIsAlliedUser = false;
$OwnerHasMarcantilePact = false;
$PlanetAbandoned = false;

if($Fleet['Mission'] != 8)
{
    $planetOwnerDetails = FlightControl\Utils\Fetchers\fetchPlanetOwnerDetails([
        'targetCoordinates' => $Target,
        'user' => &$_User,
        'isExtendedUserDetailsEnabled' => true,
    ]);

    if ($planetOwnerDetails) {
        $CheckGalaxyRow = doquery(
            "SELECT `galaxy_id` FROM {{table}} WHERE `galaxy` = {$Target['galaxy']} AND `system` = {$Target['system']} AND `planet` = {$Target['planet']} LIMIT 1;", 'galaxy',
            true
        );

        $CheckPlanetOwner = $planetOwnerDetails;

        $CheckPlanetOwner['galaxy_id'] = $CheckGalaxyRow['galaxy_id'];
        $UsedPlanet = true;
        if($CheckPlanetOwner['owner'] > 0)
        {
            if($CheckPlanetOwner['owner'] == $_User['id'])
            {
                $YourPlanet = true;
            }
            else
            {
                if((isset($CheckPlanetOwner['AllyPact1']) && $CheckPlanetOwner['AllyPact1'] >= ALLYPACT_NONAGGRESSION) || (isset($CheckPlanetOwner['AllyPact2']) && $CheckPlanetOwner['AllyPact2'] >= ALLYPACT_NONAGGRESSION))
                {
                    $OwnerIsAlliedUser = true;
                }
                if((isset($CheckPlanetOwner['AllyPact1']) && $CheckPlanetOwner['AllyPact1'] >= ALLYPACT_MERCANTILE) || (isset($CheckPlanetOwner['AllyPact2']) && $CheckPlanetOwner['AllyPact2'] >= ALLYPACT_MERCANTILE))
                {
                    $OwnerHasMarcantilePact = true;
                }
                if(($CheckPlanetOwner['active1'] == 1 OR $CheckPlanetOwner['active2'] == 1) OR ($CheckPlanetOwner['ally_id'] == $_User['ally_id'] AND $_User['ally_id'] > 0) OR ((isset($CheckPlanetOwner['AllyPact1']) && $CheckPlanetOwner['AllyPact1'] >= ALLYPACT_DEFENSIVE) || (isset($CheckPlanetOwner['AllyPact2']) && $CheckPlanetOwner['AllyPact2'] >= ALLYPACT_DEFENSIVE)))
                {
                    $OwnerFriend = true;
                    if($CheckPlanetOwner['active1'] == 1 OR $CheckPlanetOwner['active2'] == 1)
                    {
                        $OwnerIsBuddyFriend = true;
                    }
                }
            }
        }
        else
        {
            $PlanetAbandoned = true;
        }
    }
    else
    {
        $CheckPlanetOwner = [];
    }
}
else
{
    // This is a Recycling Mission, so check Galaxy Data
    $CheckDebrisField = doquery("SELECT `galaxy_id`, `metal`, `crystal` FROM {{table}} WHERE galaxy = '{$Target['galaxy']}' AND system = '{$Target['system']}' AND planet = '{$Target['planet']}'", 'galaxy', true);
}

$smartFleetsBlockadeStateValidationResult = FlightControl\Utils\Validators\validateSmartFleetsBlockadeState([
    'timestamp' => $Now,
    'fleetData' => $Fleet,
    'fleetOwnerDetails' => [
        'userId' => $_User['id'],
        'planetId' => $_Planet['id'],
    ],
    'targetOwnerDetails' => (
        $CheckPlanetOwner['owner'] > 0 ?
        [
            'userId' => $CheckPlanetOwner['owner'],
            'planetId' => $CheckPlanetOwner['id'],
            'onlinetime' => $CheckPlanetOwner['onlinetime'],
        ] :
        null
    ),
    'settings' => [
        'idleTime' => $Protections['idleTime']
    ],
]);

if (!$smartFleetsBlockadeStateValidationResult['isValid']) {
    $firstValidationError = $smartFleetsBlockadeStateValidationResult['errors'];

    $errorMessage = null;
    switch ($firstValidationError['blockType']) {
        case 'GLOBAL_ENDTIME':
            $errorMessage = $_Lang['SFB_Stop_GlobalBlockade'];
            break;
        case 'GLOBAL_POSTENDTIME':
            $errorMessage = sprintf(
                $_Lang['SFB_Stop_GlobalPostBlockade'],
                prettyDate('d m Y, H:i:s', $firstValidationError['details']['hardEndTime'], 1)
            );
            break;
        case 'USER':
            $errorDetails = $firstValidationError['details'];
            $reasonMessage = (
                empty($errorDetails['reason']) ?
                    $_Lang['SFB_Stop_ReasonNotGiven'] :
                    "\"{$errorDetails['reason']}\""
            );

            $errorMessage = sprintf(
                ($errorDetails['userId'] == $_User['id'] ? $_Lang['SFB_Stop_UserBlockadeOwn'] : $_Lang['SFB_Stop_UserBlockade']),
                prettyDate('d m Y', $errorDetails['endTime'], 1),
                date('H:i:s', $errorDetails['endTime']),
                $reasonMessage
            );

            break;
        case 'PLANET':
            $errorDetails = $firstValidationError['details'];
            $reasonMessage = (
                empty($errorDetails['reason']) ?
                    $_Lang['SFB_Stop_ReasonNotGiven'] :
                    "\"{$errorDetails['reason']}\""
            );
            $errorMessageTemplate = (
                $errorDetails['planetId'] == $_Planet['id'] ?
                (
                    $_Planet['planet_type'] == 1 ?
                    $_Lang['SFB_Stop_PlanetBlockadeOwn_Planet'] :
                    $_Lang['SFB_Stop_PlanetBlockadeOwn_Moon']
                ) :
                (
                    $Target['type'] == 1 ?
                    $_Lang['SFB_Stop_PlanetBlockade_Planet'] :
                    $_Lang['SFB_Stop_PlanetBlockade_Moon']
                )
            );

            $errorMessage = sprintf(
                $errorMessageTemplate,
                prettyDate('d m Y', $errorDetails['endTime'], 1),
                date('H:i:s', $errorDetails['endTime']),
                $reasonMessage
            );

            break;
        default:
            $errorMessage = $_Lang['fleet_generic_errors_unknown'];
            break;
    }

    messageRed(
        $errorMessage . $_Lang['SFB_Stop_LearnMore'],
        $_Lang['SFB_BoxTitle']
    );
}

// --- Parse Fleet Array
$Fleet['count'] = 0;
$Fleet['storage'] = 0;
$Fleet['FuelStorage'] = 0;
$Fleet['TotalResStorage'] = 0;

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

        messageRed($errorMessage, $ErrorTitle);
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

        $planetElementKey = _getElementPlanetKey($ShipID);
        $FleetRemover[] = "`{$planetElementKey}` = `{$planetElementKey}` - {$ShipCount}";
    }
} else {
    messageRed($_Lang['fl2_FleetArrayPostEmpty'], $ErrorTitle);
}

if($Fleet['count'] <= 0)
{
    messageRed($_Lang['fl2_ZeroShips'], $ErrorTitle);
}
$Fleet['array'] = $FleetArray;
unset($FleetArray);

$validMissionTypes = FlightControl\Utils\Helpers\getValidMissionTypes([
    'targetCoordinates' => $Target,
    'fleetShips' => $Fleet['array'],
    'fleetShipsCount' => $Fleet['count'],
    'isPlanetOccupied' => $UsedPlanet,
    'isPlanetOwnedByUser' => $YourPlanet,
    'isPlanetOwnedByUsersFriend' => $OwnerFriend,
    // TODO: additional pre-validation might be needed
    'isUnionMissionAllowed' => true,
]);

// --- Check if everything is OK with ACS
if ($Fleet['Mission'] == 2 AND in_array(2, $validMissionTypes)) {
    $joinUnionValidationResult = FlightControl\Utils\Validators\validateJoinUnion([
        'newFleet' => $Fleet,
        'timestamp' => $Now,
        'user' => &$_User,
        'destinationEntry' => &$CheckPlanetOwner,
    ]);

    if (!$joinUnionValidationResult['isValid']) {
        $firstValidationError = $joinUnionValidationResult['errors'][0];

        $errorMessage = null;
        switch ($firstValidationError['errorCode']) {
            case 'INVALID_UNION_ID':
                $errorMessage = $_Lang['fl_acs_bad_group_id'];
                break;
            case 'UNION_NOT_FOUND':
                $errorMessage = $_Lang['fl_acs_bad_group_id'];
                break;
            case 'USER_CANT_JOIN':
                $errorMessage = $_Lang['fl_acs_cannot_join_this_group'];
                break;
            case 'INVALID_DESTINATION_COORDINATES':
                $errorMessage = $_Lang['fl_acs_badcoordinates'];
                break;
            case 'UNION_JOINED_FLEETS_COUNT_EXCEEDED':
                $errorMessage = $_Lang['fl_acs_fleetcount_extended'];
                break;
            case 'UNION_JOIN_TIME_EXCEEDED':
                $errorMessage = $_Lang['fl_acs_cannot_join_time_extended'];
                break;
            default:
                $errorMessage = $_Lang['fleet_generic_errors_unknown'];
                break;
        }

        messageRed($errorMessage, $ErrorTitle);
    }

    $UpdateACS = true;

    // TODO: Optimize by not fetching this again
    $CheckACS = FlightControl\Utils\Helpers\getFleetUnionJoinData([
        'newFleet' => $Fleet,
    ]);
}

$Throw = false;

// --- If Mission is not correct, show Error
if(!in_array($Fleet['Mission'], $validMissionTypes))
{
    if ($Fleet['Mission'] == 1) {
        if ($Target['type'] == 2) {
            messageRed($_Lang['fl3_CantAttackDebris'], $ErrorTitle);
        }
        if (!$UsedPlanet) {
            messageRed($_Lang['fl3_CantAttackNonUsed'], $ErrorTitle);
        }
        if ($YourPlanet) {
            messageRed($_Lang['fl3_CantAttackYourself'], $ErrorTitle);
        }
    }
    if ($Fleet['Mission'] == 2) {
        if ($Target['type'] == 2) {
            messageRed($_Lang['fl3_CantACSDebris'], $ErrorTitle);
        }
        if (!$UsedPlanet) {
            messageRed($_Lang['fl3_CantACSNonUsed'], $ErrorTitle);
        }
        if ($YourPlanet) {
            messageRed($_Lang['fl3_CantACSYourself'], $ErrorTitle);
        }
    }
    if ($Fleet['Mission'] == 3) {
        if ($Target['type'] == 2) {
            messageRed($_Lang['fl3_CantTransportDebris'], $ErrorTitle);
        }
        if (!$UsedPlanet) {
            messageRed($_Lang['fl3_CantTransportNonUsed'], $ErrorTitle);
        }
        // TODO: This should be checked,
        // to prevent from other error states from being caught by this
        messageRed($_Lang['fl3_CantTransportSpyProbes'], $ErrorTitle);
    }
    if ($Fleet['Mission'] == 4) {
        if ($Target['type'] == 2) {
            messageRed($_Lang['fl3_CantStayDebris'], $ErrorTitle);
        }
        if (!$UsedPlanet) {
            messageRed($_Lang['fl3_CantStayNonUsed'], $ErrorTitle);
        }
        if (!$YourPlanet) {
            messageRed($_Lang['fl3_CantStayNonYourself'], $ErrorTitle);
        }
    }
    if ($Fleet['Mission'] == 5) {
        if ($Target['type'] == 2) {
            messageRed($_Lang['fl3_CantProtectDebris'], $ErrorTitle);
        }
        if (!$UsedPlanet) {
            messageRed($_Lang['fl3_CantProtectNonUsed'], $ErrorTitle);
        }
        if ($YourPlanet) {
            messageRed($_Lang['fl3_CantProtectYourself'], $ErrorTitle);
        }
        // TODO: This should be checked,
        // to prevent from other error states from being caught by this
        messageRed($_Lang['fl3_CantProtectNonFriend'], $ErrorTitle);
    }
    if ($Fleet['Mission'] == 6) {
        if ($Target['type'] == 2) {
            messageRed($_Lang['fl3_CantSpyDebris'], $ErrorTitle);
        }
        if (!$UsedPlanet) {
            messageRed($_Lang['fl3_CantSpyNonUsed'], $ErrorTitle);
        }
        if ($YourPlanet) {
            messageRed($_Lang['fl3_CantSpyYourself'], $ErrorTitle);
        }
        // TODO: This should be checked,
        // to prevent from other error states from being caught by this
        messageRed($_Lang['fl3_CantSpyProbesCount'], $ErrorTitle);
    }
    if ($Fleet['Mission'] == 7) {
        if ($Target['type'] != 1) {
            messageRed($_Lang['fl3_CantSettleNonPlanet'], $ErrorTitle);
        }
        if ($UsedPlanet) {
            messageRed($_Lang['fl3_CantSettleOnUsed'], $ErrorTitle);
        }
        // TODO: This should be checked,
        // to prevent from other error states from being caught by this
        messageRed($_Lang['fl3_CantSettleNoShips'], $ErrorTitle);
    }
    if ($Fleet['Mission'] == 8) {
        if ($Target['type'] != 2) {
            messageRed($_Lang['fl3_CantRecycleNonDebris'], $ErrorTitle);
        }
        // TODO: This should be checked,
        // to prevent from other error states from being caught by this
        messageRed($_Lang['fl3_CantRecycleNoShip'], $ErrorTitle);
    }
    if ($Fleet['Mission'] == 9) {
        if ($Target['type'] != 3) {
            messageRed($_Lang['fl3_CantDestroyNonMoon'], $ErrorTitle);
        }
        if (!$UsedPlanet) {
            messageRed($_Lang['fl3_CantDestroyNonUsed'], $ErrorTitle);
        }
        if ($YourPlanet) {
            messageRed($_Lang['fl3_CantDestroyYourself'], $ErrorTitle);
        }
        // TODO: This should be checked,
        // to prevent from other error states from being caught by this
        messageRed($_Lang['fl3_CantDestroyNoShip'], $ErrorTitle);
    }
    if ($Fleet['Mission'] == 15) {
        if (!isFeatureEnabled(\FeatureType::Expeditions)) {
            messageRed($_Lang['fl3_ExpeditionsAreOff'], $ErrorTitle);
        }
    }

    messageRed($_Lang['fl3_BadMissionSelected'], $ErrorTitle);
}

// --- If Mission is Recycling and there is no Debris Field, show Error
if($Fleet['Mission'] == 8)
{
    if($CheckDebrisField['metal'] <= 0 AND $CheckDebrisField['crystal'] <= 0)
    {
        messageRed($_Lang['fl3_NoDebrisFieldHere'], $ErrorTitle);
    }
}

if ($Fleet['Mission'] == 5) {
    $missionHoldValidationResult = FlightControl\Utils\Validators\validateMissionHold([
        'newFleet' => $Fleet,
    ]);

    if (!$missionHoldValidationResult['isValid']) {
        $firstValidationError = $missionHoldValidationResult['errors'][0];

        $errorMessage = null;
        switch ($firstValidationError['errorCode']) {
            case 'INVALID_HOLD_TIME':
                $errorMessage = $_Lang['fl3_Holding_BadTime'];
                break;
            default:
                $errorMessage = $_Lang['fleet_generic_errors_unknown'];
                break;
        }

        messageRed($errorMessage, $ErrorTitle);
    }
}

// --- Check if Expeditions and HoldingTimes are Correct
$Throw = false;
$Fleet['StayTime'] = 0;
if($Fleet['Mission'] == 15)
{
    if($Fleet['ExpeTime'] < 1)
    {
        $Throw = $_Lang['fl3_Expedition_Min1H'];
    }
    elseif($Fleet['ExpeTime'] > 12)
    {
        $Throw = $_Lang['fl3_Expedition_Max12H'];
    }
    $Fleet['StayTime'] = $Fleet['ExpeTime'] * 3600;
}
elseif($Fleet['Mission'] == 5)
{
    $Fleet['StayTime'] = $Fleet['HoldTime'] * 3600;
}
if($Throw)
{
    messageRed($Throw, $ErrorTitle);
}

// --- Set Variables to better usage
if(!empty($CheckDebrisField))
{
    $TargetData = &$CheckDebrisField;
}
else
{
    $TargetData = &$CheckPlanetOwner;
}

// --- Check if User data are OK
if($UsedPlanet AND !$YourPlanet AND !$PlanetAbandoned)
{
    $SaveMyTotalRank = false;

    $StatsData['his'] = ($TargetData['total_points'] > 0 ? $TargetData['total_points'] : 0);
    if(!CheckAuth('programmer'))
    {
        $StatsData['mine'] = ($_User['total_points'] > 0 ? $_User['total_points'] : 0);
    }
    else
    {
        $StatsData['mine'] = $StatsData['his'];
        if($_User['total_rank'] <= 0)
        {
            $SaveMyTotalRank = $_User['total_rank'];
            $_User['total_rank'] = $TargetData['total_rank'];
        }
    }

    if(isOnVacation($TargetData))
    {
        if($SaveMyTotalRank !== false)
        {
            $_User['total_rank'] = $SaveMyTotalRank;
        }
        if($TargetData['is_banned'] == 1)
        {
            messageRed($_Lang['fl3_CantSendBanned'], $ErrorTitle);
        }
        else
        {
            messageRed($_Lang['fl3_CantSendVacation'], $ErrorTitle);
        }
    }

    if($Protections['ally'] == 1)
    {
        if($_User['ally_id'] > 0 AND $_User['ally_id'] == $TargetData['ally_id'])
        {
            if(in_array($Fleet['Mission'], $Protections['mtypes']))
            {
                if($SaveMyTotalRank !== false)
                {
                    $_User['total_rank'] = $SaveMyTotalRank;
                }
                messageRed($_Lang['fl3_CantSendAlly'], $ErrorTitle);
            }
        }
    }

    if($Protections['enable'])
    {
        $Throw = false;
        $DoFarmCheck = false;
        if(in_array($Fleet['Mission'], $Protections['mtypes']))
        {
            if($_User['total_rank'] >= 1)
            {
                if($TargetData['total_rank'] >= 1)
                {
                    if($_User['NoobProtection_EndTime'] > $Now)
                    {
                        $Throw = sprintf($_Lang['fl3_ProtectNewTimeYou'], pretty_time($_User['NoobProtection_EndTime'] - $Now));
                    }
                    else if($TargetData['first_login'] == 0)
                    {
                        $Throw = $_Lang['fl3_ProtectNewTimeHe2'];
                    }
                    else if($TargetData['NoobProtection_EndTime'] > $Now)
                    {
                        $Throw = sprintf($_Lang['fl3_ProtectNewTimeHe'], pretty_time($TargetData['NoobProtection_EndTime'] - $Now));
                    }

                    if($Throw === false)
                    {
                        if($StatsData['mine'] >= $Protections['basicLimit'])
                        {
                            if($TargetData['onlinetime'] >= ($Now - $Protections['idleTime']))
                            {
                                if($StatsData['his'] < $Protections['basicLimit'])
                                {
                                    $Throw = sprintf($_Lang['fl3_ProtectHIWeak'], prettyNumber($Protections['basicLimit']));
                                }
                                else
                                {
                                    if($StatsData['his'] < $Protections['weakLimit'] OR $StatsData['mine'] < $Protections['weakLimit'])
                                    {
                                        if($StatsData['mine'] > ($StatsData['his'] * $Protections['weakMulti']))
                                        {
                                            $Throw = sprintf($_Lang['fl3_ProtectUR2Strong'], prettyNumber($Protections['weakMulti']));
                                        }
                                        elseif(($StatsData['mine'] * $Protections['weakMulti']) < $StatsData['his'])
                                        {
                                            $Throw = sprintf($_Lang['fl3_ProtectHI2Strong'], prettyNumber($Protections['weakMulti']));
                                        }
                                    }
                                    else
                                    {
                                        if($Protections['antifarm_enabled'] == true AND ($StatsData['mine'] / $StatsData['his']) >= $Protections['antifarm_rate'])
                                        {
                                            $DoFarmCheck = true;
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            $Throw = sprintf($_Lang['fl3_ProtectURWeak'], prettyNumber($Protections['basicLimit']));
                        }
                    }
                }
                else
                {
                    $Throw = $_Lang['fl3_ProtectHIStatNotCalc'];
                }
            }
            else
            {
                $Throw = $_Lang['fl3_ProtectURStatNotCalc'];
            }
            if($Protections['adminEnable'])
            {
                if(CheckAuth('supportadmin') OR CheckAuth('supportadmin', AUTHCHECK_NORMAL, $TargetData))
                {
                    if(CheckAuth('supportadmin'))
                    {
                        $Throw = $_Lang['fl3_ProtectAdminCant'];
                    }
                    else
                    {
                        $Throw = $_Lang['fl3_ProtectCantAdmin'];
                    }
                }
            }

            if(empty($Throw) AND ($DoFarmCheck === true OR $Protections['bashLimit_enabled'] === true))
            {
                if($DoFarmCheck === true)
                {
                    $TodayIs = explode('.', date('d.m.Y'));
                    $TodayTimestamp = mktime(0, 0, 0, $TodayIs[1], $TodayIs[0], $TodayIs[2]);
                    if($TodayTimestamp <= 0)
                    {
                        $TodayTimestamp = 0;
                    }
                    $BashTimestamps[] = array('type' => 'farm', 'key' => 'antifarm', 'stamp' => $TodayTimestamp);
                }
                if($Protections['bashLimit_enabled'] === true)
                {
                    $BashTimestamps[] = array('type' => 'bash', 'key' => 'bashLimit', 'stamp' => $Now - $Protections['bashLimit_interval']);
                }
                sort($BashTimestamps, SORT_ASC);
                $BashTimestampMinVal = $BashTimestamps[0]['stamp'];

                $excludedDestructionReasons = [
                    strval(Flights\Enums\FleetDestructionReason::INBATTLE_FIRSTROUND_NODAMAGE),
                    strval(Flights\Enums\FleetDestructionReason::DRAW_NOBASH),
                    strval(Flights\Enums\FleetDestructionReason::INBATTLE_OTHERROUND_NODAMAGE),
                ];
                $excludedDestructionReasonsStr = implode(', ', $excludedDestructionReasons);

                $SQLResult_GetFleetArchiveRecords = doquery(
                    "SELECT * " .
                    "FROM {{table}} " .
                    "WHERE " .
                    "(`Fleet_Time_Start` + `Fleet_Time_ACSAdd`) >= {$BashTimestampMinVal} AND " .
                    "`Fleet_Owner` = {$_User['id']} AND " .
                    "`Fleet_End_Owner` = {$TargetData['owner']} AND " .
                    "`Fleet_Mission` IN (1, 2, 9) AND " .
                    "`Fleet_ReportID` > 0 AND " .
                    "`Fleet_Destroyed_Reason` NOT IN ({$excludedDestructionReasonsStr}) " .
                    ";",
                    'fleet_archive'
                );

                if($SQLResult_GetFleetArchiveRecords->num_rows > 0)
                {
                    while($ArchiveRecord = $SQLResult_GetFleetArchiveRecords->fetch_assoc())
                    {
                        foreach($BashTimestamps as $Values)
                        {
                            if(($ArchiveRecord['Fleet_Time_Start'] + $ArchiveRecord['Fleet_Time_ACSAdd']) >= $Values['stamp'])
                            {
                                $GetEndID = $ArchiveRecord['Fleet_End_ID'];
                                if($ArchiveRecord['Fleet_End_ID_Changed'] > 0)
                                {
                                    $GetEndID = $ArchiveRecord['Fleet_End_ID_Changed'];
                                }
                                $SaveArchiveData[$Values['type']][$GetEndID] += 1;
                            }
                        }
                    }
                }

                foreach($BashTimestamps as $Values)
                {
                    if(!empty($SaveArchiveData))
                    {
                        $FleetArchiveRecordsCount = array_sum($SaveArchiveData[$Values['type']]);
                    }
                    if($FleetArchiveRecordsCount >= $Protections[$Values['key'].'_counttotal'])
                    {
                        $Throw = sprintf($_Lang['fl3_Protect_AttackLimitTotal'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
                        break;
                    }
                    elseif(($FleetArchiveRecordsCount + $fleetsInFlightCounters['aggressiveFleetsInFlight']['byTargetOwnerId'][$TargetData['owner']]) >= $Protections[$Values['key'].'_counttotal'])
                    {
                        $Throw = sprintf($_Lang['fl3_Protect_AttackLimitTotalFly'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
                        break;
                    }
                    elseif($SaveArchiveData[$Values['type']][$TargetData['id']] >= $Protections[$Values['key'].'_countplanet'])
                    {
                        $Throw = sprintf($_Lang['fl3_Protect_AttackLimitSingle'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
                        break;
                    }
                    elseif(($SaveArchiveData[$Values['type']][$TargetData['id']] + $fleetsInFlightCounters['aggressiveFleetsInFlight']['byTargetId'][$TargetData['id']]) >= $Protections[$Values['key'].'_countplanet'])
                    {
                        $Throw = sprintf($_Lang['fl3_Protect_AttackLimitSingleFly'], $_Lang['fl3_Protect_AttackLimit_'.$Values['key']]);
                        break;
                    }
                }
            }

            if($Throw)
            {
                if($SaveMyTotalRank !== false)
                {
                    $_User['total_rank'] = $SaveMyTotalRank;
                }
                messageRed($Throw, $ErrorTitle);
            }
        }
    }

    if($SaveMyTotalRank !== false)
    {
        $_User['total_rank'] = $SaveMyTotalRank;
    }
}

// --- Calculate Speed and Distance
$AllFleetSpeed = getFleetShipsSpeeds($Fleet['array'], $_User);
$GenFleetSpeed = $Fleet['Speed'];
$SpeedFactor = getUniFleetsSpeedFactor();
$MaxFleetSpeed = min($AllFleetSpeed);
if(MORALE_ENABLED)
{
    if($_User['morale_level'] <= MORALE_PENALTY_FLEETSLOWDOWN)
    {
        $MaxFleetSpeed *= MORALE_PENALTY_FLEETSLOWDOWN_VALUE;
    }
}

$Distance = getFlightDistanceBetween($_Planet, $Target);

$isUsingQuantumGate = false;
$quantumGateUseType = 0;

if ($Fleet['UseQuantum']) {
    $quantumGateValidationResult = FlightControl\Utils\Validators\validateQuantumGate([
        'fleet' => $Fleet,
        'originPlanet' => $_Planet,
        'targetPlanet' => $TargetData,
        'targetData' => $Target,
        'isTargetOccupied' => $UsedPlanet,
        'isTargetOwnPlanet' => $YourPlanet,
        'isTargetOwnedByFriend' => $OwnerFriend,
        'isTargetOwnedByFriendlyMerchant' => $OwnerHasMarcantilePact,
        'currentTimestamp' => $Now,
    ]);

    if (!$quantumGateValidationResult['isSuccess']) {
        $errorMessage = FlightControl\Utils\Errors\mapQuantumGateValidationErrorToReadableMessage(
            $quantumGateValidationResult['error']
        );

        messageRed($errorMessage, $ErrorTitle);
    } else {
        $isUsingQuantumGate = true;
        $quantumGateUseType = $quantumGateValidationResult['payload']['useType'];
    }
}

if($isUsingQuantumGate)
{
    if($quantumGateUseType == 1)
    {
        $DurationTarget = $DurationBack = 1;
        $Consumption = 0;
    }
    elseif($quantumGateUseType == 2)
    {
        $DurationTarget = 1;
        $DurationBack = getFlightDuration([
            'speedFactor' => $GenFleetSpeed,
            'distance' => $Distance,
            'maxShipsSpeed' => $MaxFleetSpeed
        ]);

        $Consumption = getFlightTotalConsumption(
            [
                'ships' => $Fleet['array'],
                'distance' => $Distance,
                'duration' => $DurationBack,
            ],
            $_User
        );
        $Consumption = $Consumption / 2;
    }
}
else
{
    $DurationTarget = $DurationBack = getFlightDuration([
        'speedFactor' => $GenFleetSpeed,
        'distance' => $Distance,
        'maxShipsSpeed' => $MaxFleetSpeed
    ]);

    $Consumption = getFlightTotalConsumption(
        [
            'ships' => $Fleet['array'],
            'distance' => $Distance,
            'duration' => $DurationTarget,
        ],
        $_User
    );
}

if($_Planet['deuterium'] < $Consumption)
{
    messageRed($_Lang['fl3_NoEnoughFuel'], $ErrorTitle);
}
if($Consumption > ($Fleet['storage'] + $Fleet['FuelStorage']))
{
    messageRed($_Lang['fl3_NoEnoughtStorage4Fuel'], $ErrorTitle);
}
if($_Planet['deuterium'] < ($Consumption + $Fleet['resources']['deuterium']))
{
    messageRed($_Lang['fl3_PlanetNoEnoughdeuterium'], $ErrorTitle);
}
if($Fleet['FuelStorage'] >= $Consumption)
{
    $Fleet['storage'] += $Consumption;
}
else
{
    $Fleet['storage'] += $Fleet['FuelStorage'];
}
$Fleet['storage'] -= $Consumption;
foreach($Fleet['resources'] as $Value)
{
    $Fleet['TotalResStorage'] += $Value;
}
if($Fleet['TotalResStorage'] > $Fleet['storage'])
{
    messageRed($_Lang['fl3_NoEnoughStorage4Res'], $ErrorTitle);
}

$Fleet['SetCalcTime'] = $Now + $DurationTarget;
$Fleet['SetStayTime'] = ($Fleet['StayTime'] > 0 ? $Fleet['SetCalcTime'] + $Fleet['StayTime'] : '0');
$Fleet['SetBackTime'] = $Fleet['SetCalcTime'] + $Fleet['StayTime'] + $DurationBack;

if(isset($UpdateACS))
{
    $NewEndTime = $Fleet['SetCalcTime'];
    $OldFlightTime = $CheckACS['start_time_org'] - $CheckACS['mf_start_time'];
    $FlightDifference = ($NewEndTime - $CheckACS['mf_start_time']) - $OldFlightTime;

    if($OldFlightTime == 0)
    {
        $OldFlightTime = 1;
    }
    if($FlightDifference == 0)
    {
        $FlightDifference = 1;
    }
    if(($FlightDifference/$OldFlightTime) <= 0.3)
    {
        if($NewEndTime < $CheckACS['start_time'])
        {
            $Difference = $CheckACS['start_time'] - $NewEndTime;
            $Fleet['SetCalcTime'] += $Difference;
            $Fleet['SetBackTime'] += $Difference;
        }
        elseif($NewEndTime > $CheckACS['start_time'])
        {
            $Difference = $NewEndTime - $CheckACS['start_time'];
            $UpdateACSRow[] = "`start_time` = `start_time` + {$Difference}";
            $UpdateACSFleets[] = "`fleet_start_time` = `fleet_start_time` + {$Difference}";
            $UpdateACSFleets[] = "`fleet_end_time` = `fleet_end_time` + {$Difference}";
        }
    }
    else
    {
        messageRed($_Lang['fl3_ACSFleet2Slow'], $ErrorTitle);
    }
}

if($isUsingQuantumGate AND $quantumGateUseType == 2)
{
    $Add2UpdatePlanet[] = "`quantumgate_lastuse` = {$Now}";
    $Add2UpdatePlanetPHP['quantumgate_lastuse'] = $Now;
}

// MultiAlert System
$SendAlert = false;
$IPIntersectionFound = 'false';
$IPIntersectionFiltred = 'false';
$IPIntersectionNow = 'false';
if($UsedPlanet AND !$YourPlanet AND !$PlanetAbandoned)
{
    include($_EnginePath.'includes/functions/AlertSystemUtilities.php');
    $CheckIntersection = AlertUtils_IPIntersect($_User['id'], $TargetData['owner'], array
    (
        'LastTimeDiff' => (TIME_DAY * 30),
        'ThisTimeDiff' => (TIME_DAY * 30),
        'ThisTimeStamp' => ($Now - SERVER_MAINOPEN_TSTAMP)
    ));
    if($CheckIntersection !== false)
    {
        $IPIntersectionFound = 'true';
        if($_User['user_lastip'] == $TargetData['lastip'])
        {
            $IPIntersectionNow = 'true';
        }

        if($_User['multiIP_DeclarationID'] > 0 AND $_User['multiIP_DeclarationID'] == $TargetData['multiIP_DeclarationID'])
        {
            $Query_CheckDeclaration = '';
            $Query_CheckDeclaration .= "SELECT `id` FROM {{table}} WHERE ";
            $Query_CheckDeclaration .= "`status` = 1 AND `id` = {$_User['multiIP_DeclarationID']} ";
            $Query_CheckDeclaration .= "LIMIT 1;";
            $CheckDeclaration = doquery($Query_CheckDeclaration, 'declarations', true);
            $DeclarationID = $CheckDeclaration['id'];
        }
        else
        {
            $DeclarationID = 0;
        }

        $alertFiltersSearchParams = FlightControl\Utils\Factories\createAlertFiltersSearchParams([
            'fleetOwner' => &$_User,
            'targetOwner' => $TargetData,
            'ipsIntersectionsCheckResult' => $CheckIntersection,
        ]);
        $FilterResult = AlertUtils_CheckFilters($alertFiltersSearchParams);

        if($FilterResult['FilterUsed'])
        {
            $IPIntersectionFiltred = 'true';
        }
        if(!$FilterResult['SendAlert'])
        {
            $DontSendAlert = true;
        }
        if(!$FilterResult['ShowAlert'])
        {
            $DontShowAlert = true;
        }

        if(!isset($DontSendAlert))
        {
            $SendAlert = true;
        }
        if(1 == 0 AND $DontShowAlert !== true)
        {
            // Currently there is no indicator that user wants to get MultiAlert Messages (do disable this code)
            $LockFleetSending = true;
            $ShowMultiAlert = true;
            $_Alert['MultiAlert']['Data']['Blocked'] = true;
        }
    }
}

if(!isset($LockFleetSending))
{
    $FleetArray = array();
    foreach($Fleet['array'] as $ShipID => $ShipCount)
    {
        $FleetArray[] = "{$ShipID},{$ShipCount}";
    }
    $FleetArrayCopy = $Fleet['array'];
    $Fleet['array'] = implode(';', $FleetArray);

    if(empty($TargetData['id']))
    {
        $TargetData['id'] = '0';
    }
    if(empty($TargetData['owner']))
    {
        $TargetData['owner'] = '0';
    }
    if(empty($TargetData['galaxy_id']))
    {
        $TargetData['galaxy_id'] = '0';
    }

    $Query_Insert = '';
    $Query_Insert .= "INSERT INTO {{table}} SET ";
    $Query_Insert .= "`fleet_owner` = {$_User['id']}, ";
    $Query_Insert .= "`fleet_mission` = {$Fleet['Mission']}, ";
    $Query_Insert .= "`fleet_amount` = {$Fleet['count']}, ";
    $Query_Insert .= "`fleet_array` = '{$Fleet['array']}', ";
    $Query_Insert .= "`fleet_start_time` = {$Fleet['SetCalcTime']}, ";
    $Query_Insert .= "`fleet_start_id` = {$_Planet['id']}, ";
    $Query_Insert .= "`fleet_start_galaxy` = {$_Planet['galaxy']}, ";
    $Query_Insert .= "`fleet_start_system` = {$_Planet['system']}, ";
    $Query_Insert .= "`fleet_start_planet` = {$_Planet['planet']}, ";
    $Query_Insert .= "`fleet_start_type` = {$_Planet['planet_type']}, ";
    $Query_Insert .= "`fleet_end_time` = {$Fleet['SetBackTime']}, ";
    $Query_Insert .= "`fleet_end_id` = {$TargetData['id']}, ";
    $Query_Insert .= "`fleet_end_id_galaxy` = {$TargetData['galaxy_id']}, ";
    $Query_Insert .= "`fleet_end_stay` = {$Fleet['SetStayTime']}, ";
    $Query_Insert .= "`fleet_end_galaxy` = {$Target['galaxy']}, ";
    $Query_Insert .= "`fleet_end_system` = {$Target['system']}, ";
    $Query_Insert .= "`fleet_end_planet` = {$Target['planet']}, ";
    $Query_Insert .= "`fleet_end_type` = {$Target['type']}, ";
    $Query_Insert .= "`fleet_resource_metal` = {$Fleet['resources']['metal']}, ";
    $Query_Insert .= "`fleet_resource_crystal` = {$Fleet['resources']['crystal']}, ";
    $Query_Insert .= "`fleet_resource_deuterium` = {$Fleet['resources']['deuterium']}, ";
    $Query_Insert .= "`fleet_target_owner` = '{$TargetData['owner']}', ";
    $Query_Insert .= "`fleet_send_time` = {$Now};";
    doquery($Query_Insert, 'fleets');

    $LastFleetID = doquery("SELECT LAST_INSERT_ID() as `id`;", '', true);
    $LastFleetID = $LastFleetID['id'];

    // PushAlert
    if($UsedPlanet AND !$YourPlanet AND !$PlanetAbandoned)
    {
        if($Fleet['Mission'] == 3)
        {
            if($StatsData['mine'] < $StatsData['his'])
            {
                if(!empty($Fleet['resources']))
                {
                    foreach($Fleet['resources'] as $ThisValue)
                    {
                        if($ThisValue > 0)
                        {
                            $_Alert['PushAlert']['HasResources'] = true;
                            break;
                        }
                    }
                }
                if ($_Alert['PushAlert']['HasResources'] === true) {
                    $alertFiltersSearchParams = FlightControl\Utils\Factories\createAlertFiltersSearchParams([
                        'fleetOwner' => &$_User,
                        'targetOwner' => $TargetData,
                        'ipsIntersectionsCheckResult' => null,
                    ]);
                    $FilterResult = AlertUtils_CheckFilters(
                        $alertFiltersSearchParams,
                        [
                            'DontLoad' => true,
                            'DontLoad_OnlyIfCacheEmpty' => true,
                        ]
                    );

                    if($FilterResult['SendAlert'])
                    {
                        $_Alert['PushAlert']['Data']['TargetUserID'] = $TargetData['owner'];
                        if($_User['ally_id'] > 0 AND $_User['ally_id'] == $TargetData['ally_id'])
                        {
                            $_Alert['PushAlert']['Data']['SameAlly'] = $TargetData['ally_id'];
                        }
                        else if($OwnerIsAlliedUser === true)
                        {
                            $_Alert['PushAlert']['Data']['AllyPact'] = array
                            (
                                'SenderAlly' => $_User['ally_id'],
                                'TargetAlly' => $TargetData['ally_id'],
                            );
                        }
                        if($OwnerIsBuddyFriend === true)
                        {
                            $_Alert['PushAlert']['Data']['BuddyFriends'] = true;
                        }
                        $_Alert['PushAlert']['Data']['FleetID'] = $LastFleetID;
                        $_Alert['PushAlert']['Data']['Stats']['Sender'] = array('Points' => $StatsData['mine'], 'Position' => $_User['total_rank']);
                        $_Alert['PushAlert']['Data']['Stats']['Target'] = array('Points' => $StatsData['his'], 'Position' => $TargetData['total_rank']);
                        $_Alert['PushAlert']['Data']['Resources'] = array
                        (
                            'Metal' => floatval($Fleet['resources']['metal']),
                            'Crystal' => floatval($Fleet['resources']['crystal']),
                            'Deuterium' => floatval($Fleet['resources']['deuterium'])
                        );

                        Alerts_Add(1, $Now, 5, 4, 5, $_User['id'], $_Alert['PushAlert']['Data']);
                    }
                }
            }
        }
    }
}

if($SendAlert)
{
    $_Alert['MultiAlert']['Importance'] = 10;
    $_Alert['MultiAlert']['Data']['MissionID'] = $Fleet['Mission'];
    if($LastFleetID > 0)
    {
        $_Alert['MultiAlert']['Data']['FleetID'] = $LastFleetID;
    }
    $_Alert['MultiAlert']['Data']['TargetUserID'] = $TargetData['owner'];
    foreach($CheckIntersection['Intersect'] as $ThisIPID)
    {
        $_Alert['MultiAlert']['Data']['Intersect'][] = array
        (
            'IPID' => $ThisIPID,
            'SenderData' => $CheckIntersection['IPLogData'][$_User['id']][$ThisIPID],
            'TargetData' => $CheckIntersection['IPLogData'][$TargetData['owner']][$ThisIPID]
        );
    }
    if($DeclarationID > 0)
    {
        $_Alert['MultiAlert']['Data']['DeclarationID'] = $DeclarationID;
        $_Alert['MultiAlert']['Type'] = 2;
    }
    else
    {
        $_Alert['MultiAlert']['Type'] = 1;
    }

    $Query_AlertOtherUsers = '';
    $Query_AlertOtherUsers .= "SELECT DISTINCT `User_ID` FROM {{table}} WHERE ";
    $Query_AlertOtherUsers .= "`User_ID` NOT IN ({$_User['id']}, {$TargetData['owner']}) AND ";
    $Query_AlertOtherUsers .= "`IP_ID` IN (".implode(', ', $CheckIntersection['Intersect']).") AND ";
    $Query_AlertOtherUsers .= "`Count` > `FailCount`;";
    $Result_AlertOtherUsers = doquery($Query_AlertOtherUsers, 'user_enterlog');
    if($Result_AlertOtherUsers->num_rows > 0)
    {
        while($FetchData = $Result_AlertOtherUsers->fetch_assoc())
        {
            $_Alert['MultiAlert']['Data']['OtherUsers'][] = $FetchData['User_ID'];
        }
    }

    Alerts_Add(1, $Now, $_Alert['MultiAlert']['Type'], 1, $_Alert['MultiAlert']['Importance'], $_User['id'], $_Alert['MultiAlert']['Data']);
}

if(isset($ShowMultiAlert))
{
    messageRed($_Lang['MultiAlert'], $_Lang['fl_error']);
}

if(isset($UpdateACS))
{
    if(!empty($CheckACS['fleets_id']))
    {
        $NewFleetsID[] = $CheckACS['fleets_id'];
    }
    $NewFleetsID[] = '|'.$LastFleetID.'|';
    $UpdateACSRow[] = "`fleets_id` = '".implode(',', $NewFleetsID)."'";

    if(!empty($CheckACS['user_joined']))
    {
        if(strstr($CheckACS['user_joined'], '|'.$_User['id'].'|') === FALSE)
        {
            $NewUsers[] = $CheckACS['user_joined'];
            $NewUsers[] = '|'.$_User['id'].'|';
            $UpdateACSRow[] = "`user_joined` = '".implode(',', $NewUsers)."'";
        }
    }
    else
    {
        $UpdateACSRow[] = "`user_joined` = '|{$_User['id']}|'";
    }

    $UpdateACSRow[] = "`fleets_count` = `fleets_count` + 1";

    if(!empty($UpdateACSRow))
    {
        doquery("UPDATE {{table}} SET ".implode(', ', $UpdateACSRow)." WHERE `id` = {$Fleet['ACS_ID']};", 'acs');
    }

    if(!empty($UpdateACSFleets))
    {
        $Fleets = $CheckACS['main_fleet_id'];
        if(!empty($CheckACS['fleets_id']))
        {
            $Fleets .= ','.str_replace('|', '', $CheckACS['fleets_id']);
        }
        doquery("UPDATE {{table}} SET ".implode(', ', $UpdateACSFleets)." WHERE `fleet_id` IN ({$Fleets});", 'fleets');
    }
}

if($isUsingQuantumGate)
{
    $QuantumGate_Used = '1';
}
else
{
    $QuantumGate_Used = '0';
}
$QryArchive = '';
$QryArchive .= "INSERT INTO {{table}} (`Fleet_ID`, `Fleet_Owner`, `Fleet_Mission`, `Fleet_Array`, `Fleet_Time_Send`, `Fleet_Time_Start`, `Fleet_Time_Stay`, `Fleet_Time_End`, `Fleet_Start_ID`, `Fleet_Start_Galaxy`, `Fleet_Start_System`, `Fleet_Start_Planet`, `Fleet_Start_Type`, `Fleet_Start_Res_Metal`, `Fleet_Start_Res_Crystal`, `Fleet_Start_Res_Deuterium`, `Fleet_End_ID`, `Fleet_End_ID_Galaxy`, `Fleet_End_Galaxy`, `Fleet_End_System`, `Fleet_End_Planet`, `Fleet_End_Type`, `Fleet_End_Owner`, `Fleet_ACSID`, `Fleet_Info_HadSameIP_Ever`, `Fleet_Info_HadSameIP_Ever_Filtred`, `Fleet_Info_HadSameIP_OnSend`, `Fleet_Info_UsedTeleport`) VALUES ";
$QryArchive .= " ({$LastFleetID}, {$_User['id']}, {$Fleet['Mission']}, '{$Fleet['array']}', {$Now}, {$Fleet['SetCalcTime']}, {$Fleet['SetStayTime']}, {$Fleet['SetBackTime']}, {$_Planet['id']}, {$_Planet['galaxy']}, {$_Planet['system']}, {$_Planet['planet']}, {$_Planet['planet_type']}, {$Fleet['resources']['metal']}, {$Fleet['resources']['crystal']}, {$Fleet['resources']['deuterium']}, '{$TargetData['id']}', '{$TargetData['galaxy_id']}', {$Target['galaxy']}, {$Target['system']}, {$Target['planet']}, {$Target['type']}, '{$TargetData['owner']}', '{$Fleet['ACS_ID']}', {$IPIntersectionFound}, {$IPIntersectionFiltred}, {$IPIntersectionNow}, {$QuantumGate_Used}) ";

if(!empty($UpdateACSFleets))
{
    $UpdateACSFleetsIDs = explode(',', str_replace('|', '', $CheckACS['fleets_id']));
    $UpdateACSFleetsIDs[] = $CheckACS['main_fleet_id'];
    if(!empty($UpdateACSFleetsIDs))
    {
        $QryArchive .= ', ';
        foreach($UpdateACSFleetsIDs as $FleetID)
        {
            if(!empty($FleetID))
            {
                $QryArchiveA[] = "({$FleetID}, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
            }
        }
        $QryArchive .= implode(', ',$QryArchiveA);
        $QryArchive .= " ON DUPLICATE KEY UPDATE ";
        $QryArchive .= "`Fleet_Time_ACSAdd` = `Fleet_Time_ACSAdd` + {$Difference}";
    }
}
doquery($QryArchive, 'fleet_archive');

$_Planet['metal'] -= $Fleet['resources']['metal'];
$_Planet['crystal'] -= $Fleet['resources']['crystal'];
$_Planet['deuterium'] -= ($Fleet['resources']['deuterium'] + $Consumption);

$_Lang['ShipsRows'] = '';
foreach($FleetArrayCopy as $ShipID => $ShipCount)
{
    $_Planet[$_Vars_GameElements[$ShipID]] -= $ShipCount;
    $_Lang['ShipsRows'] .= '<tr><th class="pad">'.$_Lang['tech'][$ShipID].'</th><th class="pad">'.prettyNumber($ShipCount).'</th></tr>';
}
if(!empty($Add2UpdatePlanetPHP))
{
    foreach($Add2UpdatePlanetPHP as $Key => $Value)
    {
        $_Planet[$Key] = $Value;
    }
}

$QryUpdatePlanet = '';
$QryUpdatePlanet .= "UPDATE {{table}} SET ";
$QryUpdatePlanet .= implode(', ', $FleetRemover).', ';
$QryUpdatePlanet .= "`metal` = '{$_Planet['metal']}', ";
$QryUpdatePlanet .= "`crystal` = '{$_Planet['crystal']}', ";
$QryUpdatePlanet .= "`deuterium` = '{$_Planet['deuterium']}' ";
if(!empty($Add2UpdatePlanet))
{
    $QryUpdatePlanet .= ", ".implode(', ', $Add2UpdatePlanet);
}
$QryUpdatePlanet .= " WHERE ";
$QryUpdatePlanet .= "`id` = {$_Planet['id']};";

doquery('LOCK TABLE {{table}} WRITE', 'planets');
doquery($QryUpdatePlanet, 'planets');
doquery('UNLOCK TABLES', '');

$UserDev_Log[] = FlightControl\Utils\Factories\createFleetDevLogEntry([
    'currentPlanet' => &$_Planet,
    'newFleetId' => $LastFleetID,
    'timestamp' => $Now,
    'fleetData' => $Fleet,
    'fuelUsage' => $Consumption,
]);

// ---

$_Lang['FleetMission'] = $_Lang['type_mission'][$Fleet['Mission']];
$_Lang['FleetDistance'] = prettyNumber($Distance);
$_Lang['FleetSpeed'] = prettyNumber($MaxFleetSpeed);
$_Lang['FleetFuel'] = prettyNumber($Consumption);
$_Lang['StartGalaxy'] = $_Planet['galaxy'];
$_Lang['StartSystem'] = $_Planet['system'];
$_Lang['StartPlanet'] = $_Planet['planet'];
$_Lang['StartType'] = ($_Planet['planet_type'] == 1 ? 'planet' : ($_Planet['planet_type'] == 3 ? 'moon' : 'debris'));
$_Lang['TargetGalaxy'] = $Target['galaxy'];
$_Lang['TargetSystem'] = $Target['system'];
$_Lang['TargetPlanet'] = $Target['planet'];
$_Lang['TargetType'] = ($Target['type'] == 1 ? 'planet' : ($Target['type'] == 3 ? 'moon' : 'debris'));
$_Lang['FleetStartTime'] = prettyDate('d m Y H:i:s', $Fleet['SetCalcTime'], 1);
$_Lang['FleetEndTime'] = prettyDate('d m Y H:i:s', $Fleet['SetBackTime'], 1);
$_Lang['useQuickRes'] = ($_POST['useQuickRes'] == '1' ? 'true' : 'false');

display(parsetemplate(gettemplate('fleet3_body'), $_Lang), $_Lang['fl_title']);

?>
