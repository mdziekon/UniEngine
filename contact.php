<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_AllowInVacationMode = TRUE;
$_DontForceRulesAcceptance = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

	if(isset($_User['is_banned']) && $_User['is_banned'] == 1)
	{
		$_DontShowMenus = true;
	}

	includeLang('contact');

	$BodyTPL = gettemplate('contact_body');
	$RowsTPL = gettemplate('contact_body_rows');
	$parse = $_Lang;
	$parse['InsertRows'] = '';

	$QrySelectUser = '';
	$QrySelectUser .= "SELECT `username`, `email`, `authlevel` ";
	$QrySelectUser .= "FROM {{table}} ";
	$QrySelectUser .= "WHERE `authlevel` > 0 ORDER BY `authlevel` DESC;";
	$GetData = doquery($QrySelectUser, 'users');

	while($DataRow = mysql_fetch_assoc($GetData))
	{
		$Row['Username']	= $DataRow['username'];
		$Row['Authlabel']	= $_Lang['user_level'][GetAuthLabel($DataRow)];
		$Row['Email']		= $DataRow['email'];
		$parse['InsertRows'] .= parsetemplate($RowsTPL, $Row);
	}

	$page = parsetemplate($BodyTPL, $parse);

	display($page, $_Lang['Title'], false);

?>