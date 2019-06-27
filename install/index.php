<?php

define('IN_INSTALL', true);

if (file_exists('lock')) {
    die('"lock" file found');
}

$_UseLang = 'pl';

$_Install_IsOnLocalhost = false;
$_Install_ConfigFile = 'config';
$_Install_ConfigDirectory = './config';

if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' OR $_SERVER['SERVER_ADDR'] == '::1') {
    $_Install_ConfigFile = 'config.localhost';
    $_Install_IsOnLocalhost = true;
}

include('install_functions.php');
include('./utils/determine_required_fields.php');
include('./utils/normalize_config_inputs.php');
include('./utils/translate_php_input_values_to_html.php');
include('./utils/verify_config_inputs.php');
include('./utils/verify_requirements.php');

includeLang();

if (!$_Install_IsOnLocalhost) {
    $_Lang['PHP_HideLocalhostInfo'] = 'display: none;';
}

// Check Requirements
$requirementsVerificationResult = verify_requirements([
    'configDirectory' => $_Install_ConfigDirectory,
    'configFile' => $_Install_ConfigFile
]);

if (!$requirementsVerificationResult['hasPassed']) {
    $_Lang['PHP_HideFormBox'] = 'display: none;';

    foreach ($requirementsVerificationResult['tests'] as $Key => $Value) {
        $_Lang['PHP_RequirementsList'][] = sprintf(
            $_Lang['CheckError_TestRow'],
            ($Value === true ? 'red' : 'lime'),
            ($Value === true ? $_Lang['CheckError_Fail'] : $_Lang['CheckError_Success']),
            $_Lang['Requirements_'.$Key]
        );
    }
    $_Lang['PHP_RequirementsList'] = implode('<br/>', $_Lang['PHP_RequirementsList']);
    $_Lang['PHP_InfoBox_Text'] = sprintf($_Lang['CheckError_Template'], $_Lang['PHP_RequirementsList']);

    display();
    die();
}

$_Lang['PHP_HideInfoBox'] = 'display: none;';

if (!isset($_POST['install'])) {
    display();
    die();
}

// --- Start installation process ---
$_Install_Vars = normalize_config_inputs($_POST);
$_Install_RequiredFields = determine_required_fields($_Install_Vars);

$htmlValues = translate_php_input_values_to_html($_Install_Vars, $_POST);

foreach ($htmlValues as $key => $value) {
    $_Lang['set_' . $key] = $value;
}

// Verify is all required fields are not empty
$_Install_CanProceed = true;
foreach ($_Install_RequiredFields as $fieldName) {
    $fieldKey = 'set_' . $fieldName;

    if (
        !isset($_POST[$fieldKey]) ||
        $_POST[$fieldKey] == ''
    ) {
        $_Install_CanProceed = false;
        $_Lang['PHP_BadVal_set_'.$fieldName] = 'class="redBorder"';
    }
}

if (!$_Install_CanProceed) {
    $_Lang['PHP_HideInfoBox'] = '';
    $_Lang['PHP_InfoBox_Color'] = 'red';
    $_Lang['PHP_InfoBox_Center'] = 'center';
    $_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_EmptyFields'];

    display();
    die();
}

// Check if every given field is correct
$_Install_VerifyVars = verify_config_inputs($_Install_Vars);

if (!$_Install_VerifyVars['isValid']) {
    foreach ($_Install_VerifyVars['tests'] as $key => $value) {
        if ($value) {
            continue;
        }

        $_Lang['PHP_BadVal_set_' . $key] = 'class="redBorder"';
    }

    $_Lang['PHP_HideInfoBox'] = '';
    $_Lang['PHP_InfoBox_Color'] = 'red';
    $_Lang['PHP_InfoBox_Center'] = 'center';
    $_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_BadVars'];

    display();
    die();
}

// Try to establish a connection with DataBase Server
$_Install_DBLink = new mysqli(
    $_Install_Vars['dbconfig_host'],
    $_Install_Vars['dbconfig_user'],
    $_Install_Vars['dbconfig_pass']
);

if ($_Install_DBLink === false) {
    $_Lang['PHP_HideInfoBox'] = '';
    $_Lang['PHP_InfoBox_Color'] = 'red';
    $_Lang['PHP_InfoBox_Center'] = 'center';
    $_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_CannotConnectToMySQL'];

    display();
    die();
}

// Try to select game DataBase
$_Install_DBSelect = $_Install_DBLink->select_db($_Install_Vars['dbconfig_name']);

if (!$_Install_DBSelect) {
    $_Lang['PHP_HideInfoBox'] = '';
    $_Lang['PHP_InfoBox_Color'] = 'red';
    $_Lang['PHP_InfoBox_Center'] = 'center';
    $_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_CannotSelectDatabase'];

    display();
    die();
}

// Try to write all required data to files
$_Install_TPL_Config = [
    'Host'          => $_Install_Vars['dbconfig_host'],
    'Username'      => $_Install_Vars['dbconfig_user'],
    'Password'      => $_Install_Vars['dbconfig_pass'],
    'DBName'        => $_Install_Vars['dbconfig_name'],
    'DBPrefix'      => $_Install_Vars['dbconfig_prefix'].'_',
    'SecretWord'    => generateRandomHash(20)
];
$_Install_TPL_Constants = [
    'UNIID'                                 => $_Install_Vars['const_uniid'],
    'AdminEmail'                            => $_Install_Vars['admin_email'],
    'Domain'                                => $_Install_Vars['const_domain'],
    'GenerateSubdomainLink'                 => (
        !empty($_Install_Vars['const_subdomain']) ?
        ($_Install_Vars['const_subdomain'] . '.' . $_Install_Vars['const_domain']) :
        $_Install_Vars['const_domain']
    ),
    'AutoTool_ZipLog_Hash'                  => md5($_Install_Vars['uni_autotoolpass_gziplog']),
    'AutoTool_StatBuilder_Hash'             => md5($_Install_Vars['uni_autotoolpass_statbuilder']),
    'AutoTool_GC_Hash'                      => md5($_Install_Vars['uni_autotoolpass_gc']),
    'GameName'                              => $_Install_Vars['uni_gamename'],
    'Reg_RequireEmailConfirm'               => (
        $_Install_Vars['uni_mailactivationneeded'] ?
        'true' :
        'false'
    ),
    'Reg_RecaptchaEnabled'                  => (
        $_Install_Vars['const_recaptcha_enable'] ?
        'true' :
        'false'
    ),
    'Reg_Recaptcha_ServerIP_As_Hostname'    => (
        $_Install_Vars['const_recaptcha_serverip_as_hostname'] ?
        'true' :
        'false'
    ),
    'Reg_Recaptcha_Private'                 => $_Install_Vars['const_recaptcha_private'],
    'Reg_Recaptcha_Public'                  => $_Install_Vars['const_recaptcha_public'],
    'InsertServerMainOpenTime'              => time()
];
$_Install_RegisterJS = [
    'ReplaceDomain' => $_Install_Vars['const_domain']
];

$_Install_CreateConfig = parseFile('install_filetpl_config.tpl', $_Install_TPL_Config);
$_Install_CreateConstants = parseFile('install_filetpl_constants.tpl', $_Install_TPL_Constants);
$_Install_ReplaceRegisterJS = parseFile('../js/register.js', $_Install_RegisterJS);

$_Install_SaveConfig = file_put_contents('../'.$_Install_ConfigFile.'.php', $_Install_CreateConfig);
$_Install_SaveConstants = file_put_contents('../includes/constants.php', $_Install_CreateConstants);
$_Install_SaveRegisterJS = file_put_contents('../js/register.js', $_Install_ReplaceRegisterJS);

if (!($_Install_SaveConfig && $_Install_SaveConstants)) {
    $_Lang['PHP_HideInfoBox'] = '';
    $_Lang['PHP_InfoBox_Color'] = 'red';
    $_Lang['PHP_InfoBox_Center'] = 'center';
    if (!$_Install_SaveConfig && !$_Install_SaveConstants) {
        $_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_FailToSaveBothFiles'];
    } else if (!$_Install_SaveConfig) {
        $_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_FailToSaveConfig'];
    } else {
        $_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_FailToSaveConstants'];
    }

    display();
    die();
}

if (!$_Install_SaveRegisterJS) {
    $_Lang['PHP_HideInfoBox'] = '';
    $_Lang['PHP_InfoBox_Color'] = 'red';
    $_Lang['PHP_InfoBox_Center'] = 'center';
    $_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_FailToSaveRegister'];

    display();
    die();
}

// Now, final try - call every query
$_Install_QueriesData = [
    'prefix'                                        => $_Install_Vars['dbconfig_prefix'],
    'Config_GameName'                               => $_Install_Vars['uni_gamename'],
    'Config_GameSpeed'                              => $_Install_Vars['uni_gamespeed'] * 2500,
    'Config_FleetSpeed'                             => $_Install_Vars['uni_fleetspeed'] * 2500,
    'Config_ResourceSpeed'                          => $_Install_Vars['uni_resourcespeed'],
    'Config_FleetDebris'                            => $_Install_Vars['uni_fleetdebris'],
    'Config_DefenseDebris'                          => $_Install_Vars['uni_defensedebris'],
    'Config_MissileDebris'                          => $_Install_Vars['uni_missiledebris'],
    'Config_InitialFields'                          => $_Install_Vars['uni_motherfields'],
    'Config_CookieName'                             => (
        preg_replace('/\s+/', '', strtoupper($_Install_Vars['uni_gamename'])) .
        '_CK'
    ),
    'Config_NoobProtection_Enable'                  => (
        $_Install_Vars['uni_noobprt_enable'] ?
        '1' :
        '0'
    ),
    'Config_NoobProtection_BasicLimit_Time'         => $_Install_Vars['uni_noobprt_basictime'],
    'Config_NoobProtection_BasicLimit_Multiplier'   => $_Install_Vars['uni_noobprt_basicmultiplier'],
    'Config_NoobProtection_ProtectionRemove'        => $_Install_Vars['uni_noobprt_remove'],
    'Config_NoobProtection_IdleDaysProtection'      => $_Install_Vars['uni_noobprt_idledays'],
    'Config_NoobProtection_FirstLoginProtection'    => $_Install_Vars['uni_noobprt_firstlogin'],
    'Config_AntiFarm_Enable'                        => (
        $_Install_Vars['uni_antifarmenable'] ?
        '1' :
        '0'
    ),
    'Config_AntiFarm_UserStatsRate'                 => $_Install_Vars['uni_antifarmratio'],
    'Config_AntiFarm_CountTotal'                    => $_Install_Vars['uni_antifarmtotalcount'],
    'Config_AntiFarm_CountPlanet'                   => $_Install_Vars['uni_antifarmplanetcount'],
    'Config_BashLimit_Enabled'                      => (
        $_Install_Vars['uni_antibashenable'] ?
        '1' :
        '0'
    ),
    'Config_BashLimit_Interval'                     => $_Install_Vars['uni_antibashinterval'],
    'Config_BashLimit_CountTotal'                   => $_Install_Vars['uni_antibashtotalcount'],
    'Config_BashLimit_CountPlanet'                  => $_Install_Vars['uni_antibashplanetcount'],
    'Config_TelemetryEnabled'                       => (
        $_Install_Vars['uni_telemetryenable'] ?
        '1' :
        '0'
    ),
    'AdminUser_name'                                => $_Install_Vars['admin_username'],
    'AdminUser_passhash'                            => md5($_Install_Vars['admin_password']),
    'AdminUser_email'                               => $_Install_Vars['admin_email'],
];

$_Install_DoQueries = parseFile('install_database.sql', $_Install_QueriesData);

$_Install_DoQueries = explode('-- --------------------------------------------------------', $_Install_DoQueries);

foreach ($_Install_DoQueries as $ThisQuery) {
    $_Install_QueryResult = $_Install_DBLink->query($ThisQuery);
    if ($_Install_QueryResult === false) {
        break;
    }
}

if (!$_Install_QueryResult) {
    $_Lang['PHP_HideInfoBox'] = '';
    $_Lang['PHP_InfoBox_Color'] = 'red';
    $_Lang['PHP_InfoBox_Center'] = 'center';
    $_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_QueryFail'];

    display();
    die();
}

$_Install_MigrationEntryFileCreationSuccess = false;

try {
    generateMigrationEntryFile();

    $_Install_MigrationEntryFileCreationSuccess = true;
} catch (\Exception $exception) {
    $_Install_MigrationEntryFileCreationSuccess = false;
}

if (!$_Install_MigrationEntryFileCreationSuccess)
{
    $_Lang['PHP_HideInfoBox'] = '';
    $_Lang['PHP_InfoBox_Color'] = 'red';
    $_Lang['PHP_InfoBox_Center'] = 'center';
    $_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_MigrationEntryFileCreationFail'];

    display();
    die();
}

$_Lang['PHP_HideInfoBox'] = '';
$_Lang['PHP_HideFormBox'] = 'display: none;';
$_Lang['PHP_InfoBox_Color'] = 'lime';
$_Lang['PHP_InfoBox_Center'] = 'center';
$_Lang['PHP_InfoBox_Text'] = $_Lang['InstallSuccess'];

file_put_contents('lock', '');

display();

?>
