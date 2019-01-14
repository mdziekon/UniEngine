<?php

function createMembersList($Admin = false)
{
    global $_GET, $_Lang, $_ThisUserRank, $Ally, $Time, $_SkinPath, $_User;

    $SortType = (isset($_GET['stype']) ? intval($_GET['stype']) : null);
    $SortMode = (isset($_GET['smode']) ? $_GET['smode'] : null);

    $ThisSorting = 'class="sortHigh"';
    switch($SortType)
    {
        case 1:
        {
            $SortBy = '`username`';
            $_Lang['sortByName'] = $ThisSorting;
            break;
        }
        case 2:
        {
            $SortBy = '`ally_rank_id`';
            $_Lang['sortByRank'] = $ThisSorting;
            break;
        }
        case 3:
        {
            $SortBy = '`total_points`';
            $_Lang['sortByPoints'] = $ThisSorting;
            break;
        }
        case 4:
        {
            $SortBy = '`ally_register_time`';
            $_Lang['sortByRegTime'] = $ThisSorting;
            break;
        }
        case 5:
        {
            if($_ThisUserRank['mlist_online'] === true)
            {
                $SortBy = '`onlinetime`';
                $_Lang['sortByOnline'] = $ThisSorting;
            }
            else
            {
                $SortBy = '`id`';
            }
            break;
        }
        case 6:
        {
            $SortBy = 'CONCAT(LPAD(`galaxy`, 2, \'0\'), LPAD(`system`, 3, \'0\'), LPAD(`planet`, 2, \'0\'))';
            $_Lang['sortByPlanet'] = $ThisSorting;
            break;
        }
        default:
        {
            $SortBy = '`id`';
            break;
        }
    }
    switch($SortMode)
    {
        case 'desc':
        {
            $SortHow = 'DESC';
            $_Lang['sortRev'] = 'asc';
            break;
        }
        default:
        {
            $SortHow = 'ASC';
            $_Lang['sortRev'] = 'desc';
            break;
        }
    }

    $Query_GetMembersList = '';
    $Query_GetMembersList .= "SELECT ";
    $Query_GetMembersList .= "`Users`.`id` AS `id`, `Users`.`username` AS `username`, ";
    $Query_GetMembersList .= "`Users`.`galaxy` AS `galaxy`, `Users`.`system` AS `system`, `Users`.`planet` AS `planet`, ";
    $Query_GetMembersList .= "`Users`.`onlinetime` AS `onlinetime`, ";
    $Query_GetMembersList .= "`Users`.`ally_register_time`, `Users`.`ally_rank_id` AS `ally_rank_id`, ";
    $Query_GetMembersList .= "`Points`.`total_points` AS `total_points` ";
    $Query_GetMembersList .= "FROM {{table}} AS `Users` ";
    $Query_GetMembersList .= "LEFT JOIN `{{prefix}}statpoints` AS `Points` ";
    $Query_GetMembersList .= "ON `Users`.`id` = `Points`.`id_owner` AND `Points`.`stat_type` = '1' ";
    $Query_GetMembersList .= "WHERE `Users`.`ally_id` = {$_User['ally_id']} ";
    $Query_GetMembersList .= "ORDER BY {$SortBy} {$SortHow};";
    $Result_GetMembersList = doquery($Query_GetMembersList, 'users');

    $_Lang['members_count'] = $Ally['ally_members'];
    if($Admin === false)
    {
        $RowTPL = gettemplate('alliance_memberslist_row');
    }
    else
    {
        $RowTPL = gettemplate('alliance_admin_memberslist_row');

        global $AllowedRanksChange;
        if($_ThisUserRank['mlist_mod'] === true && !empty($AllowedRanksChange) && count($AllowedRanksChange) > 1)
        {
            $CreateChangeRankList = '<select name="change_rank[{SET_ID}]">';
            foreach($AllowedRanksChange as $RankID)
            {
                $CreateChangeRankList .= "<option value=\"{$RankID}\" {SET_SELECT_{$RankID}}>{$Ally['ally_ranks'][$RankID]['name']}</option>";
            }
            $CreateChangeRankList .= '</select>';
        }

        if($_ThisUserRank['cankick'] === true && !empty($AllowedRanksChange))
        {
            $CreateKickButton = '<a href="?mode=admin&amp;edit=members&amp;kick={SET_ID}" class="kick"></a>';
        }

        if(empty($AllowedRanksChange) || ($_ThisUserRank['cankick'] !== true && $_ThisUserRank['mlist_mod'] !== true))
        {
            $AllowedRanksChange = array();
        }
    }

    $CounterLoop = 1;
    $_Lang['Rows'] = '';
    while($FetchData = $Result_GetMembersList->fetch_assoc())
    {
        $FetchData['i'] = $CounterLoop;
        $FetchData['skinpath'] = $_SkinPath;
        $FetchData['write'] = $_Lang['Ally_ML_Write'];
        if($Admin === true AND !empty($CreateChangeRankList) AND in_array($FetchData['ally_rank_id'], $AllowedRanksChange))
        {
            $SetRankSelector = str_replace
            (
                array
                (
                    '{SET_SELECT_'.$FetchData['ally_rank_id'].'}',
                    '{SET_ID}'
                ),
                array
                (
                    'selected',
                    $FetchData['id']
                ),
                $CreateChangeRankList
            );
            $SetRankSelector = preg_replace('#\{SET\_SELECT\_[0-9]+\}#si', '', $SetRankSelector);

            $FetchData['rank'] = $SetRankSelector;
        }
        else
        {
            $FetchData['rank'] = $Ally['ally_ranks'][$FetchData['ally_rank_id']]['name'];
        }
        $FetchData['points'] = prettyNumber($FetchData['total_points']);
        $FetchData['reg_time'] = prettyDate('d m Y', $FetchData['ally_register_time'], 1);

        if($_ThisUserRank['mlist_online'] === true)
        {
            if($FetchData['onlinetime'] >= ($Time - TIME_ONLINE))
            {
                $FetchData['onlinetime'] = $_Lang['Ally_ML_isOnline'];
                $FetchData['onlinecolor'] = 'lime';
            }
            else if($FetchData['onlinetime'] >= ($Time - TIME_HOUR))
            {
                $FetchData['onlinetime'] = floor(($Time - $FetchData['onlinetime']) / 60).' '.$_Lang['Ally_ML_Mins'];
                $FetchData['onlinecolor'] = 'orange';
            }
            else if($FetchData['onlinetime'] >= ($Time - TIME_DAY))
            {
                $FetchData['onlinetime'] = $_Lang['Ally_ML_isOffline'];
                $FetchData['onlinecolor'] = 'red';
            }
            else
            {
                $FetchData['onlinetime'] = floor(($Time - $FetchData['onlinetime']) / TIME_DAY).' '.$_Lang['Ally_ML_Days'];
                $FetchData['onlinecolor'] = 'red';
            }
        }
        else
        {
            $FetchData['onlinetime'] = '---';
            $FetchData['onlinecolor'] = 'orange';
        }
        if($Admin === true)
        {
            if(in_array($FetchData['ally_rank_id'], $AllowedRanksChange) && $FetchData['id'] != $_User['id'])
            {
                $FetchData['actions'] = str_replace('{SET_ID}', $FetchData['id'], $CreateKickButton);
            }
            else
            {
                $FetchData['actions'] = '&nbsp;';
            }
        }

        $_Lang['Rows'] .= parsetemplate($RowTPL, $FetchData);
        $CounterLoop += 1;
    }
}

function CheckJobsDone($JobType, $UserID)
{
    global $_User, $GlobalParsedTasks;

    if($_User['id'] == $UserID)
    {
        $CurrentUser = $_User;
    }
    else
    {
        if(empty($GlobalParsedTasks[$UserID]['tasks_done_parsed']))
        {
            $GetUserTasksDone = doquery("SELECT `tasks_done` FROM {{table}} WHERE `id` = {$UserID} LIMIT 1;", 'users', true);
            Tasks_CheckUservar($GetUserTasksDone);
            $GlobalParsedTasks[$UserID] = $GetUserTasksDone;
        }
        $CurrentUser = $GlobalParsedTasks[$UserID];
        $CurrentUser['id'] = $UserID;
    }
    Tasks_TriggerTask($CurrentUser, $JobType);
}

?>
