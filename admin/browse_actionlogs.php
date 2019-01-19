<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';

$_EnginePath= './../';
include($_EnginePath.'common.php');

includeLang('admin');

if(CheckAuth('supportadmin'))
{
    // Initialize Functions
    function humanSize($Mem)
    {
        if($Mem > 1048576)
        {
            $Unit = 1048576;
            $UnitName = 'MB';
        }
        else if($Mem > 1024)
        {
            $Unit = 1024;
            $UnitName = 'KB';
        }
        else
        {
            $Unit = 1;
            $UnitName = 'B';
            return $Mem.' '.$UnitName;
        }
        return sprintf('%0.4f', ($Mem/$Unit)).' '.$UnitName;
    }

    function PostParser($Array, $ReturnState = false)
    {
        global $PostParsed;
        static $Depth, $DeepParse, $State;
        if($Depth <= 0)
        {
            $Depth = 0;
        }
        if($ReturnState)
        {
            $State = array('arr' => false, 'cou' => 0);
        }

        foreach($Array as $Key => $Data)
        {
            if((array)$Data === $Data)
            {
                if($ReturnState)
                {
                    $State['arr'] = true;
                }
                $State['cou'] += 1;
                $Depth += 1;
                PostParser($Data);
                if(is_string($Key))
                {
                    $Key = "'{$Key}'";
                }
                $Start = "<b class=\"p_b\"><br/></b><b class=\"post_a\"><b class=\"post_a_\">{$Key}[]</b><b class=\"post_a_ post_bo\">{</b><b class=\"pos pos_sel\" style=\"left: ".(8 * $Depth)."px;\">";
                $End = "</b><b class=\"post_a_ post_bc\">}</b></b>";
                if($Depth == 1)
                {
                    $PostParsed[] = $Start.implode(' <b class="p_a">&amp;</b> ', $DeepParse[$Depth]).$End;
                    unset($DeepParse[$Depth]);
                }
                else
                {
                    $DeepParse[($Depth-1)][] = $Start.implode(' <b class="p_a">&amp;</b> ', $DeepParse[$Depth]).$End;
                    unset($DeepParse[$Depth]);
                }
                $Depth -= 1;
            }
            else
            {
                $State['cou'] += 1;
                if(is_string($Key))
                {
                    $Key = "'{$Key}'";
                }
                if(is_string($Data))
                {
                    $Data = "'{$Data}'";
                }
                $ParseIt = "<b class=\"get\"><b class=\"gn\">{$Key}</b><b class=\"ge\">=</b><b class=\"gv\">".valueParser($Data)."</b></b>";
                if($Depth > 0)
                {
                    $DeepParse[$Depth][] = $ParseIt;
                }
                else
                {
                    $PostParsed[] = $ParseIt;
                }
            }
        }

        if($ReturnState)
        {
            $DeepParse = false;
            return $State;
        }
    }

    function valueParser($Value)
    {
        $Length = strlen($Value);
        if($Length > 40)
        {
            $Part1 = substr($Value, 0, 10);
            $Part2 = substr($Value, 10, $Length - 20);
            $Part3 = substr($Value, -10);
            return "<span class=\"strFold\">{$Part1}<span class=\"doUnf\">(...)</span><span class=\"folded\">{$Part2}</span>{$Part3}</span>";
        }
        return $Value;
    }

    function walkPostArray($Array)
    {
        static $Return = array(), $CurrentPath = '', $CurrentLevel = 1;

        $PreviousPath = $CurrentPath;
        foreach($Array as $Key => $Value)
        {
            if($CurrentLevel > 1)
            {
                $CurrentPath = "{$PreviousPath}[{$Key}]";
            }
            else
            {
                $CurrentPath = $Key;
            }
            if((array)$Value === $Value)
            {
                $CurrentLevel += 1;
                walkPostArray($Value);
                $CurrentLevel -= 1;
            }
            else
            {
                $Return[] = array('name' => $CurrentPath, 'value' => $Value);
            }
        }
        $CurrentPath = $PreviousPath;

        if($CurrentLevel <= 1)
        {
            $TempReturn = $Return;
            $Return = array();
            $CurrentPath = '';
            $CurrentLevel = 1;
            return $TempReturn;
        }
    }

    function parseTimestamp($Timestamp)
    {
        $Temp['H'] = floor($Timestamp / 3600);
        $Timestamp -= $Temp['H'] * 3600;
        $Temp['M'] = floor($Timestamp / 60);
        $Timestamp -= $Temp['M'] * 60;
        $Temp['S'] = $Timestamp;

        return str_pad($Temp['H'], 2, '0', STR_PAD_LEFT).':'.str_pad($Temp['M'], 2, '0', STR_PAD_LEFT).':'.str_pad($Temp['S'], 2, '0', STR_PAD_LEFT);
    }
    // ------------------------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------------------------

    includeLang('admin/browse_actionlogs');

    $Error = $_Lang['PageTitle'];
    $ThisPage = 'browse_actionlogs.php';
    $Parse = $_Lang;
    $MainTPL = gettemplate('admin/browse_actionlogs_body');
    $RowListTPL = gettemplate('admin/browse_actionlogs_list_row');
    $HeadListTPL = gettemplate('admin/browse_actionlogs_list_header');
    $Row3LogTPL = gettemplate('admin/browse_actionlogs_log_row_3');
    $Row2LogTPL = gettemplate('admin/browse_actionlogs_log_row_2');
    $Row1LogTPL = gettemplate('admin/browse_actionlogs_log_row_1');
    $RowMLogTPL = gettemplate('admin/browse_actionlogs_log_row_multi');
    $HeadLogTPL = gettemplate('admin/browse_actionlogs_log_header');
    $ResendTPL    = gettemplate('admin/browse_actionlogs_resendrequest');

    if(!empty($_POST))
    {
        if(isset($_POST['autoExpandArr']) && $_POST['autoExpandArr'] == 'on')
        {
            $AutoExpandArray = 'true';
            $SetArrCookie = 'on';
        }
        else
        {
            $AutoExpandArray = 'false';
            $SetArrCookie = 'off';
        }
        if(isset($_POST['autoExpandAmp']) && $_POST['autoExpandAmp'] == 'on')
        {
            $AutoExpandAmper = 'true';
            $SetAmpCookie = 'on';
        }
        else
        {
            $AutoExpandAmper = 'false';
            $SetAmpCookie = 'off';
        }
        setcookie('autoExpandArr', $SetArrCookie, time() + 31536000);
        setcookie('autoExpandAmp', $SetAmpCookie, time() + 31536000);
    }
    else
    {
        if($_COOKIE['autoExpandArr'] == 'on')
        {
            $AutoExpandArray = 'true';
        }
        else if($_COOKIE['autoExpandArr'] == 'off' OR empty($_COOKIE['autoExpandArr']))
        {
            $AutoExpandArray = 'false';
        }
        if($_COOKIE['autoExpandAmp'] == 'on')
        {
            $AutoExpandAmper = 'true';
        }
        else if($_COOKIE['autoExpandAmp'] == 'off' OR empty($_COOKIE['autoExpandAmp']))
        {
            $AutoExpandAmper = 'false';
        }
    }
    if($AutoExpandArray == 'true')
    {
        $_Lang['aEArrCheck'] = 'checked';
    }
    if($AutoExpandAmper == 'true')
    {
        $_Lang['aEAmpCheck'] = 'checked';
    }

    $Parse['AutoExpandArray'] = $AutoExpandArray;
    $Parse['AutoExpandAmp'] = $AutoExpandAmper;

    $UID = intval($_GET['uid']);

    if($UID <= 0)
    {
        message($_Lang['Error_BadUID'], $Error);
    }
    $ThisPage = "{$ThisPage}?uid={$UID}";
    $Parse['UID'] = $UID;
    $_Lang['UID'] = $UID;
    $UIDMarker = str_pad($UID, 6, '0', STR_PAD_LEFT);
    $Date = isset($_GET['date']) ? $_GET['date'] : null;

    $LogExists = false;

    if(!empty($Date))
    {
        if(preg_match('/^[0-9\_]{10}$/D', $Date))
        {
            $Packed = '';
            if(isset($_GET['packed']) && $_GET['packed'] === 'true')
            {
                $FilePath = "./../action_logs/logs_{$Date}/Log_U_{$UIDMarker}_D_{$Date}.php.gz";
                $Packed = '&amp;packed=true';
                $GZiped = true;
            }
            else
            {
                $FilePath = "./../action_logs/{$UIDMarker}/Log_U_{$UIDMarker}_D_{$Date}.php";
                $GZiped = false;
            }

            if(file_exists($FilePath))
            {
                $ThisPage = "{$ThisPage}&date={$Date}{$Packed}";

                $Parse['TableColspan'] = 4;
                $LogExists = true;

                $UserData = doquery("SELECT `username` FROM {{table}} WHERE `id` = {$UID} LIMIT 1;", 'users', true);
                $Parse['UserName'] = $UserData['username'];

                $EDate = explode('_', $Date);

                if($GZiped)
                {
                    $LogFile = gzfile($FilePath);
                }
                else
                {
                    $LogFile = file($FilePath);
                }
                unset($LogFile[(count($LogFile) - 1)]);
                unset($LogFile[0]);

                if(!empty($_GET['resendline']))
                {
                    $ResendLineNo = round($_GET['resendline']);
                    if($ResendLineNo >= 0)
                    {
                        if(!empty($LogFile[$ResendLineNo]))
                        {
                            if(preg_match('/^([0-9]{1,5}|[0-9]{2}\:[0-9]{2}\:[0-9]{2})\|([a-zA-Z0-9\.\?\=\;\&\_\/\-\ ]{1,})(\|){0,1}(.*?)$/D', trim($LogFile[$ResendLineNo]), $LogFile[$ResendLineNo]))
                            {
                                $ResendFile = false;
                                $ResendPost = false;

                                if($LogFile[$ResendLineNo][2] == 'R' OR $LogFile[$ResendLineNo][2] == 'reload')
                                {
                                    if(empty($LogFile[$ResendLineNo][4]))
                                    {
                                        $PostReload = true;
                                    }
                                    else
                                    {
                                        $ResendPost = $LogFile[$ResendLineNo][4];
                                    }

                                    $ReverseScanIndex = $ResendLineNo - 1;
                                    while(!empty($LogFile[$ReverseScanIndex]))
                                    {
                                        if(!preg_match('/^([0-9]{1,5}|[0-9]{2}\:[0-9]{2}\:[0-9]{2})\|([a-zA-Z0-9\.\?\=\;\&\_\/\-\ ]{1,})(\|){0,1}(.*?)$/D', trim($LogFile[$ReverseScanIndex]), $LogFile[$ReverseScanIndex]))
                                        {
                                            $ReverseScanIndex -= 1;
                                            continue;
                                        }
                                        if($PostReload === true AND !empty($LogFile[$ReverseScanIndex][4]))
                                        {
                                            $ResendPost = $LogFile[$ReverseScanIndex][4];
                                        }
                                        if($LogFile[$ReverseScanIndex][2] == 'R' OR $LogFile[$ReverseScanIndex][2] == 'reload')
                                        {
                                            $ReverseScanIndex -= 1;
                                        }
                                        else
                                        {
                                            $ResendFile = $LogFile[$ReverseScanIndex][2];
                                            break;
                                        }
                                    }

                                    if(empty($ResendFile))
                                    {
                                        AdminMessage($_Lang['Error_ResendLineBadRevScan'], $_Lang['PageTitle']);
                                    }
                                }
                                else
                                {
                                    $ResendFile = $LogFile[$ResendLineNo][2];
                                    $ResendPost = $LogFile[$ResendLineNo][4];
                                }

                                $GenerateForm['UserInfo'] = $_Lang['Info_RequestProceeding'];
                                $GenerateForm['FilePath'] = $_EnginePath.$ResendFile;
                                if(!empty($ResendPost) AND $ResendPost != 'N')
                                {
                                    $ResendPost = json_decode($ResendPost, true);
                                    $GenerateForm['GenerateInputs'] = walkPostArray($ResendPost);
                                    foreach($GenerateForm['GenerateInputs'] as $InputData)
                                    {
                                        $InputData['value'] = stripslashes($InputData['value']);
                                        $GenerateForm['GenerateInputsArray'][] = "<textarea class=\"hide\" name=\"{$InputData['name']}\">{$InputData['value']}</textarea>";
                                    }
                                    $GenerateForm['GenerateInputs'] = implode('', $GenerateForm['GenerateInputsArray']);
                                }
                                $Page = parsetemplate($ResendTPL, $GenerateForm);
                                $_DontShowMenus = true;
                                display($Page, $_Lang['PageTitle'], false, true);
                            }
                            else
                            {
                                AdminMessage($_Lang['Error_ResendLineBadFormat'], $_Lang['PageTitle']);
                            }
                        }
                        else
                        {
                            AdminMessage($_Lang['Error_ResendLineEmpty'], $_Lang['PageTitle']);
                        }
                    }
                    else
                    {
                        AdminMessage($_Lang['Error_ResendBadLine'], $_Lang['PageTitle']);
                    }
                }

                // ------------------------------
                // Log Parser -------------------
                // ------------------------------
                $LogBreak = 100;

                // First Time
                if(substr($LogFile[1], 0, 1) === '[')
                {
                    $ExplodeFirst = $LogFile[2];
                }
                else
                {
                    $ExplodeFirst = $LogFile[1];
                }
                $ExplodeFirst = explode('|', $ExplodeFirst);
                if(strlen($ExplodeFirst[0]) == 8)
                {
                    $ExplodeFirst[0] = explode(':', $ExplodeFirst[0]);
                    $_Lang['FromHour'] = $ExplodeFirst[0][0];
                    $_Lang['FromMin'] = $ExplodeFirst[0][1];
                    $_Lang['FromSec'] = $ExplodeFirst[0][2];
                }
                else
                {
                    $TempSec = $ExplodeFirst[0];
                    $TempH = floor($TempSec/3600);
                    $TempSec -= $TempH * 3600;
                    $TempM = floor($TempSec/60);
                    $TempSec -= $TempM * 60;
                    $_Lang['FromHour'] = $TempH;
                    $_Lang['FromMin'] = $TempM;
                    $_Lang['FromSec'] = $TempSec;
                }
                // Last Time
                $LogKeys = array_keys($LogFile);
                $ExplodeLast = $LogFile[max($LogKeys)];
                $ExplodeLast = explode('|', $ExplodeLast);
                if(strlen($ExplodeLast[0]) == 8)
                {
                    $ExplodeLast[0] = explode(':', $ExplodeLast[0]);
                    $_Lang['ToHour'] = $ExplodeLast[0][0];
                    $_Lang['ToMin'] = $ExplodeLast[0][1];
                    $_Lang['ToSec'] = $ExplodeLast[0][2];
                }
                else
                {
                    $TempSec = $ExplodeLast[0];
                    $TempH = floor($TempSec/3600);
                    $TempSec -= $TempH * 3600;
                    $TempM = floor($TempSec/60);
                    $TempSec -= $TempM * 60;
                    $_Lang['ToHour'] = $TempH;
                    $_Lang['ToMin'] = $TempM;
                    $_Lang['ToSec'] = $TempSec;
                }
                $FromMinimal = ($_Lang['FromHour'] * 3600) + ($_Lang['FromMin'] * 60) + $_Lang['FromSec'];
                $ToMaximal = ($_Lang['ToHour'] * 3600) + ($_Lang['ToMin'] * 60) + $_Lang['ToSec'];
                $_Lang['FromHour'] = str_pad($_Lang['FromHour'], 2, '0', STR_PAD_LEFT);
                $_Lang['ToHour'] = str_pad($_Lang['ToHour'], 2, '0', STR_PAD_LEFT);
                $_Lang['FromMin'] = str_pad($_Lang['FromMin'], 2, '0', STR_PAD_LEFT);
                $_Lang['ToMin'] = str_pad($_Lang['ToMin'], 2, '0', STR_PAD_LEFT);
                $_Lang['FromSec'] = str_pad($_Lang['FromSec'], 2, '0', STR_PAD_LEFT);
                $_Lang['ToSec'] = str_pad($_Lang['ToSec'], 2, '0', STR_PAD_LEFT);

                // POST Filtering
                $FilterOn = false;
                $OneFilterOn = false;
                if(isset($_POST['filter_time']) && $_POST['filter_time'] == 'on')
                {
                    $OneFilterOn = true;
                    $JoinFrom = ($_POST['from_hour'] * 3600) + ($_POST['from_min'] * 60) + $_POST['from_sec'];
                    if($JoinFrom > $FromMinimal)
                    {
                        $_Lang['FromHour'] = str_pad($_POST['from_hour'], 2, '0', STR_PAD_LEFT);
                        $_Lang['FromMin'] = str_pad($_POST['from_min'], 2, '0', STR_PAD_LEFT);
                        $_Lang['FromSec'] = str_pad($_POST['from_sec'], 2, '0', STR_PAD_LEFT);
                        $EnableFromFilter = $JoinFrom;
                        $FilterOn = true;
                    }
                    else
                    {
                        $EnableFromFilter = 0;
                    }
                    $JoinTo = ($_POST['to_hour'] * 3600) + ($_POST['to_min'] * 60) + $_POST['to_sec'];
                    if($JoinTo < $ToMaximal)
                    {
                        $_Lang['ToHour'] = str_pad($_POST['to_hour'], 2, '0', STR_PAD_LEFT);
                        $_Lang['ToMin'] = str_pad($_POST['to_min'], 2, '0', STR_PAD_LEFT);
                        $_Lang['ToSec'] = str_pad($_POST['to_sec'], 2, '0', STR_PAD_LEFT);
                        $EnableToFilter = $JoinTo;
                        $FilterOn = true;
                    }
                    else
                    {
                        $EnableToFilter = 86400;
                    }
                    $_Lang['Set_filter_time_checked'] = 'checked';
                }
                else
                {
                    $EnableFromFilter = 0;
                    $EnableToFilter = 86400;
                }
                $FilterPlaceOn = false;
                if(isset($_POST['filter_place']) && $_POST['filter_place'] == 'on')
                {
                    if(!empty($_POST['filter_place_query']))
                    {
                        if(in_array($_POST['filter_place_type'], array(1, 2, 3)))
                        {
                            $OneFilterOn = true;
                            $FilterPlaceOn = true;
                            $FilterPlaceType = $_POST['filter_place_type'];
                            $Query = $_POST['filter_place_query'];
                            if($FilterPlaceType == 1)
                            {
                                $FilterPlaceFunc = function($String) use($Query)
                                {
                                    if($String == $Query)
                                    {
                                        return true;
                                    }
                                    return false;
                                };
                            }
                            else if($FilterPlaceType == 2)
                            {
                                $FilterPlaceFunc = function($String) use($Query)
                                {
                                    if($String != $Query)
                                    {
                                        return true;
                                    }
                                    return false;
                                };
                            }
                            else if($FilterPlaceType == 3)
                            {
                                $FilterPlaceFunc = function($String) use($Query)
                                {
                                    return (bool)@preg_match($Query, $String);
                                };
                            }
                            $_Lang['Set_filter_place_type_'.$_POST['filter_place_type'].'_checked'] = 'checked';
                        }
                        $_Lang['Set_filter_place_query'] = $_POST['filter_place_query'];
                    }
                    $_Lang['Set_filter_place_checked'] = 'checked';
                }

                // Pagination
                $CurrentPage = isset($_GET['page']) ? intval($_GET['page']) : 0;
                if($CurrentPage > 1)
                {
                    $AfterFilter = false;
                    $EnablePageFilter = (($CurrentPage - 1) * $LogBreak) + 1;
                }
                else
                {
                    $CurrentPage = 1;
                    $AfterFilter = true;
                    $EnablePageFilter = 1;
                }

                if($OneFilterOn === false)
                {
                    $Parse['FilteringDisplay'] = ' display: none;';
                }

                $Counters_LogsShowed = 0;
                $Counters_LogsTotalCount = 0;
                $Counters_LogsFilteredCount = 0;
                $Counters_IPShowed = 0;
                $Counters_BrowserShowed = 0;
                $Counters_ScreenInfoShowed = 0;
                $Counters_BadFormatLines = 0;

                $Switch_LastIPShowed = false;
                $Switch_LastBrowserShowed = false;
                $Switch_LastScreenInfoShowed = false;
                $Switch_LastFormatErrorShowed = false;
                $Switch_CanBeMerged = false;
                $Switch_FullReload = false;
                $Switch_BlockErrors = false;
                $Switch_LastLineHadFormatError = false;

                $Indicator_LeftmostLimitTouched = false;
                $Indicator_RightmostLimitReached = false;
                $Indicator_PageLimitTouched = false;
                $Indicator_OnScreenLimitReached = false;
                $Indicator_LastSkippedByPlaceFilter = false;

                $Data_LastIP = false;
                $Data_LastBrowser = false;
                $Data_LastScreenInfo = false;
                $Data_LastPage = false;
                $Data_LastPOST = false;
                $Data_LastIPChangeTime = false;
                $Data_LastBrowserChangeTime = false;
                $Data_LastScreenInfoChangeTime = false;
                $Data_LastFormatErrorTime = false;
                $Data_ParseRows = array();
                $Data_LastParsedRowsIndex = 0;
                foreach($LogFile as $LineNo => $LineData)
                {
                    $_This = array();
                    $Data_ThisParsedRowsIndex = $Data_LastParsedRowsIndex + 1;
                    if(substr($LineData, 0, 1) === '[')
                    {
                        // This is IP/Browser Line
                        if(!$Indicator_RightmostLimitReached)
                        {
                            $LineData = explode('|', str_replace(array('[', ']'), '', $LineData));
                            foreach($LineData as $LineDataSplit)
                            {
                                $FirstLetter = substr($LineDataSplit, 0, 1);
                                if($FirstLetter === 'B')
                                {
                                    // This is Browser Data (Marked as "B")
                                    $Data_LastBrowser = stripslashes(substr($LineDataSplit, 1));
                                    $Data_LastBrowserChangeTime = false;
                                    $Switch_LastBrowserShowed = false;
                                }
                                else if($FirstLetter === 'S')
                                {
                                    // This is ScreenResolution Data (Marked as "S")
                                    $Data_LastScreenInfo = str_replace('_', 'x', substr($LineDataSplit, 1));
                                    $Data_LastScreenInfoChangeTime = false;
                                    $Switch_LastScreenInfoShowed = false;
                                }
                                else
                                {
                                    // This is IP Data (Marked as "A" or "other")
                                    if($FirstLetter === 'A')
                                    {
                                        $Data_LastIP = substr($LineDataSplit, 1);
                                    }
                                    else
                                    {
                                        $Data_LastIP = $LineDataSplit;
                                    }
                                    $Data_LastIPChangeTime = false;
                                    $Switch_LastIPShowed = false;
                                }
                            }
                        }
                    }
                    else
                    {
                        // This is Action Line

                        // Check if Lineformat is correct
                        $LineData = trim($LineData);
                        if(!preg_match('/^([0-9]{1,5}|[0-9]{2}\:[0-9]{2}\:[0-9]{2})\|([a-zA-Z0-9\.\?\=\;\&\_\/\-\ ]{1,})(\|){0,1}(.*?)$/D', $LineData, $LineData))
                        {
                            if(!$Switch_LastLineHadFormatError)
                            {
                                $Counters_BadFormatLines = 1;
                            }
                            else
                            {
                                $Counters_BadFormatLines += 1;
                            }
                            $Data_LastFormatErrorTime = false;
                            $Switch_LastFormatErrorShowed = false;
                            $Switch_LastLineHadFormatError = true;
                            continue;
                        }
                        $Counters_LogsTotalCount += 1;
                        if(!$Indicator_RightmostLimitReached)
                        {
                            // Parse Time
                            if(strstr($LineData[1], ':'))
                            {
                                $LineData[1] = explode(':', $LineData[1]);
                                $_This['Time'] = ($LineData[1][0] * 3600) + ($LineData[1][1] * 60) + $LineData[1][2];
                            }
                            else
                            {
                                $_This['Time'] = $LineData[1];
                            }

                            // Filter Time - Rightmost Limit (ToTime Limit)
                            if($EnableToFilter < $_This['Time'])
                            {
                                $Indicator_RightmostLimitReached = true;
                                if(!$Switch_BlockErrors)
                                {
                                    $Data_ParseRows[$Data_ThisParsedRowsIndex]['Data'] = sprintf($_Lang['Log_ReachedFilter'], $_Lang['ToHour'], $_Lang['ToMin'], $_Lang['ToSec']);
                                    $Data_ParseRows[$Data_ThisParsedRowsIndex]['Type'] = '1';
                                    $Switch_BlockErrors = true;
                                }
                                $Switch_LastLineHadFormatError = false;
                                continue;
                            }
                            if(!$Switch_LastFormatErrorShowed AND $Data_LastFormatErrorTime === false)
                            {
                                $Data_LastFormatErrorTime = $_This['Time'];
                            }
                            if(!$Switch_LastIPShowed AND $Data_LastIPChangeTime === false)
                            {
                                $Data_LastIPChangeTime = $_This['Time'];
                            }
                            if(!$Switch_LastBrowserShowed AND $Data_LastBrowserChangeTime === false)
                            {
                                $Data_LastBrowserChangeTime = $_This['Time'];
                            }
                            if(!$Switch_LastScreenInfoShowed AND $Data_LastScreenInfoChangeTime === false)
                            {
                                $Data_LastScreenInfoChangeTime = $_This['Time'];
                            }

                            // Parse File
                            $_This['File'] = $LineData[2];
                            $Switch_FullReload = false;
                            if($_This['File'] == 'reload' OR $_This['File'] == 'R')
                            {
                                $_This['File'] = $Data_LastPage;
                                if(empty($LineData[3]))
                                {
                                    $Switch_FullReload = true;
                                }
                                if($Counters_BadFormatLines == 0 AND $Switch_LastBrowserShowed === true AND $Switch_LastIPShowed === true AND $Switch_LastScreenInfoShowed === true)
                                {
                                    $Switch_CanBeMerged = true;
                                }
                                if($Indicator_LastSkippedByPlaceFilter)
                                {
                                    $Switch_LastLineHadFormatError = false;
                                    continue;
                                }
                            }
                            else
                            {
                                $Data_LastPage = $_This['File'];
                                $Switch_CanBeMerged = false;
                            }
                            // Filter Place
                            if($FilterPlaceOn === true AND $FilterPlaceFunc($_This['File']) === false)
                            {
                                $Indicator_LastSkippedByPlaceFilter = true;
                                $Switch_LastLineHadFormatError = false;
                                continue;
                            }
                            $Indicator_LastSkippedByPlaceFilter = false;

                            // Filter Time - Leftmost Limit (FromTime Limit)
                            if(!$Indicator_LeftmostLimitTouched)
                            {
                                if($_This['Time'] < $EnableFromFilter)
                                {
                                    $Switch_LastLineHadFormatError = false;
                                    continue;
                                }
                                $Indicator_LeftmostLimitTouched = true;
                            }

                            $Counters_LogsFilteredCount += 1;
                            // Filter Page Limit
                            if(!$Indicator_PageLimitTouched)
                            {
                                if($Counters_LogsFilteredCount < $EnablePageFilter)
                                {
                                    $Switch_LastLineHadFormatError = false;
                                    continue;
                                }
                                $Indicator_PageLimitTouched = true;
                            }

                            // Filter OnScreen Limit
                            if(!$Indicator_OnScreenLimitReached)
                            {
                                if($Counters_LogsShowed >= $LogBreak)
                                {
                                    $Indicator_OnScreenLimitReached = true;
                                    if(!$Switch_BlockErrors)
                                    {
                                        $Data_ParseRows[$Data_ThisParsedRowsIndex]['Data'] = sprintf($_Lang['Log_TooManyLines'], prettyNumber($LogBreak));
                                        $Data_ParseRows[$Data_ThisParsedRowsIndex]['Type'] = '1';
                                        $Switch_BlockErrors = true;
                                    }
                                    $Switch_LastLineHadFormatError = false;
                                    continue;
                                }
                            }
                            else
                            {
                                $Switch_LastLineHadFormatError = false;
                                continue;
                            }

                            // Now process the Line (all filters passed)
                            $_This['Args_POST'] = $LineData[4];
                            if($Switch_CanBeMerged AND ($_This['Args_POST'] !== $Data_LastPOST AND !$Switch_FullReload))
                            {
                                $Switch_CanBeMerged = false;
                            }

                            if(!$Switch_CanBeMerged)
                            {
                                $_This['FileExplode'] = explode('?', $_This['File']);
                                $_This['File'] = $_This['FileExplode'][0];
                                $_This['Args_GET'] = isset($_This['FileExplode'][1]) ? $_This['FileExplode'][1] : null;
                                $Data_LastPOST = $LineData[4];
                                if($_This['Args_POST'] == 'N')
                                {
                                    $_This['Args_POST'] = null;
                                }

                                if($Counters_BadFormatLines > 0 OR !$Switch_LastIPShowed OR !$Switch_LastBrowserShowed OR !$Switch_LastScreenInfoShowed)
                                {
                                    // Show InfoRows
                                    $_InfoRowsData = array();

                                    if(!$Switch_LastIPShowed)
                                    {
                                        if($Counters_IPShowed == 0)
                                        {
                                            $ThisMessage = sprintf($_Lang['Log_IPisCurentlyX'], $Data_LastIP);
                                        }
                                        else
                                        {
                                            $ThisMessage = sprintf($_Lang['Log_IPChangedToX'], $Data_LastIP);
                                        }
                                        $_InfoRowsData[$Data_LastIPChangeTime][] = $ThisMessage;
                                        $Counters_IPShowed += 1;
                                    }
                                    if(!$Switch_LastBrowserShowed)
                                    {
                                        if($Counters_BrowserShowed == 0)
                                        {
                                            $ThisMessage = sprintf($_Lang['Log_BrowserCurrent'], $Data_LastBrowser);
                                        }
                                        else
                                        {
                                            $ThisMessage = sprintf($_Lang['Log_BrowserChange'], $Data_LastBrowser);
                                        }
                                        $_InfoRowsData[$Data_LastBrowserChangeTime][] = $ThisMessage;
                                        $Counters_IPShowed += 1;
                                    }
                                    if(!$Switch_LastScreenInfoShowed)
                                    {
                                        if($Counters_ScreenInfoShowed == 0)
                                        {
                                            $ThisMessage = sprintf($_Lang['Log_ScreenCurrent'], $Data_LastScreenInfo);
                                        }
                                        else
                                        {
                                            $ThisMessage = sprintf($_Lang['Log_ScreenChange'], $Data_LastScreenInfo);
                                        }
                                        $_InfoRowsData[$Data_LastScreenInfoChangeTime][] = $ThisMessage;
                                        $Counters_ScreenInfoShowed += 1;
                                    }
                                    if($Counters_BadFormatLines > 0 AND $Switch_LastLineHadFormatError)
                                    {
                                        $_InfoRowsData[$Data_LastFormatErrorTime][] = sprintf($_Lang['Log_BadFormatLines'], prettyNumber($Counters_BadFormatLines));
                                    }

                                    $_InfoRowsCount = count($_InfoRowsData);
                                    reset($_InfoRowsData);
                                    if($_InfoRowsCount > 1 OR key($_InfoRowsData) != $_This['Time'])
                                    {
                                        ksort($_InfoRowsData);

                                        foreach($_InfoRowsData as $Timestamp => $InfoRowData)
                                        {
                                            if($Timestamp == $_This['Time'])
                                            {
                                                continue;
                                            }
                                            $Data_ParseRows[$Data_ThisParsedRowsIndex]['Type'] = '2';
                                            $Data_ParseRows[$Data_ThisParsedRowsIndex]['Time'][] = parseTimestamp($Timestamp);
                                            $Data_ParseRows[$Data_ThisParsedRowsIndex]['Data'] = $InfoRowData;
                                            $Data_ThisParsedRowsIndex += 1;
                                            $Data_LastParsedRowsIndex += 1;
                                            unset($_InfoRowsData[$Timestamp]);
                                        }
                                    }
                                    if(!empty($_InfoRowsData))
                                    {
                                        $Data_ParseRows[$Data_ThisParsedRowsIndex]['Type'] = 'M';
                                        $Data_ParseRows[$Data_ThisParsedRowsIndex]['Infos'] = implode('<br/>', $_InfoRowsData[$_This['Time']]);
                                    }
                                    else
                                    {
                                        $Data_ParseRows[$Data_ThisParsedRowsIndex]['Type'] = '3';
                                    }

                                    $Data_LastIPChangeTime = false;
                                    $Data_LastBrowserChangeTime = false;
                                    $Data_LastScreenInfoChangeTime = false;
                                    $Data_LastFormatErrorTime = false;
                                }
                                else
                                {
                                    $Data_ParseRows[$Data_ThisParsedRowsIndex]['Type'] = '3';
                                }

                                // GET & POST Parser
                                if(!empty($_This['Args_GET']) OR !empty($_This['Args_POST'])){
                                    if(!empty($_This['Args_GET'])){
                                        $_This['Args_GET'] = explode('&amp;', $_This['Args_GET']);
                                        foreach($_This['Args_GET'] as &$GetParser){
                                            $GetParser = explode('=', $GetParser);
                                            $GetParser = "<b class=\"get\"><b class=\"gn\">{$GetParser[0]}</b><b class=\"ge\">=</b><b class=\"gv\">".valueParser($GetParser[1])."</b></b>";
                                        }
                                        $_This['Args_GET'] = implode(' &amp; ', $_This['Args_GET']);

                                        $Data_ParseRows[$Data_ThisParsedRowsIndex]['Data'][] = "{$_Lang['Log_GETData']}: {$_This['Args_GET']}";
                                    }
                                    if(!empty($_This['Args_POST'])){
                                        $_This['Args_POST'] = json_decode($_This['Args_POST'], true);

                                        if(!empty($_This['Args_POST']) AND (array)$_This['Args_POST'] === $_This['Args_POST']){
                                            $PostParsed = false;
                                            $GetState = PostParser($_This['Args_POST'], true);
                                            if($GetState['arr'] === true){
                                                $Data_ParseRows[$Data_ThisParsedRowsIndex]['Actions'][] = "<a href=\"#\" class=\"expandArr\">{$_Lang['ExpandArrays']}</a>";
                                            }
                                            if($GetState['cou'] > 1){
                                                $Data_ParseRows[$Data_ThisParsedRowsIndex]['Actions'][] = "<a href=\"#\" class=\"expandAmp\">{$_Lang['ExpandAmps']}</a>";
                                            }

                                            $Data_ParseRows[$Data_ThisParsedRowsIndex]['Data'][] = "{$_Lang['Log_POSTData']}:<b class=\"p_e\"></b> ".implode(' <b class="p_a">&amp;</b> ', $PostParsed);
                                        } else {
                                            $Data_ParseRows[$Data_ThisParsedRowsIndex]['Data'][] = "{$_Lang['Log_POSTData']}: {$_Lang['Log_POST_UnserializeError']}";
                                        }
                                    }

                                    $Data_ParseRows[$Data_ThisParsedRowsIndex]['Actions'][] = "<a target=\"_blank\" href=\"{$ThisPage}&amp;resendline={$LineNo}\">{$_Lang['ResendRequest']}</a>";
                                    if(!empty($Data_ParseRows[$Data_ThisParsedRowsIndex]['Actions'])){
                                        $Data_ParseRows[$Data_ThisParsedRowsIndex]['Actions'] = implode('<br/>', $Data_ParseRows[$Data_ThisParsedRowsIndex]['Actions']);
                                    }

                                    if(!empty($Data_ParseRows[$Data_ThisParsedRowsIndex]['Data'])){
                                        $Data_ParseRows[$Data_ThisParsedRowsIndex]['LeftAlignClass'] = 'lal';
                                        $Data_ParseRows[$Data_ThisParsedRowsIndex]['Data'] = implode('<br/>', $Data_ParseRows[$Data_ThisParsedRowsIndex]['Data']);
                                    }
                                } else {
                                    $Data_ParseRows[$Data_ThisParsedRowsIndex]['Data'] = $_Lang['Log_No_GET_POST'];
                                }
                                if(empty($Data_ParseRows[$Data_ThisParsedRowsIndex]['Actions'])){
                                    $Data_ParseRows[$Data_ThisParsedRowsIndex]['Actions'] = '&nbsp;';
                                }

                                // Finish for this Row
                                $Data_ParseRows[$Data_ThisParsedRowsIndex]['File'] = $_This['File'];
                                $Data_ParseRows[$Data_ThisParsedRowsIndex]['FileShow'] = str_replace('/', '/<br/>', $_This['File']);
                                $Data_ParseRows[$Data_ThisParsedRowsIndex]['Path'] = $_EnginePath;
                                $Data_LastParsedRowsIndex += 1;

                                $Counters_BadFormatLines = 0;
                                $Switch_LastIPShowed = true;
                                $Switch_LastBrowserShowed = true;
                                $Switch_LastScreenInfoShowed = true;
                                $Switch_LastFormatErrorShowed = true;
                            }
                            else
                            {
                                $Data_ThisParsedRowsIndex -= 1;
                            }
                            $Data_ParseRows[$Data_ThisParsedRowsIndex]['Time'][] = parseTimestamp($_This['Time']);

                            $Counters_LogsShowed += 1;
                            $Switch_CanBeMerged = false;
                        }
                    }
                }

                foreach($Data_ParseRows as $RowData)
                {
                    if($RowData['Type'] == '1')
                    {
                        $ThisTemplate = &$Row1LogTPL;
                    }
                    else if($RowData['Type'] == '2')
                    {
                        $ThisTemplate = &$Row2LogTPL;
                    }
                    else if($RowData['Type'] == '3')
                    {
                        $ThisTemplate = &$Row3LogTPL;
                    }
                    else if($RowData['Type'] == 'M')
                    {
                        $ThisTemplate = &$RowMLogTPL;
                    }
                    if(!empty($RowData['Time']))
                    {
                        $RowData['Time'] = implode('<br/>', $RowData['Time']);
                    }
                    if((array)$RowData['Data'] === $RowData['Data'])
                    {
                        $RowData['Data'] = implode('<br/>', $RowData['Data']);
                    }
                    $Rows[] = parsetemplate($ThisTemplate, $RowData);
                }

                if($Counters_LogsShowed < $Counters_LogsFilteredCount)
                {
                    $CurrentPage = ($CurrentPage > 1 ? $CurrentPage : 1);
                    include_once($_EnginePath.'includes/functions/Pagination.php');

                    $Pagin = CreatePaginationArray($Counters_LogsFilteredCount, $LogBreak, $CurrentPage, 7);
                    $PaginationTPL = gettemplate('admin/browse_actionlogs_pagination');
                    $PaginationViewOpt = array('CurrentPage_Classes' => 'orange fatB', 'Breaker_View' => '...');
                    $CreatePagination = ParsePaginationArray($Pagin, $CurrentPage, $PaginationTPL, $PaginationViewOpt);
                    $_Lang['Pagination'] = $Parse['Pagination'] = '<tr><th class="c pad" colspan="4">'.implode(' ', $CreatePagination).'</th></tr>';
                }

                $_Lang['ShowingXofYRows'] = sprintf($_Lang['ShowingXofYRows'], $Counters_LogsShowed, $Counters_LogsFilteredCount, $Counters_LogsTotalCount);

                $Parse['Headers']= parsetemplate($HeadLogTPL, $_Lang);
                if(!empty($Rows))
                {
                    $Parse['Content'] = implode('', $Rows);
                }
                else
                {
                    $Parse['Content'] = parsetemplate($Row1LogTPL, array('Data' => $_Lang['Info_NoLogsFilter']));
                }

                $Parse['CurrentBrowsingDate'] = " &#187; <a href=\"?uid={$UID}&amp;date={$Date}{$Packed}\">{$EDate[2]}.{$EDate[1]}.{$EDate[0]}</a>";
            }
            else
            {
                message($_Lang['Error_LogNoExists'], $Error, $ThisPage, 3);
            }
        }
        else
        {
            message($_Lang['Error_BadDateFormat'], $Error, $ThisPage, 3);
        }
    }

    if(!$LogExists)
    {
        // If Log not selected
        // ---- Show List ----
        $Parse['TableColspan'] = 2;

        $UserData = doquery("SELECT `username`, `register_time` FROM {{table}} WHERE `id` = {$UID} LIMIT 1;", 'users', true);
        $Parse['UserName'] = $UserData['username'];

        // - Sorting -
        $Sort = 'date';
        $Order = 'desc';
        $_Lang['DateSort'] = 'desc';
        $_Lang['SizeSort'] = 'desc';

        if(!empty($_GET['sort']))
        {
            if($_GET['sort'] === 'date' OR $_GET['sort'] === 'size')
            {
                $Sort = $_GET['sort'];
                if($_GET['order'] == 'desc' OR $_GET['order'] == 'asc')
                {
                    $Order = $_GET['order'];
                }
            }
        }

        if($Sort === 'date')
        {
            if($Order === 'desc')
            {
                $_Lang['DateSort'] = 'asc';
            }
            else
            {
                $_Lang['DateSort'] = 'desc';
            }
        }
        else if($Sort === 'size')
        {
            if($Order === 'desc')
            {
                $_Lang['SizeSort'] = 'asc';
            }
            else
            {
                $_Lang['SizeSort'] = 'desc';
            }
        }
        // ---------------
        // - Date Filter -
        $FilterOn = false;
        $FromDate    = str_pad(isset($_POST['from_yea']) ? $_POST['from_yea'] : null, 4, '0', STR_PAD_LEFT)
                    .'_'.str_pad(isset($_POST['from_mon']) ? $_POST['from_mon'] : null, 2, '0', STR_PAD_LEFT)
                    .'_'.str_pad(isset($_POST['from_day']) ? $_POST['from_day'] : null, 2, '0', STR_PAD_LEFT);
        $ToDate        = str_pad(isset($_POST['to_yea']) ? $_POST['to_yea'] : null, 4, '0', STR_PAD_LEFT)
                    .'_'.str_pad(isset($_POST['to_mon']) ? $_POST['to_mon'] : null, 2, '0', STR_PAD_LEFT)
                    .'_'.str_pad(isset($_POST['to_day']) ? $_POST['to_day'] : null, 2, '0', STR_PAD_LEFT);
        if(!preg_match('/^[0-9]{4}\_[0-9]{2}\_[0-9]{2}$/D', $FromDate) OR strstr($FromDate, '0000_') OR strstr($FromDate, '_00'))
        {
            $RegdayExp = explode('.', date('d.m.Y', $UserData['register_time']));
            $FromDate = $RegdayExp[2].'_'.$RegdayExp[1].'_'.$RegdayExp[0];
        }
        else
        {
            $RegdayExp = array_reverse(explode('_', $FromDate));
            $FilterOn = true;
        }
        if(!preg_match('/^[0-9]{4}\_[0-9]{2}\_[0-9]{2}$/D', $ToDate) OR strstr($ToDate, '0000_') OR strstr($ToDate, '_00'))
        {
            $TodayExp = explode('.', date('d.m.Y'));
            $ToDate = $TodayExp[2].'_'.$TodayExp[1].'_'.$TodayExp[0];
        }
        else
        {
            $TodayExp = array_reverse(explode('_', $ToDate));
            $FilterOn = true;
        }
        if($FromDate < date('Y_m_d', $UserData['register_time']))
        {
            $RegdayExp = explode('.', date('d.m.Y', $UserData['register_time']));
            $FromDate = $RegdayExp[2].'_'.$RegdayExp[1].'_'.$RegdayExp[0];
        }
        if($ToDate < $FromDate)
        {
            $TodayExp = $RegdayExp;
            $ToDate = $TodayExp[2].'_'.$TodayExp[1].'_'.$TodayExp[0];
        }
        else if($ToDate > date('Y_m_d'))
        {
            $TodayExp = explode('.', date('d.m.Y'));
            $ToDate = $TodayExp[2].'_'.$TodayExp[1].'_'.$TodayExp[0];
        }

        $DirPath= './../action_logs/'.$UIDMarker;
        $ScanDir= scandir($DirPath);
        $Logs = [];
        foreach($ScanDir as $Filename)
        {
            if(preg_match("/^Log\_U\_{$UIDMarker}\_D\_([0-9]{4}\_[0-9]{2}\_[0-9]{2})\.php$/D", $Filename, $Matches))
            {
                if($Matches[1] >= $FromDate AND $Matches[1] <= $ToDate)
                {
                    $Logs[$Matches[1]] = 'N';
                    $FileSizes[$Matches[1]] = (string) filesize($DirPath.'/'.$Filename);
                }
            }
        }

        if(isset($_POST['filter']) && $_POST['filter'] != 'on')
        {
            $Parse['FilteringDisplay'] = ' display: none;';
        }

        $ScanDir = scandir('./../action_logs');
        foreach($ScanDir as $DirName)
        {
            if(strstr($DirName, 'logs'))
            {
                $GetDate = substr($DirName, 5);
                if($GetDate >= $FromDate AND $GetDate <= $ToDate)
                {
                    $File = "./../action_logs/{$DirName}/Log_U_{$UIDMarker}_D_{$GetDate}.php.gz";
                    if(file_exists($File))
                    {
                        $Logs[$GetDate.'_P'] = 'P';
                        $FileSizes[$GetDate.'_P'] = (string) filesize($File);
                    }
                }
            }
        }

        $LogsCount = count($Logs);
        if($LogsCount == 0)
        {
            $_Lang['NoHeader'] = 'display: none;';
            if($FilterOn)
            {
                $Rows = '<tr><th class="c pad" colspan="2" class="red">'.$_Lang['Info_NoLogsForThatPeriod'].'</th></tr>';
            }
            else
            {
                $Rows = '<tr><th class="c pad" colspan="2" class="red">'.$_Lang['Info_NoLogs'].'</th></tr>';
            }
        }
        else
        {
            if($Sort === 'date' AND $Order === 'desc')
            {
                krsort($Logs);
            }
            else if($Sort === 'date' AND $Order === 'asc')
            {
                ksort($Logs);
            }
            else if($Sort === 'size' AND $Order === 'desc')
            {
                arsort($FileSizes);
                foreach($FileSizes as $Key => $Val)
                {
                    $NewLogs[$Key] = $Logs[$Key];
                }
                $Logs = $NewLogs;
            }
            else if($Sort === 'size' AND $Order === 'asc')
            {
                asort($FileSizes);
                foreach($FileSizes as $Key => $Val)
                {
                    $NewLogs[$Key] = $Logs[$Key];
                }
                $Logs = $NewLogs;
            }

            $Rows = '';
            foreach($Logs as $Key => $Name)
            {
                $Row = false;
                $Row['UID'] = $UID;
                if(strstr($Key, '_P'))
                {
                    $Row['Date'] = str_replace('_P', '', $Key);
                    $Row['Packed'] = '&amp;packed=true';
                    $Packed = true;
                }
                else
                {
                    $Row['Date'] = $Key;
                    $Packed = false;
                }
                $Row['Date_Formated'] = explode('_', $Key);
                $Row['Date_Formated'] = "{$Row['Date_Formated'][2]}.{$Row['Date_Formated'][1]}.{$Row['Date_Formated'][0]}";
                $Row['Size'] = humanSize($FileSizes[$Key]);
                if($Packed)
                {
                    $Row['Size'] = "<span class=\"packed\">{$Row['Size']}</span>";
                }

                $Rows .= parsetemplate($RowListTPL, $Row);
            }
        }

        $_Lang['FromDay'] = $RegdayExp[0];
        $_Lang['FromMon'] = $RegdayExp[1];
        $_Lang['FromYea'] = $RegdayExp[2];
        $_Lang['ToDay'] = $TodayExp[0];
        $_Lang['ToMon'] = $TodayExp[1];
        $_Lang['ToYea'] = $TodayExp[2];
        $_Lang['LogsCount'] = $LogsCount;
        $Parse['Headers'] = parsetemplate($HeadListTPL, $_Lang);
        $Parse['Content'] = $Rows;
    }

    $Parse['SetFiltering'] = isset($_POST['filter']) ? $_POST['filter'] : null;
    $Parse['ThisPage'] = $ThisPage;
    $Page = parsetemplate($MainTPL, $Parse);
    display($Page, $_Lang['PageTitle'], false, true);
}
else
{
    AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

?>
