<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(!CheckAuth('supportadmin'))
{
    AdminMessage($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

includeLang('admin/iplog_addproxy');
$TPL_Body = gettemplate('admin/iplog_addproxy_body');

if(isset($_POST['sent']) && $_POST['sent'] == '1')
{
    if(!empty($_POST['list']))
    {
        $ThisList = explode(',', $_POST['list']);
        foreach($ThisList as $ThisIP)
        {
            $ThisIP = trim($ThisIP);
            if(preg_match(REGEXP_IP, $ThisIP))
            {
                $ThisIPHash = md5($ThisIP);
                $Query_AddIPs_Array[$ThisIP] = "(NULL, 'ip', '{$ThisIP}', '{$ThisIPHash}', 0, 1)";
            }
        }

        if(!empty($Query_AddIPs_Array))
        {
            $Query_AddIPs = '';
            $Query_AddIPs .= "INSERT INTO {{table}} (`ID`, `Type`, `Value`, `ValueHash`, `SeenCount`, `isProxy`) VALUES ";
            $Query_AddIPs .= implode(',', $Query_AddIPs_Array);
            $Query_AddIPs .= " ON DUPLICATE KEY UPDATE ";
            $Query_AddIPs .= "`isProxy` = 1;";
            doquery($Query_AddIPs, 'used_ip_and_ua');

            $_MsgBox['Class'] = 'lime';
            $_MsgBox['Text'] = sprintf($_Lang['MsgBox_Success'], prettyNumber(count($Query_AddIPs_Array)));
        }
        else
        {
            $_MsgBox['Class'] = 'red';
            $_MsgBox['Text'] = $_Lang['MsgBox_AllBad'];
        }
    }
    else
    {
        $_MsgBox['Class'] = 'red';
        $_MsgBox['Text'] = $_Lang['MsgBox_ListEmpty'];
    }
}

if(empty($_MsgBox))
{
    $_MsgBox['Class'] = '';
    $_MsgBox['Text'] = '&nbsp;';
}
$_Lang['Insert_MsgBox'] = parsetemplate(gettemplate('_singleRow'), array('Classes' => 'pad2 '.$_MsgBox['Class'], 'Colspan' => 2, 'Text' => $_MsgBox['Text']));

display(parsetemplate($TPL_Body, $_Lang), $_Lang['PageTitle'], false, true);

?>
