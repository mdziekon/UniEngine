<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(!CheckAuth('go'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

$_PerPage = 25;

includeLang('admin/chatbrowser');
$TPL_Body = gettemplate('admin/chatbrowser_body');
$TPL_MsgRow = gettemplate('admin/chatbrowser_msgrow');

$_RoomID = isset($_GET['rid']) ? intval($_GET['rid']) : 0;
if($_RoomID < 0)
{
    $_RoomID = 0;
}

$_Limit_PerPage = $_PerPage;

$_Pos_Where = '';
$_Pos_Order = 'DESC';
$_Pos_ArraySort = false;
$_Pos_DefaultPos = true;
$_Highlight = 0;

// Form Handler
if(isset($_POST['sent']) && $_POST['sent'] == 1)
{
    $_ThisCMDs = array('DelSelected');

    if(!empty($_POST['cmd']) || !in_array($_POST['cmd'], $_ThisCMDs))
    {
        if($_POST['cmd'] == 'DelSelected')
        {
            foreach($_POST['msg'] as $ThisID => $ThisValue)
            {
                if($ThisValue == 'on')
                {
                    $ThisID = round($ThisID);
                    if($ThisID > 0)
                    {
                        $DeleteMsgs[] = $ThisID;
                    }
                }
            }

            if(!empty($DeleteMsgs))
            {
                $Query_DeleteMsgs = "DELETE FROM {{table}} WHERE `ID` IN (".implode(',', $DeleteMsgs).") LIMIT ".count($DeleteMsgs).";";
                doquery($Query_DeleteMsgs, 'chat_messages');

                $DeletedCount = getDBLink()->affected_rows;
                if($DeletedCount > 0)
                {
                    $_MsgBox = array('Color' => 'lime', 'Text' => sprintf($_Lang['CMDInfo_Delete_DeletedOK'], prettyNumber($DeletedCount)));
                }
                else
                {
                    $_MsgBox = array('Color' => 'red', 'Text' => $_Lang['CMDInfo_Delete_NothingDeleted']);
                }
            }
            else
            {
                $_MsgBox = array('Color' => 'red', 'Text' => $_Lang['CMDInfo_Delete_NothingToDelete']);
            }
        }
    }
    else
    {
        $_MsgBox = array('Color' => 'red', 'Text' => $_Lang['CMDInfo_BadCMD']);
    }
}

if(!empty($_GET['fID']))
{
    $FirstID = round($_GET['fID']);
    if($FirstID > 0)
    {
        $_Pos_DefaultPos = false;
        $_Pos_RemoveLeftSide = true;
        $_Pos_Where = " AND `chat`.`ID` > {$FirstID} ";
        $_Pos_LeftIDWhere = " > {$FirstID}";
        $_Pos_Order = 'ASC';
        $_Pos_ArraySort = true;
    }
}
else if(!empty($_GET['lID']))
{
    $LastID = round($_GET['lID']);
    if($LastID > 0)
    {
        $_Pos_DefaultPos = false;
        $_Pos_AddLeftSide = true;
        if(isset($_GET['this']) && $_GET['this'] == '1')
        {
            $_Highlight = $LastID;
            $_Pos_Where = " AND `chat`.`ID` <= {$LastID} ";
            $_Pos_LeftIDWhere = " > {$LastID}";
        }
        else
        {
            $_Pos_Where = " AND `chat`.`ID` < {$LastID} ";
            $_Pos_LeftIDWhere = " >= {$LastID}";
        }
        $_Pos_Order = 'DESC';
    }
}
else if(!empty($_GET['page']))
{
    $ThisPage = round($_GET['page']);
    if($ThisPage > 0)
    {
        $_Pos_AddLeftSide = true;
        $_Pos_Order = 'DESC';
        $PosOffset = ($ThisPage - 1) * $_Limit_PerPage;
    }
}

if(!empty($_GET['highlight']))
{
    $Highlight = round($_GET['highlight']);
    if($Highlight > 0)
    {
        $_Highlight = $Highlight;
    }
}

$PosOffset = 0;
if($_Pos_DefaultPos === false)
{
    $PosOffset = isset($_GET['off']) ? round($_GET['off']) : 0;
    if($PosOffset < 0)
    {
        $PosOffset += 1;
    }
    else if($PosOffset > 0)
    {
        $PosOffset -= 1;
    }
    if($PosOffset < 0)
    {
        $PosOffset *= -1;
    }
    $PosOffset *= $_Limit_PerPage;
}
if($PosOffset > 0)
{
    $_Limit_Offset = $PosOffset;
}
else
{
    $_Limit_Offset = 0;
}

$Query_GetTotalCount = "SELECT COUNT(*) AS `Count` FROM {{table}} WHERE `RID` = {$_RoomID};";
$Result_GetTotalCount = doquery($Query_GetTotalCount, 'chat_messages', true);
$_Pagination_TotalCount = $Result_GetTotalCount['Count'];

if($_Pagination_TotalCount > 0)
{
    if(!empty($_Pos_LeftIDWhere))
    {
        $Query_GetLeftSideCount = '';
        $Query_GetLeftSideCount .= "SELECT COUNT(*) AS `Count` FROM {{table}} WHERE ";
        $Query_GetLeftSideCount .= "`RID` = {$_RoomID} AND `ID` {$_Pos_LeftIDWhere};";
        $Result_GetLeftSideCount = doquery($Query_GetLeftSideCount, 'chat_messages', true);
        $_Pagination_LeftSideCount = $Result_GetLeftSideCount['Count'];
    }
    else
    {
        $_Pagination_LeftSideCount = 0;
    }
    if(isset($_Pos_RemoveLeftSide))
    {
        $_Pagination_LeftSideCount -= ($_Limit_Offset + $_Limit_PerPage);
    }
    if(isset($_Pos_AddLeftSide))
    {
        $_Pagination_LeftSideCount += ($_Limit_Offset);
    }
    if($_Pagination_LeftSideCount <= 0)
    {
        $_Pos_DefaultPos = true;
        $_Pos_Where = '';
        $_Pos_Order = 'DESC';
        $_Limit_Offset = 0;
        $_Pagination_LeftSideCount = 0;
    }
    else if(($_Pagination_TotalCount - $_Pagination_LeftSideCount) <= 0)
    {
        $_Pos_DefaultPos = true;
        $_Pos_Where = '';
        $_Pos_Order = 'DESC';
        $_Limit_Offset = $_Pagination_TotalCount - ($_Pagination_TotalCount % $_Limit_PerPage);
        $_Pagination_LeftSideCount = $_Limit_Offset;
    }

    $Query_GetMessages = '';
    $Query_GetMessages .= "SELECT `chat`.*, `user`.`username`, `user`.`authlevel` FROM {{table}} AS `chat` ";
    $Query_GetMessages .= "LEFT JOIN `{{prefix}}users` AS `user` ON `chat`.`UID` = `user`.`id` ";
    $Query_GetMessages .= "WHERE ";
    $Query_GetMessages .= "`chat`.`RID` = {$_RoomID} ";
    $Query_GetMessages .= $_Pos_Where;
    $Query_GetMessages .= "ORDER BY `chat`.`ID` {$_Pos_Order} ";
    $Query_GetMessages .= "LIMIT {$_Limit_Offset}, {$_Limit_PerPage};";
    $Result_GetMessages = doquery($Query_GetMessages, 'chat_messages');
}

$Result_GetMessages_Count = 0;
if(isset($Result_GetMessages))
{
    $Result_GetMessages_Count = $Result_GetMessages->num_rows;
}
if(isset($Result_GetMessages) && $Result_GetMessages_Count > 0)
{
    include($_EnginePath.'includes/functions/BBcodeFunction.php');
    include_once($_EnginePath.'includes/functions/Pagination.php');

    while($FetchData = $Result_GetMessages->fetch_assoc())
    {
        $MessageRows[$FetchData['ID']] = $FetchData;
    }

    if($Result_GetMessages_Count <= 10)
    {
        $_Lang['Insert_HideOnFewMessages'] = 'class="hide"';
    }

    if($_Pos_ArraySort === true)
    {
        krsort($MessageRows);
    }

    $TempIDGetter = array_keys($MessageRows);
    $LastSeenID = array_pop($TempIDGetter);
    $FirstSeenID = array_shift($TempIDGetter);

    $CurrentPage = floor($_Pagination_LeftSideCount / $_Limit_PerPage) + 1;
    $TheoreticalLeftSideCount = (($CurrentPage - 1) * $_Limit_PerPage);
    if($_Pagination_LeftSideCount > $TheoreticalLeftSideCount)
    {
        $_Pagination_TotalCount -= ($_Pagination_LeftSideCount - $TheoreticalLeftSideCount);
    }
    $Pagin = CreatePaginationArray($_Pagination_TotalCount, $_Limit_PerPage, $CurrentPage, 7);
    $PaginationTPL = '<a href="?rid='.$_RoomID.'&amp;{InsertID}&amp;off={$Value}" class="pagin {$Classes}">{$ShowValue}</a>';
    $PaginationViewOpt = array('CurrentPage_Classes' => 'fatB orange', 'Breaker_View' => '...', 'OffsetValues' => true);
    $CreatePagination = ParsePaginationArray($Pagin, $CurrentPage, $PaginationTPL, $PaginationViewOpt);
    foreach($CreatePagination as &$Value)
    {
        if(strstr($Value, '{InsertID}') !== false)
        {
            if(strstr($Value, 'off=0') !== false)
            {
                $Value = str_replace('{InsertID}', 'lID='.($FirstSeenID + 1), $Value);
            }
            else if(strstr($Value, 'off=-') !== false)
            {
                $Value = str_replace('{InsertID}', 'fID='.$FirstSeenID, $Value);
            }
            else
            {
                $Value = str_replace('{InsertID}', 'lID='.$LastSeenID, $Value);
            }
        }
    }
    if($_Pagination_LeftSideCount > $TheoreticalLeftSideCount)
    {
        array_unshift($CreatePagination, str_replace(array('{InsertID}&amp;off={$Value}', '{$Classes}', '{$ShowValue}'), array('page='.$CurrentPage.($_Highlight > 0 ? '&amp;highlight='.$_Highlight : ''), 'red mr_20', $_Lang['Pagination_Reload']), $PaginationTPL));
    }
    $_Lang['Insert_Pagination'] = implode(' ', $CreatePagination);

    foreach($MessageRows as $MessageData)
    {
        if($MessageData['ID'] == $_Highlight)
        {
            $MessageData['Hightligh'] = 'class="msgHighlight"';
        }
        $MessageData['UserAuthLevel'] = GetAuthLabel($MessageData);
        $MessageData['ParseDate'] = prettyDate('d m Y, H:i:s', $MessageData['TimeStamp_Add'], 1);

        $MessageData['ParseMessage'] = bbcodeChat($MessageData['Text']);

        $_Lang['Insert_ChatRows'][] = parsetemplate($TPL_MsgRow, $MessageData);
    }
    $_Lang['Insert_ChatRows'] = implode('', $_Lang['Insert_ChatRows']);
}
else
{
    $_Lang['Insert_ChatRows'] = parsetemplate(gettemplate('_singleRow'), array('Colspan' => 5, 'Classes' => 'pad5 orange', 'Text' => $_Lang['Notice_NoMessages']));
    $_Lang['Insert_Pagination'] = '&nbsp;';
    $_Lang['Insert_HideOnNoMessages'] = 'class="hide"';
}

$_Lang['Insert_RoomID'] = $_RoomID;
if(isset($CurrentPage) && $CurrentPage > 0)
{
    $_Lang['Insert_ThisPage'] = $CurrentPage;
}
else
{
    $_Lang['Insert_ThisPage'] = 0;
}

if(!empty($_MsgBox))
{
    $_Lang['Insert_MsgBox'] = parsetemplate(gettemplate('_singleRow'), array('Classes' => 'pad2 '.$_MsgBox['Color'], 'Colspan' => 5, 'Text' => $_MsgBox['Text']));
}

display(parsetemplate($TPL_Body, $_Lang), $_Lang['Page_Title'], false, true);

?>
