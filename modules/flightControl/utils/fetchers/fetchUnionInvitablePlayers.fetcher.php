<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Fetchers;

function _fetchInvitableAlliancesWithPact($props) {
    $allianceId = $props['allianceId'];
    $minPactType = ALLYPACT_MILITARY;

    $query = (
        "SELECT " .
        "IF(`AllyID_Sender` = {$allianceId}, `AllyID_Owner`, `AllyID_Sender`) AS `allianceId` " .
        "FROM {{table}} " .
        "WHERE " .
        "(`AllyID_Sender` = {$allianceId} OR `AllyID_Owner` = {$allianceId}) AND " .
        "`Active` = 1 AND " .
        "`Type` >= {$minPactType} " .
        "; -- _fetchInvitableAlliancesWithPact()"
    );

    $results = doquery($query, 'ally_pacts');

    return mapQueryResults($results, function ($pactEntry) {
        return $pactEntry['allianceId'];
    });
}

function _fetchAlliancesMembers($props) {
    $userId = $props['userId'];
    $allianceIds = $props['allianceIds'];

    $allianceIdsString = implode(', ', $allianceIds);

    $query = (
        "SELECT " .
        "`id`, `username` " .
        "FROM {{table}} " .
        "WHERE " .
        "`ally_id` IN ({$allianceIdsString}) AND " .
        "`id` != {$userId} " .
        "; -- _fetchAlliancesMembers()"
    );

    $results = doquery($query, 'users');

    return mapQueryResults($results, function ($userEntry) {
        return $userEntry;
    });
}

function _fetchPlayerBuddies($props) {
    $userId = $props['userId'];

    $query = (
        "SELECT " .
        "IF(`buddy`.`sender` = {$userId}, `buddy`.`owner`, `buddy`.`sender`) AS `id`, " .
        "`user`.`username` AS `username` " .
        "FROM {{table}} AS `buddy` " .
        "LEFT JOIN `{{prefix}}users` AS `user` " .
        "ON " .
        "`user`.`id` = IF(`buddy`.`sender` = {$userId}, `buddy`.`owner`, `buddy`.`sender`) " .
        "WHERE " .
        "(`buddy`.`sender` = {$userId} OR `buddy`.`owner` = {$userId}) AND " .
        "`active` = 1 " .
        "; -- _fetchPlayerBuddies()"
    );

    $results = doquery($query, 'buddy');

    return mapQueryResults($results, function ($userEntry) {
        return $userEntry;
    });
}

/**
 * @param array $props
 * @param array $props['userId']
 * @param array $props['allianceId']
 */
function fetchUnionInvitablePlayers ($props) {
    $userId = $props['userId'];
    $allianceId = $props['allianceId'];

    $alliancesMembers = [];

    if (!empty($allianceId)) {
        $alliancesWithPact = _fetchInvitableAlliancesWithPact([ 'allianceId' => $allianceId ]);

        $alliancesMembers = _fetchAlliancesMembers([
            'userId' => $userId,
            'allianceIds' => array_merge([ $allianceId ], $alliancesWithPact),
        ]);
    }

    $playerBuddies = _fetchPlayerBuddies([ 'userId' => $userId ]);

    $invitablePlayers = [];

    foreach ($alliancesMembers as $memberEntry) {
        $memberId = $memberEntry['id'];
        $invitablePlayers[$memberId] = $memberEntry;
    }
    foreach ($playerBuddies as $buddyEntry) {
        $buddyId = $buddyEntry['id'];
        $invitablePlayers[$buddyId] = $buddyEntry;
    }

    return $invitablePlayers;
}

?>
