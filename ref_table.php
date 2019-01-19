<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('reftable');
$_Lang['skinpath'] = $_SkinPath;
$_Lang['Rows'] = '';

$Query_SelectRows = '';
$Query_SelectRows .= "SELECT `ref`.*, `users`.username FROM {{table}} AS `ref` ";
$Query_SelectRows .= "LEFT JOIN {{prefix}}users AS `users` ON `users`.`id` = `ref`.`newuser_id` ";
$Query_SelectRows .= " WHERE `ref`.`referrer_id` = {$_User['id']};";

$SQLResult_SelectRows = doquery($Query_SelectRows, 'referring_table');

$_Lang['referralLink'] = GAMEURL . 'index.php?r='.$_User['id'];
$_Lang['referring_info'] = sprintf($_Lang['referring_info'], (REFERING_PROVISION * 100));

if($SQLResult_SelectRows->num_rows > 0)
{
    $RowTPL = gettemplate('ref_table_row');

    while($NewUser = $SQLResult_SelectRows->fetch_assoc())
    {
        if(!empty($NewUser['username']))
        {
            $NewUser['Username'] = $NewUser['username'];
        }
        else
        {
            $NewUser['Username'] = "<b class=\"red\">{$_Lang['UserDeleted']}</b>";
        }
        $NewUser['RegDate'] = prettyDate('d m Y - H:i:s', $NewUser['time'], 1);
        $NewUser['Provision'] = "{$NewUser['provisions_granted']} {$_Lang['DEUnits']}";

        $_Lang['Rows'] .= parsetemplate($RowTPL, $NewUser);
    }
}
else
{
    $_Lang['Rows'] = "<tr><th class=\"b red pad\" colspan=\"3\">{$_Lang['referred_noone']}</th></tr>";
}

$page = parsetemplate(gettemplate('ref_table_body'), $_Lang);
display($page, $_Lang['reftable'], false);

?>
