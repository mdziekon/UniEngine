<?php

function ReturnImage($ImagePath)
{
	if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime($ImagePath)))
	{
		// Use Browser Cache
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($ImagePath)).' GMT', true, 304);
	}
	else
	{
		// Resend new version
		header('Content-Type: image/png');
		header('Content-Length: '.filesize($ImagePath));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($ImagePath)).' GMT', true, 200);
		$Image = fopen($ImagePath, 'r');
		fpassthru($Image);
	}
	die();
}

$CachePath = './cache/img/signatures/';
$_EnginePath = './';

$UID = (isset($_GET['uid']) ? round($_GET['uid']) : 0);
$SigLang = (isset($_GET['lang']) ? $_GET['lang'] : null);
$DefaultLang = 'pl';

if(!in_array($SigLang, array('pl')))
{
	$SigLang = $DefaultLang;
}

if($UID > 0)
{
	$UserFile = "{$CachePath}{$SigLang}/user_".str_pad($UID, 6, 0, STR_PAD_LEFT).'.png';

	if(file_exists($UserFile))
	{
		if(file_exists('./cache/data/last_stats_update.php'))
		{
			include('./cache/data/last_stats_update.php');
		}
		else
		{
			$LastStatsUpdate = 0;
		}
		if(filemtime($UserFile) >= $LastStatsUpdate)
		{
			// Return existing image
			ReturnImage($UserFile);
		}
	}

	// --- Generate new image ---
	// Load DB Driver & Lang
	if(substr(sprintf('%o', fileperms($CachePath.'pl')), -4) != '0777')
	{
		if(!chmod($CachePath.'pl', 0777))
		{
			ReturnImage("{$CachePath}static/signature_{$SigLang}_error4.png");
		}
	}
	define('INSIDE', true);

	if($_SERVER['SERVER_ADDR'] == '127.0.0.1' OR $_SERVER['SERVER_ADDR'] == '::1')
	{
		// We are on Localhost
		define('LOCALHOST', TRUE);
		define('TESTSERVER', FALSE);
	}
	else
	{
		// We are not on Localhost
		define('LOCALHOST', FALSE);
		if($_SERVER['HTTP_HOST'] === GAMEURL_REMOTE_TESTSERVERHOST)
		{
			define('TESTSERVER', TRUE);
		}
		else
		{
			define('TESTSERVER', FALSE);
		}
	} 

	include("{$_EnginePath}includes/db.php");
	include("{$_EnginePath}language/{$SigLang}/generate_sig.lang");

	$Constants = array
	(
		'UserYTop' => 10,
		'AllyYTop' => 34,
		'UniPosYTop' => 3,
		'Width' => 468,
		'Padding' => 6,
	);

	// Get Data
	$UserData = doquery("SELECT `username`, `ally_id` FROM {{table}} WHERE `id` = {$UID} LIMIT 1;", 'users', true, true);
	if(empty($UserData['username']))
	{
		// Throw Error: User don't exist
		copy("{$CachePath}static/signature_{$SigLang}_error2.png", $UserFile);
		ReturnImage($UserFile);
	} 
	if($UserData['ally_id'] > 0)
	{
		$UserAlly = doquery("SELECT `ally_name`, `ally_tag` FROM {{table}} WHERE `id` = {$UserData['ally_id']} LIMIT 1;", 'alliance', true);
		if(!empty($UserAlly['ally_tag']))
		{
			$Texts['Ally'] = "{$_Lang['Ally']}: {$UserAlly['ally_tag']}";
		}
	}
	$UserStat = doquery("SELECT `total_rank`, `total_points` FROM {{table}} WHERE `id_owner` = {$UID} AND `stat_type` = 1 LIMIT 1;", 'statpoints', true);
	if($UserStat['total_rank'] <= 0)
	{
		// Throw Error: Stats not calculated
		copy("{$CachePath}static/signature_{$SigLang}_error3.png", $UserFile);
		ReturnImage($UserFile);
	} 

	// Generate Texts
	if(strlen($UserData['username']) > 20)
	{
		$UserData['username'] = substr($UserData['username'], 0, 20).'...';
	}
	$Texts['User'] = $UserData['username'];
	$Texts['Points'] = number_format($UserStat['total_points'], 0, '', '.').' '.$_Lang['Points'];
	$Texts['Position'] = $_Lang['Position'].': '.number_format($UserStat['total_rank'], 0, '', '.');
	$Texts['Uni'] = $_Lang['Universum'];
 
	foreach($Texts as $ThisKey => &$ThisValue)
	{
		if($ThisKey == 'Uni')
		{
			continue;
		}
		$ThisValue = strtoupper($ThisValue);
	}
	
	// Load image and create White Color
	$ImageCopy = imagecreatefrompng("{$CachePath}static/signature_userbg.png");
	$FontLink = "{$CachePath}static/font_roboto_bold.ttf";
	$Colors['white'] = imagecolorallocate($ImageCopy, 255, 255, 255);

	// Calculate UserString Size
	$Size					= imagettfbbox(10, 0, $FontLink, $Texts['User']);
	$Width					= $Size[4] - $Size[0];
	$Height					= ($Size[5] - $Size[1]) * -1;
	$CalcYPos['User']		= $Constants['UserYTop'] + $Height + 1;
	// Calculate AllyString Size
	$Size					= imagettfbbox(10, 0, $FontLink, $Texts['Ally']);
	$Width					= $Size[4] - $Size[0];
	$Height					= ($Size[5] - $Size[1]) * -1;
	$CalcYPos['Ally']		= $Constants['AllyYTop'] + $Height;
	// Calculate PointsString Size
	$Size					= imagettfbbox(10, 0, $FontLink, $Texts['Points']);
	$Width					= $Size[4] - $Size[0];
	$Height					= ($Size[5] - $Size[1]) * -1;
	$CalcYPos['Points']		= $Constants['UserYTop'] + $Height + 1;
	$CalcXPos['Points']		= $Constants['Width'] - $Width - $Constants['Padding'];
	// Calculate PositionString Size
	$Size					= imagettfbbox(10, 0, $FontLink, $Texts['Position']);
	$Width					= $Size[4] - $Size[0];
	$Height					= ($Size[5] - $Size[1]) * -1;
	$CalcYPos['Position']	= $Constants['AllyYTop'] + $Height;
	$CalcXPos['Position']	= $Constants['Width'] - $Width - $Constants['Padding'];
	// Calculate UniString Size
	$Size					= imagettfbbox(10, 0, $FontLink, $Texts['Uni']);
	$Width					= $Size[4] - $Size[0];
	$Height					= ($Size[5] - $Size[1]) * -1;
	$CalcYPos['Uni']		= $Constants['UniPosYTop'] + $Height;
	$CalcXPos['Uni']		= ($Constants['Width'] - $Width) / 2;

	// Insert Texts
	imagettftext($ImageCopy, 10, 0, $Constants['Padding'], $CalcYPos['User'], $Colors['white'], $FontLink, $Texts['User']);
	imagettftext($ImageCopy, 10, 0, $Constants['Padding'], $CalcYPos['Ally'], $Colors['white'], $FontLink, $Texts['Ally']);
	imagettftext($ImageCopy, 10, 0, $CalcXPos['Points'], $CalcYPos['Points'], $Colors['white'], $FontLink, $Texts['Points']);
	imagettftext($ImageCopy, 10, 0, $CalcXPos['Position'], $CalcYPos['Position'], $Colors['white'], $FontLink, $Texts['Position']);
	imagettftext($ImageCopy, 10, 0, $CalcXPos['Uni'], $CalcYPos['Uni'], $Colors['white'], $FontLink, $Texts['Uni']);

	// Save File
	imagepng($ImageCopy, $UserFile);
	// Return File & die
	ReturnImage($UserFile); 
}
else
{
	// Throw Error: BadData Given
	$FilePath = "{$CachePath}static/signature_{$SigLang}_error1.png";
	ReturnImage($FilePath);
}

?>