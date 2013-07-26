<?php

if(file_exists('lock'))
{
	die('"lock" file found');
}

define('IN_INSTALL', true);
if($_SERVER['SERVER_ADDR'] == '127.0.0.1' OR $_SERVER['SERVER_ADDR'] == '::1')
{
	$_Install_ConfigFile = 'config.localhost';
	define('ONLOCALHOST', true);
}
else
{
	$_Install_ConfigFile = 'config';
	define('ONLOCALHOST', false);
}

include('install_functions.php');

$_UseLang = 'pl';

includeLang();

// Check Requirements
$_RequirementsCheckPassed = true;
$_RequirementsCheckFails = array();

if(version_compare(PHP_VERSION, '5.4.0') < 0)
{
	$_RequirementsCheckPassed = false;
	$_RequirementsCheckFails['PHPVersion'] = true;
}
else
{
	$_RequirementsCheckFails['PHPVersion'] = false;
}
//if(error_reporting() & E_NOTICE)
{
	//$_RequirementsCheckPassed = false;
	//$_RequirementsCheckFails['PHPNoticesOff'] = true;
}
//else
{
	$_RequirementsCheckFails['PHPNoticesOff'] = false;
}
if(is_writable('../'.$_Install_ConfigFile.'.php'))
{
	$_RequirementsCheckFails['ConfigWritable'] = false;
}
else
{
	$_RequirementsCheckPassed = false;
	$_RequirementsCheckFails['ConfigWritable'] = true;
}
if(is_writable('../includes/constants.php'))
{
	$_RequirementsCheckFails['ConstantsWritable'] = false;
}
else
{
	$_RequirementsCheckPassed = false;
	$_RequirementsCheckFails['ConstantsWritable'] = true;
}
if(is_writable('../js/register.js'))
{
	$_RequirementsCheckFails['RegisterWritable'] = false;
}
else
{
	$_RequirementsCheckPassed = false;
	$_RequirementsCheckFails['RegisterWritable'] = true;
}
if(is_writable('../action_logs'))
{
	$_RequirementsCheckFails['ActionLogsWritable'] = false;
}
else
{
	$_RequirementsCheckPassed = false;
	$_RequirementsCheckFails['ActionLogsWritable'] = true;
}
if(is_writable('../admin/action_logs'))
{
	$_RequirementsCheckFails['AdminActionLogsWritable'] = false;
}
else
{
	$_RequirementsCheckPassed = false;
	$_RequirementsCheckFails['AdminActionLogsWritable'] = true;
}

if(!ONLOCALHOST)
{
	$_Lang['PHP_HideLocalhostInfo'] = 'display: none;';
}

if(!$_RequirementsCheckPassed)
{
	$_Lang['PHP_HideFormBox'] = 'display: none;';
	
	foreach($_RequirementsCheckFails as $Key => $Value)
	{
		$_Lang['PHP_RequirementsList'][] = sprintf($_Lang['CheckError_TestRow'], ($Value === true ? 'red' : 'lime'), ($Value === true ? $_Lang['CheckError_Fail'] : $_Lang['CheckError_Success']), $_Lang['Requirements_'.$Key]);
	}
	$_Lang['PHP_RequirementsList'] = implode('<br/>', $_Lang['PHP_RequirementsList']);
	$_Lang['PHP_InfoBox_Text'] = sprintf($_Lang['CheckError_Template'], $_Lang['PHP_RequirementsList']);
}
else
{
	$_Lang['PHP_HideInfoBox'] = 'display: none;';
	
	if(isset($_POST['install']))
	{
		// Check if every needed field is not empty
		$_Install_RequiredFields = array
		(
			'set_dbconfig_host', 'set_dbconfig_user', 'set_dbconfig_name', 'set_const_domain',
			'set_admin_username', 'set_admin_password', 'set_admin_email',
			'set_uni_gamename', 'set_uni_gamespeed', 'set_uni_fleetspeed', 'set_uni_resourcespeed', 'set_uni_motherfields',
			'set_uni_fleetdebris', 'set_uni_defensedebris', 'set_uni_missiledebris', 
		);
		
		foreach($_POST as $key => $value)
		{
			$_Lang[$key] = $value;
		}
		if(isset($_POST['set_const_recaptcha_enable']) && $_POST['set_const_recaptcha_enable'] == 'on')
		{
			$_Lang['set_const_recaptcha_enable'] = 'checked';
		}
		if(isset($_POST['set_uni_mailactivationneeded']) && $_POST['set_uni_mailactivationneeded'] == 'on')
		{
			$_Lang['set_uni_mailactivationneeded'] = 'checked';
		}
		if(isset($_POST['set_uni_telemetryenable']) && $_POST['set_uni_telemetryenable'] == 'on')
		{
			$_Lang['set_uni_telemetryenable'] = 'checked';
		}
		if(isset($_POST['set_uni_noobprt_enable']) && $_POST['set_uni_noobprt_enable'] == 'on')
		{
			$_Lang['set_uni_noobprt_enable'] = 'checked';
		}
		if(isset($_POST['set_uni_antifarmenable']) && $_POST['set_uni_antifarmenable'] == 'on')
		{
			$_Lang['set_uni_antifarmenable'] = 'checked';
		}
		if(isset($_POST['set_uni_antibashenable']) && $_POST['set_uni_antibashenable'] == 'on')
		{
			$_Lang['set_uni_antibashenable'] = 'checked';
		}
		
		if(function_exists('apc_fetch'))
		{
			$_Install_RequiredFields[] = 'set_const_uniid';
		}
		if(isset($_POST['set_const_recaptcha_enable']) && $_POST['set_const_recaptcha_enable'] == 'on')
		{
			$_Install_RequiredFields[] = 'set_const_recaptcha_public';
			$_Install_RequiredFields[] = 'set_const_recaptcha_private';
		}
		if(isset($_POST['set_uni_noobprt_enable']) && $_POST['set_uni_noobprt_enable'] == 'on')
		{
			$_Install_RequiredFields[] = 'set_uni_noobprt_basictime';
			$_Install_RequiredFields[] = 'set_uni_noobprt_basicmultiplier';
			$_Install_RequiredFields[] = 'set_uni_noobprt_remove';
			$_Install_RequiredFields[] = 'set_uni_noobprt_idledays';
			$_Install_RequiredFields[] = 'set_uni_noobprt_firstlogin';
		}
		if(isset($_POST['set_uni_antifarmenable']) && $_POST['set_uni_antifarmenable'] == 'on')
		{
			$_Install_RequiredFields[] = 'set_uni_antifarmratio';
			$_Install_RequiredFields[] = 'set_uni_antifarmtotalcount';
			$_Install_RequiredFields[] = 'set_uni_antifarmplanetcount';
		}
		if(isset($_POST['set_uni_antibashenable']) && $_POST['set_uni_antibashenable'] == 'on')
		{
			$_Install_RequiredFields[] = 'set_uni_antibashinterval';
			$_Install_RequiredFields[] = 'set_uni_antibashtotalcount';
			$_Install_RequiredFields[] = 'set_uni_antibashplanetcount';
		}
		
		$_Install_CanProceed = true;
		foreach($_Install_RequiredFields as $Value)
		{
			if(!isset($_POST[$Value]) || empty($_POST[$Value]))
			{
				$_Install_CanProceed = false;
				$_Lang['PHP_BadVal_'.$Value] = 'class="redBorder"';
			}
		}
		
		if($_Install_CanProceed)
		{
			// Check if every given field is correct
			$_Install_Vars['set_uni_gamespeed'] = floatval($_POST['set_uni_gamespeed']);
			$_Install_Vars['set_uni_fleetspeed'] = floatval($_POST['set_uni_fleetspeed']);
			$_Install_Vars['set_uni_resourcespeed'] = round($_POST['set_uni_resourcespeed']);
			$_Install_Vars['set_uni_motherfields'] = round($_POST['set_uni_motherfields']);
			$_Install_Vars['set_uni_fleetdebris'] = round($_POST['set_uni_fleetdebris']);
			$_Install_Vars['set_uni_defensedebris'] = round($_POST['set_uni_defensedebris']);
			$_Install_Vars['set_uni_missiledebris'] = round($_POST['set_uni_missiledebris']);
			$_Install_Vars['set_uni_noobprt_basictime'] = round($_POST['set_uni_noobprt_basictime']);
			$_Install_Vars['set_uni_noobprt_basicmultiplier'] = floatval($_POST['set_uni_noobprt_basicmultiplier']);
			$_Install_Vars['set_uni_noobprt_remove'] = round($_POST['set_uni_noobprt_remove']);
			$_Install_Vars['set_uni_noobprt_idledays'] = round($_POST['set_uni_noobprt_idledays']);
			$_Install_Vars['set_uni_noobprt_firstlogin'] = round($_POST['set_uni_noobprt_firstlogin']);
			$_Install_Vars['set_uni_antifarmratio'] = floatval($_POST['set_uni_antifarmratio']);
			$_Install_Vars['set_uni_antifarmtotalcount'] = round($_POST['set_uni_antifarmtotalcount']);
			$_Install_Vars['set_uni_antifarmplanetcount'] = round($_POST['set_uni_antifarmplanetcount']);
			$_Install_Vars['set_uni_antibashinterval'] = round($_POST['set_uni_antibashinterval']);
			$_Install_Vars['set_uni_antibashtotalcount'] = round($_POST['set_uni_antibashtotalcount']);
			$_Install_Vars['set_uni_antibashplanetcount'] = round($_POST['set_uni_antibashplanetcount']);
			
			if($_Install_Vars['set_uni_gamespeed'] <= 0)
			{
				$_Install_CanProceed = false;
				$_Lang['PHP_BadVal_set_uni_gamespeed'] = 'class="redBorder"';
			}
			if($_Install_Vars['set_uni_fleetspeed'] <= 0)
			{
				$_Install_CanProceed = false;
				$_Lang['PHP_BadVal_set_uni_fleetspeed'] = 'class="redBorder"';
			}
			if($_Install_Vars['set_uni_resourcespeed'] <= 0)
			{
				$_Install_CanProceed = false;
				$_Lang['PHP_BadVal_set_uni_resourcespeed'] = 'class="redBorder"';
			}
			if($_Install_Vars['set_uni_motherfields'] <= 0)
			{
				$_Install_CanProceed = false;
				$_Lang['PHP_BadVal_set_uni_motherfields'] = 'class="redBorder"';
			}
			if($_Install_Vars['set_uni_fleetdebris'] < 0 || $_Install_Vars['set_uni_fleetdebris'] > 100)
			{
				$_Install_CanProceed = false;
				$_Lang['PHP_BadVal_set_uni_fleetdebris'] = 'class="redBorder"';
			}
			if($_Install_Vars['set_uni_defensedebris'] < 0 || $_Install_Vars['set_uni_defensedebris'] > 100)
			{
				$_Install_CanProceed = false;
				$_Lang['PHP_BadVal_set_uni_defensedebris'] = 'class="redBorder"';
			}
			if($_Install_Vars['set_uni_missiledebris'] < 0 || $_Install_Vars['set_uni_missiledebris'] > 100)
			{
				$_Install_CanProceed = false;
				$_Lang['PHP_BadVal_set_uni_missiledebris'] = 'class="redBorder"';
			}
			if(isset($_POST['set_uni_noobprt_enable']) && $_POST['set_uni_noobprt_enable'] == 'on')
			{
				if($_Install_Vars['set_uni_noobprt_basictime'] <= 0)
				{
					$_Install_CanProceed = false;
					$_Lang['PHP_BadVal_set_uni_noobprt_basictime'] = 'class="redBorder"';
				}
				if($_Install_Vars['set_uni_noobprt_basicmultiplier'] <= 0)
				{
					$_Install_CanProceed = false;
					$_Lang['PHP_BadVal_set_uni_noobprt_basicmultiplier'] = 'class="redBorder"';
				}
				if($_Install_Vars['set_uni_noobprt_remove'] <= 0)
				{
					$_Install_CanProceed = false;
					$_Lang['PHP_BadVal_set_uni_noobprt_remove'] = 'class="redBorder"';
				}
				if($_Install_Vars['set_uni_noobprt_idledays'] <= 0)
				{
					$_Install_CanProceed = false;
					$_Lang['PHP_BadVal_set_uni_noobprt_idledays'] = 'class="redBorder"';
				}
				if($_Install_Vars['set_uni_noobprt_firstlogin'] < 0)
				{
					$_Install_CanProceed = false;
					$_Lang['PHP_BadVal_set_uni_noobprt_firstlogin'] = 'class="redBorder"';
				}
			}
			if(isset($_POST['set_uni_antifarmenable']) && $_POST['set_uni_antifarmenable'] == 'on')
			{
				if($_Install_Vars['set_uni_antifarmratio'] <= 0)
				{
					$_Install_CanProceed = false;
					$_Lang['PHP_BadVal_set_uni_antifarmratio'] = 'class="redBorder"';
				}
				if($_Install_Vars['set_uni_antifarmtotalcount'] <= 0)
				{
					$_Install_CanProceed = false;
					$_Lang['PHP_BadVal_set_uni_antifarmtotalcount'] = 'class="redBorder"';
				}
				if($_Install_Vars['set_uni_antifarmplanetcount'] <= 0)
				{
					$_Install_CanProceed = false;
					$_Lang['PHP_BadVal_set_uni_antifarmplanetcount'] = 'class="redBorder"';
				}
			}
			if(isset($_POST['set_uni_antibashenable']) && $_POST['set_uni_antibashenable'] == 'on')
			{
				if($_Install_Vars['set_uni_antibashinterval'] <= 0)
				{
					$_Install_CanProceed = false;
					$_Lang['PHP_BadVal_set_uni_antibashinterval'] = 'class="redBorder"';
				}
				if($_Install_Vars['set_uni_antibashtotalcount'] <= 0)
				{
					$_Install_CanProceed = false;
					$_Lang['PHP_BadVal_set_uni_antibashtotalcount'] = 'class="redBorder"';
				}
				if($_Install_Vars['set_uni_antibashplanetcount'] <= 0)
				{
					$_Install_CanProceed = false;
					$_Lang['PHP_BadVal_set_uni_antibashplanetcount'] = 'class="redBorder"';
				}
			}
			
			if($_Install_CanProceed)
			{
				// Try to establish a connection with DataBase Server			
				$_Install_DBLink = mysql_connect($_POST['set_dbconfig_host'], $_POST['set_dbconfig_user'], $_POST['set_dbconfig_pass']);
				if($_Install_DBLink !== false)
				{
					// Try to select game DataBase
					$_Install_DBSelect = mysql_select_db($_POST['set_dbconfig_name']);
					if($_Install_DBSelect)
					{
						// Try to write all required data to files
						$_Install_TPL_Config = array
						(
							'Host' => $_POST['set_dbconfig_host'],
							'Username' => $_POST['set_dbconfig_user'],
							'Password' => $_POST['set_dbconfig_pass'],
							'DBName' => $_POST['set_dbconfig_name'],
							'DBPrefix' => $_POST['set_dbconfig_prefix'].'_',
							'SecretWord' => generateRandomHash(20)
						);
						$_Install_TPL_Constants = array
						(
							'UNIID' => $_POST['set_const_uniid'],
							'AdminEmail' => $_POST['set_admin_email'],
							'Domain' => $_POST['set_const_domain'],
							'GenerateSubdomainLink' => (!empty($_POST['set_const_subdomain']) ? ($_POST['set_const_subdomain'].'.'.$_POST['set_const_domain']) : $_POST['set_const_domain']),
							'AutoTool_ZipLog_Hash' => md5($_POST['set_uni_autotoolpass_gziplog']),
							'AutoTool_StatBuilder_Hash' => md5($_POST['set_uni_autotoolpass_statbuilder']),
							'AutoTool_GC_Hash' => md5($_POST['set_uni_autotoolpass_gc']),
							'GameName' => $_POST['set_uni_gamename'],
							'Reg_RequireEmailConfirm' => (isset($_POST['set_uni_mailactivationneeded']) && $_POST['set_uni_mailactivationneeded'] == 'on' ? 'true' : 'false'),
							'Reg_RecaptchaEnabled' => (isset($_POST['set_const_recaptcha_enable']) && $_POST['set_const_recaptcha_enable'] == 'on' ? 'true' : 'false'),
							'Reg_Recaptcha_Private' => $_POST['set_const_recaptcha_private'],
							'Reg_Recaptcha_Public' => $_POST['set_const_recaptcha_public'],
							'InsertServerMainOpenTime' => time()
						);
						$_Install_RegisterJS = array('ReplaceDomain' => $_POST['set_const_domain']);
						
						$_Install_CreateConfig = parseFile('install_filetpl_config.tpl', $_Install_TPL_Config);
						$_Install_CreateConstants = parseFile('install_filetpl_constants.tpl', $_Install_TPL_Constants);
						$_Install_ReplaceRegisterJS = parseFile('../js/register.js', $_Install_RegisterJS);
						
						$_Install_SaveConfig = file_put_contents('../'.$_Install_ConfigFile.'.php', $_Install_CreateConfig);
						$_Install_SaveConstants = file_put_contents('../includes/constants.php', $_Install_CreateConstants);
						$_Install_SaveRegisterJS = file_put_contents('../js/register.js', $_Install_ReplaceRegisterJS);
						
						if($_Install_SaveConfig && $_Install_SaveConstants)
						{
							if($_Install_SaveRegisterJS)
							{							
								// Now, final try - call every query
								$_Install_QueriesData = array
								(
									'prefix' => $_POST['set_dbconfig_prefix'],
									'Config_GameName' => $_POST['set_uni_gamename'],
									'Config_GameSpeed' => $_Install_Vars['set_uni_gamespeed'] * 2500,
									'Config_FleetSpeed' => $_Install_Vars['set_uni_fleetspeed'] * 2500,
									'Config_ResourceSpeed' => $_Install_Vars['set_uni_resourcespeed'],
									'Config_FleetDebris' => $_Install_Vars['set_uni_fleetdebris'],
									'Config_DefenseDebris' => $_Install_Vars['set_uni_defensedebris'],
									'Config_MissileDebris' => $_Install_Vars['set_uni_missiledebris'],
									'Config_InitialFields' => $_Install_Vars['set_uni_motherfields'],
									'Config_CookieName' => preg_replace('/\s+/', '', strtoupper($_POST['set_uni_gamename'])).'_CK',
									'Config_NoobProtection_Enable' => (isset($_POST['set_uni_noobprt_enable']) && $_POST['set_uni_noobprt_enable'] == 'on' ? '1' : '0'),
									'Config_NoobProtection_BasicLimit_Time' => $_Install_Vars['set_uni_noobprt_basictime'],
									'Config_NoobProtection_BasicLimit_Multiplier' => $_Install_Vars['set_uni_noobprt_basicmultiplier'],
									'Config_NoobProtection_ProtectionRemove' => $_Install_Vars['set_uni_noobprt_remove'],
									'Config_NoobProtection_IdleDaysProtection' => $_Install_Vars['set_uni_noobprt_idledays'],
									'Config_NoobProtection_FirstLoginProtection' => $_Install_Vars['set_uni_noobprt_firstlogin'],
									'Config_AntiFarm_Enable' => (isset($_POST['set_uni_antifarmenable']) && $_POST['set_uni_antifarmenable'] == 'on' ? '1' : '0'),
									'Config_AntiFarm_UserStatsRate' => $_Install_Vars['set_uni_antifarmratio'],
									'Config_AntiFarm_CountTotal' => $_Install_Vars['set_uni_antifarmtotalcount'],
									'Config_AntiFarm_CountPlanet' => $_Install_Vars['set_uni_antifarmplanetcount'],
									'Config_BashLimit_Enabled' => (isset($_POST['set_uni_antibashenable']) && $_POST['set_uni_antibashenable'] == 'on' ? '1' : '0'),
									'Config_BashLimit_Interval' => $_Install_Vars['set_uni_antibashinterval'],
									'Config_BashLimit_CountTotal' => $_Install_Vars['set_uni_antibashtotalcount'],
									'Config_BashLimit_CountPlanet' => $_Install_Vars['set_uni_antibashplanetcount'],
									'Config_TelemetryEnabled' => (isset($_POST['set_uni_telemetryenable']) && $_POST['set_uni_telemetryenable'] == 'on' ? '1' : '0'),
									'AdminUser_name' => $_POST['set_admin_username'],
									'AdminUser_passhash' => md5($_POST['set_admin_password']),
									'AdminUser_email' => $_POST['set_admin_email'],
								);

								$_Install_DoQueries = parseFile('install_database.sql', $_Install_QueriesData);

								$_Install_DoQueries = explode('-- --------------------------------------------------------', $_Install_DoQueries);

								foreach($_Install_DoQueries as $ThisQuery)
								{
									$_Install_QueryResult = mysql_query($ThisQuery);
									if($_Install_QueryResult === false)
									{
										break;
									}
								}

								if($_Install_QueryResult)
								{
									$_Lang['PHP_HideInfoBox'] = '';
									$_Lang['PHP_HideFormBox'] = 'display: none;';
									$_Lang['PHP_InfoBox_Color'] = 'lime';
									$_Lang['PHP_InfoBox_Center'] = 'center';
									$_Lang['PHP_InfoBox_Text'] = $_Lang['InstallSuccess'];

									file_put_contents('lock', '');
								}
								else
								{
									$_Lang['PHP_HideInfoBox'] = '';
									$_Lang['PHP_InfoBox_Color'] = 'red';
									$_Lang['PHP_InfoBox_Center'] = 'center';
									$_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_QueryFail'];
								}
							}
							else
							{
								$_Lang['PHP_HideInfoBox'] = '';
								$_Lang['PHP_InfoBox_Color'] = 'red';
								$_Lang['PHP_InfoBox_Center'] = 'center';
								$_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_FailToSaveRegister'];
							}
						}
						else
						{
							$_Lang['PHP_HideInfoBox'] = '';
							$_Lang['PHP_InfoBox_Color'] = 'red';
							$_Lang['PHP_InfoBox_Center'] = 'center';
							if(!$_Install_SaveConfig && !$_Install_SaveConstants)
							{
								$_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_FailToSaveBothFiles'];
							}
							else if(!$_Install_SaveConfig)
							{
								$_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_FailToSaveConfig'];
							}
							else
							{
								$_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_FailToSaveConstants'];
							}
						}
					}
					else
					{
						$_Lang['PHP_HideInfoBox'] = '';
						$_Lang['PHP_InfoBox_Color'] = 'red';
						$_Lang['PHP_InfoBox_Center'] = 'center';
						$_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_CannotSelectDatabase'];
					}
				}
				else
				{
					$_Lang['PHP_HideInfoBox'] = '';
					$_Lang['PHP_InfoBox_Color'] = 'red';
					$_Lang['PHP_InfoBox_Center'] = 'center';
					$_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_CannotConnectToMySQL'];
				}
			}
			else
			{
				$_Lang['PHP_HideInfoBox'] = '';
				$_Lang['PHP_InfoBox_Color'] = 'red';
				$_Lang['PHP_InfoBox_Center'] = 'center';
				$_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_BadVars'];
			}
		}
		else
		{
			$_Lang['PHP_HideInfoBox'] = '';
			$_Lang['PHP_InfoBox_Color'] = 'red';
			$_Lang['PHP_InfoBox_Center'] = 'center';
			$_Lang['PHP_InfoBox_Text'] = $_Lang['InstallError_EmptyFields'];
		}
	}
}

display();

?>