<?php

define('INSIDE', true);
define('IN_RULES', true);

$_DontShowMenus = true;
$_DontCheckPolls = true;
$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

	includeLang('rules');
	$TPL = gettemplate('rules_body');

	if(isLogged() && isset($_ForceRulesAcceptBox) && $_ForceRulesAcceptBox === true)
	{
		$IsInDelete = ($_User['is_ondeletion'] == 1 ? true : false);

		if(isset($_GET['cmd']))
		{
			if($_GET['cmd'] == 'decline')
			{
				if($IsInDelete !== true)
				{
					$_User['deletion_endtime'] = time() + (ACCOUNT_DELETION_TIME * TIME_DAY);
					doquery("UPDATE {{table}} SET `is_ondeletion` = 1, `deletion_endtime` = {$_User['deletion_endtime']} WHERE `id` = {$_User['id']} LIMIT 1;", 'users');
					$IsInDelete = true;
					$_User['is_ondeletion'] = 1;
				}
			}
			else if($_GET['cmd'] == 'accept')
			{
				$AddToUpdate = '';
				if($IsInDelete === true)
				{
					$AddToUpdate = ", `is_ondeletion` = 0, `deletion_endtime` = 0";
				}
				doquery("UPDATE {{table}} SET `rules_accept_stamp` = {$_GameConfig['last_rules_changes']} {$AddToUpdate} WHERE `id` = {$_User['id']} LIMIT 1;", 'users');
				header('Location: overview.php');
				safeDie();
			}
		}

		if($_User['is_ondeletion'] == 1)
		{
			$_Lang['AcceptBox_Option_Accept'] = $_Lang['AcceptBox_Option_Accept1'];
			$_Lang['AcceptBox_Option_Decline'] = $_Lang['AcceptBox_Option_Decline1'];
			$_Lang['AcceptBox_InsertDeleteTime'] = sprintf($_Lang['AcceptBox_DeleteTime'], prettyDate('d m Y \o H:i:s', intval($_User['deletion_endtime']), 1));
		}
		else
		{
			$_Lang['AcceptBox_Option_Accept'] = $_Lang['AcceptBox_Option_Accept0'];
			$_Lang['AcceptBox_Option_Decline'] = $_Lang['AcceptBox_Option_Decline0'];
		}
		$_Lang['AcceptBox_Info'] = sprintf($_Lang['AcceptBox_Info'], prettyDate('d m Y, H:i:s', $_GameConfig['last_rules_changes'], 1));

		$TPL = str_replace('{InsertRulesAcceptanceBox}', gettemplate('rules_acceptbox'), $TPL);
	}

	display(parsetemplate($TPL, $_Lang), $_Lang['Page_Title'], false);

?>