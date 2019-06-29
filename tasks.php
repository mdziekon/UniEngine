<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

$TableWidth = 800;
$TaskTabWidthReal = 15;
$TabsPerRow = 25;

loggedCheck();

includeLang('tasks');
$BodyTPL = gettemplate('tasks_body');

$_Lang['MsgBox_Hide'] = 'class="inv"';
$_Lang['MsgBox_Text'] = '&nbsp;';
$ShowMsgBox = false;

$Mode = (isset($_GET['mode']) ? $_GET['mode'] : null);

if(isset($_GET['skipcat']) && $_GET['skipcat'] > 0)
{
    $SkipCatID = intval($_GET['skipcat']);
    if(!empty($_Vars_TasksData[$SkipCatID]))
    {
        if($_Vars_TasksData[$SkipCatID]['skip']['possible'] === true)
        {
            if(!Tasks_IsCatDone($SkipCatID, $_User))
            {
                foreach($_Vars_TasksData[$SkipCatID]['tasks'] as $TaskID => $TaskData)
                {
                    if(!empty($_User['tasks_done_parsed']['done'][$SkipCatID]))
                    {
                        if(in_array($TaskID, $_User['tasks_done_parsed']['done'][$SkipCatID]))
                        {
                            continue;
                        }
                    }
                    $CreateSkipArray[] = $TaskID;
                }
                $_User['tasks_done_parsed']['locked'][$SkipCatID.'s'] = $CreateSkipArray;
                $_User['tasks_done'] = json_encode($_User['tasks_done_parsed']);

                doquery("UPDATE {{table}} SET `tasks_done` = '{$_User['tasks_done']}' WHERE `id` = {$_User['id']};", 'users');
                $_User['tasks_done_parsed'] = null;
                Tasks_CheckUservar($_User);

                $ShowMsgBox = true;
                $_Lang['MsgBox_Text'] = sprintf($_Lang['Msg_Skiped'], $_Lang['TasksCats'][$SkipCatID]);
                $_Lang['MsgBox_Colo'] = 'lime';
            }
            else
            {
                $ShowMsgBox = true;
                $_Lang['MsgBox_Text'] = $_Lang['Msg_CantSkipDone'];
                $_Lang['MsgBox_Colo'] = 'orange';
            }
        }
        else
        {
            $ShowMsgBox = true;
            $_Lang['MsgBox_Text'] = $_Lang['Msg_CantSkipUnSkippable'];
            $_Lang['MsgBox_Colo'] = 'red';
        }
    }
    else
    {
        $ShowMsgBox = true;
        $_Lang['MsgBox_Text'] = $_Lang['Msg_CatNoExist'];
        $_Lang['MsgBox_Colo'] = 'red';
    }
}

if(1 == 2/*$Mode == 'achievements'*/)
{
    $_Lang['SetActiveTab'] = 'achievements';
}
else
{
    if($Mode == 'log')
    {
        $_Lang['SetActiveTab'] = 'log';
        $_Lang['SetActiveMode'] = 'log';
        $_Lang['Input_SetMode'] = 'mode=log';
        $_Lang['Tab01_CatList_TabTitle'] = $_Lang['Tab01_CatListDone_TabTitle'];
        $ShowLog = true;
    }
    else
    {
        $ShowLog = false;
    }

    // User shows Active Tasks
    $SelectCat = 0;
    if(isset($_GET['cat']) && $_GET['cat'] > 0)
    {
        $SelectCat = intval($_GET['cat']);
        if(empty($_Vars_TasksData[$SelectCat]))
        {
            $ShowMsgBox = true;
            $SelectCat = 0;
            $_Lang['MsgBox_Text'] = $_Lang['Msg_CatNoExist'];
        }
    }
    if($SelectCat > 0)
    {
        if($ShowLog === false)
        {
            if(Tasks_IsCatDone($SelectCat, $_User))
            {
                $ShowMsgBox = true;
                $SelectCat = 0;
                $_Lang['MsgBox_Text'] = $_Lang['Msg_CatDone'];
                $_Lang['MsgBox_Colo'] = 'orange';
            }
            elseif(!Tasks_CheckCatAvailable($SelectCat, $_User))
            {
                $ShowMsgBox = true;
                $SelectCat = 0;
                $_Lang['MsgBox_Text'] = $_Lang['Msg_CatNotAvailable'];
                $_Lang['MsgBox_Colo'] = 'red';
            }
        }
        else
        {
            if(!Tasks_IsCatDone($SelectCat, $_User))
            {
                $ShowMsgBox = true;
                $SelectCat = 0;
                $_Lang['MsgBox_Text'] = $_Lang['Msg_CatNotDone'];
                $_Lang['MsgBox_Colo'] = 'red';
            }
        }
    }

    if($SelectCat > 0)
    {
        // User shows TasksList/CatDescription
        $PageTPL = gettemplate('tasks_list_showcats_ov');
        $TaskTabTPL = gettemplate('tasks_list_showcats_ov_tasktab');
        $TaskBoxTPL_Desc = gettemplate('tasks_list_showcats_ov_taskbox_desc');
        $TaskBoxTPL_NoDesc = gettemplate('tasks_list_showcats_ov_taskbox_nodesc');

        $_Lang['Input_CatID'] = $SelectCat;
        $_Lang['Input_TasksCat'] = $_Lang['TasksCats'][$SelectCat];
        $_Lang['Input_TabFullLen'] = count($_Vars_TasksData[$SelectCat]['tasks']) + 4;
        if($_Lang['Input_TabFullLen'] > $TabsPerRow + 4)
        {
            $_Lang['Input_TabFullLen'] = $TabsPerRow + 4;
        }
        $_Lang['Input_TaskTabWidth'] = floor(($TableWidth - (($TaskTabWidthReal + 10) * $_Lang['Input_TabFullLen'])) / 2);

        if($_Vars_TasksData[$SelectCat]['skip']['possible'] === true AND $ShowLog === false)
        {
            if($_Vars_TasksData[$SelectCat]['skip']['tasksrew'] === true AND $_Vars_TasksData[$SelectCat]['skip']['catrew'] === true)
            {
                $_Lang['Tab01_CatSel_SkipInfo'] = sprintf($_Lang['Tab01_CatSel_SkipInfo'], 'lime', $_Lang['Tab01_CatSel_SkipBoth']);
            }
            elseif($_Vars_TasksData[$SelectCat]['skip']['tasksrew'] === true AND $_Vars_TasksData[$SelectCat]['skip']['catrew'] === false)
            {
                $_Lang['Tab01_CatSel_SkipInfo'] = sprintf($_Lang['Tab01_CatSel_SkipInfo'], 'orange', $_Lang['Tab01_CatSel_SkipTaskOnly']);
            }
            elseif($_Vars_TasksData[$SelectCat]['skip']['tasksrew'] === false AND $_Vars_TasksData[$SelectCat]['skip']['catrew'] === true)
            {
                $_Lang['Tab01_CatSel_SkipInfo'] = sprintf($_Lang['Tab01_CatSel_SkipInfo'], 'orange', $_Lang['Tab01_CatSel_SkipCatOnly']);
            }
            else
            {
                $_Lang['Tab01_CatSel_SkipInfo'] = sprintf($_Lang['Tab01_CatSel_SkipInfo'], 'red', $_Lang['Tab01_CatSel_SkipNone']);
            }
        }
        else
        {
            $_Lang['Input_HideCatSkip'] = ' class="hide"';
        }

        $CategoryData = Tasks_GetTaskCategoryData($SelectCat);

        if (
            !empty($CategoryData['reward']) &&
            count($CategoryData['reward']) > 0
        ) {
            $rewards = Tasks_GenerateRewardsStrings($CategoryData['reward'], $_Lang);

            $_Lang['Input_CatRewards'] = '';

            foreach ($rewards as $rewardString) {
                $_Lang['Input_CatRewards'] .= "&#149; {$rewardString}<br/>";
            }
        } else {
            $_Lang['Input_HideCatRewards'] = ' class="hide"';
        }

        if(!empty($_Lang['Input_HideCatSkip']) AND !empty($_Lang['Input_HideCatRewards']))
        {
            $_Lang['Input_HideCatRewardsOrSkip'] = ' class="hide"';
        }
        else
        {
            if(empty($_Lang['Input_HideCatSkip']))
            {
                if(empty($_Lang['Input_HideCatRewards']))
                {
                    $_Lang['Input_CatSkipTDClass'] = 'noBorL w50p';
                    $_Lang['Input_CatRewardsTDClass'] = 'w50p';
                }
                else
                {
                    $_Lang['Input_CatSkipTDClass'] = 'noBor w100p';
                    $_Lang['Input_CatRewardsTDClass'] = 'w0p';
                }
            }
        }

        if (!empty($_Lang['Input_HideCatSkip'])) {
            if (empty($_Lang['Input_CatSkipTDClass'])) {
                $_Lang['Input_CatSkipTDClass'] = 'hide';
            } else {
                $_Lang['Input_CatSkipTDClass'] .= ' hide';
            }
        }

        $_Lang['SetActiveTask'] = '1';

        $_Lang['Input_CreateTaskRows'] = '';

        $TaskLoop = 1;
        $TabRow = 1;
        foreach($_Vars_TasksData[$SelectCat]['tasks'] as $TaskID => $TaskData)
        {
            $TaskNOs[$TaskID] = $TaskLoop;

            $ThisTask = array();
            $ThisTask['Input_JobsToDo'] = '';
            $ThisTask['Input_FirstToDo'] = '';
            $ThisTask['AddTaskNo'] = '';
            $ThisTask['AddCatLink'] = '';

            if($ShowLog === true)
            {
                $ThisTask['TaskDone'] = true;
            }
            else
            {
                $ThisTask['TaskDone'] = Tasks_IsDone($TaskID, $_User);
            }

            $ThisTask['ID'] = $TaskID;
            $ThisTask['Colspan'] = $_Lang['Input_TabFullLen'];
            $ThisTask['Name'] = $_Lang['Tasks'][$TaskID]['name'];
            $ThisTask['Description'] = $_Lang['Tasks'][$TaskID]['desc'];
            $ThisTask['No'] = $TaskLoop;
            $ThisTask['Lang_Task'] = $_Lang['Tab01_CatSel_Task'];
            $ThisTask['Lang_JobsToDo'] = $_Lang['Tab01_CatSel_JobsToDo'];

            $TaskData = Tasks_GetTaskData($SelectCat, $TaskID);

            if (
                !empty($TaskData['reward']) &&
                count($TaskData['reward']) > 0
            ) {
                $ThisTask['Lang_Reward'] = $_Lang['Tab01_CatSel_Reward'];

                $rewards = Tasks_GenerateRewardsStrings($TaskData['reward'], $_Lang);

                foreach ($rewards as &$rewardString) {
                    $rewardString = ucfirst($rewardString);
                }

                $ThisTask['Input_Rewards'] = implode(', ', $rewards);
            } else {
                $ThisTask['HideRewards'] = 'hide';
            }

            $ThisTask['Image'] = Tasks_GetTaskImagePath($SelectCat, $TaskID);

            if(!empty($_Lang['TasksJobs'][$TaskID]))
            {
                foreach($_Lang['TasksJobs'][$TaskID] as $JobID => $Job)
                {
                    $StatusIndicator = '';
                    if(!empty($Job))
                    {
                        $ThisJob_LinkClass = '';
                        if($ThisTask['TaskDone'] === true OR (!empty($_User['tasks_done_parsed']['jobs'][$SelectCat][$TaskID]) AND in_array($JobID, $_User['tasks_done_parsed']['jobs'][$SelectCat][$TaskID])))
                        {
                            $ThisJob_LinkClass = 'lime';
                            if(strstr($Job, '{AddClass}') !== false)
                            {
                                $Job = str_replace('{AddClass}', 'class="lime"', $Job);
                            }
                            $Job = "<span class=\"lime\">{$Job}</span>";
                        }
                        else
                        {
                            if(strstr($Job, '{AddClass}') !== false)
                            {
                                $Job = str_replace('{AddClass}', '', $Job);
                            }

                            if(isset($_User['tasks_done_parsed']['status'][$SelectCat][$TaskID][$JobID]) && $_User['tasks_done_parsed']['status'][$SelectCat][$TaskID][$JobID] > 0)
                            {
                                $CurrentStatus = prettyNumber($_User['tasks_done_parsed']['status'][$SelectCat][$TaskID][$JobID]);
                                $TotalCount = prettyNumber($_Vars_TasksData[$SelectCat]['tasks'][$TaskID]['jobs'][$JobID][$_Vars_TasksData[$SelectCat]['tasks'][$TaskID]['jobs'][$JobID]['statusField']]);
                                $StatusIndicator = " <b class=\"lime\">[{$CurrentStatus}/{$TotalCount}]</b>";
                            }
                        }

                        if(stristr($Job, '[tech') !== false)
                        {
                            $Job = preg_replace('#\[tech=([0-9]+)\](.*?)\[/tech\]#si', '<a href="techtree.php#el$1" target="_blank" '.(!empty($ThisJob_LinkClass) ? 'class="'.$ThisJob_LinkClass.'"' : '').'>$2</a>', $Job);
                        }

                        $ThisTask['Input_JobsToDo'] .= "&#149; {$Job}{$StatusIndicator}<br/>";
                    }
                }
            }

            if($ThisTask['TaskDone'])
            {
                $ThisTabColor = 'lime';
                $TitleStatus = $_Lang['Tab01_CatSel_TaskDone'];
                $ThisTask['HideFirstToDo'] = 'hide';
            }
            else
            {
                if(Tasks_CheckAvailable($TaskID, $_User, $SelectCat))
                {
                    if(empty($SetActiveTask))
                    {
                        $SetActiveTask = $TaskID;
                    }
                    $ThisTabColor = 'orange';
                    $TitleStatus = $_Lang['Tab01_CatSel_TaskNotDone'];
                    $ThisTask['HideFirstToDo'] = 'hide';
                }
                else
                {
                    $ThisTask['Lang_FirstToDo'] = $_Lang['Tab01_CatSel_FirstToDo'];
                    $ThisTabColor = 'red';
                    $TitleStatus = $_Lang['Tab01_CatSel_TaskLocked'];
                    foreach($_Vars_TasksData[$SelectCat]['tasks'][$TaskID]['requirements'] as $ReqData)
                    {
                        if($ReqData['type'] == 'CATEGORY')
                        {
                            if(!Tasks_IsCatDone($ReqData['e'], $_User))
                            {
                                if(Tasks_CheckCatAvailable($ReqData['elementID'], $_User))
                                {
                                    $ThisTask['CreateTaskCatName'] = "<a href=\"?cat={$ReqData['elementID']}\" class=\"orange\">\"{$_Lang['TasksCats'][$ReqData['elementID']]}\"</a>";
                                }
                                else
                                {
                                    $ThisTask['CreateTaskCatName'] = "<a href=\"?cat={$ReqData['elementID']}\" class=\"red\">\"{$_Lang['TasksCats'][$ReqData['elementID']]}\"</a>";
                                }
                                $ThisTask['Input_FirstToDo'] .= "&#149; {$_Lang['Tab01_CatSel_DoCategory']} {$ThisTask['CreateTaskCatName']}<br/>";
                            }
                        }
                        else if($ReqData['type'] == 'TASK')
                        {
                            if(!Tasks_IsDone($ReqData['elementID'], $_User))
                            {
                                if(!empty($_Vars_TasksData[$SelectCat]['tasks'][$ReqData['elementID']]))
                                {
                                    $ThisTask['AddTaskNo'] = " {$TaskNOs[$ReqData['elementID']]}";
                                    $ThisTask['CreateTaskName'] = "<a href=\"#\" id=\"Link_TaskTab_{$ReqData['elementID']}\">\"{$_Lang['Tasks'][$ReqData['elementID']]['name']}\"</a>";
                                }
                                else
                                {
                                    $ReqData['categoryID'] = Tasks_GetTaskCatID($ReqData['elementID']);
                                    $ThisTask['AddCatLink'] = " (<a href=\"?cat={$ReqData['categoryID']}\">{$_Lang['TasksCats'][$ReqData['categoryID']]}</a>)";
                                    $ThisTask['CreateTaskName'] = "\"{$_Lang['Tasks'][$ReqData['elementID']]['name']}\"";
                                }
                                $ThisTask['Input_FirstToDo'] .= "&#149; {$_Lang['Tab01_CatSel_DoTask']}{$ThisTask['AddTaskNo']} {$ThisTask['CreateTaskName']}{$ThisTask['AddCatLink']}<br/>";
                            }
                        }
                    }
                }
            }
            $ThisTask['Color'] = $ThisTabColor;
            $ThisTask['TitleStatus'] = $TitleStatus;

            $_Lang['Insert_TasksList'][$TabRow][] = parsetemplate($TaskTabTPL, array
            (
                'ID' => $TaskID,
                'Color' => $ThisTabColor,
                'No' => $TaskLoop,
                'Title' => $_Lang['Tasks'][$TaskID]['name'],
                'TitleStatus' => $TitleStatus,
                'Lang_Task' => $_Lang['Tab01_CatSel_Task']
            ));

            if(($TaskLoop % $TabsPerRow) == 0)
            {
                $TabRow += 1;
            }

            if(empty($ThisTask['Description']))
            {
                $ThisTask['Template'] = $TaskBoxTPL_NoDesc;
            }
            else
            {
                $ThisTask['Template'] = $TaskBoxTPL_Desc;
            }
            $_Lang['Input_CreateTaskRows'] .= parsetemplate($ThisTask['Template'], $ThisTask);
            $TaskLoop += 1;
        }

        $_Lang['Input_CreateTasksList_FurtherRows'] = '';
        foreach($_Lang['Insert_TasksList'] as $RowIndex => $RowTabs)
        {
            if($RowIndex == 1)
            {
                $_Lang['Input_CreateTasksList_FirstRow'] = implode('', $RowTabs);
            }
            else
            {
                $CountRowTabs = count($RowTabs);
                if($CountRowTabs == $TabsPerRow)
                {
                    $_Lang['Input_CreateTasksList_FurtherRows'] .= '<tr>'.implode('', $RowTabs).'</tr>';
                }
                else
                {
                    $_Lang['Input_CreateTasksList_FurtherRows'] .= '<tr>'.implode('', $RowTabs).'<th colspan="'.($TabsPerRow - $CountRowTabs).'">&nbsp;</th></tr>';
                }
            }
        }
        $_Lang['Insert_TaskListRowspan'] = count($_Lang['Insert_TasksList']);

        if(isset($SetActiveTask) && $SetActiveTask > $_Lang['SetActiveTask'])
        {
            $_Lang['SetActiveTask'] = $SetActiveTask;
        }
        if(isset($_GET['showtask']) && $_GET['showtask'] > 0)
        {
            $_GET['showtask'] = intval($_GET['showtask']);
            if(!empty($_Vars_TasksData[$SelectCat]['tasks'][$_GET['showtask']]))
            {
                $_Lang['SetActiveTask'] = $_GET['showtask'];
            }
        }
    }
    else
    {
        // User shows CatList
        if($ShowLog === false)
        {
            $PageTPL = gettemplate('tasks_list_showcats_list_active');
        }
        else
        {
            $PageTPL = gettemplate('tasks_list_showcats_list_done');
        }
        $TaskCatTPL = gettemplate('tasks_list_showcats_list_cattab');

        $_Lang['Input_CreateTaskCatsList'] = '';
        foreach($_Vars_TasksData as $TaskCat => $TaskCatData)
        {
            $TaskCatData['DoneCount'] = '0';
            if($ShowLog === false)
            {
                if(Tasks_IsCatDone($TaskCat, $_User))
                {
                    continue;
                }
                if(!Tasks_CheckCatAvailable($TaskCat, $_User))
                {
                    $TaskCatData['CatNotAvailable'] = 'red';
                }
                foreach($TaskCatData['tasks'] as $TaskID => $TaskData)
                {
                    if(Tasks_IsDone($TaskID, $_User))
                    {
                        $TaskCatData['DoneCount'] += 1;
                    }
                }
            }
            else
            {
                if(!Tasks_IsCatDone($TaskCat, $_User))
                {
                    continue;
                }
                $TaskCatData['DoneCount'] = count($TaskCatData['tasks']);
            }

            $TaskCatData['CatID'] = $TaskCat;
            $TaskCatData['Name'] = $_Lang['TasksCats'][$TaskCat];
            $TaskCatData['Lang_Done'] = $_Lang['Tab01_CatList_Done'];
            $TaskCatData['TotalCount'] = count($TaskCatData['tasks']);

            $_Lang['Input_CreateTaskCatsList'] .= parsetemplate($TaskCatTPL, $TaskCatData);
        }
        if(empty($_Lang['Input_CreateTaskCatsList']))
        {
            if(empty($_Vars_TasksData))
            {
                $_Lang['Input_CreateTaskCatsList'] = "<tr><th class=\"red pad5\">{$_Lang['Tab01_CatList_NoCats']}</th></tr>";
            }
            else
            {
                if($ShowLog === false)
                {
                    $_Lang['Input_CreateTaskCatsList'] = "<tr><th class=\"red pad5\">{$_Lang['Tab01_CatList_AllCatsMade']}</th></tr>";
                }
                else
                {
                    $_Lang['Input_CreateTaskCatsList'] = "<tr><th class=\"red pad5\">{$_Lang['Tab01_CatList_NoCatsMade']}</th></tr>";
                }
            }
        }
    }
}

if($ShowMsgBox === true)
{
    $_Lang['MsgBox_Hide'] = '';
    if(empty($_Lang['MsgBox_Colo']))
    {
        $_Lang['MsgBox_Colo'] = 'red';
    }
}

if(!empty($PageTPL))
{
    $BodyTPL = str_replace('{PageBody}', $PageTPL, $BodyTPL);
}
$Page = parsetemplate($BodyTPL, $_Lang);
display($Page, $_Lang['PageTitle']);

?>
