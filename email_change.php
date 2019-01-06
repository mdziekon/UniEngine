<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_AllowInVacationMode = TRUE;

$_EnginePath = './';
include($_EnginePath.'common.php');

includeLang('email_change');

$Title = $_Lang['Page_Title'];

if(!isLogged())
{
    message($_Lang['Info_havetobelogged'], $Title, 'login.php', 4);
}

if(!isset($_GET['hash']) || empty($_GET['hash']))
{
    message($_Lang['Info_noenoughtdata'], $Title, 'overview.php', 4);
}

$HashType = $_GET['hash'];
$Key = (isset($_GET['key']) ? $_GET['key'] : null);

if($HashType != 'old' && $HashType != 'new' && $HashType != 'none')
{
    message($_Lang['Info_baddata'], $Title, 'overview.php', 4);
}
else
{
    if($HashType == 'old' || $HashType == 'new')
    {
        if(!preg_match('/^[0-9a-zA-Z]{32}$/D', $Key))
        {
            message($_Lang['Info_badkey'], $Title, 'overview.php', 4);
        }
    }
}

$GetData = doquery("SELECT `ID`, `Date`, `ConfirmType`, `ConfirmHash`, `ConfirmHashNew` FROM {{table}} WHERE `UserID` = {$_User['id']} ORDER BY `ID` DESC LIMIT 1;", 'mailchange', true);
$Now = time();

if($GetData['ID'] <= 0)
{
    message($_Lang['Info_noactivePrc'], $Title, 'overview.php', 4);
}

if($GetData['ConfirmType'] != 0)
{
    if($GetData['ConfirmType'] == 1 OR $GetData['ConfirmType'] == 2 OR $GetData['ConfirmType'] == 3)
    {
        message($_Lang['Info_noactivePrc'], $Title, 'overview.php', 4);
    }
    else if($GetData['ConfirmType'] == 4)
    {
        message($_Lang['Info_some1hasthisemail'], $Title, 'overview.php', 4);
    }
}

$FullConfirm = false;
$PartialConfirm = false;

if($HashType == 'none')
{
    if(($GetData['Date'] + 604800) < $Now)
    {
        if($GetData['ConfirmHashNew'] == '')
        {
            $FullConfirm = true;
            $FullConfirmByLink = false;
            $RemoveConfirm = 'ConfirmHash';
        }
        else
        {
            message($_Lang['Info_newmail_notconfirmed'], $Title, 'overview.php', 4);
        }
    }
    else
    {
        message($_Lang['Info_7daysnotpassed'], $Title, 'overview.php', 4);
    }
}
else if($HashType == 'old')
{
    if($GetData['ConfirmHash'] != '')
    {
        if($GetData['ConfirmHash'] == $Key)
        {
            if($GetData['ConfirmHashNew'] == '')
            {
                $FullConfirm = true;
                $FullConfirmByLink = true;
            }
            else
            {
                $PartialConfirm = true;
            }
            $RemoveConfirm = 'ConfirmHash';
        }
        else
        {
            message($_Lang['Info_Hashnotcorrect'], $Title, 'overview.php', 4);
        }
    }
    else
    {
        message($_Lang['Info_HashalreadyConfirmed'], $Title, 'overview.php', 4);
    }
}
else if($HashType == 'new')
{
    if($GetData['ConfirmHashNew'] != '')
    {
        if($GetData['ConfirmHashNew'] == $Key)
        {
            if($GetData['ConfirmHash'] == '')
            {
                $FullConfirm = true;
                $FullConfirmByLink = true;
            }
            else
            {
                $PartialConfirm = true;
            }
            $RemoveConfirm = 'ConfirmHashNew';
        }
        else
        {
            message($_Lang['Info_Hashnotcorrect'], $Title, 'overview.php', 4);
        }
    }
    else
    {
        message($_Lang['Info_HashalreadyConfirmed'], $Title, 'overview.php', 4);
    }
}

if($FullConfirm === true)
{
    doquery("UPDATE {{table}} SET `email` = '{$_User['email_2']}' WHERE `id` = {$_User['id']};", 'users');
    if($FullConfirmByLink)
    {
        $ConfirmType = '1';
    }
    else
    {
        $ConfirmType = '2';
    }
    doquery("UPDATE {{table}} SET `ConfirmType` = {$ConfirmType}, `{$RemoveConfirm}` = '' WHERE `ID` = {$GetData['ID']};", 'mailchange');
    doquery("UPDATE {{table}} SET `ConfirmType` = 4 WHERE `NewMail` = '{$_User['email_2']}' AND `ConfirmType` = 0;", 'mailchange');
    doquery("UPDATE {{table}} SET `email_2` = `email` WHERE `email_2` = '{$_User['email_2']}' AND `id` != {$_User['id']};", 'users');

    message($_Lang['Info_MailChangedCompletely'], $Title, 'overview.php', 5);
}
else
{
    doquery("UPDATE {{table}} SET `{$RemoveConfirm}` = '' WHERE `ID` = {$GetData['ID']};", 'mailchange');

    if($RemoveConfirm == 'ConfirmHash')
    {
        $Msg = $_Lang['Info_MailChangePartComplete_OldHash'];
    }
    else
    {
        $Msg = $_Lang['Info_MailChangePartComplete_NewHash'];
    }
    message($Msg, $Title, 'overview.php', 5);
}

?>
