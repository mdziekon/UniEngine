<?php

function Chat_CheckAccess($RoomID, $ThisUser)
{
    global $_Vars_AllyRankLabels;

    // Make sure, that this Room exists & User can be in this Room
    $Query_CheckRoom = "SELECT * FROM {{table}} WHERE `ID` = {$RoomID} LIMIT 1;";
    $Result_CheckRoom = doquery($Query_CheckRoom, 'chat_rooms', true);
    if($Result_CheckRoom['ID'] != $RoomID)
    {
        return null;
    }
    if($Result_CheckRoom['AccessType'] != 0)
    {
        if($Result_CheckRoom['AccessType'] == 1)
        {
            // This is Ally Room
            if(CheckAuth('supportadmin', AUTHCHECK_NORMAL, $ThisUser))
            {
                // Users with SupportAdmin Access (or higher) can use all Ally ChatRooms
                return true;
            }
            if($ThisUser['ally_id'] <= 0)
            {
                return false;
            }
            if($ThisUser['ally_id'] != $Result_CheckRoom['AccessCheck'])
            {
                return false;
            }
            if($ThisUser['id'] != $ThisUser['ally_owner'])
            {
                // If this user is not Ally Owner, we have to check his Privileges
                $Temp1 = json_decode($ThisUser['ally_ranks'], true);
                foreach($Temp1 as $RankID => $RankData)
                {
                    if($RankID != $ThisUser['ally_rank_id'])
                    {
                        continue;
                    }
                    foreach($RankData as $DataID => $DataVal)
                    {
                        $Temp2[$_Vars_AllyRankLabels[$DataID]] = $DataVal;
                    }
                }
                if($Temp2['canusechat'] !== true)
                {
                    return false;
                }
            }
        }
        else if($Result_CheckRoom['AccessType'] == 2)
        {
            // This is GameTeam Room
            if($ThisUser['authlevel'] < $Result_CheckRoom['AccessCheck'])
            {
                return false;
            }
        }
    }

    return true;
}

?>
