<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

includeLang('admin');
includeLang('admin/massmessage');

$_MaxLength_Subject = 100;
$_MaxLength_Text = 5000;

if(CheckAuth('supportadmin'))
{
    if($_POST && isset($_GET['mode']) && $_GET['mode'] == "change")
    {
        // If Everything is OK, so set variables and send mails!
        if(!empty($_POST['text']) AND !empty($_POST["subject"]))
        {
            $SQLResult_GetUsers = doquery("SELECT `id` FROM {{table}}", "users");
            $Time = time();

            $_POST['text'] = stripslashes(trim($_POST['text']));
            $_POST['subject'] = stripslashes(trim($_POST['subject']));
            if(get_magic_quotes_gpc())
            {
                $_POST['text']= stripslashes($_POST['text']);
                $_POST['subject'] = stripslashes($_POST['subject']);
            }

            $Subject = substr(strip_tags($_POST['subject']), 0, $_MaxLength_Subject);
            $Message = substr(strip_tags($_POST['text']), 0, $_MaxLength_Text);

            $UserList = [];
            while($UsersData = $SQLResult_GetUsers->fetch_assoc())
            {
                $UserList[] = $UsersData['id'];
            }

            $FirstMSGID = SendSimpleMessage($UserList[0], $_User['id'], $Time, 80, '', $Subject, $Message, true);
            unset($UserList[0]);

            Cache_Message($UserList, $_User['id'], $Time, 80, '', '', '{COPY_MSG_#'.$FirstMSGID.'}');

            message('<span style="color: lime;">'.sprintf($_Lang['MassMessage_Success'], count($UserList)).'</span>', 'MassMessage');
        }
        else
        {
            message($_Lang['MassMessage_NoSufficientData'], 'MassMessage');
        }
    }
    else
    {
        $_Lang['FormInsert_MaxSigns'] = $_MaxLength_Text;

        $page = parsetemplate(gettemplate('admin/massmessage_body'), $_Lang);
        display($page, 'MassMessage', false, true);
    }
}
else
{
    // User have no required access
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

?>
