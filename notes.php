<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();
includeLang('notes');

$Parse = &$_Lang;
$Command = (isset($_GET['cmd']) ? $_GET['cmd'] : null);
$_PerPage = 10;
$_MaxLengthTitle = 128;
$_MaxLengthNote = 5000;
$_PriorityArray = array(1 => 'lime', 2 => 'orange', 3 => 'red');
$_Priorities = array(1, 2, 3);

if(isset($_POST['send']) && $_POST['send'] == 1)
{
    $InsertClean['title'] = substr(trim(stripslashes($_POST['title'])), 0, $_MaxLengthTitle);
    $InsertClean['priority'] = intval($_POST['priority']);
    $InsertClean['text'] = substr(trim(stripslashes($_POST['text'])), 0, $_MaxLengthNote);

    $Insert['title'] = getDBLink()->escape_string($InsertClean['title']);
    $Insert['priority'] = $InsertClean['priority'];
    $Insert['text'] = getDBLink()->escape_string($InsertClean['text']);
}

if($Command == 'show')
{
    // Show selected Note
    $TPL_Default = gettemplate('notes_form');

    $GetID = (isset($_GET['id']) ? round($_GET['id']) : 0);
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

    if(isset($_POST['send']) && $_POST['send'] == 1)
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

                    $Query_UpdateNote = '';
                    $Query_UpdateNote .= "UPDATE {{table}} SET ";
                    $Query_UpdateNote .= "`title` = '{$Insert['title']}', ";
                    $Query_UpdateNote .= "`priority` = {$Insert['priority']}, ";
                    $Query_UpdateNote .= "`text` = '{$Insert['text']}', ";
                    $Query_UpdateNote .= "`time` = UNIX_TIMESTAMP() ";
                    $Query_UpdateNote .= "WHERE `id` = {$GetID};";
                    doquery($Query_UpdateNote, 'notes');

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

    if(isset($_POST['send']) && $_POST['send'] == 1)
    {
        if(!empty($Insert['title']))
        {
            if(in_array($Insert['priority'], $_Priorities))
            {
                if(!empty($Insert['text']))
                {
                    $Query_InsertNote = '';
                    $Query_InsertNote .= "INSERT INTO {{table}} SET ";
                    $Query_InsertNote .= "`owner` = {$_User['id']}, ";
                    $Query_InsertNote .= "`time` = UNIX_TIMESTAMP(), ";
                    $Query_InsertNote .= "`priority` = {$Insert['priority']}, ";
                    $Query_InsertNote .= "`title` = '{$Insert['title']}', ";
                    $Query_InsertNote .= "`text` = '{$Insert['text']}';";
                    doquery($Query_InsertNote, 'notes');

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

    if(isset($InsertClean))
    {
        $Parse['Input_Title'] = $InsertClean['title'];
        $Parse['Input_PrioritySelect_'.$InsertClean['priority']] = 'selected';
        $Parse['Input_Text'] = $InsertClean['text'];
    }

    $Parse['Input_InsertCMD'] = '?cmd=add';
    $Parse['Input_InsertTitle'] = $_Lang['Title_AddRow'];
    $Parse['Input_MaxTitleLength'] = $_MaxLengthTitle;
    $Parse['Input_MaxNoteLength'] = $_MaxLengthNote;
}
else if($Command == 'delete')
{
    $Command = '';

    if(isset($_POST['action']) && $_POST['action'] == 2)
    {
        // Delete all notes

        doquery("DELETE FROM {{table}} WHERE `owner` = {$_User['id']};", 'notes');
        if(getDBLink()->affected_rows > 0)
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

            if(getDBLink()->affected_rows > 0)
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

        $ThisPage = (isset($_GET['page']) ? intval($_GET['page']) : 0);
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

        $Query_GetNotes = '';
        $Query_GetNotes .= "SELECT * FROM {{table}} ";
        $Query_GetNotes .= "WHERE `owner` = {$_User['id']} ";
        $Query_GetNotes .= "ORDER BY `priority` DESC, `time` DESC LIMIT {$SkipCount}, {$_PerPage};";

        $SQLResult_GetNotes = doquery($Query_GetNotes, 'notes');

        while($NoteData = $SQLResult_GetNotes->fetch_assoc())
        {
            $NoteData = array
            (
                'ID'            => $NoteData['id'],
                'TitleColor'    => $_PriorityArray[$NoteData['priority']],
                'Title'            => $NoteData['title'],
                'Date'            => prettyDate('d m Y, H:i:s', $NoteData['time'], 1)
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

if(isset($_GET['added']) && $_GET['added'] == 'true')
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
