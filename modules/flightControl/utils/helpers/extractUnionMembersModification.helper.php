<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

/**
 * @param array $props
 * @param object $props['input']
 * @param string $props['input']['invitedUsersList']
 * @param array $props['invitableUsers']
 * @param array $props['currentUnionMembers']
 * @param array $props['currentUnionJoinedMembers']
 */
function extractUnionMembersModification($props) {
    $newInvitedUsersListInput = $props['input']['invitedUsersList'];
    $invitableUsers = $props['invitableUsers'];
    $currentUnionMembers = $props['currentUnionMembers'];
    $currentUnionJoinedMembers = $props['currentUnionJoinedMembers'];

    $newInvitedUsers = [];
    $newInvitedUsersCount = 0;

    $newInvitedUsersList = explode(',', $newInvitedUsersListInput);

    foreach ($newInvitedUsersList as $userId) {
        $userId = intval($userId);

        if (
            $userId <= 0 ||
            (
                !isset($invitableUsers[$userId]) &&
                (
                    !isset($currentUnionMembers[$userId]) ||
                    $currentUnionMembers[$userId]['isIgnoredWhenUpdating'] ||
                    $currentUnionMembers[$userId]['place'] !== 1
                )
            )
        ) {
            continue;
        }

        if ($newInvitedUsersCount >= MAX_ACS_JOINED_PLAYERS) {
            break;
        }

        $newInvitedUsers[$userId] = $userId;
        $newInvitedUsersCount += 1;
    }

    foreach ($currentUnionJoinedMembers as $userId) {
        if (in_array($userId, $newInvitedUsers)) {
            continue;
        }

        return [
            'isSuccess' => false,
            'error' => [
                'code' => 'KICKING_JOINED_MEMBER',
            ],
        ];
    }

    $newUnionMembersStates = [];

    foreach ($currentUnionMembers as $memberId => $memberDetails) {
        if ($memberDetails['isIgnoredWhenUpdating']) {
            continue;
        }

        $oldMemberPlacement = (
            $memberDetails['place'] === 1 ?
                'INVITED_USERS' :
                'INVITABLE_USERS'
        );
        $newMemberPlacement = (
            in_array($memberId, $newInvitedUsers) ?
                'INVITED_USERS' :
                'INVITABLE_USERS'
        );

        $hasChangedPlaces = $oldMemberPlacement !== $newMemberPlacement;

        if (
            $hasChangedPlaces &&
            !$memberDetails['canmove']
        ) {
            return [
                'isSuccess' => false,
                'error' => [
                    'code' => 'MOVING_UNMOVABLE_USER',
                ],
            ];
        }

        $newUnionMembersStates[$memberId] = [
            'hasChangedPlaces' => $hasChangedPlaces,
            'newMemberPlacement' => $newMemberPlacement,
            'isStillInvitable' => isset($invitableUsers[$memberId]),
        ];
    }

    return [
        'isSuccess' => true,
        'payload' => [
            'newUnionMembersStates' => $newUnionMembersStates,
        ],
    ];
}

/**
 * @param array $props
 * @param array $props['newUnionMembersStates']
 */
function hasUnionMembersStateChanged($props) {
    $newUnionMembersStates = $props['newUnionMembersStates'];

    $firstUnionMemberWithChanges = array_find($newUnionMembersStates, function ($newMemberState) {
        return $newMemberState['hasChangedPlaces'];
    });

    return $firstUnionMemberWithChanges !== null;
}

/**
 * @param array $props
 * @param array $props['newUnionMembersStates']
 */
function getUnionMembersToSendInvite($props) {
    $newUnionMembersStates = $props['newUnionMembersStates'];

    $newlyInvitedUnionMembers = array_filter($newUnionMembersStates, function ($newMemberState) {
        return (
            $newMemberState['hasChangedPlaces'] &&
            $newMemberState['newMemberPlacement'] === 'INVITED_USERS'
        );
    });

    return array_keys($newlyInvitedUnionMembers);
}

/**
 * @param array $props
 * @param array $props['newUnionMembersStates']
 * @param arrayRef $props['currentUnionMembers']
 */
function updateUnionMembersInMemory($props) {
    global $_Lang;

    $newUnionMembersStates = $props['newUnionMembersStates'];
    $currentUnionMembers = &$props['currentUnionMembers'];

    $membersToRemove = [];

    foreach ($currentUnionMembers as $memberId => &$memberDetails) {
        if (!isset($newUnionMembersStates[$memberId])) {
            continue;
        }

        $newMemberState = $newUnionMembersStates[$memberId];

        if (!$newMemberState['hasChangedPlaces']) {
            continue;
        }

        $isInvitedUser = $newMemberState['newMemberPlacement'] === 'INVITED_USERS';

        if (
            !$isInvitedUser &&
            !$newMemberState['isStillInvitable']
        ) {
            $membersToRemove[] = $memberId;
        }

        $memberDetails['place'] = (
            $isInvitedUser ?
                1 :
                2
        );
        $memberDetails['status'] = (
            $isInvitedUser ?
                $_Lang['fl_acs_invited'] :
                ''
        );
    }

    foreach ($membersToRemove as $memberId) {
        unset($currentUnionMembers[$memberId]);
    }
}

?>
