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

includeLang('admin/planets_resmanager');
$TPL_Body = gettemplate('admin/planets_resmanager_body');
$TPL_Row = gettemplate('admin/planets_resmanager_row');
$Items_Resources = array(1 => 'metal', 2 => 'crystal', 3 => 'deuterium');
$Items_Resources_Keys = array_keys($Items_Resources);
$Items_Buildings = &$_Vars_ElementCategories['build'];
$Items_Fleet = &$_Vars_ElementCategories['fleet'];
$Items_Defense = &$_Vars_ElementCategories['defense'];

$_ResLimit = 1000000000000000000000;
$Allowed_CMD = array('add', 'set', 'sub');
$Allowed_Tabs = array
(
    1 => &$Items_Resources_Keys,
    2 => &$Items_Buildings,
    3 => &$Items_Fleet,
    4 => &$Items_Defense
);

if(isset($_POST['sent']) && $_POST['sent'] == 1)
{
    $PlanetID = round($_POST['planetID']);
    $TabID = intval($_POST['tab']);
    $ThisCMD = (isset($_POST['cmd']) ? $_POST['cmd'] : null);

    $_Lang['Insert_PreviousPlanetID'] = $PlanetID;
    $_Lang['Insert_SelectCMD_'.$ThisCMD] = 'checked';
    $_Lang['Insert_DefaultTab'] = $TabID - 1;

    if($PlanetID > 0)
    {
        if(in_array($ThisCMD, $Allowed_CMD))
        {
            if(in_array($TabID, array_keys($Allowed_Tabs)))
            {
                if(!empty($_POST['res'][$TabID]))
                {
                    $Query_GetPlanet = '';
                    $Query_GetPlanet .= "SELECT `pl`.`id`, `pl`.`name`, `pl`.`id_owner`, `user`.`username`, ";
                    $Query_GetPlanet .= "`pl`.`galaxy`, `pl`.`system`, `pl`.`planet`, `pl`.`planet_type` ";
                    $Query_GetPlanet .= "FROM {{table}} AS `pl` ";
                    $Query_GetPlanet .= "LEFT JOIN {{prefix}}users AS `user` ON `user`.`id` = `pl`.`id_owner` ";
                    $Query_GetPlanet .= "WHERE `pl`.`id` = {$PlanetID} LIMIT 1;";
                    $Result_GetPlanet = doquery($Query_GetPlanet, 'planets', true);
                    if($Result_GetPlanet['id'] == $PlanetID)
                    {
                        foreach($_POST['res'][$TabID] as $ThisID => $ThisValue)
                        {
                            if(in_array($ThisID, $Allowed_Tabs[$TabID]))
                            {
                                if($ThisCMD == 'set' AND empty($ThisValue))
                                {
                                    continue;
                                }
                                if($TabID == 2 AND !in_array($ThisID, $_Vars_ElementCategories['buildOn'][$Result_GetPlanet['planet_type']]))
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
                                if($TabID == 1)
                                {
                                    foreach($Query_Update_Data as $ThisID => $ThisValue)
                                    {
                                        $Query_Update_Array[] = "`{$Items_Resources[$ThisID]}` = `{$Items_Resources[$ThisID]}` + {$ThisValue}";
                                    }
                                }
                                else
                                {
                                    foreach($Query_Update_Data as $ThisID => $ThisValue)
                                    {
                                        $Query_Update_Array[] = "`{$_Vars_GameElements[$ThisID]}` = `{$_Vars_GameElements[$ThisID]}` + {$ThisValue}";
                                    }
                                }
                                $Query_Update .= implode(',', $Query_Update_Array);
                                $Query_Update .= " WHERE `id` = {$PlanetID} LIMIT 1;";
                            }
                            else if($ThisCMD == 'set')
                            {
                                $Query_Update = '';
                                $Query_Update .= "UPDATE {{table}} SET ";
                                if($TabID == 1)
                                {
                                    foreach($Query_Update_Data as $ThisID => $ThisValue)
                                    {
                                        $Query_Update_Array[] = "`{$Items_Resources[$ThisID]}` = {$ThisValue}";
                                    }
                                }
                                else
                                {
                                    foreach($Query_Update_Data as $ThisID => $ThisValue)
                                    {
                                        $Query_Update_Array[] = "`{$_Vars_GameElements[$ThisID]}` = {$ThisValue}";
                                    }
                                }
                                $Query_Update .= implode(',', $Query_Update_Array);
                                $Query_Update .= " WHERE `id` = {$PlanetID} LIMIT 1;";
                            }
                            else if($ThisCMD == 'sub')
                            {
                                if($TabID == 1)
                                {
                                    foreach($Query_Update_Data as $ThisID => $ThisValue)
                                    {
                                        $Query_Update_Fields[] = "`{$Items_Resources[$ThisID]}`";
                                        $Query_Update_Values[] = $ThisValue;
                                        $Query_Update_Set[] = "`{$Items_Resources[$ThisID]}` = IF(VALUES(`{$Items_Resources[$ThisID]}`) > `{$Items_Resources[$ThisID]}`, 0, `{$Items_Resources[$ThisID]}` - VALUES(`{$Items_Resources[$ThisID]}`))";
                                    }
                                }
                                else
                                {
                                    foreach($Query_Update_Data as $ThisID => $ThisValue)
                                    {
                                        $Query_Update_Fields[] = "`{$_Vars_GameElements[$ThisID]}`";
                                        $Query_Update_Values[] = $ThisValue;
                                        $Query_Update_Set[] = "`{$_Vars_GameElements[$ThisID]}` = IF(VALUES(`{$_Vars_GameElements[$ThisID]}`) > `{$_Vars_GameElements[$ThisID]}`, 0, `{$_Vars_GameElements[$ThisID]}` - VALUES(`{$_Vars_GameElements[$ThisID]}`))";
                                    }
                                }
                                $Query_Update = '';
                                $Query_Update .= "INSERT INTO {{table}} (`id`, ".implode(', ', $Query_Update_Fields).") ";
                                $Query_Update .= "VALUES ({$PlanetID}, ".implode(', ', $Query_Update_Values).") ";
                                $Query_Update .= "ON DUPLICATE KEY UPDATE ";
                                $Query_Update .= implode(',', $Query_Update_Set);
                            }
                            doquery($Query_Update, 'planets');
                            if(getDBLink()->affected_rows > 0)
                            {
                                $_MsgBox = array
                                (
                                    'color' => 'lime',
                                    'text' => sprintf
                                    (
                                        $_Lang['MsgBox_UpdateOK'],
                                        ($Result_GetPlanet['planet_type'] == 1 ? $_Lang['MsgBox_UpdateOK_Planet'] : $_Lang['MsgBox_UpdateOK_Moon']),
                                        $Result_GetPlanet['name'], $Result_GetPlanet['galaxy'], $Result_GetPlanet['system'], $Result_GetPlanet['planet'],
                                        $Result_GetPlanet['id'],
                                        $Result_GetPlanet['id_owner'], $Result_GetPlanet['username'], $Result_GetPlanet['id_owner'],
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
                                        ($Result_GetPlanet['planet_type'] == 1 ? $_Lang['MsgBox_UpdateOK_Planet'] : $_Lang['MsgBox_UpdateOK_Moon']),
                                        $Result_GetPlanet['name'], $Result_GetPlanet['galaxy'], $Result_GetPlanet['system'], $Result_GetPlanet['planet'],
                                        $Result_GetPlanet['id'],
                                        $Result_GetPlanet['id_owner'], $Result_GetPlanet['username'], $Result_GetPlanet['id_owner']
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
        $_MsgBox = array('color' => 'red', 'text' => $_Lang['MsgBox_BadPlanetID']);
    }
}

$_Lang['Insert_Rows_Res'] = '';
foreach($Items_Resources as $ItemID => $ItemName)
{
    $_Lang['Insert_Rows_Res'] .= parsetemplate($TPL_Row, array
    (
        'Name' => $_Lang[ucfirst($ItemName)],
        'TabNo' => 1,
        'ResID' => $ItemID
    ));
}
$_Lang['Insert_Rows_Buildings'] = '';
foreach($Items_Buildings as $ItemID)
{
    $_Lang['Insert_Rows_Buildings'] .= parsetemplate($TPL_Row, array
    (
        'Name' => "{$_Lang['tech'][$ItemID]} [#{$ItemID}]",
        'TabNo' => 2,
        'ResID' => $ItemID
    ));
}
$_Lang['Insert_Rows_Fleet'] = '';
foreach($Items_Fleet as $ItemID)
{
    $_Lang['Insert_Rows_Fleet'] .= parsetemplate($TPL_Row, array
    (
        'Name' => "{$_Lang['tech'][$ItemID]} [#{$ItemID}]",
        'TabNo' => 3,
        'ResID' => $ItemID
    ));
}
$_Lang['Insert_Rows_Defense'] = '';
foreach($Items_Defense as $ItemID)
{
    $_Lang['Insert_Rows_Defense'] .= parsetemplate($TPL_Row, array
    (
        'Name' => "{$_Lang['tech'][$ItemID]} [#{$ItemID}]",
        'TabNo' => 4,
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
