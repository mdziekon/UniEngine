<?php

function SendMail($to, $title, $body, $From = '', $MoreThanOne = false, $CloseConnectionOnly = false)
{
    static $Object;
    global $_EnginePath, $__MailerInc;

    if($CloseConnectionOnly === true)
    {
        if($__MailerInc === true && MAILER_SMTP_USE)
        {
            $Object->SmtpClose();
            return true;
        }
        else
        {
            return false;
        }
    }

    if($__MailerInc !== TRUE)
    {
        require "{$_EnginePath}vendor/phpmailer/phpmailer/src/PHPMailer.php";
        require "{$_EnginePath}vendor/phpmailer/phpmailer/src/SMTP.php";
        require "{$_EnginePath}vendor/phpmailer/phpmailer/src/Exception.php";
        $__MailerInc = TRUE;
    }

    try
    {
        $From = trim($From);
        if(!$From)
        {
            $From = MAILER_MSGFIELDS_FROM;
            $FromName = MAILER_MSGFIELDS_FROM_NAME;
        }
        else
        {
            $FromName = $From;
        }

        if(empty($Object))
        {
            $Object = new PHPMailer\PHPMailer\PHPMailer(true);

            if(MAILER_SMTP_USE)
            {
                $Object->IsSMTP();
                $Object->SMTPAuth = true;
                $Object->SMTPSecure = 'ssl';
                $Object->Host = MAILER_SMTP_HOST;
                $Object->Port = MAILER_SMTP_PORT;
                $Object->Username = MAILER_SMTP_USER;
                $Object->Password = MAILER_SMTP_PASSWORD;
            }

            $Object->AltBody = 'HTML Compat needed';
            $Object->WordWrap = 80;
            $Object->IsHTML(true);

            if($MoreThanOne === true && MAILER_SMTP_USE)
            {
                $Object->SMTPKeepAlive = true;
            }
        }

        $body = preg_replace('/\\\\/','', $body);

        $Object->AddReplyTo($From, $FromName);
        $Object->From = $From;
        $Object->FromName = $FromName;
        $Object->AddAddress($to);
        $Object->Subject = $title;
        $Object->MsgHTML($body);

        $Object->Send();

        $Object->clearAllRecipients();

        return true;
    }
    catch(phpmailerException $e)
    {
        return $e->errorMessage();
    }
}

function SendMassMail($to, $title, $body, $From = '')
{
    global $_EnginePath, $__MailerInc;

    if($__MailerInc !== TRUE)
    {
        require "{$_EnginePath}vendor/phpmailer/phpmailer/src/PHPMailer.php";
        require "{$_EnginePath}vendor/phpmailer/phpmailer/src/SMTP.php";
        require "{$_EnginePath}vendor/phpmailer/phpmailer/src/Exception.php";
        $__MailerInc = TRUE;
    }

    try
    {
        $From = trim($From);
        if(!$From)
        {
            $From = MAILER_MSGFIELDS_FROM;
            $FromName = MAILER_MSGFIELDS_FROM_NAME;
        }
        else
        {
            $FromName = $From;
        }

        $Object = new PHPMailer\PHPMailer\PHPMailer(true);

        if(MAILER_SMTP_USE)
        {
            $Object->IsSMTP();
            $Object->SMTPAuth = true;
            $Object->SMTPSecure = 'ssl';
            $Object->Host = MAILER_SMTP_HOST;
            $Object->Port = MAILER_SMTP_PORT;
            $Object->Username = MAILER_SMTP_USER;
            $Object->Password = MAILER_SMTP_PASSWORD;
        }

        $Object->AltBody = 'HTML Compat needed';
        $Object->WordWrap = 80;
        $Object->IsHTML(true);

        $body = preg_replace('/\\\\/','', $body); //Strip backslashes

        $Object->AddReplyTo($From, $FromName);
        $Object->From = $From;
        $Object->FromName = $FromName;
        $Object->AddAddress($From);
        $Object->Subject = $title;
        $Object->MsgHTML($body);
        $Object->AddCustomHeader('Bcc: '.implode(', ', $to));

        $Object->Send();

        return true;
    }
    catch(phpmailerException $e)
    {
        return $e->errorMessage();
    }
}

function CloseMailConnection()
{
    return SendMail('', '', '', '', '', true);
}

?>
