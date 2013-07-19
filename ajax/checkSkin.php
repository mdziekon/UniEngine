<?

error_reporting(!E_ALL);

function checkNetFile($URL)
{
	$File = file_get_contents($URL.'formate.css');
	$Status = $http_response_header[0];
	if(strstr($Status, '200 OK') === false)
	{
		if(strstr($Status, '301 Moved Permanently') !== false OR strstr($Status, '302 Found') !== false OR strstr($Status, '304 Not Modified'))
		{
			if(strstr($http_response_header[9], '200 OK') === false)
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

$SkinPath = strip_tags(trim($_POST['skin_path']));
if(strstr($SkinPath, 'http://') === false AND strstr($SkinPath, 'www.') === false)
{
	$SkinPath = ltrim($SkinPath, '/');
	if(substr($SkinPath, strlen($SkinPath) - 1) != '/')
	{
		$SkinPath .= '/';
	}
	if(!file_exists('../'.$SkinPath.'index.php'))
	{
		$Return = '1';
	}
	else
	{
		$Return = '2';
	}
}
else
{
	$Return = '0';
	if(strstr($SkinPath, 'http://') === false AND strstr($SkinPath, 'www.') !== false)
	{
		$SkinPath = str_replace('www.', 'http://', $SkinPath);
	}
	$FileCheck = checkNetFile($SkinPath);
	if($FileCheck)
	{
		$Return = '2';
	}
}

echo $Return;

?>