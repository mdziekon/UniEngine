<?php

abstract class ChatRoomAccessType {
    const Open          = 0;
    const AllianceRoom  = 1;
    const GameTeamRoom  = 2;
}

function Chat_CheckAccess($RoomID, $ThisUser) {
    global $_Vars_AllyRankLabels;

    if ($RoomID == 0) {
        return true;
    }

    // Make sure, that this Room exists & User can be in this Room
    $Query_CheckRoom = "SELECT * FROM {{table}} WHERE `ID` = {$RoomID} LIMIT 1;";
    $Result_CheckRoom = doquery($Query_CheckRoom, 'chat_rooms', true);

    if (!$Result_CheckRoom) {
        return null;
    }

    if ($Result_CheckRoom['AccessType'] == ChatRoomAccessType::Open) {
        return true;
    }

    if ($Result_CheckRoom['AccessType'] == ChatRoomAccessType::AllianceRoom) {
        if (CheckAuth('supportadmin', AUTHCHECK_NORMAL, $ThisUser)) {
            // Users with SupportAdmin Access (or higher) can use all Ally ChatRooms
            return true;
        }
        if (
            $ThisUser['ally_id'] <= 0 ||
            $ThisUser['ally_id'] != $Result_CheckRoom['AccessCheck']
        ) {
            return false;
        }

        if ($ThisUser['id'] == $ThisUser['ally_owner']) {
            return true;
        }

        // If this user is not Ally Owner, we have to check his Privileges
        $allianceRanks = json_decode($ThisUser['ally_ranks'], true);

        foreach ($allianceRanks as $rankId => $rankParams) {
            if ($rankId != $ThisUser['ally_rank_id']) {
                continue;
            }

            foreach ($rankParams as $paramId => $paramValue) {
                if ($_Vars_AllyRankLabels[$paramId] !== 'canusechat') {
                    continue;
                }

                if ($paramValue === true) {
                    return true;
                }
            }
        }

        return false;
    }

    if ($Result_CheckRoom['AccessType'] == ChatRoomAccessType::GameTeamRoom) {
        return ($ThisUser['authlevel'] >= $Result_CheckRoom['AccessCheck']);
    }

    return false;
}

?>
