<?php

define('INSIDE', true);

$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

includeLang('records');

$RecordTpl = gettemplate('records_body');
$HeaderTpl = gettemplate('records_section_header');
$TableRows = gettemplate('records_section_rows');

$parse['rec_title'] = $_Lang['rec_title'];

$bloc['section'] = $_Lang['rec_build'];
$bloc['player'] = $_Lang['rec_playe'];
$bloc['level'] = $_Lang['rec_level'];
$parse['building'] = parsetemplate($HeaderTpl, $bloc);

$bloc['section'] = $_Lang['rec_specb'];
$parse['buildspe'] = parsetemplate($HeaderTpl, $bloc);

$bloc['section'] = $_Lang['rec_techn'];
$parse['research'] = parsetemplate($HeaderTpl, $bloc);

$bloc['section'] = $_Lang['rec_fleet'];
$bloc['level'] = $_Lang['rec_nbre'];
$parse['fleet'] = parsetemplate($HeaderTpl, $bloc);

$bloc['section'] = $_Lang['rec_defes'];
$parse['defenses'] = parsetemplate($HeaderTpl, $bloc);

$parse['last_count_was'] = $_Lang['last_count_was'];
$parse['last_count_time'] = prettyDate('d m Y, H:i:s', $_GameConfig['last_update'], 1);

$SQLResult_Records = doquery(
    "SELECT `r`.*, `u`.`username` FROM {{table}} AS `r` LEFT JOIN {{prefix}}users AS `u` ON `u`.`id` = `r`.`id_owner` ORDER BY `r`.`element` ASC",
    'records'
);

if($SQLResult_Records->num_rows > 0)
{
    while($Rec = $SQLResult_Records->fetch_assoc())
    {
        $Row = array();

        $Element = $Rec['element'];
        $UserID = $Rec['id_owner'];
        $UserName = $Rec['username'];
        $Count = $Rec['count'];
        $Row['ElementID'] = $Element;
        $Row['winner'] = ($UserID != 0) ? "<a href=\"profile.php?uid={$UserID}\">{$UserName}</a>" : $_Lang['rec_rien'];
        $Row['element'] = $_Lang['tech'][$Element];
        $Row['count'] = ($Count > 0) ? prettyNumber($Count) : $_Lang['rec_rien'];

        if($Element >= 1 && $Element <= 39 || $Element == 44)
        {
            // Buildings (normal)
            $parse['building'] .= parsetemplate($TableRows, $Row);
        }
        else if($Element >= 41 && $Element <= 99 && $Element != 44 && $Element != 50)
        {
            // Buildings (special)
            $parse['buildspe'] .= parsetemplate($TableRows, $Row);
        }
        else if($Element >= 101 && $Element <= 199)
        {
            // Technology
            $parse['research'] .= parsetemplate($TableRows, $Row);
        }
        else if($Element >= 201 && $Element <= 399)
        {
            // Ships
            $parse['fleet'].= parsetemplate($TableRows, $Row);
        }
        else if($Element >= 401 && $Element <= 599 AND $Element != 407 AND $Element != 408)
        {
            // Defense
            $parse['defenses'] .= parsetemplate($TableRows, $Row);
        }
    }
}

$page = parsetemplate( $RecordTpl, $parse );
display($page, $_Lang['rec_title']);

?>
