<?php
/**
 * XNOVA 2015
 * @url https://github.com/XxidroxX/Xnova
 * TODO: check functions and move in a new file 
 */
if(!defined('IN_INSTALL'))
{
	die();
}

function generateRandomHash($Length)
{
	$Signs = '0123456789abcdefghijklmnoprstuwxyzABCDEFGHIJKLMNOPRSTUWXYZ_';
	$SignsLength = strlen($Signs) - 1;
	
	$Return = '';
	for($i = 0; $i < $Length; ++$i)
	{
		$Return .= $Signs[mt_rand(0, $SignsLength)];
	}
	return $Return;
}

function parseFile($filepath, $parseArray)
{
	return preg_replace('#\{([a-z0-9\-_]*?)\}#Ssie', '( ( isset($parseArray[\'\1\']) ) ? $parseArray[\'\1\'] : \'\' );', file_get_contents($filepath));
}

function display()
{
	global $_Lang;
	
	echo parseFile('install_body.tpl', $_Lang);
}

function includeLang()
{
	global $_Lang, $_UseLang;
	
	include("install_lang_".$_UseLang.".lang");
}

?>