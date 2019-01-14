<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('chat');
$BodyTPL = gettemplate('chat_edit');

if(!CheckAuth('supportadmin'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

$EditID = (isset($_GET['mode']) ? round($_GET['mode']) : 0);
if($EditID > 0 && isset($_GET['save']))
{
    $NewMessage = getDBLink()->escape_string($_POST['message']);

    doquery(
        "UPDATE {{table}} SET `Text` = '{$NewMessage}', `TimeStamp_Edit` = UNIX_TIMESTAMP() WHERE `ID` = {$EditID} LIMIT 1;",
        'chat_messages'
    );

    header('Location: chat.php');
}
else if($EditID > 0)
{
    $GetMessage = doquery("SELECT `Text` FROM {{table}} WHERE `ID` = {$EditID} LIMIT 1;", "chat_messages", true);
    $_Lang['EditID'] = $EditID;
    $_Lang['OldText'] = stripslashes($GetMessage['Text']);
    $_Lang['SubmitButton'] = $_Lang['chat_save'];
}
else
{
    header('Location: chat.php');
}

$page = parsetemplate($BodyTPL, $_Lang);
display($page, $_Lang['Chat'], false);

?>
