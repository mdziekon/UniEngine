<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

includeLang('lostcode');

function SendCodeEmail($Mail, $Code)
{
    global $_Lang, $_EnginePath;

    include($_EnginePath.'includes/functions/SendMail.php');

    $parse['gameurl'] = GAMEURL;
    $parse['activationlink'] = '<a href="'.GAMEURL.'activate.php?code='.$Code.'">'.GAMEURL.'activate.php?code='.$Code.'</a>';
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
                "SELECT `id`,`activation_code`, `last_send_activationcode` FROM {{table}} WHERE `email` = '{$email}';",
                'users',
                true
            );

            if($Result)
            {
                if(!empty($Result['activation_code']))
                {
                    if(($Result['last_send_activationcode'] + TIME_HOUR) < time())
                    {
                        $NewCode = md5(mt_rand(0, 99999999999));
                        $Id = $Result['id'];

                        $Result = SendCodeEmail($email, $NewCode);
                        if($Result === true)
                        {
                            doquery("UPDATE {{table}} SET `activation_code` = '{$NewCode}', `last_send_activationcode` = UNIX_TIMESTAMP() WHERE `id` = {$Id}", 'users');
                            $Msg = $_Lang['new_code_sent'];
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
                    $Msg = $_Lang['acc_already_activated'];
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
    message($Msg, $_Lang['Title']);
}
else
{
    $parse = $_Lang;
    $parse['GameURL'] = GAMEURL_STRICT;
    $parse['servername'] = $_GameConfig['game_name'];
    display(parsetemplate(gettemplate('lostcode'), $parse), $_Lang['Title'], false);
}

?>
