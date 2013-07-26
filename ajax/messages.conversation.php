<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_DontForceRulesAcceptance = true;
$_UseMinimalCommon = true;

$_SetAccessLogPreFilename = 'ajax/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath.'common.php');

	function ajaxReturn($Array)
	{
		safeDie(json_encode($Array));
	}
	
	$ExcludeIDs = array();
	
	if(!isLogged())
	{
		ajaxReturn(array('Err' => '001'));
	}
	$ThreadID = (isset($_GET['tid']) ? round($_GET['tid']) : 0);
	if($ThreadID <= 0)
	{
		ajaxReturn(array('Err' => '002'));
	}
	if(isset($_GET['exc']) && !empty($_GET['exc']))
	{
		$Temp = explode(',', $_GET['exc']);
		foreach($Temp as $Value)
		{
			$Value = round($Value);
			if($Value > 0)
			{
				$ExcludeIDs[] = $Value;
			}
		}
	}
	$MaxMessageID = 0;
	if(isset($_GET['mid']) && !empty($_GET['mid']))
	{
		$MaxMessageID = round($_GET['mid']);
	}
	$_ThisCategory = 0;
	if(isset($_GET['nc']) && $_GET['nc'] == '1')
	{
		$_ThisCategory = 100;
	}
	
	$Query_GetMessages  = "SELECT `m`.*, `u`.`username`, `u`.`authlevel` FROM {{table}} AS `m` ";
	$Query_GetMessages .= "LEFT JOIN `{{prefix}}users` AS `u` ON `u`.`id` = `m`.`id_sender` ";
	$Query_GetMessages .= "WHERE (`m`.`deleted` = false OR `m`.`id_sender` = {$_User['id']}) AND (`m`.`id_owner` = {$_User['id']} OR `m`.`id_sender` = {$_User['id']}) AND `m`.`Thread_ID` = {$ThreadID} ";
	$Query_GetMessages .= " AND (`m`.`Thread_IsLast` = 0 OR `m`.`id_owner` != {$_User['id']}) ";
	$Query_GetMessages .= (!empty($ExcludeIDs) ? " AND `m`.`id` NOT IN (".implode(', ', $ExcludeIDs).") " : '');
	$Query_GetMessages .= ($MaxMessageID > 0 ? " AND `m`.`id` < {$MaxMessageID} " : '');
	$Query_GetMessages .= "ORDER BY `m`.`time` DESC, `m`.`id` DESC;";
	$GetMessages = doquery($Query_GetMessages, 'messages');
	if(mysql_num_rows($GetMessages) <= 0)
	{
		ajaxReturn(array('Err' => '003'));
	}
	else
	{
		includeLang('messages');
		includeLang('messageSystem');
		includeLang('spyReport');
		includeLang('FleetMission_MissileAttack');

		$MsgColors = array(0 => 'c0', 1 => 'c1', 2 => 'c2', 3 => 'c3', 4 => 'c4', 5 => 'c5', 15 => 'c15', 80 => 'c80', 50 => 'c50', 70 => 'c70', 100 => 'c100');
		
		if($_GameConfig['enable_bbcode'] == 1)
		{
			include($_EnginePath.'includes/functions/BBcodeFunction.php');
		}
		
		$Messages = array();
		while($CurMess = mysql_fetch_assoc($GetMessages))
		{
			$MsgCache[] = $CurMess;
		}

		foreach($MsgCache as $MsgIndex => $CurMess)
		{ 
			$parseMSG = array();			
			// Message sent by User
			$AddFrom = '';
			if(!empty($CurMess['from']))
			{
				$AddFrom = ' '.$CurMess['from'];
			}
			$CurMess['from'] = "{$_Lang['msg_const']['senders']['rangs'][GetAuthLabel($CurMess)]} <a href=\"profile.php?uid={$CurMess['id_sender']}\">{$CurMess['username']}</a>{$AddFrom}";
			
			if(in_array($CurMess['type'], array(2, 80)) AND preg_match('/^\{COPY\_MSG\_\#([0-9]{1,}){1}\}$/D', $CurMess['text'], $ThisMatch))
			{
				$GetMassMsgs[] = $ThisMatch[1];
				$CopyMsgMap[$ThisMatch[1]][] = $CurMess['id'];
				$CurMess['text'] = sprintf($_Lang['msg_const']['msgs']['err4'], $CopyData['id']);
			}
			else
			{
				if($_GameConfig['enable_bbcode'] == 1)
				{
					$CurMess['text'] = bbcode(image($CurMess['text']));
				}
				$CurMess['text'] = nl2br($CurMess['text']);
			}
			
			$parseMSG['CurrMSG_ID'] = $CurMess['id'];
			if($CurMess['read'] == false)
			{
				$parseMSG['CurrMSG_IsUnread'] = ' class="isNew"';
			}
			$parseMSG['CurrMSG_date'] = date('d.m.Y', $CurMess['time']);
			$parseMSG['CurrMSG_time'] = date('H:i:s', $CurMess['time']);
			$parseMSG['CurrMSG_from'] = $CurMess['from']; 
			$parseMSG['CurrMSG_subject'] = $CurMess['subject']; 
			$parseMSG['CurrMSG_text'] = stripslashes(nl2br($CurMess['text'])); 
			if($_ThisCategory == 100)
			{
				$parseMSG['CurrMSG_color'] = $MsgColors[$CurMess['type']];
			}
			else
			{ 
				$parseMSG['CurrMSG_color'] = '';
			}
			if($CurMess['type'] == 80 OR $CurMess['id_sender'] == $_User['id'])
			{
				$parseMSG['CurrMSG_HideCheckbox'] = 'class="inv"';
			}
			$parseMSG['CurrMSG_send'] = sprintf(($CurMess['id_owner'] == $_User['id'] ? $_Lang['mess_send_date'] : $_Lang['mess_sendbyyou_date']), $parseMSG['CurrMSG_date'], $parseMSG['CurrMSG_time']);
			if($CurMess['id_owner'] == $_User['id'])
			{
				if($CurMess['id_sender'] != $_User['id'])
				{
					$parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"reply\" href=\"messages.php?mode=write&amp;replyto=".($CurMess['Thread_ID'] > 0 ? $CurMess['Thread_ID'] : $CurMess['id'])."\">{$_Lang['mess_reply']}</a></span>";
				}
				if($CurMess['type'] == 2 AND $_User['ally_id'] > 0)
				{
					$parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"reply2\" href=\"alliance.php?mode=sendmsg\">{$_Lang['mess_reply_toally']}</a></span>";
				}
					
				if($CurMess['type'] != 80 AND $CurMess['type'] != 2 AND !CheckAuth('supportadmin', AUTHCHECK_HIGHER, $CurMess))
				{
					$parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"ignore\" href=\"settings.php?ignoreadd={$CurMess['id_sender']}\">{$_Lang['mess_ignore']}</a></span>";
				}
				$parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"report2\" href=\"report.php?type=1&amp;uid={$CurMess['id_sender']}&amp;eid={$CurMess['id']}\">{$_Lang['mess_report']}</a></span>";
				$parseMSG['CurrMSG_buttons'][] = "<span class=\"hov\"><a class=\"delete\">{$_Lang['mess_delete_single']}</a></span>";
			}
			if(!empty($parseMSG['CurrMSG_buttons']))
			{
				$parseMSG['CurrMSG_buttons'] = implode('<span class="lnBr"></span>', $parseMSG['CurrMSG_buttons']);
			}
				
			$Messages[$CurMess['id']] = $parseMSG;
		}
		$MsgCache = null;
					
		if(!empty($GetMassMsgs))
		{
			if($_ThisCategory == 100)
			{
				$QryGetMassMsg = doquery("SELECT `id`, `type`, `subject`, `text` FROM {{table}} WHERE `id` IN (".implode(', ', $GetMassMsgs).");", 'messages');
			}
			else
			{
				$QryGetMassMsg = doquery("SELECT `id`, `type`, `subject`, `text`, `from` FROM {{table}} WHERE `id` IN (".implode(', ', $GetMassMsgs).");", 'messages');
			}
			while($CopyData = mysql_fetch_assoc($QryGetMassMsg))
			{
				if($CopyData['type'] == 80 OR $CopyData['type'] == 2)
				{
					foreach($CopyMsgMap[$CopyData['id']] as $MsgKey)
					{
						$Messages[$MsgKey]['CurrMSG_subject'] = $CopyData['subject'];
						$Messages[$MsgKey]['CurrMSG_text'] = $CopyData['text'];
						if($CopyData['type'] == 2)
						{
							$Messages[$MsgKey]['CurrMSG_from'] .= ' '.$CopyData['from'];
						}
					}
				}
				else
				{
					foreach($CopyMsgMap[$CopyData['id']] as $MsgKey)
					{
						$Messages[$MsgKey]['CurrMSG_subject'] = $_Lang['msg_const']['subjects']['019'];
						$Messages[$MsgKey]['CurrMSG_text'] = sprintf($_Lang['msg_const']['msgs']['err3'], $CopyData['id']);
					}
				}
			}
		}
			
		$MsgTPL = gettemplate('message_mailbox_body');						
		foreach($Messages as $MessageData)
		{
			$AllMessages[] = parsetemplate($MsgTPL, $MessageData); 
		}
		
		ajaxReturn(array('Code' => implode('', $AllMessages)));
	}
    
?>