<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\UnionManagement;

use UniEngine\Engine\Modules\FlightControl;
use UniEngine\Engine\Modules\FlightControl\Components\UnionManagement;

function _handleInput($props) {
    global $_Lang;

    $unionOwner = $props['unionOwner'];
    $userId = $unionOwner['id'];
    $currentTimestamp = $props['currentTimestamp'];
    $input = $props['input'];

    $baseUnionDataResult = UnionManagement\Utils\getBaseUnionData($props);

    if (!$baseUnionDataResult['isSuccess']) {
        return $baseUnionDataResult;
    }

    $baseUnionData = $baseUnionDataResult['payload'];
    $Fleet4ACS = $baseUnionData['unionMainFleet'];
    $inputFleetId = $baseUnionData['unionMainFleet']['fleet_id'];

    $result = [
        'isSuccess' => true,
        'payload' => [
            'message' => [
                'content' => null,
                'color' => '',
            ],
            'unionMembers' => null,
            'unionName' => null,
            'membersSelectors' => [],
            'newUnionEntry' => null,
        ],
    ];

    $GetACSRow = doquery("SELECT * FROM {{table}} WHERE `main_fleet_id` = {$inputFleetId} LIMIT 1;", 'acs', true);
    $newUnionEntry = null;

    if ($GetACSRow['id'] <= 0) {
        $createNewUnionResult = UnionManagement\Utils\createNewUnion([
            'mainFleet' => $Fleet4ACS,
            'unionOwner' => $unionOwner,
            'currentTimestamp' => $currentTimestamp,
        ]);
        $newUnionEntry = $createNewUnionResult['newUnionEntry'];

        $result['payload']['newUnionEntry'] = $newUnionEntry;
        $GetACSRow = $newUnionEntry;
    }

    $JSACSUsers = [];
    $JSACSUsers[$userId] = [
        'name' => $unionOwner['username'],
        'status' => $_Lang['fl_acs_leader'],
        'canmove' => false,
        'isIgnoredWhenUpdating' => true,
        'place' => 1,
    ];

    $invitablePlayers = FlightControl\Utils\Fetchers\fetchUnionInvitablePlayers([
        'userId' => $userId,
        'allianceId' => $unionOwner['ally_id'],
    ]);

    foreach ($invitablePlayers as $invitablePlayer) {
        $playerId = $invitablePlayer['id'];

        $JSACSUsers[$playerId] = [
            'name' => $invitablePlayer['username'],
            'status' => '',
            'canmove' => true,
            'isIgnoredWhenUpdating' => false,
            'place' => 2,
        ];
    }

    if (!$newUnionEntry) {
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

        // TODO: refactor, maybe move into an util?
        if (!empty($input['acs_name'])) {
            $NewName = trim($input['acs_name']);
            $NewName = preg_replace('#[^a-zA-Z'.REGEXP_POLISHSIGNS.'0-9\_\-\.\ \:]#si', '', $NewName);
            if ($NewName != $GetACSRow['name']) {
                if (strlen($NewName) > 3) {
                    doquery("UPDATE {{table}} SET `name` = '{$NewName}' WHERE `id` = {$GetACSRow['id']};", 'acs');
                    $GetACSRow['name'] = $NewName;

                    $ACSMsgCol = 'lime';
                    $ACSMsg = $_Lang['fl_acs_changesSaved'];
                } else {
                    $ACSMsgCol = 'red';
                    $ACSMsg = $_Lang['fl_acs_error_shortname'];
                }

                $result['payload']['message'] = [
                    'content' => $ACSMsg,
                    'color' => $ACSMsgCol,
                ];
            }
        }

        if (
            isset($input['acsuserschanged']) &&
            $input['acsuserschanged'] == '1' &&
            !empty($input['acs_users'])
        ) {
            $newUnionMembersStatesResult = FlightControl\Utils\Helpers\extractUnionMembersModification([
                'input' => [
                    'invitedUsersList' => $input['acs_users'],
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
                FlightControl\Utils\Updaters\updateUnionMembersInDB([
                    'unionId' => $GetACSRow['id'],
                    'newUnionMembersStates' => $newUnionMembersStates,
                ]);

                if (!empty($unionMembersToSendInvite)) {
                    $invitationMessage = FlightControl\Utils\Factories\createUnionInvitationMessage([
                        'unionOwner' => $unionOwner,
                        'unionEntry' => $GetACSRow,
                        'fleetEntry' => $Fleet4ACS,
                    ]);

                    Cache_Message($unionMembersToSendInvite, 0, '', 1, '007', '018', $invitationMessage);
                }

                $result['payload']['message'] = [
                    'content' => $_Lang['fl_acs_changesSaved'],
                    'color' => 'lime',
                ];
            }

            if (!$newUnionMembersStatesResult['isSuccess']) {
                switch ($newUnionMembersStatesResult['error']['code']) {
                    case 'KICKING_JOINED_MEMBER':
                        $ACSMsg = $_Lang['fl_acs_cantkick_joined'];
                        break;
                    case 'MOVING_UNMOVABLE_USER':
                        $ACSMsg = $_Lang['fl_acs_cant_move_user'];
                        break;
                }

                $result['payload']['message'] = [
                    'content' => $ACSMsg,
                    'color' => 'red',
                ];
            }
        }
    }

    $result['payload']['unionMembers'] = $JSACSUsers;
    $result['payload']['unionName'] = (
        empty($GetACSRow['name']) ?
            $_Lang['fl_acs_noname'] :
            $GetACSRow['name']
    );

    return $result;
}

//  Arguments
//      - $props (Object)
//          - unionOwner (Object)
//          - currentTimestamp (Number)
//          - input (Object)
//
//  Returns: Object
//      - componentHTML (String)
//
function render($props) {
    $input = $props['input'];

    $lang = includeLang('fleet', true);
    $localTemplateLoader = createLocalTemplateLoader(__DIR__);
    $tplBodyCache = [
        'body' => $localTemplateLoader('body'),
    ];

    $componentTPLData = [
        'FleetID' => $input['fleet_id'],
        'P_HideACSBoxOnError' => ' class="hide"',
        'P_HideACSMSG' => ' class="hide"',
        'P_ACSMSG' => '',
        'P_ACSMSGCOL' => '',
    ];

    $inputHandlingResult = _handleInput($props);

    if (!$inputHandlingResult['isSuccess']) {
        $errorMessage = '';

        switch ($inputHandlingResult['error']['code']) {
            case 'INVALID_FLEET_ID':
                $errorMessage = $lang['fl_acs_noid'];
                break;
            case 'FLEET_DOES_NOT_EXIST':
                $errorMessage = $lang['fl_acs_noexist'];
                break;
            case 'FLEET_INCORRECT_PARAMS':
                $errorMessage = $lang['fl_acs_badmission'];
                break;
            case 'FLEET_REACHED_TARGET':
                $errorMessage = $lang['fl_acs_timeup'];
                break;
        }

        $componentTPLData['P_HideACSMSG'] = '';
        $componentTPLData['P_ACSMSG'] = $errorMessage;
        $componentTPLData['P_ACSMSGCOL'] = 'red';
    } else {
        $componentTPLData['P_HideACSBoxOnError'] = '';

        $resultPayload = $inputHandlingResult['payload'];

        if ($resultPayload['message']['content'] !== null) {
            $componentTPLData['P_HideACSMSG'] = '';
            $componentTPLData['P_ACSMSG'] = $resultPayload['message']['content'];
            $componentTPLData['P_ACSMSGCOL'] = $resultPayload['message']['color'];
        }

        $componentTPLData['InsertACSUsers'] = json_encode($resultPayload['unionMembers']);
        $componentTPLData['ACSName'] = $resultPayload['unionName'];

        foreach ($resultPayload['unionMembers'] as $memberId => $memberDetails) {
            $memberListOptionComponent = FlightControl\Components\UnionMembersListOption\render([
                'memberId' => $memberId,
                'memberDetails' => $memberDetails,
            ]);
            $listOptionType = $memberListOptionComponent['listOptionType'];

            if (empty($componentTPLData[$listOptionType] )) {
                $componentTPLData[$listOptionType]  = '';
            }
            $componentTPLData[$listOptionType]  .= $memberListOptionComponent['componentHTML'];
        }
    }

    return [
        'componentHTML' => parsetemplate($tplBodyCache['body'], array_merge($lang, $componentTPLData)),
    ];
}

?>
