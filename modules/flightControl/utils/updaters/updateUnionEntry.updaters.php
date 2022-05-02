<?php

namespace UniEngine\Engine\Modules\FlightControl\Utils\Updaters;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

/**
 * @param object $props
 * @param string $props['union']
 * @param object $props['updates']
 * @param number $props['updates']['joiningFleetId']
 * @param number $props['updates']['joiningUserId']
 * @param number $props['updates']['slowdown']
 */
function updateUnionEntry($props) {
    $union = $props['union'];
    $unionId = $union['id'];
    $updates = $props['updates'];

    $joiningFleetId = $updates['joiningFleetId'];
    $joiningUserId = $updates['joiningUserId'];
    $slowdown = (
        !empty($updates['slowdown']) ?
            $updates['slowdown'] :
            0
    );

    $joiningFleetIdString = "|{$joiningFleetId}|";
    $fleetIdsField = implode(
        ',',
        Collections\compact([
            $union['fleets_id'],
            $joiningFleetIdString,
        ])
    );

    $joiningUserIdString = "|{$joiningUserId}|";
    $isUserAlreadyUnionMember = (
        !empty($union['user_joined']) &&
        strstr($union['user_joined'], $joiningUserIdString) !== false
    );
    $usersJoinedField = (
        $isUserAlreadyUnionMember ?
            $union['user_joined'] :
            implode(
                ',',
                Collections\compact([
                    $union['user_joined'],
                    $joiningUserIdString,
                ])
            )
    );

    $fieldsToUpdate = array_merge(
        [
            "`fleets_id` = '{$fleetIdsField}'",
            "`fleets_count` = `fleets_count` + 1",
            "`user_joined` = '{$usersJoinedField}'",
        ],
        (
            $slowdown > 0 ?
                [
                    "`start_time` = `start_time` + {$slowdown}",
                ] :
                []
        )
    );
    $fieldsToUpdate = Collections\compact($fieldsToUpdate);

    $query = (
        "UPDATE {{table}} " .
        "SET " .
        implode(', ', $fieldsToUpdate) .
        " " .
        "WHERE " .
        "`id` = {$unionId} " .
        "LIMIT 1 " .
        ";"
    );

    doquery($query, 'acs');
}

?>
