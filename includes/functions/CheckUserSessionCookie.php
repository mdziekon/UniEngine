<?php

function CheckUserSessionCookie()
{
	global $_GameConfig, $_EnginePath, $_Lang, $_DontShowMenus;
	$Init['$_DontShowMenus'] = $_DontShowMenus;
	$_DontShowMenus = true;

	$UserRow = false;
	require($_EnginePath.'config.php');
	if(isset($_COOKIE[$_GameConfig['COOKIE_NAME']]))
	{
		$TheCookie = explode('/%/', $_COOKIE[$_GameConfig['COOKIE_NAME']]);
		$TheCookie[0] = intval($TheCookie[0]);
		if($TheCookie[0] <= 0)
		{
			includeLang('cookies');
			message($_Lang['cookies']['Error1'], $_Lang['cookies']['Title']);
		}
		$Query_GetUser = '';
		$Query_GetUser .= "SELECT `user`.*, `stats`.`total_rank`, `stats`.`total_points`, `ally`.`ally_name`, `ally`.`ally_owner`, `ally`.`ally_ranks`, `ally`.`ally_ChatRoom_ID` ";
		$Query_GetUser .= "FROM {{table}} AS `user` ";
		$Query_GetUser .= "LEFT JOIN `{{prefix}}statpoints` AS `stats` ON `user`.`id` = `stats`.`id_owner` AND `stats`.`stat_type` = '1' ";
		$Query_GetUser .= "LEFT JOIN `{{prefix}}alliance` AS `ally` ON `ally`.`id` = `user`.`ally_id` ";
		$Query_GetUser .= "WHERE `user`.`id` = {$TheCookie[0]} LIMIT 1;";
		$UserResult = doquery($Query_GetUser, 'users');
		
		// Check if User exists
		if(mysql_num_rows($UserResult) != 1)
		{
			includeLang('cookies');
			message($_Lang['cookies']['Error2'], $_Lang['cookies']['Title']);
		}
		$UserRow = mysql_fetch_assoc($UserResult);
		
		// Check if Password is correct
		if(!isset($__ServerConnectionSettings['secretword']))
		{
			$__ServerConnectionSettings['secretword'] = '';
		}
		if(md5("{$UserRow['password']}--{$__ServerConnectionSettings['secretword']}") !== $TheCookie[2])
		{
			includeLang('cookies');
			message($_Lang['cookies']['Error3'], $_Lang['cookies']['Title']);
		}
		
		$NextCookie = implode('/%/', $TheCookie);
		if($TheCookie[3] == 1)
		{
			$ExpireTime = time() + 31536000;
		}
		else
		{
			$ExpireTime = 0;
		}

		if(!preg_match('/^[0-9]{1,4}\_[0-9]{1,4}\_[0-9]{1,3}$/D', $_COOKIE['var_1124']))
		{
			$_COOKIE['var_1124'] = '';
		}
		else
		{
			$UserRow['new_screen_settings'] = $_COOKIE['var_1124'];
		}
		
		$Query_UpdateUser = '';
		$Query_UpdateUser .= "UPDATE {{table}} SET ";
		$Query_UpdateUser .= "`onlinetime` = UNIX_TIMESTAMP(), ";
		$Query_UpdateUser .= "`current_page` = '".mysql_real_escape_string($_SERVER['REQUEST_URI'])."', ";
		$Query_UpdateUser .= "`user_lastip` = '{$_SERVER['REMOTE_ADDR']}', ";
		$Query_UpdateUser .= "`user_agent` = '".mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'])."', ";
		$Query_UpdateUser .= "`screen_settings` = '".preg_replace('#[^0-9\_]{1,}#si', '', $_COOKIE['var_1124'])."' ";
		$Query_UpdateUser .= "WHERE `id` = {$TheCookie[0]} LIMIT 1;";
		doquery($Query_UpdateUser, 'users');
		
		Tasks_CheckUservar($UserRow);
		
		setcookie($_GameConfig['COOKIE_NAME'], FALSE, 0, '/', '.'.GAMEURL_DOMAIN);
		setcookie($_GameConfig['COOKIE_NAME'], $NextCookie, $ExpireTime, '/', '', false, true);
	}
	unset($__ServerConnectionSettings);
	$_DontShowMenus = $Init['$_DontShowMenus'];
	
	return $UserRow;
}

?>