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

$_EnginePath = './';

define('INSIDE', true);

include($_EnginePath . 'common.minimal.php');
include($_EnginePath . 'includes/constants.php');
include($_EnginePath . 'includes/unlocalised.php');
include($_EnginePath . 'includes/helpers/_includes.php');

$UID = (isset($_GET['uid']) ? round($_GET['uid']) : 0);
$SigLang = (isset($_GET['lang']) ? $_GET['lang'] : null);

if (!in_array($SigLang, UNIENGINE_LANGS_AVAILABLE)) {
    $SigLang = getDefaultUniLang();
}

$CachePath = './cache/img/signatures';
$CacheStaticsPath = "{$CachePath}/static";
$CacheLangPath = "{$CachePath}/{$SigLang}";

if($UID > 0)
{
    $UserFile = "{$CacheLangPath}/user_".str_pad($UID, 6, 0, STR_PAD_LEFT).'.png';

    if(file_exists($UserFile))
    {
        if(file_exists('./cache/data/last_stats_update.php'))
        {
            include('./cache/data/last_stats_update.php');
            if(!isset($LastStatsUpdate))
            {
                $LastStatsUpdate = 0;
            }
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
    if (substr(sprintf('%o', fileperms($CacheLangPath)), -4) != '0777') {
        ReturnImage("{$CacheStaticsPath}/signature_{$SigLang}_error4.png");
    }

    include("{$_EnginePath}includes/db.php");
    include("{$_EnginePath}language/{$SigLang}/generate_sig.lang");

    $Constants = array
    (
        'UserYTop'        => 10,
        'AllyYTop'        => 34,
        'UniPosYTop'    => 3,
        'Width'            => 468,
        'Padding'        => 6,
    );

    // Get Data
    $SQLResult_GetUserData = doquery("SELECT `username`, `ally_id` FROM {{table}} WHERE `id` = {$UID} LIMIT 1;", 'users');

    if($SQLResult_GetUserData->num_rows != 1)
    {
        // Throw Error: User don't exist
        copy("{$CacheStaticsPath}/signature_{$SigLang}_error2.png", $UserFile);
        ReturnImage($UserFile);
    }

    $UserData = $SQLResult_GetUserData->fetch_assoc();

    $UserStat = doquery("SELECT `total_rank`, `total_points` FROM {{table}} WHERE `id_owner` = {$UID} AND `stat_type` = 1 LIMIT 1;", 'statpoints', true);
    if($UserStat['total_rank'] <= 0)
    {
        // Throw Error: Stats not calculated
        copy("{$CacheStaticsPath}/signature_{$SigLang}_error3.png", $UserFile);
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
    $Texts['Ally'] = null;
    if($UserData['ally_id'] > 0)
    {
        $UserAlly = doquery("SELECT `ally_name`, `ally_tag` FROM {{table}} WHERE `id` = {$UserData['ally_id']} LIMIT 1;", 'alliance', true);
        if(!empty($UserAlly['ally_tag']))
        {
            $Texts['Ally'] = "{$_Lang['Ally']}: {$UserAlly['ally_tag']}";
        }
    }

    foreach($Texts as $ThisKey => &$ThisValue)
    {
        if($ThisKey == 'Uni')
        {
            continue;
        }
        $ThisValue = strtoupper($ThisValue);
    }

    // Load image and create White Color
    $ImageCopy = imagecreatefrompng("{$CacheStaticsPath}/signature_userbg.png");
    $FontLink = "{$CacheStaticsPath}/font_roboto_bold.ttf";
    $Colors['white'] = imagecolorallocate($ImageCopy, 255, 255, 255);

    // Calculate UserString Size
    $Size                    = imagettfbbox(10, 0, $FontLink, $Texts['User']);
    $Width                    = $Size[4] - $Size[0];
    $Height                    = ($Size[5] - $Size[1]) * -1;
    $CalcYPos['User']        = $Constants['UserYTop'] + $Height + 1;
    // Calculate AllyString Size
    $Size                    = imagettfbbox(10, 0, $FontLink, $Texts['Ally']);
    $Width                    = $Size[4] - $Size[0];
    $Height                    = ($Size[5] - $Size[1]) * -1;
    $CalcYPos['Ally']        = $Constants['AllyYTop'] + $Height;
    // Calculate PointsString Size
    $Size                    = imagettfbbox(10, 0, $FontLink, $Texts['Points']);
    $Width                    = $Size[4] - $Size[0];
    $Height                    = ($Size[5] - $Size[1]) * -1;
    $CalcYPos['Points']        = $Constants['UserYTop'] + $Height + 1;
    $CalcXPos['Points']        = $Constants['Width'] - $Width - $Constants['Padding'];
    // Calculate PositionString Size
    $Size                    = imagettfbbox(10, 0, $FontLink, $Texts['Position']);
    $Width                    = $Size[4] - $Size[0];
    $Height                    = ($Size[5] - $Size[1]) * -1;
    $CalcYPos['Position']    = $Constants['AllyYTop'] + $Height;
    $CalcXPos['Position']    = $Constants['Width'] - $Width - $Constants['Padding'];
    // Calculate UniString Size
    $Size                    = imagettfbbox(10, 0, $FontLink, $Texts['Uni']);
    $Width                    = $Size[4] - $Size[0];
    $Height                    = ($Size[5] - $Size[1]) * -1;
    $CalcYPos['Uni']        = $Constants['UniPosYTop'] + $Height;
    $CalcXPos['Uni']        = ($Constants['Width'] - $Width) / 2;

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
    $FilePath = "{$CacheStaticsPath}/signature_{$SigLang}_error1.png";
    ReturnImage($FilePath);
}

?>
