<?php

function doquery($query, $table, $fetch = false, $SilentMode = false)
{
	global $_DBLink, $_EnginePath, $_User;
	static $__ServerConnectionSettings, $debug = NULL;
	
	if($debug === NULL)
	{
		include($_EnginePath.'includes/debug.class.php');
		$debug = new debug();
	}

	if(empty($__ServerConnectionSettings))
	{
		if(LOCALHOST)
		{
			require($_EnginePath.'config.localhost.php');
		}
		else if(TESTSERVER)
		{
			require($_EnginePath.'config.testserver.php');
		}
		else
		{
			require($_EnginePath.'config.php');
		}
	}

	if(!$_DBLink)
	{
		if($SilentMode)
		{
			$_DBLink = @mysql_connect($__ServerConnectionSettings['server'], $__ServerConnectionSettings['user'], $__ServerConnectionSettings['pass']);
			@mysql_select_db($__ServerConnectionSettings['name']);
		}
		else
		{
			$_DBLink = mysql_connect($__ServerConnectionSettings['server'], $__ServerConnectionSettings['user'], $__ServerConnectionSettings['pass']) or $debug->error(mysql_error().'<br/>'.$query);
			mysql_select_db($__ServerConnectionSettings['name']) or $debug->error(mysql_error().'<br/>'.$query);
		}
		mysql_query("SET NAMES 'UTF8';");
	}
	$Replace_Search = array('{{table}}', '{{prefix}}', 'DROP');
	$Replace_Replace = array($__ServerConnectionSettings['prefix'].$table, $__ServerConnectionSettings['prefix'], '');
	$sql = str_replace($Replace_Search, $Replace_Replace, $query);
	if(isset($_User['id']) && $_User['id'] > 1)
	{
		$sql = str_replace('TRUNCATE', '', $sql);
	}

	if($SilentMode)
	{
		$sqlquery = @mysql_query($sql);
	}
	else
	{
		$sqlquery = mysql_query($sql) or $debug->error(mysql_error().'<br/>'.$sql.'<br/>File: '.$_SERVER['REQUEST_URI'].'<br/>User: '.$_User['username'].'['.$_User['id'].']<br/>');
	}
	
	if($fetch)
	{
		if($SilentMode)
		{
			$sqlrow = mysql_fetch_array($sqlquery, MYSQL_ASSOC);
		}
		else
		{
			$sqlrow = @mysql_fetch_array($sqlquery, MYSQL_ASSOC);
		}
		return $sqlrow;
	}
	else
	{
		return $sqlquery;
	}
}

?>