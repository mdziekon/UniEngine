<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

$PerPage = 30;

$Search_Value = (isset($_GET['searchtext']) ? trim($_GET['searchtext']) : null);
$SearchTextUnsecure = $Search_Value;

$TypeAllow = array('playername', 'allytag', 'allyname');
$Type = (isset($_GET['type']) ? trim($_GET['type']) : null);
$Type = (in_array($Type, $TypeAllow)) ? $Type : 'playername';

if(!isset($_GET['page']))
{
    $ThisPage = 1;
}
else
{
    $ThisPage = intval($_GET['page']);
    if($ThisPage < 1)
    {
        $ThisPage = 1;
    }
}
$QueryStart = ($ThisPage - 1) * $PerPage;

$Now = time();

includeLang('search');
$NotCounted = '<acronym title="'.$_Lang['not_counted_yet'].'">0</acronym>';

// Generate Queries
switch($Type)
{
    case 'playername':
    {
        $table = gettemplate('search_user_table');
        $row = gettemplate('search_user_row');
        $NeedenFields = "`ally`.`ally_name`, `planet`.`name`, `stat`.`total_rank` AS `rank`";
        $Query_Get  = "SELECT {{table}}.*, {$NeedenFields} FROM {{table}} ";
        $Query_Get .= "LEFT JOIN {{prefix}}alliance AS `ally` ON {{table}}.ally_id = `ally`.`id` ";
        $Query_Get .= "LEFT JOIN {{prefix}}planets AS `planet` ON {{table}}.id_planet = `planet`.`id` ";
        $Query_Get .= "LEFT JOIN {{prefix}}statpoints AS `stat` ON {{table}}.id = `stat`.`id_owner` AND `stat_type` = '1' ";
        $Query_Get .= "WHERE `username` LIKE '%{\$SearchVar}%' LIMIT {\$QueryStart}, {$PerPage};";
        $Query_Count = "SELECT COUNT(`id`) as `count` FROM {{table}} WHERE `username` LIKE '%{\$SearchVar}%';";
        $Query_Table = 'users';
        $Search_CheckRegExp = REGEXP_USERNAME;
        break;
    }
    case 'allytag':
    {
        $table = gettemplate('search_ally_table');
        $row = gettemplate('search_ally_row');
        $NeedenFields = "`stat`.`total_points` AS `ally_points`";
        $Query_Get  = "SELECT {{table}}.*, {$NeedenFields} FROM {{table}} ";
        $Query_Get .= "LEFT JOIN {{prefix}}statpoints AS `stat` ON `stat`.`id_owner` = `{{table}}`.`id` AND `stat_type` = '2' ";
        $Query_Get .= "WHERE `ally_tag` LIKE '%{\$SearchVar}%' LIMIT {\$QueryStart}, {$PerPage};";
        $Query_Count = "SELECT COUNT(`id`) as `count` FROM {{table}} WHERE `ally_tag` LIKE '%{\$SearchVar}%';";
        $Query_Table = 'alliance';
        $Search_CheckRegExp = REGEXP_ALLYNAMEANDTAG;
        break;
    }
    case 'allyname':
    {
        $table = gettemplate('search_ally_table');
        $row = gettemplate('search_ally_row');
        $NeedenFields = "`stat`.`total_points` AS `ally_points`";
        $Query_Get  = "SELECT {{table}}.*, {$NeedenFields} FROM {{table}} ";
        $Query_Get .= "LEFT JOIN {{prefix}}statpoints AS `stat` ON `stat`.`id_owner` = `{{table}}`.`id` AND `stat_type` = '2' ";
        $Query_Get .= "WHERE `ally_name` LIKE '%{\$SearchVar}%' LIMIT {\$QueryStart}, {$PerPage};";
        $Query_Count = "SELECT COUNT(`id`) as `count` FROM {{table}} WHERE `ally_name` LIKE '%{\$SearchVar}%';";
        $Query_Table = 'alliance';
        $Search_CheckRegExp = REGEXP_ALLYNAMEANDTAG;
        break;
    }
}

$Results = null;
if(!empty($Search_Value))
{
    if(empty($Search_CheckRegExp) OR preg_match($Search_CheckRegExp, $Search_Value))
    {
        $Search_Value = preg_replace(REGEXP_SANITIZELIKE_SEARCH, REGEXP_SANITIZELIKE_REPLACE, $Search_Value);

        $Query_Count = str_replace('{$SearchVar}', $Search_Value, $Query_Count);
        $Get_Count = doquery($Query_Count, $Query_Table, true);
        if($Get_Count['count'] > 0)
        {
            if($QueryStart >= $Get_Count['count'])
            {
                $QueryStart = 0;
                $ThisPage = 1;
            }
            $Query_Get = str_replace(array('{$SearchVar}', '{$QueryStart}'), array($Search_Value, $QueryStart), $Query_Get);

            $SQLResult_GetRows = doquery($Query_Get, $Query_Table);

            while($RowData = $SQLResult_GetRows->fetch_assoc())
            {
                if($Type == 'playername')
                {
                    $RowData['planet_name'] = $RowData['name'];
                    if($RowData['old_username_expire'] > $Now){
                        $RowData['username'] .= ' <acronym style="cursor: pointer;" title="'.$_Lang['Old_username_is'].': '.$RowData['old_username'].'">(?)</acronym>';
                    }
                    $RowData['ally_name'] = ($RowData['ally_id'] > 0 ? "<a href=\"alliance.php?mode=ainfo&a={$RowData['ally_id']}\">{$RowData['ally_name']}</a>" : '&nbsp;');
                    if($RowData['rank'] > 0)
                    {
                        $RowData['position'] = "<a href=\"stats.php?start={$RowData['rank']}\">{$RowData['rank']}</a>";
                    }
                    else
                    {
                        $RowData['position'] = $NotCounted;
                    }
                    $RowData['skinpath'] = $_SkinPath;
                    $RowData['coordinated'] = "{$RowData['galaxy']}:{$RowData['system']}:{$RowData['planet']}";
                    $RowData['buddy_request'] = $_Lang['buddy_request'];
                    $RowData['write_a_messege'] = $_Lang['write_a_messege'];
                    if($_User['ally_id'] > 0 AND $RowData['ally_id'] <= 0)
                    {
                        $RowData['Ally_Invite_Title'] = $_Lang['Ally_Invite_Title'];
                    }
                    else
                    {
                        $RowData['Insert_HideAllyInvite'] = 'display: none;';
                    }

                    $Results[] = parsetemplate($row, $RowData);
                }
                else
                {
                    $RowData['ally_points'] = prettyNumber($RowData['ally_points']);

                    $RowData['ally_tag'] = "<a href=\"alliance.php?mode=ainfo&tag={$RowData['ally_tag']}\">{$RowData['ally_tag']}</a>";
                    $Results[] = parsetemplate($row, $RowData);
                }
            }

            $_Lang['result_list'] = implode('', $Results);
            $Results = parsetemplate($table, $_Lang);
        }
        else
        {
            // Nothing found
            $Indicators_NothingFound = true;
        }
    }
    else
    {
        // Bad signs
        $Indicators_BadSigns = true;
    }
}

//Rest of things...
if(isset($_GET['type']))
{
    $_Lang['type_playername'] = ($_GET['type'] == 'playername') ? ' SELECTED' : '';
    $_Lang['type_allytag'] = ($_GET['type'] == 'allytag') ? ' SELECTED' : '';
    $_Lang['type_allyname'] = ($_GET['type'] == 'allyname') ? ' SELECTED' : '';
}
$_Lang['searchtext'] = $SearchTextUnsecure;
$_Lang['search_results'] = $Results;
$rc['found_results'] = $_Lang['found_results'];
$rc['results_count'] = (isset($Get_Count['count']) ? $Get_Count['count'] : 0);
$TPL_Count = gettemplate('search_results_count');

if(!empty($Results))
{
    $_Lang['search_results_count'] = parsetemplate(gettemplate('search_results_count'), $rc);
    if($Get_Count['count'] > 30)
    {
        include_once($_EnginePath.'includes/functions/Pagination.php');

        $Pagin = CreatePaginationArray($Get_Count['count'], $PerPage, $ThisPage, 7);
        $PaginationTPL = "<a class=\"pagebut {\$Classes}\" href=\"search.php?searchtext={$SearchTextUnsecure}&amp;type={$Type}&amp;page={\$Value}\">{\$ShowValue}</a>";
        $PaginationViewOpt = array('CurrentPage_Classes' => 'orange', 'Breaker_View' => '...');
        $CreatePagination = implode(' ', ParsePaginationArray($Pagin, $ThisPage, $PaginationTPL, $PaginationViewOpt));
        $pag['pagination'] = $CreatePagination;
        $pag['pagination_title'] = $_Lang['pagination_title'];
        $_Lang['pagination'] = parsetemplate(gettemplate('search_pagination'), $pag);
    }
}

if(isset($Indicators_NothingFound))
{
    $_Lang['search_results_count'] = parsetemplate($TPL_Count, array('found_results' => $_Lang['nothing_found']));
}
if(isset($Indicators_BadSigns))
{
    $_Lang['search_results_count'] = parsetemplate($TPL_Count, array('found_results' => $_Lang['badsigns_given']));
}

$Display = parsetemplate(gettemplate('search_body'), $_Lang);
display($Display, $_Lang['Search'], false);

?>
