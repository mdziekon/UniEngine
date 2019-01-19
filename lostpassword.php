<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

includeLang('lostpassword');

function SendPassEmail($Mail, $Pass, $Code)
{
    global $_Lang, $_EnginePath;

    include($_EnginePath.'includes/functions/SendMail.php');

    $parse['gameurl'] = GAMEURL;
    $parse['activation_link'] = '<a href="'.GAMEURL.'lostpassword.php?code='.$Code.'">'.GAMEURL.'lostpassword.php?code='.$Code.'</a>';
    $parse['newpass'] = $Pass;
    $EMail = parsetemplate($_Lang['mail_template'], $parse);
    $Return = SendMail($Mail, $_Lang['mail_title'], $EMail);

    return $Return;
}

if(isLogged())
{
    message($_Lang['Cannot_usethis_till_logged'], $_Lang['Title_System'], 'overview.php', 3);
}

if(isset($_POST['email']))
{
    $email = trim($_POST['email']);
    if(!empty($email))
    {
        if(is_email($email))
        {
            $email = getDBLink()->escape_string($email);

            $Result = doquery(
                "SELECT `id`, `username`, `last_send_newpass` FROM {{table}} WHERE `email` = '{$email}';",
                'users',
                true
            );

            if($Result['id'] > 0)
            {
                if(($Result['last_send_newpass'] + TIME_HOUR) < time())
                {
                    $Signs = 'abcdefghijklmnoprstuwxyz0123456789';
                    $Length = 7;
                    $Count = strlen($Signs)-1;

                    $NewPass = '';
                    for($i = 0; $i < $Length; ++$i)
                    {
                        $NewPass .= $Signs[mt_rand(0, $Count)];
                    }

                    $PassActivateLink = md5(mt_rand(0, 99999999999));
                    $Id = $Result['id'];

                    $Result = SendPassEmail($email, $NewPass, $PassActivateLink);
                    if($Result === true)
                    {
                        doquery("UPDATE {{table}} SET `new_pass` = MD5('{$NewPass}'), `new_pass_code` = '{$PassActivateLink}', `last_send_newpass` = UNIX_TIMESTAMP() WHERE `id` = {$Id}", 'users');
                        $Msg = $_Lang['new_pass_sent'];
                    }
                    else
                    {
                        $Msg = sprintf($_Lang['smtp_error'], $Result);
                    }
                }
                else
                {
                    $Msg = $_Lang['prevent_spaming'];
                }
            }
            else
            {
                $Msg = $_Lang['no_email_in_db'];
            }
        }
        else
        {
            $Msg = $_Lang['bad_email_given'];
        }
    }
    else
    {
        $Msg = $_Lang['no_mail_given'];
    }
    message($Msg, $_Lang['ResetPass']);
}
else if(isset($_GET['code']))
{
    $_GET['code'] = trim($_GET['code']);
    if(!empty($_GET['code']))
    {
        if(preg_match('/^[0-9a-zA-Z]{32}$/D', $_GET['code']))
        {
            $SQLResult_GetUserData = doquery(
                "SELECT `username`, `new_pass` FROM {{table}} WHERE `new_pass_code` = '{$_GET['code']}';",
                'users'
            );

            if($SQLResult_GetUserData->num_rows > 0)
            {
                $Result = $SQLResult_GetUserData->fetch_assoc();

                $Username = $Result['username'];
                $NewPassword = $Result['new_pass'];

                doquery(
                    "UPDATE {{table}} SET `new_pass_code` = '', `new_pass` = '', `password` = '{$NewPassword}' WHERE `username` = '{$Username}';",
                    'users'
                );

                $Msg = sprintf($_Lang['resetpass_completed'], $Username);
            }
            else
            {
                $Msg = $_Lang['no_code_in_db'];
            }
        }
        else
        {
            $Msg = $_Lang['invalid_code_format'];
        }
        message($Msg, $_Lang['ResetPass']);
    }
}
else
{
    $parse = $_Lang;
    $parse['GameURL'] = GAMEURL_STRICT;
    $parse['servername'] = $_GameConfig['game_name'];
    display(parsetemplate(gettemplate('lostpassword'), $parse), $_Lang['ResetPass'], false);
}

?>
