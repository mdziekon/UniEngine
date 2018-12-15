<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

includeLang('activate');

$Activated = false;

$_GET['code'] = (isset($_GET['code']) ? trim($_GET['code']) : null);
if(!empty($_GET['code']))
{
    if(preg_match('/^[0-9a-zA-Z]{32}$/D', $_GET['code']))
    {
        $Result = doquery("SELECT `id`, `username` FROM {{table}} WHERE `activation_code` = '{$_GET['code']}' LIMIT 1;", 'users');
        if(mysql_num_rows($Result) > 0)
        {
            $Result = mysql_fetch_assoc($Result);
            $Username = $Result['username'];
            doquery("UPDATE {{table}} SET `activation_code` = '' WHERE `id` = '{$Result['id']}';", 'users');

            $Msg = sprintf($_Lang['activation_completed'], $Username, 'overview.php', 3);
            $Activated = true;
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
}
else
{
    $Msg = $_Lang['empty_code'];
}

if($Activated)
{
    $Color = 'lime';
}
else
{
    $Color = 'red';
}

message("<span style=\"color: {$Color}\">{$Msg}</span><br/><br/>{$_Lang['go_back']}", $_Lang['title']);

?>
