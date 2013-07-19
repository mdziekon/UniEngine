<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';

include($_EnginePath.'common.php');

	loggedCheck();

	$AllowedTypes		= array('user_badmsg', 'user_bash', 'user_push', 'user_other', 'sys_badmsg', 'sys_error', 'mail_smtp', 'other', 'user_badmsg_chat');
	$TypesFlip			= array_flip($AllowedTypes);
	$NeedUsername		= array('user_badmsg', 'user_bash', 'user_push', 'user_other', 'user_badmsg_chat');
	$NeedAddInfo		= array('user_bash', 'user_push', 'user_other', 'sys_badmsg', 'sys_error', 'mail_smtp', 'other');
	$NeedElement		= array('user_badmsg', 'sys_badmsg', 'user_badmsg_chat');
	$CanHaveUsername	= array('other');

	includeLang('report');
	$Parse = $_Lang;
	$PageTPL = gettemplate('report');

	if($_POST['mode'] == 'send_report')
	{
		$Sent = false;

		if(!in_array($_POST['type'], $AllowedTypes))
		{
			$ShowMSG = $_Lang['Error_badtype']; 
		}
		else
		{
			$ReportType = $_POST['type'];

			$UserID = '0';
			$IsUsernameNeeded = in_array($ReportType, $NeedUsername);
			
			if(!empty($_POST['reported_username']) OR $IsUsernameNeeded)
			{
				if($IsUsernameNeeded)
				{
					$AllowGo = false;
				}
				if($IsUsernameNeeded AND empty($_POST['reported_username']))
				{
					$ShowMSG = $_Lang['Error_nousername'];
				}
				else
				{
					if(!preg_match(REGEXP_USERNAME_ABSOLUTE, $_POST['reported_username']))
					{
						$ShowMSG = $_Lang['Error_username_signs'];
						$AllowGo = false;
					}
					else
					{
						$CheckUser = doquery("SELECT `id` FROM {{table}} WHERE `username` = '{$_POST['reported_username']}' LIMIT 1;", 'users', true);
						if($CheckUser['id'] <= 0)
						{
							$ShowMSG = $_Lang['Error_user_noexists'];
							$AllowGo = false;
						}
						else
						{
							$UserID = $CheckUser['id'];
							$AllowGo = true;
						}
					}
				}
			}

			if($AllowGo !== false)
			{
				if(in_array($ReportType, $NeedAddInfo) AND empty($_POST['user_info']))
				{
					$ShowMSG = $_Lang['Error_no_info_given'];
				}
				else
				{
					$ElementID = round($_POST['eid']);
					if(empty($ElementID))
					{
						$ElementID = '0';
					}
					if(in_array($ReportType, $NeedElement) AND $ElementID <= 0)
					{
						$ShowMSG = $_Lang['Error_no_element_given'];
					}
					else
					{
						if($UserID > 0 AND !in_array($ReportType, $NeedUsername) AND !in_array($ReportType, $CanHaveUsername))
						{
							$UserID = '0';
						}
						if($ElementID > 0 AND !in_array($ReportType, $NeedElement))
						{
							$ElementID = '0';
						}

						$QrySendReport .= "INSERT INTO {{table}} SET ";
						$QrySendReport .= "`date` = UNIX_TIMESTAMP(), ";
						$QrySendReport .= "`sender_id` = {$_User['id']}, ";
						$QrySendReport .= "`report_type` = ".($TypesFlip[$ReportType] + 1).", ";
						$QrySendReport .= "`report_element` = {$ElementID}, ";
						$QrySendReport .= "`report_user` = {$UserID}, ";
						$QrySendReport .= "`user_info` = '".trim(mysql_real_escape_string(strip_tags(stripslashes($_POST['user_info']))))."';";
						doquery($QrySendReport, 'reports');

						$Sent = true;
					}
				}
			}
		}

		if($Sent === false)
		{
			$Parse['post_user_info']			= $_POST['user_info'];
			$Parse['post_reported_username']	= $_POST['reported_username'];
			$Parse['post_eid']					= $_POST['eid'];
			$Parse['select_type_'.$ReportType]	= 'selected';
			$Parse['Report_send_result']		= '<br/><span class="red">'.$ShowMSG.'</span><br/>&nbsp;';
		}
		else
		{
			$Parse['Report_send_result'] = '<br/><span class="lime">'.$_Lang['Report_sent'].'</span><br/>&nbsp;';
		}
	}
	else
	{
		if(!empty($_GET['eid']))
		{
			$Parse['get_eid'] = round($_GET['eid']);
		}
		if(!empty($_GET['uid']))
		{
			$UID = intval($_GET['uid']);
			$SelectUIDData = doquery("SELECT `username` FROM {{table}} WHERE `id` = {$UID} LIMIT 1;", 'users', true);
			if(!empty($SelectUIDData['username']))
			{
				$Parse['get_uid'] = $SelectUIDData['username'];
			}
		}
		if(!empty($_GET['type']))
		{
			$Type = intval($_GET['type']);
			$Parse['select_type_'.$AllowedTypes[($Type - 1)]] = 'selected';
		}
		if(!empty($_GET['info']))
		{
			$Parse['post_user_info'] = $_GET['info'];
		}

		if(($Type != 1 AND $Type != 9) OR $Parse['get_eid'] <= 0)
		{
			$Parse['Input_HideType1'] = ' disabled';
			$Parse['select_type_'.$AllowedTypes[0]] = '';
		}
	}

	$Page = parsetemplate($PageTPL, $Parse);
	display($Page, $_Lang['Title'], false);

?>