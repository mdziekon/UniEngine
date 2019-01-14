<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_DontForceRulesAcceptance = true;
$_UseMinimalCommon = true;
$_AllowInVacationMode = true;
$_CommonSettings['gamedisable_callback'] = function(){ safeDie(json_encode(array('Err' => '003'))); };

$_SetAccessLogPreFilename = 'ajax/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath.'common.php');

$PerPage = 100;
$OnlineCheck = 30;

loggedCheck(true);
includeLang('chat');
$Now = time();
$OnlineCheck = $Now - $OnlineCheck;

$RoomID = (isset($_GET['rid']) ? intval($_GET['rid']) : 0);
$FirstID = (isset($_GET['fID']) ? round($_GET['fID']) : 0);
$LastID = (isset($_GET['lID']) ? round($_GET['lID']) : 0);
$LastGet = (isset($_GET['lGet']) ? intval($_GET['lGet']) + SERVER_MAINOPEN_TSTAMP : SERVER_MAINOPEN_TSTAMP);
$LastCount = (isset($_GET['lCnt']) ? intval($_GET['lCnt']) : 0);

$isInit = ($FirstID == 0 && $LastID == 0 ? true : false);
if($RoomID < 0)
{
    $RoomID = 0;
}
else
{
    include($_EnginePath.'includes/functions/ChatUtilities.php');
    $CheckAccess = Chat_CheckAccess($RoomID, $_User);
    if($CheckAccess !== true)
    {
        if($CheckAccess === null)
        {
            $ErrorCode = '001';
        }
        else
        {
            $ErrorCode = '002';
        }
        safeDie(json_encode(array('Err' => $ErrorCode)));
    }
}
if($FirstID < 0)
{
    $FirstID = 0;
}
if($LastID < 0)
{
    $LastID = 0;
}
if($LastGet < 0)
{
    $LastGet = 0;
}
if($LastCount < 0)
{
    $LastCount = 0;
}

$Query_GetMessages = '';
$Query_GetMessages .= "SELECT `chat`.*, `user`.`username`, `user`.`authlevel` ";
$Query_GetMessages .= "FROM {{table}} AS `chat` ";
$Query_GetMessages .= "LEFT JOIN `{{prefix}}users` AS `user` ON `user`.`id` = `chat`.`UID` ";
$Query_GetMessages .= "WHERE `RID` = {$RoomID} ";
$Query_GetMessages .= "ORDER BY `ID` DESC LIMIT {$PerPage};";

$SQLResult_GetMessages = doquery($Query_GetMessages, 'chat_messages');
$MessagesCount = $SQLResult_GetMessages->num_rows;

if($MessagesCount > 0)
{
    include($_EnginePath.'includes/functions/BBcodeFunction.php');

    $ThrowNew = array();
    $Edited = array();
    $Deleted = array();
    $DeletedBetween = array();

    $msg_i = 0;
    $FirstGet = false;

    while($Message = $SQLResult_GetMessages->fetch_assoc())
    {
        $isNew = false;
        $msg_i += 1;

        if($FirstGet === false)
        {
            if($isInit !== true && $Message['ID'] < $LastID)
            {
                $Range = range($LastID, $Message['ID'] + 1, 1);
                $Deleted = array_merge($Deleted, $Range);
            }
            $FirstGet = true;
            $msg_lastID = $LastGetID = $Message['ID'];
        }
        else
        {
            if($isInit !== true && $msg_lastID - $Message['ID'] > 1)
            {
                $Range = range($msg_lastID - 1, $Message['ID'] + 1, 1);
                $DeletedBetween = array_merge($DeletedBetween, $Range);
            }
            if($msg_i == $MessagesCount)
            {
                if($isInit !== true && $Message['ID'] > $FirstID)
                {
                    $Range = range($FirstID, $Message['ID'] - 1, 1);
                    $Deleted = array_merge($Deleted, $Range);
                }
                $FirstGetID = $Message['ID'];
            }
        }

        if($isInit === true || $Message['ID'] < $FirstID)
        {
            $isNew = true;
            $ThrowNew[] = $Message['ID'];
        }
        else if($isInit === true || $Message['ID'] > $LastID)
        {
            $isNew = true;
            $ThrowNew[] = $Message['ID'];
        }
        else
        {
            if($isInit !== true && $Message['TimeStamp_Edit'] > $LastGet && !in_array($Message['ID'], $ThrowNew))
            {
                $Edited[] = $Message['ID'];
            }
        }

        if(!isset($UsersArray[$Message['UID']]))
        {
            $UsersArray[$Message['UID']] = array('username' => $Message['username'], 'authlevel' => GetAuthLabel($Message), 'isNew' => $isNew);
        }
        else
        {
            if($isNew === false)
            {
                $UsersArray[$Message['UID']]['isNew'] = false;
            }
        }

        $Message['Text'] = bbcodeChat($Message['Text']);

        $Messages[$Message['ID']] = array('id' => floatval($Message['ID']), 'u' => intval($Message['UID']), 'd' => $Message['TimeStamp_Add'] - SERVER_MAINOPEN_TSTAMP, 't' => $Message['Text']);

        $msg_lastID = $Message['ID'];
    }

    if(!empty($ThrowNew))
    {
        foreach($ThrowNew as $MID)
        {
            $Return['newm'][] = $Messages[$MID];
        }
    }
    if(!empty($Edited))
    {
        foreach($Edited as $MID)
        {
            $Return['edit'][$MID] = array('t' => $Messages[$MID]['t']);
        }
    }
    $ToDelete = array();
    if(!empty($Deleted))
    {
        $ToDelete = $Deleted;
    }
    if(!empty($DeletedBetween))
    {
        if(($FirstID != $FirstGetID OR $LastID != $LastGetID) OR $LastCount != $MessagesCount)
        {
            $ToDelete = array_merge($ToDelete, $DeletedBetween);
        }
    }
    if(!empty($ToDelete))
    {
        $Return['del'] = $ToDelete;
    }

    foreach($UsersArray as $UserID => $UserData)
    {
        if($UserData['isNew'] === true)
        {
            $Return['usr'][$UserID] = array('n' => $UserData['username'], 'c' => GetAuthLabel($UserData));
        }
    }
}
else
{
    if($isInit === false)
    {
        $Return['cmd'] = 'delall';
    }
}

$Query_GetOnline = '';
$Query_GetOnline .= "SELECT `chat`.`UID`, `user`.`username`, `user`.`authlevel`, `user`.`chat_GhostMode`, `user`.`chat_GhostMode_DontCount` ";
$Query_GetOnline .= "FROM {{table}} AS `chat` ";
$Query_GetOnline .= "LEFT JOIN `{{prefix}}users` AS `user` ON `user`.`id` = `chat`.`UID` ";
$Query_GetOnline .= "WHERE `chat`.`UID` != {$_User['id']} AND `chat`.`RID` = {$RoomID} AND `lastOnline` >= {$OnlineCheck} ORDER BY `authlevel` DESC, `id` ASC;";

$SQLResult_GetOnline = doquery($Query_GetOnline, 'chat_online');
$OnlineNo = $SQLResult_GetOnline->num_rows;

if($OnlineNo > 0)
{
    while($UserOn = $SQLResult_GetOnline->fetch_assoc())
    {
        if(empty($UserOn['username']))
        {
            continue;
        }
        if($UserOn['chat_GhostMode_DontCount'] == 0 OR $UserOn['chat_GhostMode'] == 0 OR CheckAuth('user', AUTHCHECK_HIGHER))
        {
            if(!isset($Return['onlCnt']))
            {
                $Return['onlCnt'] = 0;
            }
            $Return['onlCnt'] += 1;
        }
        if($UserOn['chat_GhostMode'] == 1 AND !CheckAuth('user', AUTHCHECK_HIGHER))
        {
            continue;
        }
        $UserOn['return'][0] = $UserOn['UID'];
        $UserOn['return'][1] = $UserOn['username'];
        $UserOn['return'][2] = GetAuthLabel($UserOn);
        if($UserOn['chat_GhostMode'] == 1)
        {
            $UserOn['return'][3] = 1;
        }
        $Temp = array_keys($UserOn['return']);
        $UserOn['returnMax'] = array_pop($Temp);
        for($i = 0; $i <= $UserOn['returnMax']; $i += 1)
        {
            if(empty($UserOn['return'][$i]))
            {
                $UserOn['return'][$i] = '0';
            }
        }
        $Return['onl'][] = implode('|', $UserOn['return']);
    }
}

$Query_UpdateOnline = '';
$Query_UpdateOnline .= "INSERT INTO {{table}} VALUES ({$RoomID}, {$_User['id']}, {$Now}) ";
$Query_UpdateOnline .= "ON DUPLICATE KEY UPDATE ";
$Query_UpdateOnline .= "`LastOnline` = VALUES(`LastOnline`);";
doquery($Query_UpdateOnline, 'chat_online');

$SelectMsgs = doquery("SELECT COUNT(`id`) as `Count` FROM {{table}} WHERE `id_owner` = {$_User['id']} AND `deleted` = false AND `read` = false;", 'messages', true);
$Msg_Count = floatval($SelectMsgs['Count']);
if($Msg_Count > 0)
{
    $Return['lmMC'] = $Msg_Count;
}

if(!empty($Return))
{
    echo json_encode($Return);
}
safeDie();

?>
