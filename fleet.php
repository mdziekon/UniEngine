<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');
include($_EnginePath . 'modules/flightControl/_includes.php');

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
$ShipRowTPL             = gettemplate('fleet_srow');

$ShipRowTPL = str_replace(
    array('fl_fleetspeed', 'fl_selmax', 'fl_selnone'),
    array($_Lang['fl_fleetspeed'], $_Lang['fl_selmax'], $_Lang['fl_selnone']),
    $ShipRowTPL
);

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

if(isset($_POST['acsmanage']) && $_POST['acsmanage'] == 'open')
{
    $ACSMsgCol = 'red';
    $_Lang['P_HideACSBoxOnError'] = $Hide;
    $FleetID = intval($_POST['fleet_id']);
    $_Lang['FleetID'] = $FleetID;
    if($FleetID > 0)
    {
        $QryGetFleet4ACSFields = '`fleet`.*, `planet`.`name` as `fleet_end_target_name`';
        $QryGetFleet4ACS = "SELECT {$QryGetFleet4ACSFields} FROM {{table}} AS `fleet` LEFT JOIN {{prefix}}planets AS `planet` ON `planet`.`id` = `fleet`.`fleet_end_id` WHERE `fleet`.`fleet_id` = {$FleetID} LIMIT 1;";
        $Fleet4ACS = doquery($QryGetFleet4ACS, 'fleets', true);
        if($Fleet4ACS['fleet_id'] == $FleetID AND $Fleet4ACS['fleet_owner'] == $_User['id'])
        {
            if($Fleet4ACS['fleet_mission'] == 1 AND $Fleet4ACS['fleet_mess'] == 0)
            {
                if($Fleet4ACS['fleet_start_time'] > $Now)
                {
                    $_Lang['P_HideACSBoxOnError'] = '';

                    $GetACSRow = doquery("SELECT * FROM {{table}} WHERE `main_fleet_id` = {$FleetID} LIMIT 1;", 'acs', true);
                    if($GetACSRow['id'] <= 0)
                    {
                        $ACSJustCreated = true;

                        $CreateACSName = substr($_User['username'].' '.date('d.m.Y H:i', $Now), 0, 50);

                        $newUnionEntry = FlightControl\Utils\Updaters\createUnionEntry([
                            'unionName' => $CreateACSName,
                            'mainFleetEntry' => $Fleet4ACS,
                        ]);

                        $GetACSRow = $newUnionEntry;

                        FlightControl\Utils\Updaters\updateFleetArchiveAcsId([
                            'fleetId' => $FleetID,
                            'newAcsId' => $newUnionEntry['id'],
                        ]);

                        if(strstr($_Lang['FlyingFleetsRows'], 'AddACSJoin_') !== false)
                        {
                            $_Lang['FlyingFleetsRows'] = str_replace('{AddACSJoin_'.$FleetID.'}', "<input type=\"radio\" value=\"{$newUnionEntry['id']}\" class=\"setACS_ID pad5\" name=\"acs_select\"><br/>{$_Lang['fl_acs_joinnow']}", $_Lang['FlyingFleetsRows']);
                        }
                    }

                    $JSACSUsers[$_User['id']] = [
                        'name' => $_User['username'],
                        'status' => $_Lang['fl_acs_leader'],
                        'canmove' => false,
                        'isIgnoredWhenUpdating' => true,
                        'place' => 1,
                    ];

                    $invitablePlayers = FlightControl\Utils\Fetchers\fetchUnionInvitablePlayers([
                        'userId' => $_User['id'],
                        'allianceId' => $_User['ally_id'],
                    ]);

                    foreach ($invitablePlayers as $invitablePlayer) {
                        $playerId = $invitablePlayer['id'];

                        $InvitableUsers[$playerId] = $invitablePlayer;
                        $JSACSUsers[$playerId] = [
                            'name' => $invitablePlayer['username'],
                            'status' => '',
                            'canmove' => true,
                            'isIgnoredWhenUpdating' => false,
                            'place' => 2,
                        ];
                    }

                    if (!isset($ACSJustCreated) || $ACSJustCreated !== true) {
                        $unionMembersDetails = FlightControl\Utils\Helpers\extractUnionMembersDetails([
                            'unionData' => $GetACSRow,
                            'invitablePlayers' => $invitablePlayers,
                        ]);

                        foreach ($unionMembersDetails as $memberId => $memberDetails) {
                            if (empty($JSACSUsers[$memberId])) {
                                $JSACSUsers[$memberId] = [];
                            }

                            $JSACSUsers[$memberId] = array_merge(
                                $JSACSUsers[$memberId],
                                $memberDetails
                            );
                        }

                        $currentUnionJoinedMembers = array_filter($JSACSUsers, function ($unionMember) use (&$_Lang) {
                            return $unionMember['status'] == $_Lang['fl_acs_joined'];
                        });
                        $currentUnionJoinedMembers = array_keys($currentUnionJoinedMembers);

                        if(!empty($_POST['acs_name']))
                        {
                            $NewName = trim($_POST['acs_name']);
                            $NewName = preg_replace('#[^a-zA-Z'.REGEXP_POLISHSIGNS.'0-9\_\-\.\ \:]#si', '', $NewName);
                            if($NewName != $GetACSRow['name'])
                            {
                                if(strlen($NewName) > 3)
                                {
                                    doquery("UPDATE {{table}} SET `name` = '{$NewName}' WHERE `id` = {$GetACSRow['id']};", 'acs');
                                    $GetACSRow['name'] = $NewName;
                                    $ACSMsgCol = 'lime';
                                    $ACSMsg = $_Lang['fl_acs_changesSaved'];
                                }
                                else
                                {
                                    $ACSMsgCol = 'red';
                                    $ACSMsg = $_Lang['fl_acs_error_shortname'];
                                }
                            }
                        }
                        if (
                            isset($_POST['acsuserschanged']) &&
                            $_POST['acsuserschanged'] == '1' &&
                            !empty($_POST['acs_users'])
                        ) {
                            $newUnionMembersStatesResult = FlightControl\Utils\Helpers\extractUnionMembersModification([
                                'input' => [
                                    'invitedUsersList' => $_POST['acs_users'],
                                ],
                                'invitableUsers' => $invitablePlayers,
                                'currentUnionMembers' => $JSACSUsers,
                                'currentUnionJoinedMembers' => $currentUnionJoinedMembers,
                            ]);

                            if (
                                $newUnionMembersStatesResult['isSuccess'] &&
                                FlightControl\Utils\Helpers\hasUnionMembersStateChanged([
                                    'newUnionMembersStates' => $newUnionMembersStatesResult['payload']['newUnionMembersStates']
                                ])
                            ) {
                                $newUnionMembersStates = $newUnionMembersStatesResult['payload']['newUnionMembersStates'];

                                $unionMembersToSendInvite = FlightControl\Utils\Helpers\getUnionMembersToSendInvite([
                                    'newUnionMembersStates' => $newUnionMembersStates,
                                ]);

                                FlightControl\Utils\Helpers\updateUnionMembersInMemory([
                                    'newUnionMembersStates' => $newUnionMembersStates,
                                    'currentUnionMembers' => &$JSACSUsers,
                                ]);

                                $newInvitedUsersList = [];

                                foreach ($newUnionMembersStates as $memberId => $newMemberState) {
                                    if ($newMemberState['newMemberPlacement'] !== 'INVITED_USERS') {
                                        continue;
                                    }

                                    $newInvitedUsersList[] = "|{$memberId}|";
                                }

                                $newInvitedUsersListString = implode(',', $newInvitedUsersList);
                                $newInvitedUsersListCount = count($newInvitedUsersList);

                                doquery(
                                    "UPDATE {{table}} " .
                                    "SET " .
                                    "`users` = '{$newInvitedUsersListString}', " .
                                    "`invited_users` = {$newInvitedUsersListCount} " .
                                    "WHERE " .
                                    "`id` = {$GetACSRow['id']} " .
                                    ";",
                                    'acs'
                                );

                                if (!empty($unionMembersToSendInvite)) {
                                    $invitationMessage = FlightControl\Utils\Factories\createUnionInvitationMessage([
                                        'unionOwner' => $_User,
                                        'unionEntry' => $GetACSRow,
                                        'fleetEntry' => $Fleet4ACS,
                                    ]);

                                    Cache_Message($unionMembersToSendInvite, 0, '', 1, '007', '018', $invitationMessage);
                                }

                                $ACSMsgCol = 'lime';
                                $ACSMsg = $_Lang['fl_acs_changesSaved'];
                            }

                            if (!$newUnionMembersStatesResult['isSuccess']) {
                                switch ($newUnionMembersStatesResult['error']['code']) {
                                    case 'KICKING_JOINED_MEMBER':
                                        $ACSMsg = $_Lang['fl_acs_cantkick_joined'];
                                        break;
                                    case 'MOVING_UNMOVABLE_USER':
                                        // TODO: Add error message
                                        $ACSMsg = 'MOVING_UNMOVABLE_USER';
                                        break;
                                }
                            }
                        }
                    }

                    if (!empty($JSACSUsers)) {
                        $_Lang['InsertACSUsers'] = json_encode($JSACSUsers);
                        foreach ($JSACSUsers as $memberId => $memberDetails) {
                            $memberListOptionComponent = FlightControl\Components\UnionMembersListOption\render([
                                'memberId' => $memberId,
                                'memberDetails' => $memberDetails,
                            ]);
                            $listOptionType = $memberListOptionComponent['listOptionType'];

                            if (empty($_Lang[$listOptionType])) {
                                $_Lang[$listOptionType] = '';
                            }
                            $_Lang[$listOptionType] .= $memberListOptionComponent['componentHTML'];
                        }
                    }

                    if(empty($GetACSRow['name']))
                    {
                        $_Lang['ACSName'] = $_Lang['fl_acs_noname'];
                    }
                    else
                    {
                        $_Lang['ACSName'] = $GetACSRow['name'];
                    }
                }
                else
                {
                    $ACSMsg = $_Lang['fl_acs_timeup'];
                }
            }
            else
            {
                $ACSMsg = $_Lang['fl_acs_badmission'];
            }
        }
        else
        {
            $ACSMsg = $_Lang['fl_acs_noexist'];
        }
    }
    else
    {
        $ACSMsg = $_Lang['fl_acs_noid'];
    }

    if(!empty($ACSMsg))
    {
        $_Lang['P_HideACSMSG'] = '';
        $_Lang['P_ACSMSG'] = $ACSMsg;
        $_Lang['P_ACSMSGCOL']= $ACSMsgCol;
    }
    else
    {
        $_Lang['P_HideACSMSG'] = $Hide;
    }

    $_Lang['Insert_ACSForm'] = parsetemplate(gettemplate('fleet_acsform'), $_Lang);
}

$_Lang['FlyingFleetsRows'] = preg_replace('#\{AddACSJoin\_[0-9]+\}#si', '', $_Lang['FlyingFleetsRows']);

$_Lang['InsertJSShipSet'] = "var JSShipSet = false;";
if(!isPro())
{
    // Don't Allow to use this function to NonPro Players
    $_GET['quickres'] = 0;
}

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

    $transportShipIds = [ 217, 203, 202 ];

    $JSSetShipsCount = [];

    foreach ($transportShipIds as $shipId) {
        $shipPlanetKey = _getElementPlanetKey($shipId);
        $shipCapacity = getShipsStorageCapacity($shipId);

        $shipsNeeded = ceil($resourcesToLoad / $shipCapacity);
        $shipsToUse = (
            $shipsNeeded <= $_Planet[$shipPlanetKey] ?
            $shipsNeeded :
            $_Planet[$shipPlanetKey]
        );

        $JSSetShipsCount[$shipId] = ((string) $shipsToUse);

        $resourcesToLoad -= ($shipsToUse * $shipCapacity);

        if ($resourcesToLoad <= 0) {
            break;
        }
    }

    if (!empty($JSSetShipsCount)) {
        $jsShipsObject = json_encode($JSSetShipsCount);

        $_Lang['InsertJSShipSet'] = "var JSShipSet = {$jsShipsObject};\n";
    }
} else {
    $_Lang['P_SetQuickRes'] = '0';
}

if(isset($_POST['gobackUsed']))
{
    if(!empty($_POST['FleetArray']))
    {
        $PostFleet = explode(';', $_POST['FleetArray']);
        foreach($PostFleet as $Data)
        {
            if(!empty($Data))
            {
                $Data = explode(',', $Data);
                if(in_array($Data[0], $_Vars_ElementCategories['fleet']))
                {
                    $InsertShipCount[$Data[0]] = prettyNumber($Data[1]);
                }
            }
        }
    }
}

foreach($_Vars_ElementCategories['fleet'] as $ID)
{
    if($_Planet[$_Vars_GameElements[$ID]] > 0)
    {
        if(empty($_Vars_Prices[$ID]['engine']))
        {
            continue;
        }
        $ThisShip = array();

        $ThisShip['ID'] = $ID;
        $ThisShip['Speed'] = prettyNumber(getShipsCurrentSpeed($ID, $_User));
        $ThisShip['Name'] = $_Lang['tech'][$ID];
        $ThisShip['Count'] = prettyNumber($_Planet[$_Vars_GameElements[$ID]]);
        if($ID == 210)
        {
            $ShipsData['storage'][$ID] = 0;
        }
        else
        {
            $ShipsData['storage'][$ID] = $_Vars_Prices[$ID]['capacity'];
        }
        $ShipsData['count'][$ID] = $_Planet[$_Vars_GameElements[$ID]];

        $ThisShip['MaxCount'] = explode('.', sprintf('%f', floor($_Planet[$_Vars_GameElements[$ID]])));
        $ThisShip['MaxCount'] = (string)$ThisShip['MaxCount'][0];

        if(!empty($InsertShipCount[$ID]))
        {
            $ThisShip['InsertShipCount'] = $InsertShipCount[$ID];
        }
        else
        {
            $ThisShip['InsertShipCount'] = '0';
        }

        $_Lang['ShipsRow'] .= parsetemplate($ShipRowTPL, $ThisShip);
    }
}
$_Lang['Insert_ShipsData'] = json_encode(isset($ShipsData) ? $ShipsData : null);

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
