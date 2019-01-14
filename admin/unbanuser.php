<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(!CheckAuth('sgo'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

includeLang('admin/unbanuser');
$_Lang['InsertInfoBoxText'] = '&nbsp;';
$_Lang['HideInfoBox'] = ' class="hide"';
$Now = time();

if(isset($_POST['send']) && $_POST['send'] == 'yes')
{
    $_Lang['HideInfoBox'] = '';
    $_Lang['InsertInfoBoxText'] = 'red';
    $Opt_RemoveVacation = (isset($_POST['vacoff']) && $_POST['vacoff'] == 'on' ? true : false);
    $_Lang['Insert_SearchBox'] = $_POST['users'];

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
                "SELECT `id`, `username`, `is_banned`, `ban_endtime`, `is_onvacation` FROM {{table}} WHERE ".implode(' OR ', $Where).";",
                'users'
            );

            $UnbanUsers = array();

            if($SQLResult_CheckUsers->num_rows > 0)
            {
                while($Data = $SQLResult_CheckUsers->fetch_assoc())
                {
                    if($Data['is_banned'] == 1 AND $Data['ban_endtime'] > $Now)
                    {
                        $UnbanUsers[$Data['id']] = $Data;
                        if($Data['is_onvacation'] == 1)
                        {
                            $UpdatePlanets[] = $Data['id'];
                        }
                    }
                }
            }
            if(!empty($UnbanUsers))
            {
                $UnbannedCount = count($UnbanUsers);

                $UpdateFields[] = "`is_banned` = 0";
                $UpdateFields[] = "`ban_endtime` = 0";
                if($Opt_RemoveVacation)
                {
                    $UpdateFields[] = "`is_onvacation` = 0";
                    $UpdateFields[] = "`vacation_starttime` = 0";
                    $UpdateFields[] = "`vacation_endtime` = 0";
                }
                else
                {
                    $EndVacation = $Now + (MAXVACATIONS_REG * TIME_DAY);
                    $UpdateFields[] = "`vacation_endtime` = IF(`is_onvacation` = 1, {$EndVacation}, `vacation_endtime`)";
                }
                $UpdateFields[] = "`block_cookies` = 0";

                $UserIDs = implode(', ', array_keys($UnbanUsers));
                doquery("UPDATE {{table}} SET ".implode(', ', $UpdateFields)." WHERE `id` IN ({$UserIDs});", 'users');
                doquery("UPDATE {{table}} SET `Active` = 0, `Removed` = 1, `RemoveDate` = {$Now} WHERE `Active` = 1 AND `EndTime` > {$Now} AND `UserID` IN ({$UserIDs});", 'bans');
                if($Opt_RemoveVacation AND !empty($UpdatePlanets))
                {
                    $PlanetUsers = implode(', ', $UpdatePlanets);
                    doquery("UPDATE {{table}} SET `last_update` = {$Now} WHERE `id_owner` IN ({$PlanetUsers});", 'planets');
                }

                $UserLinkTPL = gettemplate('admin/banuser_userlink');
                foreach($UnbanUsers as $UserID => $UserData)
                {
                    $UserLinks[] = parsetemplate($UserLinkTPL, array('ID' => $UserID, 'Username' => $UserData['username']));
                }
                $UserLinks = implode(', ', $UserLinks);
                $_Lang['InsertInfoBoxText'] = sprintf(($UnbannedCount > 1 ? $_Lang['Msg_UnbanMOK'] : $_Lang['Msg_Unban1OK']), $UserLinks);
                $_Lang['InsertInfoBoxColor'] = 'lime';
                $_Lang['Insert_SearchBox'] = '';
            }
            else
            {
                $_Lang['InsertInfoBoxText'] = $_Lang['Error_NoOne2Unban'];
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

display(parsetemplate(gettemplate('admin/unbanuser'), $_Lang), $_Lang['Page_Title'], false, true);

?>
