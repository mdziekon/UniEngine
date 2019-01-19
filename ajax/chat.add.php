<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_DontForceRulesAcceptance = true;
$_UseMinimalCommon = true;
$_AllowInVacationMode = true;

$_SetAccessLogPreFilename = 'ajax/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath.'common.php');

if(!isLogged())
{
    safeDie('4'); // Not logged in
}
if(!isset($_POST['msg']) || empty($_POST['msg']))
{
    safeDie('3'); // Message Empty
}

$RoomID = (isset($_POST['rid']) ? intval($_POST['rid']) : 0);
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
            safeDie('6'); // Room doesn't exist
        }
        else
        {
            safeDie('5'); // No RoomAccess
        }
    }
}

$Message = getDBLink()->escape_string(trim($_POST['msg']));

include($_EnginePath.'includes/functions/FilterMessages.php');

$Message = FilterMessages($Message, 2, '***');

if(empty($Message))
{
    safeDie('3'); // Message Empty
}

if(get_magic_quotes_gpc())
{
    $Message = stripslashes($Message);
}

doquery(
    "INSERT INTO {{table}} SET `RID` = {$RoomID}, `UID` = {$_User['id']}, `TimeStamp_Add` = UNIX_TIMESTAMP(), `Text` = '{$Message}';",
    'chat_messages'
);

if(getDBLink()->affected_rows > 0)
{
    safeDie('1'); // Inserted
}
else
{
    safeDie('2'); // Not inserted
}

?>
