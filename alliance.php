<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';

include($_EnginePath.'common.php');

loggedCheck();

define('IN_ALLYPAGE', true);

$Time                   = time();
$TagOrNameSanitize      = '#(\-|\_){1}#si';
$RankNameRegExp         = '/^[a-zA-Z0-9'.REGEXP_POLISHSIGNS.'\ \-\_\.]+$/D';
$Sanitize4SQLSearch     = '#(_){1}#si';
$Sanitize4SQLReplace    = '\\\$1';
$mode                   = (isset($_GET['mode']) ? $_GET['mode'] : null);
$edit                   = (isset($_GET['edit']) ? $_GET['edit'] : null);
$_MaxLength_Text        = 5000;
$_MaxLength_Request     = 2000;
$_MaxLength_MassMsg     = 5000;
$_MaxLength_IntText     = 5000;
$_MaxLength_ExtText     = 5000;
$_MaxLength_Reject      = 1000;
$_MaxLength_Invitation  = 2000;
$_MaxLength_AName       = 35;
$_MaxLength_ATag        = 8;
$_MaxLength_RankName    = 50;
$_MinLength_ATag        = 3;
$_MinLength_RankName    = 3;
$RankDataLabels         = $_Vars_AllyRankLabels;

includeLang('alliance');

/*    AllyPage is made of 3 parts:
    1. - Main part for Everyone - no need to check if user is in ally
    2. - User wants to join one of the allys
    3. - User is in ally already
*/

// --- Ally informations - Requested by User

include($_EnginePath.'includes/functions/AlliancePageFunctions.php');

if($mode == 'ainfo')
{
    include($_EnginePath.'includes/functions/BBcodeFunction.php');
    $MsgTitle = &$_Lang['AInfo_Title'];

    // Check if User has given enough data
    $AllyID = (isset($_GET['a']) ? intval($_GET['a']) : 0);
    $AllyTag = (isset($_GET['tag']) ? trim($_GET['tag']) : null);
    if(empty($AllyID) && empty($AllyTag))
    {
        message($_Lang['AInfo_NoSelector'], $MsgTitle);
    }
    if(!empty($AllyID))
    {
        if($AllyID <= 0)
        {
            message($_Lang['AInfo_BadID'], $MsgTitle);
        }
        $AllySelector = "`id` = {$AllyID}";
    }
    else
    {
        if(!preg_match(REGEXP_ALLYTAG_ABSOLUTE, $AllyTag))
        {
            message($_Lang['AInfo_BadTag'], $MsgTitle);
        }
        $AllySelector = "`ally_tag` = '{$AllyTag}'";
    }

    // Get Ally Data
    $Query_AInfo_AllyRow = '';
    $Query_AInfo_AllyRow .= "SELECT `id`, `ally_name`, `ally_tag`, `ally_image`, `ally_description`, `ally_web`, `ally_web_reveal`, `ally_members`, `ally_request_notallow` ";
    $Query_AInfo_AllyRow .= "FROM {{table}} WHERE {$AllySelector} LIMIT 1;";
    $AllyRow = doquery($Query_AInfo_AllyRow, 'alliance', true);

    // Ally doesn't exist
    if($AllyRow['id'] <= 0)
    {
        message($_Lang['AInfo_NoExist'], $MsgTitle);
    }

    // --- Show Info about Ally ---
    $_Lang['Alliance_information'] = $_Lang['AInfo_Title'].' '.$AllyRow['ally_name'];
    if(!empty($AllyRow['ally_image']))
    {
        $_Lang['ally_image'] = '<tr><th colspan="2"><img src="'.stripslashes($AllyRow['ally_image']).'"/></td></tr>';
    }
    if(!empty($AllyRow['ally_description']))
    {
        $_Lang['ally_description'] = nl2br(bbcode(stripslashes($AllyRow['ally_description'])));
    }
    else
    {
        $_Lang['ally_description'] = $_Lang['AInfo_NoAllyDesc'];
    }
    if(!empty($AllyRow['ally_web']) && ($AllyRow['ally_web_reveal'] == 1 || $_User['ally_id'] == $AllyRow['id']))
    {
        $AllyRow['ally_web'] = stripslashes($AllyRow['ally_web']);
        $_Lang['ally_web'] = "<tr><th>{$_Lang['AInfo_WebPage']}</th><th><a href=\"{$AllyRow['ally_web']}\" rel=\"nofollow\">{$AllyRow['ally_web']}</a></th></tr>";
    }
    $_Lang['ally_members'] = $AllyRow['ally_members'];
    $_Lang['ally_name']= $AllyRow['ally_name'];
    $_Lang['ally_tag'] = $AllyRow['ally_tag'];

    if($_User['ally_id'] == 0)
    {
        if($AllyRow['ally_request_notallow'] == 0)
        {
            $_Lang['Insert_Actions'] = "<tr><th colspan=\"2\"><form action=\"?mode=apply&allyid={$AllyRow['id']}\" method=\"post\" style=\"margin: 4px;\"><input type=\"submit\" value=\"{$_Lang['AInfo_MakeRequest']}\" class=\"orange pad5\" style=\"font-weight: bold;\" /></form></th></tr>";
        }
        else
        {
            $_Lang['Insert_Actions'] = "<tr><th colspan=\"2\" class=\"pad5 red\">{$_Lang['AInfo_RequestNotAllow']}</th></tr>";
        }
    }
    else if($_User['ally_id'] > 0 && $_User['ally_id'] == $AllyRow['id'])
    {
        $_Lang['Insert_Actions'] = "<tr><th colspan=\"2\" class=\"pad5\"><a href=\"alliance.php\" class=\"lime\">{$_Lang['AInfo_BelongToAlly']}</a></th></tr>";
    }

    $Page = parsetemplate(gettemplate('alliance_ainfo'), $_Lang);
    display($Page, $_Lang['Alliance_information']);
}
// End of Ally Informations

// --- Alliance Control
if($_User['ally_id'] == 0)
{
    // User has no Ally
    if(isOnVacation())
    {
        message($_Lang['Vacation_WarnMsg'], $_Lang['Vacation']);
    }
    if($_User['ally_request'] == 0)
    {
        // User has no any Request
        if($mode == 'make')
        {
            // User wants to Make alliance
            if(isset($_GET['yes']))
            {
                // User sent data
                $MsgTitle = &$_Lang['AMake_Title'];

                $CreateTag = trim($_POST['atag']);
                $CreateName = trim($_POST['aname']);
                $TagLength = strlen($CreateTag);
                $NameLength = strlen($CreateName);
                // Data checking
                if($TagLength < $_MinLength_ATag)
                {
                    message($_Lang['AMake_TagShort'], $MsgTitle, 'alliance.php?mode=make', 3);
                }
                if($TagLength > $_MaxLength_ATag)
                {
                    message($_Lang['AMake_TagLong'], $MsgTitle, 'alliance.php?mode=make', 3);
                }
                if(!preg_match(REGEXP_ALLYTAG_ABSOLUTE, $CreateTag))
                {
                    message($_Lang['AMake_BadTag'], $MsgTitle, 'alliance.php?mode=make', 3);
                }
                if($NameLength < 1)
                {
                    message($_Lang['AMake_NoName'], $MsgTitle, 'alliance.php?mode=make', 3);
                }
                if($NameLength > $_MaxLength_AName)
                {
                    message($_Lang['AMake_NameLong'], $MsgTitle, 'alliance.php?mode=make', 3);
                }
                if(!preg_match(REGEXP_ALLYNAME_ABSOLUTE, $CreateName))
                {
                    message($_Lang['AMake_BadName'], $MsgTitle, 'alliance.php?mode=make', 3);
                }

                $Query_ACreate_Check = '';
                $Query_ACreate_Check .= "SELECT `ally_tag`, `ally_name` FROM {{table}} ";
                $Query_ACreate_Check .= "WHERE `ally_tag` = '{$CreateTag}' OR `ally_name` = '{$CreateName}' LIMIT 2;";

                $Result_ACreate_Check = doquery($Query_ACreate_Check, 'alliance');
                $WarningBox = array();
                if($Result_ACreate_Check->num_rows)
                {
                    while($checkData = $Result_ACreate_Check->fetch_assoc())
                    {
                        if(strtolower($checkData['ally_tag']) == strtolower($CreateTag))
                        {
                            $WarningBox[] = sprintf($_Lang['AMake_TagExists'], $CreateTag);
                        }
                        if(strtolower($checkData['ally_name']) == strtolower($CreateName))
                        {
                            $WarningBox[] = sprintf($_Lang['AMake_NameExists'], $CreateName);
                        }
                    }
                }
                if(!empty($WarningBox))
                {
                    message(implode('<br/>', $WarningBox), $MsgTitle, 'alliance.php?mode=make', 3);
                }

                $RankDataLabelsCount = count($RankDataLabels) - 1;
                $AllyCreate_Ranks = array(array('name' => $_Lang['AMake_AllyLeader'], 'vals' => true), array('name' => $_Lang['AMake_AllyNewComer'], 'vals' => false));
                foreach($AllyCreate_Ranks as $ThisRowData)
                {
                    $ThisArray = array_fill(1, $RankDataLabelsCount, $ThisRowData['vals']);
                    $ThisArray = array_merge(array($ThisRowData['name']), $ThisArray);
                    $AllyCreate_RanksArray[] = $ThisArray;
                }
                $AllyCreate_RanksArray = getDBLink()->escape_string(json_encode($AllyCreate_RanksArray));

                $Query_ACreate_Create = '';
                $Query_ACreate_Create .= "INSERT INTO {{table}} SET ";
                $Query_ACreate_Create .= "`ally_name` = '{$CreateName}', ";
                $Query_ACreate_Create .= "`ally_tag`= '{$CreateTag}', ";
                $Query_ACreate_Create .= "`ally_owner` = {$_User['id']}, ";
                $Query_ACreate_Create .= "`ally_ranks` = '{$AllyCreate_RanksArray}', ";
                $Query_ACreate_Create .= "`ally_members` = 1, ";
                $Query_ACreate_Create .= "`ally_register_time` = UNIX_TIMESTAMP();";
                doquery($Query_ACreate_Create, 'alliance');

                $Result_GetLastID = doquery("SELECT LAST_INSERT_ID() AS `ID`;", 'alliance', true);
                $Result_GetLastID = $Result_GetLastID['ID'];

                $Query_ACreate_MakeChatRoom = '';
                $Query_ACreate_MakeChatRoom .= "INSERT INTO {{table}} SET ";
                $Query_ACreate_MakeChatRoom .= "`AccessType` = 1, ";
                $Query_ACreate_MakeChatRoom .= "`AccessCheck` = {$Result_GetLastID};";
                doquery($Query_ACreate_MakeChatRoom, 'chat_rooms');

                $Query_ACreate_UpdateAlly = '';
                $Query_ACreate_UpdateAlly .= "UPDATE {{table}} SET `ally_ChatRoom_ID` = LAST_INSERT_ID() ";
                $Query_ACreate_UpdateAlly .= "WHERE `id` = {$Result_GetLastID} LIMIT 1;";
                doquery($Query_ACreate_UpdateAlly, 'alliance');

                $Query_ACreate_UpdateUser = '';
                $Query_ACreate_UpdateUser .= "UPDATE {{table}} SET ";
                $Query_ACreate_UpdateUser .= "`ally_id` = {$Result_GetLastID}, ";
                $Query_ACreate_UpdateUser .= "`ally_rank_id` = 0, ";
                $Query_ACreate_UpdateUser .= "`ally_register_time` = UNIX_TIMESTAMP() ";
                $Query_ACreate_UpdateUser .= "WHERE `id` = {$_User['id']};";
                doquery($Query_ACreate_UpdateUser, 'users');

                CheckJobsDone('BUDDY_OR_ALLY_TASK', $_User['id']);

                message(sprintf($_Lang['AMake_Done'], $CreateName, $CreateTag), $MsgTitle, 'alliance.php', 3);
            }
            else
            {
                // Show Form
                $Page = parsetemplate(gettemplate('alliance_make'), $_Lang);
                display($Page, $_Lang['AMake_Title']);
            }
        }
        else if($mode == 'search')
        {
            // Ally search engine
            $searchText = null;
            if(!empty($_POST['searchtext']))
            {
                $searchText = trim($_POST['searchtext']);
            }
            else if(!empty($_GET['searchtext']))
            {
                $searchText = trim($_GET['searchtext']);
            }
            $_Lang['searchtext'] = $searchText;
            $ProtectedSearchText = preg_replace($Sanitize4SQLSearch, $Sanitize4SQLReplace, $searchText);

            // Show Form
            $Page = parsetemplate(gettemplate('alliance_searchform'), $_Lang);

            if(!empty($searchText))
            {
                if(!preg_match(REGEXP_ALLYNAMEANDTAG, $searchText))
                {
                    message($_Lang['AFind_BadSigns'], $_Lang['AFind_Title'], 'alliance.php?mode=search', 3);
                }

                $sPage = (isset($_GET['spage']) ? intval($_GET['spage']) : 0);
                if($sPage < 1)
                {
                    $sPage = 1;
                }
                $perPage = 20;
                $startFrom = ($sPage - 1) * $perPage;

                $Query_ASearch_Count = '';
                $Query_ASearch_Count .= "SELECT COUNT(*) AS `Count` FROM {{table}} ";
                $Query_ASearch_Count .= "WHERE `ally_name` LIKE '%{$ProtectedSearchText}%' OR `ally_tag` LIKE '%{$ProtectedSearchText}%';";

                $Result_ASearch_Count = doquery($Query_ASearch_Count, 'alliance', true);
                $TotalCount = $Result_ASearch_Count['Count'];

                if($TotalCount > 0)
                {
                    if($TotalCount < $startFrom)
                    {
                        $sPage = ceil($TotalCount / $perPage);
                        $startFrom = ($sPage - 1) * $perPage;
                    }

                    $Query_ASearch_Search = '';
                    $Query_ASearch_Search .= "SELECT `id`, `ally_tag`, `ally_name`, `ally_members` FROM {{table}} ";
                    $Query_ASearch_Search .= "WHERE ally_name LIKE '%{$ProtectedSearchText}%' or ally_tag LIKE '%{$ProtectedSearchText}%' LIMIT {$startFrom}, {$perPage};";

                    $Result_ASearch_Search = doquery($Query_ASearch_Search, 'alliance');

                    $SearchRowTPL = gettemplate('alliance_searchresult_row');
                    $_Lang['result'] = '';
                    while($Result = $Result_ASearch_Search->fetch_assoc())
                    {
                        $SanitizeSearch = preg_replace($TagOrNameSanitize, '\\\$1', $searchText);
                        $Result['ally_tag'] = preg_replace('#('.$SanitizeSearch.'){1,}#si', '<b class="lime">$1</b>', $Result['ally_tag']);
                        $Result['ally_name'] = preg_replace('#('.$SanitizeSearch.'){1,}#si', '<b class="lime">$1</b>', $Result['ally_name']);
                        $_Lang['result'] .= parsetemplate($SearchRowTPL, $Result);
                    }

                    if($TotalCount > $perPage)
                    {
                        include_once($_EnginePath.'includes/functions/Pagination.php');
                        $Pagin = CreatePaginationArray($TotalCount, $perPage, $sPage, 7);
                        $PaginationTPL = "<a class=\"pagebut {\$Classes}\" href=\"alliance.php?mode=search&amp;searchtext={$searchText}&amp;spage={\$Value}\">{\$ShowValue}</a>";
                        $PaginationViewOpt = array('CurrentPage_Classes' => 'orange', 'Breaker_View' => '...');
                        $CreatePagination = implode(' ', ParsePaginationArray($Pagin, $sPage, $PaginationTPL, $PaginationViewOpt));

                        $pagination = '<tr><th colspan="3" style="padding: 7px;">'.$CreatePagination.'</td></tr>';
                        $_Lang['pagination'] = $pagination;
                    }
                }
                else
                {
                    $_Lang['result'] = "<tr><th class=\"pad2 orange\" colspan=\"3\">{$_Lang['AFind_NothingFound']}</th></tr>";
                }
                $Page .= parsetemplate(gettemplate('alliance_searchresult_table'), $_Lang);
            }
            display($Page, $_Lang['AFind_Title']);
        }
        else if($mode == 'apply')
        {
            // Send Request
            $MsgTitle = &$_Lang['AApp_Title'];

            $AllyID = (isset($_GET['allyid']) ? intval($_GET['allyid']) : 0);
            if($AllyID <= 0)
            {
                message($_Lang['AApp_BadID'], $MsgTitle);
            }

            $Query_AApply_Check = '';
            $Query_AApply_Check .= "SELECT `id`, `ally_name`, `ally_tag`, `ally_request`, `ally_request_notallow` FROM {{table}} ";
            $Query_AApply_Check .= "WHERE `id` = {$AllyID} LIMIT 1;";

            $Result_AApply_Check = doquery($Query_AApply_Check, 'alliance', true);
            if($Result_AApply_Check['id'] != $AllyID)
            {
                message($_Lang['AApp_AllyNoExists'], $MsgTitle);
            }
            if($Result_AApply_Check['ally_request_notallow'] == 1)
            {
                message($_Lang['AApp_AllyBlockRequest'], $MsgTitle);
            }

            if(isset($_POST['send']) && $_POST['send'] == 'yes' && (!isset($_POST['action']) || $_POST['action'] != $_Lang['AApp_UseExample']))
            {
                $RequestText = strip_tags(stripslashes(trim($_POST['text'])));
                if(!empty($RequestText))
                {
                    $RequestText = getDBLink()->escape_string(substr($RequestText, 0, $_MaxLength_Request));
                    doquery("UPDATE {{table}} SET `ally_request` = {$AllyID}, `ally_request_text` = '{$RequestText}', `ally_register_time` = UNIX_TIMESTAMP() WHERE `id` = {$_User['id']};", 'users');
                    message($_Lang['AApp_Done'], $MsgTitle, 'alliance.php', 3);
                }
                else
                {
                    message($_Lang['AApp_NoText'], $MsgTitle, 'alliance.php?mode=apply&allyid='.$AllyID, 3);
                }
            }

            $text_apply = ($Result_AApply_Check['ally_request'] ? stripslashes($Result_AApply_Check['ally_request']) : $_Lang['AApp_NoSample']);

            $_Lang['allyid'] = $AllyID;
            $_Lang['text_apply'] = $text_apply;
            $_Lang['Write_to_alliance'] = sprintf($_Lang['AApp_WriteRequest'], $Result_AApply_Check['ally_name'], $Result_AApply_Check['ally_tag']);
            $_Lang['Insert_MaxLength'] = $_MaxLength_Request;

            $Page = parsetemplate(gettemplate('alliance_applyform'), $_Lang);
            display($Page, $MsgTitle);
        }
        else
        {
            // Show Default Menu & Invites List

            if(isset($_GET['cmd']) && ($_GET['cmd'] == 'inv_accept' || $_GET['cmd'] == 'inv_reject'))
            {
                $Inv_AllyID = (isset($_GET['aid']) ? intval($_GET['aid']) : 0);
                if($Inv_AllyID > 0)
                {
                    $Query_InvUse_Check = '';
                    $Query_InvUse_Check .= "SELECT `ai`.`SenderID`, `ally`.`ally_new_rank_id`, `ally`.`ally_ChatRoom_ID`FROM `{{table}}` AS `ai` ";
                    $Query_InvUse_Check .= "LEFT JOIN `{{prefix}}alliance` AS `ally` ON `ally`.`id` = `ai`.`AllyID` ";
                    $Query_InvUse_Check .= "WHERE `ai`.`OwnerID` = {$_User['id']} AND `ai`.`AllyID` = {$Inv_AllyID} AND `ai`.`State` = 1;";
                    $Result_InvUse_Check = doquery($Query_InvUse_Check, 'ally_invites', true);
                    if($Result_InvUse_Check['SenderID'] > 0)
                    {
                        if($_GET['cmd'] == 'inv_accept')
                        {
                            doquery("UPDATE {{table}} SET `ally_request` = 0, `ally_request_text` = '', `ally_id` = {$Inv_AllyID}, `ally_rank_id` = {$Result_InvUse_Check['ally_new_rank_id']}, `ally_register_time` = UNIX_TIMESTAMP() WHERE `id` = {$_User['id']};", 'users');
                            doquery("UPDATE {{table}} SET `ally_members` = `ally_members` + 1 WHERE `id` = {$Inv_AllyID};", 'alliance');
                            doquery("UPDATE {{table}} SET `State` = IF(`AllyID` = {$Inv_AllyID}, 0, -1) WHERE `OwnerID` = {$_User['id']} AND `State` = 1 LIMIT 1;", 'ally_invites');
                            if($Result_InvUse_Check['ally_ChatRoom_ID'] > 0)
                            {
                                $Query_UpdateChatRoomOnline = '';
                                $Query_UpdateChatRoomOnline .= "INSERT INTO {{table}} VALUES ({$Result_InvUse_Check['ally_ChatRoom_ID']}, {$_User['id']}, UNIX_TIMESTAMP()) ";
                                $Query_UpdateChatRoomOnline .= "ON DUPLICATE KEY UPDATE ";
                                $Query_UpdateChatRoomOnline .= "`LastOnline` = VALUES(`LastOnline`);";
                                doquery($Query_UpdateChatRoomOnline, 'chat_online');
                            }
                            CheckJobsDone('BUDDY_OR_ALLY_TASK', $_User['id']);

                            message($_Lang['AMenu_Inv_MsgAcceptSuccess'], $_Lang['Ally'], 'alliance.php', 3);
                        }
                        else
                        {
                            doquery("UPDATE {{table}} SET `State` = -1 WHERE `AllyID` = {$Inv_AllyID} AND `OwnerID` = {$_User['id']} AND `State` = 1 LIMIT 1;", 'ally_invites');

                            $MsgBox = array('text' => $_Lang['AMenu_Inv_MsgRejectSuccess'], 'col' => 'lime');
                        }
                    }
                    else
                    {
                        $MsgBox = array('text' => $_Lang['AMenu_Inv_MsgNotFound'], 'col' => 'red');
                    }
                }
                else
                {
                    $MsgBox = array('text' => $_Lang['AMenu_Inv_MsgBadID'], 'col' => 'red');
                }
            }

            $Query_InvList_Get = '';
            $Query_InvList_Get .= "SELECT `ai`.*, `u`.`username` AS `Sender_Name`, `a`.`ally_name` AS `Ally_Name` FROM {{table}} AS `ai` ";
            $Query_InvList_Get .= "LEFT JOIN `{{prefix}}users` AS `u` ON `u`.`id` = `ai`.`SenderID` ";
            $Query_InvList_Get .= "LEFT JOIN `{{prefix}}alliance` AS `a` ON `a`.`id` = `ai`.`AllyID` ";
            $Query_InvList_Get .= "WHERE `ai`.`OwnerID` = {$_User['id']} AND `State` != 0 ";
            $Query_InvList_Get .= " ORDER BY `ai`.`State` DESC, `ai`.`Date` DESC;";
            $Result_InvList_Get = doquery($Query_InvList_Get, 'ally_invites');
            if($Result_InvList_Get->num_rows > 0)
            {
                $TPL_Row = gettemplate('alliance_defaultmenu_invites_row');
                while($FetchData = $Result_InvList_Get->fetch_assoc())
                {
                    $FetchData['Date'] = prettyDate('d m Y, H:i:s', $FetchData['Date'], 1);
                    if($FetchData['State'] == 1)
                    {
                        $FetchData['Actions'][] = "<a class=\"act_accept\" href=\"?cmd=inv_accept&amp;aid={$FetchData['AllyID']}\"></a>";
                        $FetchData['Actions'][] = "<a class=\"act_reject\" href=\"?cmd=inv_reject&amp;aid={$FetchData['AllyID']}\"></a>";
                    }
                    $FetchData['State'] = $_Lang['AMenu_Inv_States'][((string)($FetchData['State'] + 0))];

                    if(!empty($FetchData['Actions']))
                    {
                        $FetchData['Actions'] = implode('', $FetchData['Actions']);
                    }
                    else
                    {
                        $FetchData['Actions'] = '&nbsp;';
                    }
                    $_Lang['Insert_InviteRows'][] = parsetemplate($TPL_Row, $FetchData);
                }
                $_Lang['Insert_InviteRows'] = implode('', $_Lang['Insert_InviteRows']);
            }
            else
            {
                $_Lang['Insert_InviteRows'] = parsetemplate(gettemplate('_singleRow'), array('Colspan' => 4, 'Classes' => 'orange pad5', 'Text' => $_Lang['AMenu_Inv_NoRows']));
            }
            if(!empty($MsgBox))
            {
                $_Lang['MsgBox'] = '<tr><td class="c pad5 '.$MsgBox['col'].'" colspan="4">'.$MsgBox['text'].'</td></tr><tr class="inv"><td></td></tr>';
            }

            $Page = parsetemplate(gettemplate('alliance_defaultmenu'), $_Lang);
            display($Page, $_Lang['Ally']);
        }
    }
    else
    {
        // User has a Request
        $MsgTitle = $_Lang['AReq_Title'];

        $Query_ARequest_Get = "SELECT `ally_name`, `ally_tag` FROM {{table}} WHERE `id` = {$_User['ally_request']} LIMIT 1;";
        $Result_ARequest_Get = doquery($Query_ARequest_Get, 'alliance', true);
        if(!empty($_POST['cancel']))
        {
            // Cancel the Request
            doquery("UPDATE {{table}} SET `ally_request` = 0, `ally_request_text` = '', `ally_register_time` = 0 WHERE `id` = {$_User['id']};", 'users');
            message(sprintf($_Lang['AReq_Canceled'], $Result_ARequest_Get['ally_name'], $Result_ARequest_Get['ally_tag']), $MsgTitle, 'alliance.php', 3);
        }
        $_Lang['RequestText'] = sprintf($_Lang['AReq_Waiting'], $_User['ally_request'], $Result_ARequest_Get['ally_name'], $Result_ARequest_Get['ally_tag']);
        $Page = parsetemplate(gettemplate('alliance_apply_waitform'), $_Lang);
        display($Page, $MsgTitle);
    }
}
elseif($_User['ally_id'] > 0 AND $_User['ally_request'] == 0)
{
    // User is in Alliance
    $MsgTitle = &$_Lang['Ally_Title'];

    // First, parse AllyData
    $Ally = doquery("SELECT * FROM {{table}} WHERE `id` = {$_User['ally_id']} LIMIT 1;", 'alliance', true);
    if($Ally['id'] <= 0)
    {
        doquery("UPDATE {{table}} SET `ally_id` = 0 WHERE `id` = {$_User['id']};", 'users');
        message($_Lang['Ally_GetAllyError'], $MsgTitle);
    }

    $Ally['ally_ranks'] = json_decode($Ally['ally_ranks'], true);
    $Ally['ally_ranks_org'] = $Ally['ally_ranks'];
    foreach($Ally['ally_ranks'] as $RankID => $RankData)
    {
        foreach($RankData as $DataID => $DataVal)
        {
            $NewRankArray[$RankID][$RankDataLabels[$DataID]] = $DataVal;
        }
    }
    $Ally['ally_ranks'] = $NewRankArray;
    $Ally['ally_ranks_count'] = count($RankDataLabels);
    $NewRankArray = null;

    // Check ThisUser Rights
    if($Ally['ally_owner'] == $_User['id'])
    {
        // This User is Main AllyAdmin (Rank #0 is only for him)
        $_ThisUserRank = $Ally['ally_ranks'][0];
        $_ThisUserRank['is_admin'] = true;
    }
    else
    {
        // This User is not Main AllyAdmin
        $_ThisUserRank = $Ally['ally_ranks'][$_User['ally_rank_id']];
        $_ThisUserRank['is_admin'] = false;
    }

    if($mode == 'exit')
    {
        // User is trying to leave the Ally
        if(isOnVacation())
        {
            message($_Lang['Vacation_WarnMsg'], $_Lang['Vacation']);
        }

        if($Ally['ally_owner'] == $_User['id'])
        {
            message($_Lang['Ally_Leave_MAdminInfo'], $MsgTitle, 'alliance.php', 3);
        }
        doquery("UPDATE {{table}} SET `ally_id` = 0 WHERE `id` = {$_User['id']};", 'users');
        doquery("UPDATE {{table}} SET `ally_members` = `ally_members` - 1 WHERE `id` = {$Ally['id']};", 'alliance');
        doquery("UPDATE {{table}} SET `State` = -2 WHERE `State` = 1 AND `SenderID` = {$_User['id']};", 'ally_invites');
        message(sprintf($_Lang['Ally_Leave_Done'], $Ally['ally_name']), $MsgTitle);
    }
    else if($mode == 'mlist' OR $mode == 'memberslist')
    {
        // User is trying to view Members List
        if($_ThisUserRank['mlist'] !== true)
        {
            message($_Lang['Ally_AccessDenied'], $MsgTitle, 'alliance.php', 3);
        }

        createMembersList();
        $Page = parsetemplate(gettemplate('alliance_memberslist_table'), $_Lang);
        display($Page, $_Lang['Ally_ML_WinTitle']);
    }
    else if($mode == 'pactslist')
    {
        // User is trying to See Ally Pacts
        include($_EnginePath.'modules/alliance/ally.pactslist.php');
    }
    else if($mode == 'newpact')
    {
        // User is trying to create new Pact
        include($_EnginePath.'modules/alliance/ally.newpact.php');
    }
    else if($mode == 'changepact')
    {
        // User is trying to modify existing Pact
        include($_EnginePath.'modules/alliance/ally.changepact.php');
    }
    else if($mode == 'circular' OR $mode == 'sendmsg')
    {
        // User is trying to Send Mass Message (to Ally Members)
        if($_ThisUserRank['sendmsg'] !== true)
        {
            message($_Lang['Ally_AccessDenied'], $MsgTitle, 'alliance.php', 3);
        }

        $_Lang['SelectedAll'] = 'selected';
        $SendMass_Ranks = null;
        $SendMass_Text = null;
        if(isset($_GET['send']))
        {
            $_POST['text'] = stripslashes(trim($_POST['text']));
            if(get_magic_quotes_gpc())
            {
                $_POST['text'] = stripslashes($_POST['text']);
            }
            $SendMass_Text = substr(strip_tags($_POST['text']), 0, $_MaxLength_Text);
            if($_POST['rank_select'] != 'all')
            {
                $SendMass_Ranks = intval($_POST['rank_select']);
                if(empty($Ally['ally_ranks'][$SendMass_Ranks]['name']))
                {
                    $SendMass_Ranks = null;
                }
            }
            else
            {
                $_Lang['SelectedAll'] = 'selected';
                $SendMass_Ranks = true;
            }
            if(!empty($SendMass_Text))
            {
                if($SendMass_Ranks !== null)
                {
                    $Query_AMassMsg_Members = '';
                    $Query_AMassMsg_Members .= "SELECT `id`, `username` FROM {{table}} ";
                    $Query_AMassMsg_Members .= "WHERE `ally_id` = {$_User['ally_id']} ";
                    if($SendMass_Ranks !== true)
                    {
                        $Query_AMassMsg_Members .= " AND `ally_rank_id` = {$SendMass_Ranks}";
                    }
                    $Result_AMassMsg_Members = doquery($Query_AMassMsg_Members, 'users');

                    if($Result_AMassMsg_Members->num_rows > 0)
                    {
                        while($FetchData = $Result_AMassMsg_Members->fetch_assoc())
                        {
                            $UsersID[] = $FetchData['id'];
                            $UsersNicks[] = $FetchData['username'];
                        }

                        $FirstMSGID = SendSimpleMessage($UsersID[0], $_User['id'], $Time, 2, "[{$Ally['ally_tag']}]", $_Lang['Ally_WR_Subject'].$_User['username'], $SendMass_Text, true);
                        unset($UsersID[0]);
                        if(!empty($UsersID))
                        {
                            Cache_Message($UsersID, $_User['id'], $Time, 2, '', '', '{COPY_MSG_#'.$FirstMSGID.'}');
                        }
                        $MsgBox = array('text' => $_Lang['Ally_WR_Done'].implode(', ', $UsersNicks), 'col' => 'lime');
                    }
                    else
                    {
                        $MsgBox = array('text' => $_Lang['Ally_WR_RankHasNoUsers'], 'col' => 'red');
                    }
                }
                else
                {
                    $MsgBox = array('text' => $_Lang['Ally_WR_NoRankSelect'], 'col' => 'red');
                }
            }
            else
            {
                $MsgBox = array('text' => $_Lang['Ally_WR_MsgEmpty'], 'col' => 'red');
            }
        }

        if(!empty($MsgBox))
        {
            $_Lang['MsgBox'] = '<tr><td class="c pad5 '.$MsgBox['col'].'" colspan="2">'.$MsgBox['text'].'</td></tr><tr class="inv"><td></td></tr>';
        }

        $_Lang['PutMessage'] = $SendMass_Text;
        $_Lang['OtherRanks'] = '';
        foreach($Ally['ally_ranks'] as $Index => $Data)
        {
            if($SendMass_Ranks === $Index)
            {
                $ThisSelected = 'selected';
            }
            else
            {
                $ThisSelected = '';
            }
            $_Lang['OtherRanks'] .= "<option value=\"{$Index}\" {$ThisSelected}>{$Data['name']}</option>";
        }

        $_Lang['Insert_MaxLength'] = $_MaxLength_MassMsg;
        $Page = parsetemplate(gettemplate('alliance_circular'), $_Lang);
        display($Page, $_Lang['Ally_WR_WinTitle']);
    }
    else if($mode == 'invite')
    {
        // User is trying to Invite someone
        if($_ThisUserRank['caninvite'] !== true)
        {
            message($_Lang['Ally_AccessDenied'], $MsgTitle, 'alliance.php', 3);
        }

        $Invite_Text = (isset($_POST['text']) ? stripslashes(strip_tags(trim($_POST['text']))) : null);
        $_Lang['Insert_Text'] = $Invite_Text;
        if(!empty($_GET['uid']) || (!empty($_POST['uid']) && $_POST['unamechanged'] != '1'))
        {
            if(!empty($_POST['uid']))
            {
                $Invite_UID = intval($_POST['uid']);
            }
            else
            {
                $Invite_UID = intval($_GET['uid']);
                $Invite_FromGet = true;
            }
            if($Invite_UID > 0)
            {
                $Query_AInvite_CheckUserWhere = "`id` = {$Invite_UID}";
            }
        }
        else if(!empty($_POST['username']))
        {
            $Invite_UName = trim($_POST['username']);
            if(preg_match(REGEXP_USERNAME_ABSOLUTE, $Invite_UName))
            {
                $Query_AInvite_CheckUserWhere = "`username` = '{$Invite_UName}'";
            }
        }

        if(!empty($Query_AInvite_CheckUserWhere))
        {
            $Query_AInvite_CheckUser = "SELECT `id`, `username`, `ally_id` FROM {{table}} WHERE {$Query_AInvite_CheckUserWhere} LIMIT 1;";
            $Result_AInvite_CheckUser = doquery($Query_AInvite_CheckUser, 'users', true);
            if($Result_AInvite_CheckUser['id'] > 0)
            {
                if($Result_AInvite_CheckUser['ally_id'] == 0)
                {
                    $_Lang['Insert_UID'] = $Invite_UID = $Result_AInvite_CheckUser['id'];
                    $_Lang['Insert_Username'] = $Result_AInvite_CheckUser['username'];
                    if(isset($Invite_FromGet))
                    {
                        $_Lang['Insert_LockUsername'] = 'disabled';
                    }

                    if($_GET['send'] == '1')
                    {
                        if(!empty($Invite_Text))
                        {
                            $Invite_Text = substr($Invite_Text, 0, $_MaxLength_Invitation);

                            $Query_AInvite_CheckInvite = '';
                            $Query_AInvite_CheckInvite .= "SELECT COUNT(*) AS `Count` FROM {{table}} WHERE ";
                            $Query_AInvite_CheckInvite .= "`AllyID` = {$Ally['id']} AND `OwnerID` = {$Invite_UID} AND `State` = 1;";
                            $Result_AInvite_CheckInvite = doquery($Query_AInvite_CheckInvite, 'ally_invites', true);
                            if($Result_AInvite_CheckInvite['Count'] <= 0)
                            {
                                $Query_AInvite_Insert = '';
                                $Query_AInvite_Insert .= "INSERT INTO {{table}} (`AllyID`, `OwnerID`, `SenderID`, `Date`) VALUES ";
                                $Query_AInvite_Insert .= "({$Ally['id']}, {$Invite_UID}, {$_User['id']}, UNIX_TIMESTAMP()) ";
                                $Query_AInvite_Insert .= "ON DUPLICATE KEY UPDATE `AllyID` = `AllyID`;";
                                doquery($Query_AInvite_Insert, 'ally_invites');

                                $Message = array();
                                $Message['msg_id'] = '095';
                                $Message['args'] = array
                                (
                                    $Ally['id'], $Ally['ally_name'], $Ally['ally_tag'], $_User['id'], $_User['username'], $Invite_Text
                                );
                                $Message = json_encode($Message);
                                Cache_Message($Invite_UID, 0, NULL, 2, '005', '023', $Message);

                                $MsgBox = array('text' => sprintf($_Lang['Ally_INV_Msg_Success'], $Invite_UID, $Result_AInvite_CheckUser['username']), 'col' => 'lime');
                            }
                            else
                            {
                                $MsgBox = array('text' => $_Lang['Ally_INV_Msg_AlreadyInvited'], 'col' => 'red');
                            }
                            $_Lang['Insert_UID'] = $_Lang['Insert_Username'] = $_Lang['Insert_LockUsername'] = '';
                        }
                        else
                        {
                            $MsgBox = array('text' => $_Lang['Ally_INV_Msg_EmptyText'], 'col' => 'red');
                        }
                    }
                }
                else
                {
                    $MsgBox = array('text' => $_Lang['Ally_INV_Msg_UserInAlly'], 'col' => 'red');
                }
            }
            else
            {
                $MsgBox = array('text' => $_Lang['Ally_INV_Msg_UserNoExists'], 'col' => 'red');
            }
        }
        else
        {
            if(isset($_GET['send']))
            {
                $MsgBox = array('text' => $_Lang['Ally_INV_Msg_BadInput'], 'col' => 'red');
            }
        }

        if(!empty($MsgBox))
        {
            $_Lang['MsgBox'] = '<tr><td class="c pad5 '.$MsgBox['col'].'" colspan="2">'.$MsgBox['text'].'</td></tr><tr class="inv"><td></td></tr>';
        }

        $_Lang['Insert_MaxLength'] = $_MaxLength_Invitation;
        $Page = parsetemplate(gettemplate('alliance_invite'), $_Lang);
        display($Page, $_Lang['Ally_INV_Title']);
    }
    else if($mode == 'invlist')
    {
        // User is trying to show Invites List
        if(!empty($_GET['del']))
        {
            $InvList_DelID = intval($_GET['del']);
            if($InvList_DelID > 0)
            {
                $Query_InvList_Delete = '';
                $Query_InvList_Delete .= "SELECT `SenderID` FROM {{table}} ";
                $Query_InvList_Delete .= "WHERE `AllyID` = {$Ally['id']} AND `OwnerID` = {$InvList_DelID} AND `State` = 1 LIMIT 1;";
                $Result_InvList_Delete = doquery($Query_InvList_Delete, 'ally_invites', true);
                if($Result_InvList_Delete['SenderID'] > 0)
                {
                    if($_ThisUserRank['managereq'] === true OR $_User['id'] == $Result_InvList_Delete['SenderID'])
                    {
                        if($_User['id'] == $Result_InvList_Delete['SenderID'])
                        {
                            $SetState = '-2';
                        }
                        else
                        {
                            $SetState = '-3';
                        }
                        doquery("UPDATE {{table}} SET `State` = {$SetState} WHERE `AllyID` = {$Ally['id']} AND `OwnerID` = {$InvList_DelID} AND `State` = 1 LIMIT 1;", 'ally_invites');
                        $MsgBox = array('text' => $_Lang['Ally_INVList_MsgSuccess'], 'col' => 'lime');
                    }
                    else
                    {
                        $MsgBox = array('text' => $_Lang['Ally_INVList_MsgCantDel'], 'col' => 'red');
                    }
                }
                else
                {
                    $MsgBox = array('text' => $_Lang['Ally_INVList_MsgNotFound'], 'col' => 'red');
                }
            }
            else
            {
                $MsgBox = array('text' => $_Lang['Ally_INVList_MsgBadID'], 'col' => 'red');
            }
        }

        $Query_InvList_Get = '';
        $Query_InvList_Get .= "SELECT `ai`.*, `u1`.`username` AS `Owner_Name`, `u2`.`username` AS `Sender_Name` FROM {{table}} AS `ai` ";
        $Query_InvList_Get .= "LEFT JOIN `{{prefix}}users` AS `u1` ON `u1`.`id` = `ai`.`OwnerID` ";
        $Query_InvList_Get .= "LEFT JOIN `{{prefix}}users` AS `u2` ON `u2`.`id` = `ai`.`SenderID` ";
        $Query_InvList_Get .= "WHERE `ai`.`AllyID` = {$Ally['id']} ";
        if($_ThisUserRank['lookreq'] !== true)
        {
            $Query_InvList_Get .= " AND `ai`.`SenderID` = {$_User['id']}";
        }
        $Query_InvList_Get .= " ORDER BY `ai`.`State` DESC, `ai`.`Date` DESC;";
        $Result_InvList_Get = doquery($Query_InvList_Get, 'ally_invites');
        if($Result_InvList_Get->num_rows > 0)
        {
            $TPL_Row = gettemplate('alliance_invlist_row');
            while($FetchData = $Result_InvList_Get->fetch_assoc())
            {
                $FetchData['Date'] = prettyDate('d m Y, H:i:s', $FetchData['Date'], 1);
                if($FetchData['State'] == 1 AND ($_ThisUserRank['managereq'] === true OR $_User['id'] == $FetchData['SenderID']))
                {
                    $FetchData['Actions'][] = "<a class=\"act_del\" href=\"?mode=invlist&amp;del={$FetchData['OwnerID']}\"></a>";
                }
                $FetchData['State'] = $_Lang['Ally_INVList_States'][((string)($FetchData['State'] + 0))];

                if(!empty($FetchData['Actions']))
                {
                    $FetchData['Actions'] = implode('', $FetchData['Actions']);
                }
                else
                {
                    $FetchData['Actions'] = '&nbsp;';
                }
                $_Lang['Insert_Rows'][] = parsetemplate($TPL_Row, $FetchData);
            }
            $_Lang['Insert_Rows'] = implode('', $_Lang['Insert_Rows']);
        }
        else
        {
            $_Lang['Insert_Rows'] = parsetemplate(gettemplate('_singleRow'), array('Colspan' => 5, 'Classes' => 'orange pad5', 'Text' => $_Lang['Ally_INVList_NoRows']));
        }

        if(!empty($MsgBox))
        {
            $_Lang['MsgBox'] = '<tr><td class="c pad5 '.$MsgBox['col'].'" colspan="5">'.$MsgBox['text'].'</td></tr><tr class="inv"><td></td></tr>';
        }
        $Page = parsetemplate(gettemplate('alliance_invlist_body'), $_Lang);
        display($Page, $_Lang['Ally_INVList_Title']);
    }
    else if($mode == 'admin')
    {
        // User has entered Admin mode
        if(isOnVacation())
        {
            message($_Lang['Vacation_WarnMsg'], $_Lang['Vacation']);
        }

        if($_ThisUserRank['admingen'] !== true)
        {
            $_Lang['HideManageAllyData'] = 'hide';
            $Hiding += 1;
        }
        if($_ThisUserRank['mlist_mod'] !== true)
        {
            $_Lang['HideManageMemList'] = 'hide';
            $Hiding += 1;
        }
        if($_ThisUserRank['ranks_mod'] !== true)
        {
            $_Lang['HideManageRanks'] = 'hide';
            $Hiding += 1;
        }
        if($_ThisUserRank['lookreq'] !== true)
        {
            $_Lang['HideLookReq'] = 'hide';
            $Hiding += 1;
        }
        if(isset($Hiding) && $Hiding == 4)
        {
            message($_Lang['Ally_AccessDenied'], $MsgTitle, 'alliance.php', 3);
        }

        if(empty($edit))
        {
            // User is on AllyAdmin Front Page
            $_Lang['HideInfoBox'] = $_Lang['HideWarnBox'] = 'hide';
            $_Lang['GetLastMark'] = 'Mark01';

            $NewcomerRankSet = array();
            $NewcomerRankSetGray = array();
            foreach($Ally['ally_ranks'] as $RankID => $RankData)
            {
                if($RankID == 0)
                {
                    continue;
                }
                if(($RankData['like_admin'] === true AND $_ThisUserRank['is_admin'] !== true) OR $_ThisUserRank['like_admin'] !== true)
                {
                    $NewcomerRankSetGray[] = $RankID;
                }
                $NewcomerRankSet[] = $RankID;
            }

            if(isset($_POST['change']) && $_POST['change'] == 'texts')
            {
                if($_ThisUserRank['admingen'] === true)
                {
                    $NotChanged = 0;
                    if($_POST['mode'] == 'saveAll' OR ($_POST['mode'] == 'saveOne' AND $_POST['saveonly'] == 'Mark01'))
                    {
                        $TempText = trim(strip_tags(stripslashes($_POST['ext_text'])));
                        if(strlen($TempText) > $_MaxLength_ExtText)
                        {
                            $TempText = substr($TempText, 0, $_MaxLength_ExtText);
                            $Warnings[] = $_Lang['ADM_ExtTextCutted'];
                        }
                        if($TempText != $Ally['ally_description'])
                        {
                            $InsertText = getDBLink()->escape_string($TempText);
                            $ChangeQuery[] = "`ally_description` = '{$InsertText}'";
                            $Ally['ally_description'] = $TempText;
                            $Info[] = $_Lang['ADM_ExtTextChanged'];
                        }
                        else
                        {
                            $NotChanged += 1;
                        }
                    }
                    if($_POST['mode'] == 'saveAll' OR ($_POST['mode'] == 'saveOne' AND $_POST['saveonly'] == 'Mark02'))
                    {
                        $TempText = trim(strip_tags(stripslashes($_POST['int_text'])));
                        if(strlen($TempText) > $_MaxLength_IntText)
                        {
                            $TempText = substr($TempText, 0, $_MaxLength_IntText);
                            $Warnings[] = $_Lang['ADM_IntTextCutted'];
                        }
                        if($TempText != $Ally['ally_text'])
                        {
                            $InsertText = getDBLink()->escape_string($TempText);
                            $ChangeQuery[] = "`ally_text` = '{$InsertText}'";
                            $Ally['ally_text'] = $TempText;
                            $Info[] = $_Lang['ADM_IntTextChanged'];
                        }
                        else
                        {
                            $NotChanged += 1;
                        }
                    }
                    if($_POST['mode'] == 'saveAll' OR ($_POST['mode'] == 'saveOne' AND $_POST['saveonly'] == 'Mark03'))
                    {
                        $TempText = trim(strip_tags(stripslashes($_POST['req_text'])));
                        if(strlen($TempText) > $_MaxLength_Request)
                        {
                            $TempText = substr($TempText, 0, $_MaxLength_Request);
                            $Warnings[] = $_Lang['ADM_ReqTextCutted'];
                        }
                        if($TempText != $Ally['ally_request'])
                        {
                            $InsertText = getDBLink()->escape_string($TempText);
                            $ChangeQuery[] = "`ally_request` = '{$InsertText}'";
                            $Ally['ally_request'] = $TempText;
                            $Info[] = $_Lang['ADM_ReqTextChanged'];
                        }
                        else
                        {
                            $NotChanged += 1;
                        }
                    }
                    if($NotChanged == 3 OR ($_POST['mode'] == 'saveOne' AND $NotChanged == 1))
                    {
                        $Info[] = $_Lang['ADM_NothingChanged'];
                    }
                    if(!empty($ChangeQuery))
                    {
                        doquery("UPDATE {{table}} SET ".implode(', ', $ChangeQuery)." WHERE `id` = {$Ally['id']};", 'alliance');
                    }

                    if(!empty($_POST['saveonly']) AND in_array($_POST['saveonly'], array('Mark01', 'Mark02', 'Mark03')))
                    {
                        $_Lang['GetLastMark'] = $_POST['saveonly'];
                    }
                }
                else
                {
                    $Warnings[] = $_Lang['ADM_TextChgNoAccess'];
                }
            }
            else if(isset($_POST['change']) && $_POST['change'] == 'reqset')
            {
                if($_ThisUserRank['like_admin'] === true)
                {
                    // Set, if Ally is open for new Requests
                    $NotChanged = 0;

                    $SetAllyOpen = intval($_POST['allyOpen']);
                    if($SetAllyOpen == 0 OR $SetAllyOpen == 1)
                    {
                        $SetAllyOpen = ($SetAllyOpen == 1 ? 0 : 1);
                        if($SetAllyOpen != $Ally['ally_request_notallow'])
                        {
                            $ChangeQuery[] = "`ally_request_notallow` = {$SetAllyOpen}";
                            $Ally['ally_request_notallow'] = $SetAllyOpen;
                            $Info[] = $_Lang['ADM_ReqSetOpenChanged'];
                        }
                        else
                        {
                            $NotChanged += 1;
                        }
                    }
                    else
                    {
                        $Warnings[] = $_Lang['ADM_ReqSetOpenBadOption'];
                    }
                    // Set Newcomer default Rank
                    $SetNewcomerRank = intval($_POST['newComerRank']);
                    if($SetNewcomerRank > 0)
                    {
                        if($SetNewcomerRank != $Ally['ally_new_rank_id'])
                        {
                            if(in_array($SetNewcomerRank, $NewcomerRankSet))
                            {
                                if(!in_array($SetNewcomerRank, $NewcomerRankSetGray))
                                {
                                    $ChangeQuery[] = "`ally_new_rank_id` = {$SetNewcomerRank}";
                                    $Ally['ally_new_rank_id'] = $SetNewcomerRank;
                                    $Info[] = $_Lang['ADM_ReqSetRankChanged'];
                                }
                                else
                                {
                                    $Warnings[] = $_Lang['ADM_OnlyAdminCanSetThis'];
                                }
                            }
                            else
                            {
                                $Warnings[] = $_Lang['ADM_RankNoExist'];
                            }
                        }
                        else
                        {
                            $NotChanged += 1;
                        }
                    }
                    else
                    {
                        if($SetNewcomerRank == 0)
                        {
                            $NotChanged += 1;
                        }
                        else
                        {
                            $Warnings[] = $_Lang['ADM_ReqSetBadRank'];
                        }
                    }

                    if($NotChanged == 2)
                    {
                        $Info[] = $_Lang['ADM_NothingChanged'];
                    }
                    if(!empty($ChangeQuery))
                    {
                        doquery("UPDATE {{table}} SET ".implode(', ', $ChangeQuery)." WHERE `id` = {$Ally['id']};", 'alliance');
                    }
                }
                else
                {
                    $Warnings[] = $_Lang['ADM_ReqSetNoAccess'];
                }
            }

            if(!empty($Info))
            {
                $_Lang['Info_Box'] = implode('<br/>', $Info);
                $_Lang['HideInfoBox'] = '';
            }
            else
            {
                $_Lang['HideInfoBox'] = 'hide';
            }
            if(!empty($Warnings))
            {
                $_Lang['Warn_Box'] = implode('<br/>', $Warnings);
                $_Lang['HideWarnBox'] = '';
            }
            else
            {
                $_Lang['HideWarnBox'] = 'hide';
            }

            $_Lang['Req_Text'] = $Ally['ally_request'];
            $_Lang['Int_Text'] = $Ally['ally_text'];
            $_Lang['Ext_Text'] = $Ally['ally_description'];

            if($Ally['ally_owner'] != $_User['id'])
            {
                $_Lang['HideHandOver'] = $_Lang['HideDeleteAlly'] = 'hide';
            }
            $_Lang['CollapseReqSetButton'] = 'collapse';
            if($_ThisUserRank['like_admin'] !== true)
            {
                $_Lang['CollapseReqSet'] = 'collapsed';
                $_Lang['CollapseReqSetButton'] = 'expand';
                $_Lang['DisableReqSetInputs'] = 'disabled';
            }
            if($_ThisUserRank['admingen'] !== true)
            {
                $_Lang['HideTextsSet'] = 'hide';
            }
            $_Lang['NewComersRankRows'] = '';
            $DisabledCount = 0;
            foreach($NewcomerRankSet as $RankID)
            {
                $ThisSetDisabled = '';
                $ThisSetSelected = '';
                if(in_array($RankID, $NewcomerRankSetGray))
                {
                    $ThisSetDisabled = ' class="disNCOpt" disabled';
                    $DisabledCount += 1;
                }
                if($RankID == $Ally['ally_new_rank_id'])
                {
                    $ThisSetSelected = ' selected';
                }
                $_Lang['NewComersRankRows'] .= "<option value=\"{$RankID}\"{$ThisSetDisabled}{$ThisSetSelected}>{$Ally['ally_ranks'][$RankID]['name']}</option>";
            }
            if(empty($_Lang['DisableReqSetInputs']) AND count($NewcomerRankSet) == $DisabledCount)
            {
                $_Lang['DisableReqSetRanks'] = 'disabled';
            }
            if($Ally['ally_request_notallow'] == 0)
            {
                $_Lang['AcceptReq_Select'] = 'selected';
            }
            else
            {
                $_Lang['BlockReq_Select'] = 'selected';
            }
            $_Lang['Insert_MaxLength_IntText'] = $_MaxLength_IntText;
            $_Lang['Insert_MaxLength_ExtText'] = $_MaxLength_ExtText;
            $_Lang['Insert_MaxLength_ReqText'] = $_MaxLength_Request;

            $Page = parsetemplate(gettemplate('alliance_admin'), $_Lang);
            display($Page, $_Lang['ADM_Title']);
        }
        else
        {
            // User is trying to use one of Admin Functions
            if($edit == 'info')
            {
                // User is trying to change Data about Ally
                if($_ThisUserRank['admingen'] !== true)
                {
                    message($_Lang['Ally_AccessDenied'], $MsgTitle, 'alliance.php?mode=admin', 3);
                }

                if($Ally['ally_owner'] != $_User['id'])
                {
                    $_Lang['HideChangeName'] = 'hide';
                    $_Lang['HideChangeTag'] = 'hide';
                }

                if(isset($_POST['change']) && $_POST['change'] == 'name')
                {
                    if($Ally['ally_owner'] != $_User['id'])
                    {
                        $Errors[] = $_Lang['ADM_OnlyLeaderChangeName'];
                    }
                    else
                    {
                        $NewName = trim($_POST['new_name']);
                        if(!empty($NewName))
                        {
                            if(strlen($NewName) <= $_MaxLength_AName)
                            {
                                if(preg_match(REGEXP_ALLYNAME_ABSOLUTE, $NewName))
                                {
                                    if($NewName != $Ally['ally_name'])
                                    {
                                        $CheckExist = doquery("SELECT `id` FROM {{table}} WHERE `ally_name` = '{$NewName}' LIMIT 1;", 'alliance', true);
                                        if($CheckExist['id'] <= 0)
                                        {
                                            doquery("UPDATE {{table}} SET `ally_name` = '{$NewName}' WHERE `id` = {$Ally['id']};", 'alliance');
                                            $Info[] = $_Lang['ADM_AllyNameChanged'];
                                        }
                                        else
                                        {
                                            $Errors[] = $_Lang['ADM_NewNameNotFree'];
                                        }
                                    }
                                    else
                                    {
                                        $Errors[] = $_Lang['ADM_SameName'];
                                    }
                                }
                                else
                                {
                                    $Errors[] = $_Lang['ADM_BadNewName'];
                                }
                            }
                            else
                            {
                                $Errors[] = $_Lang['ADM_NewNameLong'];
                            }
                        }
                        else
                        {
                            $Errors[] = $_Lang['ADM_NewNameEmpty'];
                        }
                    }
                }
                else if(isset($_POST['change']) && $_POST['change'] == 'tag')
                {
                    if($Ally['ally_owner'] != $_User['id'])
                    {
                        $Errors[] = $_Lang['ADM_OnlyLeaderChangeTag'];
                    }
                    else
                    {
                        $NewTag = trim($_POST['new_tag']);
                        if(!empty($NewTag))
                        {
                            $NewTagLength = strlen($NewTag);
                            if($NewTagLength >= $_MinLength_ATag)
                            {
                                if($NewTagLength <= $_MaxLength_ATag)
                                {
                                    if(preg_match(REGEXP_ALLYTAG_ABSOLUTE, $NewTag))
                                    {
                                        if($NewTag != $Ally['ally_tag'])
                                        {
                                            $CheckExist = doquery("SELECT `id` FROM {{table}} WHERE `ally_tag` = '{$NewTag}' LIMIT 1;", 'alliance', true);
                                            if($CheckExist['id'] <= 0)
                                            {
                                                doquery("UPDATE {{table}} SET `ally_tag` = '{$NewTag}' WHERE `id` = {$Ally['id']};", 'alliance');
                                                $Info[] = $_Lang['ADM_AllyTagChanged'];
                                            }
                                            else
                                            {
                                                $Errors[] = $_Lang['ADM_NewTagNotFree'];
                                            }
                                        }
                                        else
                                        {
                                            $Errors[] = $_Lang['ADM_SameTag'];
                                        }
                                    }
                                    else
                                    {
                                        $Errors[] = $_Lang['ADM_BadNewTag'];
                                    }
                                }
                                else
                                {
                                    $Errors[] = $_Lang['ADM_NewTagLong'];
                                }
                            }
                            else
                            {
                                $Errors[] = $_Lang['ADM_NewTagShort'];
                            }
                        }
                        else
                        {
                            $Errors[] = $_Lang['ADM_NewTagEmpty'];
                        }
                    }
                }
                else if(isset($_POST['change']) && $_POST['change'] == 'general')
                {
                    if(empty($_POST['website']))
                    {
                        $NewWebsite = '';
                    }
                    else
                    {
                        $_POST['website'] = trim($_POST['website']);
                        if(preg_match('/^(http\:\/\/|www\.|https\:\/\/){1}.*?$/D', $_POST['website'], $Matches))
                        {
                            $NewWebsite = getDBLink()->escape_string(strip_tags($_POST['website']));
                            if($Matches[1] == 'www.')
                            {
                                $NewWebsite = substr($NewWebsite, 4);
                                $NewWebsite = 'http://'.$NewWebsite;
                            }
                        }
                        else
                        {
                            $Errors[] = $_Lang['ADM_BadNewWebsite'];
                            $NewWebsite = null;
                        }
                    }
                    if($NewWebsite !== null AND $NewWebsite != $Ally['ally_web'])
                    {
                        $ChangeQuery[] = "`ally_web` = '{$NewWebsite}'";
                        $Ally['ally_web'] = $NewWebsite;
                    }
                    $RevealWebsite = (isset($_POST['website_reveal']) && $_POST['website_reveal'] == 'on' ? 1 : 0);
                    if($RevealWebsite != $Ally['ally_web_reveal'])
                    {
                        $ChangeQuery[] = "`ally_web_reveal` = {$RevealWebsite}";
                        $Ally['ally_web_reveal'] = $RevealWebsite;
                    }

                    if(empty($_POST['logourl']))
                    {
                        $NewLogo = '';
                    }
                    else
                    {
                        $_POST['logourl'] = trim($_POST['logourl']);
                        if(preg_match('/^(http\:\/\/|www\.|https\:\/\/){1}.*?$/D', $_POST['logourl'], $Matches))
                        {
                            $NewLogo = getDBLink()->escape_string(strip_tags($_POST['logourl']));
                            if($Matches[1] == 'www.')
                            {
                                $NewLogo = substr($NewLogo, 4);
                                $NewLogo = 'http://'.$NewLogo;
                            }
                        }
                        else
                        {
                            $Errors[] = $_Lang['ADM_BadNewLogo'];
                            $NewLogo = null;
                        }
                    }
                    if($NewLogo !== null AND $NewLogo != $Ally['ally_image'])
                    {
                        $ChangeQuery[] = "`ally_image` = '{$NewLogo}'";
                        $Ally['ally_image'] = $NewLogo;
                    }

                    if(!empty($ChangeQuery))
                    {
                        doquery("UPDATE {{table}} SET ".implode(', ', $ChangeQuery)." WHERE `id` = {$Ally['id']};", 'alliance');
                        $Info[] = $_Lang['ADM_AllyInfoChanged'];
                    }
                    else
                    {
                        $Errors[] = $_Lang['ADM_AllyInfoNotChanged'];
                    }
                }

                $_Lang['HideInfoBox_name'] = 'hide';
                $_Lang['HideInfoBox_tag'] = 'hide';
                $_Lang['HideInfoBox_general'] = 'hide';
                if(isset($_POST['change']))
                {
                    switch($_POST['change'])
                    {
                        case 'name':
                        {
                            $TextBoxes = 'name';
                            $_Lang['HideInfoBox_name'] = '';
                            break;
                        }
                        case 'tag':
                        {
                            $TextBoxes = 'tag';
                            $_Lang['HideInfoBox_tag']= '';
                            break;
                        }
                        case 'general':
                        {
                            $TextBoxes = 'general';
                            $_Lang['HideInfoBox_general']= '';
                            break;
                        }
                    }
                }

                if(!empty($Errors))
                {
                    if($TextBoxes != 'general')
                    {
                        $_Lang['InfoBox_color'] = 'red';
                        $_Lang['InfoBox_'.$TextBoxes] = implode('<br/>', $Errors);
                    }
                    else
                    {
                        $_Lang['InfoBox_'.$TextBoxes] = '<b class="red">'.implode('<br/>', $Errors).'</b>';
                    }
                }
                if(!empty($Info))
                {
                    if($TextBoxes != 'general')
                    {
                        $_Lang['InfoBox_color'] = 'lime';
                        $_Lang['InfoBox_'.$TextBoxes] = implode('<br/>', $Info);
                    }
                    else
                    {
                        if(!isset($_Lang['InfoBox_'.$TextBoxes]))
                        {
                            $_Lang['InfoBox_'.$TextBoxes] = '';
                        }
                        $_Lang['InfoBox_'.$TextBoxes] .= (empty($_Lang['InfoBox_'.$TextBoxes]) ? '' : '<br/>').'<b class="lime">'.implode('<br/>', $Info).'</b>';
                    }
                }
                $_Lang['CurrentWebsite'] = stripslashes($Ally['ally_web']);
                $_Lang['CheckRevealWebsite'] = ($Ally['ally_web_reveal'] == 1 ? 'checked' : '');
                $_Lang['CurrentLogoUrl'] = stripslashes($Ally['ally_image']);

                $Page = parsetemplate(gettemplate('alliance_admin_changedata'), $_Lang);
                display($Page, $_Lang['ADM_Title']);
            }
            else if($edit == 'handover')
            {
                // User is trying to HandOver the Ally
                if($Ally['ally_owner'] != $_User['id'])
                {
                    message($_Lang['Ally_AccessDenied'], $MsgTitle, 'alliance.php?mode=admin', 3);
                }
                if($Ally['ally_members'] == 1)
                {
                    message($_Lang['ADM_OnlyOwnerInAlly'], $MsgTitle, 'alliance.php?mode=admin', 3);
                }

                $Query_AHandOver_Members = '';
                $Query_AHandOver_Members .= "SELECT `id`, `username`, `ally_rank_id` FROM {{table}} ";
                $Query_AHandOver_Members .= "WHERE `ally_id` = {$Ally['id']} AND `id` != {$_User['id']};";

                $Result_AHandOver_Members = doquery($Query_AHandOver_Members, 'users');
                $_Lang['UserList'] = '';
                while($FetchData = $Result_AHandOver_Members->fetch_assoc())
                {
                    $_Lang['UserList'] .= "<option value=\"{$FetchData['id']}\">{$FetchData['username']} ({$Ally['ally_ranks'][$FetchData['ally_rank_id']]['name']})</option>";
                    $Members[$FetchData['id']] = $FetchData['ally_rank_id'];
                }

                $_Lang['HideError'] = 'hide';
                if(isset($_POST['send']) && $_POST['send'] == 'yes')
                {
                    $NewOwner = intval($_POST['new_owner']);
                    if($NewOwner > 0)
                    {
                        if(array_key_exists($NewOwner, $Members))
                        {
                            doquery("UPDATE {{table}} SET `ally_owner` = {$NewOwner} WHERE `id` = {$Ally['id']};", 'alliance');
                            doquery("INSERT INTO {{table}} (`id`, `ally_rank_id`) VALUES ({$_User['id']}, {$Members[$NewOwner]}), ({$NewOwner}, 0) ON DUPLICATE KEY UPDATE `ally_rank_id` = VALUES(`ally_rank_id`);", 'users');
                            message($_Lang['ADM_HandOverDone'], $MsgTitle, 'alliance.php', 3);
                        }
                        else
                        {
                            $ErrorBox = $_Lang['ADM_BadUserIDHO'];
                        }
                    }
                    else
                    {
                        $ErrorBox = $_Lang['ADM_NoUserIDHO'];
                    }
                    $_Lang['HideError'] = '';
                    $_Lang['ErrorText'] = $ErrorBox;
                }

                $Page = parsetemplate(gettemplate('alliance_admin_handover'), $_Lang);
                display($Page, $_Lang['ADM_Title']);
            }
            else if($edit == 'delete')
            {
                // User is trying to Delete Ally
                if($Ally['ally_owner'] != $_User['id'])
                {
                    message($_Lang['Ally_AccessDenied'], $MsgTitle, 'alliance.php?mode=admin', 3);
                }

                $_DisplaySettings['dontShow_AllyChat_MsgCount'] = true;
                $_DisplaySettings['dontShow_AllyChat_Link'] = true;

                doquery("DELETE FROM {{table}} WHERE `id` = {$Ally['id']};", 'alliance');
                doquery("DELETE FROM {{table}} WHERE `AllyID` = {$Ally['id']};", 'ally_invites');
                doquery("DELETE FROM {{table}} WHERE `AllyID_Sender` = {$Ally['id']} OR `AllyID_Owner` = {$Ally['id']};", 'ally_pacts');
                if($Ally['ally_ChatRoom_ID'] > 0)
                {
                    doquery("DELETE FROM {{table}} WHERE `ID` = {$Ally['ally_ChatRoom_ID']} LIMIT 1;", 'chat_rooms');
                    doquery("DELETE FROM {{table}} WHERE `RID` = {$Ally['ally_ChatRoom_ID']};", 'chat_messages');
                    doquery("DELETE FROM {{table}} WHERE `RID` = {$Ally['ally_ChatRoom_ID']};", 'chat_online');
                }
                doquery("UPDATE {{table}} SET `ally_id` = 0, `ally_register_time` = 0, `ally_rank_id` = 0, `ally_request` = 0, `ally_request_text` = '' WHERE `ally_id` = {$Ally['id']} OR `ally_request` = {$Ally['id']};", 'users');
                message($_Lang['ADM_AllyDeleted'], $MsgTitle, 'alliance.php', 3);
            }
            else if($edit == 'members')
            {
                // User is trying to Manage Members
                if($_ThisUserRank['mlist'] !== true OR ($_ThisUserRank['mlist_mod'] !== true AND $_ThisUserRank['cankick'] !== true))
                {
                    message($_Lang['Ally_AccessDenied'], $MsgTitle, 'alliance.php?mode=admin', 3);
                }

                foreach($Ally['ally_ranks'] as $RankID => $RankData)
                {
                    if($RankID == 0 OR $RankID == $_User['ally_rank_id'])
                    {
                        continue;
                    }
                    if($_ThisUserRank['like_admin'] === true)
                    {
                        if($RankData['like_admin'] !== true OR $_ThisUserRank['is_admin'] === true)
                        {
                            $AllowedRanksChange[] = $RankID;
                        }
                    }
                    else
                    {
                        if($RankData['like_admin'] === true)
                        {
                            continue;
                        }
                        else
                        {
                            $DisallowChange = false;
                            foreach($_ThisUserRank as $Key => $Value)
                            {
                                if($Key == 'name')
                                {
                                    continue;
                                }
                                if($Key == 'mlist_mod' OR $Key == 'cankick' OR $Key == 'ranks_mod')
                                {
                                    if($RankData[$Key] === true)
                                    {
                                        $DisallowChange = true;
                                        break;
                                    }
                                }
                                else
                                {
                                    if($Value !== true AND $RankData[$Key] === true)
                                    {
                                        $DisallowChange = true;
                                        break;
                                    }
                                }
                            }
                            if($DisallowChange === false)
                            {
                                $AllowedRanksChange[] = $RankID;
                            }
                        }
                    }
                }
                $_Lang['HideInfoBox'] = 'hide';

                if(!empty($_GET['kick']))
                {
                    $InfoBoxCol = 'red';
                    if($_ThisUserRank['cankick'] === true)
                    {
                        $KickID = intval($_GET['kick']);
                        if($KickID > 0)
                        {
                            if($KickID != $_User['id'])
                            {
                                $GetUser = doquery("SELECT `id`, `username`, `ally_id`, `ally_rank_id` FROM {{table}} WHERE `id` = {$KickID} LIMIT 1;", 'users', true);
                                if($GetUser['id'] == $KickID AND $GetUser['ally_id'] == $Ally['id'])
                                {
                                    if(in_array($GetUser['ally_rank_id'], $AllowedRanksChange))
                                    {
                                        doquery("UPDATE {{table}} SET `ally_id` = 0, `ally_rank_id` = 0 WHERE `id` = {$KickID};", 'users');
                                        doquery("UPDATE {{table}} SET `ally_members` = `ally_members` - 1 WHERE `id` = {$Ally['id']};", 'alliance');
                                        doquery("UPDATE {{table}} SET `State` = -3 WHERE `State` = 1 AND `SenderID` = {$KickID};", 'ally_invites');
                                        $Ally['ally_members'] -= 1;
                                        $InfoBoxTxt = sprintf($_Lang['ADM_MemberXKicked'], $GetUser['username']);
                                        $InfoBoxCol = 'lime';
                                    }
                                    else
                                    {
                                        $InfoBoxTxt = $_Lang['ADM_CantKickRank'];
                                    }
                                }
                                else
                                {
                                    $InfoBoxTxt = $_Lang['ADM_CantKickBadUser'];
                                }
                            }
                            else
                            {
                                $InfoBoxTxt = $land['ADM_CantKickYourself'];
                            }
                        }
                        else
                        {
                            $InfoBoxTxt = $_Lang['ADM_CantKickBadID'];
                        }
                    }
                    else
                    {
                        $InfoBoxTxt = $_Lang['ADM_YouCantKick'];
                    }

                    $_Lang['HideInfoBox']= '';
                    $_Lang['InfoBoxText']= $InfoBoxTxt;
                    $_Lang['InfoBoxColor'] = $InfoBoxCol;
                }
                else if(isset($_POST['send']) && $_POST['send'] == 'yes')
                {
                    $InfoBoxCol = 'red';

                    $ErrorsChanging = 0;
                    $RankIsSame = 0;
                    $ErrorsSaving = 0;

                    if($_ThisUserRank['mlist_mod'] === true)
                    {
                        foreach($_POST['change_rank'] as $UserID => $NewRank)
                        {
                            $UserID = intval($UserID);
                            $NewRank = intval($NewRank);
                            if($UserID > 0 AND $NewRank > 0 AND in_array($NewRank, $AllowedRanksChange))
                            {
                                $RankChanges[$UserID] = $NewRank;
                            }
                            else
                            {
                                $ErrorsChanging += 1;
                            }
                        }
                        if(!empty($RankChanges))
                        {
                            $MembersList = doquery("SELECT `id`, `ally_id`, `ally_rank_id` FROM {{table}} WHERE `id` IN (".implode(', ', array_keys($RankChanges)).");", 'users');
                            if($MembersList->num_rows > 0)
                            {
                                while($FetchData = $MembersList->fetch_assoc())
                                {
                                    if($FetchData['ally_id'] == $Ally['id'] AND $RankChanges[$FetchData['id']] != $FetchData['ally_rank_id'] AND in_array($FetchData['ally_rank_id'], $AllowedRanksChange))
                                    {
                                        $DoChange[$FetchData['id']] = $RankChanges[$FetchData['id']];
                                    }
                                    else
                                    {
                                        if($RankChanges[$FetchData['id']] != $FetchData['ally_rank_id'])
                                        {
                                            $RankIsSame += 1;
                                        }
                                        else
                                        {
                                            $ErrorsSaving += 1;
                                        }
                                    }
                                }

                                if(!empty($DoChange))
                                {
                                    $UpdateQuery = "INSERT INTO {{table}} (`id`, `ally_rank_id`) VALUES ";
                                    foreach($DoChange as $UserID => $RankID)
                                    {
                                        $UpdateQueryArr[] = "({$UserID}, {$RankID})";
                                    }
                                    $UpdateQuery .= implode(', ', $UpdateQueryArr);
                                    $UpdateQuery .= " ON DUPLICATE KEY UPDATE ";
                                    $UpdateQuery .= "`ally_rank_id` = VALUES(`ally_rank_id`);";
                                    doquery($UpdateQuery, 'users');

                                    if(count($DoChange) == (count($RankChanges) - $RankIsSame + $ErrorsChanging))
                                    {
                                        $InfoBoxTxt = $_Lang['ADM_MListModAllDone'];
                                    }
                                    else
                                    {
                                        $InfoBoxTxt = $_Lang['ADM_MListModNAllDone'];
                                    }
                                    $InfoBoxCol = 'lime';
                                }
                                else
                                {
                                    if($ErrorsSaving > 0)
                                    {
                                        $InfoBoxTxt = $_Lang['ADM_MListModBadData2'];
                                    }
                                    else
                                    {
                                        $InfoBoxTxt = $_Lang['ADM_MListModBadData'];
                                    }
                                }
                            }
                            else
                            {
                                $InfoBoxTxt = $_Lang['ADM_MListModBadData'];
                            }
                        }
                        else
                        {
                            $InfoBoxTxt = $_Lang['ADM_MListModBadData'];
                        }
                    }
                    else
                    {
                        $InfoBoxTxt = $_Lang['ADM_YouCantMListMod'];
                    }

                    $_Lang['HideInfoBox'] = '';
                    $_Lang['InfoBoxText'] = $InfoBoxTxt;
                    $_Lang['InfoBoxColor'] = $InfoBoxCol;
                }

                createMembersList(true);
                $Page = parsetemplate(gettemplate('alliance_admin_memberslist_table'), $_Lang);
                display($Page, $_Lang['ADM_Title']);
            }
            else if($edit == 'reqlist')
            {
                // User wants to Look at Requests
                if(!isset($_GET['from']) || $_GET['from'] != 'front')
                {
                    $GoBackTo = 'alliance.php?mode=admin';
                    $_Lang['GoBackLink'] = '?mode=admin';
                }
                else
                {
                    $GoBackTo = 'alliance.php';
                    $_Lang['GoBackLink'] = 'alliance.php';
                }
                if($_ThisUserRank['lookreq'] !== true)
                {
                    message($_Lang['Ally_AccessDenied'], $MsgTitle, $GoBackTo, 3);
                }

                if(!empty($_POST['rq']))
                {
                    $InfoBoxCol = 'red';
                    if($_ThisUserRank['managereq'] === true)
                    {
                        $RequestUser = intval($_POST['rq']);
                        if($RequestUser > 0)
                        {
                            if($_POST['opt'] == 1 OR $_POST['opt'] == 2)
                            {
                                $CheckRequest = doquery("SELECT `id`, `username`, `ally_request` FROM {{table}} WHERE `id` = {$RequestUser} LIMIT 1;", 'users', true);
                                if($CheckRequest['id'] == $RequestUser)
                                {
                                    if($CheckRequest['ally_request'] == $Ally['id'])
                                    {
                                        if($_POST['opt'] == 1)
                                        {
                                            doquery("UPDATE {{table}} SET `ally_request` = 0, `ally_request_text` = '', `ally_id` = {$Ally['id']}, `ally_rank_id` = {$Ally['ally_new_rank_id']}, `ally_register_time` = UNIX_TIMESTAMP() WHERE `id` = {$RequestUser};", 'users');
                                            doquery("UPDATE {{table}} SET `ally_members` = `ally_members` + 1 WHERE `id` = {$Ally['id']};", 'alliance');
                                            if($Ally['ally_ChatRoom_ID'] > 0)
                                            {
                                                $Query_UpdateChatRoomOnline = '';
                                                $Query_UpdateChatRoomOnline .= "INSERT INTO {{table}} VALUES ({$Ally['ally_ChatRoom_ID']}, {$RequestUser}, UNIX_TIMESTAMP()) ";
                                                $Query_UpdateChatRoomOnline .= "ON DUPLICATE KEY UPDATE ";
                                                $Query_UpdateChatRoomOnline .= "`LastOnline` = VALUES(`LastOnline`);";
                                                doquery($Query_UpdateChatRoomOnline, 'chat_online');
                                            }

                                            $Message = array();
                                            $Message['msg_id'] = '023';
                                            $Message['args'] = array($Ally['ally_name']);
                                            $Message = json_encode($Message);
                                            Cache_Message($RequestUser, 0, NULL, 2, '005', '010', $Message);

                                            $InfoBoxCol = 'lime';
                                            $InfoBoxTxt = sprintf($_Lang['ADM_RL_ReqAccepted'], $CheckRequest['username']);

                                            CheckJobsDone('BUDDY_OR_ALLY_TASK', $RequestUser);
                                        }
                                        else
                                        {
                                            doquery("UPDATE {{table}} SET `ally_request` = 0, `ally_request_text` = '', `ally_register_time` = 0 WHERE `id` = {$RequestUser};", 'users');

                                            $RejectReason = strip_tags(stripslashes($_POST['rjr']));
                                            if(strlen($RejectReason) > $_MaxLength_Reject)
                                            {
                                                $RejectReason = substr($RejectReason, 0, $_MaxLength_Reject);
                                            }
                                            else if(strlen($RejectReason) == 0)
                                            {
                                                $RejectReason = $_Lang['ADM_RL_NoReason'];
                                            }
                                            $Message = array();
                                            $Message['msg_id'] = '024';
                                            $Message['args'] = array($Ally['ally_name'], $RejectReason);
                                            $Message = json_encode($Message);
                                            Cache_Message($RequestUser, 0, NULL, 2, '005', '011', $Message);

                                            $InfoBoxCol = 'lime';
                                            $InfoBoxTxt = sprintf($_Lang['ADM_RL_ReqRejected'], $CheckRequest['username']);
                                        }
                                    }
                                    else
                                    {
                                        $InfoBoxTxt = $_Lang['ADM_RL_UserNotInAlly'];
                                    }
                                }
                                else
                                {
                                    $InfoBoxTxt = $_Lang['ADM_RL_UserNoExist'];
                                }
                            }
                            else
                            {
                                $InfoBoxTxt = $_Lang['ADM_RL_BadOption'];
                            }
                        }
                        else
                        {
                            $InfoBoxTxt = $_Lang['ADM_RL_BadID'];
                        }
                    }
                    else
                    {
                        $InfoBoxTxt = $_Lang['ADM_RL_NoManageAccess'];
                    }
                }

                if(!empty($InfoBoxTxt))
                {
                    $_Lang['InfoBoxText']= $InfoBoxTxt;
                    $_Lang['InfoBoxColor'] = $InfoBoxCol;
                }
                else
                {
                    $_Lang['HideInfoBox']= 'hide';
                }

                $SortType = (isset($_GET['stype']) ? intval($_GET['stype']) : null);
                $SortMode = (isset($_GET['smode']) ? intval($_GET['smode']) : null);

                $ThisSorting = 'class="sortHigh"';
                switch($SortType)
                {
                    case 1:
                        $SortBy = '`username`';
                        $_Lang['sortByName'] = $ThisSorting;
                        break;
                    case 2:
                        $SortBy = '`total_rank`';
                        $_Lang['sortByStats'] = $ThisSorting;
                        break;
                    case 3:
                        $SortBy = '`ally_register_time`';
                        $_Lang['sortBySendDate'] = $ThisSorting;
                        break;
                    default:
                        $SortBy = '`id`';
                        break;
                }
                switch($SortMode)
                {
                    case 'desc':
                        $SortHow = 'DESC';
                        $_Lang['sortRev'] = 'asc';
                        break;
                    default:
                        $SortHow = 'ASC';
                        $_Lang['sortRev'] = 'desc';
                        break;
                }

                $Query_ARequests_Get = '';
                $Query_ARequests_Get .= "SELECT `id`, `username`, `ally_register_time`, `ally_request_text`, `Stats`.`total_rank`, `Stats`.`total_points` FROM {{table}} ";
                $Query_ARequests_Get .= "LEFT JOIN {{prefix}}statpoints AS `Stats` ON `Stats`.`id_owner` = {{table}}.`id` AND `Stats`.`stat_type` = 1 ";
                $Query_ARequests_Get .= "WHERE `ally_request` = {$Ally['id']} ORDER BY {$SortBy} {$SortHow};";

                $GetRequests = doquery($Query_ARequests_Get, 'users');
                $ReqCount = $GetRequests->num_rows;
                $_Lang['RequestCount'] = $ReqCount;

                if($_ThisUserRank['managereq'] === true)
                {
                    $TableTPL = gettemplate('alliance_admin_reqlist_mg_table');
                    $RowTPL = gettemplate('alliance_admin_reqlist_mg_row');
                }
                else
                {
                    $TableTPL = gettemplate('alliance_admin_reqlist_lk_table');
                    $RowTPL = gettemplate('alliance_admin_reqlist_lk_row');
                }

                if($ReqCount > 0)
                {
                    $_Lang['HideNoRequests'] = 'hide';
                    $_Lang['RequestRows'] = '';
                    while($Request = $GetRequests->fetch_assoc())
                    {
                        $Request['ally_request_text'] = stripslashes($Request['ally_request_text']);
                        if($Request['total_rank'] > 0)
                        {
                            $Request['StatPoints'] = "{$Request['total_rank']} (".prettyNumber($Request['total_points'])." {$_Lang['ADM_RL_points']})";
                        }
                        else
                        {
                            $Request['StatPoints'] = '<b class="ncalc">0</b>';
                        }
                        $Request['SendDate'] = prettyDate('d m Y - H:i:s', $Request['ally_register_time'], 1);
                        $Request['ADM_RL_Accept'] = $_Lang['ADM_RL_Accept'];
                        $Request['ADM_RL_Refuse'] = $_Lang['ADM_RL_Refuse'];
                        $Request['ADM_RL_RejectReason'] = $_Lang['ADM_RL_RejectReason'];
                        $Request['ADM_RL_Save'] = $_Lang['ADM_RL_Save'];

                        $_Lang['RequestRows'] .= parsetemplate($RowTPL, $Request);
                    }
                }
                $Page = parsetemplate($TableTPL, $_Lang);
                display($Page, $_Lang['ADM_Title']);
            }
            else if($edit == 'ranks')
            {
                // User is trying to View RankList (and manage Ranks)
                if($_ThisUserRank['ranks_mod'] !== true)
                {
                    message($_Lang['Ally_AccessDenied'], $MsgTitle, 'alliance.php?mode=admin', 3);
                }

                if($_ThisUserRank['is_admin'] === true)
                {
                    $ImAllyOwner = true;
                }
                else
                {
                    $ImAllyOwner = false;
                }

                // Set Default Disabled for Options
                $DefaultDisabledOpt = array();
                if(!$ImAllyOwner)
                {
                    if($_ThisUserRank['like_admin'] !== true)
                    {
                        $DefaultDisabledOpt = array(1, 8, 9, 10, 11);
                        if($_ThisUserRank['mlist'] !== true)
                        {
                            $DefaultDisabledOpt[] = 2;
                            $DefaultDisabledOpt[] = 3;
                        }
                        else if($_ThisUserRank['mlist_online'] !== true)
                        {
                            $DefaultDisabledOpt[] = 3;
                        }
                        if($_ThisUserRank['sendmsg'] !== true)
                        {
                            $DefaultDisabledOpt[] = 4;
                        }
                        if($_ThisUserRank['admingen'] !== true)
                        {
                            $DefaultDisabledOpt[] = 5;
                        }
                        if($_ThisUserRank['lookreq'] !== true)
                        {
                            $DefaultDisabledOpt[] = 6;
                            $DefaultDisabledOpt[] = 7;
                        }
                        else if($_ThisUserRank['managereq'] !== true)
                        {
                            $DefaultDisabledOpt[] = 7;
                        }
                    }
                    else
                    {
                        $DefaultDisabledOpt[] = 1;
                    }
                }
                $_Lang['HideInfoBox'] = 'hide';

                $Result_ARanks_Counter = doquery("SELECT COUNT(*) AS `Count`, `ally_rank_id` FROM {{table}} WHERE `ally_id` = {$Ally['id']} GROUP BY `ally_rank_id`;", 'users');
                while($FetchData = $Result_ARanks_Counter->fetch_assoc())
                {
                    $RanksCountArray[$FetchData['ally_rank_id']] = $FetchData['Count'];
                }

                if(!empty($_POST['action']))
                {
                    $InfoBoxCol = 'red';
                    if($_POST['action'] == 'add')
                    {
                        $NewName = trim($_POST['newName']);
                        $NewNameLower = strtolower($NewName);
                        $NewNameLen = strlen($NewName);
                        if($NewNameLen >= $_MinLength_RankName)
                        {
                            if($NewNameLen <= $_MaxLength_RankName)
                            {
                                if(preg_match($RankNameRegExp, $NewName))
                                {
                                    $BreakNameOcu = false;
                                    foreach($Ally['ally_ranks'] as $RankID => $RankData)
                                    {
                                        if(strtolower($RankData['name']) == $NewNameLower)
                                        {
                                            $BreakNameOcu = true;
                                            break;
                                        }
                                    }
                                    if($BreakNameOcu === false)
                                    {
                                        $BreakBadOpt = false;
                                        for($i = 1; $i < $Ally['ally_ranks_count']; $i += 1)
                                        {
                                            if(isset($_POST['opt'][$i]) && $_POST['opt'][$i] == 'on')
                                            {
                                                if(in_array($i, $DefaultDisabledOpt))
                                                {
                                                    $BreakBadOpt = true;
                                                    break;
                                                }
                                                else
                                                {
                                                    $NewRankOpts[$i] = true;
                                                }
                                            }
                                            else
                                            {
                                                $NewRankOpts[$i] = false;
                                            }
                                        }
                                        if($BreakBadOpt === false)
                                        {
                                            if($NewRankOpts[1] === true)
                                            {
                                                foreach($NewRankOpts as $ID => $Value)
                                                {
                                                    if($Value !== true)
                                                    {
                                                        $NewRankOpts[1] = false;
                                                        break;
                                                    }
                                                }
                                            }
                                            if($NewRankOpts[3] === true)
                                            {
                                                if($NewRankOpts[2] !== true)
                                                {
                                                    $NewRankOpts[3] = false;
                                                }
                                            }
                                            if($NewRankOpts[7] === true)
                                            {
                                                if($NewRankOpts[6] !== true)
                                                {
                                                    $NewRankOpts[7] = false;
                                                }
                                            }
                                            $NewRankAdd = array_merge(array(0 => $NewName), $NewRankOpts);
                                            $Ally['ally_ranks_org'][] = $NewRankAdd;
                                            foreach($NewRankAdd as $DataID => $DataVal)
                                            {
                                                $NewRankData[$RankDataLabels[$DataID]] = $DataVal;
                                            }
                                            $Ally['ally_ranks'][] = $NewRankData;

                                            $NewRanksObj = getDBLink()->escape_string(json_encode($Ally['ally_ranks_org']));

                                            doquery(
                                                "UPDATE {{table}} SET `ally_ranks` = '{$NewRanksObj}' WHERE `id` = {$Ally['id']};",
                                                'alliance'
                                            );

                                            $InfoBoxCol = 'lime';
                                            $InfoBoxTxt = $_Lang['ADM_RkL_AddedRank'];
                                        }
                                        else
                                        {
                                            if($BreakBadData)
                                            {
                                                $InfoBoxTxt = $_Lang['ADM_RkL_BadOptGiven'];
                                            }
                                            else
                                            {
                                                $InfoBoxTxt = $_Lang['ADM_RkL_ForbiddenOptGiven'];
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $InfoBoxTxt = $_Lang['ADM_RkL_RankExists'];
                                    }
                                }
                                else
                                {
                                    $InfoBoxTxt = $_Lang['ADM_RkL_ForbiddenSigns'];
                                }
                            }
                            else
                            {
                                $InfoBoxTxt = $_Lang['ADM_RkL_NameLong'];
                            }
                        }
                        else
                        {
                            $InfoBoxTxt = $_Lang['ADM_RkL_NameShort'];
                        }
                    }
                    else if($_POST['action'] == 'saveChg')
                    {
                        if(!empty($_POST['chgData']))
                        {
                            $ChangingErrors = 0;
                            $NoChanges = 0;
                            $RankNoExists = 0;

                            foreach($Ally['ally_ranks'] as $RankData)
                            {
                                $ExistingNames[] = strtolower($RankData['name']);
                            }
                            foreach($_POST['chgData'] as $RankID => $RankChgData)
                            {
                                if(!empty($Ally['ally_ranks'][$RankID]['name']))
                                {
                                    $ParsedRankData = &$Ally['ally_ranks'][$RankID];
                                    if($ImAllyOwner OR ($_ThisUserRank['like_admin'] === true AND $ParsedRankData['like_admin'] !== true) OR
                                    (
                                        $_ThisUserRank['like_admin'] !== true AND
                                        $ParsedRankData['like_admin'] !== true AND
                                        $ParsedRankData['ranks_mod'] !== true AND
                                        $ParsedRankData['mlist_mod'] !== true AND
                                        $ParsedRankData['cankick'] !== true
                                    ))
                                    {
                                        $ThisCanChangeName= true;
                                        $ThisCanChangeRights= true;
                                    }
                                    else
                                    {
                                        $ThisCanChangeName = false;
                                        $ThisCanChangeRights = false;
                                        $ThisCantChangeName = 'na_nonam';
                                        $ThisCantChangeRights = 'na_nochg';
                                    }
                                    if($RankID == 0)
                                    {
                                        $ThisCanChangeRights = false;
                                        $ThisCantChangeRights = 'adm_nochg';
                                    }
                                    if($ThisCanChangeName)
                                    {
                                        $ChangingErrors += 1;
                                        $ThisNewName = trim($RankChgData[0]);
                                        $ThisNewNameLower = strtolower($ThisNewName);
                                        $ThisNewNameLen = strlen($ThisNewName);
                                        if($ThisNewNameLen > 0)
                                        {
                                            if($ThisNewNameLower != strtolower($ParsedRankData['name']))
                                            {
                                                if(preg_match($RankNameRegExp, $ThisNewName))
                                                {
                                                    if($ThisNewNameLen >= $_MinLength_RankName)
                                                    {
                                                        if($ThisNewNameLen <= $_MaxLength_RankName)
                                                        {
                                                            if(!in_array($ThisNewNameLower, $ExistingNames))
                                                            {
                                                                $SaveChanges[$RankID][0] = $ThisNewName;
                                                                $ChangingErrors -= 1;
                                                            }
                                                            else
                                                            {
                                                                $ErrorsFound['NameExists'] += 1;
                                                            }
                                                        }
                                                        else
                                                        {
                                                            $ErrorsFound['NameLong'] += 1;
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $ErrorsFound['NameShort'] += 1;
                                                    }
                                                }
                                                else
                                                {
                                                    $ErrorsFound['NamePregMatch'] += 1;
                                                }
                                            }
                                            else
                                            {
                                                $NoChanges += 1;
                                                $ChangingErrors -= 1;
                                            }
                                        }
                                        else
                                        {
                                            $ErrorsFound['NameEmpty'] += 1;
                                        }
                                    }
                                    if($ThisCanChangeRights)
                                    {
                                        for($i = 1; $i < $Ally['ally_ranks_count']; $i += 1)
                                        {
                                            $RankChgData[$i] = (isset($RankChgData[$i]) && $RankChgData[$i] == 'on' ? true : false);
                                            if($RankChgData[$i] !== $Ally['ally_ranks_org'][$RankID][$i])
                                            {
                                                if(in_array($i, $DefaultDisabledOpt))
                                                {
                                                    $ChangingErrors += 1;
                                                    $ErrorsFound['Opt'.$i.'Disabled'] += 1;
                                                }
                                                else
                                                {
                                                    $SaveChanges[$RankID][$i] = $RankChgData[$i];
                                                }
                                            }
                                            else
                                            {
                                                $NoChanges += 1;
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $RankNoExists += 1;
                                    $ErrorsFound['RankNoExists'] += 1;
                                }
                            }
                            if(!empty($SaveChanges))
                            {
                                foreach($SaveChanges as $RankID => $RankData)
                                {
                                    $Recalculate = false;
                                    foreach($RankData as $DataID => $DataValue)
                                    {
                                        $Ally['ally_ranks_org'][$RankID][$DataID] = $DataValue;
                                        $Ally['ally_ranks'][$RankID][$RankDataLabels[$DataID]] = $DataValue;
                                    }
                                    $RankShortcut = &$Ally['ally_ranks_org'][$RankID];
                                    if($RankShortcut[1] === true)
                                    {
                                        foreach($RankShortcut as $ID => $Value)
                                        {
                                            if($ID == 0)
                                            {
                                                continue;
                                            }
                                            if($Value !== true)
                                            {
                                                $RankShortcut[1] = false;
                                                $Recalculate = true;
                                                break;
                                            }
                                        }
                                    }
                                    if($RankShortcut[3] === true)
                                    {
                                        if($RankShortcut[2] !== true)
                                        {
                                            $RankShortcut[3] = false;
                                            $Recalculate = true;
                                        }
                                    }
                                    if($RankShortcut[7] === true)
                                    {
                                        if($RankShortcut[6] !== true)
                                        {
                                            $RankShortcut[7] = false;
                                            $Recalculate = true;
                                        }
                                    }
                                    if($Recalculate === true)
                                    {
                                        foreach($RankShortcut as $DataID => $DataValue)
                                        {
                                            $Ally['ally_ranks'][$RankID][$RankDataLabels[$DataID]] = $DataValue;
                                        }
                                    }
                                }

                                $NewRanksObj = getDBLink()->escape_string(json_encode($Ally['ally_ranks_org']));

                                doquery("UPDATE {{table}} SET `ally_ranks` = '{$NewRanksObj}' WHERE `id` = {$Ally['id']};", 'alliance');
                                $InfoBoxCol = 'lime';
                                if($RankNoExists > 0 OR $ChangingErrors > 0)
                                {
                                    $InfoBoxTxt = $_Lang['ADM_RkL_PosChgSaved'];
                                }
                                else
                                {
                                    $InfoBoxTxt = $_Lang['ADM_RkL_AllChgSaved'];
                                }
                            }
                            else
                            {
                                $InfoBoxCol = 'orange';
                                $InfoBoxTxt = $_Lang['ADM_RkL_NothingChanged'];
                                if($RankNoExists > 0 OR $ChangingErrors > 0)
                                {
                                    $InfoBoxTxt .= '<br/>'.$_Lang['ADM_RkL_ChgErrors'];
                                }
                            }
                        }
                        else
                        {
                            $InfoBoxTxt = $_Lang['ADM_RkL_EmptyData'];
                        }
                    }
                    else if(substr($_POST['action'], 0, 4) == 'del_')
                    {
                        $RankID = intval(substr($_POST['action'], 4));
                        if($RankID > 0)
                        {
                            if(!empty($Ally['ally_ranks'][$RankID]['name']))
                            {
                                if($ImAllyOwner OR ($_ThisUserRank['like_admin'] === true AND $RankData['like_admin'] !== true) OR
                                (
                                    $_ThisUserRank['like_admin'] !== true AND
                                    $RankData['like_admin'] !== true AND
                                    $RankData['ranks_mod'] !== true AND
                                    $RankData['mlist_mod'] !== true AND
                                    $RankData['cankick'] !== true
                                ))
                                {
                                    $ThisCanDelete = true;
                                }
                                else
                                {
                                    $ThisCanDelete = false;
                                    $ThisCantDelete = 'na_nodel';
                                }
                                if($RankID == 0)
                                {
                                    $ThisCanDelete = false;
                                    $ThisCantDelete = 'adm_nodel';
                                }
                                if($RankID == $Ally['ally_new_rank_id'])
                                {
                                    $ThisCanDelete = false;
                                    $ThisCantDelete = 'nc_nodel';
                                }

                                if($ThisCanDelete)
                                {
                                    if(isset($RanksCountArray[$RankID]) && $RanksCountArray[$RankID] > 0)
                                    {
                                        $RanksCountArray[$Ally['ally_new_rank_id']] += $RanksCountArray[$RankID];
                                        doquery("UPDATE {{table}} SET `ally_rank_id` = {$Ally['ally_new_rank_id']} WHERE `ally_id` = {$Ally['id']} AND `ally_rank_id` = {$RankID};", 'users');
                                    }
                                    unset($Ally['ally_ranks'][$RankID]);
                                    unset($Ally['ally_ranks_org'][$RankID]);

                                    $NewRanksObj = getDBLink()->escape_string(json_encode($Ally['ally_ranks_org']));

                                    doquery("UPDATE {{table}} SET `ally_ranks` = '{$NewRanksObj}' WHERE `id` = {$Ally['id']};", 'alliance');

                                    $InfoBoxCol = 'lime';
                                    $InfoBoxTxt = $_Lang['ADM_RkL_RankDeleted'];
                                }
                                else
                                {
                                    $InfoBoxTxt = $_Lang['ADM_RkL_RankDelete_'.$ThisCantDelete];
                                }
                            }
                            else
                            {
                                $InfoBoxTxt = $_Lang['ADM_RkL_RankNoExists'];
                            }
                        }
                        else
                        {
                            if(substr($_POST['action'], 4) == '0')
                            {
                                $InfoBoxTxt = $_Lang['ADM_RkL_RankDelete_adm_nodel'];
                            }
                            else
                            {
                                $InfoBoxTxt = $_Lang['ADM_RkL_BadRankIDGiven'];
                            }
                        }
                    }
                    else
                    {
                        $InfoBoxTxt = $_Lang['ADM_RkL_BadPostAction'];
                    }

                    $_Lang['HideInfoBox'] = '';
                    $_Lang['InfoBoxText'] = $InfoBoxTxt;
                    $_Lang['InfoBoxColor'] = $InfoBoxCol;
                }

                $RowTPL = gettemplate('alliance_admin_ranklist_row');
                $_Lang['RanksRows'] = '';
                $RanksCount = 0;
                foreach($Ally['ally_ranks'] as $RankID => $RankData)
                {
                    $ThisRank = array();

                    if($ImAllyOwner OR ($_ThisUserRank['like_admin'] === true AND $RankData['like_admin'] !== true) OR
                    (
                        $_ThisUserRank['like_admin'] !== true AND
                        $RankData['like_admin'] !== true AND
                        $RankData['ranks_mod'] !== true AND
                        $RankData['mlist_mod'] !== true AND
                        $RankData['cankick'] !== true
                    ))
                    {
                        $ThisCanChangeName = true;
                        $ThisCanChangeRights = true;
                        $ThisCanDelete = true;
                    }
                    else
                    {
                        $ThisCanChangeName = false;
                        $ThisCanChangeRights = false;
                        $ThisCanDelete = false;
                        $ThisCantChangeName = 'na_nonam';
                        $ThisCantChangeRights = 'na_nochg';
                        $ThisCantDelete = 'na_nodel';
                    }
                    if($RankID == 0)
                    {
                        $ThisCanChangeRights = false;
                        $ThisCanDelete = false;
                        $ThisCantChangeRights = 'adm_nochg';
                        $ThisCantDelete = 'adm_nodel';
                    }
                    if($RankID == $Ally['ally_new_rank_id'])
                    {
                        $ThisCanDelete = false;
                        $ThisCantDelete = 'nc_nodel';
                    }

                    if($ThisCanChangeName)
                    {
                        $ThisRank['RankName'] = '<input type="text" name="chgData['.$RankID.'][0]" value="'.$RankData['name'].'" />';
                    }
                    else
                    {
                        $ThisRank['RankName'] = '<b class="'.$ThisCantChangeName.'">'.$RankData['name'].'</b>';
                    }
                    if($ThisCanChangeRights !== true)
                    {
                        $ThisRank['DisableInfo'] = ' class="'.$ThisCantChangeRights.'"';
                        for($i = 1; $i < $Ally['ally_ranks_count']; $i += 1)
                        {
                            $ThisRank['CBoxes'][$i][] = 'disabled';
                            $ThisRank['DisableInfo'.$i] = $ThisRank['DisableInfo'];
                        }
                    }
                    else
                    {
                        $ThisRank['DisableInfo'] = ' class="na_nochg"';
                        for($i = 1; $i < $Ally['ally_ranks_count']; $i += 1)
                        {
                            if(in_array($i, $DefaultDisabledOpt))
                            {
                                $ThisRank['CBoxes'][$i][] = 'disabled';
                                $ThisRank['DisableInfo'.$i] = $ThisRank['DisableInfo'];
                            }
                            else
                            {
                                $ThisRank['CBoxes'][$i][] = 'name="chgData['.$RankID.']['.$i.']"';
                            }
                        }
                    }

                    $PropCounter = 1;
                    foreach($RankData as $PropKey => $PropVal)
                    {
                        if($PropKey == 'name')
                        {
                            continue;
                        }
                        if($PropVal === true)
                        {
                            $ThisRank['CBoxes'][$PropCounter][] = 'checked';
                        }
                        $PropCounter += 1;
                    }
                    if(!empty($ThisRank['CBoxes']))
                    {
                        foreach($ThisRank['CBoxes'] as $Index => $Vals)
                        {
                            $ThisRank['CBox_'.$Index] = implode(' ', $Vals);
                        }
                    }

                    if($ThisCanDelete !== true)
                    {
                        $ThisRank['DeleteButton'] = '<s class="red '.$ThisCantDelete.'">'.$_Lang['ADM_RkL_Delete'].'</s>';
                    }
                    else
                    {
                        $ThisRank['DeleteButton'] = '<input type="button" class="delButton" id="del_'.$RankID.'" value="'.$_Lang['ADM_RkL_Delete'].'" />';
                    }
                    if(isset($RanksCountArray[$RankID]) && $RanksCountArray[$RankID] > 0)
                    {
                        $ThisRank['MembersCount'] = $RanksCountArray[$RankID];
                    }
                    else
                    {
                        $ThisRank['MembersCount'] = '0';
                    }
                    $_Lang['RanksRows'] .= parsetemplate($RowTPL, $ThisRank);
                    $RanksCount += 1;
                }
                $_Lang['RankCount'] = $RanksCount;

                $AddRank['DisableInfo'] = ' class="na_adchg"';
                for($i = 1; $i < $Ally['ally_ranks_count']; $i += 1)
                {
                    if(in_array($i, $DefaultDisabledOpt))
                    {
                        $AddRank['CBoxes'][$i][] = 'disabled';
                        $_Lang['DisableInfo'.$i] = $AddRank['DisableInfo'];
                    }
                    else
                    {
                        $AddRank['CBoxes'][$i][] = 'name="opt['.$i.']"';
                    }
                }
                if(!empty($AddRank['CBoxes']))
                {
                    foreach($AddRank['CBoxes'] as $Index => $Vals)
                    {
                        $_Lang['CBox_'.$Index] = implode(' ', $Vals);
                    }
                }

                $Page = parsetemplate(gettemplate('alliance_admin_ranklist_table'), $_Lang);
                display($Page, $_Lang['ADM_Title']);
            }
            else
            {
                message($_Lang['ADM_BadEditSelected'], $_Lang['ADM_Title'], 'alliance.php', 3);
            }
        }
    }
    else
    {
        // User is on Ally Front Page
        include($_EnginePath.'includes/functions/BBcodeFunction.php');
        $_Lang = array_merge($_Lang, $Ally);

        $_Lang['ally_user_rank'] = $_ThisUserRank['name'];
        if(empty($_Lang['ally_web']))
        {
            $_Lang['ally_web'] = '&nbsp;';
        }
        else
        {
            $_Lang['ally_web'] = "<a href=\"{$_Lang['ally_web']}\" rel=\"nofollow\">{$_Lang['ally_web']}</a>";
        }

        if($_ThisUserRank['mlist'] !== true)
        {
            $_Lang['HideShowMList'] = 'hide';
        }
        if($_ThisUserRank['caninvite'] !== true)
        {
            $_Lang['HideInviteNewUser'] = 'hide';
        }
        if($_ThisUserRank['sendmsg'] !== true)
        {
            $_Lang['HideSendMail'] = 'hide';
        }
        if($Ally['ally_ChatRoom_ID'] <= 0 OR $_ThisUserRank['canusechat'] !== true)
        {
            $_Lang['HideAllyChat'] = 'hide';
        }
        else
        {
            $_Lang['Insert_ChatRoomID'] = $Ally['ally_ChatRoom_ID'];
        }
        if($_ThisUserRank['admingen'] !== true)
        {
            $_Lang['HideAllyAdmin'] = 'hide';
        }
        if($_ThisUserRank['lookreq'] !== true)
        {
            $_Lang['HideLookReq'] = 'hide';
        }
        else
        {
            $RequestsCount = doquery("SELECT COUNT(*) AS `count` FROM {{table}} WHERE `ally_request` = {$Ally['id']};", 'users', true);
            $RequestsCount = (string) ($RequestsCount['count'] + 0);
            if($RequestsCount == 0)
            {
                $_Lang['HideLookReq'] = 'hide';
            }
            $_Lang['RequestCount'] = $RequestsCount;
            if($_ThisUserRank['managereq'] === true AND $RequestsCount > 0)
            {
                $_Lang['RequestColor'] = ' orange';
            }
        }

        if($Ally['ally_owner'] == $_User['id'])
        {
            $_Lang['HideLeaveAlly'] = 'class="hide"';
        }

        // Ally Description
        if(!empty($Ally['ally_description']))
        {
            $_Lang['ally_description'] = nl2br(bbcode($Ally['ally_description']));
        }
        else
        {
            $_Lang['ally_description'] = $_Lang['AFP_NoAllyDesc'];
        }
        // Ally InnerText
        if(!empty($Ally['ally_text']))
        {
            $_Lang['ally_text'] = nl2br(bbcode($Ally['ally_text']));
        }
        else
        {
            $_Lang['ally_text'] = $_Lang['AFP_NoAllyInner'];
        }

        $Page = parsetemplate(gettemplate('alliance_frontpage'), $_Lang);
        display($Page, $_Lang['AFP_YouAlly']);
    }
}

?>
