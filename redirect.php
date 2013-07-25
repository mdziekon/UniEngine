<?php

$redirects = 
array
(
	
);

$ID = intval($_GET['id']);
if(!empty($redirects[$ID]))
{
	if($ID == 0 AND !empty($_GET['rule']))
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