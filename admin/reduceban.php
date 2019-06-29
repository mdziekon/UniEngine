<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(!CheckAuth('sgo'))
{
    AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}
includeLang('admin/reduceban');
$Now = time();

$TPL = gettemplate('admin/reduceban');

$_Lang['InsertInfoBoxText'] = '&nbsp;';
$_Lang['HideInfoBox'] = ' class="inv"';
$_Lang['InsertInfoBoxColor'] = 'red';

if(isset($_POST['send']) && $_POST['send'] == 'yes')
{
    $_Lang['HideInfoBox'] = '';
    $_POST['users']    = trim($_POST['users']);
    $_Lang['Insert_SearchBox'] = $_POST['users'];

    if(isset($_POST['reduce_type']) && in_array($_POST['reduce_type'], array('01', '02')))
    {
        if($_POST['reduce_type'] == '01')
        {
            // Reduce to given Date
            $Opt_ReduceType = 1;
            $Opt_NewEndTime = strtotime($_POST['recude_date']);
            if($Opt_NewEndTime > $Now)
            {
                $DoSearch = true;
            }
            else
            {
                $_Lang['InsertInfoBoxText'] = $_Lang['Error_BadNewEndTime'];
            }
        }
        else if($_POST['reduce_type'] == '02')
        {
            // Reduce by given period of time
            $Opt_ReduceType = 2;
            $Opt_Period  = intval($_POST['period_days']) * TIME_DAY;
            $Opt_Period += intval($_POST['period_hours']) * 3600;
            $Opt_Period += intval($_POST['period_mins']) * 60;
            $Opt_Period += intval($_POST['period_secs']);

            if($Opt_Period > 0)
            {
                $DoSearch = true;
            }
            else
            {
                $_Lang['InsertInfoBoxText'] = $_Lang['Error_BadPeriod'];
            }
        }
    }
    else
    {
        $_Lang['InsertInfoBoxText'] = $_Lang['Error_BadType'];
    }

    if(isset($DoSearch))
    {
        if(!empty($_POST['users']))
        {
            $UserErrors['badID'] = 0;
            $UserErrors['badNick'] = 0;

            $Users = explode(',', $_POST['users']);
            foreach($Users as $UserData)
            {
                $UserData = trim($UserData);
                if(strstr($UserData, '[') !== FALSE)
                {
                    if(preg_match('/^\[[0-9]{1,20}\]$/D', $UserData))
                    {
                        $GetUsers['id'][] = trim($UserData, '[]');
                    }
                    else
                    {
                        $UserErrors['badID'] += 1;
                    }
                }
                else
                {
                    if(preg_match(REGEXP_USERNAME_ABSOLUTE, $UserData))
                    {
                        $GetUsers['name'][] = "'{$UserData}'";
                    }
                    else
                    {
                        $UserErrors['badNick'] += 1;
                    }
                }
            }
            if(!empty($GetUsers))
            {
                if(!empty($GetUsers['id']))
                {
                    $Where[] = "`id` IN (".implode(', ', $GetUsers['id']).")";
                }
                if(!empty($GetUsers['name']))
                {
                    $Where[] = "`username` IN (".implode(', ', $GetUsers['name']).")";
                }

                $SQLResult_CheckUsers = doquery(
                    "SELECT `id`, `username`, `is_banned`, `ban_endtime` FROM {{table}} WHERE ".implode(' OR ', $Where).";",
                    'users'
                );

                $UpdateUsers = array();

                if($SQLResult_CheckUsers->num_rows > 0)
                {
                    while($Data = $SQLResult_CheckUsers->fetch_assoc())
                    {
                        if($Data['is_banned'] == 1 AND $Data['ban_endtime'] > $Now)
                        {
                            if($Opt_ReduceType == 1 AND $Opt_NewEndTime >= $Data['ban_endtime'])
                            {
                                continue;
                            }
                            else if($Opt_ReduceType == 2 AND ($Data['ban_endtime'] - $Opt_Period) <= $Now)
                            {
                                continue;
                            }
                            $UpdateUsers[$Data['id']] = $Data;
                        }
                    }
                }
                if(!empty($UpdateUsers))
                {
                    $UpdatedCount = count($UpdateUsers);

                    if($Opt_ReduceType == 1)
                    {
                        $UpdateFields[] = "`vacation_endtime` = IF((`is_onvacation` = 1 AND `vacation_type` = 1), `vacation_endtime` - (`ban_endtime` - {$Opt_NewEndTime}), `vacation_endtime`)";
                        $UpdateFields[] = "`ban_endtime` = {$Opt_NewEndTime}";
                        $UpdateBanRows[] = "`EndTime` = {$Opt_NewEndTime}";
                    }
                    elseif($Opt_ReduceType == 2)
                    {
                        $UpdateFields[] = "`vacation_endtime` = IF((`is_onvacation` = 1 AND `vacation_type` = 1), `vacation_endtime` - {$Opt_Period}, `vacation_endtime`)";
                        $UpdateFields[] = "`ban_endtime` = `ban_endtime` - {$Opt_Period}";
                        $UpdateBanRows[] = "`EndTime` = `EndTime` - {$Opt_Period}";
                    }

                    $UserIDs = implode(', ', array_keys($UpdateUsers));
                    doquery("UPDATE {{table}} SET ".implode(', ', $UpdateFields)." WHERE `id` IN ({$UserIDs});", 'users');
                    doquery("UPDATE {{table}} SET ".implode(', ', $UpdateBanRows)." WHERE `Active` = 1 AND `EndTime` > {$Now} AND `UserID` IN ({$UserIDs});", 'bans');

                    $UserLinkTPL = gettemplate('admin/banuser_userlink');
                    foreach($UpdateUsers as $UserID => $UserData)
                    {
                        $UserLinks[] = parsetemplate($UserLinkTPL, array('ID' => $UserID, 'Username' => $UserData['username']));
                    }
                    $UserLinks = implode(', ', $UserLinks);
                    $_Lang['InsertInfoBoxText'] = sprintf(($UpdatedCount > 1 ? $_Lang['Msg_UpdateMOK'] : $_Lang['Msg_Update1OK']), $UserLinks);
                    $_Lang['InsertInfoBoxColor'] = 'lime';
                    $_Lang['Insert_SearchBox'] = '';
                }
                else
                {
                    $_Lang['InsertInfoBoxText'] = $_Lang['Error_NoOne2Update'];
                }
            }
            else
            {
                $_Lang['InsertInfoBoxText'] = $_Lang['Error_BadSearch'];
            }
        }
        else
        {
            $_Lang['InsertInfoBoxText'] = $_Lang['Error_EmptySearch'];
        }
    }
}

if(!empty($_GET['ids']))
{
    $InsertIDs = explode(',', $_GET['ids']);
    foreach($InsertIDs as $ThisID)
    {
        $_Lang['InsertUsernames'][] = "[{$ThisID}]";
    }
    $_Lang['InsertUsernames'] = implode(',', $_Lang['InsertUsernames']);
}
else if(!empty($_GET['user']))
{
    $_Lang['InsertUsernames'] = $_GET['user'];
}

$_Lang['JS_DatePicker_TranslationLang'] = getJSDatePickerTranslationLang();

$Page = parsetemplate($TPL, $_Lang);
display($Page, $_Lang['Page_Title'], false, true);

?>
