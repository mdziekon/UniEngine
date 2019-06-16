<?php

function FleetControl_Retreat($FleetSelector, $InstandRetreat = false)
{
    $Now = time();
    $UnixTS = 'UNIX_TIMESTAMP()';
    $DeleteACS = array();
    if($InstandRetreat !== true AND $InstandRetreat !== false)
    {
        $InstandRetreat = false;
    }

    $Fields_SelectFleets = '`f`.`fleet_id`, `f`.`fleet_owner`, `f`.`fleet_mission`, `f`.`fleet_mess`, `f`.`fleet_end_stay`, `f`.`fleet_send_time`, `f`.`fleet_start_time`, `a`.`id` as `fleet_acs_id`, `a`.`fleets_id`';
    $Query_SelectFleets = "SELECT {$Fields_SelectFleets} FROM {{table}} AS `f` LEFT JOIN {{prefix}}acs AS `a` ON `a`.`main_fleet_id` = `f`.`fleet_id` WHERE {$FleetSelector};";

    $SQLResult_GetFleets = doquery($Query_SelectFleets, 'fleets');

    $RowsCount = $SQLResult_GetFleets->num_rows;

    if($RowsCount > 0)
    {
        // Parse Fleets
        while($Fleet = $SQLResult_GetFleets->fetch_assoc())
        {
            if($Fleet['fleet_mission'] == 10)
            {
                // Rockets can't be retreated
                $Return['Errors'][$Fleet['fleet_id']] = 1;
                continue;
            }
            if($Fleet['fleet_mess'] == 0)
            {
                $Return['Types'][$Fleet['fleet_id']] = 1;
                $UpdateFleets[$Fleet['fleet_id']] = array
                (
                    'fleet_id' => $Fleet['fleet_id'],
                    'fleet_start_time' => "{$UnixTS} - 1",
                    'fleet_end_stay' => '0',
                    'fleet_end_time' => ($InstandRetreat ? $UnixTS : "(2 * {$UnixTS}) - {$Fleet['fleet_send_time']} + 1"),
                    'fleet_target_owner' => $Fleet['fleet_owner'],
                    'fleet_mess' => 1,
                );
                $UpdateArchive[$Fleet['fleet_id']] = array
                (
                    'Fleet_ID' => $Fleet['fleet_id'],
                    'Fleet_TurnedBack' => 'true',
                    'Fleet_TurnedBack_Time' => $UnixTS,
                    'Fleet_TurnedBack_EndTime' => ($InstandRetreat ? $UnixTS : "(2 * {$UnixTS}) - {$Fleet['fleet_send_time']} + 1"),
                );
                if($Fleet['fleet_mission'] == 5)
                {
                    $UpdateFleets[$Fleet['fleet_id']]['fleet_mess'] = 2;
                }
                if($Fleet['fleet_mission'] == 2)
                {
                    $UpdateFleets[$Fleet['fleet_id']]['fleet_mission'] = 1;
                    $UpdateArchive[$Fleet['fleet_id']]['Fleet_Mission_Changed'] = 'true';

                    $GetACSDataBy[] = $Fleet['fleet_id'];
                    $FleetsOwners[$Fleet['fleet_id']] = $Fleet['fleet_owner'];
                }
                else
                {
                    if(!empty($Fleet['fleet_acs_id']))
                    {
                        $DeleteACS[] = $Fleet['fleet_id'];
                    }

                    if(!empty($Fleet['fleets_id']))
                    {
                        $ExplodeFleets = explode(',', str_replace('|', '', $Fleet['fleets_id']));
                        foreach($ExplodeFleets as $FleetID)
                        {
                            if($FleetID > 0)
                            {
                                $Return['Types'][$FleetID] = 5;
                                $UpdateFleets[$FleetID] = array
                                (
                                    'fleet_id' => $FleetID,
                                    'fleet_start_time' => "{$UnixTS} - 1",
                                    'fleet_end_stay' => '0',
                                    'fleet_end_time' => ($InstandRetreat ? $UnixTS : '0'),
                                    'fleet_target_owner' => $Fleet['fleet_owner'],
                                    'fleet_mess' => 1,
                                    'fleet_mission' => 1,
                                );
                                $UpdateArchive[$FleetID] = array
                                (
                                    'Fleet_ID' => $FleetID,
                                    'Fleet_TurnedBack' => 'true',
                                    'Fleet_TurnedBack_Time' => $UnixTS,
                                    'Fleet_TurnedBack_EndTime' => ($InstandRetreat ? $UnixTS : '0'),
                                    'Fleet_Mission_Changed' => 'true',
                                );
                            }
                        }
                    }
                }
            }
            else if($Fleet['fleet_mess'] == 1)
            {
                if($Fleet['fleet_mission'] == 5 && $Fleet['fleet_end_stay'] > $Now)
                {
                    $Return['Types'][$Fleet['fleet_id']] = 2;
                    $UpdateFleets[$Fleet['fleet_id']] = array
                    (
                        'fleet_id' => $Fleet['fleet_id'],
                        'fleet_end_stay' => $UnixTS,
                        'fleet_end_time' => ($InstandRetreat ? $UnixTS : "{$UnixTS} + ({$Fleet['fleet_start_time']} - {$Fleet['fleet_send_time']}) + 1"),
                        'fleet_mess' => 2,
                    );
                    $UpdateArchive[$Fleet['fleet_id']] = array
                    (
                        'Fleet_ID' => $Fleet['fleet_id'],
                        'Fleet_TurnedBack' => 'true',
                        'Fleet_TurnedBack_Time' => $UnixTS,
                        'Fleet_TurnedBack_EndTime' => ($InstandRetreat ? $UnixTS : "{$UnixTS} + ({$Fleet['fleet_start_time']} - {$Fleet['fleet_send_time']}) + 1"),
                    );
                }
                else
                {
                    if($InstandRetreat)
                    {
                        $Return['Types'][$Fleet['fleet_id']] = 3;
                        $UpdateFleets[$Fleet['fleet_id']] = array
                        (
                            'fleet_id' => $Fleet['fleet_id'],
                            'fleet_end_time' => $UnixTS,
                            'fleet_mess' => 2,
                        );
                        $UpdateArchive[$Fleet['fleet_id']] = array
                        (
                            'Fleet_ID' => $Fleet['fleet_id'],
                            'Fleet_TurnedBack' => 'true',
                            'Fleet_TurnedBack_Time' => $UnixTS,
                            'Fleet_TurnedBack_EndTime' => $UnixTS,
                        );
                        if($Fleet['fleet_mission'] == 5)
                        {
                            $UpdateFleets[$Fleet['fleet_id']]['fleet_end_stay'] = $UnixTS;
                            $UpdateFleets[$Fleet['fleet_id']]['fleet_mess'] = 2;
                        }
                    }
                    else
                    {
                        $Return['Errors'][$Fleet['fleet_id']] = 2;
                    }
                }
            }
            else if($Fleet['fleet_mess'] == 2)
            {
                if($InstandRetreat)
                {
                    $Return['Types'][$Fleet['fleet_id']] = 4;
                    $UpdateFleets[$Fleet['fleet_id']] = array
                    (
                        'fleet_id' => $Fleet['fleet_id'],
                        'fleet_end_stay' => $Fleet['fleet_end_stay'],
                        'fleet_end_time' => $UnixTS,
                    );
                    $UpdateArchive[$Fleet['fleet_id']] = array
                    (
                        'Fleet_ID' => $Fleet['fleet_id'],
                        'Fleet_TurnedBack' => 'true',
                        'Fleet_TurnedBack_Time' => $UnixTS,
                        'Fleet_TurnedBack_EndTime' => $UnixTS,
                    );
                }
                else
                {
                    $Return['Errors'][$Fleet['fleet_id']] = 2;
                }
            }
        }

        // Check if some ACS needs Update
        if(!empty($GetACSDataBy))
        {
            foreach($GetACSDataBy as $FleetID)
            {
                $GetACSWhere[] = "`fleets_id` LIKE '%|{$FleetID}|%'";
            }
            $Query_SelectACS = "SELECT `id`, `fleets_id`, `user_joined`, `main_fleet_id` FROM {{table}} WHERE ".implode(' OR ', $GetACSWhere).";";

            $SQLResult_GetACS = doquery($Query_SelectACS, 'acs');

            if($SQLResult_GetACS->num_rows > 0)
            {
                while($ACS = $SQLResult_GetACS->fetch_assoc())
                {
                    if(in_array($ACS['main_fleet_id'], $DeleteACS))
                    {
                        continue;
                    }
                    $ParsedFleets = $ParsedUsers = array();
                    $TempFleets = explode(',', str_replace('|', '', $ACS['fleets_id']));
                    $TempUsers = explode(',', str_replace('|', '', $ACS['user_joined']));
                    foreach($TempFleets as $ElementID)
                    {
                        if($ElementID > 0)
                        {
                            $ACSFleetsMap[$ElementID] = $ACS['id'];
                            $ParsedFleets[$ElementID] = $ElementID;
                        }
                    }
                    foreach($TempUsers as $ElementID)
                    {
                        if($ElementID > 0)
                        {
                            $ParsedUsers[$ElementID] = $ElementID;
                        }
                    }

                    $ACSData[$ACS['id']] = array('id' => $ACS['id'], 'fleets_id' => $ParsedFleets, 'fleets_count' => count($ParsedFleets), 'user_joined' => $ParsedUsers, 'users_count' => count($ParsedUsers));
                    if($ACSData[$ACS['id']]['fleets_count'] > 1 && $ACSData[$ACS['id']]['users_count'] > 1)
                    {
                        foreach($ParsedFleets as $FleetID)
                        {
                            if(!($FleetsOwners[$FleetID] > 0))
                            {
                                $GetFleetOwners[$FleetID] = $FleetID;
                            }
                        }
                    }
                }
                if(!empty($GetFleetOwners))
                {
                    $Query_SelectFleetOwners = "SELECT `fleet_id`, `fleet_owner` FROM {{table}} WHERE `fleet_id` IN (".implode(', ', $GetFleetOwners).");";

                    $SQLResult_GetFleetOwners = doquery($Query_SelectFleetOwners, 'fleets');

                    while($Owners = $SQLResult_GetFleetOwners->fetch_assoc())
                    {
                        $FleetsOwners[$Owners['fleet_id']] = $Owners['fleet_owner'];
                    }
                }

                foreach($GetACSDataBy as $FleetID)
                {
                    $ACSPointer = &$ACSData[$ACSFleetsMap[$FleetID]];
                    if(empty($ACSPointer))
                    {
                        continue;
                    }
                    if($ACSPointer['fleets_count'] == 1)
                    {
                        $UpdateACS[$ACSPointer['id']] = array
                        (
                            'id' => $ACSPointer['id'],
                            'fleets_id' => array(),
                            'user_joined' => array(),
                            'fleets_count' => '0',
                        );
                        $ACSPointer['fleets_id'] = array();
                        $ACSPointer['fleets_count'] = 0;
                    }
                    else
                    {
                        unset($ACSPointer['fleets_id'][$FleetID]);
                        if($ACSPointer['users_count'] == 1)
                        {
                            if(empty($UpdateACS[$ACSPointer['id']]))
                            {
                                $UpdateACS[$ACSPointer['id']] = array
                                (
                                    'id' => $ACSPointer['id'],
                                    'fleets_id' => $ACSPointer['fleets_id'],
                                    'fleets_count' => $ACSPointer['fleets_count'] - 1,
                                );
                            }
                            else
                            {
                                $UpdateACS[$ACSPointer['id']]['fleets_id'] = $ACSPointer['fleets_id'];
                                $UpdateACS[$ACSPointer['id']]['fleets_count'] -= 1;
                            }
                            $ACSPointer['fleets_count'] -= 1;
                        }
                        else
                        {
                            $OwnerFound = false;
                            foreach($ACSPointer['fleets_id'] as $FleetID2)
                            {
                                if($FleetsOwners[$FleetID2] == $FleetsOwners[$FleetID])
                                {
                                    $OwnerFound = true;
                                    break;
                                }
                            }
                            if(empty($UpdateACS[$ACSPointer['id']]))
                            {
                                $UpdateACS[$ACSPointer['id']] = array
                                (
                                    'id' => $ACSPointer['id'],
                                    'fleets_id' => $ACSPointer['fleets_id'],
                                    'fleets_count' => $ACSPointer['fleets_count'] - 1,
                                );
                            }
                            else
                            {
                                $UpdateACS[$ACSPointer['id']]['fleets_id'] = $ACSPointer['fleets_id'];
                                $UpdateACS[$ACSPointer['id']]['fleets_count'] -= 1;
                            }
                            $ACSPointer['fleets_count'] -= 1;
                            if($OwnerFound !== true)
                            {
                                unset($ACSPointer['user_joined'][$FleetsOwners[$FleetID]]);
                                $UpdateACS[$ACSPointer['id']]['user_joined'] = $ACSPointer['user_joined'];
                                $ACSPointer['users_count'] -= 1;
                            }
                        }
                    }
                }
            }
        }

        // Make Updates
        if(!empty($UpdateFleets))
        {
            $Query_UpdateFleets = "INSERT INTO {{table}} (`fleet_id`, `fleet_start_time`, `fleet_end_stay`, `fleet_end_time`, `fleet_target_owner`, `fleet_mess`, `fleet_mission`) VALUES ";
            $Pattern_UpdateFleets = array('fleet_id' => null, 'fleet_start_time' => null, 'fleet_end_stay' => null, 'fleet_end_time' => null, 'fleet_target_owner' => null, 'fleet_mess' => null, 'fleet_mission' => null);
            foreach($UpdateFleets as $Data)
            {
                $Data = array_merge($Pattern_UpdateFleets, $Data);
                foreach($Data as &$Values)
                {
                    if($Values === null)
                    {
                        $Values = '"!nupd!"';
                    }
                }
                $Array_UpdateFleets[] = "({$Data['fleet_id']}, {$Data['fleet_start_time']}, {$Data['fleet_end_stay']}, {$Data['fleet_end_time']}, {$Data['fleet_target_owner']}, {$Data['fleet_mess']}, {$Data['fleet_mission']})";
            }
            $Query_UpdateFleets .= implode(', ', $Array_UpdateFleets);
            $Query_UpdateFleets .= " ON DUPLICATE KEY UPDATE ";
            $Query_UpdateFleets .= "`fleet_start_time` = IF(VALUES(`fleet_start_time`) = \"!nupd!\", `fleet_start_time`, VALUES(`fleet_start_time`)), ";
            $Query_UpdateFleets .= "`fleet_end_stay` = IF(VALUES(`fleet_end_stay`) > 0, VALUES(`fleet_end_stay`), 0), ";
            $Query_UpdateFleets .= "`fleet_end_time` = IF(VALUES(`fleet_end_time`) > 0, VALUES(`fleet_end_time`), (2 * UNIX_TIMESTAMP()) - `fleet_send_time` + 1), ";
            $Query_UpdateFleets .= "`fleet_target_owner` = IF(VALUES(`fleet_target_owner`) = \"!nupd!\", `fleet_target_owner`, VALUES(`fleet_target_owner`)), ";
            $Query_UpdateFleets .= "`fleet_mess` = IF(VALUES(`fleet_mess`) = \"!nupd!\", `fleet_mess`, VALUES(`fleet_mess`)), ";
            $Query_UpdateFleets .= "`fleet_mission` = IF(VALUES(`fleet_mission`) = \"!nupd!\", `fleet_mission`, VALUES(`fleet_mission`));";
            doquery($Query_UpdateFleets, 'fleets');

            $Return['Updates']['Fleets'] = count($UpdateFleets);
        }
        if(!empty($UpdateArchive))
        {
            $Query_UpdateArchive= "INSERT INTO {{table}} (`Fleet_ID`, `Fleet_TurnedBack`, `Fleet_TurnedBack_Time`, `Fleet_TurnedBack_EndTime`, `Fleet_Mission_Changed`) VALUES ";
            $Pattern_UpdateArchive = array('Fleet_ID' => null, 'Fleet_TurnedBack' => null, 'Fleet_TurnedBack_Time' => null, 'Fleet_TurnedBack_EndTime' => null, 'Fleet_Mission_Changed' => null);
            foreach($UpdateArchive as $Data)
            {
                $Data = array_merge($Pattern_UpdateArchive, $Data);
                foreach($Data as &$Values)
                {
                    if($Values === null)
                    {
                        $Values = '"!nupd!"';
                    }
                }
                $Array_UpdateArchive[] = "({$Data['Fleet_ID']}, {$Data['Fleet_TurnedBack']}, {$Data['Fleet_TurnedBack_Time']}, {$Data['Fleet_TurnedBack_EndTime']}, {$Data['Fleet_Mission_Changed']})";
            }
            $Query_UpdateArchive .= implode(', ', $Array_UpdateArchive);
            $Query_UpdateArchive .= " ON DUPLICATE KEY UPDATE ";
            $Query_UpdateArchive .= "`Fleet_TurnedBack` = IF(VALUES(`Fleet_TurnedBack`) = \"!nupd!\", `Fleet_TurnedBack`, VALUES(`Fleet_TurnedBack`)), ";
            $Query_UpdateArchive .= "`Fleet_TurnedBack_Time` = IF(VALUES(`Fleet_TurnedBack_Time`) = \"!nupd!\", `Fleet_TurnedBack_Time`, VALUES(`Fleet_TurnedBack_Time`)), ";
            $Query_UpdateArchive .= "`Fleet_TurnedBack_EndTime` = IF(VALUES(`Fleet_TurnedBack_EndTime`) > 0, VALUES(`Fleet_TurnedBack_EndTime`), (2 * UNIX_TIMESTAMP()) - `Fleet_Time_Send` + 1), ";
            $Query_UpdateArchive .= "`Fleet_Mission_Changed` = IF(VALUES(`Fleet_Mission_Changed`) = \"!nupd!\", `Fleet_Mission_Changed`, VALUES(`Fleet_Mission_Changed`));";
            doquery($Query_UpdateArchive, 'fleet_archive');

            $Return['Updates']['Archives'] = count($UpdateArchive);
        }
        if(!empty($UpdateACS))
        {
            $Query_UpdateACS= "INSERT INTO {{table}} (`id`, `fleets_id`, `user_joined`, `fleets_count`) VALUES ";
            $Pattern_UpdateACS = array('id' => null, 'fleets_id' => null, 'user_joined' => null, 'fleets_count' => null);
            foreach($UpdateACS as $Data)
            {
                $Data = array_merge($Pattern_UpdateACS, $Data);
                foreach($Data as &$Values)
                {
                    if($Values === null)
                    {
                        $Values = '!nupd!';
                    }
                }
                if($Data['fleets_id'] != '!nupd!')
                {
                    foreach($Data['fleets_id'] as $ElementID)
                    {
                        $Data['parsed_fleets'][] = "|{$ElementID}|";
                    }
                    if(!empty($Data['parsed_fleets']))
                    {
                        $Data['fleets_id'] = implode(',', $Data['parsed_fleets']);
                    }
                    else
                    {
                        $Data['fleets_id'] = '';
                    }
                }
                if($Data['user_joined'] != '!nupd!')
                {
                    foreach($Data['user_joined'] as $ElementID)
                    {
                        $Data['parsed_users'][] = "|{$ElementID}|";
                    }
                    if(!empty($Data['parsed_users']))
                    {
                        $Data['user_joined'] = implode(',', $Data['parsed_users']);
                    }
                    else
                    {
                        $Data['user_joined'] = '';
                    }
                }
                $Array_UpdateACS[] = "({$Data['id']}, \"{$Data['fleets_id']}\", \"{$Data['user_joined']}\", {$Data['fleets_count']})";
            }
            $Query_UpdateACS .= implode(', ', $Array_UpdateACS);
            $Query_UpdateACS .= " ON DUPLICATE KEY UPDATE ";
            $Query_UpdateACS .= "`fleets_id` = IF(VALUES(`fleets_id`) = \"!nupd!\", `fleets_id`, VALUES(`fleets_id`)), ";
            $Query_UpdateACS .= "`user_joined` = IF(VALUES(`user_joined`) = \"!nupd!\", `user_joined`, VALUES(`user_joined`)), ";
            $Query_UpdateACS .= "`fleets_count` = IF(VALUES(`fleets_count`) > 0, VALUES(`fleets_count`), 0);";
            doquery($Query_UpdateACS, 'acs');

            $Return['Updates']['ACS'] = count($UpdateACS);
        }
        if(!empty($DeleteACS))
        {
            doquery("DELETE FROM {{table}} WHERE `main_fleet_id` IN (".implode(', ', $DeleteACS).");", 'acs');

            $Return['Deletes']['ACS'] = count($DeleteACS);
        }
        $Return['Rows'] = $RowsCount;
    }
    else
    {
        $Return['Rows'] = 0;
    }

    return $Return;
}

?>
