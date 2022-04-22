<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Updaters;

/**
 * @param object $props
 * @param string $props['unionId']
 * @param array $props['newInvitedUsersList']
 */
function _updateUnionMemberships($props) {
    $unionId = $props['unionId'];
    $newInvitedUsersList = $props['newInvitedUsersList'];

    $newInvitedUsersListString = implode(',', $newInvitedUsersList);
    $newInvitedUsersListCount = count($newInvitedUsersList);

    doquery(
        "UPDATE {{table}} " .
        "SET " .
        "`users` = '{$newInvitedUsersListString}', " .
        "`invited_users` = {$newInvitedUsersListCount} " .
        "WHERE " .
        "`id` = {$unionId} " .
        ";",
        'acs'
    );
}

/**
 * @param object $props
 * @param string $props['unionId']
 * @param array $props['newUnionMembersStates']
 */
function updateUnionMembersInDB($props) {
    $unionId = $props['unionId'];
    $newUnionMembersStates = $props['newUnionMembersStates'];

    $newInvitedUsersList = [];

    foreach ($newUnionMembersStates as $memberId => $newMemberState) {
        if ($newMemberState['newMemberPlacement'] !== 'INVITED_USERS') {
            continue;
        }

        $newInvitedUsersList[] = "|{$memberId}|";
    }

    _updateUnionMemberships([
        'unionId' => $unionId,
        'newInvitedUsersList' => $newInvitedUsersList,
    ]);
}

?>
