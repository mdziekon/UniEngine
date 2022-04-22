<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Helpers;

use UniEngine\Engine\Modules\FlightControl;

function _extractUnionUserIds($props) {
    $unionData = $props['unionData'];

    if (empty($unionData['users'])) {
        return [];
    }

    $usersList = str_replace('|', '', $unionData['users']);
    $usersList = explode(',', $usersList);
    $usersList = array_filter(
        $usersList,
        function ($userId) {
            return $userId > 0;
        }
    );

    return $usersList;
}

/**
 * @param array $props
 * @param object $props['unionData']
 * @param array $props['invitablePlayers']
 */
function extractUnionMembersDetails($props) {
    global $_Lang;

    $unionData = $props['unionData'];
    $invitablePlayers = $props['invitablePlayers'];

    $unionMemberIds = _extractUnionUserIds($props);

    $unionMembersDetails = object_map($unionMemberIds, function ($unionMemberId) use (&$unionData, &$invitablePlayers, &$_Lang) {
        $memberUsername = (
            !empty($invitablePlayers[$unionMemberId]['name']) ?
                $invitablePlayers[$unionMemberId]['name'] :
                null
        );
        $memberStatus = (
            (strstr($unionData['user_joined'], "|{$unionMemberId}|") !== false) ?
                $_Lang['fl_acs_joined'] :
                $_Lang['fl_acs_invited']
        );

        $memberDetails = [
            'name' => $memberUsername,
            'status' => $memberStatus,
            'canmove' => true,
            'place' => 1,
        ];

        return [
            $memberDetails,
            $unionMemberId,
        ];
    });

    $unionMembersWithMissingUsername = array_filter($unionMembersDetails, function ($memberDetails) {
        return empty($memberDetails['name']);
    });
    $missingUsernameMemberIds = array_keys($unionMembersWithMissingUsername);
    $unionMissingUsersData = FlightControl\Utils\Fetchers\fetchUnionMissingUsersData([
        'userIds' => $missingUsernameMemberIds,
    ]);

    foreach ($unionMissingUsersData as $userEntry) {
        $userId = $userEntry['id'];

        $unionMembersDetails[$userId]['name'] = $userEntry['username'];
    }

    return $unionMembersDetails;
}

?>
