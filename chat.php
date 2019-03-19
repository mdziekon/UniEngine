<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_AllowInVacationMode = true;

$_EnginePath = './';

include($_EnginePath.'common.php');

loggedCheck();

includeLang('chat');
includeLang('months');
$BodyTPL = gettemplate('chat_body');

$RoomID = (isset($_GET['rid']) ? intval($_GET['rid']) : 0);
if($RoomID <= 0)
{
    $RoomID = 0;
    $_ChatTitle = sprintf($_Lang['ChatTitle_String'], $_Lang['ChatTitle_Main']);
}
else
{
    $_ChatTitle = $_Lang['Chat'];
    $Query_GetRoom = doquery("SELECT * FROM {{table}} WHERE `ID` = {$RoomID} LIMIT 1;", 'chat_rooms', true);
    if($Query_GetRoom['ID'] == $RoomID)
    {
        if($Query_GetRoom['AccessType'] == 1 AND ($Query_GetRoom['AccessCheck'] == $_User['ally_id'] OR CheckAuth('supportadmin')))
        {
            $_ChatTitle = sprintf($_Lang['ChatTitle_String'], sprintf($_Lang['ChatTitle_Ally'], $_User['ally_name']));
        }
        else if($Query_GetRoom['AccessType'] == 2 AND $_User['authlevel'] >= $Query_GetRoom['AccessCheck'])
        {
            $_ChatTitle = sprintf($_Lang['ChatTitle_String'], $_Lang['ChatTitle_GameTeam']);
        }
    }
}

$Query_GetFirstID = '';
$Query_GetFirstID .= "SELECT `msg`.`ID` FROM {{table}} AS `msg` ";
$Query_GetFirstID .= "LEFT JOIN `{{prefix}}chat_online` AS `visit` ON `visit`.`RID` = {$RoomID} AND `visit`.`UID` = {$_User['id']} ";
$Query_GetFirstID .= "WHERE `msg`.`TimeStamp_Add` <= `visit`.`LastOnline` AND `msg`.`RID` = {$RoomID} ";
$Query_GetFirstID .= "ORDER BY `msg`.`ID` DESC LIMIT 1;";
$GetFirstID = doquery($Query_GetFirstID, 'chat_messages', true);

$_Lang['Online_You_ID'] = $_User['id'];
$_Lang['Online_You_Color'] = (string)GetAuthLabel($_User);
$_Lang['Online_You_Name'] = $_User['username'];

$_Lang['LastSeenID'] = (string)($GetFirstID['ID'] + 0);
$_Lang['ServerStamp'] = SERVER_MAINOPEN_TSTAMP;
$_Lang['UserAuth'] = (string)($_User['authlevel'] + 0);
$_Lang['RoomID'] = (string)($RoomID + 0);
$_Lang['Insert_JSLang_Errors'] = json_encode($_Lang['jsLang_Errors']);

if($_User['chat_GhostMode'] == 1)
{
    $_Lang['Online_You_Invisible'] = 'usrInv';
    $_Lang['Insert_CheckedGhostMode'] = 'checked';
}
if($_User['chat_GhostMode_DontCount'] == 1)
{
    $_Lang['Insert_CheckedGhostMode_DontCount'] = 'checked';
}
if(!CheckAuth('supportadmin'))
{
    $_Lang['Insert_Hide_GhostMode_DontCount'] = 'hide';
}
$_Lang['Insert_Settings'] = parsetemplate(gettemplate('chat_body_settings'), $_Lang);

$_DisplaySettings['dontShow_MainChat_MsgCount'] = true;
$_DisplaySettings['dontShow_AllyChat_MsgCount'] = true;

display(parsetemplate($BodyTPL, $_Lang), $_ChatTitle, false);

?>
