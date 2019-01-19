<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

includeLang('banned');
$parse = $_Lang;
$parse['Rows'] = '';
$RowTPL = gettemplate('bans_row');

$SelectFields = '`bans`.`Reason`, `bans`.`StartTime`, `bans`.`EndTime`, `bans`.`GiverID`, `users`.`username`, `users2`.`username` AS `GiverUsername`';

$SQLResult_SelectBans = doquery("SELECT {$SelectFields} FROM {{table}} AS `bans` LEFT JOIN `{{prefix}}users` AS `users` ON `users`.`id` = `bans`.`UserID` LEFT JOIN `{{prefix}}users` AS `users2` ON `users2`.`id` = `bans`.`GiverID` AND `bans`.`GiverID` > 0 WHERE `bans`.`EndTime` > UNIX_TIMESTAMP() AND `bans`.`Active` = 1 ORDER BY `bans`.`ID`;",'bans');

$BansCount = $SQLResult_SelectBans->num_rows;

if($BansCount > 0)
{
    $i = 0;
    while($Ban = $SQLResult_SelectBans->fetch_assoc())
    {
        $Row = array();
        $Row['BanNo'] = ++$i;
        $Row['BanUser'] = $Ban['username'];
        if(!empty($Ban['Reason']))
        {
            $Row['BanReason'] = $Ban['Reason'];
        }
        else
        {
            $Row['BanReason'] = $_Lang['Reason_NotSpecified'];
        }
        $Row['BanStart'] = prettyDate('d m Y<\b\r/>H:i:s', $Ban['StartTime'], 1);
        $Row['BanEnd'] = prettyDate('d m Y<\b\r/>H:i:s', $Ban['EndTime'], 1);
        $Row['BanBy'] = ($Ban['GiverID'] > 0 ? "<a class=\"orange\" href=\"messages.php?mode=write&uid={$Ban['GiverID']}\">{$Ban['GiverUsername']}</a>" : "<b class=\"red\">{$_Lang['ban_bysys']}</b>");

        $parse['Rows'] .= parsetemplate($RowTPL, $Row);
    }
}

if($BansCount == 0)
{
    $parse['Rows'] .= '<tr><th class="orange" colspan="6">'.$_Lang['NoBannedPPL'].'</th></tr>';
}
else
{
    $parse['Rows'] .= '<tr><th class="orange" colspan="6">'.$_Lang['CountOfBannedPPL'].': '.$BansCount.'</th></tr>';
}

$page = parsetemplate(gettemplate('banned_body'), $parse);
display($page, $_Lang['ban_title'], false);

?>
