<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(!CheckAuth('supportadmin'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

includeLang('admin/SFBSettings');
$_Lang['Insert_ChronoApplets'] = '';
$Now = time();
$TPL_List_NoElements = gettemplate('admin/SFBSettings_list_noelements');
$TPL_List_Headers = gettemplate('admin/SFBSettings_list_headers');
$TPL_List_Row = gettemplate('admin/SFBSettings_list_row');

$Parsed_List_Headers = parsetemplate($TPL_List_Headers, $_Lang);

$_Checked = 'checked';
$_Selected = 'selected';
$_Disabled = 'disabled';
$_AllowedTypes = array(1, 2, 3);
$_BlockTypesTables = array(2 => 'users', 3 => 'planets');
$_NeedElementIDTypes = array(2, 3);

if(empty($_GET['cmd']))
{
    $_CMD = 1;
}
else
{
    if($_GET['cmd'] == 'add')
    {
        $_CMD = 2;
        if(isset($_POST['action']) && $_POST['action'] == 'save')
        {
            $_FormData_Type = intval($_POST['type']);
            $_FormData_StartTime = strtotime($_POST['startTime_date']);
            $_FormData_EndTime = strtotime($_POST['endTime_date']);
            $_FormData_PostEndTime = strtotime($_POST['postEndTime_date']);
            $_FormData_ElementID = round($_POST['elementID']);
            $_FormData_DontBlockIfIdle = (isset($_POST['dontBlockIfIdle']) && $_POST['dontBlockIfIdle'] == 'on' ? 1 : 0);
            $_FormData_Reason = $_POST['reason'];
            if(!empty($_POST['mission']) AND (array)$_POST['mission'] === $_POST['mission'])
            {
                foreach($_POST['mission'] as $MissionID => $Data)
                {
                    $MissionID = intval($MissionID);
                    if($Data == 'on' AND in_array($MissionID, $_Vars_FleetMissions['all']))
                    {
                        $_FormData_Missions[] = $MissionID;
                    }
                }
            }
            if($_FormData_StartTime <= $Now)
            {
                $_FormData_StartTime = $Now;
            }
            if($_FormData_PostEndTime <= $_FormData_EndTime)
            {
                $_FormData_PostEndTime = 0;
            }

            if($_FormData_StartTime <= $_FormData_EndTime)
            {
                if(in_array($_FormData_Type, $_AllowedTypes))
                {
                    if($_FormData_Type == 1)
                    {
                        $_FormData_ElementID = 0;
                    }
                    elseif($_FormData_Type == 2 OR $_FormData_Type == 3)
                    {
                        $_FormData_DontBlockIfIdle = 0;
                        $_FormData_PostEndTime = 0;
                    }

                    if(!empty($_FormData_Missions))
                    {
                        if($_FormData_EndTime > $Now)
                        {
                            $AllowProceed = false;
                            if(in_array($_FormData_Type, $_NeedElementIDTypes))
                            {
                                if($_FormData_ElementID > 0)
                                {
                                    $CheckElementID = doquery("SELECT `ID` FROM {{table}} WHERE `id` = {$_FormData_ElementID} LIMIT 1;", $_BlockTypesTables[$_FormData_Type], true);
                                    if($CheckElementID['ID'] == $_FormData_ElementID)
                                    {
                                        $AllowProceed = true;
                                    }
                                    else
                                    {
                                        $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_AddElementIDNoExists'];
                                        $_Lang['Insert_MsgBoxColor'] = 'red';
                                    }
                                }
                                else
                                {
                                    $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_AddBadElementID'];
                                    $_Lang['Insert_MsgBoxColor'] = 'red';
                                }
                            }
                            else
                            {
                                $AllowProceed = true;
                            }

                            if($AllowProceed === true)
                            {
                                $_FormData_Reason = getDBLink()->escape_string($_FormData_Reason);
                                if(count($_FormData_Missions) == count($_Vars_FleetMissions['all']))
                                {
                                    $_FormData_Missions = array('0');
                                }
                                $_FormData_Missions = implode(',', $_FormData_Missions);

                                $InsertSFB = "INSERT INTO {{table}} SET ";
                                $InsertSFB .= "`AdminID` = '{$_User['id']}', ";
                                $InsertSFB .= "`Type` = {$_FormData_Type},";
                                $InsertSFB .= "`BlockMissions` = '{$_FormData_Missions}',";
                                $InsertSFB .= "`Reason` = '{$_FormData_Reason}',";
                                $InsertSFB .= "`StartTime` = '{$_FormData_StartTime}',";
                                $InsertSFB .= "`EndTime` = '{$_FormData_EndTime}',";
                                $InsertSFB .= "`PostEndTime` = '{$_FormData_PostEndTime}',";
                                $InsertSFB .= "`ElementID` = '{$_FormData_ElementID}',";
                                $InsertSFB .= "`DontBlockIfIdle` = '{$_FormData_DontBlockIfIdle}';";
                                doquery($InsertSFB, 'smart_fleet_blockade');

                                header('Location: ?msg=addok');
                                safeDie();
                            }
                        }
                        else
                        {
                            $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_AddEndTimeBad'];
                            $_Lang['Insert_MsgBoxColor'] = 'red';
                        }
                    }
                    else
                    {
                        $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_AddNoMissions'];
                        $_Lang['Insert_MsgBoxColor'] = 'red';
                    }
                }
                else
                {
                    $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_AddBadType'];
                    $_Lang['Insert_MsgBoxColor'] = 'red';
                }
            }
            else
            {
                $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_AddBadStart'];
                $_Lang['Insert_MsgBoxColor'] = 'red';
            }
        }
    }
    else if($_GET['cmd'] == 'edit')
    {
        $_CMD = 3;
        $RowID = isset($_GET['id']) ? round($_GET['id']) : 0;
        if($RowID > 0)
        {
            $GetEditRow = doquery("SELECT * FROM {{table}} WHERE `ID` = {$RowID} LIMIT 1;", 'smart_fleet_blockade', true);
            if($GetEditRow['ID'] == $RowID)
            {
                if(($GetEditRow['EndTime'] > $Now) OR ($GetEditRow['PostEndTime'] > $Now AND $GetEditRow['Type'] == 1))
                {
                    $_FormData_Type = $GetEditRow['Type'];
                    $_FormData_StartTime = $GetEditRow['StartTime'];
                    $_FormData_EndTime = $GetEditRow['EndTime'];
                    $_FormData_PostEndTime = $GetEditRow['PostEndTime'];
                    $_FormData_ElementID = $GetEditRow['ElementID'];
                    $_FormData_DontBlockIfIdle = ($GetEditRow['DontBlockIfIdle'] == 1 ? 1 : 0);
                    $_FormData_Reason = $GetEditRow['Reason'];
                    if($GetEditRow['BlockMissions'] == '0')
                    {
                        $_FormData_Missions = $_Vars_FleetMissions['all'];
                    }
                    else
                    {
                        $_FormData_Missions = explode(',', $GetEditRow['BlockMissions']);
                    }
                    if($_FormData_StartTime > $Now)
                    {
                        $_Lang['Insert_Current_StartTime'] = date('(Y-m-d H:i:s)', $_FormData_StartTime);
                    }
                    else
                    {
                        $_Lang['Insert_Disable_StartTime'] = $_Disabled;
                    }
                    $_Lang['Insert_Current_EndTime'] = date('(Y-m-d H:i:s)', $_FormData_EndTime);
                    if($_FormData_PostEndTime > 0)
                    {
                        $_Lang['Insert_Current_PostEndTime'] = date('(Y-m-d H:i:s)', $_FormData_PostEndTime);
                    }
                    else
                    {
                        $HidePostEndTime = true;
                    }
                    if($_FormData_Type != 1)
                    {
                        $_Lang['Insert_Disable_PostEndTime'] = $_Disabled;
                    }
                    else
                    {
                        if($_FormData_EndTime <= $Now)
                        {
                            $_Lang['Insert_Disable_EndTime'] = $_Disabled;
                        }
                    }

                    if(isset($_POST['action']) && $_POST['action'] == 'save')
                    {
                        $_FormData_StartTime = isset($_POST['startTime_date']) ? strtotime($_POST['startTime_date']) : 0;
                        $_FormData_EndTime = strtotime($_POST['endTime_date']);
                        $_FormData_PostEndTime = strtotime($_POST['postEndTime_date']);
                        $_FormData_DontBlockIfIdle = (isset($_POST['dontBlockIfIdle']) && $_POST['dontBlockIfIdle'] == 'on' ? 1 : 0);
                        $_FormData_Reason = $_POST['reason'];
                        $_FormData_Missions = array();
                        if(!empty($_POST['mission']) AND (array)$_POST['mission'] === $_POST['mission'])
                        {
                            foreach($_POST['mission'] as $MissionID => $Data)
                            {
                                $MissionID = intval($MissionID);
                                if($Data == 'on' AND in_array($MissionID, $_Vars_FleetMissions['all']))
                                {
                                    $_FormData_Missions[] = $MissionID;
                                }
                            }
                        }
                        if($GetEditRow['StartTime'] <= $Now)
                        {
                            $_FormData_StartTime = $GetEditRow['StartTime'];
                        }
                        else
                        {
                            if($_FormData_StartTime <= $Now)
                            {
                                $_FormData_StartTime = $Now;
                            }
                        }
                        if($_FormData_PostEndTime <= $_FormData_EndTime)
                        {
                            $_FormData_PostEndTime = 0;
                        }

                        if($_FormData_Type == 2 OR $_FormData_Type == 3)
                        {
                            $_FormData_DontBlockIfIdle = 0;
                            $_FormData_PostEndTime = 0;
                        }
                        else
                        {
                            if($GetEditRow['EndTime'] <= $Now)
                            {
                                $_FormData_EndTime = $GetEditRow['EndTime'];
                            }
                        }

                        if(!empty($_FormData_Missions))
                        {
                            if($GetEditRow['EndTime'] <= $_FormData_EndTime)
                            {
                                if(!($_FormData_Type == 1 AND $GetEditRow['EndTime'] <= $Now AND $_FormData_PostEndTime < $GetEditRow['PostEndTime']))
                                {
                                    $_FormData_Reason = getDBLink()->escape_string($_FormData_Reason);
                                    if(count($_FormData_Missions) == count($_Vars_FleetMissions['all']))
                                    {
                                        $_FormData_Missions = array('0');
                                    }
                                    $_FormData_Missions = implode(',', $_FormData_Missions);

                                    $UpdateSFB = "UPDATE {{table}} SET ";
                                    $UpdateSFB .= "`BlockMissions` = '{$_FormData_Missions}',";
                                    $UpdateSFB .= "`Reason` = '{$_FormData_Reason}',";
                                    $UpdateSFB .= "`StartTime` = '{$_FormData_StartTime}', ";
                                    $UpdateSFB .= "`EndTime` = '{$_FormData_EndTime}',";
                                    $UpdateSFB .= "`PostEndTime` = '{$_FormData_PostEndTime}',";
                                    $UpdateSFB .= "`DontBlockIfIdle` = '{$_FormData_DontBlockIfIdle}' ";
                                    $UpdateSFB .= "WHERE `ID` = {$RowID};";
                                    doquery($UpdateSFB, 'smart_fleet_blockade');

                                    header('Location: ?msg=editok');
                                    safeDie();
                                }
                                else
                                {
                                    $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_EditPostEndTimeBad'];
                                    $_Lang['Insert_MsgBoxColor'] = 'red';
                                }
                            }
                            else
                            {
                                $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_EditEndTimeBad'];
                                $_Lang['Insert_MsgBoxColor'] = 'red';
                            }
                        }
                        else
                        {
                            $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_EditNoMissions'];
                            $_Lang['Insert_MsgBoxColor'] = 'red';
                        }
                    }
                }
                else
                {
                    header('Location: ?msg=editold');
                    safeDie();
                }
            }
            else
            {
                header('Location: ?msg=editnoid');
                safeDie();
            }
        }
        else
        {
            header('Location: ?msg=editbadid');
            safeDie();
        }
    }
    else if($_GET['cmd'] == 'cancel')
    {
        $_CMD = 1; // Use Overview & show there all messages from system
        $CancelID = round($_GET['id']);
        if($CancelID > 0)
        {
            $GetSFBRow = doquery("SELECT * FROM {{table}} WHERE `ID` = {$CancelID} LIMIT 1;", 'smart_fleet_blockade', true);
            if($GetSFBRow['ID'] == $CancelID)
            {
                if($GetSFBRow['EndTime'] > $Now OR $GetSFBRow['PostEndTime'] > $Now)
                {
                    if($GetSFBRow['EndTime'] > $Now)
                    {
                        $UpdateQueryArray[] = "`EndTime` = UNIX_TIMESTAMP()";
                    }
                    if($GetSFBRow['PostEndTime'] > $Now)
                    {
                        $UpdateQueryArray[] = "`PostEndTime` = UNIX_TIMESTAMP()";
                    }
                    $UpdateQuery = "UPDATE {{table}} SET ".implode(', ', $UpdateQueryArray)." WHERE `ID` = {$CancelID};";
                    doquery($UpdateQuery, 'smart_fleet_blockade');

                    $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_CancelOK'];
                    $_Lang['Insert_MsgBoxColor'] = 'lime';
                }
                else
                {
                    $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_CancelInactive'];
                    $_Lang['Insert_MsgBoxColor'] = 'orange';
                }
            }
            else
            {
                $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_CancelNoExist'];
                $_Lang['Insert_MsgBoxColor'] = 'red';
            }
        }
        else
        {
            $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_CancelBadID'];
            $_Lang['Insert_MsgBoxColor'] = 'red';
        }
    }
}

if($_CMD == 1)
{
    // Show Overview (Active Blockades & Short Log)
    $TPL = gettemplate('admin/SFBSettings_body_overview');

    $SelectActiveSFB = "SELECT * FROM {{table}} WHERE `EndTime` > UNIX_TIMESTAMP() OR `PostEndTime` > UNIX_TIMESTAMP() ORDER BY `EndTime` ASC;";
    $SelectInActiveSFB = "SELECT * FROM {{table}} WHERE `EndTime` <= UNIX_TIMESTAMP() AND (`PostEndTime` = 0 OR (`PostEndTime` <= UNIX_TIMESTAMP() AND `PostEndTime` > 0)) ORDER BY `EndTime` DESC LIMIT 10;";

    $ActiveSFB = doquery($SelectActiveSFB, 'smart_fleet_blockade');
    $InActiveSFB = doquery($SelectInActiveSFB, 'smart_fleet_blockade');

    $CombineData = array
    (
        array('Result' => $ActiveSFB, 'Prefix' => 'ActiveList', 'RowType' => 1, 'NoRowsText' => $_Lang['SFB_ActiveList_NoElements']),
        array('Result' => $InActiveSFB, 'Prefix' => 'InActiveList', 'RowType' => 2, 'NoRowsText' => $_Lang['SFB_InActiveList_NoElements']),
    );
}
else if($_CMD == 2 OR $_CMD == 3)
{
    // Show Adding or Editing Form
    $TPL = gettemplate('admin/SFBSettings_body_manage');
    $TPL_Manage_MissionSelector = gettemplate('admin/SFBSettings_body_manage_missionselector');

    $_Lang['Insert_MissionSelectors'] = '';
    foreach($_Vars_FleetMissions['all'] as $MissionID)
    {
        $MissionSelector = array();
        $MissionSelector['MissionID'] = $MissionID;
        if(in_array($MissionID, $_Vars_FleetMissions['civil']))
        {
            $MissionSelector['MissionClass'] = 'civil';
        }
        else
        {
            $MissionSelector['MissionClass'] = 'military';
        }
        if(!empty($_FormData_Missions) AND in_array($MissionID, $_FormData_Missions))
        {
            $MissionSelector['IsChecked'] = $_Checked;
        }
        $MissionSelector['MissionName'] = $_Lang['SFB_Mission__'.$MissionID];

        $_Lang['Insert_MissionSelectors'] .= parsetemplate($TPL_Manage_MissionSelector, $MissionSelector);
    }

    if(isset($_FormData_Type) && in_array($_FormData_Type, $_AllowedTypes))
    {
        $_Lang['Insert_Form_Select_Type_'.$_FormData_Type] = $_Selected;
    }
    if(empty($_FormData_StartTime))
    {
        $_Lang['Insert_startTime_date'] = date('Y-m-d H:i:s', $Now - ($Now % 60));
    }
    else
    {
        $_Lang['Insert_startTime_date'] = date('Y-m-d H:i:s', $_FormData_StartTime);
    }
    if(empty($_FormData_EndTime))
    {
        $_Lang['Insert_endTime_date'] = date('Y-m-d H:i:s', ($Now + TIME_DAY - ($Now % 60)));
    }
    else
    {
        $_Lang['Insert_endTime_date'] = date('Y-m-d H:i:s', $_FormData_EndTime);
    }
    if(!isset($HidePostEndTime))
    {
        if(empty($_FormData_PostEndTime))
        {
            $_Lang['Insert_postEndTime_date'] = date('Y-m-d H:i:s', ($Now + (2 * TIME_DAY) - ($Now % 60)));
        }
        else
        {
            $_Lang['Insert_postEndTime_date'] = date('Y-m-d H:i:s', $_FormData_PostEndTime);
        }
    }
    if(!empty($_FormData_ElementID))
    {
        $_Lang['Insert_Form_ElementID'] = $_FormData_ElementID;
    }
    if(isset($_FormData_DontBlockIfIdle) && $_FormData_DontBlockIfIdle == 1)
    {
        $_Lang['Insert_Form_Select_DontBlockIfIdle'] = $_Checked;
    }
    if(!empty($_FormData_Reason))
    {
        $_Lang['Insert_Form_Reason'] = stripslashes($_FormData_Reason);
    }

    if($_CMD == 2)
    {
        $_Lang['Insert_SubmitButton'] = $_Lang['SFB_Labels_SubmitAdd'];
    }
    else
    {
        $_Lang['Insert_SubmitButton'] = $_Lang['SFB_Labels_SubmitEdit'];
        $_Lang['Insert_Disable_TypeSelect'] = $_Disabled;
        $_Lang['Insert_Disable_ElementID'] = $_Disabled;
    }
}

if(empty($_Lang['Insert_MsgBoxText']))
{
    if(isset($_GET['msg']) && $_GET['msg'] == 'addok')
    {
        $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_AddOK'];
        $_Lang['Insert_MsgBoxColor'] = 'lime';
    }
    else if(isset($_GET['msg']) && $_GET['msg'] == 'editok')
    {
        $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_EditOK'];
        $_Lang['Insert_MsgBoxColor'] = 'lime';
    }
    else if(isset($_GET['msg']) && $_GET['msg'] == 'editold')
    {
        $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_EditOldRow'];
        $_Lang['Insert_MsgBoxColor'] = 'orange';
    }
    else if(isset($_GET['msg']) && $_GET['msg'] == 'editnoid')
    {
        $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_EditRowNoExists'];
        $_Lang['Insert_MsgBoxColor'] = 'red';
    }
    else if(isset($_GET['msg']) && $_GET['msg'] == 'editbadid')
    {
        $_Lang['Insert_MsgBoxText'] = $_Lang['SFB_Response_EditBadID'];
        $_Lang['Insert_MsgBoxColor'] = 'red';
    }
    else
    {
        $_Lang['Insert_HideMsgBox'] = 'inv';
        $_Lang['Insert_MsgBoxText'] = '&nbsp;';
    }
}

if(!empty($CombineData))
{
    foreach($CombineData as $Data)
    {
        if($Data['Result']->num_rows > 0)
        {
            $_Lang['Insert_'.$Data['Prefix']] = $Parsed_List_Headers;
            while($SFBData = $Data['Result']->fetch_assoc())
            {
                $SFBData['RowType'] = $Data['RowType'];
                $SFBData['RowPrefix'] = $Data['Prefix'];
                $SFBRows[] = $SFBData;
                $SQLQueryData_GetUsernames[] = $SFBData['AdminID'];
                if($SFBData['Type'] == 2)
                {
                    $SQLQueryData_GetUsernames[] = $SFBData['ElementID'];
                }
                if($SFBData['Type'] == 3)
                {
                    $SQLQueryData_GetPlanets[] = $SFBData['ElementID'];
                }
            }
        }
        else
        {
            $_Lang['Insert_'.$Data['Prefix']] = parsetemplate($TPL_List_NoElements, array('Text' => $Data['NoRowsText']));
        }
    }

    if(!empty($SFBRows))
    {
        include($_EnginePath.'includes/functions/InsertJavaScriptChronoApplet.php');

        if(!empty($SQLQueryData_GetUsernames))
        {
            $SQLResult_GetUsernames = doquery(
                "SELECT `id`, `username` FROM {{table}} WHERE `id` IN (".implode(', ', $SQLQueryData_GetUsernames).");",
                'users'
            );

            if($SQLResult_GetUsernames->num_rows > 0)
            {
                while($LoadData = $SQLResult_GetUsernames->fetch_assoc())
                {
                    $Usernames[$LoadData['id']] = $LoadData['username'];
                }
            }
        }
        if(!empty($SQLQueryData_GetPlanets))
        {
            $SQLResult_GetPlanets = doquery(
                "SELECT `id`, `name`, `galaxy`, `system`, `planet`, `planet_type` FROM {{table}} WHERE `id` IN (".implode(', ', $SQLQueryData_GetPlanets).");",
                'planets'
            );

            if($SQLResult_GetPlanets->num_rows > 0)
            {
                while($LoadData = $SQLResult_GetPlanets->fetch_assoc())
                {
                    $Planets[$LoadData['id']] = $LoadData;
                }
            }
        }
        if($_GameConfig['enable_bbcode'] == 1)
        {
            include($_EnginePath.'includes/functions/BBcodeFunction.php');
        }

        foreach($SFBRows as $DataRow)
        {
            $DataRow['AdminParsed'] = "<a href=\"user_info.php?uid={$DataRow['AdminID']}\" target=\"_blank\">{$Usernames[$DataRow['AdminID']]} (#{$DataRow['AdminID']})</a>";
            $DataRow['TypeParsed'] = $_Lang['SFB_Types_'.$DataRow['Type']];
            if($DataRow['Type'] == 2)
            {
                $DataRow['ElementParsed'] = (!empty($Usernames[$DataRow['ElementID']]) ? "<a href=\"user_info.php?uid={$DataRow['ElementID']}\" target=\"_blank\">{$Usernames[$DataRow['ElementID']]} (#{$DataRow['ElementID']})</a>" : "<b class=\"red\">{$_Lang['User_Deleted']}</b> (#{$DataRow['ElementID']})");
            }
            else if($DataRow['Type'] == 3)
            {
                $DataRow['ElementParsed'] = (!empty($Planets[$DataRow['ElementID']]) ? "<a href=\"../galaxy.php?mode=3&galaxy={$Planets[$DataRow['ElementID']]['galaxy']}&system={$Planets[$DataRow['ElementID']]['system']}&planet={$Planets[$DataRow['ElementID']]['planet']}\" target=\"_blank\">{$Planets[$DataRow['ElementID']]['name']} (#{$DataRow['ElementID']})<br/>[{$Planets[$DataRow['ElementID']]['galaxy']}:{$Planets[$DataRow['ElementID']]['system']}:{$Planets[$DataRow['ElementID']]['planet']}|".($Planets[$DataRow['ElementID']]['planet_type'] == 1 ? $_Lang['Symbols_Planet'] : $_Lang['Symbols_Moon'])."]</a>" : "<b class=\"red\">".($Planets[$DataRow['ElementID']]['planet_type'] == 1 ? $_Lang['Planet_Deleted'] : $_Lang['Moon_Deleted'])."</b> (#{$DataRow['ElementID']})");
            }
            if($DataRow['BlockMissions'] == '0')
            {
                $DataRow['Missions'] = $_Lang['SFB_Mission_All'];
            }
            else
            {
                $ExplodeMissions = explode(',', $DataRow['BlockMissions']);
                $CivilCount = 0;
                $AggresiveCount = 0;
                foreach($ExplodeMissions as $MissionID)
                {
                    if(!in_array($MissionID, $_Vars_FleetMissions['all']))
                    {
                        continue;
                    }
                    $DataRow['Missions'][] = $_Lang['SFB_Mission__'.$MissionID];
                    if(in_array($MissionID, $_Vars_FleetMissions['civil']))
                    {
                        $CivilCount += 1;
                    }
                    else
                    {
                        $AggresiveCount += 1;
                    }
                }
                if($CivilCount == count($_Vars_FleetMissions['civil']) AND $AggresiveCount == 0)
                {
                    $DataRow['Missions'] = $_Lang['SFB_Mission_Civil'];
                }
                else if($AggresiveCount == count($_Vars_FleetMissions['military']) AND $CivilCount == 0)
                {
                    $DataRow['Missions'] = $_Lang['SFB_Mission_Aggresive'];
                }
                else
                {
                    $DataRow['Missions'] = implode(', ', $DataRow['Missions']);
                }
            }
            if($DataRow['StartTime'] > $Now)
            {
                $_Lang['Insert_ChronoApplets'] .= InsertJavaScriptChronoApplet('Start', $DataRow['ID'], $DataRow['StartTime'], true);
                $DataRow['StartTime'] = prettyDate('d m Y, H:i:s', $DataRow['StartTime'], 1)."<br/>{$_Lang['SFB_EndIn']}: <span class=\"orange\" id=\"bxxStart{$DataRow['ID']}\">".pretty_time($DataRow['StartTime'] - $Now, true, 'D')."</span>";
            }
            else
            {
                $DataRow['StartTime'] = prettyDate('d m Y, H:i:s', $DataRow['StartTime'], 1);
            }
            if($DataRow['EndTime'] > $Now)
            {
                $_Lang['Insert_ChronoApplets'] .= InsertJavaScriptChronoApplet('End', $DataRow['ID'], $DataRow['EndTime'], true);
                $DataRow['EndTime'] = prettyDate('d m Y, H:i:s', $DataRow['EndTime'], 1)."<br/>{$_Lang['SFB_EndIn']}: <span class=\"orange\" id=\"bxxEnd{$DataRow['ID']}\">".pretty_time($DataRow['EndTime'] - $Now, true, 'D')."</span>";
            }
            else
            {
                $DataRow['EndTime'] = prettyDate('d m Y, H:i:s', $DataRow['EndTime'], 1);
            }
            if($DataRow['Type'] == 1 AND $DataRow['PostEndTime'] > 0)
            {
                if($DataRow['PostEndTime'] > $Now)
                {
                    $_Lang['Insert_ChronoApplets'] .= InsertJavaScriptChronoApplet('PostEnd', $DataRow['ID'], $DataRow['PostEndTime'], true);
                    $DataRow['PostEndTimeParse'] = prettyDate('d m Y, H:i:s', $DataRow['PostEndTime'], 1)."<br/>{$_Lang['SFB_EndIn']}: <span class=\"orange\" id=\"bxxPostEnd{$DataRow['ID']}\">".pretty_time($DataRow['PostEndTime'] - $Now, true, 'D')."</span>";
                }
                else
                {
                    $DataRow['PostEndTimeParse'] = prettyDate('d m Y, H:i:s', $DataRow['PostEndTime'], 1);
                }
                $DataRow['PostEndTimeParse'] = "<div style=\"border-top: dashed skyblue 1px;\" class=\"hPostEndTime\">{$DataRow['PostEndTimeParse']}</div>";
            }
            if($DataRow['Type'] == 1 AND $DataRow['DontBlockIfIdle'] == 1)
            {
                $DataRow['AdditionalData'][] = $_Lang['SFB_Additional_DontBlockIfIdle'];
            }
            if(empty($DataRow['AdditionalData']))
            {
                $DataRow['AdditionalData'] = '&nbsp;';
            }
            else
            {
                $DataRow['AdditionalData'] = implode('<br/>', $DataRow['AdditionalData']);
            }

            if(empty($DataRow['Reason']))
            {
                $DataRow['Reason'] = '-';
            }
            else
            {
                if($_GameConfig['enable_bbcode'] == 1){
                    $DataRow['Reason'] = trim(nl2br(bbcode(image(strip_tags(str_replace("'", '&#39;', $DataRow['Reason']), '<br><br/>')))));
                } else {
                    $DataRow['Reason'] = trim(nl2br(strip_tags($DataRow['Reason'], '<br><br/>')));
                }
            }

            if($DataRow['RowType'] == 2)
            {
                $DataRow['ActionsParsed'] = '&nbsp;';
            }
            else
            {
                $DataRow['ActionsParsed'][] = "<a class=\"editLink\" href=\"?cmd=edit&amp;id={$DataRow['ID']}\">{$_Lang['SFB_Action_Edit']}</a>";
                $DataRow['ActionsParsed'][] = "<a class=\"cancelLink\" href=\"?cmd=cancel&amp;id={$DataRow['ID']}\">{$_Lang['SFB_Action_Cancel']}</a>";
                $DataRow['ActionsParsed'] = implode('<br/>', $DataRow['ActionsParsed']);$DataRow['ActionsParsed'];
            }

            $_Lang['Insert_'.$DataRow['RowPrefix']] .= parsetemplate($TPL_List_Row, $DataRow);
        }
    }
}

$_Lang['JS_DatePicker_TranslationLang'] = getJSDatePickerTranslationLang();

$Title = $_Lang['SFB_Title'];
$Page = parsetemplate($TPL, $_Lang);

display($Page, $Title, false, true);

?>
