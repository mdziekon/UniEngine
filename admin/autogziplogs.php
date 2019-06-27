<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

includeLang('admin');
includeLang('admin/autogziplogs');

$isAuthorised = false;

if (
    isset($_User['id']) &&
    $_User['id'] > 0 &&
    CheckAuth('programmer')
) {
    $isAuthorised = true;
}

if (
    (
        !isset($_User['id']) ||
        $_User['id'] <= 0
    ) &&
    !empty(AUTOTOOL_ZIPLOGS_PASSWORDHASH) &&
    !empty($_GET['pass']) &&
    md5($_GET['pass']) == AUTOTOOL_ZIPLOGS_PASSWORDHASH
) {
    $isAuthorised = true;
}

if (!$isAuthorised) {
    AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);

    die();
}

$FilesZipped = 0;
$StartTime = microtime(true);

$TodayDate = date('Y_m_d', mktime(0, 0, 1) - (12 * TIME_HOUR));
$TodayLogsDir = 'logs_'.$TodayDate;
// First, GZIP all game Logs and then move them to proper directory
$DirArray = false;
if(file_exists('../action_logs/'))
{
    $DirsCompressed[] = 'action_logs';
    $DirList = scandir('../action_logs/');

    foreach($DirList as $FileName)
    {
        if($FileName != '.' AND $FileName != '..' AND $FileName != $TodayLogsDir AND strstr($FileName, '.php') === FALSE)
        {
            $DirArray[] = $FileName;
        }
    }

    if(!empty($DirArray))
    {
        if(!file_exists('../action_logs/'.$TodayLogsDir))
        {
            mkdir('../action_logs/'.$TodayLogsDir);
        }
        foreach($DirArray as $UserID)
        {
            $PlainFilename = 'Log_U_'.$UserID.'_D_'.$TodayDate.'.php';
            $FileName = '../action_logs/'.$UserID.'/Log_U_'.$UserID.'_D_'.$TodayDate.'.php';
            if(file_exists($FileName))
            {
                // Get FileData
                $fp = fopen($FileName, 'r');
                $data = fread ($fp, filesize($FileName));
                fclose($fp);
                // Create Archive
                $zp = gzopen($FileName.'.gz', 'w9');
                gzwrite($zp, $data);
                gzclose($zp);
                // Delete OldFile
                unlink($FileName);
                // Move new file to proper directory (today logs dir)
                rename($FileName.'.gz', '../action_logs/'.$TodayLogsDir.'/'.$PlainFilename.'.gz');
                // Increase Counter
                $FilesZipped += 1;
            }
        }
    }
}
// Second, GZIP all Admin Logs
$DirArray = false;
if(file_exists('./action_logs/'))
{
    $DirsCompressed[] = 'admin/action_logs';
    $DirList = scandir('./action_logs/');

    foreach($DirList as $FileName)
    {
        if($FileName != '.' AND $FileName != '..' AND strstr($FileName, '.php') === FALSE)
        {
            $DirArray[] = $FileName;
        }
    }

    if(!empty($DirArray))
    {
        if(!file_exists('./action_logs/'.$TodayLogsDir))
        {
            mkdir('./action_logs/'.$TodayLogsDir);
        }
        foreach($DirArray as $UserID)
        {
            $PlainFilename = 'Log_U_'.$UserID.'_D_'.$TodayDate.'.php';
            $FileName = './action_logs/'.$UserID.'/Log_U_'.$UserID.'_D_'.$TodayDate.'.php';
            if(file_exists($FileName))
            {
                // Get FileData
                $fp = fopen($FileName, 'r');
                $data = fread ($fp, filesize($FileName));
                fclose($fp);
                // Create Archive
                $zp = gzopen($FileName.'.gz', 'w9');
                gzwrite($zp, $data);
                gzclose($zp);
                // Delete OldFile
                unlink($FileName);
                // Move new file to proper directory (today logs dir)
                rename($FileName.'.gz', './action_logs/'.$TodayLogsDir.'/'.$PlainFilename.'.gz');
                // Increase Counter
                $FilesZipped += 1;
            }
        }
    }
}

$EndTime = microtime(true);

if(empty($DirsCompressed))
{
    $DirsCompressed[] = $_Lang['AutoGZipLogs_NoDirs'];
}

AdminMessage(sprintf($_Lang['AutoGZipLogs_Success'], $FilesZipped, sprintf('%0.6f', $EndTime - $StartTime), implode(', ', $DirsCompressed)), $_Lang['AutoGZipLogs_Title']);

?>
