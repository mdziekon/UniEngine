<?php

define('INSIDE', true);

$_AllowInVacationMode = true;
$_DontCheckPolls = true;
$_DontShowMenus = true;
$_DontForceRulesAcceptance = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();
includeLang('logout');

setcookie(getSessionCookieKey(), '', time() - 100000, '/', '', 0);

if(isset($_GET['kicked']))
{
    $Msg = $_Lang['You_have_been_kicked'];
}
else if(isset($_GET['badip']))
{
    header('Location: login.php');
    safeDie();
}
else
{
    $Msg = $_Lang['see_you'];
}

message($Msg, $_Lang['session_closed'], 'login.php');

?>
