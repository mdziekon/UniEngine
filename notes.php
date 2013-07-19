<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

	loggedCheck();
	includeLang('notes');
	
	$Parse = &$_Lang;
	$Command = $_GET['cmd'];
	$_PerPage = 10;
	$_MaxLengthTitle = 128;
	$_MaxLengthNote = 5000;
	$_PriorityArray = array(1 => 'lime', 2 => 'orange', 3 => 'red');
	$_Priorities = array(1, 2, 3);
	
	if($_POST['send'] == 1)
	{
		$InsertClean['title'] = substr(trim(stripslashes($_POST['title'])), 0, $_MaxLengthTitle);
		$InsertClean['priority'] = intval($_POST['priority']);
		$InsertClean['text'] = substr(trim(stripslashes($_POST['text'])), 0, $_MaxLengthNote);
		
		$Insert['title'] = mysql_real_escape_string($InsertClean['title']);
		$Insert['priority'] = $InsertClean['priority'];
		$Insert['text'] = mysql_real_escape_string($InsertClean['text']);
	}
	
	if($Command == 'show')
	{
		// Show selected Note
		$TPL_Default = gettemplate('notes_form');
		
		$GetID = round($_GET['id']);
		if($GetID <= 0)
		{
			message($_Lang['Errors_BadIDGiven'], $_Lang['Title'], 'notes.php', 3);
		}
		
		$Get_Row = doquery("SELECT * FROM {{table}} WHERE `id` = {$GetID} LIMIT 1;", 'notes', true);
		if(empty($Get_Row))
		{
			message($_Lang['Errors_IDNoExist'], $_Lang['Title'], 'notes.php', 3);
		}
		if($Get_Row['owner'] != $_User['id'])
		{
			message($_Lang['Errors_NoteNotYour'], $_Lang['Title'], 'notes.php', 3);
		}
		
		if($_POST['send'] == 1)
		{			
			if(!empty($Insert['title']))
			{
				$Get_Row['title'] = $InsertClean['title'];
				if(in_array($Insert['priority'], $_Priorities))
				{
					$Get_Row['priority'] = $InsertClean['priority'];
					if(!empty($Insert['text']))
					{
						$Get_Row['text'] = $InsertClean['text'];
						
						$Query = "UPDATE {{table}} SET `title` = '{$Insert['title']}', `priority` = {$Insert['priority']}, `text` = '{$Insert['text']}', `time` = UNIX_TIMESTAMP() WHERE `id` = {$GetID};";
						doquery($Query, 'notes');
						
						$Parse['Input_MsgText'] = $_Lang['Msg_EditSuccess'];
						$Parse['Input_MsgColor'] = 'lime';
					}
					else
					{
						$Parse['Input_MsgText'] = $_Lang['Errors_TextEmpty'];
						$Parse['Input_MsgColor'] = 'red';
					}
				}
				else
				{
					$Parse['Input_MsgText'] = $_Lang['Errors_BadPriority'];
					$Parse['Input_MsgColor'] = 'red';
				}
			}
			else
			{
				$Parse['Input_MsgText'] = $_Lang['Errors_TitleEmpty'];
				$Parse['Input_MsgColor'] = 'red';
			}
		}
		
		$Parse['Input_Title'] = $Get_Row['title'];
		$Parse['Input_PrioritySelect_'.$Get_Row['priority']] = 'selected';
		$Parse['Input_Text'] = $Get_Row['text'];
		
		$Parse['Input_InsertCMD'] = '?cmd=show&id='.$GetID;
		$Parse['Input_InsertTitle'] = $_Lang['Title_EditRow'];
		$Parse['Input_MaxTitleLength'] = $_MaxLengthTitle;
		$Parse['Input_MaxNoteLength'] = $_MaxLengthNote;
	}
	else if($Command == 'add')
	{
		// Add new note
		$TPL_Default = gettemplate('notes_form');
		
		if($_POST['send'] == 1)
		{			
			if(!empty($Insert['title']))
			{
				if(in_array($Insert['priority'], $_Priorities))
				{
					if(!empty($Insert['text']))
					{						
						$Query = "INSERT INTO {{table}} SET `owner` = {$_User['id']}, `time` = UNIX_TIMESTAMP(), `priority` = {$Insert['priority']}, `title` = '{$Insert['title']}', `text` = '{$Insert['text']}';";
						doquery($Query, 'notes');
						
						header('Location: ?added=true');
						safeDie();
					}
					else
					{
						$Parse['Input_MsgText'] = $_Lang['Errors_TextEmpty'];
						$Parse['Input_MsgColor'] = 'red';
					}
				}
				else
				{
					$Parse['Input_MsgText'] = $_Lang['Errors_BadPriority'];
					$Parse['Input_MsgColor'] = 'red';
				}
			}
			else
			{
				$Parse['Input_MsgText'] = $_Lang['Errors_TitleEmpty'];
				$Parse['Input_MsgColor'] = 'red';
			}
		}
		
		$Parse['Input_Title'] = $InsertClean['title'];
		$Parse['Input_PrioritySelect_'.$InsertClean['priority']] = 'selected';
		$Parse['Input_Text'] = $InsertClean['text'];
		
		$Parse['Input_InsertCMD'] = '?cmd=add';
		$Parse['Input_InsertTitle'] = $_Lang['Title_AddRow'];
		$Parse['Input_MaxTitleLength'] = $_MaxLengthTitle;
		$Parse['Input_MaxNoteLength'] = $_MaxLengthNote;
	}
	else if($Command == 'delete')
	{
		$Command = '';
		
		if($_POST['action'] == 2)
		{
			// Delete all notes
			
			doquery("DELETE FROM {{table}} WHERE `owner` = {$_User['id']};", 'notes');
			if(mysql_affected_rows() > 0)
			{
				$Parse['Input_MsgText'] = $_Lang['Msg_DeleteAllSuccess'];
				$Parse['Input_MsgColor'] = 'lime';
			}
			else
			{
				$Parse['Input_MsgText'] = $_Lang['Errors_NothingToDelete'];
				$Parse['Input_MsgColor'] = 'red';
			}
		}
		else
		{
			// Delete selected notes
			if(!empty($_POST['del']))
			{
				foreach($_POST['del'] as $Key => $Value)
				{
					if($Value == 'on')
					{
						$Key = round($Key);
						if($Key > 0)
						{
							$DeleteIDs[] = $Key;
						}
					}
				}				
			}
			
			if(!empty($DeleteIDs))
			{
				doquery("DELETE FROM {{table}} WHERE `id` IN (".implode(', ', $DeleteIDs).") AND `owner` = {$_User['id']};", 'notes');
				
				if(mysql_affected_rows() > 0)
				{
					$Parse['Input_MsgText'] = $_Lang['Msg_DeleteSelectedSuccess'];
					$Parse['Input_MsgColor'] = 'lime';
				}
				else
				{
					$Parse['Input_MsgText'] = $_Lang['Errors_NothingDeleted'];
					$Parse['Input_MsgColor'] = 'red';
				}
			}
			else
			{
				$Parse['Input_MsgText'] = $_Lang['Errors_NothingDeleted'];
				$Parse['Input_MsgColor'] = 'red';
			}
		}
	}
	else
	{
		$Command = '';
	}
	
	if(empty($Command))
	{
		// Show list of notes
		$TPL_Default = gettemplate('notes_body');
		
		$Get_Count = doquery("SELECT COUNT(`id`) AS `Count` FROM {{table}} WHERE `owner` = {$_User['id']};", 'notes', true);
		$TotalCount = $Get_Count['Count'];
		if($TotalCount > 0)
		{
			$TPL_List_Row = gettemplate('notes_list_row');
			
			$ThisPage = intval($_GET['page']);
			if($ThisPage < 1)
			{
				$ThisPage = 1;
			}
			$SkipCount = ($ThisPage - 1) * $_PerPage;
			if($SkipCount >= $TotalCount)
			{
				$ThisPage = 1;
				$SkipCount = 0;
			}
			
			$GetNotes = doquery("SELECT * FROM {{table}} WHERE `owner` = {$_User['id']} ORDER BY `priority` DESC, `time` DESC LIMIT {$SkipCount}, {$_PerPage};", 'notes');
			while($NoteData = mysql_fetch_assoc($GetNotes))
			{
				$NoteData = array
				(
					'ID' => $NoteData['id'],
					'TitleColor' => $_PriorityArray[$NoteData['priority']],
					'Title' => $NoteData['title'],
					'Date' => prettyDate('d m Y, H:i:s', $NoteData['time'], 1)
				);
				
				$Parse['Input_NotesList'][] = parsetemplate($TPL_List_Row, $NoteData);
			}
			$Parse['Input_NotesList'] = implode('', $Parse['Input_NotesList']);
			
			if($TotalCount > $_PerPage)
			{
				include_once($_EnginePath.'includes/functions/Pagination.php');
				$Pagination = CreatePaginationArray($TotalCount, $_PerPage, $ThisPage, 7);
				$PaginationTPL = "<a class=\"pagin {\$Classes}\" href=\"?page={\$Value}\">{\$ShowValue}</a>";
				$PaginationViewOpt = array('CurrentPage_Classes' => 'orange', 'Breaker_View' => '...');
				$Pagination = ParsePaginationArray($Pagination, $ThisPage, $PaginationTPL, $PaginationViewOpt);
				$Parse['Input_Pagination'] = implode(' ', $Pagination);
			}
			else
			{
				$Parse['Input_HideAtNoPagination'] = 'hide';
			}
			$Parse['Input_HideAtNotes'] = 'hide';
		}
		else
		{
			$Parse['Input_HideAtNoNotes'] = 'hide';
		}
	}
	
	if($_GET['added'] == 'true')
	{
		$Parse['Input_MsgText'] = $_Lang['Msg_AddSuccess'];
		$Parse['Input_MsgColor'] = 'lime';
	}
	
	if(empty($Parse['Input_MsgText']))
	{
		$Parse['Input_HideMsgBox'] = 'hide';
	}
	
	display(parsetemplate($TPL_Default, $Parse), $Parse['Title'], false);

?>