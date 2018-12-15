<?php

define('INSIDE', true);

$_DontCheckPolls = TRUE;
$_DontForceRulesAcceptance = true;
$_UseMinimalCommon = true;
$_AllowInVacationMode = true;

$_SetAccessLogPreFilename = 'ajax/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath.'common.php');

if(isLogged())
{
    if(CheckAuth('supportadmin'))
    {
        $Allowed = array('1', '2');
    }
    else
    {
        $Allowed = array('1');
    }

    $KeyEquivalents = array('1' => 'GhostMode', '2' => 'GhostMode_DontCount');
    $ValSettings = array('1' => 'checkbox', '2' => 'checkbox');
    foreach($_POST as $Key => $Val)
    {
        if(strstr($Key, 'setChg_'))
        {
            $Key = str_replace('setChg_', '', $Key);
            if(in_array($Key, $Allowed))
            {
                $ToChange[$Key] = $Val;
            }
        }
    }
    if(!empty($ToChange))
    {
        $UpdateQuery = '';
        $UpdateQuery .= "UPDATE {{table}} SET ";
        foreach($ToChange as $Key => $Val)
        {
            if($ValSettings[$Key] == 'checkbox')
            {
                if($Val == 'true')
                {
                    $Val = '1';
                }
                else
                {
                    $Val = '0';
                }
            }
            $UpdateQueryArray[] = "`chat_{$KeyEquivalents[$Key]}` = '{$Val}'";
        }
        $UpdateQuery .= implode(', ', $UpdateQueryArray);
        $UpdateQuery .= " WHERE `id` = {$_User['id']} LIMIT 1;";
        doquery($UpdateQuery, 'users');
        safeDie('1');
    }
    safeDie('2');
}
safeDie('4');

?>
