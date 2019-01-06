<?php

if(!isset($_GET['a']))
{
    $_GET['a'] = 0;
}
header('Location: alliance.php?mode=ainfo&a='.intval($_GET['a']));
die();

?>
