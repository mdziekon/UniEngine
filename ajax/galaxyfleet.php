<?php

define('INSIDE', true);

$_SetAccessLogPreFilename = 'ajax/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath.'common.php');

include($_EnginePath . 'modules/flightControl/_includes.php');

use UniEngine\Engine\Modules\Flights;
use UniEngine\Engine\Modules\FlightControl;

function CreateReturn($ReturnCode)
{
    global $Update, $ShipCount, $Galaxy, $System, $Planet, $Type, $ActualFleets, $Spy_Probes, $Recyclers, $Colonizers;
    if(empty($Update))
    {
        $Update = '0';
    }
    if(empty($Galaxy))
    {
        $Galaxy = '0';
    }
    if(empty($System))
    {
        $System = '0';
    }
    if(empty($Planet))
    {
        $Planet = '0';
    }
    if(empty($Type))
    {
        $Type = '0';
    }
    if(empty($ActualFleets))
    {
        $ActualFleets = '0';
    }
    safeDie($ReturnCode.';'.$Update.';'.prettyNumber($ShipCount).';'.$Galaxy.';'.$System.';'.$Planet.';'.$Type.'|'.$ActualFleets.','.prettyNumber($Spy_Probes).','.prettyNumber($Recyclers).','.prettyNumber($Colonizers));
}

if(!isLogged())
{
    CreateReturn('601');
}

if (!isUserAccountActivated($_User)) {
    CreateReturn('661');
}

$Galaxy        = (isset($_POST['galaxy']) ? intval($_POST['galaxy']) : 0);
$System        = (isset($_POST['system']) ? intval($_POST['system']) : 0);
$Planet        = (isset($_POST['planet']) ? intval($_POST['planet']) : 0);
$Type        = (isset($_POST['type']) ? intval($_POST['type']) : 0);
$Mission    = (isset($_POST['mission']) ? intval($_POST['mission']) : 0);
$Time        = time();
if($Mission != 6 AND $Mission != 7 AND $Mission != 8)
{
    CreateReturn('602');
}

$isValidCoordinate = Flights\Utils\Checks\isValidCoordinate([
    'coordinate' => [
        'galaxy' => $Galaxy,
        'system' => $System,
        'planet' => $Planet,
        'type' => $Type,
    ],
    'areExpeditionsExcluded' => true,
]);

if (!$isValidCoordinate['isValid']) {
    $errorCode = (
        (
            $isValidCoordinate['error']['code'] === 'OUT_OF_BOUNDS' &&
            $isValidCoordinate['error']['param'] === 'type'
        ) ?
            '604' :
            '603'
    );

    CreateReturn($errorCode);
}

$CurrentPlanet    = &$_Planet;
$FlyingFleets    = doquery("SELECT COUNT(`fleet_id`) as `Number` FROM {{table}} WHERE `fleet_owner` = '{$_User['id']}';", 'fleets', true);

$Recyclers        = $CurrentPlanet['recycler'];
$Spy_Probes        = $CurrentPlanet['espionage_probe'];
$Colonizers        = $CurrentPlanet['colony_ship'];
$ActualFleets    = $FlyingFleets['Number'];

if(MORALE_ENABLED)
{
    Morale_ReCalculate($_User, $Time);
}

$fleetSlotsCount = FlightControl\Utils\Helpers\getUserFleetSlotsCount([
    'user' => $_User,
    'timestamp' => $Time,
]);

if ($ActualFleets >= $fleetSlotsCount) {
    $Update = '1';
    CreateReturn('609');
}

switch($Mission)
{
    case 6:
    {
        //Spy
        if(!($Type == 1 OR $Type == 3))
        {
            CreateReturn('613');
        }

        $ShipID = 210;
        $ShipCount = $_User['settings_spyprobescount'];

        if($Type == 1)
        {
            $Query_GetTarget_Galaxy = 'id_planet';
        }
        else
        {
            $Query_GetTarget_Galaxy = 'id_moon';
        }
        $Query_GetTarget = '';
        $Query_GetTarget .= "SELECT `pl`.`id`, `pl`.`id_owner`, `galaxy`.`galaxy_id` FROM {{table}} AS `pl` ";
        $Query_GetTarget .= "LEFT JOIN `{{prefix}}galaxy` AS `galaxy` ON `pl`.`id` = `galaxy`.`{$Query_GetTarget_Galaxy}` ";
        $Query_GetTarget .= "WHERE `pl`.`galaxy` = {$Galaxy} AND `pl`.`system` = {$System} AND `pl`.`planet` = {$Planet} AND `pl`.`planet_type` = {$Type} ";
        $Query_GetTarget .= "LIMIT 1; -- GalaxyFleet|GetTarget";
        $TargetPlanet = doquery($Query_GetTarget, 'planets', true);

        $GalaxyRow['galaxy_id'] = $TargetPlanet['galaxy_id'];
        $TargetUser = $TargetPlanet['id_owner'];
        $TargetID = $TargetPlanet['id'];
        $TargetPlanetType = $Type;

        if($TargetID <= 0)
        {
            CreateReturn('614');
        }
        if($TargetUser == $_User['id'])
        {
            CreateReturn('615');
        }

        if($TargetUser > 0)
        {
            $Query_GetUser = '';
            $Query_GetUser .= "SELECT `usr`.`ally_id`, `usr`.`is_onvacation`, `usr`.`is_banned`, `usr`.`onlinetime`, `usr`.`authlevel`, `usr`.`first_login`, `usr`.`NoobProtection_EndTime`, ";
            $Query_GetUser .= "`stat`.`total_points`, `stat`.`total_rank` ";
            $Query_GetUser .= "FROM {{table}} as `usr` ";
            $Query_GetUser .= "LEFT JOIN `{{prefix}}statpoints` AS `stat` ON `stat`.`stat_type` = 1 AND `stat`.`id_owner` = `usr`.`id` ";
            $Query_GetUser .= "WHERE `id` = {$TargetUser} LIMIT 1;";
            $HeDBRec = doquery($Query_GetUser, 'users', true);

            $usersStats = FlightControl\Utils\Factories\createFleetUsersStatsData([
                'fleetOwner' => $_User,
                'targetOwner' => $HeDBRec,
            ]);

            $targetOwnerValidation = FlightControl\Utils\Validators\validateTargetOwner([
                'fleetEntry' => [
                    'Mission' => $Mission,
                ],
                'fleetOwner' => $_User,
                'targetOwner' => $HeDBRec,
                'usersStats' => $usersStats,
                'currentTimestamp' => $Time,
                // Note: should be safe to NOT pass this, as Spy mission is not bash-checked
                'targetInfo' => null,
                'fleetsInFlightCounters' => null,
            ]);

            if (!$targetOwnerValidation['isSuccess']) {
                $mapTargetOwnerValidationErrorsToAjaxErrorCodes = [
                    'TARGET_USER_BANNED'                    => '617',
                    'TARGET_USER_ON_VACATION'               => '618',
                    'TARGET_ALLY_PROTECTION'                => '616',
                    'ADMIN_CANNOT_BE_AGGRESSIVE'            => '623',
                    'ADMIN_IS_PROTECTED_AGAINST_AGGRESSION' => '625',
                    'NOOB_PROTECTION_VALIDATION_ERROR' => [
                        'ATTACKER_STATISTICS_UNAVAILABLE'                   => '631',
                        'TARGET_STATISTICS_UNAVAILABLE'                     => '630',
                        'ATTACKER_NOOBPROTECTION_ENDTIME_NOT_REACHED'       => '632',
                        'TARGET_NEVER_LOGGED_IN'                            => '634',
                        'TARGET_NOOBPROTECTION_ENDTIME_NOT_REACHED'         => '633',
                        'ATTACKER_NOOBPROTECTION_BASIC_LIMIT_NOT_REACHED'   => '620',
                        'TARGET_NOOBPROTECTION_BASIC_LIMIT_NOT_REACHED'     => '619',
                        'TARGET_NOOBPROTECTION_TOO_WEAK_BY_MULTIPLIER'      => '621',
                        'ATTACKER_NOOBPROTECTION_TOO_WEAK_BY_MULTIPLIER'    => '622',
                    ],
                    'BASH_PROTECTION_VALIDATION_ERROR' => null,
                ];

                $errorCode = $targetOwnerValidation['error']['code'];
                $returnCode = $mapTargetOwnerValidationErrorsToAjaxErrorCodes[$errorCode];

                if (is_array($returnCode)) {
                    $subValidatorErrorCode = $targetOwnerValidation['error']['params']['code'];
                    $returnCode = $mapTargetOwnerValidationErrorsToAjaxErrorCodes[$errorCode][$subValidatorErrorCode];
                }

                CreateReturn($returnCode);
            }
        }
        break;
    }
    case 8:
    {
        //Recycling
        $ShipID = 209;
        if($Type != 2)
        {
            CreateReturn('612');
        }

        $GalaxyRow = doquery("SELECT `galaxy_id`, `metal`,`crystal` FROM {{table}} WHERE `galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} LIMIT 1;", 'galaxy', true);
        if(!($GalaxyRow['metal'] > 0 OR $GalaxyRow['crystal'] > 0))
        {
            CreateReturn('611');
        }
        $ShipCount = ceil(($GalaxyRow['metal'] + $GalaxyRow['crystal']) / $_Vars_Prices[$ShipID]['capacity']);
        break;
    }
    case 7:
    {
        //Colonization
        $ShipID = 208;
        if($Type != 1)
        {
            CreateReturn('612');
        }

        $PlanetCheck = doquery("SELECT `id` FROM {{table}} WHERE `galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} AND `planet_type` = 1 LIMIT 1;", 'planets', true);
        if($PlanetCheck['id'] > 0)
        {
            CreateReturn('624');
        }
        $ShipCount = 1;
        break;
    }
}

$smartFleetsBlockadeStateValidationResult = FlightControl\Utils\Validators\validateSmartFleetsBlockadeState([
    'timestamp' => $Time,
    'fleetData' => [
        'Mission' => $Mission,
    ],
    'fleetOwnerDetails' => [
        'userId' => $_User['id'],
        'planetId' => $CurrentPlanet['id'],
    ],
    'targetOwnerDetails' => (
        $TargetUser > 0 ?
        [
            'userId' => $TargetUser,
            'planetId' => $TargetID,
            'onlinetime' => $HeDBRec['onlinetime'],
        ] :
        null
    ),
    'settings' => [
        'idleTime' => getIdleProtectionTimeLimit(),
    ],
]);

if (!$smartFleetsBlockadeStateValidationResult['isValid']) {
    $firstValidationError = $smartFleetsBlockadeStateValidationResult['errors'];

    $errorMessage = null;
    switch ($firstValidationError['blockType']) {
        case 'GLOBAL_ENDTIME':
            CreateReturn('628');

            break;
        case 'GLOBAL_POSTENDTIME':
            CreateReturn('635');

            break;
        case 'USER':
            $errorDetails = $firstValidationError['details'];

            if ($errorDetails['userId'] == $_User['id']) {
                CreateReturn('636');
            }

            CreateReturn('637');

            break;
        case 'PLANET':
            $errorDetails = $firstValidationError['details'];

            if ($errorDetails['planetId'] == $CurrentPlanet['id']) {
                CreateReturn(
                    ($CurrentPlanet['planet_type'] == 1) ?
                        '638' :
                        '639'
                );
            }

            CreateReturn(
                ($TargetPlanetType == 1) ?
                    '640' :
                    '641'
            );

            break;
        default:
            CreateReturn('694');
            break;
    }
}

if($ShipCount < 0)
{
    CreateReturn('605');
}
if($ShipCount == 0)
{
    $Update = 1;
    CreateReturn('610');
}
if($CurrentPlanet[$_Vars_GameElements[$ShipID]] <= 0)
{
    //No ships
    switch($Mission)
    {
        case 6:
        {
            //Spy
            $Return = '606_1';
            break;
        }
        case 8:
        {
            //Recycling
            $Return = '606_2';
            break;
        }
        case 7:
        {
            //Colonization
            $Return = '606_3';
            break;
        }
    }
    $Update = '1';
    CreateReturn($Return);
}

if($CurrentPlanet[$_Vars_GameElements[$ShipID]] < $ShipCount)
{
    $ShipCount = $CurrentPlanet[$_Vars_GameElements[$ShipID]];
}

$FleetArray = [
    $ShipID => $ShipCount
];

$availableSpeeds = FlightControl\Utils\Helpers\getAvailableSpeeds([
    'user' => &$_User,
    'timestamp' => $Time,
]);

reset($availableSpeeds);

$GenFleetSpeed = current($availableSpeeds);
$SpeedFactor = getUniFleetsSpeedFactor();

$slowestShipSpeed = FlightControl\Utils\Helpers\getSlowestShipSpeed([
    'shipsDetails' => getFleetShipsSpeeds($FleetArray, $_User),
    'user' => &$_User,
]);

$distance = getFlightDistanceBetween(
    $CurrentPlanet,
    [
        'galaxy' => $Galaxy,
        'system' => $System,
        'planet' => $Planet
    ]
);
$duration = getFlightDuration([
    'speedFactor' => $GenFleetSpeed,
    'distance' => $distance,
    'maxShipsSpeed' => $slowestShipSpeed
]);
$consumption = getFlightTotalConsumption(
    [
        'ships' => [
            $ShipID => $ShipCount
        ],
        'distance' => $distance,
        'duration' => $duration,
    ],
    $_User
);

$fleet['start_time'] = $duration + $Time;
$fleet['end_time'] = (2 * $duration) + $Time;

if ($CurrentPlanet['deuterium'] < $consumption) {
    CreateReturn('607');
}

$FleetStorage = $_Vars_Prices[$ShipID]['capacity'] * $ShipCount;
if($Mission == 6)
{
    // Try to SlowDown fleet only if it's Espionage Mission
    while($FleetStorage < $consumption)
    {
        $GenFleetSpeed = next($availableSpeeds);
        if($GenFleetSpeed !== false)
        {
            $duration = getFlightDuration([
                'speedFactor' => $GenFleetSpeed,
                'distance' => $distance,
                'maxShipsSpeed' => $slowestShipSpeed
            ]);
            $consumption = getFlightTotalConsumption(
                [
                    'ships' => [
                        $ShipID => $ShipCount
                    ],
                    'distance' => $distance,
                    'duration' => $duration,
                ],
                $_User
            );

            $fleet['start_time'] = $duration + $Time;
            $fleet['end_time'] = (2 * $duration) + $Time;
        }
        else
        {
            break;
        }
    }
}

if($FleetStorage >= $consumption)
{
    switch($Mission)
    {
        case 6: //Spy
            $TargetOwner = $TargetUser;
            break;
        case 8: //Recycling
            $TargetOwner = 0;
            break;
        case 7: //Colonization
            $TargetOwner = 0;
            break;
    }
}
else
{
    CreateReturn('608');
}

$fleetEntry = [
    'Mission' => $Mission,
    'count' => $ShipCount,
    'array' => $FleetArray,
    'SetCalcTime' => $fleet['start_time'],
    'SetStayTime' => '0',
    'SetBackTime' => $fleet['end_time'],
    'resources' => [
        'metal' => '0',
        'crystal' => '0',
        'deuterium' => '0',
    ],
];
$targetPlanet = [
    'id' => $TargetID,
    'galaxy_id' => $GalaxyRow['galaxy_id'],
    'ownerId' => $TargetOwner,
];
$targetCoords = [
    'galaxy' => $Galaxy,
    'system' => $System,
    'planet' => $Planet,
    'type' => $Type,
];

$createdFleetId = FlightControl\Utils\Updaters\insertFleetEntry([
    'ownerUser' => $_User,
    'ownerPlanet' => $CurrentPlanet,
    'fleetEntry' => $fleetEntry,
    'targetPlanet' => $targetPlanet,
    'targetCoords' => $targetCoords,
    'currentTime' => $Time,
]);

FlightControl\Utils\Updaters\insertFleetArchiveEntry([
    'fleetEntryId' => $createdFleetId,
    'ownerUser' => $_User,
    'ownerPlanet' => $CurrentPlanet,
    'fleetEntry' => $fleetEntry,
    'targetPlanet' => $targetPlanet,
    'targetCoords' => $targetCoords,
    'flags' => [
        'hasIpIntersection' => false,
        'hasIpIntersectionFiltered' => false,
        'hasIpIntersectionOnSend' => false,
        'hasUsedTeleportation' => false,
    ],
    'currentTime' => $Time,
]);

$CurrentPlanet['deuterium'] = $CurrentPlanet['deuterium'] - $consumption;

$QryUpdatePlanet = '';
$QryUpdatePlanet .= "UPDATE {{table}} SET ";
$QryUpdatePlanet .= "`{$_Vars_GameElements[$ShipID]}` = `{$_Vars_GameElements[$ShipID]}` - {$ShipCount}, ";
$QryUpdatePlanet .= "`deuterium` = '{$CurrentPlanet["deuterium"]}' ";
$QryUpdatePlanet .= "WHERE ";
$QryUpdatePlanet .= "`id` = '{$CurrentPlanet['id']}'";

doquery("LOCK TABLE {{table}} WRITE", 'planets');
doquery($QryUpdatePlanet, "planets");
doquery("UNLOCK TABLES", '');

// User Development Log
$devLogFleetArray = $FleetArray;

if ($consumption > 0) {
    $devLogFleetArray['F'] = $consumption;
}

$UserDev_Log[] = [
    'PlanetID' => $CurrentPlanet['id'],
    'Date' => $Time,
    'Place' => 9,
    'Code' => $Mission,
    'ElementID' => $createdFleetId,
    'AdditionalData' => Array2String($devLogFleetArray),
];

$ActualFleets += 1;
switch($Mission)
{
    case 6:
    {
        //Spy
        $Spy_Probes -= $ShipCount;
        $Return = '600_1'; //OK
        break;
    }
    case 8:
    {
        //Recycling
        $Recyclers -= $ShipCount;
        $Return = '600_2'; //OK
        break;
    }
    case 7:
    {
        //Colonization
        $Colonizers -= $ShipCount;
        $Return = '600_3'; //OK
        break;
    }
}
$Update = '1';
if(empty($Return))
{
    $Return = '694';
}
CreateReturn($Return);

?>
