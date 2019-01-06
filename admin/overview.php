<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath.'common.php');

if(CheckAuth('go'))
{
    header('Location: userlist.php?over=yes');
    safeDie();
}
else
{
    AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

?>
