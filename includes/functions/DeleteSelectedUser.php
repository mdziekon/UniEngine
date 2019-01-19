<?php

function DeleteSelectedUser($UserID)
{
    global $_MemCache, $_GameConfig;

    $Now = time();

    // Prepare Queries
    $UsersImplode            = implode(',', $UserID);
    $Query_GetUsers_Limit    = count($UserID);
    $Query_GetUsers_Fields    = array
    (
        'u' => array
        (
            'id', 'username', 'password', 'email', 'email_2', 'user_lastip', 'ip_at_reg', 'register_time', 'onlinetime', 'user_agent',
            'ally_id', 'multiIP_DeclarationID',
        ),
        'a' => array
        (
            'ally_owner', 'ally_ChatRoom_ID'
        ),
    );
    foreach($Query_GetUsers_Fields as $Prefix => $Fields)
    {
        foreach($Fields as $Key => $Value)
        {
            if(is_string($Key))
            {
                $Temp[] = "`{$Prefix}`.`{$Key}` AS `{$Value}`";
            }
            else
            {
                $Temp[] = "`{$Prefix}`.`{$Value}`";
            }
        }
    }
    $Query_GetUsers_Fields = implode(', ', $Temp);
    $Query_GetUsers                    .= "SELECT {$Query_GetUsers_Fields} FROM `{{table}}` AS `u` ";
    $Query_GetUsers                    .= "LEFT JOIN `{{prefix}}alliance` AS `a` ON `u`.`ally_id` = `a`.`id` ";
    $Query_GetUsers                    .= "WHERE `u`.`id` IN ({$UsersImplode}) LIMIT {$Query_GetUsers_Limit};";

    $Query_GetPlanets                = "SELECT `id` FROM {{table}} WHERE `id_owner` IN ({$UsersImplode}) AND `planet_type` = 1;";
    $Query_GetDeclarations            = "SELECT `id`, `users` FROM {{table}} WHERE `id` IN (%s) LIMIT %s;";

    $Query_DeletePlanets            = "DELETE FROM {{table}} WHERE `id_owner` IN ({$UsersImplode});";
    $Query_DeleteGalaxyData            = "DELETE FROM {{table}} WHERE `id_planet` IN (%s) LIMIT %s;";
    $Query_DeleteFleets                = "DELETE FROM {{table}} WHERE `fleet_owner` IN ({$UsersImplode});";
    $Query_DeleteMessages            = "DELETE FROM {{table}} WHERE `id_owner` IN ({$UsersImplode}) OR `id_sender` IN ({$UsersImplode});";
    $Query_DeleteStats                = "DELETE FROM {{table}} WHERE `stat_type` = 1 AND `id_owner` IN ({$UsersImplode}) LIMIT {$Query_GetUsers_Limit};";
    $Query_DeleteRecords            = "DELETE FROM {{table}} WHERE `id_owner` IN ({$UsersImplode});";
    $Query_DeleteAchievements        = "DELETE FROM {{table}} WHERE `A_UserID` IN ({$UsersImplode}) LIMIT {$Query_GetUsers_Limit};";
    $Query_DeleteNotes                = "DELETE FROM {{table}} WHERE `owner` IN ({$UsersImplode});";
    $Query_DeleteFleetShortcuts        = "DELETE FROM {{table}} WHERE `id_owner` IN ({$UsersImplode});";
    $Query_DeleteBuddyLinks            = "DELETE FROM {{table}} WHERE `sender` IN ({$UsersImplode}) OR `owner` IN ({$UsersImplode});";
    $Query_DeleteChatMessages        = "DELETE FROM {{table}} WHERE `UID` IN ({$UsersImplode});";
    $Query_DeleteUsers                = "DELETE FROM {{table}} WHERE `id` IN ({$UsersImplode}) LIMIT {$Query_GetUsers_Limit};";
    $Query_DeleteAllys                = "DELETE FROM {{table}} WHERE `id` IN (%s) LIMIT %s;";
    $Query_DeleteAllyPacts            = "DELETE FROM {{table}} WHERE `AllyID_Sender` IN (%s) OR `AllyID_Owner` IN (%s);";
    $Query_DeleteAllyInvites        = "DELETE FROM {{table}} WHERE %s;";
    $Query_DeleteAllyStats            = "DELETE FROM {{table}} WHERE `stat_type` = 2 AND `id_owner` IN (%s) LIMIT %s;";
    $Query_DeleteAllyChatRooms        = "DELETE FROM {{table}} WHERE `ID` IN (%s) LIMIT %s;";
    $Query_DeleteAllyChatMessages    = "DELETE FROM {{table}} WHERE `RID` IN (%s);";
    $Query_DeleteAllyChatOnline        = "DELETE FROM {{table}} WHERE `RID` IN (%s);";

    $Query_UpdateUsersCount            = "UPDATE {{table}} SET `config_value` = `config_value` - {$Query_GetUsers_Limit} WHERE `config_name` = 'users_amount' LIMIT 1;";
    $Query_UpdateAllyUsers            = "UPDATE {{table}} SET `ally_id` = 0, `ally_register_time` = 0, `ally_rank_id` = 0, `ally_request` = 0, `ally_request_text` = '' WHERE `ally_id` IN (%s) OR `ally_request` IN (%s);";
    $Query_UpdateAllyData            = "INSERT INTO {{table}} (`id`, `ally_members`) VALUES %s ON DUPLICATE KEY UPDATE `ally_members` = `ally_members` - VALUES(`ally_members`);";
    $Query_UpdateDeclarationsStatus    = "UPDATE {{table}} SET `status` = -2 WHERE `id` IN (%s) LIMIT %s;";
    $Query_UpdateDeclarationsUsers    = "UPDATE {{table}} SET `multi_validated` = 0 WHERE `id` IN (%s) LIMIT %s;";
    $Query_UpdateDeclarationsContent= "INSERT INTO {{table}} (`id`, `users`) VALUES %s ON DUPLICATE KEY UPDATE `users` = VALUES(`users`);";

    $Query_InsertData                .= "INSERT INTO {{table}} (`id`, `username`, `password`, `email`, `email2`, `last_ip`, `reg_ip`, `register_time`, `last_online`, `user_agent`, `delete_time`) VALUES ";

    // Delete all data
    $Result_GetUsers = doquery($Query_GetUsers, 'users');
    if($Result_GetUsers->num_rows > 0)
    {
        // --- Save necessary UserData
        $SaveData = array();
        $AllysToUpdate = array();
        $AllysToDelete = array();
        $GetDeclataions = array();
        $UsersInDeclarations = array();
        while($Data = $Result_GetUsers->fetch_assoc())
        {
            $SaveData[] = $Data;
            if($Data['ally_id'] > 0)
            {
                if($Data['id'] == $Data['ally_owner'])
                {
                    $AllysToDelete[] = $Data['ally_id'];
                    if($Data['ally_ChatRoom_ID'] > 0)
                    {
                        $DeleteChatRoomsInfo[] = $Data['ally_ChatRoom_ID'];
                    }
                    unset($AllysToUpdate[$Data['ally_id']]);
                }
                else
                {
                    $AllysToUpdate[$Data['ally_id']] += 1;
                }
            }
            if($Data['multiIP_DeclarationID'] > 0)
            {
                if(!in_array($Data['multiIP_DeclarationID'], $GetDeclataions))
                {
                    $GetDeclataions[] = $Data['multiIP_DeclarationID'];
                }
                $UsersInDeclarations[$Data['multiIP_DeclarationID']][] = $Data['id'];
            }
        }

        // --- Delete Planets
        $GalaxyImplode = array();
        $Result_GetPlanets = doquery($Query_GetPlanets, 'planets');
        while($Data = $Result_GetPlanets->fetch_assoc())
        {
            $GalaxyImplode[] = $Data['id'];
        }
        if(!empty($GalaxyImplode))
        {
            $GalaxyLimit = count($GalaxyImplode);
            $GalaxyImplode = implode(',', $GalaxyImplode);
            doquery($Query_DeletePlanets, 'planets');
            doquery(sprintf($Query_DeleteGalaxyData, $GalaxyImplode, $GalaxyLimit), 'galaxy');
        }

        // --- Handle Allys Update or Deletion
        $Query_DeleteAllyInvites_Where = array();
        $Query_DeleteAllyInvites_Where[] = "`OwnerID` IN ({$UsersImplode})";
        $Query_DeleteAllyInvites_Where[] = "`SenderID` IN ({$UsersImplode})";
        if(!empty($AllysToDelete))
        {
            $AllysToDelete_Count = count($AllysToDelete);
            $AllysToDelete = implode(',', $AllysToDelete);
            doquery(sprintf($Query_DeleteAllys, $AllysToDelete, $AllysToDelete_Count), 'alliance');
            doquery(sprintf($Query_DeleteAllyPacts, $AllysToDelete, $AllysToDelete), 'ally_pacts');
            doquery(sprintf($Query_UpdateAllyUsers, $AllysToDelete, $AllysToDelete), 'users');
            doquery(sprintf($Query_DeleteAllyStats, $AllysToDelete, $AllysToDelete_Count), 'statpoints');
            $Query_DeleteAllyInvites_Where[] = "`AllyID` IN ({$AllysToDelete})";
        }
        if(!empty($AllysToUpdate))
        {
            $AllysToUpdate_Values = array();
            foreach($AllysToUpdate as $AllyID => $AllyCount)
            {
                $AllysToUpdate_Values[] = "({$AllyID}, {$AllyCount})";
            }
            doquery(sprintf($Query_UpdateAllyData, implode(',', $AllysToUpdate_Values)), 'alliance');
        }
        if(!empty($Query_DeleteAllyInvites_Where))
        {
            doquery(sprintf($Query_DeleteAllyInvites, implode(' OR ', $Query_DeleteAllyInvites_Where)), 'ally_invites');
        }

        // --- Handle AllyChat Deletion
        if(!empty($DeleteChatRoomsInfo))
        {
            $DleeteChatRooms_Count = count($DeleteChatRoomsInfo);
            $DeleteChatRooms = implode(',', $DeleteChatRoomsInfo);
            doquery(sprintf($Query_DeleteAllyChatRooms, $DeleteChatRooms, $DleeteChatRooms_Count), 'chat_rooms');
            doquery(sprintf($Query_DeleteAllyChatMessages, $DeleteChatRooms), 'chat_messages');
            doquery(sprintf($Query_DeleteAllyChatOnline, $DeleteChatRooms), 'chat_online');
        }

        // --- Handle Fleets (first, retread all, then delete - this will prevent ACS failures)
        FleetControl_Retreat("`fleet_owner` IN ({$UsersImplode}) OR `fleet_target_owner` IN ({$UsersImplode})", true);
        doquery($Query_DeleteFleets, 'fleets');

        // --- Handle MultiDeclarations
        if(!empty($GetDeclataions))
        {
            $UsersToUpdate = array();
            $DeclarationsToUpdate_Status = array();
            $DeclarationsToUpdate_Content = array();

            $GetDeclataions_Count = count($GetDeclataions);
            $GetDeclataions = implode(',', $GetDeclataions);
            $Result_GetDeclarations = doquery(sprintf($Query_GetDeclarations, $GetDeclataions, $GetDeclataions_Count), 'declarations');
            if($Result_GetDeclarations->num_rows > 0)
            {
                while($Data = $Result_GetDeclarations->fetch_assoc())
                {
                    $Data['users'] = explode(',', $Data['users']);
                    $Data['thisUsers'] = [];
                    foreach($Data['users'] as $ThisUser)
                    {
                        $ThisUser = str_replace('|', '', $ThisUser);
                        if($ThisUser > 0)
                        {
                            $Data['thisUsers'][$ThisUser] = $ThisUser;
                            if(empty($Data['thisOwner']))
                            {
                                $Data['thisOwner'] = $ThisUser;
                            }
                        }
                    }
                    foreach($UsersInDeclarations[$Data['id']] as $ThisUser)
                    {
                        unset($Data['thisUsers'][$ThisUser]);
                    }
                    $ThisUsersCount = count($Data['thisUsers']);
                    if($ThisUsersCount <= 1 OR in_array($Data['thisOwner'], $UsersInDeclarations[$Data['id']]))
                    {
                        $DeclarationsToUpdate_Status[] = $Data['id'];
                        if($ThisUsersCount > 0)
                        {
                            $UsersToUpdate = array_merge($UsersToUpdate, $Data['thisUsers']);
                            $Message = array();
                            $Message['msg_id'] = '079';
                            $Message['args'] = array();
                            $Message = json_encode($Message);
                            Cache_Message($Data['thisUsers'], 0, $Now, 70, '007', '020', $Message);
                        }
                    }
                    else
                    {
                        $DeclarationsToUpdate_Content[$Data['id']] = $Data['thisUsers'];
                    }
                }

                if(!empty($DeclarationsToUpdate_Status))
                {
                    doquery(sprintf($Query_UpdateDeclarationsStatus, implode(',', $DeclarationsToUpdate_Status), count($DeclarationsToUpdate_Status)), 'declarations');
                }
                if(!empty($UsersToUpdate))
                {
                    doquery(sprintf($Query_UpdateDeclarationsUsers, implode(',', $UsersToUpdate), count($UsersToUpdate)), 'users');
                }
                if(!empty($DeclarationsToUpdate_Content))
                {
                    $Query_UpdateDeclarationsContent_Array = array();
                    foreach($DeclarationsToUpdate_Content as $ThisID => $ThisUsers)
                    {
                        foreach($ThisUsers as &$Value)
                        {
                            $Value = "|{$Value}|";
                        }
                        $ThisUsers = implode(',', $ThisUsers);
                        $Query_UpdateDeclarationsContent_Array[] = "({$ThisID}, '{$ThisUsers}')";
                    }
                    doquery(sprintf($Query_UpdateDeclarationsContent, implode(',', $Query_UpdateDeclarationsContent_Array)), 'declarations');
                }
            }
        }

        // --- Rest of Deleting
        doquery($Query_DeleteMessages, 'messages');
        doquery($Query_DeleteStats, 'statpoints');
        doquery($Query_DeleteRecords, 'records');
        doquery($Query_DeleteAchievements, 'achievements_stats');
        doquery($Query_DeleteNotes, 'notes');
        doquery($Query_DeleteFleetShortcuts, 'fleet_shortcuts');
        doquery($Query_DeleteBuddyLinks, 'buddy');
        doquery($Query_DeleteChatMessages, 'chat_messages');
        doquery($Query_DeleteUsers, 'users');

        // --- Do necessary Updates
        doquery($Query_UpdateUsersCount, 'config');
        $_GameConfig['users_amount'] -= $Query_GetUsers_Limit;
        $_MemCache->GameConfig = $_GameConfig;

        // --- Save some historical Data
        foreach($SaveData as $Data)
        {
            $Query_InsertData_Array[] = "({$Data['id']}, '{$Data['username']}', '{$Data['password']}', '{$Data['email']}', '{$Data['email_2']}', '{$Data['user_lastip']}', '{$Data['ip_at_reg']}', {$Data['register_time']}, {$Data['onlinetime']}, '{$Data['user_agent']}', {$Now})";
        }
        $Query_InsertData .= implode(',', $Query_InsertData_Array);
        doquery($Query_InsertData, 'deleted_users');
    }
}

?>
