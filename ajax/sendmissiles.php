<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_DontForceRulesAcceptance = true;

$_SetAccessLogPreFilename = 'ajax/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath . 'common.php');
include($_EnginePath . 'modules/flightControl/_includes.php');

use UniEngine\Engine\Modules\FlightControl;

function CreateReturn($ReturnCode, $Update = '0')
{
    global $_Planet, $FlyingFleets;

    $Missiles = prettyNumber($_Planet['interplanetary_missile']);
    safeDie("{$ReturnCode}|{$Update}|{$Missiles}|{$FlyingFleets}");
}

if(!isLogged())
{
    CreateReturn('601');
}

if (!isUserAccountActivated($_User)) {
    CreateReturn('661');
}

$Now = time();

$FlyingFleets = doquery("SELECT COUNT(`fleet_id`) as `Number` FROM {{table}} WHERE `fleet_owner` = '{$_User['id']}';", 'fleets', true);
$FlyingFleets = $FlyingFleets['Number'];

$MaxFleets = FlightControl\Utils\Helpers\getUserFleetSlotsCount([
    'user' => $_User,
    'timestamp' => $Now,
]);

if ($FlyingFleets >= $MaxFleets) {
    CreateReturn('609');
}

$protection = $_GameConfig['noobprotection'];
$adminprotection = $_GameConfig['adminprotection'];
$allyprotection = $_GameConfig['allyprotection'];
$Protections['idleTime'] = $_GameConfig['no_idle_protect'] * TIME_DAY;

$Galaxy = (isset($_POST['galaxy']) ? intval($_POST['galaxy']) : 0);
$System = (isset($_POST['system']) ? intval($_POST['system']) : 0);
$Planet = (isset($_POST['planet']) ? intval($_POST['planet']) : 0);
$Type = 1;

$Mission = 10;
$Missiles = (isset($_POST['count']) ? round(str_replace('.', '', $_POST['count'])) : 0);
$PrimTarget = (isset($_POST['target']) ? intval($_POST['target']) : 0);
if($PrimTarget == 0)
{
    $PrimTarget = '0';
}

$Dist = abs($System - $_Planet['system']);
include($_EnginePath.'includes/functions/GetMissileRange.php');
$MissilesRange = GetMissileRange();
$FlightTime = round(((30 + (60 * $Dist)) * 2500) / $_GameConfig['game_speed']);

if($Missiles <= 0)
{
    CreateReturn('651');
}
if($Galaxy <= 0 OR $Planet <= 0 OR $System <= 0 OR $PrimTarget < 0 OR $PrimTarget > 99)
{
    CreateReturn('652');
}
if($_Planet['missile_silo'] < 4)
{
    CreateReturn('649');
}
if($MissilesRange <= 0)
{
    CreateReturn('648');
}
if($Dist > $MissilesRange OR $Galaxy != $_Planet['galaxy'])
{
    CreateReturn('647');
}
if($Missiles > $_Planet['interplanetary_missile'])
{
    CreateReturn('646', '1');
}

$Query_GetPlanet = '';
$Query_GetPlanet .= "SELECT `pl`.`id`, `pl`.`id_owner`, `galaxy`.`galaxy_id` FROM {{table}} AS `pl` ";
$Query_GetPlanet .= "LEFT JOIN `{{prefix}}galaxy` AS `galaxy` ON `galaxy`.`id_planet` = `pl`.`id` ";
$Query_GetPlanet .= "WHERE `pl`.`galaxy` = {$Galaxy} AND `pl`.`system` = {$System} AND `pl`.`planet` = {$Planet} AND `pl`.`planet_type` = {$Type} ";
$Query_GetPlanet .= "LIMIT 1; -- SendMissiles|GetPlanet";
$PlanetData = doquery($Query_GetPlanet, 'planets', true);

if($PlanetData['id'] <= 0)
{
    CreateReturn('650');
}
if($PlanetData['id_owner'] == $_User['id'])
{
    CreateReturn('645');
}

if($PlanetData['id_owner'] > 0)
{
    $Query_GetUser = '';
    $Query_GetUser .= "SELECT `usr`.`is_onvacation`, `usr`.`is_banned`, `usr`.`ally_id`, `usr`.`first_login`, `usr`.`NoobProtection_EndTime`, `usr`.`onlinetime`, `usr`.`authlevel`, ";
    $Query_GetUser .= "`stat`.`total_points`, `stat`.`total_rank` ";
    $Query_GetUser .= "FROM {{table}} AS `usr` ";
    $Query_GetUser .= "LEFT JOIN `{{prefix}}statpoints` AS `stat` ON `stat`.`stat_type` = 1 AND `stat`.`id_owner` = `usr`.`id` ";
    $Query_GetUser .= "WHERE `usr`.`id` = {$PlanetData['id_owner']} LIMIT 1; -- SendMissiles|GetUser";
    $HeDBRec = doquery($Query_GetUser, 'users', true);

    if(isOnVacation($HeDBRec))
    {
        if($HeDBRec['is_banned'] == 1)
        {
            CreateReturn('642');
        }
        else
        {
            CreateReturn('643');
        }
    }
    if($allyprotection == 1)
    {
        if($_User['ally_id'] > 0 AND $_User['ally_id'] == $HeDBRec['ally_id'])
        {
            CreateReturn('644');
        }
    }

    if(!CheckAuth('programmer'))
    {
        $MyGameLevel = $_User['total_points'];
    }
    else
    {
        $MyGameLevel = $HeDBRec['total_points'];
        if($_User['total_rank'] <= 0)
        {
            $_User['total_rank'] = $HeDBRec['total_rank'];
        }
    }
    $HeGameLevel = $HeDBRec['total_points'];

    if ($protection == 1) {
        $noobProtectionValidationResult = FlightControl\Utils\Validators\validateNoobProtection([
            'attackerUser' => $_User,
            'attackerStats' => $MyGameLevel,
            'targetUser' => $HeDBRec,
            'targetStats' => $HeGameLevel,
            'currentTimestamp' => $Now,
        ]);

        if (!$noobProtectionValidationResult['isSuccess']) {
            $mapNoobProtectionErrorsToAjaxErrorCodes = [
                'ATTACKER_STATISTICS_UNAVAILABLE'                   => '663',
                'TARGET_STATISTICS_UNAVAILABLE'                     => '662',
                'ATTACKER_NOOBPROTECTION_ENDTIME_NOT_REACHED'       => '653',
                'TARGET_NEVER_LOGGED_IN'                            => '655',
                'TARGET_NOOBPROTECTION_ENDTIME_NOT_REACHED'         => '654',
                'ATTACKER_NOOBPROTECTION_BASIC_LIMIT_NOT_REACHED'   => '657',
                // TODO: This should have a separate error message
                'TARGET_NOOBPROTECTION_BASIC_LIMIT_NOT_REACHED'     => '656',
                'TARGET_NOOBPROTECTION_TOO_WEAK_BY_MULTIPLIER'      => '656',
                'ATTACKER_NOOBPROTECTION_TOO_WEAK_BY_MULTIPLIER'    => '658',
            ];

            $errorCode = $noobProtectionValidationResult['error']['code'];

            CreateReturn($mapNoobProtectionErrorsToAjaxErrorCodes[$errorCode]);
        }
    }
    if((CheckAuth('supportadmin') OR CheckAuth('supportadmin', AUTHCHECK_NORMAL, $HeDBRec)) AND $adminprotection == 1)
    {
        if(CheckAuth('supportadmin'))
        {
            CreateReturn('659');
        }
        else
        {
            CreateReturn('660');
        }
    }
}

$smartFleetsBlockadeStateValidationResult = FlightControl\Utils\Validators\validateSmartFleetsBlockadeState([
    'timestamp' => $Now,
    'fleetData' => [
        'Mission' => $Mission,
    ],
    'fleetOwnerDetails' => [
        'userId' => $_User['id'],
        'planetId' => $_Planet['id'],
    ],
    'targetOwnerDetails' => (
        $PlanetData['id_owner'] > 0 ?
        [
            'userId' => $PlanetData['id_owner'],
            'planetId' => $PlanetData['id'],
            'onlinetime' => $HeDBRec['onlinetime'],
        ] :
        null
    ),
    'settings' => [
        'idleTime' => $Protections['idleTime'],
    ],
]);

if (!$smartFleetsBlockadeStateValidationResult['isValid']) {
    CreateReturn('626');
}

$fleetEntry = [
    'Mission' => $Mission,
    'count' => $Missiles,
    'array' => [
        '503' => $Missiles,
        'primary_target' => $PrimTarget,
    ],
    'SetCalcTime' => ($Now + $FlightTime),
    'SetStayTime' => '0',
    'SetBackTime' => ($Now + $FlightTime),
    'resources' => [
        'metal' => '0',
        'crystal' => '0',
        'deuterium' => '0',
    ],
];
$targetPlanet = [
    'id' => $PlanetData['id'],
    'galaxy_id' => $PlanetData['galaxy_id'],
    'owner' => $PlanetData['id_owner'],
];
$targetCoords = [
    'galaxy' => $Galaxy,
    'system' => $System,
    'planet' => $Planet,
    'type' => "1",
];

$LastFleetID = FlightControl\Utils\Updaters\insertFleetEntry([
    'ownerUser' => $_User,
    'ownerPlanet' => $_Planet,
    'fleetEntry' => $fleetEntry,
    'targetPlanet' => $targetPlanet,
    'targetCoords' => $targetCoords,
    'currentTime' => $Now,
]);

doquery("UPDATE {{table}} SET `interplanetary_missile` = `interplanetary_missile` - {$Missiles} WHERE `id` = {$_Planet['id']};", 'planets');

FlightControl\Utils\Updaters\insertFleetArchiveEntry([
    'fleetEntryId' => $LastFleetID,
    'ownerUser' => $_User,
    'ownerPlanet' => $_Planet,
    'fleetEntry' => $fleetEntry,
    'targetPlanet' => $targetPlanet,
    'targetCoords' => $targetCoords,
    'flags' => [
        'hasIpIntersection' => false,
        'hasIpIntersectionFiltered' => false,
        'hasIpIntersectionOnSend' => false,
        'hasUsedTeleportation' => false,
    ],
    'currentTime' => $Now,
]);

// User Development Log
$UserDev_Log[] = array('PlanetID' => $_Planet['id'], 'Date' => $Now, 'Place' => 11, 'Code' => '0', 'ElementID' => $LastFleetID, 'AdditionalData' => 'R,'.$Missiles);
// ---

$FlyingFleets += 1;
$_Planet['interplanetary_missile'] -= $Missiles;
CreateReturn('600_4', 1);

?>
