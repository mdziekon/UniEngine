<?php

if(!empty($_GET['r']))
{
    $ID = intval($_GET['r']);
    if($ID > 0)
    {
        define('INSIDE', true);

        $_EnginePath = './';
        include($_EnginePath.'includes/constants.php');

        if($_COOKIE[REFERING_COOKIENAME] <= 0)
        {
            setcookie(REFERING_COOKIENAME, $ID, time() + (14*24*60*60), '', GAMEURL_DOMAIN);
        }
        header('Location: reg_mainpage.php'); 
        die();
    }
}

header('Location: login.php');
die();

?>
