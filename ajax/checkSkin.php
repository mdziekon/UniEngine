<?php

$_EnginePath = '../';
include_once("{$_EnginePath}includes/unlocalised.php");
include_once("{$_EnginePath}modules/settings/_includes.php");

use UniEngine\Engine\Modules\Settings;

function checkNetFile($URL)
{
    $File = @file_get_contents($URL.'formate.css');
    if($File === false)
    {
        return false;
    }
    $Status = (isset($http_response_header) ? $http_response_header[0] : '');
    if(strstr($Status, '200 OK') === false)
    {
        if(strstr($Status, '301 Moved Permanently') !== false || strstr($Status, '302 Found') !== false || strstr($Status, '304 Not Modified'))
        {
            if(!isset($http_response_header[9]) || strstr($http_response_header[9], '200 OK') === false)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return false;
        }
    }
    else
    {
        return true;
    }
}

$SkinPath = (isset($_POST['skin_path']) ? strip_tags(trim($_POST['skin_path'])) : null);
if (!Settings\Utils\Helpers\isExternalUrl($SkinPath)) {
    $SkinPath = ltrim($SkinPath, '/');
    if(substr($SkinPath, strlen($SkinPath) - 1) != '/')
    {
        $SkinPath .= '/';
    }

    $availableSkins = Settings\Utils\Helpers\getAvailableSkins([ 'rootDir' => $_EnginePath ]);
    $isAvailableSkin = array_find($availableSkins, function ($skinDetails) use ($SkinPath) {
        return $skinDetails['path'] === $SkinPath;
    });

    if (!$isAvailableSkin) {
        $Return = '1';
    } else {
        $Return = '2';
    }
}
else
{
    $Return = '0';
    if (Settings\Utils\Helpers\isValidExternalUrl($SkinPath)) {
        if (
            !Settings\Utils\Helpers\hasHttpProtocol($SkinPath) &&
            Settings\Utils\Helpers\hasWWWPart($SkinPath)
        ) {
            $SkinPath = Settings\Utils\Helpers\completeWWWUrl($SkinPath);
        }

        $FileCheck = checkNetFile($SkinPath);
        if($FileCheck)
        {
            $Return = '2';
        }
    }
}

echo $Return;

?>
