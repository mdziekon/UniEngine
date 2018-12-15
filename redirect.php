<?php

$redirects = array();

$ID = (isset($_GET['id']) ? intval($_GET['id']) : 0);
if(!empty($redirects[$ID]))
{
    if($ID == 0 AND isset($_GET['rule']))
    {
        $Add = '#rule'.$_GET['rule'];
    }
    header('Location: '.$redirects[$ID].$Add);
    die();
}
else
{
    define('INSIDE', true);

    $_EnginePath = './';
    include($_EnginePath.'includes/constants.php');

    header('Location: '.GAMEURL_STRICT);
    die();
}

?>
