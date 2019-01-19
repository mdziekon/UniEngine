<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = './../';

include($_EnginePath.'common.php');

if(!CheckAuth('supportadmin'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

includeLang('admin');
includeLang('admin/reports_list');

if(empty($_GET['showall']) || $_GET['showall'] == 0)
{
    $ShowAll = '0';
}
else
{
    $ShowAll = '1';
}

if(isset($_GET['deleteall']) && $_GET['deleteall'] == 'yes')
{
    doquery("TRUNCATE TABLE {{table}}", 'reports');
}

$PageTPL = gettemplate('admin/reportlist_body');
$RowsTPL = gettemplate('admin/reportlist_rows');

$TypeList = $_Lang['Report_types'];
$StatusList = $_Lang['Report_statuslist'];

if(!empty($_GET['action']))
{
    $MSGColor = 'red';
    $ID = isset($_GET['id']) ? floor(floatval($_GET['id'])) : 0;
    if($ID > 0)
    {
        $SQLResult_GetDeclaration = doquery(
            "SELECT `id`, `status` FROM {{table}} WHERE `id` = {$ID} LIMIT 1;",
            'reports'
        );

        if($SQLResult_GetDeclaration->num_rows == 1)
        {
            switch($_GET['action'])
            {
                case 'delete':
                    doquery("DELETE FROM {{table}} WHERE `id` = {$ID};", 'reports');
                    $MSG = $_Lang['Report_deleted'];
                    $MSGColor = 'lime';
                    break;
                case 'change_status':
                    $Status = isset($_GET['set_status']) ? intval($_GET['set_status']) : -1;
                    if($Status < 0)
                    {
                        $MSG = $_Lang['Report_no_status_given'];
                    }
                    else
                    {
                        doquery("UPDATE {{table}} SET `status` = {$Status} WHERE `id` = {$ID};", 'reports');
                        $MSG = $_Lang['Report_status_changed'];
                        $MSGColor = 'lime';
                    }
                    break;
            }
        }
        else
        {
            $MSG = $_Lang['Report_noexist'];
        }
    }
    else
    {
        $MSG = $_Lang['No_id_given'];
    }
}

$ShowAllWhere = '';
if($ShowAll == '0')
{
    $ShowAllWhere = "WHERE `status` NOT IN (9,10)";
}
$SQLResult_GetReports = doquery(
    "SELECT {{table}}.*, `users`.`username`, `users1`.`username` AS `reported_user` FROM {{table}} LEFT JOIN {{prefix}}users AS `users` ON `sender_id` = `users`.`id` LEFT JOIN {{prefix}}users AS `users1` ON `report_user` = `users1`.`id` {$ShowAllWhere} ORDER BY {{table}}.`date` DESC;",
    'reports'
);

$parse = $_Lang;
$parse['adm_ul_table'] = '';
if(!empty($MSG))
{
    $parse['system_msg'] = '<tr><td class="c" colspan="9" style="padding: 5px; color: '.$MSGColor.'">'.$MSG.'</td></tr><tr style="visibility: hidden;"><td><br/></td></tr>';
}

if($SQLResult_GetReports->num_rows > 0)
{
    while($u = $SQLResult_GetReports->fetch_assoc())
    {
        $Bloc['data_id'] = $u['id'];
        $Bloc['data_date'] = date('d.m.Y', $u['date']).' <br/><span class="lime">'.date('H:i:s', $u['date']).'</span>';
        $Bloc['data_sender'] = $u['username'].'<br/>(<a href="user_inf.php?uid='.$u['sender_id'].'" target="_blank">#'.$u['sender_id'].'</a>)';
        $Bloc['sender_id'] = $u['sender_id'];
        $Bloc['data_type'] = $TypeList[($u['report_type'] - 1)];
        $Bloc['data_element'] = (($u['report_element'] > 0) ? '#'.$u['report_element'] : ' - ');
        if(in_array($u['report_type'], array(1, 5)))
        {
            $Bloc['data_element'] = '<a href="messagelist.php?mid='.$u['report_element'].'">'.$Bloc['data_element'].'</a>';
        }
        else if($u['report_type'] == 9)
        {
            $Bloc['data_element'] = '<a href="chatbrowser.php?lID='.$u['report_element'].'&amp;this=1">'.$Bloc['data_element'].'</a>';
        }
        $Bloc['data_user'] = (($u['report_user'] > 0) ? $u['reported_user'].'<br/>(<a href="user_inf.php?uid='.$u['report_user'].'" target="_blank">#'.$u['report_user'].'</a>)' : ' - ');
        $Bloc['report_user'] = $u['report_user'];
        $Bloc['data_info'] = (!empty($u['user_info']) ? nl2br($u['user_info']) : '-');
        $Bloc['data_status'] = $StatusList[$u['status']];
        $Bloc['showall'] = $ShowAll;
        $Bloc['msg_answer_subject'] = sprintf($_Lang['Report_reply_subject'], $u['id']);
        $Bloc['msg_answer_input'] = $_Lang['Report_reply_input'];
        if($u['report_type'] == 2)
        {
            $Bloc['data_datebash'] = date('Y-m-d', $u['date']);
        }
        else
        {
            $Bloc['Hide_NoBash'] = 'hide';
        }

        $parse['adm_ul_table'] .= parsetemplate($RowsTPL, $Bloc );
    }
}
else
{
    $parse['adm_ul_table'] = '<tr><th class="c" colspan="9" style="padding: 5px; color: red;">'.$_Lang['Report_noreports'].'</td></tr>';
}

$page = parsetemplate($PageTPL, $parse);
display($page, $_Lang['Report_list_title'], false, true);

?>
