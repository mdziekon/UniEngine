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

includeLang('admin/users_techmanager');
$TPL_Body = gettemplate('admin/users_techmanager_body');
$TPL_Row = gettemplate('admin/users_techmanager_row');
$Items_Technologies = &$_Vars_ElementCategories['tech'];

$_ResLimit = 10000000000;
$Allowed_CMD = array('add', 'set', 'sub');
$Allowed_Tabs = array
(
    1 => &$Items_Technologies,
);

if(isset($_POST['sent']) && $_POST['sent'] == 1)
{
    $UserID = round($_POST['userID']);
    $TabID = intval($_POST['tab']);
    $ThisCMD = (isset($_POST['cmd']) ? $_POST['cmd'] : null);

    $_Lang['Insert_PreviousUserID'] = $UserID;
    $_Lang['Insert_SelectCMD_'.$ThisCMD] = 'checked';
    $_Lang['Insert_DefaultTab'] = $TabID - 1;

    if($UserID > 0)
    {
        if(in_array($ThisCMD, $Allowed_CMD))
        {
            if(in_array($TabID, array_keys($Allowed_Tabs)))
            {
                if(!empty($_POST['res'][$TabID]))
                {
                    $Query_GetUser = '';
                    $Query_GetUser .= "SELECT `id`, `username` ";
                    $Query_GetUser .= "FROM {{table}} WHERE `id` = {$UserID} LIMIT 1; -- admin/user_techmanager.php - Query #1";
                    $Result_GetUser = doquery($Query_GetUser, 'users', true);
                    if($Result_GetUser['id'] == $UserID)
                    {
                        foreach($_POST['res'][$TabID] as $ThisID => $ThisValue)
                        {
                            if(in_array($ThisID, $Allowed_Tabs[$TabID]))
                            {
                                if($ThisCMD == 'set' AND empty($ThisValue))
                                {
                                    continue;
                                }
                                $ThisValue = round(str_replace('.', '', $ThisValue));
                                if(($ThisCMD == 'set' AND $ThisValue >= 0) OR ($ThisCMD != 'set' AND $ThisValue > 0))
                                {
                                    if($ThisValue > $_ResLimit)
                                    {
                                        $ThisValue = $_ResLimit;
                                    }
                                    $Query_Update_Data[$ThisID] = $ThisValue;
                                }
                            }
                        }

                        if(!empty($Query_Update_Data))
                        {
                            if($ThisCMD == 'add')
                            {
                                $Query_Update = '';
                                $Query_Update .= "UPDATE {{table}} SET ";
                                foreach($Query_Update_Data as $ThisID => $ThisValue)
                                {
                                    $Query_Update_Array[] = "`{$_Vars_GameElements[$ThisID]}` = `{$_Vars_GameElements[$ThisID]}` + {$ThisValue}";
                                }
                                $Query_Update .= implode(',', $Query_Update_Array);
                                $Query_Update .= " WHERE `id` = {$UserID} LIMIT 1;";
                            }
                            else if($ThisCMD == 'set')
                            {
                                $Query_Update = '';
                                $Query_Update .= "UPDATE {{table}} SET ";
                                foreach($Query_Update_Data as $ThisID => $ThisValue)
                                {
                                    $Query_Update_Array[] = "`{$_Vars_GameElements[$ThisID]}` = {$ThisValue}";
                                }
                                $Query_Update .= implode(',', $Query_Update_Array);
                                $Query_Update .= " WHERE `id` = {$UserID} LIMIT 1;";
                            }
                            else if($ThisCMD == 'sub')
                            {
                                foreach($Query_Update_Data as $ThisID => $ThisValue)
                                {
                                    $Query_Update_Fields[] = "`{$_Vars_GameElements[$ThisID]}`";
                                    $Query_Update_Values[] = $ThisValue;
                                    $Query_Update_Set[] = "`{$_Vars_GameElements[$ThisID]}` = IF(VALUES(`{$_Vars_GameElements[$ThisID]}`) > `{$_Vars_GameElements[$ThisID]}`, 0, `{$_Vars_GameElements[$ThisID]}` - VALUES(`{$_Vars_GameElements[$ThisID]}`))";
                                }
                                $Query_Update = '';
                                $Query_Update .= "INSERT INTO {{table}} (`id`, ".implode(', ', $Query_Update_Fields).") ";
                                $Query_Update .= "VALUES ({$UserID}, ".implode(', ', $Query_Update_Values).") ";
                                $Query_Update .= "ON DUPLICATE KEY UPDATE ";
                                $Query_Update .= implode(',', $Query_Update_Set);
                            }
                            doquery($Query_Update, 'users');
                            if(getDBLink()->affected_rows > 0)
                            {
                                $_MsgBox = array
                                (
                                    'color' => 'lime',
                                    'text' => sprintf
                                    (
                                        $_Lang['MsgBox_UpdateOK'],
                                        $Result_GetUser['id'], $Result_GetUser['username'], $Result_GetUser['id'],
                                        prettyNumber(count($Query_Update_Data))
                                    )
                                );
                            }
                            else
                            {
                                $_MsgBox = array
                                (
                                    'color' => 'orange',
                                    'text' => sprintf
                                    (
                                        $_Lang['MsgBox_UpdateNotAffected'],
                                        $Result_GetUser['id'], $Result_GetUser['username'], $Result_GetUser['id']
                                    )
                                );
                            }
                        }
                        else
                        {
                            $_MsgBox = array('color' => 'red', 'text' => $_Lang['MsgBox_BadInput']);
                        }
                    }
                    else
                    {
                        $_MsgBox = array('color' => 'red', 'text' => $_Lang['MsgBox_PlanetDoesntExist']);
                    }
                }
                else
                {
                    $_MsgBox = array('color' => 'red', 'text' => $_Lang['MsgBox_EmptyInput']);
                }
            }
            else
            {
                $_MsgBox = array('color' => 'red', 'text' => $_Lang['MsgBox_BadTabID']);
            }
        }
        else
        {
            $_MsgBox = array('color' => 'red', 'text' => $_Lang['MsgBox_BadCMD']);
        }
    }
    else
    {
        $_MsgBox = array('color' => 'red', 'text' => $_Lang['MsgBox_BadUserID']);
    }
}

$_Lang['Insert_Rows_Technologies'] = '';
foreach($Items_Technologies as $ItemID)
{
    $_Lang['Insert_Rows_Technologies'] .= parsetemplate($TPL_Row, array
    (
        'Name' => "{$_Lang['tech'][$ItemID]} [#{$ItemID}]",
        'TabNo' => 1,
        'ResID' => $ItemID
    ));
}

if(!empty($_MsgBox))
{
    $_Lang['MsgBox_Text'] = $_MsgBox['text'];
    $_Lang['MsgBox_Color'] = $_MsgBox['color'];
}
else
{
    $_Lang['MsgBox_Text'] = '&nbsp;';
}

display(parsetemplate($TPL_Body, $_Lang), $_Lang['PageTitle'], false, true);

?>
