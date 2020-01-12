<?php

use UniEngine\Engine\Includes\Helpers\Users;

function IPandUA_Logger($TheUser, $Failed = false)
{
    if(!isset($TheUser['id']) || $TheUser['id'] <= 0)
    {
        return false;
    }
    if(isset($TheUser['isAI']) && $TheUser['isAI'] == 1)
    {
        return false;
    }

    global $_SERVER;

    $usersIP = Users\Session\getCurrentIP();
    $UAVal = getDBLink()->escape_string($_SERVER['HTTP_USER_AGENT']);

    $IPHash = md5($usersIP);
    $UAHash = md5($UAVal);
    $InsertIPandUAQuery = '';
    $InsertIPandUAQuery .= "INSERT INTO {{table}} (`Type`, `Value`, `ValueHash`) VALUES ";
    $InsertIPandUAQuery .= "('ip', '{$usersIP}', '{$IPHash}'), ";
    $InsertIPandUAQuery .= "('ua', '{$UAVal}', '{$UAHash}') ";
    $InsertIPandUAQuery .= "ON DUPLICATE KEY UPDATE ";
    $InsertIPandUAQuery .= "`SeenCount` = `SeenCount` + 1;";
    doquery($InsertIPandUAQuery, 'used_ip_and_ua');

    $SelectBrowserAndIPLogs = doquery(
        "SELECT `ID`, `Type` FROM {{table}} WHERE (`Type` = 'ua' AND `ValueHash` = '{$UAHash}') OR (`Type` = 'ip' AND `ValueHash` = '{$IPHash}');",
        'used_ip_and_ua'
    );

    while($AccessData = $SelectBrowserAndIPLogs->fetch_assoc())
    {
        if($AccessData['Type'] == 'ip')
        {
            $UsedIP_ID = $AccessData['ID'];
        }
        else
        {
            $UsedUA_ID = $AccessData['ID'];
        }
    }

    $SelectEnterLog = doquery("SELECT `ID` FROM {{table}} WHERE `User_ID` = {$TheUser['id']} AND `IP_ID` = {$UsedIP_ID} AND `UA_ID` = {$UsedUA_ID};", 'user_enterlog', true);
    $ServerStamp = $TextServerStamp = ServerStamp();

    $AddToStamp = array();
    if($Failed === true)
    {
        $AddToStamp[] = 'F';
    }
    if(!empty($AddToStamp))
    {
        $TextServerStamp .= '|'.implode('|', $AddToStamp);
    }

    if($SelectEnterLog['ID'] > 0)
    {
        if($Failed === true)
        {
            $Query_InsertLog_Array[] = "`FailCount` = `FailCount` + 1";
        }
        $Query_InsertLog = '';
        $Query_InsertLog .= "UPDATE {{table}} SET ";
        $Query_InsertLog_Array[] = "`Times` = CONCAT(`Times`, ',{$TextServerStamp}')";
        $Query_InsertLog_Array[] = "`Count` = `Count` + 1";
        $Query_InsertLog_Array[] = "`LastTime` = {$ServerStamp}";
        $Query_InsertLog .= implode(', ', $Query_InsertLog_Array);
        $Query_InsertLog .= " WHERE `ID` = {$SelectEnterLog['ID']};";
    }
    else
    {
        if($Failed === true)
        {
            $Query_InsertLog_Array[] = "`FailCount` = 1";
        }
        $Query_InsertLog = '';
        $Query_InsertLog .= "INSERT INTO {{table}} SET ";
        $Query_InsertLog_Array[] = "`User_ID` = {$TheUser['id']}";
        $Query_InsertLog_Array[] = "`IP_ID` = {$UsedIP_ID}";
        $Query_InsertLog_Array[] = "`UA_ID` = {$UsedUA_ID}";
        $Query_InsertLog_Array[] = "`Times` = '{$TextServerStamp}'";
        $Query_InsertLog_Array[] = "`Count` = `Count`";
        $Query_InsertLog_Array[] = "`LastTime` = {$ServerStamp}";
        $Query_InsertLog .= implode(', ', $Query_InsertLog_Array);
        $Query_InsertLog .= ';';
    }
    doquery($Query_InsertLog, 'user_enterlog');
}

?>
