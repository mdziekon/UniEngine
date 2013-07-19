<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

	loggedCheck();

	includeLang('reftable');
	$_Lang['skinpath'] = $_SkinPath;

	$RefList = doquery("SELECT `ref`.*, `users`.username FROM {{table}} AS `ref` LEFT JOIN {{prefix}}users AS `users` ON `users`.`id` = `ref`.`newuser_id` WHERE `ref`.`referrer_id` = {$_User['id']};", 'referring_table');

	$_Lang['referralLink'] = GAMEURL . 'index.php?r='.$_User['id'];
	$_Lang['referring_info'] = sprintf($_Lang['referring_info'], (REFERING_PROVISION * 100));

	if(mysql_num_rows($RefList) > 0)
	{
		$RowTPL = gettemplate('ref_table_row');

		while($NewUser = mysql_fetch_assoc($RefList))
		{
			if(!empty($NewUser['username']))
			{
				$NewUser['Username'] = $NewUser['username'];
			}
			else
			{
				$NewUser['Username'] = "<b class=\"red\">{$_Lang['UserDeleted']}</b>";
			}
			$NewUser['RegDate'] = prettyDate('d m Y - H:i:s', $NewUser['time'], 1);
			$NewUser['Provision'] = "{$NewUser['provisions_granted']} {$_Lang['DEUnits']}";

			$_Lang['Rows'] .= parsetemplate($RowTPL, $NewUser);
		}
	}
	else
	{
		$_Lang['Rows'] = "<tr><th class=\"b red pad\" colspan=\"3\">{$_Lang['referred_noone']}</th></tr>";
	}

	$page = parsetemplate(gettemplate('ref_table_body'), $_Lang);
	display($page, $_Lang['reftable'], false);

?>