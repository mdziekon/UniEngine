<?php

define('INSIDE', true);
define('IN_ADMIN', true);

$_SetAccessLogPreFilename = 'admin/';
$_SetAccessLogPath = '../';
$_EnginePath = '../';

include($_EnginePath.'common.php');

includeLang('admin');
includeLang('admin/errorslist');

$parse = $_Lang;
if(!CheckAuth('programmer'))
{
    message($_Lang['sys_noalloaw'], $_Lang['sys_noaccess']);
}

$TPL_Row = gettemplate('admin/errors_row');

$DeleteID = (isset($_GET['delete']) ? round(floatval($_GET['delete'])) : 0);
$DoDeleteAll = (isset($_GET['deleteall']) && $_GET['deleteall'] == 'yes' ? true : false);

if($DeleteID > 0)
{
    doquery("DELETE FROM {{table}} WHERE `error_id` = {$DeleteID} LIMIT 1;", 'errors');
}
else if($DoDeleteAll)
{
    doquery("TRUNCATE TABLE {{table}}", 'errors');
}

$SQLResult_GetErrors = doquery("SELECT * FROM {{table}} LIMIT 100;", 'errors');
$i = 0;

$parse['errors_list'] = '';
while($ErrorData = $SQLResult_GetErrors->fetch_assoc())
{
    ++$i;
    $parse['errors_list'] .= parsetemplate($TPL_Row, array
    (
        'ID' => $ErrorData['error_id'],
        'Date' => date('d.m.Y H:i:s', $ErrorData['error_time']),
        'Text' => nl2br($ErrorData['error_text'])
    ));
}

$Query_GetCount = doquery("SELECT COUNT(`error_id`) AS `count` FROM {{table}};", 'errors', true);
$i = $Query_GetCount['count'];
if($i >= 100)
{
    $parse['errors_list'] .= "<tr><th class=b colspan=4>{$_Lang['ErrorsList_TooManyErrors']}</th></tr>";
}

$parse['errors_list'] .= "<tr><th class=b colspan=4>{$_Lang['ErrorsList_Count']}: {$i}</th></tr>";

display(parsetemplate(gettemplate('admin/errors_body'), $parse), $_Lang['ErrorsList_Title'], false, true);
?>
