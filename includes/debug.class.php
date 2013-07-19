<?php

if(!defined('INSIDE')){ die('Access Denied!');}

class debug
{
	var $NestingPrevention = 0;
	var $PreviousMessage = '';
	function error($message)
	{
		global $_DBLink, $_User, $_EnginePath;
	
		$this->NestingPrevention += 1;
		if($this->NestingPrevention > 1)
		{
			trigger_error('<b>ErrorNesting Prevention!</b><br/>'.$this->PreviousMessage, E_USER_ERROR);
		}	
		define('IN_ERROR', true);

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
		
		if(!$_DBLink)
		{
			trigger_error('DBDriver Connection Error #01<br/>', E_USER_ERROR);
		}
		if(empty($_User['id']))
		{
			$_User['id'] = '0';
		}
		$Replace_Search		= array('{{table}}', '{{prefix}}');
		$Replace_Replace	= array($__ServerConnectionSettings['prefix'].'errors', $__ServerConnectionSettings['prefix']);
		
		$query = "INSERT INTO {{table}} SET `error_sender` = {$_User['id']}, `error_time` = UNIX_TIMESTAMP(), `error_text` = '".mysql_real_escape_string($message)."';";			
		$query = str_replace($Replace_Search, $Replace_Replace, $query);
		mysql_query($query) or trigger_error('DBDriver Fatal Error #01<br/>'.mysql_error(), E_USER_ERROR);
			
		$query = "SELECT LAST_INSERT_ID() as `id` FROM {{table}} LIMIT 1;";
		$query = str_replace($Replace_Search, $Replace_Replace, $query);
		$q = mysql_fetch_assoc(mysql_query($query)) or trigger_error('DBDriver Fatal Error #02<br/>'.mysql_error(), E_USER_ERROR);
		
		$ErrorMsg = 'An Error occured!<br/>Error ID: <b>'.$q['id'].'</b>';
		$this->PreviousMessage = $ErrorMsg;
	
		if(!function_exists('message'))
		{
			echo $ErrorMsg;
		}
		else
		{
			message($ErrorMsg, 'System Error!');
		}

		$this->NestingPrevention -= 1;
	
		mysql_close($_DBLink);
		die();
	}		
}

?>