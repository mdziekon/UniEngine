<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(!CheckAuth('programmer'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

$_Checked = 'checked';

includeLang('admin/settings');

$AllowedVars = array
(
    'EngineInfo_Version', 'EngineInfo_BuildNo', 'game_disable', 'close_reason', 'enforceRulesAcceptance',
    'last_rules_changes', 'TelemetryEnabled', 'enable_bbcode', 'game_speed', 'resource_multiplier',
    'fleet_speed', 'Fleet_Cdr', 'Defs_Cdr', 'Debris_Def_Rocket', 'stat_settings', 'initial_fields', 'BuildLabWhileRun',
    'metal_basic_income', 'crystal_basic_income', 'deuterium_basic_income', 'energy_basic_income',
    'OverviewNewsFrame', 'OverviewNewsText', 'OverviewBanner', 'OverviewClickBanner', 'banned_ip_list', 'BannedMailDomains',
);

$_DefFunctions = array
(
    'checkbox' => function($Value){ if($Value == 'on'){ return '1'; } return '0'; },
    'checkbox_reverse' => function($Value){ if($Value == 'on'){ return '0'; } return '1'; },
    'sanitize_plusint' => function($Value){ $Value = intval($Value); if($Value <= 0){ return false; } return $Value; },
    'sanitize_nonnegint' => function($Value){ $Value = intval($Value); if($Value < 0){ return false; } return $Value; },
    'sanitize_percent' => function($Value){ $Value = round($Value, 2); if($Value < 0 OR $Value > 100){ return false; } return $Value; },
    'sanitize_text' => function($Value){ return getDBLink()->escape_string(stripslashes($Value)); },
    'sanitize_check' => function($Value){ return getDBLink()->escape_string($Value); },
    'desanitize_text' => function($Value){ return stripslashes($Value); },
    'dedesanitize_text' => function($Name, $Value){ return stripslashes($_POST[$Name]); },
);

$_PreProcessor = array
(
    'close_reason' => $_DefFunctions['sanitize_text'],
    'game_disable' => $_DefFunctions['checkbox'],
    'enforceRulesAcceptance' => $_DefFunctions['checkbox'],
    'last_rules_changes' => function($Value){ return strtotime($Value); },
    'TelemetryEnabled' => $_DefFunctions['checkbox'],
    'enable_bbcode' => $_DefFunctions['checkbox'],
    'game_speed' => function($Value){ return $Value * 2500; },
    'fleet_speed' => function($Value){ return $Value * 2500; },
    'BuildLabWhileRun' => $_DefFunctions['checkbox_reverse'],
    'OverviewNewsText' => $_DefFunctions['sanitize_text'],
    'OverviewClickBanner' => $_DefFunctions['sanitize_text'],
    'OverviewNewsFrame' => $_DefFunctions['checkbox'],
    'OverviewBanner' => $_DefFunctions['checkbox'],
    'banned_ip_list' => function($Value){ $Value = explode("\n", $Value); $Value = array_map('trim', $Value); return implode('|', $Value); },
    'BannedMailDomains' => function($Value){ $Value = explode("\n", $Value); $Value = array_map('trim', $Value); return implode('|', $Value); },
);
$_CheckSanitize = array
(
    'close_reason' => $_DefFunctions['sanitize_check'],
    'OverviewNewsText' => $_DefFunctions['sanitize_check'],
    'OverviewClickBanner' => $_DefFunctions['sanitize_check'],
);

$_PostProcessor['2db'] = array
(

);
$_PostProcessor['2var'] = array
(
    'close_reason' => $_DefFunctions['dedesanitize_text'],
    'OverviewNewsText' => $_DefFunctions['dedesanitize_text'],
    'OverviewClickBanner' => $_DefFunctions['dedesanitize_text'],
);

$_Sanitizers = array
(
    'EngineInfo_Version' => function($Value){ if(!preg_match('/^[0-9\.]{1,}$/D', $Value)){ return false; } return $Value; },
    'EngineInfo_BuildNo' => function($Value){ $Value = intval($Value); if($Value < 0){ return false; } return $Value; },
    'last_rules_changes' => function($Value){ if($Value < 0){ return false; } return $Value; },
    'game_speed' => $_DefFunctions['sanitize_plusint'],
    'resource_multiplier' => $_DefFunctions['sanitize_plusint'],
    'fleet_speed' => $_DefFunctions['sanitize_plusint'],
    'Fleet_Cdr' => $_DefFunctions['sanitize_percent'],
    'Defs_Cdr' => $_DefFunctions['sanitize_percent'],
    'Debris_Def_Rocket' => $_DefFunctions['sanitize_percent'],
    'stat_settings' => $_DefFunctions['sanitize_plusint'],
    'initial_fields' => $_DefFunctions['sanitize_plusint'],
    'metal_basic_income' => $_DefFunctions['sanitize_nonnegint'],
    'crystal_basic_income' => $_DefFunctions['sanitize_nonnegint'],
    'deuterium_basic_income' => $_DefFunctions['sanitize_nonnegint'],
    'energy_basic_income' => $_DefFunctions['sanitize_nonnegint'],
    'banned_ip_list' => function($Value){ if(!preg_match('/^[0-9\.\|\:]{1,}$/D', $Value)){ return false; } return $Value; },
    'BannedMailDomains' => function($Value){ if(!preg_match('/^[a-zA-Z0-9\.\_\-\|]{1,}$/D', $Value)){ return false; } return $Value; },
);

if(isset($_GET['configcachereload']) && $_GET['configcachereload'] == 1)
{
    $Query_GetGameConfig = "SELECT * FROM {{table}};";
    $Result_GetGameConfig = doquery($Query_GetGameConfig, 'config');
    while($FetchData = $Result_GetGameConfig->fetch_assoc())
    {
        $_GameConfig[$FetchData['config_name']] = $FetchData['config_value'];
    }
    $_MemCache->GameConfig = $_GameConfig;

    $_Lang['Msg_Color'] = 'lime';
    $_Lang['Msg_Text'] = $_Lang['Messages_ConfigCacheReloaded'];
}
else if(isset($_POST['opt_save']) && $_POST['opt_save'] == '1')
{
    foreach($AllowedVars as $Name)
    {
        $Value = isset($_POST[$Name]) ? $_POST[$Name] : null;
        if(in_array($Name, $AllowedVars))
        {
            if(!empty($_PreProcessor[$Name]))
            {
                $Value = $_PreProcessor[$Name]($Value);
            }

            $VarToCheck = $_GameConfig[$Name];
            if(!empty($_CheckSanitize[$Name]))
            {
                $VarToCheck = $_CheckSanitize[$Name]($VarToCheck);
            }
            if($Value != $VarToCheck)
            {
                if(isset($_Sanitizers[$Name]))
                {
                    $Value = $_Sanitizers[$Name]($Value);
                }

                if($Value !== false)
                {
                    $ToUpdate[$Name] = $Value;
                    if(!empty($_PostProcessor['2db'][$Name]))
                    {
                        $ToUpdate[$Name] = $_PostProcessor['2db'][$Name]($Value);
                    }
                    if(!empty($_PostProcessor['2var'][$Name]))
                    {
                        $Value = $_PostProcessor['2var'][$Name]($Name, $Value);
                    }
                    $_GameConfig[$Name] = $Value;
                }
            }
        }
    }

    if(!empty($ToUpdate))
    {
        $UpdateQuery  = "INSERT INTO {{table}} (`config_name`, `config_value`) VALUES ";
        foreach($ToUpdate as $FieldName => $Value)
        {
            $UpdateQueryArray[] = "('{$FieldName}', '{$Value}')";
        }
        $UpdateQuery .= implode(', ', $UpdateQueryArray);
        $UpdateQuery .= " ON DUPLICATE KEY UPDATE ";
        $UpdateQuery .= "`config_value` = VALUES(`config_value`);";
        doquery($UpdateQuery, 'config');
        $_MemCache->GameConfig = $_GameConfig;

        $UpdatedRows = getDBLink()->affected_rows;
        if($UpdatedRows > 0)
        {
            $_Lang['Msg_Color'] = 'lime';
            $_Lang['Msg_Text'] = sprintf($_Lang['Messages_UpdateSuccess'], prettyNumber($UpdatedRows / 2));
        }
        else
        {
            $_Lang['Msg_Color'] = 'orange';
            $_Lang['Msg_Text'] = $_Lang['Messages_NoUpdateMade'];
        }
    }
    else
    {
        $_Lang['Msg_Color'] = 'orange';
        $_Lang['Msg_Text'] = $_Lang['Messages_NothingToUpdate'];
    }
}

if(empty($_Lang['Msg_Text']))
{
    $_Lang['Msg_Hide'] = ' style="display: none;"';
}

$_Lang['PHP_EngineInfo_Version'] = $_GameConfig['EngineInfo_Version'];
$_Lang['PHP_EngineInfo_BuildNo'] = $_GameConfig['EngineInfo_BuildNo'];
$_Lang['PHP_game_disable'] = ($_GameConfig['game_disable'] ? $_Checked : '');
$_Lang['PHP_close_reason'] = $_GameConfig['close_reason'];
$_Lang['PHP_enforceRulesAcceptance'] = ($_GameConfig['enforceRulesAcceptance'] ? $_Checked : '');
$_Lang['PHP_last_rules_changes'] = date('Y-m-d H:i:s', $_GameConfig['last_rules_changes']);
$_Lang['PHP_TelemetryEnabled'] = ($_GameConfig['TelemetryEnabled'] ? $_Checked : '');
$_Lang['PHP_enable_bbcode'] = ($_GameConfig['enable_bbcode'] ? $_Checked : '');
$_Lang['PHP_game_speed'] = $_GameConfig['game_speed'] / 2500;
$_Lang['PHP_resource_multiplier'] = $_GameConfig['resource_multiplier'];
$_Lang['PHP_fleet_speed'] = $_GameConfig['fleet_speed'] / 2500;
$_Lang['PHP_Fleet_Cdr'] = $_GameConfig['Fleet_Cdr'];
$_Lang['PHP_Defs_Cdr'] = $_GameConfig['Defs_Cdr'];
$_Lang['PHP_Debris_Def_Rocket'] = $_GameConfig['Debris_Def_Rocket'];
$_Lang['PHP_stat_settings'] = $_GameConfig['stat_settings'];
$_Lang['PHP_initial_fields'] = $_GameConfig['initial_fields'];
$_Lang['PHP_BuildLabWhileRun'] = (!$_GameConfig['BuildLabWhileRun'] ? $_Checked : '');
$_Lang['PHP_metal_basic_income'] = $_GameConfig['metal_basic_income'];
$_Lang['PHP_crystal_basic_income'] = $_GameConfig['crystal_basic_income'];
$_Lang['PHP_deuterium_basic_income'] = $_GameConfig['deuterium_basic_income'];
$_Lang['PHP_energy_basic_income'] = $_GameConfig['energy_basic_income'];
$_Lang['PHP_OverviewNewsFrame'] = ($_GameConfig['OverviewNewsFrame'] ? $_Checked : '');
$_Lang['PHP_OverviewNewsText'] = $_GameConfig['OverviewNewsText'];
$_Lang['PHP_OverviewBanner'] = ($_GameConfig['OverviewBanner'] ? $_Checked : '');
$_Lang['PHP_OverviewClickBanner'] = $_GameConfig['OverviewClickBanner'];
$_Lang['PHP_banned_ip_list'] = str_replace('|', "\n", $_GameConfig['banned_ip_list']);
$_Lang['PHP_BannedMailDomains'] = str_replace('|', "\n", $_GameConfig['BannedMailDomains']);

$_Lang['JS_DatePicker_TranslationLang'] = getJSDatePickerTranslationLang();

$Page = parsetemplate(gettemplate('admin/settings_body'), $_Lang);
display($Page, $_Lang['Body_Title'], false, true);

?>
